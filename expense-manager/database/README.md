# Datenbank-Einrichtung

Dieses Verzeichnis enthält die SQLite-Datenbank für die Finanzverwaltungsanwendung.

## Hinweis

Die Datenbankdatei `database.sqlite` wird nicht im Git-Repository gespeichert, um Probleme mit Datenänderungen und Versionskonflikten zu vermeiden.

## Neue Installation einrichten

Wenn Sie das Repository neu geklont haben, müssen Sie die Datenbank manuell einrichten:

1. Erstellen Sie eine leere Datei mit dem Namen `database.sqlite` in diesem Verzeichnis.
2. Führen Sie das Setup-Skript aus, um die Datenbankstruktur zu erstellen:

```
php setup_database.php
```

3. Optional: Erstellen Sie ein Verzeichnis `backups` in diesem Ordner, um automatische Datenbank-Backups zu speichern.

## Bestehendes Backup wiederherstellen

Wenn Sie ein Backup der Datenbank haben, können Sie es einfach in dieses Verzeichnis kopieren und in `database.sqlite` umbenennen.

## Berechtigungen

Stellen Sie sicher, dass der Webserver Schreibrechte für dieses Verzeichnis hat, damit die Anwendung die Datenbank aktualisieren und Backups erstellen kann:

```
chmod -R 775 database/
chown -R www-data:www-data database/
```

(Passen Sie den Benutzer und die Gruppe entsprechend Ihrer Serverumgebung an.)

## Automatische Backups

Die Anwendung erstellt automatisch tägliche Backups der Datenbank vor der ersten schreibenden Operation. Diese werden im Unterverzeichnis `backups` gespeichert.

Sie können die Backups auch über die Benutzeroberfläche unter "Datenbank-Backups" verwalten. 