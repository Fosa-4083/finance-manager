#!/bin/bash
# Deployment-Skript für den Expense Manager
# Dieses Skript führt ein sicheres Deployment durch, das die Datenbank schützt

# Konfiguration
APP_NAME="expense-manager"
SERVER_DATA_DIR="/var/expense-manager"
DEPLOY_DIR="/var/www/vhosts/strassl.info/httpdocs/expense-manager"
BACKUP_DIR="$SERVER_DATA_DIR/database/backups"
DB_FILE="$SERVER_DATA_DIR/database/database.sqlite"
ENV_FILE="$SERVER_DATA_DIR/.env"

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

echo "=== Deployment für $APP_NAME wird gestartet ==="

# Prüfen, ob das Skript als Root ausgeführt wird
if [ "$(id -u)" != "0" ]; then
   error_exit "Dieses Skript muss als Root ausgeführt werden."
fi

# Prüfen, ob das Zielverzeichnis existiert
if [ ! -d "$DEPLOY_DIR" ]; then
    warning "Zielverzeichnis $DEPLOY_DIR existiert nicht. Es wird erstellt."
    mkdir -p "$DEPLOY_DIR" || error_exit "Konnte Verzeichnis $DEPLOY_DIR nicht erstellen."
fi

# Prüfen, ob das Datenverzeichnis existiert, sonst erstellen
if [ ! -d "$SERVER_DATA_DIR" ]; then
    warning "Datenverzeichnis $SERVER_DATA_DIR existiert nicht. Es wird erstellt."
    mkdir -p "$SERVER_DATA_DIR" || error_exit "Konnte Verzeichnis $SERVER_DATA_DIR nicht erstellen."
    mkdir -p "$BACKUP_DIR" || error_exit "Konnte Backup-Verzeichnis $BACKUP_DIR nicht erstellen."
    
    # Berechtigungen für den Webserver setzen
    chown -R www-data:www-data "$SERVER_DATA_DIR" || warning "Konnte Berechtigungen für $SERVER_DATA_DIR nicht setzen."
fi

# Umgebungsvariable für den Datenbankpfad erstellen, falls nicht vorhanden
if [ ! -f "$ENV_FILE" ]; then
    warning "Umgebungsdatei $ENV_FILE existiert nicht. Sie wird erstellt."
    echo "DB_PATH=$DB_FILE" > "$ENV_FILE" || error_exit "Konnte Umgebungsdatei $ENV_FILE nicht erstellen."
    chown www-data:www-data "$ENV_FILE" || warning "Konnte Berechtigungen für $ENV_FILE nicht setzen."
fi

# Git-Repository aktualisieren
cd "$DEPLOY_DIR" || error_exit "Konnte nicht in das Verzeichnis $DEPLOY_DIR wechseln."

# Prüfen, ob es sich um ein Git-Repository handelt
if [ -d ".git" ]; then
    success "Git-Repository gefunden. Führe Pull durch..."
    git pull || error_exit "Git Pull fehlgeschlagen."
else
    warning "Kein Git-Repository gefunden. Klone das Repository..."
    # Sichern des aktuellen Inhalts, falls vorhanden
    if [ "$(ls -A $DEPLOY_DIR)" ]; then
        TEMP_DIR=$(mktemp -d)
        mv "$DEPLOY_DIR"/* "$TEMP_DIR/" || error_exit "Konnte bestehende Dateien nicht sichern."
        success "Bestehende Dateien wurden nach $TEMP_DIR gesichert."
    fi
    
    # Repository klonen (URL muss angepasst werden)
    git clone https://github.com/Fosa-4083/finance-manager.git . || error_exit "Git Clone fehlgeschlagen."
fi

# Symlink für die Datenbank erstellen, falls nicht vorhanden
if [ ! -L "$DEPLOY_DIR/database/database.sqlite" ]; then
    # Sicherstellen, dass das Datenbankverzeichnis existiert
    mkdir -p "$DEPLOY_DIR/database" || error_exit "Konnte Verzeichnis $DEPLOY_DIR/database nicht erstellen."
    
    # Prüfen, ob eine lokale Datenbank existiert, die migriert werden muss
    if [ -f "$DEPLOY_DIR/database/database.sqlite" ] && [ ! -f "$DB_FILE" ]; then
        success "Lokale Datenbank gefunden. Migriere zu $DB_FILE..."
        mkdir -p "$(dirname "$DB_FILE")" || error_exit "Konnte Verzeichnis $(dirname "$DB_FILE") nicht erstellen."
        cp "$DEPLOY_DIR/database/database.sqlite" "$DB_FILE" || error_exit "Konnte Datenbank nicht migrieren."
        rm "$DEPLOY_DIR/database/database.sqlite" || warning "Konnte lokale Datenbank nicht entfernen."
    fi
    
    # Symlink erstellen
    ln -sf "$DB_FILE" "$DEPLOY_DIR/database/database.sqlite" || error_exit "Konnte Symlink für Datenbank nicht erstellen."
    success "Symlink für Datenbank wurde erstellt."
fi

# Symlink für das Backup-Verzeichnis erstellen, falls nicht vorhanden
if [ ! -L "$DEPLOY_DIR/database/backups" ]; then
    # Prüfen, ob ein lokales Backup-Verzeichnis existiert, das migriert werden muss
    if [ -d "$DEPLOY_DIR/database/backups" ] && [ "$(ls -A $DEPLOY_DIR/database/backups)" ]; then
        success "Lokale Backups gefunden. Migriere zu $BACKUP_DIR..."
        mkdir -p "$BACKUP_DIR" || error_exit "Konnte Verzeichnis $BACKUP_DIR nicht erstellen."
        cp -r "$DEPLOY_DIR/database/backups"/* "$BACKUP_DIR/" || warning "Konnte Backups nicht vollständig migrieren."
        rm -rf "$DEPLOY_DIR/database/backups" || warning "Konnte lokales Backup-Verzeichnis nicht entfernen."
    fi
    
    # Symlink erstellen
    ln -sf "$BACKUP_DIR" "$DEPLOY_DIR/database/backups" || error_exit "Konnte Symlink für Backup-Verzeichnis nicht erstellen."
    success "Symlink für Backup-Verzeichnis wurde erstellt."
fi

# Berechtigungen für den Webserver setzen
chown -R www-data:www-data "$DEPLOY_DIR" || warning "Konnte Berechtigungen für $DEPLOY_DIR nicht setzen."
chmod -R 755 "$DEPLOY_DIR" || warning "Konnte Zugriffsrechte für $DEPLOY_DIR nicht setzen."

# Datenbank-Setup ausführen, falls die Datenbank nicht existiert
if [ ! -f "$DB_FILE" ]; then
    success "Keine Datenbank gefunden. Führe Setup aus..."
    cd "$DEPLOY_DIR" && php setup_database.php || error_exit "Datenbank-Setup fehlgeschlagen."
fi

success "=== Deployment für $APP_NAME wurde erfolgreich abgeschlossen ==="
echo "Anwendung ist verfügbar unter: https://strassl.info/expense-manager"
echo "Datenbank-Pfad: $DB_FILE"
echo "Backup-Verzeichnis: $BACKUP_DIR" 