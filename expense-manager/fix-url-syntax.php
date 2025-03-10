<?php
/**
 * Dieses Skript korrigiert Syntaxfehler in den URL-Aufrufen,
 * wo PHP-Tags innerhalb von PHP-Tags platziert wurden.
 */

// Verzeichnis der Views
$viewsDir = __DIR__ . '/src/Views';

// Funktion zum Durchsuchen eines Verzeichnisses
function scanDirectory($dir) {
    $files = [];
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            $files = array_merge($files, scanDirectory($path));
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $files[] = $path;
        }
    }
    
    return $files;
}

// Alle PHP-Dateien im Views-Verzeichnis finden
$files = scanDirectory($viewsDir);
echo "Gefundene Dateien: " . count($files) . "\n";

// Zähler für aktualisierte Dateien
$updatedFiles = 0;

// Jede Datei durchgehen und URLs korrigieren
foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Muster 1: url('/pfad?id=<?= $var ?>')
    $pattern1 = '/url\([\'"]([^\'"]*)(\?id=)?\<\?=\s*([^\s\'";]+)\s*\?\>([^\'"]*)[\'"]?\)/i';
    $replacement1 = 'url(\'$1$2\' . $3 . \'$4\')';
    $content = preg_replace($pattern1, $replacement1, $content);
    
    // Muster 2: url('/pfad/<?= $var ?>/unterpfad')
    $pattern2 = '/url\([\'"]([^\'"]*)\<\?=\s*([^\s\'";]+)\s*\?\>([^\'"]*)[\'"]?\)/i';
    $replacement2 = 'url(\'$1\' . $2 . \'$3\')';
    $content = preg_replace($pattern2, $replacement2, $content);
    
    // Wenn Änderungen vorgenommen wurden, Datei aktualisieren
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Aktualisiert: " . $file . "\n";
        $updatedFiles++;
    }
}

echo "Aktualisierte Dateien: " . $updatedFiles . "\n";
echo "Fertig!\n"; 