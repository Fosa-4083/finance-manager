# Migration von SQLite zu MariaDB mit phpMyAdmin

Diese Anleitung beschreibt, wie Sie Ihre SQLite-Datenbank zu MariaDB migrieren können und die generierte SQL-Datei in phpMyAdmin importieren.

## Voraussetzungen

- PHP (Kommandozeile)
- Zugriff auf phpMyAdmin
- Bestehende SQLite-Datenbank

## Schritt 1: SQL-Datei generieren

1. Führen Sie das Konvertierungsskript aus:

   ```bash
   cd /pfad/zum/expense-manager
   php sqlite_to_mariadb.php
   ```

   Das Skript liest die SQLite-Datenbank und erstellt eine Datei `mariadb_import.sql` im selben Verzeichnis.

2. Die generierte SQL-Datei enthält alle Tabellen, Indizes, Fremdschlüssel und Daten aus Ihrer SQLite-Datenbank, konvertiert in ein MariaDB-kompatibles Format.

## Schritt 2: Import in phpMyAdmin

1. Öffnen Sie phpMyAdmin in Ihrem Browser.

2. Erstellen Sie eine neue Datenbank (falls noch nicht vorhanden):
   - Klicken Sie auf "Neu" im linken Navigationsbereich.
   - Geben Sie "expense_manager" als Datenbanknamen ein.
   - Wählen Sie "utf8mb4_unicode_ci" als Kollation.
   - Klicken Sie auf "Erstellen".

3. Wählen Sie die Datenbank "expense_manager" aus.

4. Klicken Sie auf den Tab "Import".

5. Klicken Sie auf "Durchsuchen" und wählen Sie die generierte `mariadb_import.sql` Datei aus.

6. Stellen Sie sicher, dass folgende Optionen ausgewählt sind:
   - Zeichensatz der Datei: utf8
   - Format: SQL

7. Klicken Sie auf "OK" oder "Importieren", um den Import zu starten.

8. Warten Sie, bis der Import abgeschlossen ist. Dies kann je nach Größe der Datenbank einige Zeit dauern.

## Schritt 3: Anwendung konfigurieren

Nach erfolgreicher Migration müssen Sie Ihre Anwendung so konfigurieren, dass sie die MariaDB-Datenbank verwendet:

1. Erstellen Sie eine neue Konfigurationsdatei `config/database_mariadb.php` mit folgendem Inhalt:

   ```php
   <?php
   return [
       'driver' => 'mysql',
       'host' => 'localhost', // Ändern Sie dies entsprechend Ihrem MariaDB-Server
       'port' => 3306,
       'database' => 'expense_manager',
       'username' => 'ihr_benutzername', // Ändern Sie dies
       'password' => 'ihr_passwort',     // Ändern Sie dies
       'charset' => 'utf8mb4',
       'collation' => 'utf8mb4_unicode_ci',
       'prefix' => '',
   ];
   ```

2. Passen Sie Ihre Anwendung an, um diese Konfiguration zu verwenden.

## Fehlerbehebung

- **Fehler beim Import**: Wenn der Import in phpMyAdmin fehlschlägt, prüfen Sie die Fehlermeldung. Häufige Probleme sind zu große Dateien oder Zeitüberschreitungen. In diesem Fall können Sie die SQL-Datei in kleinere Teile aufteilen.

- **Zeichensatzprobleme**: Wenn Umlaute oder Sonderzeichen nicht korrekt angezeigt werden, stellen Sie sicher, dass die Zeichensatzeinstellungen in phpMyAdmin und in der Datenbankkonfiguration übereinstimmen.

- **Fremdschlüsselprobleme**: Wenn Fehler mit Fremdschlüsseln auftreten, kann es hilfreich sein, die Option "Fremdschlüsselprüfungen deaktivieren" beim Import zu aktivieren.

## Manuelle Ausführung der SQL-Befehle

Wenn der Import über die phpMyAdmin-Oberfläche nicht funktioniert, können Sie die SQL-Befehle auch manuell ausführen:

1. Öffnen Sie die generierte SQL-Datei in einem Texteditor.
2. Kopieren Sie die SQL-Befehle.
3. Öffnen Sie in phpMyAdmin den Tab "SQL".
4. Fügen Sie die SQL-Befehle ein und klicken Sie auf "OK".

Beachten Sie, dass phpMyAdmin möglicherweise eine Begrenzung für die Größe der SQL-Abfragen hat. In diesem Fall müssen Sie die Befehle in kleinere Teile aufteilen. 