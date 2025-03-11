<?php

namespace Controllers;

use Utils\Database;
use PDO;

/**
 * Basisklasse für alle Controller
 */
abstract class BaseController {
    /**
     * Datenbankverbindung
     */
    protected PDO $db;

    /**
     * Konstruktor
     * 
     * @param PDO|null $db Datenbankverbindung (optional)
     */
    public function __construct(?PDO $db = null) {
        if ($db) {
            // Nutze die übergebene Datenbankverbindung
            $this->db = $db;
        } else {
            // Erstelle eine neue Datenbankverbindung
            $this->db = new Database();
        }
    }
} 