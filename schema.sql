-- Schema Inicial do Banco de Dados - Sistema de Gestão Eleitoral (SGE)
-- Sprint 1: Módulo de Usuários, Perfis (RBAC) e Auditoria

-- CREATE DATABASE IF NOT EXISTS `sge` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `sge`;

-- 1. Tabela de Perfis de Acesso (RBAC)
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `permissions` TEXT NOT NULL, -- JSON contendo chaves booleanas de controle de acesso (ex: {"users_write":true, "financial_approve":true})
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de Usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `celular` VARCHAR(20) NOT NULL, -- Para envio de WhatsApp
    `password_hash` VARCHAR(255) NOT NULL,
    `role_id` INT NOT NULL,
    `status` ENUM('ATIVO', 'PENDENTE', 'INATIVO') DEFAULT 'PENDENTE',
    `profile_photo_path` VARCHAR(255) NULL, -- Caminho físico da foto de perfil
    `token_ativacao_hash` VARCHAR(64) NULL, -- Hash SHA-256 para ativação de convites
    `token_expira_em` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de Trilha de Auditoria (Imutável)
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL, -- Registra NULL se for ação de convidado/login falho antes de autenticar
    `action` VARCHAR(100) NOT NULL, -- Ex: 'LOGIN_SUCCESS', 'INVITE_USER', 'ACTIVATE_ACCOUNT'
    `table_name` VARCHAR(50) NOT NULL,
    `record_id` INT NULL,
    `old_values` TEXT NULL, -- JSON
    `new_values` TEXT NULL, -- JSON
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela de Contas Bancárias da Campanha
CREATE TABLE IF NOT EXISTS `bank_accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `bank_name` VARCHAR(100) NOT NULL,
    `agency` VARCHAR(20) NOT NULL,
    `account_number` VARCHAR(30) NOT NULL,
    `fund_type` ENUM('FEFC', 'FUNDO_PARTIDARIO', 'OUTROS_RECURSOS') NOT NULL,
    `balance` DECIMAL(15,2) DEFAULT 0.00,
    `status` ENUM('ATIVA', 'ENCERRADA') DEFAULT 'ATIVA',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela de Fornecedores
CREATE TABLE IF NOT EXISTS `suppliers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cnpj_cpf` VARCHAR(20) NOT NULL UNIQUE,
    `corporate_name` VARCHAR(255) NOT NULL,
    `trade_name` VARCHAR(255) NULL,
    `address` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(100) NULL,
    `status` ENUM('ATIVO', 'INATIVO') DEFAULT 'ATIVO',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela de Categorias Oficiais do SPCE/TSE
CREATE TABLE IF NOT EXISTS `spce_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NOT NULL,
    `type` ENUM('RECEITA', 'DESPESA') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabela de Tipos de Despesas (Categoria Informada pelo Colaborador)
CREATE TABLE IF NOT EXISTS `expense_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabela de Despesas Gerais da Campanha
CREATE TABLE IF NOT EXISTS `despesas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `description` VARCHAR(255) NOT NULL,
    `supplier_id` INT NOT NULL,
    `bank_account_id` INT NULL,
    `value` DECIMAL(15,2) NOT NULL,
    `date_incurred` DATE NOT NULL,
    `payment_method` VARCHAR(50) NULL,
    `status` ENUM('PENDENTE', 'APROVADO', 'REJEITADO', 'PAGO') DEFAULT 'PENDENTE',
    `spce_category_id` INT NULL,
    `expense_type_id` INT NULL,
    `user_id` INT NOT NULL,
    `approved_by` INT NULL,
    `approved_at` DATETIME NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`spce_category_id`) REFERENCES `spce_categories` (`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`expense_type_id`) REFERENCES `expense_types` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`approved_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabela de Metadados de Comprovantes Criptografados (Despesas Gerais)
CREATE TABLE IF NOT EXISTS `comprovantes_cripto` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `expense_id` INT NOT NULL,
    `encrypted_file_path` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `iv` VARCHAR(64) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`expense_id`) REFERENCES `despesas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabela de Relatórios de Viagem de Colaboradores
CREATE TABLE IF NOT EXISTS `travel_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `purpose` VARCHAR(255) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `vehicle_plate` VARCHAR(20) NULL,
    `status` ENUM('EM_ANDAMENTO', 'ENVIADO', 'APROVADO', 'REJEITADO') DEFAULT 'EM_ANDAMENTO',
    `approved_by` INT NULL,
    `approved_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`approved_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabela de Recibos de Despesas de Viagem Criptografados
CREATE TABLE IF NOT EXISTS `travel_receipts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `travel_report_id` INT NOT NULL,
    `supplier_cnpj` VARCHAR(20) NOT NULL,
    `receipt_date` DATE NOT NULL,
    `value` DECIMAL(15,2) NOT NULL,
    `spce_category_id` INT NOT NULL,
    `encrypted_file_path` VARCHAR(255) NOT NULL,
    `iv` VARCHAR(64) NOT NULL,
    `status` ENUM('PENDENTE', 'APROVADO', 'REJEITADO') DEFAULT 'PENDENTE',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`travel_report_id`) REFERENCES `travel_reports` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`spce_category_id`) REFERENCES `spce_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Tabela de Comprovação de Atividades de Militância Georreferenciadas
CREATE TABLE IF NOT EXISTS `militancy_activities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `activity_date` DATE NOT NULL,
    `description` TEXT NOT NULL,
    `encrypted_photo_path` VARCHAR(255) NOT NULL,
    `iv` VARCHAR(64) NOT NULL,
    `latitude` DECIMAL(10,8) NOT NULL,
    `longitude` DECIMAL(11,8) NOT NULL,
    `status` ENUM('PENDENTE', 'APROVADO', 'REJEITADO') DEFAULT 'PENDENTE',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Tabela de Fotos Adicionais de Militância
CREATE TABLE IF NOT EXISTS `militancy_photos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `militancy_id` INT NOT NULL,
    `encrypted_photo_path` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `iv` VARCHAR(64) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`militancy_id`) REFERENCES `militancy_activities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Tabela de Contratos de Fornecedores/Empresas por Tempo Determinado
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
  `file_path` VARCHAR(255) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



