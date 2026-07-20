-- Schema para Módulo de RH e Gestão de Colaboradores (Fase 4)
-- Sistema de Gestão Eleitoral (SGE)

-- 1. Tabela de Colaboradores de Campanha
CREATE TABLE IF NOT EXISTS `colaboradores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL, -- Vinculado após homologação e atribuição de perfil pelo Admin
    `nome_completo` VARCHAR(150) NOT NULL,
    `cpf` VARCHAR(14) NOT NULL UNIQUE,
    `rg` VARCHAR(20) NOT NULL,
    `rg_orgao_emissor` VARCHAR(20) NOT NULL,
    `documento_foto_path` VARCHAR(255) NULL, -- Foto/PDF do documento de identificação (RG, CNH, CIN)
    `data_nascimento` DATE NOT NULL,
    `idade_calculada` INT NOT NULL,
    `celular_whatsapp` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `cep` VARCHAR(10) NULL,
    `logradouro` VARCHAR(255) NULL,
    `numero` VARCHAR(20) NULL,
    `complemento` VARCHAR(50) NULL,
    `bairro` VARCHAR(100) NULL,
    `cidade` VARCHAR(100) NULL,
    `uf` VARCHAR(2) NULL,
    `banco_codigo` VARCHAR(10) NULL,
    `banco_nome` VARCHAR(100) NULL,
    `agencia` VARCHAR(20) NULL,
    `conta` VARCHAR(30) NULL,
    `tipo_conta` ENUM('CORRENTE', 'POUPANCA') DEFAULT 'CORRENTE',
    `chave_pix` VARCHAR(100) NULL,
    `optin_whatsapp` TINYINT(1) DEFAULT 0,
    `optin_timestamp` DATETIME NULL,
    `optin_ip` VARCHAR(45) NULL,
    `token_cadastro` VARCHAR(64) NULL UNIQUE,
    `status` ENUM('AGUARDANDO_AVAL_CADASTRO', 'AGUARDANDO_ASSINATURA_CONTRATO', 'AGUARDANDO_CONFERENCIA_CONTRATO', 'ATIVO', 'REJEITADO') DEFAULT 'AGUARDANDO_AVAL_CADASTRO',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de Contratos de Colaboradores
CREATE TABLE IF NOT EXISTS `contratos_colaboradores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `colaborador_id` INT NOT NULL,
    `titulo_contrato` VARCHAR(150) NOT NULL,
    `funcao_campanha` VARCHAR(100) NOT NULL,
    `valor_contratado` DECIMAL(15,2) NOT NULL,
    `forma_pagamento` VARCHAR(100) NOT NULL,
    `data_inicio` DATE NOT NULL,
    `data_fim` DATE NOT NULL,
    `tipo_assinatura` ENUM('TERCEIROS_API', 'MANUAL_UPLOAD') DEFAULT 'TERCEIROS_API',
    `external_signature_url` VARCHAR(255) NULL, -- Link para assinatura em plataforma de terceiros (ex: ZapSign)
    `pdf_original_path` VARCHAR(255) NULL,
    `pdf_assinado_path` VARCHAR(255) NULL,
    `hash_documento` VARCHAR(64) NULL, -- SHA-256 do arquivo PDF
    `status_contrato` ENUM('EMITIDO', 'AGUARDANDO_ASSINATURA', 'ASSINADO', 'CANCELADO') DEFAULT 'EMITIDO',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
