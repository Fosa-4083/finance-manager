<?php
/**
 * Datenbank-Konfiguration
 * 
 * Diese Datei definiert die Konfiguration für die Datenbankverbindung.
 * Sie ermöglicht eine flexible Konfiguration des Datenbankpfads.
 */

// Standard-Datenbankpfad relativ zum Projektverzeichnis
$defaultPath = __DIR__ . '/../database/database.sqlite';

// Server-Pfad für persistente Daten
$serverPath = '/var/expense-manager/database/database.sqlite';

// Umgebungsvariable für den Datenbankpfad
$envPath = getenv('DB_PATH');

// .env-Datei prüfen (falls vorhanden)
$envFile = '/var/expense-manager/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/DB_PATH=(.+)/', $envContent, $matches)) {
        $envPath = $matches[1];
    }
}

// Datenbankpfad bestimmen (Priorität: Umgebungsvariable > Server-Pfad > Standard-Pfad)
$dbPath = $envPath ?: (file_exists($serverPath) ? $serverPath : $defaultPath);

// Backup-Verzeichnis bestimmen
$backupDir = dirname($dbPath) . '/backups';

// Konfiguration zurückgeben
return [
    'path' => $dbPath,
    'backup_dir' => $backupDir,
    'default_path' => $defaultPath,
    'server_path' => $serverPath
]; 