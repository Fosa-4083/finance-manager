# Deployment-Anleitung für den Expense Manager

Diese Anleitung beschreibt, wie Sie den Expense Manager auf einen FTP-Server hochladen und dort betreiben können.

## Voraussetzungen

- PHP 8.0 oder höher auf dem Zielserver
- FTP-Zugang zum Zielserver
- SQLite-Unterstützung auf dem Zielserver
- Schreibrechte für das Verzeichnis, in dem die Anwendung installiert wird

## Deployment mit dem Deployment-Tool

Der Expense Manager enthält ein Deployment-Tool, mit dem Sie die Anwendung einfach auf einen FTP-Server hochladen können.

### Schritt 1: Deployment-Skript ausführen

Öffnen Sie ein Terminal und navigieren Sie zum Hauptverzeichnis der Anwendung. Führen Sie dann das Deployment-Skript mit den entsprechenden Parametern aus:

```bash
php deploy.php --host=ftp.example.com --username=ihr_benutzername --password=ihr_passwort --remote-path=/pfad/auf/dem/server
```

Ersetzen Sie die Parameter durch Ihre eigenen FTP-Zugangsdaten:

- `--host`: Hostname oder IP-Adresse des FTP-Servers
- `--username`: Ihr FTP-Benutzername
- `--password`: Ihr FTP-Passwort
- `--remote-path`: Pfad auf dem FTP-Server, in dem die Anwendung installiert werden soll

### Weitere Optionen

Das Deployment-Tool bietet weitere Optionen:

- `--port=21`: FTP-Port (Standard: 21)
- `--ssl`: SSL/TLS-Verbindung verwenden
- `--exclude=pfad`: Pfad vom Deployment ausschließen (kann mehrfach angegeben werden)
- `--backup-db`: Datenbank-Backup erstellen und hochladen
- `--changed-only`: Nur geänderte Dateien hochladen
- `--no-overwrite`: Bestehende Dateien nicht überschreiben
- `--help`: Hilfetext anzeigen

Beispiel für ein Deployment mit SSL und Backup der Datenbank:

```bash
php deploy.php --host=ftp.example.com --username=ihr_benutzername --password=ihr_passwort --remote-path=/pfad/auf/dem/server --ssl --backup-db
```

## Manuelles Deployment

Wenn Sie das Deployment-Tool nicht verwenden möchten, können Sie die Anwendung auch manuell auf den FTP-Server hochladen:

1. Verbinden Sie sich mit einem FTP-Client (z.B. FileZilla) mit Ihrem FTP-Server.
2. Laden Sie alle Dateien und Verzeichnisse der Anwendung auf den Server hoch, mit Ausnahme von:
   - `.git`
   - `.gitignore`
   - `node_modules`
   - `vendor/bin`
   - `tests`
   - `.env`
   - `database/database.sqlite` (falls Sie eine lokale Datenbank haben)
   - `storage/logs`
   - `storage/cache`
   - `storage/temp`

3. Stellen Sie sicher, dass die Verzeichnisberechtigungen korrekt gesetzt sind:
   - Das Verzeichnis `database` muss beschreibbar sein
   - Das Verzeichnis `storage` und alle Unterverzeichnisse müssen beschreibbar sein

## Konfiguration auf dem Server

Nach dem Deployment müssen Sie die Anwendung auf dem Server konfigurieren:

### Schritt 1: Datenbank einrichten

Wenn Sie eine neue Installation durchführen, müssen Sie die Datenbank einrichten:

1. Erstellen Sie eine leere SQLite-Datenbank im Verzeichnis `database` auf dem Server.
2. Importieren Sie die Datenbankstruktur aus der Datei `database/schema.sql`.

Wenn Sie eine bestehende Datenbank verwenden möchten, laden Sie Ihre lokale Datenbank auf den Server hoch.

### Schritt 2: Webserver-Konfiguration

Konfigurieren Sie Ihren Webserver so, dass das Verzeichnis `public` als Document Root verwendet wird. Wenn Sie keinen Zugriff auf die Webserver-Konfiguration haben, können Sie auch eine `.htaccess`-Datei im Hauptverzeichnis der Anwendung erstellen:

```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>
```

### Schritt 3: Anwendung testen

Öffnen Sie einen Webbrowser und navigieren Sie zur URL Ihrer Anwendung. Wenn alles korrekt konfiguriert ist, sollte die Anwendung nun funktionieren.

## Fehlerbehebung

### Problem: Verbindung zum FTP-Server fehlgeschlagen

- Überprüfen Sie Hostname, Benutzername und Passwort.
- Stellen Sie sicher, dass der FTP-Port nicht blockiert ist.
- Versuchen Sie es mit einer SSL/TLS-Verbindung (`--ssl`).

### Problem: Keine Schreibrechte auf dem Server

- Überprüfen Sie die Verzeichnisberechtigungen auf dem Server.
- Kontaktieren Sie Ihren Hosting-Anbieter, um die korrekten Berechtigungen zu erhalten.

### Problem: SQLite-Fehler

- Stellen Sie sicher, dass SQLite auf dem Server installiert und aktiviert ist.
- Überprüfen Sie, ob das Verzeichnis `database` beschreibbar ist.

### Problem: Anwendung zeigt eine leere Seite

- Überprüfen Sie die PHP-Fehlerprotokolle auf dem Server.
- Stellen Sie sicher, dass alle erforderlichen PHP-Erweiterungen aktiviert sind.
- Überprüfen Sie die Pfadkonfiguration in der Anwendung.

## Regelmäßige Backups

Es wird empfohlen, regelmäßige Backups Ihrer Datenbank zu erstellen. Sie können das Deployment-Tool mit der Option `--backup-db` verwenden, um ein Backup zu erstellen und auf den Server hochzuladen:

```bash
php deploy.php --host=ftp.example.com --username=ihr_benutzername --password=ihr_passwort --backup-db
```

Die Backups werden im Verzeichnis `/backups` auf dem Server gespeichert.

## Weitere Informationen

Für weitere Informationen oder bei Problemen wenden Sie sich bitte an den Support oder konsultieren Sie die Dokumentation der Anwendung. 