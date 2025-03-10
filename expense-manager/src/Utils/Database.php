<?php

namespace Utils;

/**
 * PDO-Wrapper mit Backup-Funktionalität
 */
class Database extends \PDO {
    /**
     * Backup-Manager
     */
    private Backup $backupManager;
    
    /**
     * Flag, ob heute bereits ein Backup erstellt wurde
     */
    private bool $dailyBackupCreated = false;
    
    /**
     * Flag zum Deaktivieren des Backup-Mechanismus (für Tests)
     */
    private bool $backupsEnabled = true;
    
    /**
     * Flag zur Erkennung schreibender Operationen
     */
    private bool $isWriteOperation = false;
    
    /**
     * Erstellt eine neue Datenbankverbindung
     * 
     * @param string $dsn DSN für die Datenbankverbindung
     * @param string|null $username Benutzername (wird für SQLite nicht benötigt)
     * @param string|null $password Passwort (wird für SQLite nicht benötigt)
     * @param array $options PDO-Optionen
     */
    public function __construct(string $dsn, ?string $username = null, ?string $password = null, array $options = []) {
        parent::__construct($dsn, $username, $password, $options);
        
        // SQLite-Datei aus DSN extrahieren
        $dbFile = preg_replace('/^sqlite:/', '', $dsn);
        
        // Backup-Manager initialisieren
        $this->backupManager = new Backup($dbFile);
    }
    
    /**
     * Erstellt ein Backup vor der ersten schreibenden Operation des Tages
     */
    private function createBackupIfNeeded(): void {
        if (!$this->backupsEnabled || $this->dailyBackupCreated || !$this->isWriteOperation) {
            return;
        }
        
        try {
            $this->dailyBackupCreated = $this->backupManager->createDailyBackup();
        } catch (\Exception $e) {
            // Fehler beim Backup-Erstellen - Ausnahme nicht weiterwerfen, 
            // damit die Anwendung weiterläuft
            error_log('Backup-Fehler: ' . $e->getMessage());
        }
    }
    
    /**
     * Überschreibt exec, um Backups zu erstellen
     */
    public function exec(string $statement): int|false {
        $this->isWriteOperation = $this->isWriteOperation($statement);
        $this->createBackupIfNeeded();
        return parent::exec($statement);
    }
    
    /**
     * Überschreibt prepare, um Backups zu erstellen
     */
    public function prepare(string $query, array $options = []): \PDOStatement|false {
        $this->isWriteOperation = $this->isWriteOperation($query);
        $statement = parent::prepare($query, $options);
        
        if ($statement && $this->isWriteOperation) {
            $originalExecute = [$statement, 'execute'];
            $self = $this;
            
            // execute-Methode umschreiben, um Backup vor der Ausführung zu erstellen
            $statement->execute = function(array $params = null) use ($originalExecute, $self) {
                $self->createBackupIfNeeded();
                return call_user_func($originalExecute, $params);
            };
        }
        
        return $statement;
    }
    
    /**
     * Überschreibt query, um Backups zu erstellen
     */
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): \PDOStatement|false {
        $this->isWriteOperation = $this->isWriteOperation($query);
        $this->createBackupIfNeeded();
        
        if ($fetchMode !== null) {
            return parent::query($query, $fetchMode, ...$fetchModeArgs);
        }
        
        return parent::query($query);
    }
    
    /**
     * Prüft, ob ein SQL-Statement eine schreibende Operation ist
     */
    private function isWriteOperation(string $sql): bool {
        $sql = trim(strtoupper($sql));
        return preg_match('/^(INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|REPLACE)/i', $sql) === 1;
    }
    
    /**
     * Deaktiviert Backups (für Tests)
     */
    public function disableBackups(): void {
        $this->backupsEnabled = false;
    }
    
    /**
     * Aktiviert Backups
     */
    public function enableBackups(): void {
        $this->backupsEnabled = true;
    }
    
    /**
     * Gibt den Backup-Manager zurück
     */
    public function getBackupManager(): Backup {
        return $this->backupManager;
    }
} 