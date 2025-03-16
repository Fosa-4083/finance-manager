<?php
// Prüfen, ob die Anfrage an die Vorschlagsroute geht
$isApiRequest = strpos($_SERVER['REQUEST_URI'], '/expenses/suggestions') !== false;

// Debug-Ausgaben nur anzeigen, wenn es keine API-Anfrage ist
if (!$isApiRequest) {
    // Testausgabe, um zu überprüfen, ob diese Datei geladen wird
    echo "<!-- Diese Datei wurde am " . date('Y-m-d H:i:s') . " aktualisiert -->";
}

// Weiterleitung zur public/index.php
require_once __DIR__ . '/public/index.php'; 