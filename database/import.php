<?php
// Verbindung zur SQLite-Datenbank herstellen
try {
    $db = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Fehler bei der Verbindung zur Datenbank: " . $e->getMessage());
}

// SQL-Dump-Datei lesen
$sqlDump = file_get_contents('/Users/r.strassl/Downloads/strasslinfodb1.sql');

// Debug-Ausgabe
echo "SQL-Dump geladen: " . strlen($sqlDump) . " Bytes\n";

// Kategorien importieren
$categoryPattern = '/INSERT INTO `category` \(`category_id`, `name`, `description`, `color`, `goal`\) VALUES\s*\n(.*?);/s';
preg_match($categoryPattern, $sqlDump, $categoryMatches);

if (isset($categoryMatches[1])) {
    $categoryValues = $categoryMatches[1];
    echo "Gefundene Kategorie-Inserts: " . substr($categoryValues, 0, 100) . "...\n";
    
    // Manuelles Parsen der Kategorien
    $categories = [];
    $lines = explode("\n", $categoryValues);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Prüfen, ob die Zeile mit einer Klammer beginnt (ein Datensatz)
        if (strpos($line, '(') === 0) {
            // Entferne Klammern und Komma am Ende
            $line = rtrim($line, ',');
            $line = trim($line, '()');
            
            // Extrahiere die Werte
            preg_match('/(\d+),\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*(\d+)/', $line, $matches);
            
            if (count($matches) >= 6) {
                $categories[] = [
                    'id' => $matches[1],
                    'name' => $matches[2],
                    'description' => $matches[3],
                    'color' => $matches[4],
                    'goal' => $matches[5]
                ];
            }
        }
    }
    
    echo "Anzahl gefundener Kategorien: " . count($categories) . "\n";
    if (count($categories) > 0) {
        echo "Erste Kategorie: " . print_r($categories[0], true) . "\n";
    }
    
    // Kategorien in die Datenbank einfügen
    foreach ($categories as $category) {
        // Prüfen, ob die Kategorie bereits existiert
        $stmt = $db->prepare("SELECT id FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $category['id']);
        $stmt->execute();
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Kategorie mit ID " . $category['id'] . " existiert bereits.\n";
        } else {
            $stmt = $db->prepare("INSERT INTO categories (id, name, description, color, goal) VALUES (:id, :name, :description, :color, :goal)");
            $stmt->bindParam(':id', $category['id']);
            $stmt->bindParam(':name', $category['name']);
            $stmt->bindParam(':description', $category['description']);
            $stmt->bindParam(':color', $category['color']);
            $stmt->bindParam(':goal', $category['goal']);
            $stmt->execute();
            
            echo "Kategorie hinzugefügt: ID=" . $category['id'] . ", Name=" . $category['name'] . "\n";
        }
    }
} else {
    echo "Keine Kategorien im SQL-Dump gefunden.\n";
}

// Ausgaben importieren
$expensePattern = '/INSERT INTO `expense` \(`expense_id`, `category_id`, `date`, `description`, `value`, `afa`\) VALUES\s*\n(.*?);/s';
if (preg_match($expensePattern, $sqlDump, $expenseMatches)) {
    $expenseValues = $expenseMatches[1];
    echo "Gefundene Ausgaben-Werte: " . substr($expenseValues, 0, 100) . "...\n";
    
    // Manuelles Parsen der Ausgaben, da die Regex-Muster nicht zuverlässig funktionieren
    $lines = explode("\n", $expenseValues);
    $expenses = [];
    
    foreach ($lines as $line) {
        // Zeilen mit Ausgabendaten enthalten Klammern
        if (strpos($line, '(') !== false) {
            // Entferne Klammern und Komma am Ende
            $line = trim($line);
            if (substr($line, -1) == ',') {
                $line = substr($line, 0, -1);
            }
            $line = trim($line, '(),');
            
            // Teile die Zeile in Werte auf
            $parts = [];
            $current = '';
            $inQuote = false;
            
            for ($i = 0; $i < strlen($line); $i++) {
                $char = $line[$i];
                
                if ($char == "'" && ($i == 0 || $line[$i-1] != '\\')) {
                    $inQuote = !$inQuote;
                    $current .= $char;
                } else if ($char == ',' && !$inQuote) {
                    $parts[] = trim($current);
                    $current = '';
                } else {
                    $current .= $char;
                }
            }
            
            if (!empty($current)) {
                $parts[] = trim($current);
            }
            
            if (count($parts) == 6) {
                $expenseId = trim($parts[0]);
                $categoryId = trim($parts[1]);
                $date = trim($parts[2], "'");
                $description = trim($parts[3], "'");
                $value = trim($parts[4]);
                $isSubscription = trim($parts[5]); // afa wird als is_subscription verwendet
                
                $expenses[] = [
                    'id' => $expenseId,
                    'category_id' => $categoryId,
                    'date' => $date,
                    'description' => $description,
                    'value' => $value,
                    'is_subscription' => $isSubscription
                ];
            }
        }
    }
    
    echo "Gefundene Ausgaben: " . count($expenses) . "\n";
    
    // Debug: Erste Ausgabe anzeigen
    if (count($expenses) > 0) {
        echo "Erste Ausgabe: " . print_r($expenses[0], true) . "\n";
    }
    
    foreach ($expenses as $expense) {
        $expenseId = $expense['id'];
        $categoryId = $expense['category_id'];
        $date = $expense['date'];
        $description = $expense['description'];
        $value = $expense['value'];
        $isSubscription = $expense['is_subscription'];
        
        // Prüfen, ob die Ausgabe bereits existiert
        $stmt = $db->prepare("SELECT id FROM expenses WHERE id = ?");
        $stmt->execute([$expenseId]);
        $existingExpense = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingExpense) {
            // Ausgabe einfügen
            $stmt = $db->prepare("INSERT INTO expenses (id, category_id, date, description, value, is_subscription) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$expenseId, $categoryId, $date, $description, $value, $isSubscription]);
            echo "Füge Ausgabe hinzu: ID=$expenseId, Kategorie=$categoryId, Wert=$value\n";
        } else {
            echo "Ausgabe existiert bereits: ID=$expenseId\n";
        }
    }
    
    echo "Buchungen importiert.\n";
} else {
    echo "Keine Ausgaben im SQL-Dump gefunden.\n";
}

// Ausgabenziele importieren
$goalPattern = '/INSERT INTO `category_expense_goal` \(`category_id`, `year`, `goal`\) VALUES\s*\n(.*?);/s';
if (preg_match($goalPattern, $sqlDump, $goalMatches)) {
    $goalValues = $goalMatches[1];
    echo "Gefundene Ausgabenziel-Inserts: " . count($goalMatches) . "\n";
    echo "Verarbeite Ausgabenziel: " . $goalValues . "\n";
    
    // Einzelne Ziele extrahieren mit verbesserter Regex
    $goalItemPattern = '/\((\d+),\s*(\d+),\s*([-\d]+)\)/';
    preg_match_all($goalItemPattern, $goalValues, $goals, PREG_SET_ORDER);
    
    echo "Gefundene Ausgabenziele: " . count($goals) . "\n";
    
    foreach ($goals as $goal) {
        $categoryId = $goal[1];
        $year = $goal[2];
        $goalValue = $goal[3];
        
        // Prüfen, ob das Ziel bereits existiert
        $stmt = $db->prepare("SELECT id FROM expense_goals WHERE category_id = ? AND year = ?");
        $stmt->execute([$categoryId, $year]);
        $existingGoal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingGoal) {
            // Ziel einfügen
            $stmt = $db->prepare("INSERT INTO expense_goals (category_id, year, goal) VALUES (?, ?, ?)");
            $stmt->execute([$categoryId, $year, $goalValue]);
            echo "Füge Ausgabenziel hinzu: Kategorie=$categoryId, Jahr=$year, Ziel=$goalValue\n";
        } else {
            echo "Ausgabenziel existiert bereits: Kategorie=$categoryId, Jahr=$year\n";
        }
    }
    
    echo "Ausgabenziele importiert.\n";
} else {
    echo "Keine Ausgabenziele im SQL-Dump gefunden.\n";
    
    // Da keine Ausgabenziele im SQL-Dump gefunden wurden, extrahieren wir sie aus den Kategorien
    // und erstellen Ausgabenziele für das aktuelle Jahr
    $currentYear = date('Y');
    
    // Alle Kategorien abrufen
    $stmt = $db->prepare("SELECT id, goal FROM categories WHERE goal > 0");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Erstelle Ausgabenziele aus Kategorien für das Jahr $currentYear\n";
    
    foreach ($categories as $category) {
        $categoryId = $category['id'];
        $goalValue = $category['goal'];
        
        // Prüfen, ob das Ziel bereits existiert
        $stmt = $db->prepare("SELECT id FROM expense_goals WHERE category_id = ? AND year = ?");
        $stmt->execute([$categoryId, $currentYear]);
        $existingGoal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingGoal) {
            // Ziel einfügen
            $stmt = $db->prepare("INSERT INTO expense_goals (category_id, year, goal) VALUES (?, ?, ?)");
            $stmt->execute([$categoryId, $currentYear, $goalValue]);
            echo "Füge Ausgabenziel hinzu: Kategorie=$categoryId, Jahr=$currentYear, Ziel=$goalValue\n";
        } else {
            echo "Ausgabenziel existiert bereits: Kategorie=$categoryId, Jahr=$currentYear\n";
        }
    }
    
    echo "Ausgabenziele aus Kategorien importiert.\n";
}

echo "Daten erfolgreich importiert!\n"; 