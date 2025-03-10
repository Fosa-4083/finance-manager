<?php

namespace Commands;

use Utils\DeploymentManager;

class DeployCommand {
    private $args;
    private $options;

    /**
     * Konstruktor für das Deploy-Kommando
     * 
     * @param array $args Kommandozeilenargumente
     */
    public function __construct($args = []) {
        $this->args = $args;
        $this->options = $this->parseOptions();
    }

    /**
     * Optionen aus den Kommandozeilenargumenten parsen
     * 
     * @return array Geparste Optionen
     */
    private function parseOptions() {
        $options = [
            'host' => null,
            'username' => null,
            'password' => null,
            'port' => 21,
            'ssl' => false,
            'remote-path' => '/',
            'exclude' => [],
            'backup-db' => false,
            'changed-only' => false,
            'overwrite' => true,
            'help' => false
        ];

        foreach ($this->args as $i => $arg) {
            if ($i === 0) continue; // Überspringen des Skriptnamens
            
            if (strpos($arg, '--') === 0) {
                $option = substr($arg, 2);
                if (strpos($option, '=') !== false) {
                    list($key, $value) = explode('=', $option, 2);
                    if ($key === 'exclude') {
                        $options[$key][] = $value;
                    } else {
                        $options[$key] = $value;
                    }
                } else {
                    $options[$option] = true;
                }
            }
        }

        return $options;
    }

    /**
     * Hilfetext anzeigen
     */
    private function showHelp() {
        echo "Verwendung: php deploy.php [Optionen]\n";
        echo "\n";
        echo "Optionen:\n";
        echo "  --host=HOSTNAME       FTP-Server-Hostname oder IP\n";
        echo "  --username=USERNAME   Benutzername für die Anmeldung\n";
        echo "  --password=PASSWORD   Passwort für die Anmeldung\n";
        echo "  --port=PORT           FTP-Port (Standard: 21)\n";
        echo "  --ssl                 SSL/TLS-Verbindung verwenden\n";
        echo "  --remote-path=PATH    Basispfad auf dem FTP-Server (Standard: /)\n";
        echo "  --exclude=PATH        Pfad vom Deployment ausschließen (kann mehrfach angegeben werden)\n";
        echo "  --backup-db           Datenbank-Backup erstellen und hochladen\n";
        echo "  --changed-only        Nur geänderte Dateien hochladen\n";
        echo "  --no-overwrite        Bestehende Dateien nicht überschreiben\n";
        echo "  --help                Diese Hilfe anzeigen\n";
        echo "\n";
        echo "Beispiel:\n";
        echo "  php deploy.php --host=ftp.example.com --username=user --password=pass --remote-path=/www/expense-manager\n";
    }

    /**
     * Kommando ausführen
     * 
     * @return int Exit-Code (0 für Erfolg, 1 für Fehler)
     */
    public function execute() {
        if ($this->options['help']) {
            $this->showHelp();
            return 0;
        }

        // Prüfen, ob die erforderlichen Optionen angegeben wurden
        if (!$this->options['host'] || !$this->options['username'] || !$this->options['password']) {
            echo "Fehler: Host, Benutzername und Passwort müssen angegeben werden.\n";
            $this->showHelp();
            return 1;
        }

        // Lokalen Basispfad ermitteln (Verzeichnis der Anwendung)
        $localBasePath = dirname(dirname(__DIR__));

        // DeploymentManager initialisieren
        $deploymentManager = new DeploymentManager(
            $this->options['host'],
            $this->options['username'],
            $this->options['password'],
            $localBasePath,
            $this->options['remote-path'],
            $this->options['exclude'],
            (int)$this->options['port'],
            $this->options['ssl']
        );

        // Verbindung herstellen
        echo "Verbindung zum FTP-Server wird hergestellt...\n";
        if (!$deploymentManager->connect()) {
            echo "Fehler: " . $deploymentManager->getLastError() . "\n";
            return 1;
        }

        // Deployment durchführen
        echo "Deployment wird gestartet...\n";
        $success = false;

        if ($this->options['changed-only']) {
            echo "Nur geänderte Dateien werden hochgeladen...\n";
            $success = $deploymentManager->deployChangedFiles();
        } else {
            echo "Alle Dateien werden hochgeladen...\n";
            $success = $deploymentManager->deploy(true, $this->options['overwrite']);
        }

        if (!$success) {
            echo "Fehler beim Deployment: " . $deploymentManager->getLastError() . "\n";
            $deploymentManager->disconnect();
            return 1;
        }

        // Datenbank-Backup erstellen und hochladen, wenn gewünscht
        if ($this->options['backup-db']) {
            echo "Datenbank-Backup wird erstellt und hochgeladen...\n";
            $databasePath = $localBasePath . '/database/database.sqlite';
            $success = $deploymentManager->backupDatabase($databasePath);

            if (!$success) {
                echo "Fehler beim Datenbank-Backup: " . $deploymentManager->getLastError() . "\n";
                $deploymentManager->disconnect();
                return 1;
            }
        }

        // Verbindung trennen
        $deploymentManager->disconnect();

        echo "Deployment erfolgreich abgeschlossen.\n";
        return 0;
    }
} 