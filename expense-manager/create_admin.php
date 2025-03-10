<?php

require_once __DIR__ . '/src/Models/User.php';

try {
    $db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $user = new Models\User($db);
    if ($user->create('admin@example.com', 'admin123', 'Administrator')) {
        echo "Admin-Benutzer erfolgreich erstellt!\n";
    } else {
        echo "Fehler beim Erstellen des Admin-Benutzers.\n";
    }
} catch (Exception $e) {
    echo "Fehler: " . $e->getMessage() . "\n";
} 