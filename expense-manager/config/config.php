<?php

// Datenbankkonfiguration
const DB_PATH = __DIR__ . '/../database/database.sqlite';
const BASE_PATH = __DIR__ . '/../';
const VIEW_PATH = __DIR__ . '/../src/Views/';

// Zeitzone einstellen
date_default_timezone_set('Europe/Vienna');

// Fehlerberichterstattung aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sitzung starten
session_start(); 