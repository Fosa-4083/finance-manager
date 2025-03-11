<?php
/**
 * Datenbank-Konfiguration
 * 
 * Diese Datei definiert die Konfiguration für die MariaDB/MySQL-Datenbankverbindung.
 */

// Standard-Konfiguration für MySQL/MariaDB
$mysqlConfig = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'finance-manager',
    'username' => getenv('DB_USER') ?: (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user()),
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];

// .env-Datei prüfen (falls vorhanden)
$envFile = '/var/expense-manager/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    
    // MySQL/MariaDB-Konfiguration aus .env-Datei lesen
    if (preg_match('/DB_HOST=(.+)/', $envContent, $matches)) {
        $mysqlConfig['host'] = $matches[1];
    }
    if (preg_match('/DB_PORT=(.+)/', $envContent, $matches)) {
        $mysqlConfig['port'] = $matches[1];
    }
    if (preg_match('/DB_NAME=(.+)/', $envContent, $matches)) {
        $mysqlConfig['database'] = $matches[1];
    }
    if (preg_match('/DB_USER=(.+)/', $envContent, $matches)) {
        $mysqlConfig['username'] = $matches[1];
    }
    if (preg_match('/DB_PASSWORD=(.+)/', $envContent, $matches)) {
        $mysqlConfig['password'] = $matches[1];
    }
}

// Konfiguration zurückgeben
return [
    'mysql' => $mysqlConfig
]; 