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
        
        // SQL-Abfrage für Ausgabenziele
        $sql = '
            SELECT 
                eg.id,
                eg.category_id,
                eg.year,
                eg.goal,
                c.name as category_name,
                c.color,
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
            ORDER BY eg.year DESC, c.name
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
            
            $goalsByYear[$goal['year']][] = $goal;
        }
        
        // Zusammenfassung pro Jahr berechnen
        $yearSummaries = [];
        foreach ($goalsByYear as $year => $goals) {
            $totalGoal = 0;
            $totalCurrent = 0;
            
            foreach ($goals as $goal) {
                $totalGoal += (float)$goal['goal'];
                $totalCurrent += (float)$goal['current_value'];
            }
            
            $yearSummaries[$year] = [
                'total_goal' => $totalGoal,
                'total_current' => $totalCurrent,
                'percentage' => $totalGoal > 0 ? ($totalCurrent / $totalGoal) * 100 : 0
            ];
        }
        
        include VIEW_PATH . 'expense-goals/index.php';
    }

    public function create() {
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name');
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include VIEW_PATH . 'expense-goals/create.php';
    }

    public function store() {
        $category_id = $_POST['category_id'];
        $year = $_POST['year'];
        $goal = $_POST['goal'];
        
        $stmt = $this->db->prepare('INSERT INTO expense_goals (category_id, year, goal) VALUES (?, ?, ?)');
        $stmt->execute([$category_id, $year, $goal]);
        
        $_SESSION['success'] = 'Ausgabenziel erfolgreich erstellt.';
        header('Location: ' . \Utils\Path::url('/expense-goals'));
        exit;
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