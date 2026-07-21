-- ========================================================
-- SCRIPT DE CARGA E HOMOLOGACAO DE ADMINISTRADORES DO SGE
-- Data de Geracao: 2026-07-21 12:52:42
-- Insere/Atualiza nas tabelas 'usuarios' e 'colaboradores'
-- ========================================================

-- Administrador: Maculevicius
INSERT INTO `usuarios` (`name`, `email`, `celular`, `password_hash`, `role_id`, `status`, `created_at`, `updated_at`)
VALUES ('Maculevicius', 'maculevicius@sge.com', '11983316837', '$2y$10$0IJa6pCwfQNHXq4CPPOiQe2daWQ6q9oz3fMFUGKWXHBimgABBaMQy', 1, 'ATIVO', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `celular` = VALUES(`celular`), `password_hash` = VALUES(`password_hash`), `role_id` = VALUES(`role_id`), `status` = VALUES(`status`), `updated_at` = NOW();
INSERT INTO `colaboradores` (`usuario_id`, `nome_completo`, `cpf`, `email`, `celular_whatsapp`, `status`, `optin_whatsapp`, `created_at`, `updated_at`)
VALUES ((SELECT id FROM `usuarios` WHERE email = 'maculevicius@sge.com' LIMIT 1), 'Maculevicius', '000.000.001-01', 'maculevicius@sge.com', '11983316837', 'ATIVO', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `nome_completo` = VALUES(`nome_completo`), `cpf` = VALUES(`cpf`), `celular_whatsapp` = VALUES(`celular_whatsapp`), `status` = VALUES(`status`), `updated_at` = NOW();

-- Administrador: Rafael
INSERT INTO `usuarios` (`name`, `email`, `celular`, `password_hash`, `role_id`, `status`, `created_at`, `updated_at`)
VALUES ('Rafael', 'rafael@sge.com', '14991113113', '$2y$10$2AfgUN12YQqIrqFzADhSE..VhhalZapaCtbbS8CtOg9pwHWMiHtVq', 1, 'ATIVO', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `celular` = VALUES(`celular`), `password_hash` = VALUES(`password_hash`), `role_id` = VALUES(`role_id`), `status` = VALUES(`status`), `updated_at` = NOW();
INSERT INTO `colaboradores` (`usuario_id`, `nome_completo`, `cpf`, `email`, `celular_whatsapp`, `status`, `optin_whatsapp`, `created_at`, `updated_at`)
VALUES ((SELECT id FROM `usuarios` WHERE email = 'rafael@sge.com' LIMIT 1), 'Rafael', '000.000.002-02', 'rafael@sge.com', '14991113113', 'ATIVO', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `nome_completo` = VALUES(`nome_completo`), `cpf` = VALUES(`cpf`), `celular_whatsapp` = VALUES(`celular_whatsapp`), `status` = VALUES(`status`), `updated_at` = NOW();
