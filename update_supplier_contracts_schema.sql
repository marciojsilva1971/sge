-- ========================================================
-- SCRIPT DE ATUALIZACAO DE ESTRUTURA DO BANCO DE DADOS SGE
-- Modulo: Contratos por Tempo Determinado (Fornecedores/Empresas)
-- Data: 2026-07-21
-- ========================================================

CREATE TABLE IF NOT EXISTS `supplier_contracts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `supplier_id` INT NOT NULL,
  `contract_number` VARCHAR(50) NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `monthly_amount` DECIMAL(15,2) NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('VIGENTE', 'ENCERRADO', 'CANCELADO') NOT NULL DEFAULT 'VIGENTE',
  `file_path` VARCHAR(255) NULL,
  `file_name` VARCHAR(255) NULL,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajusta colunas caso a tabela ja tenha sido criada anteriormente com NOT NULL
ALTER TABLE `supplier_contracts` 
  MODIFY COLUMN `file_path` VARCHAR(255) NULL,
  MODIFY COLUMN `file_name` VARCHAR(255) NULL;

