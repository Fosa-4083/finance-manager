<?php

namespace Utils;

class DeploymentManager {
    private $ftpClient;
    private $localBasePath;
    private $remoteBasePath;
    private $excludedPaths;
    private $lastError;

    /**
     * Konstruktor für den Deployment-Manager
     * 
     * @param string $host FTP-Server-Hostname oder IP
     * @param string $username Benutzername für die Anmeldung
     * @param string $password Passwort für die Anmeldung
     * @param string $localBasePath Lokaler Basispfad der Anwendung
     * @param string $remoteBasePath Basispfad auf dem FTP-Server
     * @param array $excludedPaths Pfade, die vom Deployment ausgeschlossen werden sollen
     * @param int $port FTP-Port (Standard: 21)
     * @param bool $ssl SSL/TLS-Verbindung verwenden (Standard: false)
     */
    public function __construct($host, $username, $password, $localBasePath, $remoteBasePath = '/', 
                               $excludedPaths = [], $port = 21, $ssl = false) {
        $this->ftpClient = new FtpClient($host, $username, $password, $port, true, $ssl);
        $this->localBasePath = rtrim($localBasePath, '/');
        $this->remoteBasePath = rtrim($remoteBasePath, '/');
        
        // Standard-Ausschlüsse hinzufügen
        $this->excludedPaths = array_merge([
            '.git',
            '.gitignore',
            'node_modules',
            'vendor/bin',
            'tests',
            '.env',
            'database/database.sqlite', // Lokale Datenbank nicht hochladen
            'storage/logs',
            'storage/cache',
            'storage/temp'
        ], $excludedPaths);
        
        $this->lastError = '';
    }

    /**
     * Verbindung zum FTP-Server herstellen
     * 
     * @return bool Erfolg der Verbindung
     */
    public function connect() {
        $result = $this->ftpClient->connect();
        if (!$result) {
            $this->lastError = $this->ftpClient->getLastError();
        }
        return $result;
    }

    /**
     * Verbindung zum FTP-Server trennen
     */
    public function disconnect() {
        $this->ftpClient->disconnect();
    }

    /**
     * Prüfen, ob ein Pfad ausgeschlossen werden soll
     * 
     * @param string $path Zu prüfender Pfad
     * @return bool True, wenn der Pfad ausgeschlossen werden soll
     */
    private function isExcluded($path) {
        $relativePath = str_replace($this->localBasePath . '/', '', $path);
        
        foreach ($this->excludedPaths as $excludedPath) {
            if (strpos($relativePath, $excludedPath) === 0 || 
                $relativePath === $excludedPath ||
                fnmatch($excludedPath, $relativePath)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verzeichnisstruktur auf dem FTP-Server erstellen
     * 
     * @param string $path Lokaler Verzeichnispfad
     * @param string $remotePath Pfad auf dem FTP-Server
     * @return bool Erfolg der Operation
     */
    private function createDirectoryStructure($path, $remotePath) {
        if (!is_dir($path) || $this->isExcluded($path)) {
            return true;
        }
        
        // Verzeichnis auf dem Server erstellen, wenn es nicht existiert
        $dirList = $this->ftpClient->listDirectory(dirname($remotePath));
        if ($dirList === false) {
            $this->lastError = $this->ftpClient->getLastError();
            return false;
        }
        
        $dirName = basename($remotePath);
        if (!in_array($dirName, $dirList)) {
            if (!$this->ftpClient->createDirectory($remotePath)) {
                $this->lastError = $this->ftpClient->getLastError();
                return false;
            }
        }
        
        // Unterverzeichnisse rekursiv erstellen
        $handle = opendir($path);
        if (!$handle) {
            $this->lastError = "Konnte Verzeichnis nicht öffnen: $path";
            return false;
        }
        
        $success = true;
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $localFilePath = $path . '/' . $file;
            $remoteFilePath = $remotePath . '/' . $file;
            
            if (is_dir($localFilePath) && !$this->isExcluded($localFilePath)) {
                $success = $this->createDirectoryStructure($localFilePath, $remoteFilePath) && $success;
            }
        }
        
        closedir($handle);
        return $success;
    }

    /**
     * Dateien auf den FTP-Server hochladen
     * 
     * @param string $path Lokaler Verzeichnispfad
     * @param string $remotePath Pfad auf dem FTP-Server
     * @param bool $overwrite Bestehende Dateien überschreiben
     * @return bool Erfolg der Operation
     */
    private function uploadFiles($path, $remotePath, $overwrite = true) {
        if (!is_dir($path) || $this->isExcluded($path)) {
            return true;
        }
        
        $handle = opendir($path);
        if (!$handle) {
            $this->lastError = "Konnte Verzeichnis nicht öffnen: $path";
            return false;
        }
        
        $success = true;
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $localFilePath = $path . '/' . $file;
            $remoteFilePath = $remotePath . '/' . $file;
            
            if (is_dir($localFilePath)) {
                if (!$this->isExcluded($localFilePath)) {
                    $success = $this->uploadFiles($localFilePath, $remoteFilePath, $overwrite) && $success;
                }
            } else {
                if (!$this->isExcluded($localFilePath)) {
                    // Prüfen, ob die Datei bereits existiert und ob sie überschrieben werden soll
                    $fileList = $this->ftpClient->listDirectory(dirname($remoteFilePath));
                    if ($fileList === false) {
                        $this->lastError = $this->ftpClient->getLastError();
                        $success = false;
                        continue;
                    }
                    
                    $fileName = basename($remoteFilePath);
                    if (!in_array($fileName, $fileList) || $overwrite) {
                        // Dateiendung prüfen, um den richtigen Übertragungsmodus zu wählen
                        $extension = strtolower(pathinfo($localFilePath, PATHINFO_EXTENSION));
                        $mode = in_array($extension, ['txt', 'php', 'html', 'css', 'js', 'json', 'xml', 'md', 'csv']) 
                                ? FTP_ASCII : FTP_BINARY;
                        
                        if (!$this->ftpClient->upload($localFilePath, $remoteFilePath, $mode)) {
                            $this->lastError = $this->ftpClient->getLastError();
                            $success = false;
                        }
                    }
                }
            }
        }
        
        closedir($handle);
        return $success;
    }

    /**
     * Anwendung auf den FTP-Server deployen
     * 
     * @param bool $createStructure Verzeichnisstruktur erstellen
     * @param bool $overwrite Bestehende Dateien überschreiben
     * @return bool Erfolg des Deployments
     */
    public function deploy($createStructure = true, $overwrite = true) {
        if (!$this->ftpClient->isConnected() && !$this->connect()) {
            return false;
        }
        
        // Verzeichnisstruktur erstellen
        if ($createStructure) {
            if (!$this->createDirectoryStructure($this->localBasePath, $this->remoteBasePath)) {
                return false;
            }
        }
        
        // Dateien hochladen
        return $this->uploadFiles($this->localBasePath, $this->remoteBasePath, $overwrite);
    }

    /**
     * Nur geänderte Dateien auf den FTP-Server hochladen
     * 
     * @param int $timestamp Zeitstempel, ab dem Dateien als geändert gelten
     * @return bool Erfolg des Deployments
     */
    public function deployChangedFiles($timestamp = 0) {
        if (!$this->ftpClient->isConnected() && !$this->connect()) {
            return false;
        }
        
        // Wenn kein Zeitstempel angegeben wurde, den letzten Tag verwenden
        if ($timestamp === 0) {
            $timestamp = time() - 86400; // 24 Stunden
        }
        
        return $this->uploadChangedFiles($this->localBasePath, $this->remoteBasePath, $timestamp);
    }

    /**
     * Geänderte Dateien auf den FTP-Server hochladen
     * 
     * @param string $path Lokaler Verzeichnispfad
     * @param string $remotePath Pfad auf dem FTP-Server
     * @param int $timestamp Zeitstempel, ab dem Dateien als geändert gelten
     * @return bool Erfolg der Operation
     */
    private function uploadChangedFiles($path, $remotePath, $timestamp) {
        if (!is_dir($path) || $this->isExcluded($path)) {
            return true;
        }
        
        // Verzeichnis auf dem Server erstellen, wenn es nicht existiert
        $dirList = $this->ftpClient->listDirectory(dirname($remotePath));
        if ($dirList === false) {
            $this->lastError = $this->ftpClient->getLastError();
            return false;
        }
        
        $dirName = basename($remotePath);
        if (!in_array($dirName, $dirList)) {
            if (!$this->ftpClient->createDirectory($remotePath)) {
                $this->lastError = $this->ftpClient->getLastError();
                return false;
            }
        }
        
        $handle = opendir($path);
        if (!$handle) {
            $this->lastError = "Konnte Verzeichnis nicht öffnen: $path";
            return false;
        }
        
        $success = true;
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $localFilePath = $path . '/' . $file;
            $remoteFilePath = $remotePath . '/' . $file;
            
            if (is_dir($localFilePath)) {
                if (!$this->isExcluded($localFilePath)) {
                    $success = $this->uploadChangedFiles($localFilePath, $remoteFilePath, $timestamp) && $success;
                }
            } else {
                if (!$this->isExcluded($localFilePath) && filemtime($localFilePath) >= $timestamp) {
                    // Dateiendung prüfen, um den richtigen Übertragungsmodus zu wählen
                    $extension = strtolower(pathinfo($localFilePath, PATHINFO_EXTENSION));
                    $mode = in_array($extension, ['txt', 'php', 'html', 'css', 'js', 'json', 'xml', 'md', 'csv']) 
                            ? FTP_ASCII : FTP_BINARY;
                    
                    if (!$this->ftpClient->upload($localFilePath, $remoteFilePath, $mode)) {
                        $this->lastError = $this->ftpClient->getLastError();
                        $success = false;
                    }
                }
            }
        }
        
        closedir($handle);
        return $success;
    }

    /**
     * Datenbank-Backup erstellen und auf den FTP-Server hochladen
     * 
     * @param string $databasePath Pfad zur SQLite-Datenbank
     * @param string $backupDir Verzeichnis für Backups auf dem FTP-Server
     * @return bool Erfolg der Operation
     */
    public function backupDatabase($databasePath, $backupDir = '/backups') {
        if (!$this->ftpClient->isConnected() && !$this->connect()) {
            return false;
        }
        
        if (!file_exists($databasePath)) {
            $this->lastError = "Datenbank-Datei existiert nicht: $databasePath";
            return false;
        }
        
        // Backup-Verzeichnis auf dem Server erstellen, wenn es nicht existiert
        $dirList = $this->ftpClient->listDirectory(dirname($backupDir));
        if ($dirList === false) {
            $this->lastError = $this->ftpClient->getLastError();
            return false;
        }
        
        $dirName = basename($backupDir);
        if (!in_array($dirName, $dirList)) {
            if (!$this->ftpClient->createDirectory($backupDir)) {
                $this->lastError = $this->ftpClient->getLastError();
                return false;
            }
        }
        
        // Backup-Dateiname mit Zeitstempel erstellen
        $timestamp = date('Y-m-d_H-i-s');
        $backupFileName = basename($databasePath, '.sqlite') . '_' . $timestamp . '.sqlite';
        $remotePath = $backupDir . '/' . $backupFileName;
        
        // Datenbank auf den Server hochladen
        if (!$this->ftpClient->upload($databasePath, $remotePath, FTP_BINARY)) {
            $this->lastError = $this->ftpClient->getLastError();
            return false;
        }
        
        return true;
    }

    /**
     * Letzten Fehler abrufen
     * 
     * @return string Letzter Fehler
     */
    public function getLastError() {
        return $this->lastError;
    }
} 