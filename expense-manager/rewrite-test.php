<?php
// Diese Datei testet, ob URL-Rewriting funktioniert

// Cache-Header setzen
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<h1>URL-Rewriting Test</h1>";
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

// Überprüfe, ob mod_rewrite aktiviert ist
echo "<h2>Überprüfung von mod_rewrite</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<p>Apache-Module sind verfügbar. Überprüfe mod_rewrite...</p>";
    if (in_array('mod_rewrite', $modules)) {
        echo "<p style='color: green;'>mod_rewrite ist aktiviert!</p>";
    } else {
        echo "<p style='color: red;'>mod_rewrite ist NICHT aktiviert!</p>";
        echo "<p>Verfügbare Module:</p>";
        echo "<pre>" . implode("\n", $modules) . "</pre>";
    }
} else {
    echo "<p>Kann Apache-Module nicht überprüfen (möglicherweise CGI/FastCGI).</p>";
    echo "<p>Überprüfe .htaccess-Dateien...</p>";
    
    // Überprüfe .htaccess im Hauptverzeichnis
    $htaccess_root = file_exists(__DIR__ . '/.htaccess');
    echo "<p>.htaccess im Hauptverzeichnis: " . ($htaccess_root ? "Vorhanden" : "NICHT vorhanden") . "</p>";
    
    // Überprüfe .htaccess im public-Verzeichnis
    $htaccess_public = file_exists(__DIR__ . '/public/.htaccess');
    echo "<p>.htaccess im public-Verzeichnis: " . ($htaccess_public ? "Vorhanden" : "NICHT vorhanden") . "</p>";
}

// Überprüfe, ob AllowOverride aktiviert ist
echo "<h2>Überprüfung von AllowOverride</h2>";
echo "<p>Um zu testen, ob AllowOverride aktiviert ist, versuchen Sie, auf diese URL zuzugreifen:</p>";
echo "<p><a href='rewrite-test/check'>rewrite-test/check</a></p>";
echo "<p>Wenn Sie diese Seite sehen, ist AllowOverride wahrscheinlich NICHT aktiviert.</p>";

// Zeige Links zu anderen Test-Dateien
echo "<h2>Links</h2>";
echo "<ul>";
echo "<li><a href='test-server.php'>Zurück zum Server-Test</a></li>";
echo "<li><a href='public/test.php'>Test im public-Verzeichnis</a></li>";
echo "<li><a href='index.php'>Hauptseite direkt aufrufen</a></li>";
echo "</ul>"; 