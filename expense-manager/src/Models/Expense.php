<?php

namespace Models;

use PDO;

class Expense extends BaseModel {
    public $id;
    public $category_id;
    public $project_id;
    public $date;
    public $description;
    public $value;
    public $afa;

    public function __construct($db = null) {
        // Basisklassen-Konstruktor aufrufen
        parent::__construct($db);
    }

    public function save() {
        try {
            if ($this->id) {
                // Update
                $stmt = $this->db->prepare('
                    UPDATE expenses 
                    SET category_id = ?, project_id = ?, date = ?, description = ?, value = ?, afa = ?
                    WHERE id = ?
                ');
                
                error_log("Expense::save - Update-Query mit Parametern: " . 
                          "category_id={$this->category_id}, " . 
                          "project_id=" . ($this->project_id ?? 'NULL') . ", " . 
                          "date={$this->date}, " . 
                          "description={$this->description}, " . 
                          "value={$this->value}, " . 
                          "afa=" . ($this->afa ? 1 : 0) . ", " . 
                          "id={$this->id}");
                
                return $stmt->execute([
                    $this->category_id,
                    $this->project_id,
                    $this->date,
                    $this->description,
                    $this->value,
                    $this->afa ? 1 : 0,
                    $this->id
                ]);
            } else {
                // Insert
                $stmt = $this->db->prepare('
                    INSERT INTO expenses (category_id, project_id, date, description, value, afa, user_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                
                // Benutzer-ID aus der Session holen
                $userId = null;
                if (isset($_SESSION['user_id'])) {
                    $userId = $_SESSION['user_id'];
                }
                
                error_log("Expense::save - Insert-Query mit Parametern: " . 
                          "category_id={$this->category_id}, " . 
                          "project_id=" . ($this->project_id ?? 'NULL') . ", " . 
                          "date={$this->date}, " . 
                          "description={$this->description}, " . 
                          "value={$this->value}, " . 
                          "afa=" . ($this->afa ? 1 : 0) . ", " . 
                          "user_id=" . ($userId ?? 'NULL'));
                
                $result = $stmt->execute([
                    $this->category_id,
                    $this->project_id,
                    $this->date,
                    $this->description,
                    $this->value,
                    $this->afa ? 1 : 0,
                    $userId
                ]);
                
                if ($result) {
                    $this->id = $this->db->lastInsertId();
                    error_log("Expense::save - Insert erfolgreich, neue ID: {$this->id}");
                } else {
                    error_log("Expense::save - Insert fehlgeschlagen");
                }
                
                return $result;
            }
        } catch (\PDOException $e) {
            error_log("Expense::save - PDOException: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Aktualisiert eine bestehende Ausgabe
     * 
     * @return bool Gibt true zurück, wenn die Aktualisierung erfolgreich war, sonst false
     */
    public function update() {
        // Stellt sicher, dass eine ID vorhanden ist
        if (!$this->id) {
            error_log("Expense::update - Fehler: Keine ID vorhanden");
            return false;
        }
        
        // Ruft die save()-Methode auf, die das Update durchführt
        return $this->save();
    }

    public function findById($id) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM expenses WHERE id = ?');
            $stmt->execute([$id]);
            $expense = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($expense) {
                $this->id = $expense['id'];
                $this->category_id = $expense['category_id'];
                $this->project_id = $expense['project_id'];
                $this->date = $expense['date'];
                $this->description = $expense['description'];
                $this->value = $expense['value'];
                $this->afa = $expense['afa'];
                return true;
            }
            
            return false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Löscht eine Ausgabe aus der Datenbank
     * 
     * @return bool Gibt true zurück, wenn das Löschen erfolgreich war, sonst false
     */
    public function delete() {
        try {
            // Stellt sicher, dass eine ID vorhanden ist
            if (!$this->id) {
                error_log("Expense::delete - Fehler: Keine ID vorhanden");
                return false;
            }
            
            $stmt = $this->db->prepare('DELETE FROM expenses WHERE id = ?');
            return $stmt->execute([$this->id]);
        } catch (\PDOException $e) {
            error_log("Expense::delete - PDOException: " . $e->getMessage());
            return false;
        }
    }

    // Getter und Setter für die Eigenschaften
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
    }

    public function getCategoryId() {
        return $this->category_id;
    }

    public function setCategoryId($category_id) {
        $this->category_id = $category_id;
    }

    public function getProjectId() {
        return $this->project_id;
    }

    public function setProjectId($project_id) {
        $this->project_id = $project_id;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getAfa() {
        return $this->afa;
    }

    public function setAfa($afa) {
        $this->afa = $afa;
    }
} 