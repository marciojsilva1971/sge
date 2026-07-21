-- ========================================================
-- SCRIPT DE ATUALIZACAO DE ESTRUTURA DO BANCO DE DADOS SGE
-- Modulo: Prestacao de Contas e Exportacao SPCE (TSE)
-- Compatibilidade: MySQL 5.7+, MySQL 8.0+, MariaDB 10+
-- Data: 2026-07-21
-- ========================================================

-- 1. Garante a existencia da tabela de receitas caso ainda nao tenha sido criada
CREATE TABLE IF NOT EXISTS `receitas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `description` VARCHAR(255) NOT NULL,
  `value` DECIMAL(15,2) NOT NULL,
  `date_received` DATE NOT NULL,
  `bank_account_id` INT NOT NULL,
  `spce_category_id` INT NOT NULL,
  `donor_name` VARCHAR(255) NOT NULL,
  `donor_cpf` VARCHAR(20) NOT NULL,
  `tse_status` ENUM('PENDENTE', 'ENVIADO_72H') NOT NULL DEFAULT 'PENDENTE',
  `tse_reported_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts`(`id`),
  FOREIGN KEY (`spce_category_id`) REFERENCES `spce_categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Adiciona coluna tse_status se a tabela ja existia sem ela
SET @dbname = DATABASE();
SET @tablename = 'receitas';
SET @columnname = 'tse_status';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE `receitas` ADD COLUMN `tse_status` ENUM('PENDENTE', 'ENVIADO_72H') NOT NULL DEFAULT 'PENDENTE'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Adiciona coluna tse_reported_at se a tabela ja existia sem ela
SET @columnname = 'tse_reported_at';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE `receitas` ADD COLUMN `tse_reported_at` DATETIME NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
