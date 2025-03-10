<?php

namespace Controllers;

use Models\User;
use Utils\Session;

class AuthController {
    private $db;
    private $user;
    private $session;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
        $this->session = Session::getInstance();
    }

    /**
     * Login-Formular anzeigen
     */
    public function showLoginForm() {
        // Wenn bereits angemeldet, zum Dashboard weiterleiten
        if ($this->session->isLoggedIn()) {
            header('Location: /');
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
            header('Location: /');
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
            header('Location: /login');
            exit;
        }

        // Formularfelder validieren
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $this->session->setFlash('error', 'Bitte geben Sie eine gültige E-Mail-Adresse und ein Passwort ein.');
            header('Location: /login');
            exit;
        }

        // Debug-Ausgabe
        error_log("Login-Versuch für E-Mail: " . $email);
        
        // Benutzer authentifizieren
        if ($this->user->authenticate($email, $password)) {
            error_log("Authentifizierung erfolgreich");
            // Benutzer in der Session speichern
            $this->session->setUser(
                $this->user->getId(),
                $this->user->getEmail(),
                $this->user->getName()
            );

            $this->session->setFlash('success', 'Sie wurden erfolgreich angemeldet.');
            header('Location: /');
            exit;
        } else {
            error_log("Authentifizierung fehlgeschlagen");
            $this->session->setFlash('error', 'Ungültige E-Mail-Adresse oder Passwort.');
            header('Location: /login');
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
            header('Location: /register');
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
            header('Location: /register');
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
            header('Location: /');
            exit;
        } else {
            $this->session->setFlash('error', 'Diese E-Mail-Adresse wird bereits verwendet.');
            header('Location: /register');
            exit;
        }
    }

    /**
     * Benutzer abmelden
     */
    public function logout() {
        $this->session->clearUser();
        $this->session->setFlash('success', 'Sie wurden erfolgreich abgemeldet.');
        header('Location: /login');
        exit;
    }

    /**
     * Profil anzeigen
     */
    public function showProfile() {
        // Prüfen, ob Benutzer angemeldet ist
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
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
            header('Location: /login');
            exit;
        }

        // CSRF-Token validieren
        if (!isset($_POST['csrf_token']) || !$this->session->validateCsrfToken($_POST['csrf_token'])) {
            $this->session->setFlash('error', 'Ungültige Anfrage. Bitte versuchen Sie es erneut.');
            header('Location: /profile');
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
            header('Location: /profile');
            exit;
        }

        // Profil aktualisieren
        $data = [
            'name' => $name,
            'email' => $email
        ];

        if (!$this->user->update($data)) {
            $this->session->setFlash('error', 'Fehler beim Aktualisieren des Profils.');
            header('Location: /profile');
            exit;
        }

        // Passwort aktualisieren, wenn erforderlich
        if ($updatePassword) {
            if (!$this->user->changePassword($current_password, $new_password)) {
                $this->session->setFlash('error', 'Das aktuelle Passwort ist falsch.');
                header('Location: /profile');
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
        header('Location: /profile');
        exit;
    }
} 