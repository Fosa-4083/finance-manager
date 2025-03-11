<?php

namespace Controllers;

use Utils\Path;
use Utils\Database;
use PDO;

class UserController extends BaseController {
    public function __construct($db = null) {
        // Basisklassen-Konstruktor aufrufen
        parent::__construct($db);
    }
    
    /**
     * Zeigt eine Liste aller Benutzer an
     */
    public function index() {
        // Benutzer muss eingeloggt sein
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Sie müssen angemeldet sein, um diese Seite aufzurufen.';
            header('Location: ' . Path::url('/'));
            exit;
        }
        
        // Benutzer aus der Datenbank abrufen
        $stmt = $this->db->prepare('SELECT id, name, email, created_at, last_login FROM users ORDER BY name');
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Erfolgs- oder Fehlermeldung aus der Session holen
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        
        // Session-Variablen löschen
        unset($_SESSION['success']);
        unset($_SESSION['error']);
        
        // View anzeigen
        include VIEW_PATH . 'admin/users.php';
    }
    
    /**
     * Zeigt das Formular zum Erstellen eines neuen Benutzers an
     */
    public function create() {
        // Benutzer muss eingeloggt sein
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Sie müssen angemeldet sein, um diese Seite aufzurufen.';
            header('Location: ' . Path::url('/'));
            exit;
        }
        
        // View anzeigen
        include VIEW_PATH . 'admin/user_create.php';
    }
    
    /**
     * Speichert einen neuen Benutzer in der Datenbank
     */
    public function store() {
        // Benutzer muss eingeloggt sein
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Sie müssen angemeldet sein, um diese Aktion auszuführen.';
            header('Location: ' . Path::url('/'));
            exit;
        }
        
        // Formulardaten validieren
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Bitte füllen Sie alle Pflichtfelder aus.';
            header('Location: ' . Path::url('/users/create'));
            exit;
        }
        
        if ($password !== $password_confirm) {
            $_SESSION['error'] = 'Die Passwörter stimmen nicht überein.';
            header('Location: ' . Path::url('/users/create'));
            exit;
        }
        
        // Überprüfen, ob die E-Mail-Adresse bereits verwendet wird
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Diese E-Mail-Adresse wird bereits verwendet.';
            header('Location: ' . Path::url('/users/create'));
            exit;
        }
        
        // Benutzer in der Datenbank speichern
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $result = $stmt->execute([$name, $email, $hashedPassword]);
        
        if ($result) {
            $_SESSION['success'] = 'Benutzer wurde erfolgreich erstellt.';
            header('Location: ' . Path::url('/users'));
        } else {
            $_SESSION['error'] = 'Fehler beim Erstellen des Benutzers.';
            header('Location: ' . Path::url('/users/create'));
        }
        exit;
    }
    
    /**
     * Zeigt das Formular zum Bearbeiten eines Benutzers an
     */
    public function edit() {
        // Benutzer muss eingeloggt sein
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Sie müssen angemeldet sein, um diese Seite aufzurufen.';
            header('Location: ' . Path::url('/'));
            exit;
        }
        
        // Benutzer-ID aus der Anfrage holen
        $userId = $_GET['id'] ?? null;
        
        if (!$userId) {
            $_SESSION['error'] = 'Ungültige Benutzer-ID.';
            header('Location: ' . Path::url('/users'));
            exit;
        }
        
        // Eigenes Profil kann nicht hier bearbeitet werden
        if ($userId == $_SESSION['user']['id']) {
            $_SESSION['error'] = 'Um Ihr eigenes Profil zu bearbeiten, verwenden Sie bitte die Profil-Seite.';
            header('Location: ' . Path::url('/users'));
            exit;
        }
        
        // Benutzer aus der Datenbank abrufen
        $stmt = $this->db->prepare('SELECT id, name, email FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user) {
            $_SESSION['error'] = 'Benutzer nicht gefunden.';
            header('Location: ' . Path::url('/users'));
            exit;
        }
        
        // View anzeigen
        include VIEW_PATH . 'admin/user_edit.php';
    }
    
    /**
     * Aktualisiert einen Benutzer in der Datenbank
     */
    public function update() {
        // Benutzer muss eingeloggt sein
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Sie müssen angemeldet sein, um diese Aktion auszuführen.';
            header('Location: ' . Path::url('/'));
            exit;
        }
        
        // Formulardaten validieren
        $userId = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        if (!$userId || empty($name) || empty($email)) {
            $_SESSION['error'] = 'Bitte füllen Sie alle Pflichtfelder aus.';
            header('Location: ' . Path::url('/users/edit?id=' . $userId));
            exit;
        }
        
        // Eigenes Profil kann nicht hier bearbeitet werden
        if ($userId == $_SESSION['user']['id']) {
            $_SESSION['error'] = 'Um Ihr eigenes Profil zu bearbeiten, verwenden Sie bitte die Profil-Seite.';
            header('Location: ' . Path::url('/users'));
            exit;
        }
        
        // Überprüfen, ob die E-Mail-Adresse bereits verwendet wird (außer vom aktuellen Benutzer)
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Diese E-Mail-Adresse wird bereits verwendet.';
            header('Location: ' . Path::url('/users/edit?id=' . $userId));
            exit;
        }
        
        // Benutzer in der Datenbank aktualisieren
        if (!empty($password)) {
            // Überprüfen, ob die Passwörter übereinstimmen, falls ein neues Passwort gesetzt werden soll
            if ($password !== $password_confirm) {
                $_SESSION['error'] = 'Die Passwörter stimmen nicht überein.';
                header('Location: ' . Path::url('/users/edit?id=' . $userId));
                exit;
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare('UPDATE users SET name = ?, email = ?, password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $result = $stmt->execute([$name, $email, $hashedPassword, $userId]);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET name = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $result = $stmt->execute([$name, $email, $userId]);
        }
        
        if ($result) {
            $_SESSION['success'] = 'Benutzer wurde erfolgreich aktualisiert.';
            header('Location: ' . Path::url('/users'));
        } else {
            $_SESSION['error'] = 'Fehler beim Aktualisieren des Benutzers.';
            header('Location: ' . Path::url('/users/edit?id=' . $userId));
        }
        exit;
    }
    
    /**
     * Löscht einen Benutzer aus der Datenbank
     */
    public function delete() {
        // Benutzer muss eingeloggt sein
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Sie müssen angemeldet sein, um diese Aktion auszuführen.';
            header('Location: ' . Path::url('/'));
            exit;
        }
        
        // Benutzer-ID aus der Anfrage holen
        $userId = $_GET['id'] ?? null;
        
        if (!$userId) {
            $_SESSION['error'] = 'Ungültige Benutzer-ID.';
            header('Location: ' . Path::url('/users'));
            exit;
        }
        
        // Eigenes Profil kann nicht gelöscht werden
        if ($userId == $_SESSION['user']['id']) {
            $_SESSION['error'] = 'Sie können Ihr eigenes Benutzerkonto nicht löschen.';
            header('Location: ' . Path::url('/users'));
            exit;
        }
        
        // Benutzer aus der Datenbank löschen
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $result = $stmt->execute([$userId]);
        
        if ($result) {
            $_SESSION['success'] = 'Benutzer wurde erfolgreich gelöscht.';
        } else {
            $_SESSION['error'] = 'Fehler beim Löschen des Benutzers.';
        }
        
        header('Location: ' . Path::url('/users'));
        exit;
    }
} 