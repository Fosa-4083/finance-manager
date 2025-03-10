-- Tabelle für Benutzer
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL
);

-- Tabelle für Kategorien
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    color TEXT NOT NULL DEFAULT '#000000',
    goal DECIMAL(10,2),
    user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle für Ausgaben
CREATE TABLE IF NOT EXISTS expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    value DECIMAL(10,2) NOT NULL,
    afa BOOLEAN NOT NULL DEFAULT 0,
    project_id INTEGER DEFAULT NULL REFERENCES projects(id) ON DELETE SET NULL,
    user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Tabelle für Ausgabenziele
CREATE TABLE IF NOT EXISTS expense_goals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    year INTEGER NOT NULL,
    goal DECIMAL(10,2) NOT NULL,
    user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Tabelle für Projekte
CREATE TABLE IF NOT EXISTS projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    budget DECIMAL(10,2),
    status TEXT DEFAULT 'aktiv',
    user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE
);

-- Index für schnellere Abfragen
CREATE INDEX IF NOT EXISTS idx_categories_user_id ON categories(user_id);
CREATE INDEX IF NOT EXISTS idx_expenses_user_id ON expenses(user_id);
CREATE INDEX IF NOT EXISTS idx_expense_goals_user_id ON expense_goals(user_id);
CREATE INDEX IF NOT EXISTS idx_projects_user_id ON projects(user_id); 