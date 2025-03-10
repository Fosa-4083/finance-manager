<?php
/**
 * Dieses Skript aktualisiert alle absoluten URLs in den View-Dateien,
 * um die Path-Klasse zu verwenden.
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

// Jede Datei durchgehen und URLs aktualisieren
foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // href="/" ersetzen
    $content = preg_replace('/href="\/"/i', 'href="<?php echo \\Utils\\Path::url(\'/\'); ?>"', $content);
    
    // href="/pfad" ersetzen
    $content = preg_replace('/href="\/([a-zA-Z0-9_-]+)"/i', 'href="<?php echo \\Utils\\Path::url(\'/\1\'); ?>"', $content);
    
    // href="/pfad/unterpfad" ersetzen
    $content = preg_replace('/href="\/([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+)"/i', 'href="<?php echo \\Utils\\Path::url(\'/\1/\2\'); ?>"', $content);
    
    // href="/pfad?param=wert" ersetzen
    $content = preg_replace('/href="\/([a-zA-Z0-9_-]+)\?([^"]+)"/i', 'href="<?php echo \\Utils\\Path::url(\'/\1?\2\'); ?>"', $content);
    
    // href="/pfad/unterpfad?param=wert" ersetzen
    $content = preg_replace('/href="\/([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+)\?([^"]+)"/i', 'href="<?php echo \\Utils\\Path::url(\'/\1/\2?\3\'); ?>"', $content);
    
    // action="/" ersetzen
    $content = preg_replace('/action="\/"/i', 'action="<?php echo \\Utils\\Path::url(\'/\'); ?>"', $content);
    
    // action="/pfad" ersetzen
    $content = preg_replace('/action="\/([a-zA-Z0-9_-]+)"/i', 'action="<?php echo \\Utils\\Path::url(\'/\1\'); ?>"', $content);
    
    // action="/pfad/unterpfad" ersetzen
    $content = preg_replace('/action="\/([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+)"/i', 'action="<?php echo \\Utils\\Path::url(\'/\1/\2\'); ?>"', $content);
    
    // action="/pfad?param=wert" ersetzen
    $content = preg_replace('/action="\/([a-zA-Z0-9_-]+)\?([^"]+)"/i', 'action="<?php echo \\Utils\\Path::url(\'/\1?\2\'); ?>"', $content);
    
    // action="/pfad/unterpfad?param=wert" ersetzen
    $content = preg_replace('/action="\/([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+)\?([^"]+)"/i', 'action="<?php echo \\Utils\\Path::url(\'/\1/\2?\3\'); ?>"', $content);
    
    // src="/pfad" ersetzen
    $content = preg_replace('/src="\/([a-zA-Z0-9_-]+)"/i', 'src="<?php echo \\Utils\\Path::url(\'/\1\'); ?>"', $content);
    
    // Wenn Änderungen vorgenommen wurden, Datei aktualisieren
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Aktualisiert: " . $file . "\n";
        $updatedFiles++;
    }
}

echo "Aktualisierte Dateien: " . $updatedFiles . "\n";
echo "Fertig!\n"; 