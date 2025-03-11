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
     * @return mixed
     */
    public function get($key, $default = null) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
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
        $this->set('user_id', $userId);
        $this->set('user_email', $email);
        $this->set('user_name', $name);
        $this->set('logged_in', true);
    }

    /**
     * Benutzer aus der Session entfernen
     */
    public function clearUser() {
        $this->remove('user_id');
        $this->remove('user_email');
        $this->remove('user_name');
        $this->remove('logged_in');
        
        // Auch das Remember-Me-Cookie löschen, falls vorhanden
        $this->clearRememberMeCookie();
    }
    
    /**
     * Remember-Me-Cookie setzen
     * 
     * @param int $userId Benutzer-ID
     * @param string $token Eindeutiger Token
     * @param int $days Gültigkeitsdauer in Tagen
     */
    public function setRememberMeCookie($userId, $token, $days = 30) {
        $expiry = time() + (86400 * $days); // 86400 = 1 Tag in Sekunden
        $cookieValue = $userId . ':' . $token;
        
        // Cookie setzen
        setcookie(
            'remember_me',
            $cookieValue,
            [
                'expires' => $expiry,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }
    
    /**
     * Remember-Me-Cookie löschen
     */
    public function clearRememberMeCookie() {
        if (isset($_COOKIE['remember_me'])) {
            // Cookie mit Vergangenheitsdatum setzen, um es zu löschen
            setcookie(
                'remember_me',
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }
    }
    
    /**
     * Remember-Me-Cookie auslesen
     * 
     * @return array|null Array mit user_id und token oder null, wenn kein Cookie vorhanden
     */
    public function getRememberMeCookie() {
        if (isset($_COOKIE['remember_me'])) {
            $parts = explode(':', $_COOKIE['remember_me']);
            if (count($parts) === 2) {
                return [
                    'user_id' => $parts[0],
                    'token' => $parts[1]
                ];
            }
        }
        return null;
    }

    /**
     * Prüfen, ob ein Benutzer angemeldet ist
     * 
     * @return bool
     */
    public function isLoggedIn() {
        return $this->get('logged_in', false);
    }

    /**
     * Angemeldeten Benutzer abrufen
     * 
     * @return array|null
     */
    public function getUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $this->get('user_id'),
            'email' => $this->get('user_email'),
            'name' => $this->get('user_name')
        ];
    }

    /**
     * Benutzer-ID des angemeldeten Benutzers abrufen
     * 
     * @return int|null
     */
    public function getUserId() {
        return $this->get('user_id');
    }

    /**
     * Benutzer abmelden
     */
    public function logout() {
        $this->clearUser();
        
        // Session-ID erneuern, um Session-Fixation zu verhindern
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Flash-Nachricht setzen
     * 
     * @param string $type Typ der Nachricht (success, error, warning, info)
     * @param string $message Nachrichtentext
     */
    public function setFlash($type, $message) {
        $this->flash[$type] = $message;
        $_SESSION['flash_next'][$type] = $message;
    }

    /**
     * Alle Flash-Nachrichten abrufen
     * 
     * @return array
     */
    public function getAllFlash() {
        $flash = $this->flash;
        $this->flash = [];
        return $flash;
    }

    /**
     * Flash-Nachricht eines bestimmten Typs abrufen
     * 
     * @param string $type Typ der Nachricht
     * @return string|null
     */
    public function getFlash($type) {
        if (isset($this->flash[$type])) {
            $message = $this->flash[$type];
            unset($this->flash[$type]);
            return $message;
        }
        return null;
    }

    /**
     * Prüfen, ob Flash-Nachricht eines bestimmten Typs existiert
     * 
     * @param string $type Typ der Nachricht
     * @return bool
     */
    public function hasFlash($type) {
        return isset($this->flash[$type]);
    }

    /**
     * Alle Flash-Nachrichten löschen
     */
    public function clearFlash() {
        $this->flash = [];
        if (isset($_SESSION['flash_next'])) {
            unset($_SESSION['flash_next']);
        }
    }

    /**
     * CSRF-Token generieren oder abrufen
     * 
     * @return string
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
     * @return bool
     */
    public function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
} 