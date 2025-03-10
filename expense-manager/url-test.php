<?php
// Cache-Header setzen
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<h1>URL-Test</h1>";
echo "<p>Generiert am: " . date('Y-m-d H:i:s') . "</p>";

// Zeige URL-Informationen
echo "<h2>URL-Informationen</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Parameter</th><th>Wert</th></tr>";
echo "<tr><td>REQUEST_URI</td><td>" . htmlspecialchars($_SERVER['REQUEST_URI']) . "</td></tr>";
echo "<tr><td>SCRIPT_NAME</td><td>" . htmlspecialchars($_SERVER['SCRIPT_NAME']) . "</td></tr>";
echo "<tr><td>PHP_SELF</td><td>" . htmlspecialchars($_SERVER['PHP_SELF']) . "</td></tr>";
echo "<tr><td>DOCUMENT_ROOT</td><td>" . htmlspecialchars($_SERVER['DOCUMENT_ROOT']) . "</td></tr>";
echo "<tr><td>SCRIPT_FILENAME</td><td>" . htmlspecialchars($_SERVER['SCRIPT_FILENAME']) . "</td></tr>";
echo "<tr><td>__FILE__</td><td>" . htmlspecialchars(__FILE__) . "</td></tr>";
echo "<tr><td>__DIR__</td><td>" . htmlspecialchars(__DIR__) . "</td></tr>";
echo "<tr><td>getcwd()</td><td>" . htmlspecialchars(getcwd()) . "</td></tr>";
echo "<tr><td>dirname(__FILE__)</td><td>" . htmlspecialchars(dirname(__FILE__)) . "</td></tr>";
echo "</table>";

// Zeige Verzeichnisstruktur
echo "<h2>Verzeichnisstruktur</h2>";
echo "<pre>";
echo "Aktuelles Verzeichnis: " . __DIR__ . "\n\n";

// Überprüfe, ob ein expense-manager Unterverzeichnis existiert
$subdir = __DIR__ . '/expense-manager';
if (is_dir($subdir)) {
    echo "Unterverzeichnis expense-manager existiert!\n";
    echo "Inhalt des Unterverzeichnisses:\n";
    $files = scandir($subdir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file\n";
        }
    }
} else {
    echo "Unterverzeichnis expense-manager existiert nicht.\n";
}

// Überprüfe das übergeordnete Verzeichnis
$parentDir = dirname(__DIR__);
echo "\nÜbergeordnetes Verzeichnis: $parentDir\n";
echo "Inhalt des übergeordneten Verzeichnisses:\n";
$files = scandir($parentDir);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file\n";
    }
}

echo "</pre>";

// Zeige Dateipfade
echo "<h2>Wichtige Dateipfade</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Datei</th><th>Existiert</th><th>Pfad</th></tr>";

$files = [
    'index.php',
    'public/index.php',
    '.htaccess',
    'public/.htaccess',
    'config/config.php',
    '../index.php',
    '../expense-manager/index.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $path = str_replace('//', '/', $path);
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($file) . "</td>";
    
    if (file_exists($path)) {
        echo "<td style='color:green'>Ja</td>";
        echo "<td>" . htmlspecialchars($path) . "</td>";
    } else {
        echo "<td style='color:red'>Nein</td>";
        echo "<td>-</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

// Zeige Links
echo "<h2>Test-Links</h2>";
echo "<ul>";
echo "<li><a href='/'>Root</a></li>";
echo "<li><a href='/expense-manager/'>expense-manager/</a></li>";
echo "<li><a href='/expense-manager/public/'>expense-manager/public/</a></li>";
echo "<li><a href='/expense-manager/index.php'>expense-manager/index.php</a></li>";
echo "<li><a href='/expense-manager/public/index.php'>expense-manager/public/index.php</a></li>";
echo "</ul>";

// Zeige Umgebungsvariablen
echo "<h2>Umgebungsvariablen</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>"; 