<?php

namespace Utils;

class FtpClient {
    private $connection;
    private $isConnected = false;
    private $host;
    private $username;
    private $password;
    private $port;
    private $passive;
    private $ssl;
    private $timeout;
    private $lastError;

    /**
     * Konstruktor für den FTP-Client
     * 
     * @param string $host FTP-Server-Hostname oder IP
     * @param string $username Benutzername für die Anmeldung
     * @param string $password Passwort für die Anmeldung
     * @param int $port FTP-Port (Standard: 21)
     * @param bool $passive Passiver Modus (Standard: true)
     * @param bool $ssl SSL/TLS-Verbindung verwenden (Standard: false)
     * @param int $timeout Timeout in Sekunden (Standard: 90)
     */
    public function __construct($host, $username, $password, $port = 21, $passive = true, $ssl = false, $timeout = 90) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->passive = $passive;
        $this->ssl = $ssl;
        $this->timeout = $timeout;
    }

    /**
     * Verbindung zum FTP-Server herstellen
     * 
     * @return bool Erfolg der Verbindung
     */
    public function connect() {
        // Prüfen, ob FTP-Erweiterung verfügbar ist
        if (!extension_loaded('ftp')) {
            $this->lastError = "FTP-Erweiterung ist nicht verfügbar.";
            return false;
        }

        // Verbindung herstellen (mit oder ohne SSL)
        if ($this->ssl) {
            if (!function_exists('ftp_ssl_connect')) {
                $this->lastError = "FTP-SSL-Verbindung wird nicht unterstützt.";
                return false;
            }
            $this->connection = @ftp_ssl_connect($this->host, $this->port, $this->timeout);
        } else {
            $this->connection = @ftp_connect($this->host, $this->port, $this->timeout);
        }

        // Prüfen, ob Verbindung erfolgreich war
        if (!$this->connection) {
            $this->lastError = "Verbindung zum FTP-Server konnte nicht hergestellt werden.";
            return false;
        }

        // Anmelden
        if (!@ftp_login($this->connection, $this->username, $this->password)) {
            $this->lastError = "Anmeldung am FTP-Server fehlgeschlagen.";
            $this->disconnect();
            return false;
        }

        // Passiven Modus aktivieren, wenn gewünscht
        if ($this->passive) {
            ftp_pasv($this->connection, true);
        }

        $this->isConnected = true;
        return true;
    }

    /**
     * Verbindung zum FTP-Server trennen
     */
    public function disconnect() {
        if ($this->isConnected && $this->connection) {
            ftp_close($this->connection);
            $this->isConnected = false;
        }
    }

    /**
     * Datei auf den FTP-Server hochladen
     * 
     * @param string $localFile Lokaler Dateipfad
     * @param string $remoteFile Zieldateipfad auf dem Server
     * @param int $mode FTP_ASCII oder FTP_BINARY
     * @return bool Erfolg des Uploads
     */
    public function upload($localFile, $remoteFile, $mode = FTP_BINARY) {
        if (!$this->isConnected) {
            if (!$this->connect()) {
                return false;
            }
        }

        if (!file_exists($localFile)) {
            $this->lastError = "Lokale Datei existiert nicht: $localFile";
            return false;
        }

        $result = @ftp_put($this->connection, $remoteFile, $localFile, $mode);
        
        if (!$result) {
            $this->lastError = "Fehler beim Hochladen der Datei.";
            return false;
        }

        return true;
    }

    /**
     * Datei vom FTP-Server herunterladen
     * 
     * @param string $remoteFile Quelldateipfad auf dem Server
     * @param string $localFile Lokaler Zieldateipfad
     * @param int $mode FTP_ASCII oder FTP_BINARY
     * @return bool Erfolg des Downloads
     */
    public function download($remoteFile, $localFile, $mode = FTP_BINARY) {
        if (!$this->isConnected) {
            if (!$this->connect()) {
                return false;
            }
        }

        $result = @ftp_get($this->connection, $localFile, $remoteFile, $mode);
        
        if (!$result) {
            $this->lastError = "Fehler beim Herunterladen der Datei.";
            return false;
        }

        return true;
    }

    /**
     * Verzeichnis auf dem FTP-Server erstellen
     * 
     * @param string $directory Verzeichnispfad
     * @return bool Erfolg der Operation
     */
    public function createDirectory($directory) {
        if (!$this->isConnected) {
            if (!$this->connect()) {
                return false;
            }
        }

        $result = @ftp_mkdir($this->connection, $directory);
        
        if (!$result) {
            $this->lastError = "Fehler beim Erstellen des Verzeichnisses.";
            return false;
        }

        return true;
    }

    /**
     * Verzeichnisinhalt auflisten
     * 
     * @param string $directory Verzeichnispfad
     * @return array|bool Liste der Dateien oder false bei Fehler
     */
    public function listDirectory($directory = '.') {
        if (!$this->isConnected) {
            if (!$this->connect()) {
                return false;
            }
        }

        $result = @ftp_nlist($this->connection, $directory);
        
        if (!$result) {
            $this->lastError = "Fehler beim Auflisten des Verzeichnisses.";
            return false;
        }

        return $result;
    }

    /**
     * Detaillierte Verzeichnisauflistung
     * 
     * @param string $directory Verzeichnispfad
     * @return array|bool Liste der Dateien mit Details oder false bei Fehler
     */
    public function listDirectoryDetails($directory = '.') {
        if (!$this->isConnected) {
            if (!$this->connect()) {
                return false;
            }
        }

        $result = @ftp_rawlist($this->connection, $directory);
        
        if (!$result) {
            $this->lastError = "Fehler beim Auflisten des Verzeichnisses.";
            return false;
        }

        return $result;
    }

    /**
     * Datei oder Verzeichnis auf dem FTP-Server löschen
     * 
     * @param string $path Pfad zur Datei oder zum Verzeichnis
     * @param bool $isDirectory Gibt an, ob es sich um ein Verzeichnis handelt
     * @return bool Erfolg der Operation
     */
    public function delete($path, $isDirectory = false) {
        if (!$this->isConnected) {
            if (!$this->connect()) {
                return false;
            }
        }

        if ($isDirectory) {
            $result = @ftp_rmdir($this->connection, $path);
        } else {
            $result = @ftp_delete($this->connection, $path);
        }
        
        if (!$result) {
            $this->lastError = "Fehler beim Löschen von: $path";
            return false;
        }

        return true;
    }

    /**
     * Datei oder Verzeichnis auf dem FTP-Server umbenennen
     * 
     * @param string $oldName Alter Name
     * @param string $newName Neuer Name
     * @return bool Erfolg der Operation
     */
    public function rename($oldName, $newName) {
        if (!$this->isConnected) {
            if (!$this->connect()) {
                return false;
            }
        }

        $result = @ftp_rename($this->connection, $oldName, $newName);
        
        if (!$result) {
            $this->lastError = "Fehler beim Umbenennen von: $oldName zu $newName";
            return false;
        }

        return true;
    }

    /**
     * Aktuelles Verzeichnis auf dem FTP-Server ändern
     * 
     * @param string $directory Zielverzeichnis
     * @return bool Erfolg der Operation
     */
    public function changeDirectory($directory) {
        if (!$this->isConnected) {
            if (!$this->connect()) {
                return false;
            }
        }

        $result = @ftp_chdir($this->connection, $directory);
        
        if (!$result) {
            $this->lastError = "Fehler beim Wechseln in das Verzeichnis: $directory";
            return false;
        }

        return true;
    }

    /**
     * Aktuelles Verzeichnis auf dem FTP-Server abfragen
     * 
     * @return string|bool Aktuelles Verzeichnis oder false bei Fehler
     */
    public function getCurrentDirectory() {
        if (!$this->isConnected) {
            if (!$this->connect()) {
                return false;
            }
        }

        $result = @ftp_pwd($this->connection);
        
        if ($result === false) {
            $this->lastError = "Fehler beim Abfragen des aktuellen Verzeichnisses.";
            return false;
        }

        return $result;
    }

    /**
     * Letzten Fehler abrufen
     * 
     * @return string Letzter Fehler
     */
    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Prüfen, ob eine Verbindung besteht
     * 
     * @return bool Verbindungsstatus
     */
    public function isConnected() {
        return $this->isConnected;
    }

    /**
     * Destruktor zum automatischen Schließen der Verbindung
     */
    public function __destruct() {
        $this->disconnect();
    }
} 