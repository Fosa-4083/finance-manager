<?php

namespace Controllers;

use PDO;

class CategoryController {
    private $db;

    public function __construct() {
        try {
            echo "<!-- Debug: Versuche Datenbankverbindung aufzubauen -->\n";
            $dbPath = __DIR__ . '/../../database/database.sqlite';
            echo "<!-- Debug: Datenbank-Pfad: " . $dbPath . " -->\n";
            $this->db = new PDO('sqlite:' . $dbPath);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<!-- Debug: Datenbankverbindung erfolgreich -->\n";
        } catch (\PDOException $e) {
            echo "<div class='alert alert-danger'>";
            echo "Datenbankfehler: " . $e->getMessage();
            echo "</div>";
        }
    }

    public function index() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        try {
            // Hole Kategorien aus der Datenbank
            $stmt = $this->db->query('SELECT * FROM categories ORDER BY type, name');
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Gruppiere Kategorien nach Typ
            $groupedCategories = [
                'expense' => [],
                'income' => []
            ];
            
            foreach ($categories as $category) {
                $groupedCategories[$category['type']][] = $category;
            }
            
            // Übergebe die Variablen an die View
            $viewData = [
                'categories' => $categories,
                'groupedCategories' => $groupedCategories
            ];
            
            // Extrahiere die Variablen für die View
            extract($viewData);
            
            // Lade die View
            $viewFile = VIEW_PATH . 'categories/index.php';
            if (!file_exists($viewFile)) {
                throw new \Exception('View-Datei nicht gefunden: ' . $viewFile);
            }
            
            include $viewFile;
            
        } catch (\Exception $e) {
            error_log("Fehler in CategoryController::index - " . $e->getMessage());
            echo "<div class='alert alert-danger'>";
            echo "Ein Fehler ist aufgetreten. Bitte kontaktieren Sie den Administrator.";
            echo "</div>";
        }
    }

    public function create() {
        include VIEW_PATH . 'categories/create.php';
    }

    public function store() {
        $name = $_POST['name'];
        $color = $_POST['color'];
        $type = $_POST['type'] ?? 'expense';
        $description = $_POST['description'] ?? '';
        
        if (empty($name)) {
            $_SESSION['error'] = 'Der Kategoriename ist erforderlich.';
            header('Location: ' . \Utils\Path::url('/categories/create'));
            exit;
        }

        $stmt = $this->db->prepare('INSERT INTO categories (name, color, type, description) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $color, $type, $description]);
        
        $_SESSION['success'] = 'Kategorie erfolgreich erstellt.';
        header('Location: ' . \Utils\Path::url('/categories'));
        exit;
    }

    public function edit() {
        $id = $_GET['id'];
        
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            $_SESSION['error'] = 'Kategorie nicht gefunden.';
            header('Location: ' . \Utils\Path::url('/categories'));
            exit;
        }
        
        include VIEW_PATH . 'categories/edit.php';
    }

    public function update() {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $color = $_POST['color'];
        $type = $_POST['type'] ?? 'expense';
        $description = $_POST['description'] ?? '';
        
        if (empty($name)) {
            $_SESSION['error'] = 'Der Kategoriename ist erforderlich.';
            header('Location: /categories/edit?id=' . $id);
            exit;
        }

        $stmt = $this->db->prepare('UPDATE categories SET name = ?, color = ?, type = ?, description = ? WHERE id = ?');
        $stmt->execute([$name, $color, $type, $description, $id]);
        
        $_SESSION['success'] = 'Kategorie erfolgreich aktualisiert.';
        header('Location: ' . \Utils\Path::url('/categories'));
        exit;
    }

    public function delete() {
        $id = $_GET['id'];
        
        // Prüfen, ob die Kategorie in Verwendung ist
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM expenses WHERE category_id = ?');
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['error'] = 'Diese Kategorie kann nicht gelöscht werden, da sie von ' . $count . ' Ausgaben verwendet wird.';
        } else {
            $stmt = $this->db->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Kategorie erfolgreich gelöscht.';
        }
        
        header('Location: ' . \Utils\Path::url('/categories'));
        exit;
    }
}
