<?php
/**
 * Diagnose-Skript für den Expense Manager
 * 
 * Dieses Skript überprüft den Status der Datenbank und gibt Informationen
 * zur Fehlerbehebung aus.
 */

// Fehler anzeigen
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Expense Manager - Diagnose</h1>";
echo "<pre>";

// Systemumgebung
echo "=== Systemumgebung ===\n";
echo "PHP-Version: " . phpversion() . "\n";
echo "Webserver: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Dokumenten-Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Skript-Pfad: " . __FILE__ . "\n";
echo "Aktuelles Verzeichnis: " . getcwd() . "\n\n";

// Konfiguration laden
echo "=== Datenbank-Konfiguration ===\n";
try {
    $config = require __DIR__ . '/config/database.php';
    echo "Konfiguration geladen.\n";
    echo "Konfigurierter Datenbankpfad: " . $config['path'] . "\n";
    echo "Backup-Verzeichnis: " . $config['backup_dir'] . "\n";
    echo "Standard-Pfad: " . $config['default_path'] . "\n";
    echo "Server-Pfad: " . $config['server_path'] . "\n\n";
} catch (Exception $e) {
    echo "FEHLER beim Laden der Konfiguration: " . $e->getMessage() . "\n\n";
}

// Umgebungsvariablen
echo "=== Umgebungsvariablen ===\n";
echo "DB_PATH: " . (getenv('DB_PATH') ?: 'nicht gesetzt') . "\n";

// .env-Datei
echo "\n=== .env-Datei ===\n";
$envFile = '/var/expense-manager/.env';
if (file_exists($envFile)) {
    echo "Datei existiert: $envFile\n";
    echo "Inhalt:\n" . file_get_contents($envFile) . "\n";
} else {
    echo "Datei existiert nicht: $envFile\n";
}

// Datenbankdatei
echo "\n=== Datenbankdatei ===\n";
if (isset($config['path'])) {
    $dbPath = $config['path'];
    if (file_exists($dbPath)) {
        echo "Datenbankdatei existiert: $dbPath\n";
        echo "Größe: " . filesize($dbPath) . " Bytes\n";
        echo "Berechtigungen: " . substr(sprintf('%o', fileperms($dbPath)), -4) . "\n";
        echo "Besitzer: " . posix_getpwuid(fileowner($dbPath))['name'] . "\n";
        echo "Gruppe: " . posix_getgrgid(filegroup($dbPath))['name'] . "\n";
    } else {
        echo "Datenbankdatei existiert nicht: $dbPath\n";
        
        // Überprüfen, ob das Verzeichnis existiert
        $dbDir = dirname($dbPath);
        if (is_dir($dbDir)) {
            echo "Verzeichnis existiert: $dbDir\n";
            echo "Berechtigungen: " . substr(sprintf('%o', fileperms($dbDir)), -4) . "\n";
            echo "Besitzer: " . posix_getpwuid(fileowner($dbDir))['name'] . "\n";
            echo "Gruppe: " . posix_getgrgid(filegroup($dbDir))['name'] . "\n";
        } else {
            echo "Verzeichnis existiert nicht: $dbDir\n";
        }
    }
}

// Symlinks überprüfen
echo "\n=== Symlinks ===\n";
$expectedDbSymlink = __DIR__ . '/database/database.sqlite';
if (is_link($expectedDbSymlink)) {
    echo "Symlink existiert: $expectedDbSymlink\n";
    echo "Ziel: " . readlink($expectedDbSymlink) . "\n";
} else if (file_exists($expectedDbSymlink)) {
    echo "Datei existiert (kein Symlink): $expectedDbSymlink\n";
} else {
    echo "Datei existiert nicht: $expectedDbSymlink\n";
}

$expectedBackupSymlink = __DIR__ . '/database/backups';
if (is_link($expectedBackupSymlink)) {
    echo "Symlink existiert: $expectedBackupSymlink\n";
    echo "Ziel: " . readlink($expectedBackupSymlink) . "\n";
} else if (is_dir($expectedBackupSymlink)) {
    echo "Verzeichnis existiert (kein Symlink): $expectedBackupSymlink\n";
} else {
    echo "Verzeichnis existiert nicht: $expectedBackupSymlink\n";
}

// Datenbankverbindung testen
echo "\n=== Datenbankverbindung ===\n";
try {
    if (isset($config['path']) && file_exists($config['path'])) {
        $db = new PDO('sqlite:' . $config['path']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Verbindung zur Datenbank hergestellt.\n";
        
        // Tabellen überprüfen
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        echo "Vorhandene Tabellen: " . implode(', ', $tables) . "\n";
        
        // Prüfen, ob die users-Tabelle existiert
        if (in_array('users', $tables)) {
            $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
            echo "Anzahl Benutzer: $userCount\n";
        } else {
            echo "FEHLER: Die Tabelle 'users' existiert nicht!\n";
        }
    } else {
        echo "Kann keine Verbindung herstellen, da die Datenbankdatei nicht existiert.\n";
    }
} catch (PDOException $e) {
    echo "FEHLER bei der Datenbankverbindung: " . $e->getMessage() . "\n";
}

// Empfehlungen
echo "\n=== Empfehlungen ===\n";
if (!isset($config['path']) || !file_exists($config['path'])) {
    echo "1. Führen Sie das Setup-Skript aus, um die Datenbank zu erstellen:\n";
    echo "   php " . __DIR__ . "/setup_database.php\n\n";
}

if (!is_link($expectedDbSymlink) && !file_exists($expectedDbSymlink)) {
    echo "2. Erstellen Sie einen Symlink zur Datenbank:\n";
    echo "   ln -sf " . ($config['path'] ?? '/pfad/zur/datenbank.sqlite') . " " . $expectedDbSymlink . "\n\n";
}

if (!is_link($expectedBackupSymlink) && !is_dir($expectedBackupSymlink)) {
    echo "3. Erstellen Sie einen Symlink zum Backup-Verzeichnis:\n";
    echo "   ln -sf " . ($config['backup_dir'] ?? '/pfad/zu/backups') . " " . $expectedBackupSymlink . "\n\n";
}

echo "4. Führen Sie das Deployment-Skript aus, um die Umgebung automatisch zu konfigurieren:\n";
echo "   bash " . __DIR__ . "/deploy.sh\n\n";

echo "</pre>"; 