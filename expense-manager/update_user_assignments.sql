-- SQL-Skript zur Zuordnung aller Einträge zum Benutzer mit ID 1
-- Dieses Skript aktualisiert alle Einträge in den Tabellen expenses, projects, categories und expense_goals,
-- bei denen die user_id NULL ist, und setzt sie auf den Benutzer mit ID 1.

-- Prüfen, ob der Benutzer mit ID 1 existiert
SELECT COUNT(*) AS 'Benutzer mit ID 1 existiert' FROM users WHERE id = 1;

-- Ausgaben (expenses) dem Benutzer zuordnen
UPDATE expenses SET user_id = 1 WHERE user_id IS NULL;

-- Projekte (projects) dem Benutzer zuordnen
UPDATE projects SET user_id = 1 WHERE user_id IS NULL;

-- Ausgabenziele (expense_goals) dem Benutzer zuordnen
UPDATE expense_goals SET user_id = 1 WHERE user_id IS NULL;

-- Kategorien (categories) dem Benutzer zuordnen
-- Dieser Befehl wird nur funktionieren, wenn die Spalte user_id in der Tabelle categories existiert
-- Falls die Spalte nicht existiert, führen Sie zuerst das Skript add_user_id_to_categories.sql aus
UPDATE categories SET user_id = 1 WHERE user_id IS NULL;

-- Statistik ausgeben
SELECT 
    (SELECT COUNT(*) FROM expenses WHERE user_id = 1) AS 'Zugeordnete Ausgaben',
    (SELECT COUNT(*) FROM projects WHERE user_id = 1) AS 'Zugeordnete Projekte',
    (SELECT COUNT(*) FROM expense_goals WHERE user_id = 1) AS 'Zugeordnete Ausgabenziele',
    (SELECT COUNT(*) FROM categories WHERE user_id = 1) AS 'Zugeordnete Kategorien';

-- Bestätigung
SELECT 'Zuordnung zum Benutzer mit ID 1 abgeschlossen!' AS 'Status'; 