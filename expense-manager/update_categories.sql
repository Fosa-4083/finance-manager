-- SQL-Skript zum Aktualisieren der Kategorie-Beschreibungen
-- Erstellt am 11.03.2025

-- Ausgaben-Kategorien
UPDATE `categories` SET `description` = 'Alle Ausgaben für Haushaltswaren, Möbel, Küchenutensilien und Dekoration' WHERE `id` = 1;
UPDATE `categories` SET `description` = 'Ausgaben für Unterhaltung, Kino, Konzerte und Events' WHERE `id` = 2;
UPDATE `categories` SET `description` = 'Monatliche Miete, Strom, Wasser, Heizung und andere Wohnnebenkosten' WHERE `id` = 3;
UPDATE `categories` SET `description` = 'Kontoführungsgebühren, Kreditkartengebühren und andere Bankkosten' WHERE `id` = 4;
UPDATE `categories` SET `description` = 'Krankenversicherung, Haftpflicht, Hausrat und andere Versicherungen' WHERE `id` = 5;
UPDATE `categories` SET `description` = 'Sonstige Ausgaben, die in keine andere Kategorie passen' WHERE `id` = 6;
UPDATE `categories` SET `description` = 'Kraftstoff, Reparaturen, Versicherung, Parkgebühren und öffentliche Verkehrsmittel' WHERE `id` = 7;
UPDATE `categories` SET `description` = 'Internet, Telefon, Mobilfunk und andere Kommunikationskosten' WHERE `id` = 8;
UPDATE `categories` SET `description` = 'Kosmetik, Friseur, Hygieneartikel und Pflegeprodukte' WHERE `id` = 9;
UPDATE `categories` SET `description` = 'Kleidung, Schuhe und Accessoires' WHERE `id` = 10;
UPDATE `categories` SET `description` = 'Streaming-Dienste, Musik, Filme und andere Unterhaltungsmedien' WHERE `id` = 11;
UPDATE `categories` SET `description` = 'Bücher, E-Books, Hörbücher und Zeitschriften' WHERE `id` = 12;
UPDATE `categories` SET `description` = 'Urlaubsreisen, Wochenendausflüge und damit verbundene Kosten' WHERE `id` = 13;
UPDATE `categories` SET `description` = 'Computer, Smartphones, Tablets und andere elektronische Geräte' WHERE `id` = 14;
UPDATE `categories` SET `description` = 'Sportausrüstung, Mitgliedschaften, Hobbys und Freizeitaktivitäten' WHERE `id` = 15;
UPDATE `categories` SET `description` = 'Geschenke für Familie, Freunde und Kollegen' WHERE `id` = 16;
UPDATE `categories` SET `description` = 'Arztbesuche, Medikamente und andere Gesundheitsausgaben' WHERE `id` = 17;
UPDATE `categories` SET `description` = 'Laufende Kosten für Geschäft oder Selbstständigkeit' WHERE `id` = 18;
UPDATE `categories` SET `description` = 'Kreditraten, Zinszahlungen und Tilgungen' WHERE `id` = 19;
UPDATE `categories` SET `description` = 'Ausgaben für Kinder wie Schule, Kleidung, Spielzeug und Betreuung' WHERE `id` = 20;

-- Einnahmen-Kategorien
UPDATE `categories` SET `description` = 'Regelmäßiges Gehalt aus dem Hauptberuf' WHERE `id` = 21;
UPDATE `categories` SET `description` = 'Einkommen aus Nebenjobs, Freelance-Tätigkeiten oder Teilzeitarbeit' WHERE `id` = 22;
UPDATE `categories` SET `description` = 'Zinsen, Dividenden, Mieteinnahmen und andere Kapitalerträge' WHERE `id` = 23;
UPDATE `categories` SET `description` = 'Sonstige Einnahmen wie Steuerrückerstattungen, Geschenke oder Verkäufe' WHERE `id` = 24;

-- Neue Kategorien hinzufügen (optional)
INSERT INTO `categories` (`name`, `color`, `type`, `description`) 
VALUES (' Lebensmittel', '#8BC34A', 'expense', 'Ausgaben für Lebensmittel, Supermarkteinkäufe und Grundnahrungsmittel');

INSERT INTO `categories` (`name`, `color`, `type`, `description`) 
VALUES (' Restaurant & Café', '#CDDC39', 'expense', 'Essen gehen, Café-Besuche, Lieferservice und Takeaway');

INSERT INTO `categories` (`name`, `color`, `type`, `description`) 
VALUES (' Bildung', '#03A9F4', 'expense', 'Kurse, Seminare, Studiengebühren und Lernmaterialien');

INSERT INTO `categories` (`name`, `color`, `type`, `description`) 
VALUES (' Spenden', '#9C27B0', 'expense', 'Wohltätige Spenden und Unterstützungen');

INSERT INTO `categories` (`name`, `color`, `type`, `description`) 
VALUES (' Boni & Prämien', '#4CAF50', 'income', 'Einmalige Bonuszahlungen, Prämien und Sonderzahlungen');

-- Bestätigung
SELECT 'Kategorien wurden erfolgreich aktualisiert!' AS 'Info'; 