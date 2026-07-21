-- Script de Carga/Homologacao de Administradores do SGE
-- Gerado em: 2026-07-21 12:48:25

INSERT INTO `usuarios` (`name`, `email`, `celular`, `password_hash`, `role_id`, `status`, `created_at`, `updated_at`)
VALUES ('Maculevicius', 'maculevicius@sge.com', '11983316837', '$2y$10$yOBIguKkLUGvn1YDqbFPR.zzHdgi.DmZiphDMs1umV9mSb2Xuyx.u', 1, 'ATIVO', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `celular` = VALUES(`celular`), `password_hash` = VALUES(`password_hash`), `role_id` = VALUES(`role_id`), `status` = VALUES(`status`), `updated_at` = NOW();

INSERT INTO `usuarios` (`name`, `email`, `celular`, `password_hash`, `role_id`, `status`, `created_at`, `updated_at`)
VALUES ('Rafael', 'rafael@sge.com', '14991113113', '$2y$10$uJCfiDieruSGHnlIZYTbx.UFXTSKtojQ7V9GlS7p.U7WLlSKxEg4G', 1, 'ATIVO', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `celular` = VALUES(`celular`), `password_hash` = VALUES(`password_hash`), `role_id` = VALUES(`role_id`), `status` = VALUES(`status`), `updated_at` = NOW();
