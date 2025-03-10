BEGIN TRANSACTION;

-- Hole category_id für jede Kategorie
WITH RECURSIVE years(year) AS (
  SELECT 2010
  UNION ALL
  SELECT year + 1 
  FROM years 
  WHERE year < 2025
)
INSERT INTO expense_goals (category_id, year, goal)
SELECT 
    c.id,
    y.year,
    CASE c.name
        -- Fixe Kosten (monatlich * 12)
        WHEN 'A: Miete & Nebenkosten' THEN 15000     -- ~1250/Monat
        WHEN 'A: Betriebskosten' THEN 1800           -- ~150/Monat
        WHEN 'A: Bankgebühren' THEN 180              -- ~15/Monat
        WHEN 'A: Versicherungen' THEN 1200           -- ~100/Monat
        WHEN 'A: Kredite & Zinsen' THEN 3000         -- ~250/Monat

        -- Variable Kosten (monatlich * 12)
        WHEN 'A: Haushaltswaren' THEN 600            -- ~50/Monat
        WHEN 'A: Elektronik' THEN 2400               -- ~200/Monat
        WHEN 'A: Auto & Transport' THEN 12000        -- ~1000/Monat
        WHEN 'A: Kommunikation' THEN 360             -- ~30/Monat

        -- Persönliche Ausgaben (monatlich * 12)
        WHEN 'A: Körperpflege' THEN 180              -- ~15/Monat
        WHEN 'A: Kleidung' THEN 1800                 -- ~150/Monat
        WHEN 'A: Geschenke' THEN 600                 -- ~50/Monat
        WHEN 'A: Gesundheit' THEN 1200               -- ~100/Monat

        -- Freizeit & Unterhaltung (monatlich * 12)
        WHEN 'A: Medien & Unterhaltung' THEN 240     -- ~20/Monat
        WHEN 'A: Bücher & Hörbücher' THEN 180        -- ~15/Monat
        WHEN 'A: Freizeit & Sport' THEN 2400         -- ~200/Monat
        WHEN 'A: Urlaub & Reisen' THEN 8400          -- ~700/Monat

        -- Spezielle Kategorien
        WHEN 'A: Kinder' THEN 4200                   -- ~350/Monat
        WHEN 'A: Sparen & Vorsorge' THEN 7200        -- ~600/Monat
        WHEN 'A: Sonstiges' THEN 1200                -- ~100/Monat

        -- Einnahmen-Kategorien (Jahresziele)
        WHEN 'E: Gehalt Hauptjob' THEN 100000        -- Jahresgehalt
        WHEN 'E: Gehalt Nebenjob' THEN 10000         -- Nebeneinkünfte
        WHEN 'E: Kapitalerträge' THEN 1000           -- Zinsen/Dividenden
        WHEN 'E: Mieteinnahmen' THEN 24000           -- Mieteinnahmen
        WHEN 'E: Sonstiges' THEN 1200                -- Sonstige Einnahmen
    END as yearly_goal
FROM categories c
CROSS JOIN years y
WHERE c.name IN (
    'A: Miete & Nebenkosten', 'A: Betriebskosten', 'A: Bankgebühren', 'A: Versicherungen',
    'A: Kredite & Zinsen', 'A: Haushaltswaren', 'A: Elektronik', 'A: Auto & Transport',
    'A: Kommunikation', 'A: Körperpflege', 'A: Kleidung', 'A: Geschenke', 'A: Gesundheit',
    'A: Medien & Unterhaltung', 'A: Bücher & Hörbücher', 'A: Freizeit & Sport',
    'A: Urlaub & Reisen', 'A: Kinder', 'A: Sparen & Vorsorge', 'A: Sonstiges',
    'E: Gehalt Hauptjob', 'E: Gehalt Nebenjob', 'E: Kapitalerträge', 'E: Mieteinnahmen',
    'E: Sonstiges'
);

COMMIT; 