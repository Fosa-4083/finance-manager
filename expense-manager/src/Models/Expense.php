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
                    INSERT INTO expenses (category_id, project_id, date, description, value, afa) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ');
                
                $result = $stmt->execute([
                    $this->category_id,
                    $this->project_id,
                    $this->date,
                    $this->description,
                    $this->value,
                    $this->afa ? 1 : 0
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

    // Getter und Setter für die Eigenschaften
    public function getId() {
        return $this->id;
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