<?php
// Diese Datei erzwingt eine Aktualisierung der Anwendung

// Cache-Header setzen, um sicherzustellen, dass die Seite nicht gecacht wird
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Opcache leeren, falls aktiviert
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Aktuelle Zeit für eindeutige URL-Parameter
$timestamp = time();

echo "<h1>Erzwungene Aktualisierung</h1>";
echo "<p>Durchgeführt am: " . date('Y-m-d H:i:s') . "</p>";

// Überprüfe, ob die Dateien existieren
echo "<h2>Dateiüberprüfung</h2>";
echo "<ul>";
$files = [
    'index.php',
    'public/index.php',
    '.htaccess',
    'public/.htaccess',
    'database/database.sqlite'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $mtime = filemtime($path);
        echo "<li>" . htmlspecialchars($file) . ": Vorhanden, zuletzt geändert am " . date('Y-m-d H:i:s', $mtime) . "</li>";
    } else {
        echo "<li>" . htmlspecialchars($file) . ": <span style='color:red'>Nicht gefunden</span></li>";
    }
}
echo "</ul>";

// Erstelle eine temporäre Datei, um zu zeigen, dass Schreibzugriff funktioniert
$tempFile = __DIR__ . '/temp_' . $timestamp . '.txt';
$success = file_put_contents($tempFile, "Test-Datei erstellt am " . date('Y-m-d H:i:s'));

if ($success) {
    echo "<p style='color:green'>Test-Datei erfolgreich erstellt: " . htmlspecialchars(basename($tempFile)) . "</p>";
    // Lösche die temporäre Datei wieder
    unlink($tempFile);
    echo "<p>Test-Datei wieder gelöscht.</p>";
} else {
    echo "<p style='color:red'>Konnte keine Test-Datei erstellen. Möglicherweise fehlen Schreibrechte.</p>";
}

// Links zur Anwendung mit Cache-Busting-Parameter
echo "<h2>Links zur Anwendung</h2>";
echo "<p>Klicken Sie auf einen der folgenden Links, um die Anwendung mit einem Cache-Busting-Parameter zu öffnen:</p>";
echo "<ul>";
echo "<li><a href='/expense-manager/?nocache=" . $timestamp . "' target='_blank'>Expense Manager öffnen</a></li>";
echo "<li><a href='/expense-manager/public/?nocache=" . $timestamp . "' target='_blank'>Expense Manager (public) öffnen</a></li>";
echo "</ul>";

// Anweisungen zum manuellen Cache-Leeren
echo "<h2>Browser-Cache leeren</h2>";
echo "<p>Wenn die Anwendung immer noch nicht korrekt angezeigt wird, leeren Sie bitte den Browser-Cache:</p>";
echo "<ul>";
echo "<li><strong>Chrome:</strong> Strg+Shift+Entf (Windows/Linux) oder Cmd+Shift+Entf (Mac)</li>";
echo "<li><strong>Firefox:</strong> Strg+Shift+Entf (Windows/Linux) oder Cmd+Shift+Entf (Mac)</li>";
echo "<li><strong>Safari:</strong> Cmd+Option+E</li>";
echo "<li><strong>Edge:</strong> Strg+Shift+Entf</li>";
echo "</ul>";

// Anweisungen zum Neustarten des Webservers
echo "<h2>Server-Cache leeren</h2>";
echo "<p>Wenn die Anwendung immer noch nicht korrekt angezeigt wird, könnte es sein, dass der Server einen Cache verwendet. In diesem Fall müsste der Webserver neu gestartet werden:</p>";
echo "<pre>sudo service apache2 restart</pre>";
echo "<p>Dies erfordert Administratorrechte auf dem Server.</p>";

// Anweisungen zum manuellen Deployment
echo "<h2>Manuelles Deployment</h2>";
echo "<p>Wenn alle anderen Methoden fehlschlagen, können Sie die Anwendung manuell über FTP auf den Server hochladen:</p>";
echo "<ol>";
echo "<li>Laden Sie die Dateien aus dem GitHub-Repository herunter</li>";
echo "<li>Laden Sie die Dateien über FTP in das Verzeichnis <code>/var/www/vhosts/strassl.info/httpdocs/expense-manager</code> hoch</li>";
echo "<li>Stellen Sie sicher, dass die Berechtigungen korrekt gesetzt sind</li>";
echo "</ol>";

// Zeige PHP-Informationen
echo "<h2>PHP-Informationen</h2>";
echo "<p>PHP-Version: " . phpversion() . "</p>";
echo "<p>Dokument-Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Server-Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Request-URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Zeige Opcache-Status, falls aktiviert
if (function_exists('opcache_get_status')) {
    echo "<h2>Opcache-Status</h2>";
    echo "<pre>";
    print_r(opcache_get_status(false));
    echo "</pre>";
} 