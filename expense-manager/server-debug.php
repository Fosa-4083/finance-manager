<?php
/**
 * Server-Debug-Skript
 * 
 * Dieses Skript sammelt detaillierte Informationen über die Anwendung auf dem Server
 * und zeigt sie in einer übersichtlichen Form an.
 */

// Cache-Header setzen, um sicherzustellen, dass die Seite nicht gecacht wird
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

function getDirectorySize($path) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }
    return $size;
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function getFileContent($path, $maxLength = 500) {
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strlen($content) > $maxLength) {
            return substr($content, 0, $maxLength) . '...';
        }
        return $content;
    }
    return null;
}

// HTML-Header
echo '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server-Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .file-content { max-height: 200px; overflow-y: auto; white-space: pre-wrap; }
        .section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .timestamp { color: #666; font-size: 0.9em; }
        .button { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
        .button:hover { background-color: #45a049; }
    </style>
</head>
<body>';

// Header
echo '<h1>Server-Debug für Expense Manager</h1>';
echo '<p class="timestamp">Generiert am: ' . date('Y-m-d H:i:s') . '</p>';
echo '<p>Diese Seite zeigt detaillierte Informationen über die Anwendung auf dem Server.</p>';

// Systemumgebung
echo '<div class="section">';
echo '<h2>Systemumgebung</h2>';
echo '<table>';
echo '<tr><th>Parameter</th><th>Wert</th></tr>';
echo '<tr><td>PHP-Version</td><td>' . phpversion() . '</td></tr>';
echo '<tr><td>Betriebssystem</td><td>' . PHP_OS . '</td></tr>';
echo '<tr><td>Server-Software</td><td>' . $_SERVER['SERVER_SOFTWARE'] . '</td></tr>';
echo '<tr><td>Dokument-Root</td><td>' . $_SERVER['DOCUMENT_ROOT'] . '</td></tr>';
echo '<tr><td>Aktuelles Verzeichnis</td><td>' . __DIR__ . '</td></tr>';
echo '<tr><td>Request-URI</td><td>' . $_SERVER['REQUEST_URI'] . '</td></tr>';
echo '<tr><td>Server-Name</td><td>' . $_SERVER['SERVER_NAME'] . '</td></tr>';
echo '<tr><td>Server-Adresse</td><td>' . $_SERVER['SERVER_ADDR'] . '</td></tr>';
echo '<tr><td>Zeitzone</td><td>' . date_default_timezone_get() . '</td></tr>';
echo '</table>';
echo '</div>';

// PHP-Erweiterungen
echo '<div class="section">';
echo '<h2>PHP-Erweiterungen</h2>';
echo '<table>';
echo '<tr><th>Erweiterung</th><th>Status</th></tr>';
$requiredExtensions = ['pdo', 'pdo_sqlite', 'sqlite3', 'json', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    echo '<tr>';
    echo '<td>' . $ext . '</td>';
    if (extension_loaded($ext)) {
        echo '<td class="success">Geladen</td>';
    } else {
        echo '<td class="error">Nicht geladen</td>';
    }
    echo '</tr>';
}
echo '</table>';
echo '</div>';

// Verzeichnisstruktur
echo '<div class="section">';
echo '<h2>Verzeichnisstruktur</h2>';
echo '<table>';
echo '<tr><th>Verzeichnis</th><th>Existiert</th><th>Größe</th><th>Anzahl Dateien</th><th>Berechtigungen</th></tr>';

$directories = [
    '.',
    './public',
    './src',
    './src/Controllers',
    './src/Models',
    './src/Views',
    './database',
    './config',
    './bootstrap'
];

foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    $path = str_replace('//', '/', $path);
    
    echo '<tr>';
    echo '<td>' . htmlspecialchars($dir) . '</td>';
    
    if (is_dir($path)) {
        $fileCount = count(glob($path . '/*'));
        $size = getDirectorySize($path);
        $perms = fileperms($path);
        $permsOctal = substr(sprintf('%o', $perms), -4);
        
        echo '<td class="success">Ja</td>';
        echo '<td>' . formatBytes($size) . '</td>';
        echo '<td>' . $fileCount . '</td>';
        echo '<td>' . $permsOctal . '</td>';
    } else {
        echo '<td class="error">Nein</td>';
        echo '<td>-</td>';
        echo '<td>-</td>';
        echo '<td>-</td>';
    }
    
    echo '</tr>';
}

echo '</table>';
echo '</div>';

// Wichtige Dateien
echo '<div class="section">';
echo '<h2>Wichtige Dateien</h2>';
echo '<table>';
echo '<tr><th>Datei</th><th>Existiert</th><th>Größe</th><th>Letzte Änderung</th><th>MD5-Hash</th></tr>';

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
    
    echo '<tr>';
    echo '<td>' . htmlspecialchars($file) . '</td>';
    
    if (file_exists($path)) {
        $size = filesize($path);
        $mtime = filemtime($path);
        $hash = getFileHash($path);
        
        echo '<td class="success">Ja</td>';
        echo '<td>' . formatBytes($size) . '</td>';
        echo '<td>' . date('Y-m-d H:i:s', $mtime) . '</td>';
        echo '<td>' . $hash . '</td>';
    } else {
        echo '<td class="error">Nein</td>';
        echo '<td>-</td>';
        echo '<td>-</td>';
        echo '<td>-</td>';
    }
    
    echo '</tr>';
}

echo '</table>';
echo '</div>';

// Dateiinhalte
echo '<div class="section">';
echo '<h2>Dateiinhalte</h2>';
echo '<p>Die ersten 500 Zeichen jeder wichtigen Datei:</p>';

$contentFiles = [
    'index.php',
    'public/index.php',
    '.htaccess',
    'public/.htaccess',
    'config/config.php',
    'src/Router.php'
];

foreach ($contentFiles as $file) {
    $path = __DIR__ . '/' . $file;
    $path = str_replace('//', '/', $path);
    
    echo '<h3>' . htmlspecialchars($file) . '</h3>';
    
    if (file_exists($path)) {
        $content = getFileContent($path);
        echo '<div class="file-content"><pre>' . htmlspecialchars($content) . '</pre></div>';
    } else {
        echo '<p class="error">Datei nicht gefunden</p>';
    }
}

echo '</div>';

// Datenbankverbindung
echo '<div class="section">';
echo '<h2>Datenbankverbindung</h2>';

$dbPath = __DIR__ . '/database/database.sqlite';
if (file_exists($dbPath)) {
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo '<p class="success">Datenbankverbindung erfolgreich!</p>';
        
        // Tabellen
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo '<h3>Tabellen</h3>';
        echo '<ul>';
        foreach ($tables as $table) {
            echo '<li>' . htmlspecialchars($table) . '</li>';
        }
        echo '</ul>';
        
        // Datensätze pro Tabelle
        echo '<h3>Datensätze pro Tabelle</h3>';
        echo '<table>';
        echo '<tr><th>Tabelle</th><th>Anzahl Datensätze</th></tr>';
        
        foreach ($tables as $table) {
            $stmt = $db->query("SELECT COUNT(*) FROM " . $table);
            $count = $stmt->fetchColumn();
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($table) . '</td>';
            echo '<td>' . $count . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        
    } catch (PDOException $e) {
        echo '<p class="error">Datenbankfehler: ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p class="error">Datenbankdatei nicht gefunden: ' . $dbPath . '</p>';
}

echo '</div>';

// Berechtigungen
echo '<div class="section">';
echo '<h2>Berechtigungen</h2>';
echo '<table>';
echo '<tr><th>Pfad</th><th>Typ</th><th>Berechtigungen</th><th>Lesbar</th><th>Schreibbar</th><th>Ausführbar</th></tr>';

$permPaths = [
    '.',
    './index.php',
    './public',
    './public/index.php',
    './database',
    './database/database.sqlite',
    './src',
    './.htaccess',
    './public/.htaccess'
];

foreach ($permPaths as $path) {
    $fullPath = __DIR__ . '/' . $path;
    $fullPath = str_replace('//', '/', $fullPath);
    
    if (file_exists($fullPath)) {
        $perms = fileperms($fullPath);
        $permsOctal = substr(sprintf('%o', $perms), -4);
        $type = is_dir($fullPath) ? 'Verzeichnis' : 'Datei';
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($path) . '</td>';
        echo '<td>' . $type . '</td>';
        echo '<td>' . $permsOctal . '</td>';
        
        if (is_readable($fullPath)) {
            echo '<td class="success">Ja</td>';
        } else {
            echo '<td class="error">Nein</td>';
        }
        
        if (is_writable($fullPath)) {
            echo '<td class="success">Ja</td>';
        } else {
            echo '<td class="error">Nein</td>';
        }
        
        if (is_executable($fullPath)) {
            echo '<td class="success">Ja</td>';
        } else {
            echo '<td class="error">Nein</td>';
        }
        
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($path) . '</td>';
        echo '<td colspan="5" class="error">Nicht gefunden</td>';
        echo '</tr>';
    }
}

echo '</table>';
echo '</div>';

// Funktionstest
echo '<div class="section">';
echo '<h2>Funktionstest</h2>';
echo '<p>Klicken Sie auf die folgenden Links, um die Anwendung zu testen:</p>';
echo '<p><a href="/expense-manager/?nocache=' . time() . '" class="button" target="_blank">Expense Manager öffnen</a></p>';
echo '<p><a href="/expense-manager/public/?nocache=' . time() . '" class="button" target="_blank">Expense Manager (public) öffnen</a></p>';
echo '</div>';

// Vergleichsanleitung
echo '<div class="section">';
echo '<h2>Vergleichsanleitung</h2>';
echo '<p>Um die Daten auf dem Server mit den lokalen Daten zu vergleichen:</p>';
echo '<ol>';
echo '<li>Führen Sie dieses Skript auf dem Server aus</li>';
echo '<li>Führen Sie das gleiche Skript lokal aus</li>';
echo '<li>Vergleichen Sie die MD5-Hashes der wichtigen Dateien</li>';
echo '<li>Überprüfen Sie die Dateiinhalte auf Unterschiede</li>';
echo '<li>Stellen Sie sicher, dass die Datenbankstruktur identisch ist</li>';
echo '</ol>';
echo '</div>';

// Lokale Vergleichsdaten
echo '<div class="section">';
echo '<h2>Lokale Vergleichsdaten</h2>';
echo '<p>Fügen Sie hier die Ausgabe des lokalen Skripts ein, um einen direkten Vergleich zu ermöglichen:</p>';
echo '<textarea style="width: 100%; height: 200px; font-family: monospace;"></textarea>';
echo '</div>';

// Debugging-Tools
echo '<div class="section">';
echo '<h2>Debugging-Tools</h2>';
echo '<p>Weitere Debugging-Tools:</p>';
echo '<ul>';
echo '<li><a href="/expense-manager/force-update.php">Force Update</a> - Erzwingt eine Aktualisierung der Anwendung</li>';
echo '<li><a href="/expense-manager/test-load.php">Test Load</a> - Testet, welche Dateien geladen werden</li>';
echo '<li><a href="/expense-manager/version.php">Version</a> - Zeigt Versionsinformationen an</li>';
echo '</ul>';
echo '</div>';

// Footer
echo '<footer style="margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; text-align: center;">';
echo '<p>Server-Debug-Skript für Expense Manager | Generiert am: ' . date('Y-m-d H:i:s') . '</p>';
echo '</footer>';

echo '</body></html>'; 