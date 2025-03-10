<?php
// Debug-Datei, um Vorschläge direkt zu testen
define('ROOT_PATH', __DIR__ . '/');
define('CONTROLLER_PATH', ROOT_PATH . 'src/Controllers/');
define('MODEL_PATH', ROOT_PATH . 'src/Models/');
define('VIEW_PATH', ROOT_PATH . 'src/Views/');
define('UTILS_PATH', ROOT_PATH . 'src/Utils/');

// Utils-Klassen einbinden
require_once UTILS_PATH . 'Database.php';
require_once UTILS_PATH . 'Path.php';
require_once UTILS_PATH . 'Auth.php';

// Controller einbinden
require_once CONTROLLER_PATH . 'ExpenseController.php';

// Header für JSON-Output
header('Content-Type: application/json');

// Debug-Einstellungen
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Parameter aus der URL holen
$field = $_GET['field'] ?? 'description';
$query = $_GET['query'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$project_id = $_GET['project_id'] ?? '';

// ExpenseController instanziieren
$controller = new \Controllers\ExpenseController();

// Direkte Ausgabe der Vorschläge für Debugging
$suggestions = $controller->getSuggestions($field, $query, $category_id, $project_id);
echo json_encode($suggestions); 