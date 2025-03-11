<?php

namespace Utils;

/**
 * PDO-Wrapper für MariaDB/MySQL
 */
class Database extends \PDO {
    /**
     * Datenbankname
     */
    private string $dbName;
    
    /**
     * Erstellt eine neue Datenbankverbindung
     * 
     * @param string|null $dsn DSN für die Datenbankverbindung (optional)
     * @param string|null $username Benutzername
     * @param string|null $password Passwort
     * @param array $options PDO-Optionen
     */
    public function __construct(?string $dsn = null, ?string $username = null, ?string $password = null, array $options = []) {
        // Konfiguration laden
        $config = require __DIR__ . '/../../config/database.php';
        $mysqlConfig = $config['mysql'];
        
        // DSN erstellen, falls nicht angegeben
        if ($dsn === null) {
            $this->dbName = $mysqlConfig['database'];
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $mysqlConfig['host'],
                $mysqlConfig['port'],
                $mysqlConfig['database'],
                $mysqlConfig['charset']
            );
            $username = $mysqlConfig['username'];
            $password = $mysqlConfig['password'];
        } else {
            // Datenbankname aus DSN extrahieren
            if (preg_match('/dbname=([^;]+)/', $dsn, $matches)) {
                $this->dbName = $matches[1];
            } else {
                $this->dbName = $mysqlConfig['database'];
            }
        }
        
        // Verbindung herstellen
        parent::__construct($dsn, $username, $password);
        
        // Attribute nach der Verbindung setzen
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    }
    
    /**
     * Überschreibt exec, um Fehlerbehandlung zu verbessern
     */
    public function exec(string $statement): int|false {
        try {
            return parent::exec($statement);
        } catch (\PDOException $e) {
            $this->handleError($e, $statement);
            return false;
        }
    }
    
    /**
     * Überschreibt prepare, um Fehlerbehandlung zu verbessern
     */
    public function prepare(string $query, array $options = []): \PDOStatement|false {
        try {
            return parent::prepare($query, $options);
        } catch (\PDOException $e) {
            $this->handleError($e, $query);
            return false;
        }
    }
    
    /**
     * Überschreibt query, um Fehlerbehandlung zu verbessern
     */
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): \PDOStatement|false {
        try {
            if ($fetchMode !== null) {
                return parent::query($query, $fetchMode, ...$fetchModeArgs);
            }
            return parent::query($query);
        } catch (\PDOException $e) {
            $this->handleError($e, $query);
            return false;
        }
    }
    
    /**
     * Fehlerbehandlung für Datenbankoperationen
     */
    private function handleError(\PDOException $e, string $query): void {
        // Fehler protokollieren
        error_log("Datenbankfehler: " . $e->getMessage() . " in Query: " . $query);
        
        // Fehler weiterwerfen
        throw $e;
    }
    
    /**
     * Gibt den Datenbanknamen zurück
     */
    public function getDbName(): string {
        return $this->dbName;
    }
} 