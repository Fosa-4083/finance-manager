<?php

// Erstelle Zielverzeichnis, falls es nicht existiert
$targetDir = 'package';
if (!file_exists($targetDir)) {
    mkdir($targetDir);
    mkdir("$targetDir/expense-manager");
}

// Kopiere die Hauptverzeichnisse
$directories = [
    'expense-manager/src' => "$targetDir/expense-manager/src",
    'expense-manager/public' => "$targetDir/expense-manager/public",
    'expense-manager/database' => "$targetDir/expense-manager/database"
];

foreach ($directories as $src => $dest) {
    if (file_exists($src)) {
        echo "Kopiere $src nach $dest\n";
        shell_exec("cp -r $src $dest");
    } else {
        echo "Warnung: Verzeichnis $src nicht gefunden\n";
    }
}

// Kopiere die .htaccess Datei in das public Verzeichnis
$htaccessContent = <<<EOT
RewriteEngine On
RewriteBase /

# Wenn die Datei oder das Verzeichnis nicht existiert
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Leite alle Anfragen an index.php weiter
RewriteRule ^(.*)$ index.php [QSA,L]

# PHP Fehler anzeigen (nur für Entwicklung, in Produktion auskommentieren)
php_flag display_errors on
php_value error_reporting E_ALL
EOT;

file_put_contents("$targetDir/expense-manager/public/.htaccess", $htaccessContent);
echo "Erstelle .htaccess in public Verzeichnis\n";

// Kopiere README.md
$readmeContent = <<<EOT
# Finanzverwaltung - Installations- und Einrichtungsanleitung

## Systemvoraussetzungen

- PHP 8.0 oder höher
- SQLite3 Unterstützung für PHP
- mod_rewrite für Apache (oder entsprechende URL-Rewriting-Funktionalität für andere Webserver)
- Schreibrechte für das Verzeichnis `database/`

## Installation

1. Kopieren Sie den Inhalt des expense-manager Verzeichnisses in das gewünschte Verzeichnis auf Ihrem Webserver.

2. Stellen Sie sicher, dass das Verzeichnis `database/` für PHP schreibbar ist:
   ```bash
   chmod 755 database/
   chmod 644 database/database.sqlite
   ```

3. Konfigurieren Sie Ihren Webserver:
   - Stellen Sie sicher, dass mod_rewrite aktiviert ist
   - Die .htaccess-Datei befindet sich bereits im public Verzeichnis

4. Setzen Sie die korrekten Berechtigungen:
   ```bash
   find . -type f -exec chmod 644 {} \;
   find . -type d -exec chmod 755 {} \;
   ```

## Fehlerbehebung

1. **Weiße Seite oder 500 Error**
   - Überprüfen Sie die PHP-Fehlerprotokolle
   - Stellen Sie sicher, dass SQLite3 aktiviert ist
   - Überprüfen Sie die Dateiberechtigungen

2. **Datenbank-Fehler**
   - Stellen Sie sicher, dass database/database.sqlite existiert und schreibbar ist

3. **URL-Routing funktioniert nicht**
   - Überprüfen Sie, ob mod_rewrite aktiviert ist
   - Überprüfen Sie die .htaccess-Konfiguration
EOT;

file_put_contents("$targetDir/README.md", $readmeContent);
echo "Erstelle README.md\n";

// Erstelle ZIP-Archiv
$zip = new ZipArchive();
$zipName = 'expense-manager-package.zip';

if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($targetDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($targetDir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    
    $zip->close();
    echo "ZIP-Archiv $zipName wurde erfolgreich erstellt\n";
} else {
    echo "Fehler beim Erstellen des ZIP-Archivs\n";
}

// Lösche temporäres Verzeichnis
shell_exec("rm -rf $targetDir");
echo "Aufräumen abgeschlossen\n"; 