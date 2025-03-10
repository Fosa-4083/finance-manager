<?php

// Migration zur Erstellung der Projekttabelle und Aktualisierung der Ausgabentabelle

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tabelle für Projekte erstellen
    $db->exec('
        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            start_date DATE,
            end_date DATE,
            budget DECIMAL(10,2),
            status TEXT DEFAULT "aktiv"
        )
    ');

    // Spalte für Projekt-ID zur Ausgabentabelle hinzufügen
    $db->exec('
        ALTER TABLE expenses ADD COLUMN project_id INTEGER NULL;
    ');
    
    // Foreign Key Constraint hinzufügen
    $db->exec('
        PRAGMA foreign_keys = ON;
        CREATE TABLE expenses_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            project_id INTEGER NULL,
            date DATE NOT NULL,
            description TEXT,
            value DECIMAL(10,2) NOT NULL,
            is_subscription BOOLEAN NOT NULL DEFAULT 0,
            FOREIGN KEY (category_id) REFERENCES categories(id),
            FOREIGN KEY (project_id) REFERENCES projects(id)
        );
        
        INSERT INTO expenses_new (id, category_id, date, description, value, is_subscription)
        SELECT id, category_id, date, description, value, is_subscription FROM expenses;
        
        DROP TABLE expenses;
        ALTER TABLE expenses_new RENAME TO expenses;
    ');

    echo "Migration erfolgreich: Projekttabelle erstellt und Ausgabentabelle aktualisiert.\n";
} catch (PDOException $e) {
    echo "Fehler bei der Migration: " . $e->getMessage() . "\n";
} 