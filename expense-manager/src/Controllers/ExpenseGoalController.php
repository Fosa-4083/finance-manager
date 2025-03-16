<?php

namespace Controllers;

use Models\ExpenseGoal;
use PDO;

class ExpenseGoalController extends BaseController {
    public function __construct($db = null) {
        // Basisklassen-Konstruktor aufrufen
        parent::__construct($db);
    }

    public function index() {
        // Jahr-Filter aus der URL holen oder aktuelles Jahr als Standard verwenden
        $selectedYear = isset($_GET['year']) ? $_GET['year'] : null;
        
        // Alle verfügbaren Jahre für den Filter laden
        $stmtYears = $this->db->query('
            SELECT DISTINCT year 
            FROM expense_goals 
            ORDER BY year DESC
        ');
        $availableYears = $stmtYears->fetchAll(PDO::FETCH_COLUMN);
        
        // Wenn keine Jahre gefunden wurden, aktuelles Jahr als Standard verwenden
        if (empty($availableYears)) {
            $availableYears[] = date('Y');
        }
        
        // Benutzer-ID aus der Session holen
        $userId = $this->session->getUserId();
        
        // Alle Kategorien abrufen
        $stmtCategories = $this->db->prepare('
            SELECT c.id, c.name, c.color, c.type 
            FROM categories c 
            WHERE (c.user_id = ? OR c.user_id IS NULL)
            ORDER BY c.type, c.name
        ');
        $stmtCategories->execute([$userId]);
        $allCategories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
        
        // SQL-Abfrage für Ausgabenziele
        $sql = '
            SELECT 
                eg.id,
                eg.category_id,
                eg.year,
                eg.goal,
                c.name as category_name,
                c.color,
                c.type as category_type,
                COALESCE(SUM(CASE 
                    WHEN DATE_FORMAT(e.date, "%Y") = eg.year AND (eg.year < DATE_FORMAT(NOW(), "%Y") OR (eg.year = DATE_FORMAT(NOW(), "%Y") AND DAYOFYEAR(e.date) <= DAYOFYEAR(NOW()))
                    ) THEN e.value 
                    ELSE 0 
                END), 0) as current_value,
                COALESCE(SUM(CASE 
                    WHEN DATE_FORMAT(e.date, "%Y") = eg.year THEN e.value 
                    ELSE 0 
                END), 0) as total_value
            FROM expense_goals eg
            JOIN categories c ON eg.category_id = c.id
            LEFT JOIN expenses e ON e.category_id = c.id 
                AND DATE_FORMAT(e.date, "%Y") = eg.year
        ';
        
        // Filter nach Jahr hinzufügen, wenn ausgewählt
        $params = [];
        if ($selectedYear) {
            $sql .= ' WHERE eg.year = ?';
            $params[] = $selectedYear;
        }
        
        // Gruppierung und Sortierung
        $sql .= '
            GROUP BY eg.id
            ORDER BY eg.year DESC, c.type, c.name
        ';
        
        // Abfrage ausführen
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $expenseGoals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ausgabenziele nach Jahren gruppieren für die Anzeige
        $goalsByYear = [];
        foreach ($expenseGoals as $goal) {
            // Stellen Sie sicher, dass alle erforderlichen Schlüssel vorhanden sind
            $goal['goal'] = isset($goal['goal']) ? (float)$goal['goal'] : 0;
            $goal['current_value'] = isset($goal['current_value']) ? (float)$goal['current_value'] : 0;
            $goal['total_value'] = isset($goal['total_value']) ? (float)$goal['total_value'] : 0;
            $goal['color'] = isset($goal['color']) ? $goal['color'] : '#cccccc';
            $goal['category_name'] = isset($goal['category_name']) ? $goal['category_name'] : 'Unbekannt';
            
            $goalsByYear[$goal['year']][$goal['category_type']][] = $goal;
        }
        
        // Für jedes Jahr und jede Kategorie prüfen, ob ein Ziel existiert
        // Wenn nicht, ein leeres Ziel hinzufügen
        $yearsToShow = $selectedYear ? [$selectedYear] : $availableYears;
        
        foreach ($yearsToShow as $year) {
            if (!isset($goalsByYear[$year])) {
                $goalsByYear[$year] = [
                    'income' => [],
                    'expense' => []
                ];
            }
            
            // Für jede Kategorie prüfen, ob ein Ziel für dieses Jahr existiert
            foreach ($allCategories as $category) {
                $categoryExists = false;
                $categoryType = $category['type'];
                
                // Prüfen, ob die Kategorie bereits in den Zielen für dieses Jahr existiert
                if (isset($goalsByYear[$year][$categoryType])) {
                    foreach ($goalsByYear[$year][$categoryType] as $goal) {
                        if ($goal['category_id'] == $category['id']) {
                            $categoryExists = true;
                            break;
                        }
                    }
                } else {
                    $goalsByYear[$year][$categoryType] = [];
                }
                
                // Wenn die Kategorie nicht existiert, ein leeres Ziel hinzufügen
                if (!$categoryExists) {
                    // Aktuellen Wert für diese Kategorie in diesem Jahr abrufen
                    $stmtCurrentValue = $this->db->prepare('
                        SELECT COALESCE(SUM(value), 0) as current_value
                        FROM expenses
                        WHERE category_id = ? AND DATE_FORMAT(date, "%Y") = ?
                    ');
                    $stmtCurrentValue->execute([$category['id'], $year]);
                    $currentValue = $stmtCurrentValue->fetchColumn();
                    
                    $goalsByYear[$year][$categoryType][] = [
                        'id' => null,
                        'category_id' => $category['id'],
                        'year' => $year,
                        'goal' => 0,
                        'category_name' => $category['name'],
                        'color' => $category['color'],
                        'category_type' => $categoryType,
                        'current_value' => $currentValue,
                        'total_value' => $currentValue,
                        'is_empty_goal' => true // Markierung für leere Ziele
                    ];
                }
            }
            
            // Sortieren der Kategorien nach Namen
            if (isset($goalsByYear[$year]['income'])) {
                usort($goalsByYear[$year]['income'], function($a, $b) {
                    return strcmp($a['category_name'], $b['category_name']);
                });
            }
            
            if (isset($goalsByYear[$year]['expense'])) {
                usort($goalsByYear[$year]['expense'], function($a, $b) {
                    return strcmp($a['category_name'], $b['category_name']);
                });
            }
        }
        
        // Zusammenfassung pro Jahr und Typ berechnen
        $yearSummaries = [];
        foreach ($goalsByYear as $year => $typeGoals) {
            $yearSummaries[$year] = [
                'income' => [
                    'total_goal' => 0,
                    'total_current' => 0,
                    'percentage' => 0
                ],
                'expense' => [
                    'total_goal' => 0,
                    'total_current' => 0,
                    'percentage' => 0
                ],
                'total' => [
                    'total_goal' => 0,
                    'total_current' => 0,
                    'percentage' => 0
                ]
            ];
            
            // Einnahmen-Zusammenfassung
            if (isset($typeGoals['income'])) {
                foreach ($typeGoals['income'] as $goal) {
                    $yearSummaries[$year]['income']['total_goal'] += (float)$goal['goal'];
                    $yearSummaries[$year]['income']['total_current'] += (float)$goal['current_value'];
                }
                $yearSummaries[$year]['income']['percentage'] = $yearSummaries[$year]['income']['total_goal'] > 0 ? 
                    ($yearSummaries[$year]['income']['total_current'] / $yearSummaries[$year]['income']['total_goal']) * 100 : 0;
            }
            
            // Ausgaben-Zusammenfassung
            if (isset($typeGoals['expense'])) {
                foreach ($typeGoals['expense'] as $goal) {
                    $yearSummaries[$year]['expense']['total_goal'] += (float)$goal['goal'];
                    $yearSummaries[$year]['expense']['total_current'] += (float)$goal['current_value'];
                }
                $yearSummaries[$year]['expense']['percentage'] = $yearSummaries[$year]['expense']['total_goal'] > 0 ? 
                    ($yearSummaries[$year]['expense']['total_current'] / $yearSummaries[$year]['expense']['total_goal']) * 100 : 0;
            }
            
            // Gesamt-Zusammenfassung
            $yearSummaries[$year]['total']['total_goal'] = $yearSummaries[$year]['income']['total_goal'] + $yearSummaries[$year]['expense']['total_goal'];
            $yearSummaries[$year]['total']['total_current'] = $yearSummaries[$year]['income']['total_current'] + $yearSummaries[$year]['expense']['total_current'];
            $yearSummaries[$year]['total']['percentage'] = $yearSummaries[$year]['total']['total_goal'] > 0 ? 
                ($yearSummaries[$year]['total']['total_current'] / $yearSummaries[$year]['total']['total_goal']) * 100 : 0;
        }
        
        include VIEW_PATH . 'expense-goals/index.php';
    }

    public function create() {
        // Vorausgewählte Kategorie und Jahr aus der URL holen
        $preselectedCategoryId = isset($_GET['category_id']) ? $_GET['category_id'] : null;
        $preselectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
        
        // Benutzer-ID aus der Session holen
        $userId = $this->session->getUserId();
        
        // Kategorien aus der Datenbank laden
        $stmt = $this->db->prepare('
            SELECT * FROM categories 
            WHERE (user_id = ? OR user_id IS NULL)
            ORDER BY type, name
        ');
        $stmt->execute([$userId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include VIEW_PATH . 'expense-goals/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $category_id = $_POST['category_id'] ?? null;
            $year = $_POST['year'] ?? date('Y');
            $goal = $_POST['goal'] ?? 0;
            
            if (!$category_id) {
                $_SESSION['error'] = 'Bitte wählen Sie eine Kategorie aus.';
                header('Location: ' . \Utils\Path::url('/expense-goals'));
                exit;
            }
            
            // Benutzer-ID aus der Session holen
            $userId = $this->session->getUserId();
            
            // Prüfen, ob bereits ein Ziel für diese Kategorie und dieses Jahr existiert
            $stmt = $this->db->prepare('
                SELECT id FROM expense_goals 
                WHERE category_id = ? AND year = ? AND (user_id = ? OR user_id IS NULL)
            ');
            $stmt->execute([$category_id, $year, $userId]);
            $existingGoal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingGoal) {
                // Update des bestehenden Ziels
                $stmt = $this->db->prepare('
                    UPDATE expense_goals 
                    SET goal = ? 
                    WHERE id = ?
                ');
                $stmt->execute([$goal, $existingGoal['id']]);
                
                $_SESSION['success'] = 'Ausgabenziel erfolgreich aktualisiert.';
            } else {
                // Neues Ziel erstellen
                $stmt = $this->db->prepare('
                    INSERT INTO expense_goals (category_id, year, goal, user_id) 
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$category_id, $year, $goal, $userId]);
                
                $_SESSION['success'] = 'Ausgabenziel erfolgreich erstellt.';
            }
            
            header('Location: ' . \Utils\Path::url('/expense-goals?year=' . $year));
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'];
        
        $stmt = $this->db->prepare('
            SELECT eg.*, c.name as category_name 
            FROM expense_goals eg
            JOIN categories c ON eg.category_id = c.id
            WHERE eg.id = ?
        ');
        $stmt->execute([$id]);
        $expenseGoal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$expenseGoal) {
            $_SESSION['error'] = 'Ausgabenziel nicht gefunden.';
            header('Location: ' . \Utils\Path::url('/expense-goals'));
            exit;
        }
        
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name');
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include VIEW_PATH . 'expense-goals/edit.php';
    }

    public function update() {
        $id = $_POST['id'];
        $category_id = $_POST['category_id'];
        $year = $_POST['year'];
        $goal = $_POST['goal'];
        
        $stmt = $this->db->prepare('UPDATE expense_goals SET category_id = ?, year = ?, goal = ? WHERE id = ?');
        $stmt->execute([$category_id, $year, $goal, $id]);
        
        $_SESSION['success'] = 'Ausgabenziel erfolgreich aktualisiert.';
        header('Location: ' . \Utils\Path::url('/expense-goals'));
        exit;
    }

    public function delete() {
        $id = $_POST['id'];
        
        $stmt = $this->db->prepare('DELETE FROM expense_goals WHERE id = ?');
        $stmt->execute([$id]);
        
        $_SESSION['success'] = 'Ausgabenziel erfolgreich gelöscht.';
        header('Location: ' . \Utils\Path::url('/expense-goals'));
        exit;
    }
} 