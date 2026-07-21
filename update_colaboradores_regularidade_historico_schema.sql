-- Cria a tabela de histórico de consultas de regularidade cadastral
CREATE TABLE IF NOT EXISTS `colaboradores_regularidade_historico` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `colaborador_id` INT NOT NULL,
    `valido` TINYINT(1) NOT NULL,
    `status_cpf` VARCHAR(100) NOT NULL,
    `status_tse` VARCHAR(100) NOT NULL,
    `tse_regularidade_json` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
