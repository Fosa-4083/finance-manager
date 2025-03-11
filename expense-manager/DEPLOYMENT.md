# Deployment-Anleitung für den Expense Manager

Diese Anleitung beschreibt, wie der Expense Manager auf einem Webserver installiert und aktualisiert werden kann, ohne dass die Datenbank überschrieben wird.

## Übersicht

Das Deployment-Konzept basiert auf folgenden Prinzipien:

1. Die Datenbank wird außerhalb des Deployment-Verzeichnisses gespeichert
2. Symlinks verbinden die Anwendung mit der Datenbank
3. Ein Deployment-Skript automatisiert den Prozess

## Verzeichnisstruktur

```
/var/www/html/expense-manager/  <-- Deployment-Verzeichnis (wird bei Updates überschrieben)
/var/data/expense-manager/      <-- Persistentes Datenverzeichnis (bleibt bei Updates erhalten)
  ├── database.sqlite           <-- Datenbank-Datei
  ├── .env                      <-- Umgebungsvariablen
  └── backups/                  <-- Backup-Verzeichnis
```

## Erstinstallation

1. Laden Sie das Deployment-Skript herunter:

```bash
wget -O /tmp/deploy.sh https://raw.githubusercontent.com/Fosa-4083/finance-manager/main/deploy.sh
chmod +x /tmp/deploy.sh
```

2. Führen Sie das Skript als Root aus:

```bash
sudo /tmp/deploy.sh
```

Das Skript führt folgende Aktionen aus:
- Erstellt die notwendigen Verzeichnisse
- Klont das Git-Repository
- Erstellt Symlinks für die Datenbank und Backups
- Führt das Datenbank-Setup aus, falls nötig
- Setzt die korrekten Berechtigungen

## Updates

Um die Anwendung zu aktualisieren, führen Sie einfach das Deployment-Skript erneut aus:

```bash
sudo /var/www/html/expense-manager/deploy.sh
```

Das Skript aktualisiert das Git-Repository, während die Datenbank und Backups erhalten bleiben.

## Manuelle Installation

Falls Sie das Deployment-Skript nicht verwenden möchten, können Sie die Installation auch manuell durchführen:

1. Erstellen Sie die Verzeichnisse:

```bash
sudo mkdir -p /var/www/html/expense-manager
sudo mkdir -p /var/data/expense-manager/backups
```

2. Klonen Sie das Repository:

```bash
cd /var/www/html/expense-manager
sudo git clone https://github.com/Fosa-4083/finance-manager.git .
```

3. Erstellen Sie die Umgebungsdatei:

```bash
echo "DB_PATH=/var/data/expense-manager/database.sqlite" | sudo tee /var/data/expense-manager/.env
```

4. Erstellen Sie die Symlinks:

```bash
sudo ln -sf /var/data/expense-manager/database.sqlite /var/www/html/expense-manager/database/database.sqlite
sudo ln -sf /var/data/expense-manager/backups /var/www/html/expense-manager/database/backups
```

5. Setzen Sie die Berechtigungen:

```bash
sudo chown -R www-data:www-data /var/www/html/expense-manager
sudo chown -R www-data:www-data /var/data/expense-manager
sudo chmod -R 755 /var/www/html/expense-manager
```

6. Führen Sie das Datenbank-Setup aus:

```bash
cd /var/www/html/expense-manager
sudo php setup_database.php
```

## Fehlerbehebung

### Die Datenbank ist nicht erreichbar

Prüfen Sie, ob die Symlinks korrekt erstellt wurden:

```bash
ls -la /var/www/html/expense-manager/database/
```

Prüfen Sie, ob die Umgebungsdatei existiert und korrekt ist:

```bash
cat /var/data/expense-manager/.env
```

### Berechtigungsprobleme

Stellen Sie sicher, dass der Webserver Schreibrechte für die Datenbank hat:

```bash
sudo chown -R www-data:www-data /var/data/expense-manager
sudo chmod -R 755 /var/data/expense-manager
```

## Sicherheit

- Die Datenbank wird außerhalb des Webroot-Verzeichnisses gespeichert
- Das Deployment-Skript erstellt automatisch Backups vor Änderungen
- Die Anwendung erstellt täglich automatische Backups

## Anpassung

Sie können die Pfade im Deployment-Skript anpassen, indem Sie die Variablen am Anfang des Skripts ändern:

```bash
APP_NAME="expense-manager"
SERVER_DATA_DIR="/var/data/$APP_NAME"
DEPLOY_DIR="/var/www/html/$APP_NAME"
``` 