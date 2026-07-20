# Especificação e Planejamento: Módulo de RH e Gestão de Colaboradores de Campanha

## Visão Geral do Módulo
O Módulo de Recursos Humanos (RH) destina-se ao cadastramento, coleta de anuência (LGPD/WhatsApp), geração e gestão de contratos de prestação de serviços de campanha (Art. 100 da Lei nº 9.504/1997), além da gestão de dados bancários para integração com a folha de pagamento do Módulo Financeiro.

---

## Formas de Assinatura do Contrato PDF

Conforme diretriz do projeto, **não haverá assinatura eletrônica processada nativamente pelo nosso sistema**. O sistema suportará exclusivamente as duas modalidades abaixo:

> [!NOTE]
> **Modalidade 1: Assinatura por Plataforma de Terceiros (API)**
> * **Como funciona:** O contrato PDF gerado é enviado via API para uma plataforma homologada (ex: ZapSign, Clicksign, D4Sign, Gov.br).
> * **Fluxo:** O colaborador recebe por E-mail e WhatsApp (Z-API) o link direto de assinatura fornecido pela plataforma externa. Após assinado externamente, a plataforma notifica o SGE via Webhook ou o Admin atualiza o status com a cópia final assinada.

> [!IMPORTANT]
> **Modalidade 2: Assinatura Manual (Upload de Cópia Física Digitalizada)**
> * **Como funciona:** O contrato em PDF é gerado e disponibilizado para download. O colaborador imprime, assina de próprio punho e faz o upload da foto/PDF do documento assinado no portal (ou entrega física para o RH fazer o upload no painel admin).

---

## Estrutura de Banco de Dados Atualizada

```sql
-- 1. Tabela de Colaboradores de Campanha
CREATE TABLE IF NOT EXISTS `colaboradores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL, -- Vinculado após homologação e atribuição de perfil pelo Admin
    `nome_completo` VARCHAR(150) NOT NULL,
    `cpf` VARCHAR(14) NOT NULL UNIQUE,
    `rg` VARCHAR(20) NOT NULL,
    `rg_orgao_emissor` VARCHAR(20) NOT NULL,
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
    `status` ENUM('PENDENTE_ASSINATURA', 'AGUARDANDO_HOMOLOGACAO', 'ATIVO', 'REJEITADO') DEFAULT 'PENDENTE_ASSINATURA',
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
```
