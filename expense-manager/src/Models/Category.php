<?php

namespace Models;

class Category {
    private $id;
    private $name;
    private $description;
    private $color;
    private $goal;

    public function __construct($name, $description = null, $color = null, $goal = null) {
        $this->name = $name;
        $this->description = $description;
        $this->color = $color;
        $this->goal = $goal;
    }

    // Getter und Setter für die Eigenschaften
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getColor() {
        return $this->color;
    }

    public function setColor($color) {
        $this->color = $color;
    }

    public function getGoal() {
        return $this->goal;
    }

    public function setGoal($goal) {
        $this->goal = $goal;
    }
} 