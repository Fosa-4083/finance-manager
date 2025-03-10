<?php
// Einfache Test-Datei im public-Verzeichnis

// Cache-Header setzen
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<h1>Test im public-Verzeichnis</h1>";
echo "<p>Generiert am: " . date('Y-m-d H:i:s') . "</p>";

// Zeige Umgebungsinformationen
echo "<h2>Umgebungsinformationen</h2>";
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "__FILE__: " . __FILE__ . "\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "</pre>";

// Überprüfe, ob die index.php im selben Verzeichnis existiert
$indexPath = __DIR__ . '/index.php';
echo "<h2>Überprüfung der index.php</h2>";
if (file_exists($indexPath)) {
    echo "<p>Die Datei index.php existiert im selben Verzeichnis.</p>";
    echo "<p>Größe: " . filesize($indexPath) . " Bytes</p>";
    echo "<p>Letzte Änderung: " . date('Y-m-d H:i:s', filemtime($indexPath)) . "</p>";
    
    // Zeige die ersten Zeilen der Datei
    $content = file_get_contents($indexPath);
    $lines = explode("\n", $content);
    $firstLines = array_slice($lines, 0, 10);
    
    echo "<h3>Die ersten 10 Zeilen der index.php:</h3>";
    echo "<pre>";
    foreach ($firstLines as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    echo "</pre>";
} else {
    echo "<p>Die Datei index.php existiert NICHT im selben Verzeichnis!</p>";
}

// Überprüfe, ob die .htaccess im selben Verzeichnis existiert
$htaccessPath = __DIR__ . '/.htaccess';
echo "<h2>Überprüfung der .htaccess</h2>";
if (file_exists($htaccessPath)) {
    echo "<p>Die Datei .htaccess existiert im selben Verzeichnis.</p>";
    echo "<p>Größe: " . filesize($htaccessPath) . " Bytes</p>";
    echo "<p>Letzte Änderung: " . date('Y-m-d H:i:s', filemtime($htaccessPath)) . "</p>";
    
    // Zeige den Inhalt der Datei
    $content = file_get_contents($htaccessPath);
    
    echo "<h3>Inhalt der .htaccess:</h3>";
    echo "<pre>";
    echo htmlspecialchars($content);
    echo "</pre>";
} else {
    echo "<p>Die Datei .htaccess existiert NICHT im selben Verzeichnis!</p>";
}

// Zeige Links zu anderen Test-Dateien
echo "<h2>Links</h2>";
echo "<ul>";
echo "<li><a href='../test-server.php'>Zurück zum Server-Test</a></li>";
echo "<li><a href='../'>Zurück zum Hauptverzeichnis</a></li>";
echo "<li><a href='index.php'>index.php direkt aufrufen</a></li>";
echo "</ul>"; 