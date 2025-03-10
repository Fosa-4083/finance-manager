<?php

namespace Models;

class Project {
    private $db;
    public $id;
    public $name;
    public $description;
    public $start_date;
    public $end_date;
    public $budget;
    public $status;

    public function __construct() {
        $this->db = new \PDO('sqlite:' . __DIR__ . '/../../database/database.sqlite');
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function save() {
        try {
            // Sicherstellen, dass das Budget eine Zahl ist
            $this->budget = is_numeric($this->budget) ? (float)$this->budget : 0;
            
            if ($this->id) {
                // Update
                $stmt = $this->db->prepare('
                    UPDATE projects 
                    SET name = ?, description = ?, start_date = ?, end_date = ?, budget = ?, status = ?
                    WHERE id = ?
                ');
                
                return $stmt->execute([
                    $this->name,
                    $this->description,
                    $this->start_date,
                    $this->end_date,
                    $this->budget,
                    $this->status,
                    $this->id
                ]);
            } else {
                // Insert
                $stmt = $this->db->prepare('
                    INSERT INTO projects (name, description, start_date, end_date, budget, status) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ');
                
                $result = $stmt->execute([
                    $this->name,
                    $this->description,
                    $this->start_date,
                    $this->end_date,
                    $this->budget,
                    $this->status ?: 'aktiv'
                ]);
                
                if ($result) {
                    $this->id = $this->db->lastInsertId();
                }
                
                return $result;
            }
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function delete() {
        try {
            $stmt = $this->db->prepare('DELETE FROM projects WHERE id = ?');
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function findById($id) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM projects WHERE id = ?');
            $stmt->execute([$id]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($project) {
                $this->id = $project['id'];
                $this->name = $project['name'];
                $this->description = $project['description'];
                $this->start_date = $project['start_date'];
                $this->end_date = $project['end_date'];
                $this->budget = is_numeric($project['budget']) ? (float)$project['budget'] : 0;
                $this->status = $project['status'];
                return $this;
            }
            
            return null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function getAll() {
        try {
            // Verwende DISTINCT, um sicherzustellen, dass jedes Projekt nur einmal geladen wird
            // Sortiere nach ID, um eine konsistente Reihenfolge zu gewährleisten
            $stmt = $this->db->query('SELECT * FROM projects ORDER BY id');
            $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Debug-Ausgabe
            error_log("Projekte aus der Datenbank:");
            foreach ($projects as $project) {
                error_log("ID: " . $project['id'] . ", Name: " . $project['name']);
            }
            
            // Entferne Duplikate basierend auf der ID
            $uniqueProjects = [];
            $seenIds = [];
            
            foreach ($projects as $project) {
                if (!in_array($project['id'], $seenIds)) {
                    $seenIds[] = $project['id'];
                    $uniqueProjects[] = $project;
                }
            }
            
            return $uniqueProjects;
        } catch (\PDOException $e) {
            error_log("Fehler beim Laden der Projekte: " . $e->getMessage());
            return [];
        }
    }

    public function getActiveProjects() {
        try {
            $stmt = $this->db->query('SELECT * FROM projects WHERE status = "aktiv" ORDER BY name');
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getProjectExpenses($projectId) {
        try {
            $stmt = $this->db->prepare('
                SELECT e.*, c.name as category_name, c.color as category_color
                FROM expenses e
                JOIN categories c ON e.category_id = c.id
                WHERE e.project_id = ?
                ORDER BY e.date DESC
            ');
            $stmt->execute([$projectId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getProjectSummary($projectId) {
        try {
            $stmt = $this->db->prepare('
                SELECT 
                    SUM(value) as total_expenses,
                    COUNT(*) as expense_count
                FROM expenses
                WHERE project_id = ?
            ');
            $stmt->execute([$projectId]);
            $summary = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Standardwerte für den Fall, dass keine Daten gefunden wurden
            if (!$summary) {
                $summary = [
                    'total_expenses' => 0,
                    'expense_count' => 0
                ];
            }
            
            // Projekt-Details abrufen
            $projectFound = $this->findById($projectId);
            
            // Sicherstellen, dass die Werte numerisch sind
            $summary['total_expenses'] = is_numeric($summary['total_expenses']) ? (float)$summary['total_expenses'] : 0;
            $summary['expense_count'] = (int)$summary['expense_count'];
            
            // Budget-Nutzung berechnen
            $summary['budget'] = is_numeric($this->budget) ? (float)$this->budget : 0;
            $summary['budget_used_percent'] = $summary['budget'] > 0 ? 
                round((abs($summary['total_expenses']) / $summary['budget']) * 100, 2) : 0;
            
            return $summary;
        } catch (\PDOException $e) {
            // Standardwerte zurückgeben, wenn ein Fehler auftritt
            return [
                'total_expenses' => 0,
                'expense_count' => 0,
                'budget' => 0,
                'budget_used_percent' => 0
            ];
        }
    }

    /**
     * Prüft, ob ein Projekt mit dem angegebenen Namen bereits existiert
     * 
     * @param string $name Der zu prüfende Projektname
     * @param int|null $excludeId Optional: ID eines Projekts, das von der Prüfung ausgeschlossen werden soll (für Updates)
     * @return bool True, wenn ein Projekt mit diesem Namen existiert, sonst False
     */
    public function existsWithName($name, $excludeId = null) {
        try {
            $sql = 'SELECT COUNT(*) FROM projects WHERE name = ?';
            $params = [$name];
            
            if ($excludeId !== null) {
                $sql .= ' AND id != ?';
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }
} 