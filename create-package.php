<?php

// Zu packende Verzeichnisse und Dateien
$include = [
    'expense-manager/src',
    'expense-manager/public',
    'expense-manager/database',
    'README.md',
    '.htaccess'
];

// Zu ignorierende Dateien und Verzeichnisse
$exclude = [
    '.git',
    '.gitignore',
    'node_modules',
    '.DS_Store',
    'deploy.php',
    'deploy-secure.php',
    '.env',
    '.env.example',
    'create-package.php'
];

// Erstelle temporäres Verzeichnis
$tempDir = 'temp_package';
if (!file_exists($tempDir)) {
    mkdir($tempDir);
}

// Kopiere Dateien
foreach ($include as $item) {
    if (is_dir($item)) {
        shell_exec("cp -r $item $tempDir/");
    } else {
        shell_exec("cp $item $tempDir/");
    }
}

// Erstelle ZIP-Archiv
$zipFile = 'expense-manager.zip';
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($tempDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($tempDir) + 1);
            
            // Überspringe ausgeschlossene Dateien
            $skip = false;
            foreach ($exclude as $excludeItem) {
                if (strpos($relativePath, $excludeItem) !== false) {
                    $skip = true;
                    break;
                }
            }
            
            if (!$skip) {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    $zip->close();
    echo "ZIP-Archiv $zipFile wurde erfolgreich erstellt.\n";
} else {
    echo "Fehler beim Erstellen des ZIP-Archivs.\n";
}

// Lösche temporäres Verzeichnis
shell_exec("rm -rf $tempDir"); 