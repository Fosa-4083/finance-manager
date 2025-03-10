<?php
// Debug-Datei zur Überprüfung der Anwendung

echo "<h1>Expense Manager Debug</h1>";
echo "<p>Generiert am: " . date('Y-m-d H:i:s') . "</p>";

// Zeige PHP-Informationen
echo "<h2>PHP-Informationen</h2>";
echo "<p>PHP-Version: " . phpversion() . "</p>";
echo "<p>Dokument-Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Server-Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Request-URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Zeige Dateiinhalte
echo "<h2>Dateiinhalte</h2>";

// Überprüfe index.php
echo "<h3>index.php</h3>";
if (file_exists(__DIR__ . '/index.php')) {
    echo "<pre>" . htmlspecialchars(file_get_contents(__DIR__ . '/index.php')) . "</pre>";
} else {
    echo "<p>Datei nicht gefunden</p>";
}

// Überprüfe public/index.php
echo "<h3>public/index.php (erste 20 Zeilen)</h3>";
if (file_exists(__DIR__ . '/public/index.php')) {
    $content = file_get_contents(__DIR__ . '/public/index.php');
    $lines = explode("\n", $content);
    $first20Lines = array_slice($lines, 0, 20);
    echo "<pre>" . htmlspecialchars(implode("\n", $first20Lines)) . "</pre>";
} else {
    echo "<p>Datei nicht gefunden</p>";
}

// Überprüfe .htaccess
echo "<h3>.htaccess</h3>";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "<pre>" . htmlspecialchars(file_get_contents(__DIR__ . '/.htaccess')) . "</pre>";
} else {
    echo "<p>Datei nicht gefunden</p>";
}

// Überprüfe public/.htaccess
echo "<h3>public/.htaccess</h3>";
if (file_exists(__DIR__ . '/public/.htaccess')) {
    echo "<pre>" . htmlspecialchars(file_get_contents(__DIR__ . '/public/.htaccess')) . "</pre>";
} else {
    echo "<p>Datei nicht gefunden</p>";
}

// Überprüfe Datenbankverbindung
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

// Überprüfe Berechtigungen
echo "<h2>Berechtigungen</h2>";
echo "<p>Berechtigungen für wichtige Dateien und Verzeichnisse:</p>";
echo "<ul>";
$files = [
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

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $path = str_replace('//', '/', $path);
    
    if (file_exists($path)) {
        $perms = fileperms($path);
        $permsOctal = substr(sprintf('%o', $perms), -4);
        
        echo "<li>" . htmlspecialchars($file) . ": ";
        echo "Berechtigungen = " . $permsOctal;
        
        if (is_readable($path)) {
            echo ", Lesbar";
        } else {
            echo ", <span style='color:red'>Nicht lesbar</span>";
        }
        
        if (is_writable($path)) {
            echo ", Schreibbar";
        } else {
            echo ", <span style='color:red'>Nicht schreibbar</span>";
        }
        
        if (is_dir($path)) {
            echo ", Verzeichnis";
        } else {
            echo ", Datei";
        }
        
        echo "</li>";
    } else {
        echo "<li>" . htmlspecialchars($file) . ": <span style='color:red'>Nicht gefunden</span></li>";
    }
}
echo "</ul>";