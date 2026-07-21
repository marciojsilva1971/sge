-- ========================================================
-- SCRIPT DE CARGA E HOMOLOGACAO DE ADMINISTRADORES DO SGE
-- Data de Geracao: 2026-07-21 12:59:35
-- Insere/Atualiza nas tabelas 'usuarios' e 'colaboradores'
-- ========================================================

-- Administrador: Maculevicius
INSERT INTO `usuarios` (`name`, `email`, `celular`, `password_hash`, `role_id`, `status`, `created_at`, `updated_at`)
VALUES ('Maculevicius', 'maculevicius@sge.com', '11983316837', '$2y$10$jInOt6CkHvpwJClufRII8OpZgi9H0i4fHErzQW5Kq/re21y9V6hHG', 1, 'ATIVO', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `celular` = VALUES(`celular`), `password_hash` = VALUES(`password_hash`), `role_id` = VALUES(`role_id`), `status` = VALUES(`status`), `updated_at` = NOW();
INSERT INTO `colaboradores` (`usuario_id`, `nome_completo`, `cpf`, `rg`, `rg_orgao_emissor`, `data_nascimento`, `cidade`, `uf`, `email`, `celular_whatsapp`, `status`, `optin_whatsapp`, `created_at`, `updated_at`)
VALUES ((SELECT id FROM `usuarios` WHERE email = 'maculevicius@sge.com' LIMIT 1), 'Maculevicius', '384.912.748-15', '48.291.034-8', 'SSP/SP', '1985-05-20', 'São Paulo', 'SP', 'maculevicius@sge.com', '11983316837', 'ATIVO', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `nome_completo` = VALUES(`nome_completo`), `cpf` = VALUES(`cpf`), `rg` = VALUES(`rg`), `rg_orgao_emissor` = VALUES(`rg_orgao_emissor`), `data_nascimento` = VALUES(`data_nascimento`), `cidade` = VALUES(`cidade`), `uf` = VALUES(`uf`), `celular_whatsapp` = VALUES(`celular_whatsapp`), `status` = VALUES(`status`), `updated_at` = NOW();

-- Administrador: Rafael
INSERT INTO `usuarios` (`name`, `email`, `celular`, `password_hash`, `role_id`, `status`, `created_at`, `updated_at`)
VALUES ('Rafael', 'rafael@sge.com', '14991113113', '$2y$10$tUguUvEIk/VOPD/e5Y61a.G/IxI5eSgpopX/0JPXacQeWWU6QwXo6', 1, 'ATIVO', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `celular` = VALUES(`celular`), `password_hash` = VALUES(`password_hash`), `role_id` = VALUES(`role_id`), `status` = VALUES(`status`), `updated_at` = NOW();
INSERT INTO `colaboradores` (`usuario_id`, `nome_completo`, `cpf`, `rg`, `rg_orgao_emissor`, `data_nascimento`, `cidade`, `uf`, `email`, `celular_whatsapp`, `status`, `optin_whatsapp`, `created_at`, `updated_at`)
VALUES ((SELECT id FROM `usuarios` WHERE email = 'rafael@sge.com' LIMIT 1), 'Rafael', '295.817.361-82', '35.719.824-1', 'SSP/SP', '1990-08-15', 'São Paulo', 'SP', 'rafael@sge.com', '14991113113', 'ATIVO', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `nome_completo` = VALUES(`nome_completo`), `cpf` = VALUES(`cpf`), `rg` = VALUES(`rg`), `rg_orgao_emissor` = VALUES(`rg_orgao_emissor`), `data_nascimento` = VALUES(`data_nascimento`), `cidade` = VALUES(`cidade`), `uf` = VALUES(`uf`), `celular_whatsapp` = VALUES(`celular_whatsapp`), `status` = VALUES(`status`), `updated_at` = NOW();
