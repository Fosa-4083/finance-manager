<?php

namespace Models;

use PDO;

class User extends BaseModel {
    private $id;
    private $email;
    private $name;
    private $password;
    private $created_at;
    private $updated_at;
    private $last_login;

    public function __construct($db = null) {
        // Basisklassen-Konstruktor aufrufen
        parent::__construct($db);
    }

    /**
     * Benutzer anhand der ID laden
     * 
     * @param int $id Benutzer-ID
     * @return bool Erfolg des Ladevorgangs
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $this->setProperties($user);
            return true;
        }
        
        return false;
    }

    /**
     * Benutzer anhand der E-Mail-Adresse laden
     * 
     * @param string $email E-Mail-Adresse
     * @return bool Erfolg des Ladevorgangs
     */
    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $this->setProperties($user);
            return true;
        }
        
        return false;
    }

    /**
     * Benutzer erstellen
     * 
     * @param string $email E-Mail-Adresse
     * @param string $password Passwort (unverschlüsselt)
     * @param string $name Name des Benutzers
     * @return bool Erfolg der Erstellung
     */
    public function create($email, $password, $name) {
        // Prüfen, ob die E-Mail-Adresse bereits existiert
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            return false; // E-Mail-Adresse existiert bereits
        }
        
        // Passwort hashen
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Benutzer erstellen
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password, name, created_at, updated_at) 
            VALUES (:email, :password, :name, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            $this->email = $email;
            $this->name = $name;
            $this->password = $hashedPassword;
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = date('Y-m-d H:i:s');
            return true;
        }
        
        return false;
    }

    /**
     * Benutzer aktualisieren
     * 
     * @param array $data Zu aktualisierende Daten
     * @return bool Erfolg der Aktualisierung
     */
    public function update($data) {
        if (!$this->id) {
            return false;
        }
        
        $allowedFields = ['name', 'email'];
        $updates = [];
        $params = [':id' => $this->id];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET " . implode(', ', $updates) . " 
            WHERE id = :id
        ");
        
        if ($stmt->execute($params)) {
            // Eigenschaften aktualisieren
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $this->$key = $value;
                }
            }
            $this->updated_at = date('Y-m-d H:i:s');
            return true;
        }
        
        return false;
    }

    /**
     * Passwort ändern
     * 
     * @param string $currentPassword Aktuelles Passwort
     * @param string $newPassword Neues Passwort
     * @return bool Erfolg der Passwortänderung
     */
    public function changePassword($currentPassword, $newPassword) {
        if (!$this->id || !password_verify($currentPassword, $this->password)) {
            return false;
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password = :password, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $this->password = $hashedPassword;
            $this->updated_at = date('Y-m-d H:i:s');
            return true;
        }
        
        return false;
    }

    /**
     * Passwort zurücksetzen (ohne Überprüfung des aktuellen Passworts)
     * 
     * @param string $newPassword Neues Passwort
     * @return bool Erfolg der Passwortänderung
     */
    public function resetPassword($newPassword) {
        if (!$this->id) {
            return false;
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password = :password, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $this->password = $hashedPassword;
            $this->updated_at = date('Y-m-d H:i:s');
            return true;
        }
        
        return false;
    }

    /**
     * Benutzer löschen
     * 
     * @return bool Erfolg des Löschvorgangs
     */
    public function delete() {
        if (!$this->id) {
            return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Benutzer authentifizieren
     * 
     * @param string $email E-Mail-Adresse
     * @param string $password Passwort
     * @return bool Erfolg der Authentifizierung
     */
    public function authenticate($email, $password) {
        error_log("Versuche Authentifizierung für E-Mail: " . $email);
        if (!$this->getByEmail($email)) {
            error_log("Benutzer nicht gefunden");
            return false;
        }
        
        error_log("Gespeichertes Passwort: " . $this->password);
        error_log("Eingegebenes Passwort: " . $password);
        
        if (password_verify($password, $this->password)) {
            error_log("Passwort korrekt");
            // Letzten Login aktualisieren
            $stmt = $this->db->prepare("
                UPDATE users 
                SET last_login = CURRENT_TIMESTAMP 
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        }
        
        error_log("Passwort falsch");
        return false;
    }

    /**
     * Alle Benutzer abrufen
     * 
     * @return array Liste aller Benutzer
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT id, email, name, created_at, last_login FROM users ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Eigenschaften aus einem Datensatz setzen
     * 
     * @param array $data Datensatz
     */
    private function setProperties($data) {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->name = $data['name'];
        $this->password = $data['password'];
        $this->created_at = $data['created_at'];
        $this->updated_at = $data['updated_at'];
        $this->last_login = $data['last_login'];
    }

    // Getter-Methoden
    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getName() {
        return $this->name;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function getLastLogin() {
        return $this->last_login;
    }

    /**
     * Speichert ein Remember-Me-Token für den Benutzer
     * 
     * @param string $token Das zu speichernde Token
     * @return bool Erfolg des Speichervorgangs
     */
    public function saveRememberToken($token) {
        if (!$this->id) {
            return false;
        }
        
        try {
            // Zuerst prüfen, ob bereits ein Token existiert
            $stmt = $this->db->prepare("SELECT id FROM user_tokens WHERE user_id = :user_id AND type = 'remember_me'");
            $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            
            $tokenExists = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($tokenExists) {
                // Token aktualisieren
                $stmt = $this->db->prepare("
                    UPDATE user_tokens 
                    SET token = :token, expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
                    WHERE user_id = :user_id AND type = 'remember_me'
                ");
            } else {
                // Neues Token erstellen
                $stmt = $this->db->prepare("
                    INSERT INTO user_tokens (user_id, token, type, expires_at)
                    VALUES (:user_id, :token, 'remember_me', DATE_ADD(NOW(), INTERVAL 30 DAY))
                ");
            }
            
            $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Fehler beim Speichern des Remember-Tokens: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Benutzer anhand eines Remember-Me-Tokens finden
     * 
     * @param int $userId Benutzer-ID
     * @param string $token Remember-Me-Token
     * @return bool Erfolg des Ladevorgangs
     */
    public function findByRememberToken($userId, $token) {
        try {
            // Token in der Datenbank suchen
            $stmt = $this->db->prepare("
                SELECT u.* 
                FROM users u
                JOIN user_tokens t ON u.id = t.user_id
                WHERE u.id = :user_id 
                AND t.token = :token 
                AND t.type = 'remember_me'
                AND t.expires_at > NOW()
            ");
            
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $this->setProperties($user);
                
                // Letzten Login aktualisieren
                $this->updateLastLogin();
                
                return true;
            }
            
            return false;
        } catch (\PDOException $e) {
            error_log("Fehler beim Suchen des Remember-Tokens: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Letzten Login-Zeitpunkt aktualisieren
     */
    private function updateLastLogin() {
        if (!$this->id) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Fehler beim Aktualisieren des letzten Logins: " . $e->getMessage());
            return false;
        }
    }
} 