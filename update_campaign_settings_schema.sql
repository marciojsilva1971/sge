-- Script SQL: Tabela de Configurações da Campanha e Cargo Eleitoral (SGE)
-- Tabela para armazenar cargo em disputa, UF e limite de gastos TSE customizável pelo usuário.

CREATE TABLE IF NOT EXISTS `campaign_settings` (
    `id` INT PRIMARY KEY DEFAULT 1,
    `candidate_name` VARCHAR(150) NOT NULL DEFAULT 'Candidato SGE',
    `electoral_role` VARCHAR(100) NOT NULL DEFAULT 'Deputado Federal',
    `uf` VARCHAR(2) NOT NULL DEFAULT 'DF',
    `party_name` VARCHAR(100) NULL DEFAULT 'Partido Exemplo',
    `party_number` VARCHAR(10) NULL DEFAULT '00',
    `spending_limit` DECIMAL(15,2) NOT NULL DEFAULT 3168878.60,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insere o registro padrão inicial (id = 1) se a tabela estiver vazia
INSERT IGNORE INTO `campaign_settings` (`id`, `candidate_name`, `electoral_role`, `uf`, `party_name`, `party_number`, `spending_limit`) 
VALUES (1, 'Candidato SGE', 'Deputado Federal', 'DF', 'Partido Exemplo', '00', 3168878.60);
