<?php

namespace Controllers;

use Models\Project;
use Models\Expense;

class ProjectController {
    private $project;
    private $expense;

    public function __construct() {
        $this->project = new Project();
        $this->expense = new Expense();
    }

    public function index() {
        $projects = $this->project->getAll();
        $processedProjects = [];
        
        // Debug-Ausgabe der ursprünglichen Projekte
        error_log("Projekte nach getAll():");
        foreach ($projects as $project) {
            error_log("Original - ID: " . $project['id'] . ", Name: " . $project['name']);
        }
        
        // Für jedes Projekt die Zusammenfassung abrufen, ohne Referenz zu verwenden
        foreach ($projects as $index => $project) {
            $projectId = $project['id'];
            
            // Prüfen, ob dieses Projekt bereits verarbeitet wurde
            $alreadyProcessed = false;
            foreach ($processedProjects as $proc) {
                if ($proc['id'] == $projectId) {
                    $alreadyProcessed = true;
                    break;
                }
            }
            
            if (!$alreadyProcessed) {
                $summary = $this->project->getProjectSummary($projectId);
                $project['total_expenses'] = $summary['total_expenses'] ?? 0;
                $project['expense_count'] = $summary['expense_count'] ?? 0;
                $project['budget_used_percent'] = $summary['budget_used_percent'] ?? 0;
                
                $processedProjects[] = $project;
            }
        }
        
        // Debug-Ausgabe der verarbeiteten Projekte
        error_log("Anzahl der verarbeiteten Projekte: " . count($processedProjects));
        foreach ($processedProjects as $project) {
            error_log("Verarbeitet - ID: " . $project['id'] . ", Name: " . $project['name']);
        }
        
        // Verwende die verarbeiteten Projekte für die Anzeige
        $projects = $processedProjects;
        
        include VIEW_PATH . 'projects/index.php';
    }

    public function create() {
        include VIEW_PATH . 'projects/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->project->name = $_POST['name'] ?? '';
            $this->project->description = $_POST['description'] ?? '';
            $this->project->start_date = $_POST['start_date'] ?? null;
            $this->project->end_date = $_POST['end_date'] ?? null;
            $this->project->budget = $_POST['budget'] ?? 0;
            $this->project->status = $_POST['status'] ?? 'aktiv';
            
            if (empty($this->project->name)) {
                $_SESSION['error'] = 'Der Projektname ist erforderlich.';
                header('Location: /projects/create');
                exit;
            }
            
            // Prüfen, ob ein Projekt mit diesem Namen bereits existiert
            if ($this->project->existsWithName($this->project->name)) {
                $_SESSION['error'] = 'Ein Projekt mit diesem Namen existiert bereits.';
                header('Location: /projects/create');
                exit;
            }
            
            if ($this->project->save()) {
                $_SESSION['success'] = 'Projekt erfolgreich erstellt.';
                header('Location: /projects');
                exit;
            } else {
                $_SESSION['error'] = 'Fehler beim Erstellen des Projekts.';
                header('Location: /projects/create');
                exit;
            }
        }
    }

    public function edit($id) {
        $project = $this->project->findById($id);
        if ($project) {
            include VIEW_PATH . 'projects/edit.php';
        } else {
            $_SESSION['error'] = 'Projekt nicht gefunden.';
            header('Location: /projects');
            exit;
        }
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->project->findById($id)) {
                $_SESSION['error'] = 'Projekt nicht gefunden.';
                header('Location: /projects');
                exit;
            }
            
            $this->project->name = $_POST['name'] ?? '';
            $this->project->description = $_POST['description'] ?? '';
            $this->project->start_date = $_POST['start_date'] ?? null;
            $this->project->end_date = $_POST['end_date'] ?? null;
            $this->project->budget = $_POST['budget'] ?? 0;
            $this->project->status = $_POST['status'] ?? 'aktiv';
            
            if (empty($this->project->name)) {
                $_SESSION['error'] = 'Der Projektname ist erforderlich.';
                header("Location: /projects/edit?id=$id");
                exit;
            }
            
            // Prüfen, ob ein anderes Projekt mit diesem Namen bereits existiert
            if ($this->project->existsWithName($this->project->name, $id)) {
                $_SESSION['error'] = 'Ein anderes Projekt mit diesem Namen existiert bereits.';
                header("Location: /projects/edit?id=$id");
                exit;
            }
            
            if ($this->project->save()) {
                $_SESSION['success'] = 'Projekt erfolgreich aktualisiert.';
                header('Location: /projects');
                exit;
            } else {
                $_SESSION['error'] = 'Fehler beim Aktualisieren des Projekts.';
                header("Location: /projects/edit/$id");
                exit;
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'Keine Projekt-ID angegeben.';
            header('Location: /projects');
            exit;
        }
        
        if ($this->project->findById($id)) {
            if ($this->project->delete()) {
                $_SESSION['success'] = 'Projekt erfolgreich gelöscht.';
            } else {
                $_SESSION['error'] = 'Fehler beim Löschen des Projekts.';
            }
        } else {
            $_SESSION['error'] = 'Projekt nicht gefunden.';
        }
        
        header('Location: /projects');
        exit;
    }

    public function show() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'Keine Projekt-ID angegeben.';
            header('Location: /projects');
            exit;
        }
        
        if ($this->project->findById($id)) {
            $expenses = $this->project->getProjectExpenses($id);
            $summary = $this->project->getProjectSummary($id);
            
            // Das Projekt-Objekt an die View übergeben
            $project = $this->project;
            
            include VIEW_PATH . 'projects/show.php';
        } else {
            $_SESSION['error'] = 'Projekt nicht gefunden.';
            header('Location: /projects');
            exit;
        }
    }
} 