<?php

// Autoloader einbinden
require_once __DIR__ . '/vendor/autoload.php';

// Wenn der Autoloader nicht verfügbar ist, manuelle Einbindung der Klassen
if (!class_exists('Commands\DeployCommand')) {
    require_once __DIR__ . '/src/Utils/FtpClient.php';
    require_once __DIR__ . '/src/Utils/DeploymentManager.php';
    require_once __DIR__ . '/src/Commands/DeployCommand.php';
}

// Deploy-Kommando ausführen
$command = new Commands\DeployCommand($argv);
$exitCode = $command->execute();

// Exit-Code zurückgeben
exit($exitCode); 