<?php
/**
 * Aufräum-Skript für den Live-Server
 * 
 * Dieses Skript benennt Verzeichnisse um, anstatt sie zu löschen,
 * um bei Problemen leicht zurückwechseln zu können.
 */

// Sicherheitsabfrage
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo "<h1>Sicherheitsabfrage</h1>";
    echo "<p>Dieses Skript wird Verzeichnisse auf dem Server umbenennen.</p>";
    echo "<p>Stellen Sie sicher, dass Sie ein Backup haben, bevor Sie fortfahren.</p>";
    echo "<p><a href='?confirm=yes' style='color: red; font-weight: bold;'>Ich habe ein Backup und möchte fortfahren</a></p>";
    exit;
}

// Cache-Header setzen
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Zeitstempel für Backup-Namen
$timestamp = date('Ymd_His');

// Protokoll starten
$log = [];
$log[] = "Aufräum-Skript gestartet am " . date('Y-m-d H:i:s');

// Funktion zum Umbenennen von Verzeichnissen/Dateien
function safeRename($source, $dest) {
    global $log;
    
    if (file_exists($source)) {
        if (rename($source, $dest)) {
            $log[] = "Erfolgreich umbenannt: $source -> $dest";
            return true;
        } else {
            $log[] = "FEHLER beim Umbenennen: $source -> $dest";
            return false;
        }
    } else {
        $log[] = "Datei/Verzeichnis existiert nicht: $source";
        return false;
    }
}

// Funktion zum Erstellen einer Datei
function createFile($path, $content) {
    global $log;
    
    if (file_put_contents($path, $content)) {
        $log[] = "Datei erstellt: $path";
        return true;
    } else {
        $log[] = "FEHLER beim Erstellen der Datei: $path";
        return false;
    }
}

// 1. Backup der index.php im Hauptverzeichnis
$rootDir = dirname(__DIR__);
$rootIndexPath = $rootDir . '/index.php';
$rootIndexBackup = $rootDir . '/index.php.bak_' . $timestamp;

if (file_exists($rootIndexPath)) {
    safeRename($rootIndexPath, $rootIndexBackup);
}

// 2. Backup der .htaccess im Hauptverzeichnis
$rootHtaccessPath = $rootDir . '/.htaccess';
$rootHtaccessBackup = $rootDir . '/.htaccess.bak_' . $timestamp;

if (file_exists($rootHtaccessPath)) {
    safeRename($rootHtaccessPath, $rootHtaccessBackup);
}

// 3. Neue index.php im Hauptverzeichnis erstellen, die zur expense-manager Anwendung weiterleitet
$newIndexContent = <<<'EOT'
<?php
// Weiterleitung zur expense-manager Anwendung
header('Location: /expense-manager/');
exit;
EOT;

createFile($rootIndexPath, $newIndexContent);

// 4. Neue .htaccess im Hauptverzeichnis erstellen
$newHtaccessContent = <<<'EOT'
Options +FollowSymLinks
RewriteEngine On

# Wenn die Anfrage direkt an das Hauptverzeichnis geht
RewriteCond %{REQUEST_URI} ^/$
RewriteRule ^$ /expense-manager/ [L,R=301]
EOT;

createFile($rootHtaccessPath, $newHtaccessContent);

// 5. Überprüfen, ob es ein altes expense-manager Verzeichnis gibt
$oldExpenseManagerPath = $rootDir . '/expense-manager-old';
if (is_dir($oldExpenseManagerPath)) {
    $log[] = "Altes expense-manager-old Verzeichnis gefunden. Wird umbenannt...";
    safeRename($oldExpenseManagerPath, $oldExpenseManagerPath . '_' . $timestamp);
}

// 6. Überprüfen, ob es ein finance-manager Verzeichnis gibt
$financeManagerPath = $rootDir . '/finance-manager';
if (is_dir($financeManagerPath)) {
    $log[] = "finance-manager Verzeichnis gefunden. Wird umbenannt...";
    safeRename($financeManagerPath, $financeManagerPath . '_' . $timestamp);
}

// Protokoll anzeigen
echo "<h1>Aufräum-Skript</h1>";
echo "<p>Ausgeführt am: " . date('Y-m-d H:i:s') . "</p>";

echo "<h2>Protokoll</h2>";
echo "<pre>";
foreach ($log as $entry) {
    echo htmlspecialchars($entry) . "\n";
}
echo "</pre>";

echo "<h2>Nächste Schritte</h2>";
echo "<p>Die Verzeichnisse wurden umbenannt und neue Dateien erstellt.</p>";
echo "<p>Überprüfen Sie, ob die Anwendung jetzt korrekt funktioniert:</p>";
echo "<ul>";
echo "<li><a href='/' target='_blank'>Hauptseite</a> (sollte zur expense-manager Anwendung weiterleiten)</li>";
echo "<li><a href='/expense-manager/' target='_blank'>expense-manager</a> (sollte die aktuelle Version anzeigen)</li>";
echo "</ul>";

echo "<h2>Wiederherstellung</h2>";
echo "<p>Wenn etwas schief geht, können Sie die Backups wiederherstellen:</p>";
echo "<pre>";
echo "mv $rootIndexBackup $rootIndexPath\n";
echo "mv $rootHtaccessBackup $rootHtaccessPath\n";
echo "</pre>";

// Fertig
$log[] = "Aufräum-Skript beendet am " . date('Y-m-d H:i:s'); 