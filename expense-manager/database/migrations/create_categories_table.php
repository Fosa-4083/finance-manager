<?php

use PDO;

class CreateCategoriesTable {
    public function up(PDO $db) {
        $sql = "CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            color TEXT NOT NULL DEFAULT '#000000'
        )";
        
        $db->exec($sql);
    }

    public function down(PDO $db) {
        $db->exec("DROP TABLE IF EXISTS categories");
    }
} 