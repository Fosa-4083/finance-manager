<?php
/**
 * Datenbank-Setup-Skript für MariaDB
 * 
 * Dieses Skript erstellt die Datenbankstruktur für die Anwendung.
 * Es erstellt die Tabellen für Benutzer, Kategorien, Projekte, Ausgaben und Ausgabenziele.
 */

// Konfiguration laden
require_once __DIR__ . '/config/database.php';

// Datenbankverbindung herstellen
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
    
    echo "Verbindung zur Datenbank hergestellt.\n";
    
    // Prüfen, ob die Datenbank bereits existiert
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $databaseExists = $stmt->rowCount() > 0;
    
    if ($databaseExists) {
        echo "WARNUNG: Die Datenbank '" . DB_NAME . "' existiert bereits.\n";
        echo "Möchten Sie die bestehende Datenbank überschreiben? (j/n): ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($line) !== 'j') {
            echo "Setup abgebrochen.\n";
            exit;
        }
        
        // Datenbank löschen
        $pdo->exec("DROP DATABASE `" . DB_NAME . "`");
        echo "Bestehende Datenbank gelöscht.\n";
    }
    
    // Datenbank erstellen
    $pdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Datenbank '" . DB_NAME . "' erstellt.\n";
    
    // Datenbank auswählen
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // Tabellen erstellen
    $sql = file_get_contents(__DIR__ . '/mariadb_import.sql');
    $pdo->exec($sql);
    
    echo "Datenbankstruktur erfolgreich erstellt.\n";
    echo "Ein Admin-Benutzer wurde erstellt mit:\n";
    echo "E-Mail: admin@example.com\n";
    echo "Passwort: admin\n";
    echo "Bitte ändern Sie das Passwort nach dem ersten Login!\n";
    
} catch (PDOException $e) {
    die("Fehler bei der Datenbankeinrichtung: " . $e->getMessage() . "\n");
} 