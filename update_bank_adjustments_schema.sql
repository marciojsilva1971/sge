-- ========================================================
-- SCRIPT DE ESTRUTURA: CARGA E CONCILIACAO DE SALDOS BANCARIOS
-- Modulo: Financeiro / Conciliacao e Extratos em PDF
-- Compatibilidade: MySQL 5.7+, MySQL 8.0+, MariaDB 10+
-- Data: 2026-07-21
-- ========================================================

CREATE TABLE IF NOT EXISTS `bank_balance_adjustments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `bank_account_id` INT NOT NULL,
  `adjustment_type` ENUM('CARGA_INICIAL', 'AJUSTE_CREDITO', 'AJUSTE_DEBITO', 'CONCILIACAO') NOT NULL,
  `old_balance` DECIMAL(15,2) NOT NULL,
  `adjustment_amount` DECIMAL(15,2) NOT NULL,
  `new_balance` DECIMAL(15,2) NOT NULL,
  `reason` TEXT NOT NULL,
  `statement_file_path` VARCHAR(255) NOT NULL,
  `statement_file_name` VARCHAR(255) NOT NULL,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`created_by`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
