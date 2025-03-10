<?php
// Testdatei für die Vorschlagsfunktion

// Konfiguration laden
require_once __DIR__ . '/config/config.php';

// Klassen laden
require_once __DIR__ . '/src/Models/Expense.php';
require_once __DIR__ . '/src/Controllers/ExpenseController.php';
require_once __DIR__ . '/src/Utils/Path.php';

use Utils\Path;

// Ausgabe für Debugging
header('Content-Type: text/html; charset=utf-8');
echo "<h1>Test der Vorschlagsfunktion</h1>";

// Datenbank initialisieren
try {
    $db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Datenbankverbindung erfolgreich hergestellt.</p>";
} catch (PDOException $e) {
    echo "<p>Datenbankfehler: " . $e->getMessage() . "</p>";
    exit;
}

// ExpenseController initialisieren
$controller = new Controllers\ExpenseController($db);

// Test für Beschreibungsvorschläge
echo "<h2>Test für Beschreibungsvorschläge</h2>";
echo "<p>Suche nach 'test':</p>";

// Direkte SQL-Abfrage für Beschreibungsvorschläge
$query = '%test%';
$sql = "SELECT DISTINCT description, value, category_id FROM expenses WHERE description LIKE ? ORDER BY date DESC LIMIT 8";
$stmt = $db->prepare($sql);
$stmt->execute([$query]);
$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Anzahl der gefundenen Vorschläge: " . count($suggestions) . "</p>";

if (count($suggestions) > 0) {
    echo "<ul>";
    foreach ($suggestions as $suggestion) {
        echo "<li>" . htmlspecialchars($suggestion['description']) . " (" . abs($suggestion['value']) . " €)</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Keine Vorschläge gefunden. Probieren wir eine allgemeinere Suche.</p>";
    
    // Allgemeinere Suche, um zu sehen, ob überhaupt Daten vorhanden sind
    $sql = "SELECT DISTINCT description, value, category_id FROM expenses ORDER BY date DESC LIMIT 8";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Anzahl allgemeiner Buchungen: " . count($suggestions) . "</p>";
    
    if (count($suggestions) > 0) {
        echo "<ul>";
        foreach ($suggestions as $suggestion) {
            echo "<li>" . htmlspecialchars($suggestion['description']) . " (" . abs($suggestion['value']) . " €)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Keine Buchungen in der Datenbank gefunden. Es müssen erst Buchungen angelegt werden, damit Vorschläge angezeigt werden können.</p>";
    }
}

// Tabellen-Struktur anzeigen
echo "<h2>Datenbankstruktur</h2>";
$sql = "SELECT name FROM sqlite_master WHERE type='table';";
$stmt = $db->prepare($sql);
$stmt->execute();
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "<p>Tabellen in der Datenbank:</p>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li>" . htmlspecialchars($table) . "</li>";
}
echo "</ul>";

// Anzahl der Einträge in der expenses-Tabelle
$sql = "SELECT COUNT(*) FROM expenses;";
$stmt = $db->prepare($sql);
$stmt->execute();
$count = $stmt->fetchColumn();

echo "<p>Anzahl der Einträge in der expenses-Tabelle: " . $count . "</p>";

// Prüfen, ob die Tabelle eine Spalte 'description' hat
$sql = "PRAGMA table_info(expenses);";
$stmt = $db->prepare($sql);
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Spalten in der expenses-Tabelle:</p>";
echo "<ul>";
foreach ($columns as $column) {
    echo "<li>" . htmlspecialchars($column['name']) . " (" . htmlspecialchars($column['type']) . ")</li>";
}
echo "</ul>";

echo "<p><a href='public/index.php'>Zurück zur Anwendung</a></p>"; 