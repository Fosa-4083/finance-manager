<?php

namespace Controllers;

use Models\Expense;
use Models\Project;
use PDO;

class ExpenseController extends BaseController {
    private $project;

    public function __construct($db = null) {
        // Basisklassen-Konstruktor aufrufen
        parent::__construct($db);
        
        // Project-Modell initialisieren
        $this->project = new Project($this->db);
    }

    public function index() {
        // Zeitraum-Typ (Monat, Jahr, Benutzerdefiniert, Gesamter Zeitraum)
        $period_type = isset($_GET['period_type']) ? $_GET['period_type'] : 'month';
        
        // Standardwerte für Filter
        $month = isset($_GET['month']) ? $_GET['month'] : date('m');
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;
        $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        
        // Neue Filter
        $description_search = isset($_GET['description_search']) ? $_GET['description_search'] : null;
        $min_amount = isset($_GET['min_amount']) && is_numeric($_GET['min_amount']) ? floatval($_GET['min_amount']) : null;
        $max_amount = isset($_GET['max_amount']) && is_numeric($_GET['max_amount']) ? floatval($_GET['max_amount']) : null;
        
        // Benutzerdefinierter Zeitraum oder Standardzeitraum
        if ($period_type === 'all') {
            // Für "Gesamter Zeitraum" keine Datumsbeschränkung
            $start_date = null;
            $end_date = null;
        } else {
            // Benutzerdefinierter Zeitraum
            $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // Erster Tag des aktuellen Monats
            $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');       // Letzter Tag des aktuellen Monats
        }
        
        // Paginierung
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
        $offset = ($page - 1) * $per_page;
        
        // SQL-Abfrage für die Gesamtanzahl der Datensätze
        $count_sql = '
            SELECT COUNT(*) as total
            FROM expenses e 
            JOIN categories c ON e.category_id = c.id 
            LEFT JOIN projects p ON e.project_id = p.id
            WHERE 1=1
        ';
        
        // SQL-Abfrage mit Filtern
        $sql = '
            SELECT e.*, c.name as category_name, c.color as category_color,
                   p.name as project_name
            FROM expenses e 
            JOIN categories c ON e.category_id = c.id 
            LEFT JOIN projects p ON e.project_id = p.id
            WHERE 1=1
        ';
        
        $params = [];
        $count_params = [];
        
        // Zeitraum-Filter je nach Typ hinzufügen
        if ($period_type === 'month') {
            $sql .= ' AND DATE_FORMAT(e.date, "%m") = :month AND DATE_FORMAT(e.date, "%Y") = :year';
            $count_sql .= ' AND DATE_FORMAT(e.date, "%m") = :month AND DATE_FORMAT(e.date, "%Y") = :year';
            $params[':month'] = $month;
            $params[':year'] = $year;
            $count_params[':month'] = $month;
            $count_params[':year'] = $year;
        } elseif ($period_type === 'year') {
            $sql .= ' AND DATE_FORMAT(e.date, "%Y") = :year';
            $count_sql .= ' AND DATE_FORMAT(e.date, "%Y") = :year';
            $params[':year'] = $year;
            $count_params[':year'] = $year;
        } elseif ($period_type === 'custom') {
            $sql .= ' AND e.date BETWEEN :start_date AND :end_date';
            $count_sql .= ' AND e.date BETWEEN :start_date AND :end_date';
            $params[':start_date'] = $start_date;
            $params[':end_date'] = $end_date;
            $count_params[':start_date'] = $start_date;
            $count_params[':end_date'] = $end_date;
        }
        
        // Kategorie-Filter hinzufügen, wenn ausgewählt
        if ($category_id) {
            $sql .= ' AND e.category_id = :category_id';
            $count_sql .= ' AND e.category_id = :category_id';
            $params[':category_id'] = $category_id;
            $count_params[':category_id'] = $category_id;
        }
        
        // Projekt-Filter hinzufügen, wenn ausgewählt
        if ($project_id) {
            $sql .= ' AND e.project_id = :project_id';
            $count_sql .= ' AND e.project_id = :project_id';
            $params[':project_id'] = $project_id;
            $count_params[':project_id'] = $project_id;
        }
        
        // Typ-Filter hinzufügen (Einnahme/Ausgabe)
        if ($type === 'income') {
            $sql .= ' AND e.value > 0';
            $count_sql .= ' AND e.value > 0';
        } elseif ($type === 'expense') {
            $sql .= ' AND e.value < 0';
            $count_sql .= ' AND e.value < 0';
        }
        
        // Beschreibungssuche hinzufügen
        if ($description_search) {
            $sql .= ' AND e.description LIKE :description_search';
            $count_sql .= ' AND e.description LIKE :description_search';
            $params[':description_search'] = '%' . $description_search . '%';
            $count_params[':description_search'] = '%' . $description_search . '%';
        }
        
        // Betragsfilter hinzufügen
        if ($min_amount !== null) {
            $sql .= ' AND ABS(e.value) >= :min_amount';
            $count_sql .= ' AND ABS(e.value) >= :min_amount';
            $params[':min_amount'] = $min_amount;
            $count_params[':min_amount'] = $min_amount;
        }
        
        if ($max_amount !== null) {
            $sql .= ' AND ABS(e.value) <= :max_amount';
            $count_sql .= ' AND ABS(e.value) <= :max_amount';
            $params[':max_amount'] = $max_amount;
            $count_params[':max_amount'] = $max_amount;
        }
        
        // Gesamtanzahl der Datensätze ermitteln
        $count_stmt = $this->db->prepare($count_sql);
        $count_stmt->execute($count_params);
        $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total_count / $per_page);
        
        // Sortierung und Paginierung hinzufügen
        $sql .= ' ORDER BY e.date DESC LIMIT :limit OFFSET :offset';
        $params[':limit'] = $per_page;
        $params[':offset'] = $offset;
        
        // Abfrage ausführen
        $stmt = $this->db->prepare($sql);
        
        // PDO unterstützt keine Bindung von LIMIT und OFFSET als benannte Parameter in SQLite
        // Daher müssen wir sie manuell binden
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Kategorien für Filter abrufen
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name');
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Projekte für Filter abrufen
        $projects = $this->project->getAll();
        
        // Summen berechnen (für die aktuelle Seite)
        $totalExpenses = 0;     // Gesamtsumme (Einnahmen - Ausgaben)
        $totalIncome = 0;       // Summe der Einnahmen
        $totalExpensesOnly = 0; // Summe der Ausgaben (als positiver Wert)
        
        foreach ($expenses as $expense) {
            $totalExpenses += $expense['value'];
            
            if ($expense['value'] > 0) {
                $totalIncome += $expense['value'];
            } else {
                $totalExpensesOnly += abs($expense['value']);
            }
        }
        
        // Gesamtsummen für alle Datensätze berechnen (nicht nur für die aktuelle Seite)
        $total_sql = '
            SELECT 
                SUM(CASE WHEN e.value > 0 THEN e.value ELSE 0 END) as total_income,
                SUM(CASE WHEN e.value < 0 THEN ABS(e.value) ELSE 0 END) as total_expenses_only,
                SUM(e.value) as total_expenses
            FROM expenses e 
            JOIN categories c ON e.category_id = c.id 
            LEFT JOIN projects p ON e.project_id = p.id
            WHERE 1=1
        ';
        
        // Die gleichen Filter wie für die Hauptabfrage anwenden
        if ($period_type === 'month') {
            $total_sql .= ' AND DATE_FORMAT(e.date, "%m") = :month AND DATE_FORMAT(e.date, "%Y") = :year';
        } elseif ($period_type === 'year') {
            $total_sql .= ' AND DATE_FORMAT(e.date, "%Y") = :year';
        } elseif ($period_type === 'custom') {
            $total_sql .= ' AND e.date BETWEEN :start_date AND :end_date';
        }
        
        if ($category_id) {
            $total_sql .= ' AND e.category_id = :category_id';
        }
        
        if ($project_id) {
            $total_sql .= ' AND e.project_id = :project_id';
        }
        
        if ($type === 'income') {
            $total_sql .= ' AND e.value > 0';
        } elseif ($type === 'expense') {
            $total_sql .= ' AND e.value < 0';
        }
        
        if ($description_search) {
            $total_sql .= ' AND e.description LIKE :description_search';
        }
        
        if ($min_amount !== null) {
            $total_sql .= ' AND ABS(e.value) >= :min_amount';
        }
        
        if ($max_amount !== null) {
            $total_sql .= ' AND ABS(e.value) <= :max_amount';
        }
        
        $total_stmt = $this->db->prepare($total_sql);
        $total_stmt->execute($count_params);
        $totals = $total_stmt->fetch(PDO::FETCH_ASSOC);
        
        $totalAllIncome = $totals['total_income'] ?? 0;
        $totalAllExpensesOnly = $totals['total_expenses_only'] ?? 0;
        $totalAllExpenses = $totals['total_expenses'] ?? 0;
        
        // Verfügbare Jahre für Filter
        $stmt = $this->db->query('SELECT DISTINCT DATE_FORMAT(date, "%Y") as year FROM expenses ORDER BY year DESC');
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Wenn keine Jahre gefunden wurden, aktuelles Jahr hinzufügen
        if (empty($years)) {
            $years = [date('Y')];
        }
        
        // Paginierungsinformationen an die View übergeben
        $pagination = [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total_pages,
            'total_count' => $total_count
        ];
        
        include VIEW_PATH . 'expenses/index.php';
    }

    public function create() {
        // Kategorien abrufen
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name');
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Aktive Projekte abrufen
        $projects = $this->project->getActiveProjects();
        
        include VIEW_PATH . 'expenses/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $expense = new Expense();
            $expense->category_id = $_POST['category_id'] ?? null;
            $expense->project_id = $_POST['project_id'] ?? null;
            $expense->date = $_POST['date'] ?? null;
            $expense->description = $_POST['description'] ?? '';
            $value = $_POST['value'] ?? 0;
            $type = $_POST['type'] ?? 'expense';
            
            // Wenn der Betrag positiv ist, aber der Typ "expense" ist, machen wir den Betrag negativ
            if ($type === 'expense') {
                $expense->value = -abs($value);
            } else {
                $expense->value = abs($value);
            }
            
            $expense->afa = isset($_POST['afa']) ? 1 : 0;  // Lohnsteuerausgleich-relevante Ausgabe
            
            // Validierung
            if (empty($expense->category_id)) {
                $_SESSION['error'] = 'Bitte wählen Sie eine Kategorie aus.';
                header('Location: ' . \Utils\Path::url('/expenses/create'));
                exit;
            }
            
            if (empty($expense->date)) {
                $_SESSION['error'] = 'Bitte geben Sie ein Datum ein.';
                header('Location: ' . \Utils\Path::url('/expenses/create'));
                exit;
            }
            
            if (!is_numeric($value) || $value == 0) {
                $_SESSION['error'] = 'Bitte geben Sie einen gültigen Betrag ein.';
                header('Location: ' . \Utils\Path::url('/expenses/create'));
                exit;
            }
            
            if ($expense->save()) {
                $_SESSION['success'] = 'Buchung erfolgreich gespeichert.';
                header('Location: ' . \Utils\Path::url('/expenses'));
                exit;
            } else {
                $_SESSION['error'] = 'Fehler beim Speichern der Buchung.';
                header('Location: ' . \Utils\Path::url('/expenses/create'));
                exit;
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'Keine Ausgabe-ID angegeben.';
            header('Location: ' . \Utils\Path::url('/expenses'));
            exit;
        }
        
        $expense = new Expense();
        if ($expense->findById($id)) {
            // Kategorien abrufen
            $stmt = $this->db->query('SELECT * FROM categories ORDER BY name');
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Aktive Projekte abrufen
            $projects = $this->project->getActiveProjects();
            
            // Konvertiere das Expense-Objekt in ein Array für die View
            $expense = [
                'id' => $expense->id,
                'category_id' => $expense->category_id,
                'project_id' => $expense->project_id,
                'date' => $expense->date,
                'description' => $expense->description,
                'value' => $expense->value,
                'afa' => $expense->afa
            ];
            
            include VIEW_PATH . 'expenses/edit.php';
        } else {
            $_SESSION['error'] = 'Ausgabe nicht gefunden.';
            header('Location: ' . \Utils\Path::url('/expenses'));
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                $_SESSION['error'] = 'Keine Ausgabe-ID angegeben.';
                header('Location: ' . \Utils\Path::url('/expenses'));
                exit;
            }
            
            $expense = new Expense();
            if (!$expense->findById($id)) {
                $_SESSION['error'] = 'Ausgabe nicht gefunden.';
                header('Location: ' . \Utils\Path::url('/expenses'));
                exit;
            }
            
            $expense->category_id = $_POST['category_id'] ?? null;
            $expense->project_id = $_POST['project_id'] ?? null;
            $expense->date = $_POST['date'] ?? null;
            $expense->description = $_POST['description'] ?? '';
            $value = $_POST['value'] ?? 0;
            $type = $_POST['type'] ?? 'expense';
            
            // Wenn der Betrag positiv ist, aber der Typ "expense" ist, machen wir den Betrag negativ
            if ($type === 'expense') {
                $expense->value = -abs($value);
            } else {
                $expense->value = abs($value);
            }
            
            $expense->afa = isset($_POST['afa']) ? 1 : 0;
            
            // Validierung
            if (empty($expense->category_id)) {
                $_SESSION['error'] = 'Bitte wählen Sie eine Kategorie aus.';
                header('Location: ' . \Utils\Path::url('/expenses/edit?id=' . $id));
                exit;
            }
            
            if (empty($expense->date)) {
                $_SESSION['error'] = 'Bitte geben Sie ein Datum ein.';
                header('Location: ' . \Utils\Path::url('/expenses/edit?id=' . $id));
                exit;
            }
            
            if (!is_numeric($value) || $value == 0) {
                $_SESSION['error'] = 'Bitte geben Sie einen gültigen Betrag ein.';
                header('Location: ' . \Utils\Path::url('/expenses/edit?id=' . $id));
                exit;
            }
            
            if ($expense->update()) {
                $_SESSION['success'] = 'Buchung erfolgreich aktualisiert.';
                header('Location: ' . \Utils\Path::url('/expenses'));
                exit;
            } else {
                $_SESSION['error'] = 'Fehler beim Aktualisieren der Buchung.';
                header('Location: ' . \Utils\Path::url('/expenses/edit?id=' . $id));
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'Keine Ausgabe-ID angegeben.';
            header('Location: ' . \Utils\Path::url('/expenses'));
            exit;
        }
        
        $expense = new Expense();
        if ($expense->findById($id)) {
            if ($expense->delete()) {
                $_SESSION['success'] = 'Buchung erfolgreich gelöscht.';
            } else {
                $_SESSION['error'] = 'Fehler beim Löschen der Buchung.';
            }
        } else {
            $_SESSION['error'] = 'Buchung nicht gefunden.';
        }
        
        header('Location: ' . \Utils\Path::url('/expenses'));
        exit;
    }
    
    /**
     * Massenbearbeitung von Buchungen - Zuordnung zu einem Projekt
     */
    public function bulkUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \Utils\Path::url('/expenses'));
            exit;
        }
        
        $project_id = $_POST['project_id'] ?? null;
        $expense_ids = $_POST['expense_ids'] ?? [];
        
        // Validierung
        if (empty($project_id)) {
            $_SESSION['error'] = 'Bitte wählen Sie ein Projekt aus.';
            header('Location: ' . \Utils\Path::url('/expenses'));
            exit;
        }
        
        if (empty($expense_ids) || !is_array($expense_ids)) {
            $_SESSION['error'] = 'Bitte wählen Sie mindestens eine Buchung aus.';
            header('Location: ' . \Utils\Path::url('/expenses'));
            exit;
        }
        
        // Prüfen, ob das Projekt existiert
        $stmt = $this->db->prepare('SELECT id FROM projects WHERE id = ?');
        $stmt->execute([$project_id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'Das ausgewählte Projekt existiert nicht.';
            header('Location: ' . \Utils\Path::url('/expenses'));
            exit;
        }
        
        try {
            // Transaktion starten
            $this->db->beginTransaction();
            
            // Prepared Statement für die Aktualisierung
            $stmt = $this->db->prepare('UPDATE expenses SET project_id = ? WHERE id = ?');
            
            // Zähler für erfolgreiche Aktualisierungen
            $successCount = 0;
            
            // Jede Buchung aktualisieren
            foreach ($expense_ids as $expense_id) {
                if ($stmt->execute([$project_id, $expense_id])) {
                    $successCount++;
                }
            }
            
            // Transaktion abschließen
            $this->db->commit();
            
            if ($successCount > 0) {
                $_SESSION['success'] = "$successCount Buchung(en) wurden erfolgreich dem Projekt zugeordnet.";
            } else {
                $_SESSION['error'] = 'Es wurden keine Buchungen aktualisiert.';
            }
        } catch (\PDOException $e) {
            // Bei Fehler Transaktion zurückrollen
            $this->db->rollBack();
            $_SESSION['error'] = 'Datenbankfehler: ' . $e->getMessage();
        }
        
        header('Location: ' . \Utils\Path::url('/expenses'));
        exit;
    }

    public function getSuggestions() {
        $query = $_GET['query'] ?? '';
        $field = $_GET['field'] ?? '';
        $category_id = $_GET['category_id'] ?? null;
        $project_id = $_GET['project_id'] ?? null;

        $sql = '';
        $params = [];

        switch ($field) {
            case 'description':
                // Verbesserte Abfrage für Beschreibungsvorschläge mit mehr Kontext
                $sql = 'SELECT DISTINCT description, value, category_id, 
                        (SELECT COUNT(*) FROM expenses e2 WHERE e2.description = e.description) as count
                       FROM expenses e
                       WHERE description LIKE :query';
                if ($category_id) {
                    $sql .= ' AND category_id = :category_id';
                    $params[':category_id'] = $category_id;
                }
                if ($project_id) {
                    $sql .= ' AND project_id = :project_id';
                    $params[':project_id'] = $project_id;
                }
                $sql .= ' ORDER BY count DESC, date DESC LIMIT 8'; // Sortierung nach Häufigkeit und Datum
                $params[':query'] = '%' . $query . '%';
                break;

            case 'value':
                // Verbesserte Abfrage für Betragsvorschläge mit Beschreibung und Häufigkeit
                $sql = 'SELECT DISTINCT ABS(value) as value, description, 
                        (SELECT COUNT(*) FROM expenses e2 WHERE ABS(e2.value) = ABS(e.value) AND e2.category_id = e.category_id) as count
                       FROM expenses e
                       WHERE category_id = :category_id';
                if ($project_id) {
                    $sql .= ' AND project_id = :project_id';
                    $params[':project_id'] = $project_id;
                }
                $sql .= ' ORDER BY count DESC, date DESC LIMIT 8'; // Sortierung nach Häufigkeit und Datum
                $params[':category_id'] = $category_id;
                break;
        }

        if ($sql) {
            try {
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                header('Content-Type: application/json');
                echo json_encode($suggestions);
            } catch (\PDOException $e) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    }
} 