<?php
// Diese Datei testet und korrigiert die Basis-URL

// Cache-Header setzen
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<h1>Basis-URL Test und Korrektur</h1>";
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

// Analysiere die aktuelle URL
$current_url = $_SERVER['REQUEST_URI'];
$base_path = '';

// Extrahiere den Basispfad (z.B. /expense-manager)
if (preg_match('/^(\/[^\/]+)\//', $current_url, $matches)) {
    $base_path = $matches[1];
    echo "<p>Erkannter Basispfad: <strong>$base_path</strong></p>";
} else {
    echo "<p>Kein Basispfad erkannt. Die Anwendung scheint im Root-Verzeichnis zu sein.</p>";
}

// Überprüfe die Router.php-Datei
$router_file = __DIR__ . '/src/Router.php';
$router_content = file_get_contents($router_file);

echo "<h2>Router-Analyse</h2>";

// Überprüfe, ob die Router-Klasse bereits einen Basispfad berücksichtigt
if (strpos($router_content, 'basePath') !== false) {
    echo "<p>Die Router-Klasse enthält bereits einen Basispfad-Mechanismus.</p>";
} else {
    echo "<p>Die Router-Klasse enthält keinen Basispfad-Mechanismus. Hier ist ein Vorschlag zur Anpassung:</p>";
    
    echo "<pre>";
    echo "// In der Router-Klasse hinzufügen:
private \$basePath = '';

public function setBasePath(\$path) {
    \$this->basePath = \$path;
}

// In der dispatch-Methode ändern:
public function dispatch(\$uri) {
    // Basispfad entfernen, wenn vorhanden
    if (!empty(\$this->basePath) && strpos(\$uri, \$this->basePath) === 0) {
        \$uri = substr(\$uri, strlen(\$this->basePath));
    }
    
    // Wenn der URI leer ist, setze ihn auf '/'
    if (empty(\$uri)) {
        \$uri = '/';
    }
    
    // Rest der Methode bleibt gleich...
";
    echo "</pre>";
}

// Überprüfe die index.php-Datei
$index_file = __DIR__ . '/public/index.php';
$index_content = file_get_contents($index_file);

echo "<h2>index.php-Analyse</h2>";

// Überprüfe, ob die index.php bereits einen Basispfad setzt
if (strpos($index_content, 'setBasePath') !== false) {
    echo "<p>Die index.php setzt bereits einen Basispfad.</p>";
} else {
    echo "<p>Die index.php setzt keinen Basispfad. Hier ist ein Vorschlag zur Anpassung:</p>";
    
    echo "<pre>";
    echo "// Nach der Zeile \$router = new Router(\$db); hinzufügen:
// Basispfad für die Anwendung setzen (z.B. /expense-manager)
\$basePath = '$base_path';
\$router->setBasePath(\$basePath);
";
    echo "</pre>";
}

// Überprüfe die .htaccess-Datei im Hauptverzeichnis
$htaccess_file = __DIR__ . '/.htaccess';
$htaccess_content = file_get_contents($htaccess_file);

echo "<h2>.htaccess-Analyse</h2>";

// Überprüfe, ob die .htaccess bereits einen Basispfad berücksichtigt
if (strpos($htaccess_content, 'RewriteBase ' . $base_path) !== false) {
    echo "<p>Die .htaccess-Datei enthält bereits den korrekten RewriteBase-Eintrag.</p>";
} else {
    echo "<p>Die .htaccess-Datei enthält nicht den korrekten RewriteBase-Eintrag. Hier ist ein Vorschlag zur Anpassung:</p>";
    
    echo "<pre>";
    echo "RewriteEngine On
RewriteBase $base_path

# Wenn die Anfrage keine existierende Datei oder Verzeichnis betrifft
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Leite alle Anfragen an index.php weiter
RewriteRule ^(.*)$ index.php [QSA,L]
";
    echo "</pre>";
}

// Zeige Links zu anderen Test-Dateien
echo "<h2>Links</h2>";
echo "<ul>";
echo "<li><a href='test-server.php'>Zurück zum Server-Test</a></li>";
echo "<li><a href='public/test.php'>Test im public-Verzeichnis</a></li>";
echo "<li><a href='index.php'>Hauptseite direkt aufrufen</a></li>";
echo "</ul>";

// Biete die Möglichkeit, die Änderungen anzuwenden
echo "<h2>Änderungen anwenden</h2>";
echo "<p>Um die vorgeschlagenen Änderungen anzuwenden, müssen Sie die entsprechenden Dateien manuell bearbeiten oder einen Entwickler kontaktieren.</p>";
echo "<p>Die wichtigsten Änderungen sind:</p>";
echo "<ol>";
echo "<li>Fügen Sie einen Basispfad-Mechanismus zur Router-Klasse hinzu</li>";
echo "<li>Setzen Sie den Basispfad in der index.php</li>";
echo "<li>Aktualisieren Sie die .htaccess-Datei mit dem korrekten RewriteBase-Eintrag</li>";
echo "</ol>"; 