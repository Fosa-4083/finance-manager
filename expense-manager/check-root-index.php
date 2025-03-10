<?php
// Cache-Header setzen
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<h1>Überprüfung der index.php im Hauptverzeichnis</h1>";
echo "<p>Generiert am: " . date('Y-m-d H:i:s') . "</p>";

// Pfad zur index.php im Hauptverzeichnis
$rootIndexPath = dirname(__DIR__) . '/index.php';

echo "<h2>Pfad</h2>";
echo "<p>" . htmlspecialchars($rootIndexPath) . "</p>";

// Überprüfe, ob die Datei existiert
if (file_exists($rootIndexPath)) {
    echo "<h2>Datei existiert</h2>";
    
    // Zeige Dateieigenschaften
    echo "<h3>Dateieigenschaften</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Eigenschaft</th><th>Wert</th></tr>";
    echo "<tr><td>Größe</td><td>" . filesize($rootIndexPath) . " Bytes</td></tr>";
    echo "<tr><td>Letzte Änderung</td><td>" . date('Y-m-d H:i:s', filemtime($rootIndexPath)) . "</td></tr>";
    echo "<tr><td>Berechtigungen</td><td>" . substr(sprintf('%o', fileperms($rootIndexPath)), -4) . "</td></tr>";
    echo "<tr><td>MD5-Hash</td><td>" . md5_file($rootIndexPath) . "</td></tr>";
    echo "</table>";
    
    // Zeige Dateiinhalt
    echo "<h3>Dateiinhalt</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents($rootIndexPath)) . "</pre>";
} else {
    echo "<h2>Datei existiert nicht</h2>";
}

// Überprüfe auch die .htaccess im Hauptverzeichnis
$rootHtaccessPath = dirname(__DIR__) . '/.htaccess';

echo "<h2>Überprüfung der .htaccess im Hauptverzeichnis</h2>";
echo "<p>" . htmlspecialchars($rootHtaccessPath) . "</p>";

if (file_exists($rootHtaccessPath)) {
    echo "<h3>Datei existiert</h3>";
    
    // Zeige Dateieigenschaften
    echo "<h3>Dateieigenschaften</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Eigenschaft</th><th>Wert</th></tr>";
    echo "<tr><td>Größe</td><td>" . filesize($rootHtaccessPath) . " Bytes</td></tr>";
    echo "<tr><td>Letzte Änderung</td><td>" . date('Y-m-d H:i:s', filemtime($rootHtaccessPath)) . "</td></tr>";
    echo "<tr><td>Berechtigungen</td><td>" . substr(sprintf('%o', fileperms($rootHtaccessPath)), -4) . "</td></tr>";
    echo "<tr><td>MD5-Hash</td><td>" . md5_file($rootHtaccessPath) . "</td></tr>";
    echo "</table>";
    
    // Zeige Dateiinhalt
    echo "<h3>Dateiinhalt</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents($rootHtaccessPath)) . "</pre>";
} else {
    echo "<h3>Datei existiert nicht</h3>";
}

// Überprüfe, ob es ein altes expense-manager Verzeichnis gibt
$oldExpenseManagerPath = dirname(__DIR__) . '/expense-manager-old';
if (is_dir($oldExpenseManagerPath)) {
    echo "<h2>Altes expense-manager Verzeichnis gefunden</h2>";
    echo "<p>" . htmlspecialchars($oldExpenseManagerPath) . "</p>";
} else {
    echo "<h2>Kein altes expense-manager Verzeichnis gefunden</h2>";
}

// Überprüfe, ob es ein finance-manager Verzeichnis gibt
$financeManagerPath = dirname(__DIR__) . '/finance-manager';
if (is_dir($financeManagerPath)) {
    echo "<h2>finance-manager Verzeichnis gefunden</h2>";
    echo "<p>" . htmlspecialchars($financeManagerPath) . "</p>";
} else {
    echo "<h2>Kein finance-manager Verzeichnis gefunden</h2>";
} 