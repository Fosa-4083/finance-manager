# Finanzverwaltung - Installations- und Einrichtungsanleitung

## Systemvoraussetzungen

- PHP 8.0 oder höher
- SQLite3 Unterstützung für PHP
- mod_rewrite für Apache (oder entsprechende URL-Rewriting-Funktionalität für andere Webserver)
- Schreibrechte für das Verzeichnis `database/`

## Installation

1. Entpacken Sie das ZIP-Archiv in das gewünschte Verzeichnis auf Ihrem Webserver.

2. Stellen Sie sicher, dass das Verzeichnis `database/` für PHP schreibbar ist:
   ```bash
   chmod 755 database/
   chmod 644 database/database.sqlite
   ```

3. Konfigurieren Sie Ihren Webserver:

   ### Apache
   Die .htaccess-Datei ist bereits im Paket enthalten. Stellen Sie sicher, dass mod_rewrite aktiviert ist:
   ```apache
   <Directory /path/to/your/app>
       Options Indexes FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

   ### Nginx
   Fügen Sie folgende Konfiguration hinzu:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

4. Setzen Sie die korrekten Berechtigungen für alle Dateien:
   ```bash
   find . -type f -exec chmod 644 {} \;
   find . -type d -exec chmod 755 {} \;
   ```

## Verzeichnisstruktur

```
expense-manager/
├── database/
│   └── database.sqlite
├── public/
│   ├── index.php
│   └── .htaccess
├── src/
│   ├── Controllers/
│   ├── Models/
│   └── Views/
└── vendor/
```

## Erste Schritte

1. Öffnen Sie die Anwendung in Ihrem Browser
2. Die Datenbank ist bereits mit einigen Beispieldaten vorkonfiguriert
3. Sie können sofort mit der Erfassung von Einnahmen und Ausgaben beginnen

## Fehlerbehebung

### Häufige Probleme

1. **Weiße Seite oder 500 Error**
   - Überprüfen Sie die PHP-Fehlerprotokolle
   - Stellen Sie sicher, dass alle erforderlichen PHP-Erweiterungen aktiviert sind
   - Überprüfen Sie die Dateiberechtigungen

2. **Datenbank-Fehler**
   - Stellen Sie sicher, dass die SQLite-Datenbank existiert und schreibbar ist
   - Überprüfen Sie die Dateiberechtigungen der database.sqlite Datei

3. **URL-Routing funktioniert nicht**
   - Überprüfen Sie, ob mod_rewrite (Apache) aktiviert ist
   - Überprüfen Sie die .htaccess-Konfiguration
   - Bei Nginx: Überprüfen Sie die Server-Konfiguration

## Support

Bei Fragen oder Problemen erstellen Sie bitte ein Issue im GitHub Repository oder kontaktieren Sie den Support. 