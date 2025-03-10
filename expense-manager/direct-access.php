<?php
// Diese Datei greift direkt auf die index.php zu

// Cache-Header setzen
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<h1>Direkter Zugriff auf index.php</h1>";
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

// Versuche, die index.php direkt einzubinden
echo "<h2>Direktes Einbinden der index.php</h2>";
echo "<p>Versuche, die index.php direkt einzubinden...</p>";

// Speichere die aktuelle Ausgabepufferung
ob_start();

try {
    // Versuche, die index.php einzubinden
    include 'index.php';
    
    // Hole die Ausgabe
    $output = ob_get_clean();
    
    echo "<p>Einbindung erfolgreich!</p>";
    echo "<h3>Ausgabe der index.php:</h3>";
    echo "<pre>";
    echo htmlspecialchars($output);
    echo "</pre>";
} catch (Exception $e) {
    // Bei einem Fehler
    ob_end_clean();
    echo "<p>Fehler beim Einbinden der index.php: " . $e->getMessage() . "</p>";
}

// Versuche, die public/index.php direkt einzubinden
echo "<h2>Direktes Einbinden der public/index.php</h2>";
echo "<p>Versuche, die public/index.php direkt einzubinden...</p>";

// Speichere die aktuelle Ausgabepufferung
ob_start();

try {
    // Versuche, die public/index.php einzubinden
    include 'public/index.php';
    
    // Hole die Ausgabe
    $output = ob_get_clean();
    
    echo "<p>Einbindung erfolgreich!</p>";
    echo "<h3>Ausgabe der public/index.php:</h3>";
    echo "<pre>";
    echo htmlspecialchars($output);
    echo "</pre>";
} catch (Exception $e) {
    // Bei einem Fehler
    ob_end_clean();
    echo "<p>Fehler beim Einbinden der public/index.php: " . $e->getMessage() . "</p>";
}

// Zeige Links zu anderen Test-Dateien
echo "<h2>Links</h2>";
echo "<ul>";
echo "<li><a href='test-server.php'>Zurück zum Server-Test</a></li>";
echo "<li><a href='public/test.php'>Test im public-Verzeichnis</a></li>";
echo "</ul>"; 