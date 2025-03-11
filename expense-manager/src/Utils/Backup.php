<?php

namespace Utils;

/**
 * Klasse für Datenbank-Backups
 */
class Backup {
    /**
     * Pfad zur Datenbank
     */
    private string $dbPath;
    
    /**
     * Verzeichnis für Backups
     */
    private string $backupDir;
    
    /**
     * Konstruktor
     * 
     * @param string $dbPath Pfad zur Datenbank
     * @param string|null $backupDir Verzeichnis für Backups (optional)
     */
    public function __construct(string $dbPath, ?string $backupDir = null) {
        $this->dbPath = $dbPath;
        
        // Backup-Verzeichnis festlegen
        if ($backupDir === null) {
            $this->backupDir = dirname($dbPath) . '/backups';
        } else {
            $this->backupDir = $backupDir;
        }
        
        // Backup-Verzeichnis erstellen, falls es nicht existiert
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Erstellt ein Backup mit dem aktuellen Datum und der Uhrzeit
     * 
     * @return string Pfad zum erstellten Backup
     */
    public function createBackup(): string {
        // Prüfen, ob die Datenbank existiert
        if (!file_exists($this->dbPath)) {
            throw new \Exception("Datenbank existiert nicht: {$this->dbPath}");
        }
        
        // Backup-Dateiname generieren
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $this->backupDir . '/backup_' . $timestamp . '.sqlite';
        
        // Backup erstellen
        if (!copy($this->dbPath, $backupFile)) {
            throw new \Exception("Backup konnte nicht erstellt werden: {$backupFile}");
        }
        
        return $backupFile;
    }
    
    /**
     * Erstellt ein tägliches Backup, falls heute noch keines erstellt wurde
     * 
     * @return bool true, wenn ein Backup erstellt wurde, false sonst
     */
    public function createDailyBackup(): bool {
        $today = date('Y-m-d');
        $pattern = $this->backupDir . '/backup_' . $today . '_*.sqlite';
        
        // Prüfen, ob heute bereits ein Backup erstellt wurde
        if (count(glob($pattern)) > 0) {
            return false;
        }
        
        // Backup erstellen
        $this->createBackup();
        return true;
    }
    
    /**
     * Gibt eine Liste aller Backups zurück
     * 
     * @return array Liste der Backups mit Datum und Pfad
     */
    public function getBackups(): array {
        $pattern = $this->backupDir . '/backup_*.sqlite';
        $files = glob($pattern);
        
        $backups = [];
        foreach ($files as $file) {
            $filename = basename($file);
            // Datum aus dem Dateinamen extrahieren (Format: backup_YYYY-MM-DD_HH-II-SS.sqlite)
            if (preg_match('/backup_(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})\.sqlite/', $filename, $matches)) {
                $date = str_replace('_', ' ', $matches[1]);
                $date = str_replace('-', ':', $date, 2);
                $backups[] = [
                    'date' => $date,
                    'path' => $file,
                    'filename' => $filename
                ];
            }
        }
        
        // Nach Datum sortieren (neueste zuerst)
        usort($backups, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        
        return $backups;
    }
    
    /**
     * Stellt ein Backup wieder her
     * 
     * @param string $backupFile Pfad zum Backup
     * @return bool true bei Erfolg, false bei Fehler
     */
    public function restoreBackup(string $backupFile): bool {
        // Prüfen, ob das Backup existiert
        if (!file_exists($backupFile)) {
            throw new \Exception("Backup existiert nicht: {$backupFile}");
        }
        
        // Sicherheitsbackup erstellen
        $timestamp = date('Y-m-d_H-i-s');
        $safetyBackup = $this->backupDir . '/pre_restore_' . $timestamp . '.sqlite';
        
        if (file_exists($this->dbPath)) {
            if (!copy($this->dbPath, $safetyBackup)) {
                throw new \Exception("Sicherheitsbackup konnte nicht erstellt werden: {$safetyBackup}");
            }
        }
        
        // Backup wiederherstellen
        if (!copy($backupFile, $this->dbPath)) {
            throw new \Exception("Backup konnte nicht wiederhergestellt werden: {$backupFile}");
        }
        
        return true;
    }
    
    /**
     * Löscht ein Backup
     * 
     * @param string $backupFile Pfad zum Backup
     * @return bool true bei Erfolg, false bei Fehler
     */
    public function deleteBackup(string $backupFile): bool {
        // Prüfen, ob das Backup existiert
        if (!file_exists($backupFile)) {
            throw new \Exception("Backup existiert nicht: {$backupFile}");
        }
        
        // Backup löschen
        if (!unlink($backupFile)) {
            throw new \Exception("Backup konnte nicht gelöscht werden: {$backupFile}");
        }
        
        return true;
    }
    
    /**
     * Gibt das Backup-Verzeichnis zurück
     * 
     * @return string Backup-Verzeichnis
     */
    public function getBackupDir(): string {
        return $this->backupDir;
    }
} 