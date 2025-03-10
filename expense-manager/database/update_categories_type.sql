-- Backup der bestehenden Daten
CREATE TABLE categories_backup AS SELECT * FROM categories;

-- Tabelle neu erstellen mit type-Spalte
DROP TABLE categories;
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    color TEXT NOT NULL,
    type TEXT NOT NULL CHECK(type IN ('expense', 'income')) DEFAULT 'expense'
);

-- Daten wiederherstellen und Typ basierend auf Kategorie-Namen setzen
INSERT INTO categories (id, name, color, type)
SELECT 
    id, 
    name, 
    color,
    CASE 
        WHEN name LIKE 'E: %' THEN 'income'
        ELSE 'expense'
    END as type
FROM categories_backup;

-- Backup-Tabelle löschen
DROP TABLE categories_backup;

-- Aktualisiere die Kategorienamen (entferne die Präfixe)
UPDATE categories 
SET name = SUBSTR(name, 3) 
WHERE name LIKE '_: %'; 