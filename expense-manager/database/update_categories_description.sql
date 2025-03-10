-- Backup der bestehenden Daten
CREATE TABLE categories_backup AS SELECT * FROM categories;

-- Tabelle neu erstellen mit description-Spalte
DROP TABLE categories;
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    color TEXT NOT NULL,
    type TEXT NOT NULL CHECK(type IN ('expense', 'income')) DEFAULT 'expense',
    description TEXT
);

-- Daten wiederherstellen
INSERT INTO categories (id, name, color, type)
SELECT id, name, color, type
FROM categories_backup;

-- Backup-Tabelle löschen
DROP TABLE categories_backup; 