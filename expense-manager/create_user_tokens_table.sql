-- SQL-Skript zum Erstellen der Tabelle für Benutzer-Tokens
-- Diese Tabelle wird für die "Angemeldet bleiben"-Funktionalität verwendet

-- Tabelle erstellen, falls sie noch nicht existiert
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` INT AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_token_type` (`user_id`, `type`),
  INDEX `idx_token` (`token`),
  INDEX `idx_expires` (`expires_at`),
  CONSTRAINT `fk_user_tokens_user_id` FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bestätigung
SELECT 'Tabelle user_tokens wurde erfolgreich erstellt!' AS 'Info'; 