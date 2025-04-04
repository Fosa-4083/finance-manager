<?php

namespace Controllers;

use Models\User;
use Utils\Session;
use PDO;

class AuthController extends BaseController {
    private $user;
    protected $session;

    public function __construct($db = null) {
        // Basisklassen-Konstruktor aufrufen
        parent::__construct($db);
        
        // Modelle und Services initialisieren
        $this->user = new User($this->db);
        $this->session = Session::getInstance();
    }

    /**
     * Login-Formular anzeigen
     */
    public function showLoginForm() {
        // Wenn bereits angemeldet, zum Dashboard weiterleiten
        if ($this->session->isLoggedIn()) {
            header('Location: ' . \Utils\Path::url('/'));
            exit;
        }

        $csrf_token = $this->session->getCsrfToken();
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Registrierungsformular anzeigen
     */
    public function showRegisterForm() {
        // Wenn bereits angemeldet, zum Dashboard weiterleiten
        if ($this->session->isLoggedIn()) {
            header('Location: ' . \Utils\Path::url('/'));
            exit;
        }

        $csrf_token = $this->session->getCsrfToken();
        require_once __DIR__ . '/../Views/auth/register.php';
    }

    /**
     * Login-Prozess durchführen
     */
    public function login() {
        // CSRF-Token validieren
        if (!isset($_POST['csrf_token']) || !$this->session->validateCsrfToken($_POST['csrf_token'])) {
            $this->session->setFlash('error', 'Ungültige Anfrage. Bitte versuchen Sie es erneut.');
            header('Location: ' . \Utils\Path::url('/login'));
            exit;
        }

        // Formularfelder validieren
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';

        if (!$email || empty($password)) {
            $this->session->setFlash('error', 'Bitte geben Sie eine gültige E-Mail-Adresse und ein Passwort ein.');
            header('Location: ' . \Utils\Path::url('/login'));
            exit;
        }

        // Debug-Ausgabe
        error_log("Login-Versuch für E-Mail: " . $email . ", Remember Me: " . ($remember_me ? 'Ja' : 'Nein'));
        
        // Benutzer authentifizieren
        if ($this->user->authenticate($email, $password)) {
            error_log("Authentifizierung erfolgreich");
            // Benutzer in der Session speichern
            $this->session->setUser(
                $this->user->getId(),
                $this->user->getEmail(),
                $this->user->getName()
            );

            // "Angemeldet bleiben" Cookie setzen, wenn gewünscht
            if ($remember_me) {
                // Zufälliges Token generieren
                $token = bin2hex(random_bytes(32));
                
                // Token in der Datenbank speichern
                $this->user->saveRememberToken($token);
                
                // Cookie setzen (30 Tage gültig)
                $this->session->setRememberMeCookie($this->user->getId(), $token);
                
                error_log("Remember-Me-Cookie gesetzt für Benutzer ID: " . $this->user->getId());
            }

            $this->session->setFlash('success', 'Sie wurden erfolgreich angemeldet.');
            header('Location: ' . \Utils\Path::url('/'));
            exit;
        } else {
            error_log("Authentifizierung fehlgeschlagen");
            $this->session->setFlash('error', 'Ungültige E-Mail-Adresse oder Passwort.');
            header('Location: ' . \Utils\Path::url('/login'));
            exit;
        }
    }

    /**
     * Registrierungsprozess durchführen
     */
    public function register() {
        // CSRF-Token validieren
        if (!isset($_POST['csrf_token']) || !$this->session->validateCsrfToken($_POST['csrf_token'])) {
            $this->session->setFlash('error', 'Ungültige Anfrage. Bitte versuchen Sie es erneut.');
            header('Location: ' . \Utils\Path::url('/register'));
            exit;
        }

        // Formularfelder validieren
        $name = trim($_POST['name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Bitte geben Sie Ihren Namen ein.';
        }

        if (!$email) {
            $errors[] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
        }

        if (empty($password)) {
            $errors[] = 'Bitte geben Sie ein Passwort ein.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
        }

        if ($password !== $password_confirm) {
            $errors[] = 'Die Passwörter stimmen nicht überein.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->session->setFlash('error', $error);
            }
            header('Location: ' . \Utils\Path::url('/register'));
            exit;
        }

        // Benutzer erstellen
        if ($this->user->create($email, $password, $name)) {
            // Benutzer in der Session speichern
            $this->session->setUser(
                $this->user->getId(),
                $this->user->getEmail(),
                $this->user->getName()
            );

            $this->session->setFlash('success', 'Ihr Konto wurde erfolgreich erstellt.');
            header('Location: ' . \Utils\Path::url('/'));
            exit;
        } else {
            $this->session->setFlash('error', 'Diese E-Mail-Adresse wird bereits verwendet.');
            header('Location: ' . \Utils\Path::url('/register'));
            exit;
        }
    }

    /**
     * Benutzer abmelden
     */
    public function logout() {
        // Remember-Me-Cookie löschen
        $this->session->clearRememberMeCookie();
        
        // Session-Daten löschen
        $this->session->clearUser();
        $this->session->setFlash('success', 'Sie wurden erfolgreich abgemeldet.');
        header('Location: ' . \Utils\Path::url('/login'));
        exit;
    }

    /**
     * Profil anzeigen
     */
    public function showProfile() {
        // Prüfen, ob Benutzer angemeldet ist
        if (!$this->session->isLoggedIn()) {
            header('Location: ' . \Utils\Path::url('/login'));
            exit;
        }

        // Benutzerdaten abrufen
        $userId = $this->session->getUserId();
        $this->user->getById($userId);

        $csrf_token = $this->session->getCsrfToken();
        require_once __DIR__ . '/../Views/auth/profile.php';
    }

    /**
     * Profil aktualisieren
     */
    public function updateProfile() {
        // Prüfen, ob Benutzer angemeldet ist
        if (!$this->session->isLoggedIn()) {
            header('Location: ' . \Utils\Path::url('/login'));
            exit;
        }

        // CSRF-Token validieren
        if (!isset($_POST['csrf_token']) || !$this->session->validateCsrfToken($_POST['csrf_token'])) {
            $this->session->setFlash('error', 'Ungültige Anfrage. Bitte versuchen Sie es erneut.');
            header('Location: ' . \Utils\Path::url('/profile'));
            exit;
        }

        // Benutzerdaten abrufen
        $userId = $this->session->getUserId();
        $this->user->getById($userId);

        // Formularfelder validieren
        $name = trim($_POST['name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $new_password_confirm = $_POST['new_password_confirm'] ?? '';

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Bitte geben Sie Ihren Namen ein.';
        }

        if (!$email) {
            $errors[] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
        }

        // Passwort nur aktualisieren, wenn alle Passwortfelder ausgefüllt sind
        $updatePassword = false;
        if (!empty($current_password) || !empty($new_password) || !empty($new_password_confirm)) {
            if (empty($current_password)) {
                $errors[] = 'Bitte geben Sie Ihr aktuelles Passwort ein.';
            }

            if (empty($new_password)) {
                $errors[] = 'Bitte geben Sie ein neues Passwort ein.';
            } elseif (strlen($new_password) < 8) {
                $errors[] = 'Das neue Passwort muss mindestens 8 Zeichen lang sein.';
            }

            if ($new_password !== $new_password_confirm) {
                $errors[] = 'Die neuen Passwörter stimmen nicht überein.';
            }

            $updatePassword = true;
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->session->setFlash('error', $error);
            }
            header('Location: ' . \Utils\Path::url('/profile'));
            exit;
        }

        // Profil aktualisieren
        $data = [
            'name' => $name,
            'email' => $email
        ];

        if (!$this->user->update($data)) {
            $this->session->setFlash('error', 'Fehler beim Aktualisieren des Profils.');
            header('Location: ' . \Utils\Path::url('/profile'));
            exit;
        }

        // Passwort aktualisieren, wenn erforderlich
        if ($updatePassword) {
            if (!$this->user->changePassword($current_password, $new_password)) {
                $this->session->setFlash('error', 'Das aktuelle Passwort ist falsch.');
                header('Location: ' . \Utils\Path::url('/profile'));
                exit;
            }
        }

        // Session aktualisieren
        $this->session->setUser(
            $this->user->getId(),
            $this->user->getEmail(),
            $this->user->getName()
        );

        $this->session->setFlash('success', 'Ihr Profil wurde erfolgreich aktualisiert.');
        header('Location: ' . \Utils\Path::url('/profile'));
        exit;
    }
} 