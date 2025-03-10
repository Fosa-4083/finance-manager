<?php
// Diese Datei testet, welche Dateien tatsächlich geladen werden

// Cache-Header setzen
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<h1>Datei-Ladetest</h1>";
echo "<p>Durchgeführt am: " . date('Y-m-d H:i:s') . "</p>";

// Eindeutige ID für diese Anfrage
$requestId = uniqid();
echo "<p>Anfrage-ID: $requestId</p>";

// Erstelle temporäre Testdateien mit eindeutigen Inhalten
$mainIndexContent = "<?php\n// Testdatei für index.php\n// Anfrage-ID: $requestId\n// Erstellt am: " . date('Y-m-d H:i:s') . "\n\necho '<!-- MAIN-INDEX: $requestId -->';\nrequire_once __DIR__ . '/public/index.php';";
$publicIndexContent = "<?php\n// Testdatei für public/index.php\n// Anfrage-ID: $requestId\n// Erstellt am: " . date('Y-m-d H:i:s') . "\n\necho '<!-- PUBLIC-INDEX: $requestId -->';\n\n// Weiterleitung zur ursprünglichen Datei\nrequire_once __DIR__ . '/index.php.original';";

// Sichere die ursprünglichen Dateien
if (file_exists(__DIR__ . '/index.php')) {
    copy(__DIR__ . '/index.php', __DIR__ . '/index.php.original');
    echo "<p>Original index.php gesichert als index.php.original</p>";
}

if (file_exists(__DIR__ . '/public/index.php')) {
    copy(__DIR__ . '/public/index.php', __DIR__ . '/public/index.php.original');
    echo "<p>Original public/index.php gesichert als public/index.php.original</p>";
}

// Schreibe die Testdateien
file_put_contents(__DIR__ . '/index.php', $mainIndexContent);
file_put_contents(__DIR__ . '/public/index.php', $publicIndexContent);

echo "<p>Testdateien wurden erstellt.</p>";

// Erstelle eine Wiederherstellungsdatei
$restoreContent = "<?php\n// Diese Datei stellt die ursprünglichen Dateien wieder her\n\n";
$restoreContent .= "if (file_exists(__DIR__ . '/index.php.original')) {\n";
$restoreContent .= "    copy(__DIR__ . '/index.php.original', __DIR__ . '/index.php');\n";
$restoreContent .= "    unlink(__DIR__ . '/index.php.original');\n";
$restoreContent .= "    echo \"<p>index.php wiederhergestellt</p>\";\n";
$restoreContent .= "}\n\n";
$restoreContent .= "if (file_exists(__DIR__ . '/public/index.php.original')) {\n";
$restoreContent .= "    copy(__DIR__ . '/public/index.php.original', __DIR__ . '/public/index.php');\n";
$restoreContent .= "    unlink(__DIR__ . '/public/index.php.original');\n";
$restoreContent .= "    echo \"<p>public/index.php wiederhergestellt</p>\";\n";
$restoreContent .= "}\n\n";
$restoreContent .= "echo \"<p>Alle Dateien wurden wiederhergestellt.</p>\";\n";
$restoreContent .= "echo \"<p><a href='/expense-manager/?nocache=\" . time() . \"'>Zurück zur Anwendung</a></p>\";\n";

file_put_contents(__DIR__ . '/restore.php', $restoreContent);

echo "<p>Wiederherstellungsdatei wurde erstellt: <a href='/expense-manager/restore.php'>restore.php</a></p>";

// Anweisungen
echo "<h2>Anweisungen</h2>";
echo "<p>Bitte führen Sie die folgenden Schritte aus:</p>";
echo "<ol>";
echo "<li>Öffnen Sie die Anwendung mit diesem Link: <a href='/expense-manager/?nocache=" . time() . "' target='_blank'>Expense Manager öffnen</a></li>";
echo "<li>Überprüfen Sie den Quellcode der Seite (Rechtsklick -> 'Seitenquelltext anzeigen')</li>";
echo "<li>Suchen Sie nach den Kommentaren <code>MAIN-INDEX: $requestId</code> und <code>PUBLIC-INDEX: $requestId</code></li>";
echo "<li>Wenn Sie diese Kommentare finden, werden die neuen Dateien geladen</li>";
echo "<li>Wenn Sie diese Kommentare nicht finden, werden möglicherweise gecachte Dateien oder Dateien aus einem anderen Verzeichnis geladen</li>";
echo "<li>Nachdem Sie den Test abgeschlossen haben, stellen Sie die ursprünglichen Dateien wieder her: <a href='/expense-manager/restore.php'>Dateien wiederherstellen</a></li>";
echo "</ol>";

echo "<p><strong>Wichtig:</strong> Vergessen Sie nicht, die ursprünglichen Dateien wiederherzustellen, nachdem Sie den Test abgeschlossen haben!</p>";

// Weitere Informationen
echo "<h2>Weitere Informationen</h2>";
echo "<p>Wenn die Testdateien nicht geladen werden, könnte es folgende Gründe haben:</p>";
echo "<ul>";
echo "<li>Der Server verwendet einen Caching-Mechanismus wie Opcache</li>";
echo "<li>Die Anwendung lädt Dateien aus einem anderen Verzeichnis</li>";
echo "<li>Die .htaccess-Dateien funktionieren nicht korrekt</li>";
echo "</ul>";

echo "<p>In diesem Fall könnten folgende Maßnahmen helfen:</p>";
echo "<ul>";
echo "<li>Neustarten des Webservers: <code>sudo service apache2 restart</code></li>";
echo "<li>Überprüfen der Apache-Konfiguration: <code>sudo apachectl -t</code></li>";
echo "<li>Überprüfen, ob mod_rewrite aktiviert ist: <code>sudo a2enmod rewrite</code></li>";
echo "<li>Überprüfen, ob AllowOverride All in der Apache-Konfiguration gesetzt ist</li>";
echo "</ul>"; 