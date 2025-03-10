BEGIN TRANSACTION;

-- Ausgaben aktualisieren
UPDATE categories SET 
    name = 'A: Miete & Nebenkosten',
    description = 'Miete, Hypothek, Grundsteuer, Hausverwaltung, Müllgebühren, Wasser, Abwasser'
WHERE id = 3;

UPDATE categories SET 
    name = 'A: Haushaltswaren',
    description = 'Putzmittel, Werkzeuge, Kleinmöbel, Geschirr, Handtücher, Bettwäsche, Deko'
WHERE id = 1;

UPDATE categories SET 
    name = 'A: Lebensmittel',
    description = 'Supermarkteinkäufe, Getränke, Snacks, Restaurant, Café, Lieferdienste'
WHERE id = 2;

UPDATE categories SET 
    name = 'A: Bankgebühren',
    description = 'Kontoführungsgebühren, Kreditkartengebühren, Überweisungskosten'
WHERE id = 4;

UPDATE categories SET 
    name = 'A: Versicherungen',
    description = 'Kranken-, Hausrat-, Haftpflicht-, Lebens-, Unfallversicherung'
WHERE id = 5;

UPDATE categories SET 
    name = 'A: Sonstiges',
    description = 'Nicht kategorisierbare Ausgaben, einmalige ungewöhnliche Kosten'
WHERE id = 6;

UPDATE categories SET 
    name = 'A: Auto & Transport',
    description = 'Kraftstoff, KFZ-Versicherung, Reparaturen, Parkgebühren, ÖPNV-Tickets'
WHERE id = 7;

UPDATE categories SET 
    name = 'A: Kommunikation',
    description = 'Internet, Telefon, Mobilfunk, Streaming-Dienste, TV-Gebühren'
WHERE id = 8;

UPDATE categories SET 
    name = 'A: Körperpflege',
    description = 'Friseur, Kosmetik, Hygieneartikel, Drogerieartikel'
WHERE id = 9;

UPDATE categories SET 
    name = 'A: Kleidung',
    description = 'Kleidung, Schuhe, Accessoires, Reinigung'
WHERE id = 10;

UPDATE categories SET 
    name = 'A: Medien & Unterhaltung',
    description = 'Kino, Konzerte, Theater, Zeitungen, Zeitschriften, Gaming'
WHERE id = 11;

UPDATE categories SET 
    name = 'A: Bücher & Hörbücher',
    description = 'Bücher, E-Books, Hörbücher, Fachliteratur'
WHERE id = 12;

UPDATE categories SET 
    name = 'A: Urlaub & Reisen',
    description = 'Hotels, Flüge, Mietwagen, Reiseversicherung, Ausflüge'
WHERE id = 13;

UPDATE categories SET 
    name = 'A: Elektronik',
    description = 'Computer, Smartphone, TV, Haushaltsgeräte, Zubehör'
WHERE id = 14;

UPDATE categories SET 
    name = 'A: Freizeit & Sport',
    description = 'Fitnessstudio, Sportausrüstung, Hobbys, Vereinsbeiträge'
WHERE id = 15;

UPDATE categories SET 
    name = 'A: Geschenke',
    description = 'Geburtstags-, Weihnachts-, Hochzeitsgeschenke, Spenden'
WHERE id = 16;

UPDATE categories SET 
    name = 'A: Gesundheit',
    description = 'Medikamente, Arztbesuche, Heilpraktiker, Brillen, Therapien'
WHERE id = 17;

UPDATE categories SET 
    name = 'A: Betriebskosten',
    description = 'Strom, Gas, Heizung, Warmwasser, Hausmeister'
WHERE id = 18;

UPDATE categories SET 
    name = 'A: Kredite & Zinsen',
    description = 'Kreditzinsen, Dispozinsen, Ratenzahlungen'
WHERE id = 19;

UPDATE categories SET 
    name = 'A: Kinder',
    description = 'Kinderbetreuung, Schule, Spielzeug, Kleidung für Kinder'
WHERE id = 20;

UPDATE categories SET 
    name = 'A: Sparen & Vorsorge',
    description = 'Spareinlagen, Investments, Altersvorsorge, Rücklagen'
WHERE id = 26;

-- Einnahmen aktualisieren
UPDATE categories SET 
    name = 'E: Gehalt Hauptjob',
    description = 'Nettogehalt, Weihnachtsgeld, Urlaubsgeld, Boni'
WHERE id = 21;

UPDATE categories SET 
    name = 'E: Gehalt Nebenjob',
    description = 'Nebentätigkeiten, Honorare, Freelance-Arbeit'
WHERE id = 22;

UPDATE categories SET 
    name = 'E: Kapitalerträge',
    description = 'Zinsen, Dividenden, Kursgewinne, Ausschüttungen'
WHERE id = 23;

UPDATE categories SET 
    name = 'E: Sonstiges',
    description = 'Steuererstattungen, Geschenke, sonstige unregelmäßige Einnahmen'
WHERE id = 24;

UPDATE categories SET 
    name = 'E: Mieteinnahmen',
    description = 'Mieteinnahmen aus Immobilien, Untervermietung, Nebenkosten'
WHERE id = 25;

COMMIT; 