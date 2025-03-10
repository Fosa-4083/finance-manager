<?php
/**
 * Datenbank-Setup-Skript
 * 
 * Dieses Skript erstellt die Datenbank-Struktur für die Finanzverwaltungsanwendung.
 */

echo "Datenbank-Setup wird gestartet...\n";

// Pfad zur Datenbank
$dbFile = __DIR__ . '/database/database.sqlite';
$backupDir = __DIR__ . '/database/backups';

// Backupverzeichnis erstellen, falls es nicht existiert
if (!is_dir($backupDir)) {
    echo "Erstelle Backup-Verzeichnis...\n";
    if (!mkdir($backupDir, 0755, true)) {
        echo "FEHLER: Konnte Backup-Verzeichnis nicht erstellen!\n";
        exit(1);
    }
}

// Prüfen, ob die Datenbank schon existiert
$createNewDb = true;
if (file_exists($dbFile)) {
    echo "HINWEIS: Die Datenbankdatei existiert bereits.\n";
    $confirmation = readline("Möchten Sie die bestehende Datenbank löschen und neu erstellen? (j/N): ");
    if (strtolower($confirmation) !== 'j') {
        echo "Setup abgebrochen. Die bestehende Datenbank wurde nicht verändert.\n";
        exit(0);
    }
    
    // Backup der bestehenden Datenbank erstellen
    $backupFile = $backupDir . '/pre_setup_' . date('Y-m-d_H-i-s') . '.sqlite';
    echo "Erstelle Backup der bestehenden Datenbank unter: $backupFile\n";
    if (!copy($dbFile, $backupFile)) {
        echo "WARNUNG: Konnte Backup nicht erstellen!\n";
    }
}

// Datenbankverbindung herstellen
try {
    echo "Verbinde zur Datenbank...\n";
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fremdschlüssel aktivieren
    $db->exec('PRAGMA foreign_keys = ON;');
    
    echo "Erstelle Tabellen...\n";
    
    // Benutzer-Tabelle
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME DEFAULT NULL
    )');
    
    // Kategorien-Tabelle
    $db->exec('CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        color TEXT DEFAULT "#6c757d",
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Projekte-Tabelle
    $db->exec('CREATE TABLE IF NOT EXISTS projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        budget DECIMAL(10,2) DEFAULT 0,
        status TEXT DEFAULT "aktiv",
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Ausgaben-Tabelle
    $db->exec('CREATE TABLE IF NOT EXISTS expenses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        category_id INTEGER NOT NULL,
        project_id INTEGER,
        description TEXT NOT NULL,
        value DECIMAL(10,2) NOT NULL,
        date DATE NOT NULL,
        afa BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
    )');
    
    // Ausgabenziele-Tabelle
    $db->exec('CREATE TABLE IF NOT EXISTS expense_goals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER,
        name TEXT NOT NULL,
        target_amount DECIMAL(10,2) NOT NULL,
        period TEXT NOT NULL, -- "monthly", "yearly"
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )');
    
    echo "Tabellen wurden erfolgreich erstellt.\n";
    
    // Standard-Benutzer erstellen
    $hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute(['Administrator', 'admin@example.com', $hashedPassword]);
    $adminId = $db->lastInsertId();
    
    echo "Administrator-Benutzer wurde erstellt:\n";
    echo "  E-Mail: admin@example.com\n";
    echo "  Passwort: admin\n";
    echo "WICHTIG: Bitte ändern Sie das Passwort nach dem ersten Login!\n";
    
    // Standard-Kategorien erstellen
    $categories = [
        ['Lebensmittel', '#28a745'],     // Grün
        ['Wohnen', '#007bff'],           // Blau
        ['Transport', '#fd7e14'],         // Orange
        ['Unterhaltung', '#6f42c1'],     // Lila
        ['Gesundheit', '#e83e8c'],       // Pink
        ['E: Gehalt', '#20c997'],        // Türkis
        ['E: Sonstige', '#17a2b8']       // Cyan
    ];
    
    $stmt = $db->prepare('INSERT INTO categories (name, color) VALUES (?, ?)');
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    
    echo "Standard-Kategorien wurden erstellt.\n";
    
    echo "\nDatenbank-Setup wurde erfolgreich abgeschlossen!\n";
    echo "Sie können sich jetzt mit dem Administrator-Konto anmelden.\n";
    
} catch (PDOException $e) {
    echo "FEHLER: " . $e->getMessage() . "\n";
    exit(1); 