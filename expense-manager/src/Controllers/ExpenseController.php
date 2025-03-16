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
        
        // Benutzerfilter hinzufügen - nur Daten des angemeldeten Benutzers anzeigen
        $userId = $this->session->getUserId();
        if ($userId) {
            $sql .= ' AND (e.user_id = :user_id OR e.user_id IS NULL)';
            $count_sql .= ' AND (e.user_id = :user_id OR e.user_id IS NULL)';
            $params[':user_id'] = $userId;
            $count_params[':user_id'] = $userId;
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
        
        // Benutzerfilter auch für die Gesamtsummen-Abfrage hinzufügen
        if ($userId) {
            $total_sql .= ' AND (e.user_id = :user_id OR e.user_id IS NULL)';
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
            // Debug-Ausgabe
            error_log("ExpenseController::store - POST-Daten: " . print_r($_POST, true));
            
            try {
                $expense = new Expense();
                $expense->category_id = $_POST['category_id'] ?? null;
                
                // Leere project_id als NULL behandeln
                $expense->project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : null;
                
                $expense->date = $_POST['date'] ?? null;
                $expense->description = $_POST['description'] ?? '';
                $value = $_POST['value'] ?? 0;
                $type = $_POST['type'] ?? 'expense';
                
                // Debug-Ausgabe
                error_log("ExpenseController::store - Verarbeitete Daten: category_id={$expense->category_id}, project_id=" . 
                         (is_null($expense->project_id) ? "NULL" : $expense->project_id) . 
                         ", date={$expense->date}, description={$expense->description}, value={$value}, type={$type}");
                
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
                    error_log("ExpenseController::store - Fehler: Keine Kategorie ausgewählt");
                    header('Location: ' . \Utils\Path::url('/expenses/create'));
                    exit;
                }
                
                if (empty($expense->date)) {
                    $_SESSION['error'] = 'Bitte geben Sie ein Datum ein.';
                    error_log("ExpenseController::store - Fehler: Kein Datum angegeben");
                    header('Location: ' . \Utils\Path::url('/expenses/create'));
                    exit;
                }
                
                if (!is_numeric($value) || $value == 0) {
                    $_SESSION['error'] = 'Bitte geben Sie einen gültigen Betrag ein.';
                    error_log("ExpenseController::store - Fehler: Ungültiger Betrag: {$value}");
                    header('Location: ' . \Utils\Path::url('/expenses/create'));
                    exit;
                }
                
                // Debug-Ausgabe vor dem Speichern
                error_log("ExpenseController::store - Vor dem Speichern: " . print_r($expense, true));
                
                if ($expense->save()) {
                    $_SESSION['success'] = 'Buchung erfolgreich gespeichert.';
                    error_log("ExpenseController::store - Erfolg: Buchung gespeichert mit ID {$expense->id}");
                    
                    // Filterparameter aus dem Formular extrahieren
                    $filterParams = $this->extractFilterParams($_POST);
                    $redirectUrl = $this->buildRedirectUrl('/expenses', $filterParams);
                    
                    header('Location: ' . $redirectUrl);
                    exit;
                } else {
                    $_SESSION['error'] = 'Fehler beim Speichern der Buchung.';
                    error_log("ExpenseController::store - Fehler: Speichern fehlgeschlagen");
                    header('Location: ' . \Utils\Path::url('/expenses/create'));
                    exit;
                }
            } catch (\Exception $e) {
                // Fehler protokollieren
                error_log("ExpenseController::store - Exception: " . $e->getMessage());
                $_SESSION['error'] = 'Ein unerwarteter Fehler ist aufgetreten: ' . $e->getMessage();
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
        
        // Debug-Ausgabe
        error_log("ExpenseController::edit - GET-Parameter: " . print_r($_GET, true));
        
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \Utils\Path::url('/expenses'));
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : null; // Korrigiert: Leere Werte als NULL behandeln
        $date = $_POST['date'] ?? null;
        $description = $_POST['description'] ?? '';
        $value = $_POST['value'] ?? 0;
        $afa = isset($_POST['afa']) ? 1 : 0;
        
        // Validierung
        if (!$id || !$category_id || !$date || $value == 0) {
            $_SESSION['error'] = 'Bitte füllen Sie alle Pflichtfelder aus.';
            header('Location: ' . \Utils\Path::url('/expenses/edit?id=' . $id));
            exit;
        }

        // Prüfen, ob die Kategorie existiert
        $stmt = $this->db->prepare('SELECT type FROM categories WHERE id = ?');
        $stmt->execute([$category_id]);
        $category = $stmt->fetch();
        if (!$category) {
            $_SESSION['error'] = 'Die ausgewählte Kategorie existiert nicht.';
            header('Location: ' . \Utils\Path::url('/expenses/edit?id=' . $id));
            exit;
        }
        
        // Betrag basierend auf Kategorietyp anpassen (positiv für Einnahmen, negativ für Ausgaben)
        $value = abs($value); // Sicherstellen, dass der Wert positiv ist
        if ($category['type'] === 'expense') {
            $value = -$value; // Für Ausgaben negativ machen
        }
        
        try {
            $expense = new \Models\Expense($this->db);
            $expense->setId($id);
            $expense->setCategoryId($category_id);
            $expense->setProjectId($project_id); // Kann NULL sein
            $expense->setDate($date);
            $expense->setDescription($description);
            $expense->setValue($value);
            $expense->setAfa($afa);
            
            if ($expense->save()) {
                $_SESSION['success'] = 'Buchung wurde erfolgreich aktualisiert.';
                
                // Filterparameter aus dem Formular extrahieren
                $filterParams = $this->extractFilterParams($_POST);
                $redirectUrl = $this->buildRedirectUrl('/expenses', $filterParams);
                
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                $_SESSION['error'] = 'Fehler beim Aktualisieren der Buchung.';
                header('Location: ' . \Utils\Path::url('/expenses/edit?id=' . $id));
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler: ' . $e->getMessage();
            header('Location: ' . \Utils\Path::url('/expenses/edit?id=' . $id));
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'Keine Ausgabe-ID angegeben.';
            header('Location: ' . \Utils\Path::url('/expenses'));
            exit;
        }
        
        // Debug-Ausgabe
        error_log("ExpenseController::delete - GET-Parameter: " . print_r($_GET, true));
        
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
        
        // Filterparameter aus der URL extrahieren
        $filterParams = $this->extractFilterParams($_GET);
        $redirectUrl = $this->buildRedirectUrl('/expenses', $filterParams);
        
        header('Location: ' . $redirectUrl);
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
            
            // Filterparameter aus dem Formular extrahieren
            $filterParams = $this->extractFilterParams($_POST);
            $redirectUrl = $this->buildRedirectUrl('/expenses', $filterParams);
            
            header('Location: ' . $redirectUrl);
            exit;
        }
        
        if (empty($expense_ids) || !is_array($expense_ids)) {
            $_SESSION['error'] = 'Bitte wählen Sie mindestens eine Buchung aus.';
            
            // Filterparameter aus dem Formular extrahieren
            $filterParams = $this->extractFilterParams($_POST);
            $redirectUrl = $this->buildRedirectUrl('/expenses', $filterParams);
            
            header('Location: ' . $redirectUrl);
            exit;
        }
        
        // Prüfen, ob das Projekt existiert
        $stmt = $this->db->prepare('SELECT id FROM projects WHERE id = ?');
        $stmt->execute([$project_id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'Das ausgewählte Projekt existiert nicht.';
            
            // Filterparameter aus dem Formular extrahieren
            $filterParams = $this->extractFilterParams($_POST);
            $redirectUrl = $this->buildRedirectUrl('/expenses', $filterParams);
            
            header('Location: ' . $redirectUrl);
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
        
        // Filterparameter aus dem Formular extrahieren
        $filterParams = $this->extractFilterParams($_POST);
        $redirectUrl = $this->buildRedirectUrl('/expenses', $filterParams);
        
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    /**
     * Extrahiert Filterparameter aus einem Array (z.B. $_POST oder $_GET)
     * 
     * @param array $data Array mit Daten
     * @return array Array mit Filterparametern
     */
    private function extractFilterParams($data) {
        $filterParams = [];
        
        // Direkte Filterparameter
        $filterParamNames = [
            'period_type', 'month', 'year', 'category_id', 'project_id', 
            'type', 'description_search', 'min_amount', 'max_amount',
            'start_date', 'end_date', 'page', 'per_page'
        ];
        
        foreach ($filterParamNames as $param) {
            if (isset($data[$param])) {
                $filterParams[$param] = $data[$param];
            }
        }
        
        // Filterparameter mit 'filter_' Präfix
        foreach ($data as $key => $value) {
            if (strpos($key, 'filter_') === 0) {
                $paramName = substr($key, 7); // Entferne 'filter_' vom Anfang
                $filterParams[$paramName] = $value;
            }
        }
        
        // Debug-Ausgabe
        error_log("Extrahierte Filterparameter: " . print_r($filterParams, true));
        
        return $filterParams;
    }
    
    /**
     * Erstellt eine Redirect-URL mit Filterparametern
     * 
     * @param string $baseUrl Basis-URL
     * @param array $filterParams Array mit Filterparametern
     * @return string Vollständige URL mit Filterparametern
     */
    private function buildRedirectUrl($baseUrl, $filterParams) {
        $redirectUrl = \Utils\Path::url($baseUrl);
        
        if (!empty($filterParams)) {
            $redirectUrl .= '?' . http_build_query($filterParams);
        }
        
        return $redirectUrl;
    }

    public function getSuggestions() {
        // Puffer starten, um alle vorherigen Ausgaben zu erfassen und zu verwerfen
        ob_start();
        
        // Setze den Content-Type Header
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $query = $_GET['query'] ?? '';
            $field = $_GET['field'] ?? '';
            $category_id = $_GET['category_id'] ?? null;
            $project_id = $_GET['project_id'] ?? null;
            
            // Debug-Ausgabe
            error_log("getSuggestions aufgerufen mit: field=$field, query=$query, category_id=$category_id, project_id=$project_id");
    
            $sql = '';
            $params = [];
    
            switch ($field) {
                case 'description':
                    // Verbesserte Abfrage für Beschreibungsvorschläge mit vollständigem Kontext
                    $sql = 'SELECT DISTINCT e.description, ABS(e.value) as value, e.category_id, e.project_id,
                            c.name as category_name, c.color as category_color, c.type as category_type,
                            p.name as project_name,
                            (SELECT COUNT(*) FROM expenses e2 WHERE e2.description = e.description) as count
                           FROM expenses e
                           JOIN categories c ON e.category_id = c.id
                           LEFT JOIN projects p ON e.project_id = p.id
                           WHERE e.description LIKE :query';
                    
                    // Benutzerfilter hinzufügen - nur Daten des angemeldeten Benutzers anzeigen
                    $userId = $this->session->getUserId();
                    if ($userId) {
                        $sql .= ' AND (e.user_id = :user_id OR e.user_id IS NULL)';
                        $params[':user_id'] = $userId;
                    }
                    
                    if ($category_id) {
                        $sql .= ' AND e.category_id = :category_id';
                        $params[':category_id'] = $category_id;
                    }
                    if ($project_id) {
                        $sql .= ' AND e.project_id = :project_id';
                        $params[':project_id'] = $project_id;
                    }
                    $sql .= ' ORDER BY count DESC, e.date DESC LIMIT 10'; // Sortierung nach Häufigkeit und Datum
                    $params[':query'] = '%' . $query . '%';
                    break;
    
                case 'value':
                    // Verbesserte Abfrage für Betragsvorschläge mit vollständigem Kontext
                    $sql = 'SELECT DISTINCT ABS(e.value) as value, e.description, e.category_id, e.project_id,
                            c.name as category_name, c.color as category_color, c.type as category_type,
                            p.name as project_name,
                            (SELECT COUNT(*) FROM expenses e2 WHERE ABS(e2.value) = ABS(e.value) AND e2.category_id = e.category_id) as count
                           FROM expenses e
                           JOIN categories c ON e.category_id = c.id
                           LEFT JOIN projects p ON e.project_id = p.id
                           WHERE 1=1';
                    
                    // Benutzerfilter hinzufügen - nur Daten des angemeldeten Benutzers anzeigen
                    $userId = $this->session->getUserId();
                    if ($userId) {
                        $sql .= ' AND (e.user_id = :user_id OR e.user_id IS NULL)';
                        $params[':user_id'] = $userId;
                    }
                    
                    if ($category_id) {
                        $sql .= ' AND e.category_id = :category_id';
                        $params[':category_id'] = $category_id;
                    }
                    if ($project_id) {
                        $sql .= ' AND e.project_id = :project_id';
                        $params[':project_id'] = $project_id;
                    }
                    $sql .= ' ORDER BY count DESC, e.date DESC LIMIT 10'; // Sortierung nach Häufigkeit und Datum
                    break;
                    
                case 'category':
                    // Verbesserte Abfrage für Kategorievorschläge basierend auf der Beschreibung
                    $sql = 'SELECT DISTINCT c.id, c.name, c.color, c.type, c.description,
                            (SELECT COUNT(*) FROM expenses e WHERE e.category_id = c.id AND e.description LIKE :query) as relevance,
                            (SELECT AVG(ABS(e.value)) FROM expenses e WHERE e.category_id = c.id AND e.description LIKE :query) as avg_value
                           FROM categories c
                           JOIN expenses e ON c.id = e.category_id
                           WHERE e.description LIKE :query';
                    
                    // Benutzerfilter hinzufügen - nur Daten des angemeldeten Benutzers anzeigen
                    $userId = $this->session->getUserId();
                    if ($userId) {
                        $sql .= ' AND (e.user_id = :user_id OR e.user_id IS NULL)';
                        $params[':user_id'] = $userId;
                    }
                    
                    $sql .= ' GROUP BY c.id
                           ORDER BY relevance DESC
                           LIMIT 5';
                    $params[':query'] = '%' . $query . '%';
                    break;
                    
                case 'complete':
                    // Neue Abfrage für vollständige Vorschläge (alle Felder)
                    $sql = 'SELECT e.description, ABS(e.value) as value, e.category_id, e.project_id,
                            c.name as category_name, c.color as category_color, c.type as category_type,
                            p.name as project_name,
                            (SELECT COUNT(*) FROM expenses e2 WHERE e2.description = e.description) as count
                           FROM expenses e
                           JOIN categories c ON e.category_id = c.id
                           LEFT JOIN projects p ON e.project_id = p.id
                           WHERE e.description LIKE :query';
                    
                    // Benutzerfilter hinzufügen - nur Daten des angemeldeten Benutzers anzeigen
                    $userId = $this->session->getUserId();
                    if ($userId) {
                        $sql .= ' AND (e.user_id = :user_id OR e.user_id IS NULL)';
                        $params[':user_id'] = $userId;
                    }
                    
                    $sql .= ' ORDER BY count DESC, e.date DESC LIMIT 10';
                    $params[':query'] = '%' . $query . '%';
                    break;
            }
    
            if ($sql) {
                try {
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($params);
                    $suggestions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    
                    // Stelle sicher, dass alle Werte UTF-8 kodiert sind
                    $suggestions = array_map(function($item) {
                        return array_map(function($value) {
                            if (is_string($value)) {
                                return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                            }
                            return $value;
                        }, $item);
                    }, $suggestions);
                    
                    // Debug-Ausgabe in die Fehlerprotokolle
                    error_log("Vorschläge gefunden: " . count($suggestions));
                    
                    // Stelle sicher, dass die Ausgabe gültiges JSON ist
                    $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
                    $jsonResult = json_encode($suggestions, $jsonOptions);
                    
                    if ($jsonResult === false) {
                        // Fehler beim JSON-Encoding
                        error_log("JSON-Encoding-Fehler: " . json_last_error_msg());
                        
                        // Versuche, die problematischen Zeichen zu entfernen
                        $cleanedSuggestions = [];
                        foreach ($suggestions as $suggestion) {
                            $cleanedSuggestion = [];
                            foreach ($suggestion as $key => $value) {
                                if (is_string($value)) {
                                    // Entferne nicht-UTF-8-Zeichen
                                    $cleanedSuggestion[$key] = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}]/u', '', $value);
                                } else {
                                    $cleanedSuggestion[$key] = $value;
                                }
                            }
                            $cleanedSuggestions[] = $cleanedSuggestion;
                        }
                        
                        $jsonResult = json_encode($cleanedSuggestions, $jsonOptions);
                        
                        if ($jsonResult === false) {
                            // Immer noch ein Fehler, gib ein leeres Array zurück
                            error_log("JSON-Encoding-Fehler nach Bereinigung: " . json_last_error_msg());
                            
                            // Verwerfe alle bisherigen Ausgaben
                            ob_end_clean();
                            
                            echo '[]';
                            exit;
                        }
                    }
                    
                    // Verwerfe alle bisherigen Ausgaben
                    ob_end_clean();
                    
                    echo $jsonResult;
                } catch (\PDOException $e) {
                    error_log("PDO-Fehler in getSuggestions: " . $e->getMessage());
                    
                    // Verwerfe alle bisherigen Ausgaben
                    ob_end_clean();
                    
                    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()], $jsonOptions);
                }
            } else {
                // Verwerfe alle bisherigen Ausgaben
                ob_end_clean();
                
                echo '[]';
            }
        } catch (\Exception $e) {
            error_log("Allgemeiner Fehler in getSuggestions: " . $e->getMessage());
            
            // Verwerfe alle bisherigen Ausgaben
            ob_end_clean();
            
            echo json_encode(['error' => 'Fehler: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        exit;
    }
} 