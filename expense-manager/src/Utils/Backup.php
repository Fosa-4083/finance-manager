<?php

namespace Utils;

/**
 * Klasse zur Verwaltung von Datenbank-Backups
 */
class Backup {
    /**
     * Pfad zum Backup-Verzeichnis
     */
    private string $backupDir;
    
    /**
     * Name der Datenbank-Datei
     */
    private string $databaseFile;
    
    /**
     * Maximale Anzahl der aufzubewahrenden Backups
     */
    private int $maxBackups;
    
    /**
     * Konstruktor
     * 
     * @param string $databaseFile Pfad zur Datenbank-Datei
     * @param string $backupDir Pfad zum Backup-Verzeichnis
     * @param int $maxBackups Maximale Anzahl der aufzubewahrenden Backups
     */
    public function __construct(string $databaseFile, string $backupDir = null, int $maxBackups = 30) {
        $this->databaseFile = $databaseFile;
        $this->backupDir = $backupDir ?? dirname($databaseFile) . '/backups';
        $this->maxBackups = $maxBackups;
        
        // Backup-Verzeichnis erstellen, falls es nicht existiert
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Erstellt ein Backup der Datenbank, falls heute noch keines erstellt wurde
     * 
     * @return bool True, wenn ein Backup erstellt wurde, False, wenn heute bereits ein Backup existiert
     */
    public function createDailyBackup(): bool {
        $today = date('Y-m-d');
        $backupFilename = $this->backupDir . '/database_' . $today . '.sqlite';
        
        // Prüfen, ob heute bereits ein Backup erstellt wurde
        if (file_exists($backupFilename)) {
            return false;
        }
        
        // Backup erstellen
        if (!copy($this->databaseFile, $backupFilename)) {
            throw new \Exception('Fehler beim Erstellen des Backups.');
        }
        
        // Alte Backups aufräumen
        $this->cleanupOldBackups();
        
        return true;
    }
    
    /**
     * Löscht alte Backups, wenn mehr als die maximale Anzahl vorhanden sind
     */
    private function cleanupOldBackups(): void {
        $backups = glob($this->backupDir . '/database_*.sqlite');
        
        // Nach Datum sortieren (älteste zuerst)
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // Älteste Backups löschen, wenn mehr als $maxBackups vorhanden sind
        $countToDelete = count($backups) - $this->maxBackups;
        if ($countToDelete > 0) {
            for ($i = 0; $i < $countToDelete; $i++) {
                unlink($backups[$i]);
            }
        }
    }
    
    /**
     * Gibt eine Liste aller Backups zurück
     * 
     * @return array Liste der Backup-Dateien mit Datum und Dateigröße
     */
    public function listBackups(): array {
        $backups = glob($this->backupDir . '/database_*.sqlite');
        $result = [];
        
        foreach ($backups as $backup) {
            $filename = basename($backup);
            $date = str_replace(['database_', '.sqlite'], '', $filename);
            $result[] = [
                'filename' => $filename,
                'date' => $date,
                'size' => $this->formatBytes(filesize($backup)),
                'path' => $backup
            ];
        }
        
        // Nach Datum sortieren (neueste zuerst)
        usort($result, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        
        return $result;
    }
    
    /**
     * Stellt ein Backup wieder her
     * 
     * @param string $backupFile Pfad zur Backup-Datei
     * @return bool True, wenn das Backup erfolgreich wiederhergestellt wurde
     */
    public function restoreBackup(string $backupFile): bool {
        // Vor der Wiederherstellung ein Sicherungs-Backup erstellen
        $timestamp = date('Y-m-d_H-i-s');
        $preRestoreBackup = $this->backupDir . '/pre_restore_' . $timestamp . '.sqlite';
        
        if (!copy($this->databaseFile, $preRestoreBackup)) {
            throw new \Exception('Fehler beim Erstellen des Sicherungs-Backups vor der Wiederherstellung.');
        }
        
        // Backup wiederherstellen
        if (!copy($backupFile, $this->databaseFile)) {
            throw new \Exception('Fehler beim Wiederherstellen des Backups.');
        }
        
        return true;
    }
    
    /**
     * Formatiert Bytes in menschenlesbare Größen
     * 
     * @param int $bytes Anzahl der Bytes
     * @param int $precision Anzahl der Nachkommastellen
     * @return string Formatierte Größe mit Einheit
     */
    private function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Gibt das Backup-Verzeichnis zurück
     * 
     * @return string Pfad zum Backup-Verzeichnis
     */
    public function getBackupDir(): string {
        return $this->backupDir;
    }
    
    /**
     * Gibt die maximale Anzahl der aufzubewahrenden Backups zurück
     * 
     * @return int Maximale Anzahl der Backups
     */
    public function getMaxBackups(): int {
        return $this->maxBackups;
    }
} 