<?php
// Verbindung zur SQLite-Datenbank herstellen
try {
    $sqlite = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Fehler bei der Verbindung zur Datenbank: " . $e->getMessage());
}

// Tabelle für Kategorien erstellen
$sqlite->exec('CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    color TEXT NOT NULL,
    goal REAL DEFAULT 0
)');

// Tabelle für Ausgaben erstellen
$sqlite->exec('CREATE TABLE IF NOT EXISTS expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER,
    date TEXT NOT NULL,
    description TEXT,
    value REAL NOT NULL,
    is_subscription INTEGER DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(id)
)');

// Tabelle für Ausgabenziele erstellen
$sqlite->exec('CREATE TABLE IF NOT EXISTS expense_goals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER,
    year INTEGER NOT NULL,
    goal REAL NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    UNIQUE(category_id, year)
)');

echo "Datenbank erfolgreich erstellt!\n"; 