<?php

use Utils\Session;

class Router {
    private $routes = [];
    private $authRoutes = [];
    private $guestRoutes = [];
    private $db;
    private $basePath = '';
    private $isApiRequest = false;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Basispfad für die Anwendung setzen
     * 
     * @param string $path Basispfad (z.B. /expense-manager)
     */
    public function setBasePath($path) {
        $this->basePath = $path;
    }
    
    /**
     * Setzt, ob es sich um eine API-Anfrage handelt
     * 
     * @param bool $isApiRequest Gibt an, ob es sich um eine API-Anfrage handelt
     */
    public function setIsApiRequest($isApiRequest) {
        $this->isApiRequest = $isApiRequest;
    }

    /**
     * Route hinzufügen
     * 
     * @param string $path Pfad
     * @param string|callable $handler Controller-Klasse oder Closure
     * @param string|null $action Controller-Methode oder null, wenn Handler eine Closure ist
     * @param bool $requiresAuth Gibt an, ob die Route eine Authentifizierung erfordert
     * @param bool $guestOnly Gibt an, ob die Route nur für nicht angemeldete Benutzer zugänglich ist
     */
    public function addRoute($path, $handler, $action = null, $requiresAuth = false, $guestOnly = false) {
        if ($action === null) {
            // Handler ist eine Closure
            $this->routes[$path] = ['handler' => $handler];
        } else {
            // Handler ist ein Controller mit Action
            $this->routes[$path] = [
                'controller' => $handler,
                'action' => $action
            ];
        }

        if ($requiresAuth) {
            $this->authRoutes[] = $path;
        }

        if ($guestOnly) {
            $this->guestRoutes[] = $path;
        }
    }

    /**
     * Anfrage verarbeiten
     * 
     * @param string $uri Anfrage-URI
     */
    public function dispatch($uri) {
        // Debug-Ausgabe
        if (!$this->isApiRequest) {
            echo "<!-- Debug Router: URI = " . htmlspecialchars($uri) . ", Basispfad = " . htmlspecialchars($this->basePath) . " -->";
        }
        
        // Basispfad aus URI entfernen, wenn vorhanden
        if (!empty($this->basePath) && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        
        // Sicherstellen, dass der Pfad mit einem Schrägstrich beginnt
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        // Debug-Ausgabe nach Basispfad-Entfernung
        if (!$this->isApiRequest) {
            echo "<!-- Debug Router: Bereinigter URI = " . htmlspecialchars($uri) . " -->";
        }
        
        // URI-Parameter extrahieren (z.B. /projects/edit?id=1)
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Session starten
        $session = Session::getInstance();

        // Prüfen, ob die Route eine Authentifizierung erfordert
        if (in_array($path, $this->authRoutes) && !$session->isLoggedIn()) {
            $session->setFlash('error', 'Bitte melden Sie sich an, um auf diese Seite zuzugreifen.');
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        // Prüfen, ob die Route nur für nicht angemeldete Benutzer zugänglich ist
        if (in_array($path, $this->guestRoutes) && $session->isLoggedIn()) {
            header('Location: ' . $this->basePath . '/');
            exit;
        }

        if (isset($this->routes[$path])) {
            $route = $this->routes[$path];
            
            if (isset($route['handler'])) {
                // Closure ausführen
                return $route['handler']();
            } else {
                // Controller-Action ausführen
                $controllerClass = $route['controller'];
                $controller = new $controllerClass($this->db);
                $action = $route['action'];
                
                // GET-Parameter an die Action übergeben
                $params = $_GET;
                
                if (!empty($params)) {
                    return $controller->$action(...array_values($params));
                } else {
                    return $controller->$action();
                }
            }
        }
        
        return $this->notFound();
    }

    /**
     * 404-Seite anzeigen
     */
    private function notFound() {
        header("HTTP/1.0 404 Not Found");
        include __DIR__ . '/Views/404.php';
    }
} 