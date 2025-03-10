<?php

namespace Models;

class ExpenseGoal {
    private $id;
    private $category_id;
    private $year;
    private $goal;

    public function __construct($category_id, $year, $goal) {
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