<?php
// Prüfen, ob die Anfrage an die Vorschlagsroute geht
$isApiRequest = strpos($_SERVER['REQUEST_URI'], '/expenses/suggestions') !== false;

// Debug-Ausgaben nur anzeigen, wenn es keine API-Anfrage ist
if (!$isApiRequest) {
    // Testausgabe, um zu überprüfen, ob diese Datei geladen wird
    echo "<!-- public/index.php wurde am " . date('Y-m-d H:i:s') . " aktualisiert -->";
}

// Konfiguration laden
require_once __DIR__ . '/../config/config.php';

// Basisklassen laden
require_once __DIR__ . '/../src/Utils/Database.php';
require_once __DIR__ . '/../src/Models/BaseModel.php';
require_once __DIR__ . '/../src/Controllers/BaseController.php';

// Klassen laden
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/Models/Expense.php';
require_once __DIR__ . '/../src/Models/Project.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Category.php';
require_once __DIR__ . '/../src/Models/ExpenseGoal.php';
require_once __DIR__ . '/../src/Controllers/CategoryController.php';
require_once __DIR__ . '/../src/Controllers/ExpenseController.php';
require_once __DIR__ . '/../src/Controllers/ExpenseGoalController.php';
require_once __DIR__ . '/../src/Controllers/DashboardController.php';
require_once __DIR__ . '/../src/Controllers/ProjectController.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Utils/Session.php';
require_once __DIR__ . '/../src/Utils/Path.php';
require_once __DIR__ . '/../src/Utils/Backup.php';

use Controllers\CategoryController;
use Controllers\ExpenseController;
use Controllers\ExpenseGoalController;
use Controllers\DashboardController;
use Controllers\ProjectController;
use Controllers\AuthController;
use Controllers\AdminController;
use Controllers\UserController;
use Utils\Session;
use Utils\Path;
use Utils\Database;

// Session starten
Session::start();
$session = Session::getInstance();

// Datenbank initialisieren
try {
    // Konfiguration laden und anzeigen (nur für Debugging)
    $config = require __DIR__ . '/../config/database.php';
    
    if (!$isApiRequest) {
        echo "<!-- Datenbank-Konfiguration: " . htmlspecialchars(print_r($config, true)) . " -->";
    }
    
    $db = new Database();
    
    if (!$isApiRequest) {
        $dbInfo = "<!-- MariaDB-Verbindung hergestellt. Datenbank: " . $db->getDbName() . " -->";
        echo $dbInfo;
    }
    
    // Prüfen, ob der Benutzer bereits angemeldet ist
    if (!$session->isLoggedIn()) {
        // Prüfen, ob ein Remember-Me-Cookie vorhanden ist
        $rememberCookie = $session->getRememberMeCookie();
        if ($rememberCookie && isset($rememberCookie['user_id']) && isset($rememberCookie['token'])) {
            // Benutzer anhand des Tokens suchen
            $user = new Models\User($db);
            if ($user->findByRememberToken($rememberCookie['user_id'], $rememberCookie['token'])) {
                // Benutzer in der Session speichern
                $session->setUser(
                    $user->getId(),
                    $user->getEmail(),
                    $user->getName()
                );
                
                // Token erneuern
                $newToken = bin2hex(random_bytes(32));
                $user->saveRememberToken($newToken);
                $session->setRememberMeCookie($user->getId(), $newToken);
                
                if (!$isApiRequest) {
                    echo "<!-- Benutzer wurde über Remember-Me-Cookie angemeldet: " . $user->getEmail() . " -->";
                }
            } else {
                // Ungültiges Token, Cookie löschen
                $session->clearRememberMeCookie();
                
                if (!$isApiRequest) {
                    echo "<!-- Ungültiges Remember-Me-Cookie wurde gelöscht -->";
                }
            }
        }
    }
} catch (\PDOException $e) {
    if (!$isApiRequest) {
        echo "<!-- Datenbankfehler: " . htmlspecialchars($e->getMessage()) . " -->";
    }
    die("Datenbankverbindung konnte nicht hergestellt werden. Bitte überprüfen Sie die Konfiguration.");
} catch (\Exception $e) {
    if (!$isApiRequest) {
        echo "<!-- Fehler: " . htmlspecialchars($e->getMessage()) . " -->";
    }
    // Fehler beim Verarbeiten des Remember-Me-Cookies sollte die Anwendung nicht blockieren
}

$router = new Router($db);
$router->setIsApiRequest($isApiRequest);

// Basispfad für die Anwendung setzen (z.B. /expense-manager)
$basePath = '';
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/^(\/[^\/]+)\//', $requestUri, $matches)) {
    $basePath = $matches[1];
}
$router->setBasePath($basePath);
Path::setBasePath($basePath);

// Debug-Ausgabe für den Basispfad
if (!$isApiRequest) {
    echo "<!-- Debug: Basispfad = " . htmlspecialchars($basePath) . ", Request URI = " . htmlspecialchars($requestUri) . " -->";
}

// Authentifizierungs-Routen (nur für Gäste)
$router->addRoute('/login', 'Controllers\AuthController', 'showLoginForm', false, true);
$router->addRoute('/login/process', 'Controllers\AuthController', 'login', false, true);
$router->addRoute('/logout', 'Controllers\AuthController', 'logout', true);

// Dashboard als Startseite (erfordert Authentifizierung)
$router->addRoute('/', 'Controllers\DashboardController', 'index', true);

// Routen für Kategorien (erfordern Authentifizierung)
$router->addRoute('/categories', 'Controllers\CategoryController', 'index', true);
$router->addRoute('/categories/create', 'Controllers\CategoryController', 'create', true);
$router->addRoute('/categories/store', 'Controllers\CategoryController', 'store', true);
$router->addRoute('/categories/edit', 'Controllers\CategoryController', 'edit', true);
$router->addRoute('/categories/update', 'Controllers\CategoryController', 'update', true);
$router->addRoute('/categories/delete', 'Controllers\CategoryController', 'delete', true);

// Routen für Ausgaben (erfordern Authentifizierung)
$router->addRoute('/expenses', 'Controllers\ExpenseController', 'index', true);
$router->addRoute('/expenses/create', 'Controllers\ExpenseController', 'create', true);
$router->addRoute('/expenses/store', 'Controllers\ExpenseController', 'store', true);
$router->addRoute('/expenses/edit', 'Controllers\ExpenseController', 'edit', true);
$router->addRoute('/expenses/update', 'Controllers\ExpenseController', 'update', true);
$router->addRoute('/expenses/delete', 'Controllers\ExpenseController', 'delete', true);
$router->addRoute('/expenses/bulk-update', 'Controllers\ExpenseController', 'bulkUpdate', true);
$router->addRoute('/expenses/suggestions', 'Controllers\ExpenseController', 'getSuggestions', true);

// Routen für Ausgabenziele (erfordern Authentifizierung)
$router->addRoute('/expense-goals', 'Controllers\ExpenseGoalController', 'index', true);
$router->addRoute('/expense-goals/create', 'Controllers\ExpenseGoalController', 'create', true);
$router->addRoute('/expense-goals/store', 'Controllers\ExpenseGoalController', 'store', true);
$router->addRoute('/expense-goals/edit', 'Controllers\ExpenseGoalController', 'edit', true);
$router->addRoute('/expense-goals/update', 'Controllers\ExpenseGoalController', 'update', true);
$router->addRoute('/expense-goals/delete', 'Controllers\ExpenseGoalController', 'delete', true);

// Routen für Projekte (erfordern Authentifizierung)
$router->addRoute('/projects', 'Controllers\ProjectController', 'index', true);
$router->addRoute('/projects/create', 'Controllers\ProjectController', 'create', true);
$router->addRoute('/projects/store', 'Controllers\ProjectController', 'store', true);
$router->addRoute('/projects/edit', 'Controllers\ProjectController', 'edit', true);
$router->addRoute('/projects/update', 'Controllers\ProjectController', 'update', true);
$router->addRoute('/projects/delete', 'Controllers\ProjectController', 'delete', true);
$router->addRoute('/projects/show', 'Controllers\ProjectController', 'show', true);

// Routen für Admin-Funktionen (erfordern Admin-Rechte)
$router->addRoute('/admin/backups', 'Controllers\AdminController', 'backups', true);
$router->addRoute('/admin/create-backup', 'Controllers\AdminController', 'createBackup', true);
$router->addRoute('/admin/restore-backup', 'Controllers\AdminController', 'restoreBackup', true);
$router->addRoute('/admin/delete-backup', 'Controllers\AdminController', 'deleteBackup', true);

// Routen für Benutzerverwaltung (erfordern Authentifizierung)
$router->addRoute('/users', 'Controllers\UserController', 'index', true);
$router->addRoute('/users/create', 'Controllers\UserController', 'create', true);
$router->addRoute('/users/store', 'Controllers\UserController', 'store', true);
$router->addRoute('/users/edit', 'Controllers\UserController', 'edit', true);
$router->addRoute('/users/update', 'Controllers\UserController', 'update', true);
$router->addRoute('/users/delete', 'Controllers\UserController', 'delete', true);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($requestUri); 