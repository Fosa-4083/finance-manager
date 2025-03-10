<?php

// Verbindung zur SQLite-Datenbank herstellen
$sqlite = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Starte Transaktion
    $sqlite->beginTransaction();

    // Erstelle Tabellen
    $sqlite->exec('CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        color TEXT NOT NULL,
        goal REAL DEFAULT 0
    )');

    $sqlite->exec('CREATE TABLE IF NOT EXISTS expenses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER,
        date TEXT NOT NULL,
        description TEXT,
        value REAL NOT NULL,
        is_subscription INTEGER DEFAULT 0,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )');

    $sqlite->exec('CREATE TABLE IF NOT EXISTS expense_goals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER,
        year INTEGER NOT NULL,
        goal REAL NOT NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id),
        UNIQUE(category_id, year)
    )');

    // Commit Transaktion
    $sqlite->commit();
    echo "Datenbank erfolgreich erstellt!\n";

} catch (Exception $e) {
    // Bei Fehler: Rollback
    $sqlite->rollBack();
    echo "Fehler beim Erstellen der Datenbank: " . $e->getMessage() . "\n";
} 