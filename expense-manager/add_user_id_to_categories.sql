-- SQL-Skript zum Hinzufügen der user_id-Spalte zur Tabelle categories
-- Dieses Skript fügt eine user_id-Spalte zur Tabelle categories hinzu und erstellt einen Fremdschlüssel zur users-Tabelle

-- Spalte hinzufügen, falls sie noch nicht existiert
ALTER TABLE categories ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL;

-- Fremdschlüssel hinzufügen
-- Hinweis: Dieser Befehl könnte fehlschlagen, wenn der Fremdschlüssel bereits existiert
-- In diesem Fall können Sie die folgende Zeile auskommentieren oder ignorieren
ALTER TABLE categories ADD CONSTRAINT fk_categories_user_id FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE NO ACTION;

-- Bestätigung
SELECT 'Struktur der Tabelle categories wurde aktualisiert!' AS 'Status'; 