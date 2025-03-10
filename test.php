<?php
// Einfache Testdatei zur Überprüfung der PHP-Umgebung

echo "<h1>PHP-Umgebungstest</h1>";
echo "<p>PHP-Version: " . phpversion() . "</p>";
echo "<p>Serverzeit: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Dokumentwurzel: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Servername: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

echo "<h2>Verzeichnisstruktur</h2>";
echo "<pre>";
$dir = __DIR__;
echo "Aktuelles Verzeichnis: $dir\n\n";
echo "Inhalt des aktuellen Verzeichnisses:\n";
$files = scandir($dir);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file\n";
    }
}

echo "\n\nInhalt des expense-manager Verzeichnisses (falls vorhanden):\n";
if (is_dir($dir . '/expense-manager')) {
    $files = scandir($dir . '/expense-manager');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file\n";
        }
    }
} else {
    echo "Verzeichnis expense-manager nicht gefunden.\n";
}

echo "</pre>";

echo "<h2>PHP-Module</h2>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";

echo "<h2>SQLite-Test</h2>";
if (extension_loaded('sqlite3')) {
    echo "<p>SQLite3-Erweiterung ist geladen.</p>";
    
    // Teste Datenbankzugriff
    echo "<p>Teste Datenbankzugriff:</p>";
    if (file_exists($dir . '/expense-manager/database/database.sqlite')) {
        try {
            $db = new PDO('sqlite:' . $dir . '/expense-manager/database/database.sqlite');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p style='color:green'>Datenbankverbindung erfolgreich!</p>";
            
            // Teste Abfrage
            $stmt = $db->query("SELECT COUNT(*) FROM sqlite_master");
            $count = $stmt->fetchColumn();
            echo "<p>Anzahl der Tabellen: $count</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>Datenbankfehler: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red'>Datenbankdatei nicht gefunden.</p>";
    }
} else {
    echo "<p style='color:red'>SQLite3-Erweiterung ist nicht geladen!</p>";
} 