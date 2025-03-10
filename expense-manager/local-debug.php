<?php
/**
 * Lokales Debug-Skript
 * 
 * Dieses Skript sammelt detaillierte Informationen über die lokale Anwendung
 * und gibt sie in einem Format aus, das für den Vergleich mit dem Server geeignet ist.
 */

// Fehlerberichterstattung aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Zeitzone setzen
date_default_timezone_set('Europe/Vienna');

// Hilfsfunktionen
function getFileHash($path) {
    if (file_exists($path)) {
        return md5_file($path);
    }
    return null;
}

// Ausgabe als JSON
header('Content-Type: application/json');

// Daten sammeln
$data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'system' => [
        'php_version' => phpversion(),
        'os' => PHP_OS,
        'directory' => __DIR__
    ],
    'files' => []
];

// Wichtige Dateien
$files = [
    'index.php',
    'public/index.php',
    '.htaccess',
    'public/.htaccess',
    'config/config.php',
    'src/Router.php',
    'src/Controllers/ExpenseController.php',
    'src/Models/Expense.php',
    'src/Views/expenses/index.php',
    'database/database.sqlite'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $path = str_replace('//', '/', $path);
    
    if (file_exists($path)) {
        $data['files'][$file] = [
            'exists' => true,
            'size' => filesize($path),
            'modified' => date('Y-m-d H:i:s', filemtime($path)),
            'hash' => getFileHash($path)
        ];
        
        // Für Textdateien auch den Inhalt speichern
        if (pathinfo($path, PATHINFO_EXTENSION) != 'sqlite') {
            $data['files'][$file]['content'] = file_get_contents($path);
        }
    } else {
        $data['files'][$file] = [
            'exists' => false
        ];
    }
}

// Datenbankstruktur
$dbPath = __DIR__ . '/database/database.sqlite';
if (file_exists($dbPath)) {
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Tabellen
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $data['database'] = [
            'exists' => true,
            'tables' => $tables,
            'records' => []
        ];
        
        // Datensätze pro Tabelle
        foreach ($tables as $table) {
            $stmt = $db->query("SELECT COUNT(*) FROM " . $table);
            $count = $stmt->fetchColumn();
            $data['database']['records'][$table] = $count;
        }
    } catch (PDOException $e) {
        $data['database'] = [
            'exists' => true,
            'error' => $e->getMessage()
        ];
    }
} else {
    $data['database'] = [
        'exists' => false
    ];
}

// Ausgabe
echo json_encode($data, JSON_PRETTY_PRINT); 