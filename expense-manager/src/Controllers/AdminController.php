<?php

namespace Controllers;

use Utils\Database;
use Utils\Path;

class AdminController {
    private Database $db;
    
    public function __construct(Database $db = null) {
        if ($db === null) {
            $dbPath = __DIR__ . '/../../database/database.sqlite';
            $this->db = new Database('sqlite:' . $dbPath);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } else {
            $this->db = $db;
        }
    }
    
    /**
     * Zeigt die Backup-Verwaltung an
     */
    public function backups() {
        // Prüfen, ob Benutzer Admin ist
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = 'Sie haben keine Berechtigung, diese Seite aufzurufen.';
            header('Location: ' . Path::url('/'));
            exit;
        }
        
        // Backups abrufen
        $backupManager = $this->db->getBackupManager();
        $backups = $backupManager->listBackups();
        
        // Erfolgs- oder Fehlermeldung aus der Session holen
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        
        // Session-Variablen löschen
        unset($_SESSION['success']);
        unset($_SESSION['error']);
        
        // View anzeigen
        include VIEW_PATH . 'admin/backups.php';
    }
    
    /**
     * Erstellt manuell ein Backup
     */
    public function createBackup() {
        // Prüfen, ob Benutzer Admin ist
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = 'Sie haben keine Berechtigung, diese Aktion auszuführen.';
            header('Location: ' . Path::url('/'));
            exit;
        }
        
        try {
            $backupManager = $this->db->getBackupManager();
            $timestamp = date('Y-m-d_H-i-s');
            $backupFilename = dirname($this->db->getBackupManager()->getBackupDir()) . '/backups/manual_' . $timestamp . '.sqlite';
            
            // Backup-Verzeichnis erstellen, falls es nicht existiert
            $backupDir = dirname($backupFilename);
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // Daten aus bestehender Datenbankdatei kopieren
            $databaseFile = dirname($this->db->getBackupManager()->getBackupDir()) . '/database.sqlite';
            if (!copy($databaseFile, $backupFilename)) {
                throw new \Exception('Fehler beim Erstellen des Backups.');
            }
            
            $_SESSION['success'] = 'Backup wurde erfolgreich erstellt.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler beim Erstellen des Backups: ' . $e->getMessage();
        }
        
        // Zurück zur Backup-Verwaltung
        header('Location: ' . Path::url('/admin/backups'));
        exit;
    }
    
    /**
     * Stellt ein Backup wieder her
     */
    public function restoreBackup() {
        // Prüfen, ob Benutzer Admin ist
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = 'Sie haben keine Berechtigung, diese Aktion auszuführen.';
            header('Location: ' . Path::url('/'));
            exit;
        }
        
        // Backup-Datei aus Request holen
        $backupFile = $_POST['backup_file'] ?? null;
        
        if (!$backupFile || !file_exists($backupFile)) {
            $_SESSION['error'] = 'Ungültige Backup-Datei.';
            header('Location: ' . Path::url('/admin/backups'));
            exit;
        }
        
        try {
            $backupManager = $this->db->getBackupManager();
            $backupManager->restoreBackup($backupFile);
            
            $_SESSION['success'] = 'Backup wurde erfolgreich wiederhergestellt.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler beim Wiederherstellen des Backups: ' . $e->getMessage();
        }
        
        // Zurück zur Backup-Verwaltung
        header('Location: ' . Path::url('/admin/backups'));
        exit;
    }
    
    /**
     * Löscht ein Backup
     */
    public function deleteBackup() {
        // Prüfen, ob Benutzer Admin ist
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = 'Sie haben keine Berechtigung, diese Aktion auszuführen.';
            header('Location: ' . Path::url('/'));
            exit;
        }
        
        // Backup-Datei aus Request holen
        $backupFile = $_POST['backup_file'] ?? null;
        
        if (!$backupFile || !file_exists($backupFile)) {
            $_SESSION['error'] = 'Ungültige Backup-Datei.';
            header('Location: ' . Path::url('/admin/backups'));
            exit;
        }
        
        try {
            if (unlink($backupFile)) {
                $_SESSION['success'] = 'Backup wurde erfolgreich gelöscht.';
            } else {
                throw new \Exception('Fehler beim Löschen des Backups.');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler beim Löschen des Backups: ' . $e->getMessage();
        }
        
        // Zurück zur Backup-Verwaltung
        header('Location: ' . Path::url('/admin/backups'));
        exit;
    }
} 