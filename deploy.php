<?php
/**
 * FTP Deployment Skript
 * 
 * Dieses Skript lädt Dateien über FTP auf den Server hoch.
 */

// FTP Zugangsdaten
$ftpServer = '88.151.73.12';
$ftpUser = 'strasra';
$ftpPassword = '4083&Hbst';
$ftpPort = 21; // Standard FTP-Port

// Lokales Verzeichnis, das hochgeladen werden soll
$localDir = __DIR__;

// Zielverzeichnis auf dem FTP-Server
$remoteDir = '/'; // Ändern Sie dies entsprechend Ihrem Zielverzeichnis

// Dateien und Verzeichnisse, die nicht hochgeladen werden sollen
$excludeList = [
    '.git',
    '.gitignore',
    'deploy.php',
    'README.md',
    '.env',
    'vendor', // Wenn Sie Composer verwenden und Abhängigkeiten auf dem Server installieren möchten
    'node_modules' // Wenn Sie npm verwenden
];

// Verbindung zum FTP-Server herstellen
echo "Verbinde mit FTP-Server $ftpServer...\n";
$conn = ftp_connect($ftpServer, $ftpPort);

if (!$conn) {
    die("Konnte keine Verbindung zum FTP-Server herstellen.\n");
}

// Anmelden
echo "Melde an als $ftpUser...\n";
if (!ftp_login($conn, $ftpUser, $ftpPassword)) {
    ftp_close($conn);
    die("Anmeldung fehlgeschlagen.\n");
}

// Passiven Modus aktivieren (hilft bei Firewall-Problemen)
ftp_pasv($conn, true);

// Funktion zum rekursiven Hochladen von Dateien
function uploadDirectory($conn, $localDir, $remoteDir, $excludeList) {
    // Erstelle Remote-Verzeichnis, falls es nicht existiert
    if (!@ftp_chdir($conn, $remoteDir)) {
        ftp_mkdir($conn, $remoteDir);
        ftp_chdir($conn, $remoteDir);
    }
    
    $handle = opendir($localDir);
    
    while (($file = readdir($handle)) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        
        $localPath = "$localDir/$file";
        $remotePath = "$remoteDir/$file";
        
        // Überprüfen, ob die Datei/das Verzeichnis ausgeschlossen werden soll
        $excluded = false;
        foreach ($excludeList as $excludeItem) {
            if ($file == $excludeItem || strpos($localPath, "/$excludeItem/") !== false) {
                $excluded = true;
                break;
            }
        }
        
        if ($excluded) {
            echo "Überspringe $localPath\n";
            continue;
        }
        
        if (is_dir($localPath)) {
            echo "Erstelle Verzeichnis $remotePath\n";
            if (!@ftp_chdir($conn, $remotePath)) {
                ftp_mkdir($conn, $remotePath);
            } else {
                ftp_chdir($conn, $remoteDir);
            }
            uploadDirectory($conn, $localPath, $remotePath, $excludeList);
        } else {
            echo "Lade hoch: $localPath -> $remotePath\n";
            ftp_put($conn, $remotePath, $localPath, FTP_BINARY);
        }
    }
    
    closedir($handle);
    ftp_chdir($conn, dirname($remoteDir));
}

// Starte den Upload-Prozess
echo "Starte Upload-Prozess...\n";
uploadDirectory($conn, $localDir, $remoteDir, $excludeList);

// Verbindung schließen
echo "Upload abgeschlossen. Schließe Verbindung...\n";
ftp_close($conn);

echo "Deployment erfolgreich abgeschlossen!\n"; 