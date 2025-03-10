<?php
/**
 * Dieses Skript korrigiert alle absoluten URLs in Header-Weiterleitungen,
 * um den Basispfad zu berücksichtigen.
 */

// Controller-Verzeichnis
$controllerDir = __DIR__ . '/src/Controllers';

// Funktion zum Durchsuchen von Dateien im Verzeichnis
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

// Alle PHP-Dateien im Controllers-Verzeichnis finden
$files = scanDirectory($controllerDir);
echo "Gefundene Controller-Dateien: " . count($files) . "\n";

// Füge weitere spezifische Dateien hinzu
$additionalFiles = [
    __DIR__ . '/cleanup.php',
    __DIR__ . '/index.php'
];

foreach ($additionalFiles as $file) {
    if (file_exists($file)) {
        $files[] = $file;
    }
}

// Zähler für aktualisierte Dateien
$updatedFiles = 0;

// Jede Datei durchgehen und die Weiterleitungen korrigieren
foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Pattern 1: header('Location: /path');
    $pattern1 = "/header\('Location: \/([^']*)'\);/i";
    $replacement1 = "header('Location: ' . \\Utils\\Path::url('/$1'));";
    $content = preg_replace($pattern1, $replacement1, $content);
    
    // Pattern 2: header("Location: /path");
    $pattern2 = '/header\("Location: \/([^"]*)"\);/i';
    $replacement2 = 'header("Location: " . \\Utils\\Path::url(\'/$1\'));';
    $content = preg_replace($pattern2, $replacement2, $content);
    
    // Spezialfall für index.php und cleanup.php, die keine Utils\Path-Klasse verwenden
    if (strpos($file, 'index.php') !== false || strpos($file, 'cleanup.php') !== false) {
        $content = str_replace('\\Utils\\Path::url', '', $content);
    }
    
    // Wenn Änderungen vorgenommen wurden, Datei aktualisieren
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Aktualisiert: " . $file . "\n";
        $updatedFiles++;
    }
}

echo "Aktualisierte Dateien: " . $updatedFiles . "\n";
echo "Fertig!\n"; 