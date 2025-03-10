<?php
// Versionsdatei zur Überprüfung der aktuellen Version
echo "<h1>Expense Manager Version</h1>";
echo "<p>Version: 2.0 (Aktualisiert am " . date('Y-m-d H:i:s') . ")</p>";
echo "<p>Diese Datei wurde erstellt, um zu überprüfen, ob die aktuelle Version der Anwendung auf dem Server läuft.</p>";

// Zeige Verzeichnisstruktur
echo "<h2>Verzeichnisstruktur</h2>";
echo "<pre>";
$dir = __DIR__;
echo "Aktuelles Verzeichnis: $dir\n\n";

// Zeige Dateien im aktuellen Verzeichnis
echo "Dateien im aktuellen Verzeichnis:\n";
$files = scandir($dir);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file\n";
    }
}

// Zeige Dateien im public Verzeichnis
echo "\nDateien im public Verzeichnis:\n";
if (is_dir($dir . '/public')) {
    $files = scandir($dir . '/public');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file\n";
        }
    }
} else {
    echo "Verzeichnis public nicht gefunden.\n";
}

// Zeige Dateien im src Verzeichnis
echo "\nDateien im src Verzeichnis:\n";
if (is_dir($dir . '/src')) {
    $files = scandir($dir . '/src');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file\n";
        }
    }
} else {
    echo "Verzeichnis src nicht gefunden.\n";
}

echo "</pre>";

// Zeige Git-Informationen, falls verfügbar
echo "<h2>Git-Informationen</h2>";
echo "<pre>";
if (is_dir($dir . '/.git') || is_dir(dirname($dir) . '/.git')) {
    // Versuche, den aktuellen Git-Branch zu ermitteln
    $gitBranch = shell_exec('cd ' . escapeshellarg($dir) . ' && git branch --show-current 2>&1');
    echo "Aktueller Git-Branch: " . ($gitBranch ? trim($gitBranch) : "Nicht verfügbar") . "\n";
    
    // Versuche, den letzten Commit zu ermitteln
    $gitCommit = shell_exec('cd ' . escapeshellarg($dir) . ' && git log -1 --pretty=format:"%h - %s (%cr)" 2>&1');
    echo "Letzter Commit: " . ($gitCommit ? $gitCommit : "Nicht verfügbar") . "\n";
} else {
    echo "Kein Git-Repository gefunden.\n";
}
echo "</pre>"; 