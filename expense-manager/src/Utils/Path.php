<?php

namespace Utils;

class Path {
    private static $basePath = '';

    /**
     * Basispfad für die Anwendung setzen
     * 
     * @param string $path Basispfad (z.B. /expense-manager)
     */
    public static function setBasePath($path) {
        self::$basePath = $path;
    }

    /**
     * Basispfad für die Anwendung abrufen
     * 
     * @return string Basispfad
     */
    public static function getBasePath() {
        return self::$basePath;
    }

    /**
     * URL mit Basispfad generieren
     * 
     * @param string $path Pfad (z.B. /expenses)
     * @return string Vollständige URL mit Basispfad
     */
    public static function url($path) {
        // Wenn der Pfad mit einem Schrägstrich beginnt, entferne ihn
        if (substr($path, 0, 1) === '/') {
            $path = substr($path, 1);
        }

        // Wenn der Basispfad leer ist, gib nur den Pfad zurück
        if (empty(self::$basePath)) {
            return '/' . $path;
        }

        // Ansonsten füge den Basispfad hinzu
        return self::$basePath . '/' . $path;
    }
} 