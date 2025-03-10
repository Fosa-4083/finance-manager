<?php

namespace Utils;

class Session {
    private static $instance = null;
    private $data = [];
    private $flash = [];

    /**
     * Konstruktor - privat für Singleton-Pattern
     */
    private function __construct() {
        $this->data = &$_SESSION;
        
        // Flash-Nachrichten aus der vorherigen Anfrage laden
        if (isset($_SESSION['flash_next'])) {
            $this->flash = $_SESSION['flash_next'];
            unset($_SESSION['flash_next']);
        }
    }

    /**
     * Singleton-Instanz abrufen
     * 
     * @return Session
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Session starten, falls noch nicht geschehen
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Wert in der Session speichern
     * 
     * @param string $key Schlüssel
     * @param mixed $value Wert
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Wert aus der Session abrufen
     * 
     * @param string $key Schlüssel
     * @param mixed $default Standardwert, falls Schlüssel nicht existiert
     * @return mixed Wert oder Standardwert
     */
    public function get($key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    /**
     * Wert aus der Session entfernen
     * 
     * @param string $key Schlüssel
     */
    public function remove($key) {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    /**
     * Benutzer in der Session speichern
     * 
     * @param int $userId Benutzer-ID
     * @param string $email E-Mail-Adresse
     * @param string $name Name des Benutzers
     */
    public function setUser($userId, $email, $name) {
        $this->set('user', [
            'id' => $userId,
            'email' => $email,
            'name' => $name
        ]);
    }

    /**
     * Benutzer aus der Session entfernen
     */
    public function clearUser() {
        $this->remove('user');
    }

    /**
     * Prüfen, ob ein Benutzer angemeldet ist
     * 
     * @return bool
     */
    public function isLoggedIn() {
        return $this->get('user') !== null;
    }

    /**
     * Angemeldeten Benutzer abrufen
     * 
     * @return array|null Benutzerdaten oder null, wenn nicht angemeldet
     */
    public function getUser() {
        return $this->get('user');
    }

    /**
     * Benutzer-ID des angemeldeten Benutzers abrufen
     * 
     * @return int|null Benutzer-ID oder null, wenn nicht angemeldet
     */
    public function getUserId() {
        $user = $this->getUser();
        return $user ? $user['id'] : null;
    }

    /**
     * Session beenden
     */
    public function logout() {
        $this->remove('user');
        session_destroy();
    }

    /**
     * Flash-Message setzen (wird nur einmal angezeigt)
     * 
     * @param string $type Typ der Nachricht (success, error, warning, info)
     * @param string $message Nachricht
     */
    public function setFlash($type, $message) {
        if (!isset($_SESSION['flash_next'])) {
            $_SESSION['flash_next'] = [];
        }
        $_SESSION['flash_next'][$type] = $message;
    }

    /**
     * Flash-Messages abrufen und aus der Session entfernen
     * 
     * @return array Flash-Messages
     */
    public function getAllFlash() {
        return $this->flash;
    }

    /**
     * Flash-Message abrufen
     * 
     * @param string $type Typ der Nachricht (success, error, warning, info)
     * @return string|null Nachricht oder null, wenn keine Nachricht vorhanden ist
     */
    public function getFlash($type) {
        return $this->flash[$type] ?? null;
    }

    /**
     * Prüfen, ob ein Flash-Message vorhanden ist
     * 
     * @param string $type Typ der Nachricht (success, error, warning, info)
     * @return bool
     */
    public function hasFlash($type) {
        return isset($this->flash[$type]);
    }

    /**
     * Flash-Messages aus der Session entfernen
     */
    public function clearFlash() {
        $this->flash = [];
        if (isset($_SESSION['flash_next'])) {
            unset($_SESSION['flash_next']);
        }
    }

    /**
     * CSRF-Token abrufen
     * 
     * @return string CSRF-Token
     */
    public function getCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * CSRF-Token validieren
     * 
     * @param string $token Zu validierender Token
     * @return bool Token gültig
     */
    public function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
} 