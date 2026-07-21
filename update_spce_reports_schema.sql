-- ========================================================
-- SCRIPT DE ATUALIZACAO DE ESTRUTURA DO BANCO DE DADOS SGE
-- Modulo: Prestacao de Contas e Exportacao SPCE (TSE)
-- Data: 2026-07-21
-- ========================================================

-- Garante a existencia da tabela de receitas caso ainda nao tenha sido criada
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

-- Adiciona colunas de controle do TSE 72h caso a tabela ja existisse sem elas
ALTER TABLE `receitas` 
  ADD COLUMN IF NOT EXISTS `tse_status` ENUM('PENDENTE', 'ENVIADO_72H') NOT NULL DEFAULT 'PENDENTE',
  ADD COLUMN IF NOT EXISTS `tse_reported_at` DATETIME NULL;
