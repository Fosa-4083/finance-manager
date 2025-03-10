-- Benutzer-Tabelle erstellen
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL
);

-- Bestehende Tabellen um user_id erweitern
ALTER TABLE categories ADD COLUMN user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE expenses ADD COLUMN user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE expense_goals ADD COLUMN user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE projects ADD COLUMN user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE;

-- Index für schnellere Abfragen
CREATE INDEX idx_categories_user_id ON categories(user_id);
CREATE INDEX idx_expenses_user_id ON expenses(user_id);
CREATE INDEX idx_expense_goals_user_id ON expense_goals(user_id);
CREATE INDEX idx_projects_user_id ON projects(user_id);

-- Standardbenutzer erstellen (Passwort: admin123)
INSERT INTO users (email, password, name) 
VALUES ('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- Bestehende Daten dem Standardbenutzer zuweisen
UPDATE categories SET user_id = 1;
UPDATE expenses SET user_id = 1;
UPDATE expense_goals SET user_id = 1;
UPDATE projects SET user_id = 1; 