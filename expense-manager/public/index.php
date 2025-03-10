<?php

// Konfiguration laden
require_once __DIR__ . '/../config/config.php';

// Klassen laden
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/Models/Expense.php';
require_once __DIR__ . '/../src/Models/Project.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Controllers/CategoryController.php';
require_once __DIR__ . '/../src/Controllers/ExpenseController.php';
require_once __DIR__ . '/../src/Controllers/ExpenseGoalController.php';
require_once __DIR__ . '/../src/Controllers/DashboardController.php';
require_once __DIR__ . '/../src/Controllers/ProjectController.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Utils/Session.php';

use Controllers\CategoryController;
use Controllers\ExpenseController;
use Controllers\ExpenseGoalController;
use Controllers\DashboardController;
use Controllers\ProjectController;
use Controllers\AuthController;
use Utils\Session;

// Session starten
Session::start();
$session = Session::getInstance();

// Test-Login (nur temporär für Entwicklung)
if (!$session->isLoggedIn()) {
    $session->setUser(1, 'admin@example.com', 'Administrator');
}

// Datenbank initialisieren
$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$router = new Router($db);

// Authentifizierungs-Routen (nur für Gäste)
$router->addRoute('/login', 'Controllers\AuthController', 'showLoginForm', false, true);
$router->addRoute('/login/process', 'Controllers\AuthController', 'login', false, true);
$router->addRoute('/register', 'Controllers\AuthController', 'showRegisterForm', false, true);
$router->addRoute('/register/process', 'Controllers\AuthController', 'register', false, true);
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

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($requestUri); 