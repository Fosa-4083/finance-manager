BEGIN TRANSACTION;

-- Ausgaben (Rot- und Orangetöne für fixe Kosten)
UPDATE categories SET color = '#FF6B6B' WHERE name = 'A: Miete & Nebenkosten';      -- Warmes Rot
UPDATE categories SET color = '#FF9F89' WHERE name = 'A: Betriebskosten';           -- Helles Rot
UPDATE categories SET color = '#FFA07A' WHERE name = 'A: Versicherungen';           -- Lachsrot
UPDATE categories SET color = '#FFB6A3' WHERE name = 'A: Kredite & Zinsen';         -- Helles Lachsrot
UPDATE categories SET color = '#FFD1B8' WHERE name = 'A: Bankgebühren';             -- Sehr helles Orange

-- Ausgaben (Orange- und Gelbtöne für Haushalt)
UPDATE categories SET color = '#FFA500' WHERE name = 'A: Haushaltswaren';           -- Orange
UPDATE categories SET color = '#FFB347' WHERE name = 'A: Elektronik';               -- Helles Orange
UPDATE categories SET color = '#FFC87C' WHERE name = 'A: Kommunikation';            -- Pastell Orange

-- Ausgaben (Grüntöne für Mobilität und Transport)
UPDATE categories SET color = '#4CAF50' WHERE name = 'A: Auto & Transport';         -- Material Grün

-- Ausgaben (Blautöne für Freizeit und Unterhaltung)
UPDATE categories SET color = '#5C6BC0' WHERE name = 'A: Medien & Unterhaltung';    -- Indigo
UPDATE categories SET color = '#7986CB' WHERE name = 'A: Bücher & Hörbücher';       -- Helles Indigo
UPDATE categories SET color = '#64B5F6' WHERE name = 'A: Freizeit & Sport';         -- Helles Blau
UPDATE categories SET color = '#4FC3F7' WHERE name = 'A: Urlaub & Reisen';          -- Himmelblau

-- Ausgaben (Violett- und Pinktöne für Persönliches)
UPDATE categories SET color = '#9575CD' WHERE name = 'A: Körperpflege';             -- Violett
UPDATE categories SET color = '#BA68C8' WHERE name = 'A: Kleidung';                 -- Helles Violett
UPDATE categories SET color = '#F06292' WHERE name = 'A: Geschenke';                -- Pink
UPDATE categories SET color = '#E57373' WHERE name = 'A: Gesundheit';               -- Helles Rot

-- Ausgaben (Spezielle Kategorien)
UPDATE categories SET color = '#90A4AE' WHERE name = 'A: Kinder';                   -- Blaugrau
UPDATE categories SET color = '#78909C' WHERE name = 'A: Sparen & Vorsorge';        -- Dunkleres Blaugrau
UPDATE categories SET color = '#B0BEC5' WHERE name = 'A: Sonstiges';                -- Helles Blaugrau

-- Einnahmen (Grüntöne)
UPDATE categories SET color = '#2E7D32' WHERE name = 'E: Gehalt Hauptjob';          -- Dunkelgrün
UPDATE categories SET color = '#43A047' WHERE name = 'E: Gehalt Nebenjob';          -- Grün
UPDATE categories SET color = '#66BB6A' WHERE name = 'E: Kapitalerträge';           -- Helles Grün
UPDATE categories SET color = '#81C784' WHERE name = 'E: Mieteinnahmen';            -- Sehr helles Grün
UPDATE categories SET color = '#A5D6A7' WHERE name = 'E: Sonstiges';                -- Pastellgrün

COMMIT; 