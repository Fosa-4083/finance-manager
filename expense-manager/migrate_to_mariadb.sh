#!/bin/bash
# Migration von SQLite zu MariaDB für den Expense Manager
# Dieses Skript konvertiert die SQLite-Datenbank zu MariaDB und importiert sie

# Farben für die Ausgabe
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Hilfsfunktion für Fehler
error_exit() {
    echo -e "${RED}FEHLER: $1${NC}" >&2
    exit 1
}

# Hilfsfunktion für Erfolg
success() {
    echo -e "${GREEN}$1${NC}"
}

# Hilfsfunktion für Warnungen
warning() {
    echo -e "${YELLOW}WARNUNG: $1${NC}"
}

# Konfiguration
DB_NAME="expense_manager"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SQL_FILE="$SCRIPT_DIR/mariadb_import.sql"
PHP_SCRIPT="$SCRIPT_DIR/sqlite_to_mariadb.php"

echo "=== Migration von SQLite zu MariaDB für den Expense Manager ==="

# Prüfen, ob PHP installiert ist
if ! command -v php &> /dev/null; then
    error_exit "PHP ist nicht installiert. Bitte installieren Sie PHP und versuchen Sie es erneut."
fi

# Prüfen, ob das PHP-Skript existiert
if [ ! -f "$PHP_SCRIPT" ]; then
    error_exit "Das PHP-Skript $PHP_SCRIPT existiert nicht."
fi

# Prüfen, ob MariaDB/MySQL installiert ist
if ! command -v mysql &> /dev/null; then
    error_exit "MariaDB/MySQL ist nicht installiert. Bitte installieren Sie MariaDB und versuchen Sie es erneut."
fi

# SQLite zu MariaDB konvertieren
echo "Konvertiere SQLite zu MariaDB..."
php "$PHP_SCRIPT" || error_exit "Konvertierung fehlgeschlagen."

# Prüfen, ob die SQL-Datei erstellt wurde
if [ ! -f "$SQL_FILE" ]; then
    error_exit "Die SQL-Datei $SQL_FILE wurde nicht erstellt."
fi

# Benutzerinformationen abfragen
read -p "MariaDB-Benutzername: " DB_USER
read -sp "MariaDB-Passwort: " DB_PASS
echo ""
read -p "MariaDB-Host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}
read -p "MariaDB-Port [3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}

# Prüfen, ob die Datenbank bereits existiert
echo "Prüfe, ob die Datenbank $DB_NAME bereits existiert..."
if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME" 2>/dev/null; then
    warning "Die Datenbank $DB_NAME existiert bereits."
    read -p "Möchten Sie die bestehende Datenbank überschreiben? (j/n): " OVERWRITE
    if [ "$OVERWRITE" != "j" ]; then
        error_exit "Migration abgebrochen."
    fi
    
    # Datenbank löschen
    echo "Lösche bestehende Datenbank..."
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE $DB_NAME" || error_exit "Konnte Datenbank nicht löschen."
fi

# Datenbank erstellen
echo "Erstelle Datenbank $DB_NAME..."
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" || error_exit "Konnte Datenbank nicht erstellen."

# SQL-Datei importieren
echo "Importiere Daten in MariaDB..."
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE" || error_exit "Import fehlgeschlagen."

success "=== Migration erfolgreich abgeschlossen ==="
echo "Die SQLite-Datenbank wurde erfolgreich nach MariaDB migriert."
echo "Datenbank: $DB_NAME"
echo "Host: $DB_HOST:$DB_PORT"

# Konfigurationsdatei für MariaDB erstellen
CONFIG_FILE="$SCRIPT_DIR/config/database_mariadb.php"
mkdir -p "$(dirname "$CONFIG_FILE")"

cat > "$CONFIG_FILE" << EOF
<?php
/**
 * MariaDB-Konfiguration
 * 
 * Diese Datei definiert die Konfiguration für die MariaDB-Datenbankverbindung.
 */

return [
    'driver' => 'mysql',
    'host' => '$DB_HOST',
    'port' => $DB_PORT,
    'database' => '$DB_NAME',
    'username' => '$DB_USER',
    'password' => '$DB_PASS',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
];
EOF

success "Konfigurationsdatei erstellt: $CONFIG_FILE"
echo "Sie können diese Konfiguration in Ihrer Anwendung verwenden, um auf die MariaDB-Datenbank zuzugreifen." 