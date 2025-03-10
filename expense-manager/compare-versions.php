<?php
// Diese Datei vergleicht die lokale Version mit der Version auf dem Server

// Cache-Header setzen
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<h1>Versionsvergleich</h1>";
echo "<p>Durchgeführt am: " . date('Y-m-d H:i:s') . "</p>";

// Eindeutige ID für diese Anfrage
$requestId = uniqid();
echo "<p>Anfrage-ID: $requestId</p>";

// Zeige Informationen über die Anwendung
echo "<h2>Anwendungsinformationen</h2>";
echo "<ul>";
echo "<li>PHP-Version: " . phpversion() . "</li>";
echo "<li>Dokument-Root: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li>Server-Software: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Request-URI: " . $_SERVER['REQUEST_URI'] . "</li>";
echo "</ul>";

// Überprüfe wichtige Dateien
echo "<h2>Wichtige Dateien</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Datei</th><th>Existiert</th><th>Größe</th><th>Letzte Änderung</th><th>Inhalt (erste 100 Zeichen)</th></tr>";

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
    echo "<tr>";
    echo "<td>" . htmlspecialchars($file) . "</td>";
    
    if (file_exists($path)) {
        $size = filesize($path);
        $mtime = filemtime($path);
        $content = file_get_contents($path);
        $preview = substr($content, 0, 100);
        
        echo "<td style='color:green'>Ja</td>";
        echo "<td>" . $size . " Bytes</td>";
        echo "<td>" . date('Y-m-d H:i:s', $mtime) . "</td>";
        echo "<td><pre>" . htmlspecialchars($preview) . "...</pre></td>";
    } else {
        echo "<td style='color:red'>Nein</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

// Überprüfe, ob die Anwendung korrekt funktioniert
echo "<h2>Funktionstest</h2>";
echo "<p>Klicken Sie auf die folgenden Links, um die Anwendung zu testen:</p>";
echo "<ul>";
echo "<li><a href='/expense-manager/?nocache=" . time() . "' target='_blank'>Expense Manager öffnen</a></li>";
echo "<li><a href='/expense-manager/public/?nocache=" . time() . "' target='_blank'>Expense Manager (public) öffnen</a></li>";
echo "</ul>";

// Überprüfe die Datenbankverbindung
echo "<h2>Datenbankverbindung</h2>";
if (file_exists(__DIR__ . '/database/database.sqlite')) {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p style='color:green'>Datenbankverbindung erfolgreich!</p>";
        
        // Zeige Tabellen
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Tabellen in der Datenbank:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>Datenbankfehler: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>Datenbankdatei nicht gefunden.</p>";
}

// Überprüfe die Berechtigungen
echo "<h2>Berechtigungen</h2>";
echo "<p>Berechtigungen für wichtige Dateien und Verzeichnisse:</p>";
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
    
    if (file_exists($fullPath)) {
        $perms = fileperms($fullPath);
        $permsOctal = substr(sprintf('%o', $perms), -4);
        
        echo "<li>" . htmlspecialchars($path) . ": ";
        echo "Berechtigungen = " . $permsOctal;
        
        if (is_readable($fullPath)) {
            echo ", Lesbar";
        } else {
            echo ", <span style='color:red'>Nicht lesbar</span>";
        }
        
        if (is_writable($fullPath)) {
            echo ", Schreibbar";
        } else {
            echo ", <span style='color:red'>Nicht schreibbar</span>";
        }
        
        if (is_dir($fullPath)) {
            echo ", Verzeichnis";
        } else {
            echo ", Datei";
        }
        
        echo "</li>";
    } else {
        echo "<li>" . htmlspecialchars($path) . ": <span style='color:red'>Nicht gefunden</span></li>";
    }
}
echo "</ul>";

// Überprüfe die Umgebungsvariablen
echo "<h2>Umgebungsvariablen</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>"; 