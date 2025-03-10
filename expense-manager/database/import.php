<?php

// Verbindung zur SQLite-Datenbank herstellen
$sqlite = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Starte Transaktion
    $sqlite->beginTransaction();

    // Deaktiviere Foreign Key Constraints temporär
    $sqlite->exec('PRAGMA foreign_keys = OFF');

    // Lese den MySQL-Dump
    $dump = file_get_contents('/Users/r.strassl/Downloads/strasslinfodb1.sql');
    echo "Dump-Datei gelesen. Größe: " . strlen($dump) . " Bytes\n";
    
    // Kategorien importieren
    echo "Importiere Kategorien...\n";
    
    // Extrahiere den INSERT-Block für Kategorien
    if (preg_match('/INSERT INTO `category` \(`category_id`, `name`, `description`, `color`, `goal`\) VALUES(.*?);/s', $dump, $categoryMatch)) {
        $categoryValues = $categoryMatch[1];
        
        // Extrahiere die einzelnen Werte-Tupel
        preg_match_all('/\((\d+),\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*(\d+)\)/', $categoryValues, $matches, PREG_SET_ORDER);
        
        $count = 0;
        foreach ($matches as $match) {
            $id = $match[1];
            $name = $match[2];
            $description = $match[3];
            $color = $match[4];
            $goal = $match[5];
            
            echo "Füge Kategorie hinzu: ID=$id, Name=$name\n";
            
            // Füge Kategorie in SQLite ein
            $stmt = $sqlite->prepare('INSERT INTO categories (id, name, description, color, goal) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$id, $name, $description, $color, (float)$goal]);
            $count++;
        }
        echo "$count Kategorien importiert.\n";
    } else {
        echo "WARNUNG: Keine Kategorie-Daten gefunden!\n";
    }
    
    // Ausgaben importieren
    echo "Importiere Ausgaben...\n";
    
    // Extrahiere die INSERT-Blöcke für Ausgaben
    preg_match_all('/INSERT INTO `expense` \(`expense_id`, `category_id`, `date`, `description`, `value`, `afa`\) VALUES(.*?);/s', $dump, $expenseMatches);
    
    $count = 0;
    if (!empty($expenseMatches[1])) {
        foreach ($expenseMatches[1] as $expenseValues) {
            // Extrahiere die einzelnen Werte-Tupel
            preg_match_all('/\((\d+),\s*(\d+),\s*\'([^\']*)\',\s*\'([^\']*)\',\s*(-?\d+\.?\d*),\s*(\d+)\)/', $expenseValues, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $id = $match[1];
                $category_id = $match[2];
                $date = $match[3];
                $description = $match[4];
                $value = $match[5];
                $is_subscription = $match[6];
                
                echo "Füge Ausgabe hinzu: ID=$id, Kategorie=$category_id, Wert=$value\n";
                
                // Füge Ausgabe in SQLite ein
                $stmt = $sqlite->prepare('INSERT INTO expenses (id, category_id, date, description, value, afa) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$id, $category_id, $date, $description, (float)$value, (int)$is_subscription]);
                $count++;
            }
        }
        echo "$count Ausgaben importiert.\n";
    } else {
        echo "WARNUNG: Keine Ausgaben-Daten gefunden!\n";
    }
    
    // Ausgabenziele importieren
    echo "Importiere Ausgabenziele...\n";
    
    // Extrahiere den INSERT-Block für Ausgabenziele
    // Da die Tabelle category_expense_goal anders strukturiert ist, erstellen wir die Ausgabenziele manuell
    // basierend auf den Kategorien und ihren Zielen
    
    $stmt = $sqlite->prepare('SELECT id, goal FROM categories');
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = 0;
    $currentYear = date('Y');
    
    foreach ($categories as $category) {
        $category_id = $category['id'];
        $goal = $category['goal'];
        
        echo "Füge Ausgabenziel hinzu: Kategorie=$category_id, Jahr=$currentYear, Ziel=$goal\n";
        
        // Füge Ausgabenziel in SQLite ein
        $stmt = $sqlite->prepare('INSERT INTO expense_goals (category_id, year, goal) VALUES (?, ?, ?)');
        $stmt->execute([$category_id, $currentYear, (float)$goal]);
        $count++;
    }
    
    echo "$count Ausgabenziele importiert.\n";

    // Setze die Auto-Increment-Sequenzen
    $sqlite->exec('UPDATE sqlite_sequence SET seq = (SELECT MAX(id) FROM categories) WHERE name = "categories"');
    $sqlite->exec('UPDATE sqlite_sequence SET seq = (SELECT MAX(id) FROM expenses) WHERE name = "expenses"');
    $sqlite->exec('UPDATE sqlite_sequence SET seq = (SELECT MAX(id) FROM expense_goals) WHERE name = "expense_goals"');

    // Aktiviere Foreign Key Constraints wieder
    $sqlite->exec('PRAGMA foreign_keys = ON');

    // Commit Transaktion
    $sqlite->commit();
    echo "Daten erfolgreich importiert!\n";

} catch (Exception $e) {
    // Bei Fehler: Rollback
    $sqlite->rollBack();
    echo "Fehler beim Import: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
} 