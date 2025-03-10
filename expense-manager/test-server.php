<?php
// Einfaches Test-Skript für den Server

// Cache-Header setzen
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<!DOCTYPE html>
<html lang='de'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Server-Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>Server-Test</h1>
    <p>Generiert am: " . date('Y-m-d H:i:s') . "</p>";

// Überprüfe, ob die Datei direkt aufgerufen wurde
echo "<h2>Aufruf-Informationen</h2>";
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "</pre>";

// Überprüfe, ob die wichtigsten Dateien existieren
echo "<h2>Datei-Überprüfung</h2>";
echo "<ul>";

$files = [
    'index.php',
    'public/index.php',
    '.htaccess',
    'public/.htaccess',
    'config/config.php',
    'src/Router.php',
    'src/Controllers/ExpenseController.php',
    'src/Views/expenses/index.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo "<li>";
    echo "<strong>" . htmlspecialchars($file) . ":</strong> ";
    
    if (file_exists($path)) {
        echo "<span class='success'>Existiert</span> (" . filesize($path) . " Bytes, zuletzt geändert am " . date('Y-m-d H:i:s', filemtime($path)) . ")";
        
        // Zeige die ersten Zeilen der Datei
        if (is_readable($path) && pathinfo($path, PATHINFO_EXTENSION) == 'php') {
            $content = file_get_contents($path);
            $lines = explode("\n", $content);
            $firstLines = array_slice($lines, 0, 5);
            
            echo "<pre>";
            foreach ($firstLines as $line) {
                echo htmlspecialchars($line) . "\n";
            }
            echo "...</pre>";
        }
    } else {
        echo "<span class='error'>Existiert nicht</span>";
    }
    
    echo "</li>";
}

echo "</ul>";

// Überprüfe, ob die Datenbank existiert und zugänglich ist
echo "<h2>Datenbank-Überprüfung</h2>";

$dbPath = __DIR__ . '/database/database.sqlite';
if (file_exists($dbPath)) {
    echo "<p><span class='success'>Datenbank existiert</span> (" . filesize($dbPath) . " Bytes)</p>";
    
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p><span class='success'>Datenbankverbindung erfolgreich</span></p>";
        
        // Überprüfe, ob die wichtigsten Tabellen existieren
        $tables = ['users', 'expenses', 'categories', 'projects'];
        echo "<ul>";
        
        foreach ($tables as $table) {
            $stmt = $db->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='$table'");
            $count = $stmt->fetchColumn();
            
            echo "<li>";
            echo "<strong>Tabelle " . htmlspecialchars($table) . ":</strong> ";
            
            if ($count > 0) {
                $stmt = $db->query("SELECT COUNT(*) FROM $table");
                $rowCount = $stmt->fetchColumn();
                echo "<span class='success'>Existiert</span> ($rowCount Datensätze)";
            } else {
                echo "<span class='error'>Existiert nicht</span>";
            }
            
            echo "</li>";
        }
        
        echo "</ul>";
    } catch (PDOException $e) {
        echo "<p><span class='error'>Datenbankfehler: " . htmlspecialchars($e->getMessage()) . "</span></p>";
    }
} else {
    echo "<p><span class='error'>Datenbank existiert nicht</span></p>";
}

// Überprüfe die PHP-Konfiguration
echo "<h2>PHP-Konfiguration</h2>";
echo "<pre>";
echo "PHP-Version: " . phpversion() . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "</pre>";

// Überprüfe die Apache-Module
echo "<h2>Apache-Module</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<p>Gefundene Module: " . count($modules) . "</p>";
    echo "<ul>";
    $importantModules = ['mod_rewrite', 'mod_headers', 'mod_expires'];
    
    foreach ($importantModules as $module) {
        echo "<li>";
        echo "<strong>" . htmlspecialchars($module) . ":</strong> ";
        
        if (in_array($module, $modules)) {
            echo "<span class='success'>Geladen</span>";
        } else {
            echo "<span class='error'>Nicht geladen</span>";
        }
        
        echo "</li>";
    }
    
    echo "</ul>";
} else {
    echo "<p><span class='warning'>apache_get_modules() ist nicht verfügbar</span></p>";
}

// Überprüfe die Berechtigungen
echo "<h2>Berechtigungen</h2>";
echo "<ul>";

$paths = [
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

foreach ($paths as $path) {
    $fullPath = __DIR__ . '/' . $path;
    $fullPath = str_replace('//', '/', $fullPath);
    
    echo "<li>";
    echo "<strong>" . htmlspecialchars($path) . ":</strong> ";
    
    if (file_exists($fullPath)) {
        $perms = fileperms($fullPath);
        $permsOctal = substr(sprintf('%o', $perms), -4);
        
        echo "Berechtigungen = " . $permsOctal;
        
        if (is_readable($fullPath)) {
            echo ", <span class='success'>Lesbar</span>";
        } else {
            echo ", <span class='error'>Nicht lesbar</span>";
        }
        
        if (is_writable($fullPath)) {
            echo ", <span class='success'>Schreibbar</span>";
        } else {
            echo ", <span class='error'>Nicht schreibbar</span>";
        }
        
        if (is_dir($fullPath)) {
            echo ", Verzeichnis";
        } else {
            echo ", Datei";
        }
    } else {
        echo "<span class='error'>Existiert nicht</span>";
    }
    
    echo "</li>";
}

echo "</ul>";

// Zeige Links zu anderen Test-Skripten
echo "<h2>Weitere Tests</h2>";
echo "<ul>";
echo "<li><a href='server-debug.php'>Server-Debug</a></li>";
echo "<li><a href='url-test.php'>URL-Test</a></li>";
echo "<li><a href='check-root-index.php'>Root-Index überprüfen</a></li>";
echo "<li><a href='cleanup.php'>Aufräumen</a></li>";
echo "</ul>";

echo "</body>
</html>"; 