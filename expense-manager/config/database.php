<?php
/**
 * Datenbank-Konfiguration
 * 
 * Diese Datei definiert den Pfad zur Datenbank und ermöglicht es,
 * den Pfad über Umgebungsvariablen zu überschreiben.
 */

// Standard-Datenbankpfad (relativ zum Projektverzeichnis)
$defaultPath = __DIR__ . '/../database/database.sqlite';

// Datenbankpfad aus Umgebungsvariable oder .env-Datei
$envPath = getenv('DB_PATH');

// Datenbankpfad für persistente Daten auf dem Server
$serverPath = '/var/data/expense-manager/database/database.sqlite';

// Priorisierung: 1. Umgebungsvariable, 2. Server-Pfad (wenn existiert), 3. Standard-Pfad
$dbPath = $envPath ?: (file_exists($serverPath) ? $serverPath : $defaultPath);

return [
    'path' => $dbPath,
    'backup_dir' => dirname($dbPath) . '/backups',
    'default_path' => $defaultPath,
    'server_path' => $serverPath,
]; 