<?php

namespace Models;

use PDO;

class ExpenseGoal extends BaseModel {
    private $id;
    private $category_id;
    private $year;
    private $goal;

    public function __construct($db = null, $category_id = null, $year = null, $goal = null) {
        // Basisklassen-Konstruktor aufrufen
        parent::__construct($db);
        
        // Eigenschaften setzen
        $this->category_id = $category_id;
        $this->year = $year;
        $this->goal = $goal;
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

    public function getYear() {
        return $this->year;
    }

    public function setYear($year) {
        $this->year = $year;
    }

    public function getGoal() {
        return $this->goal;
    }

    public function setGoal($goal) {
        $this->goal = $goal;
    }
} 