-- Script SQL para Inserção Direta do Usuário Joana no Banco de Dados SGE
-- Pode ser executado via CLI MySQL ou phpMyAdmin no servidor remoto

INSERT INTO `usuarios` (`name`, `email`, `celular`, `password_hash`, `role_id`, `status`)
VALUES (
    'Joana',
    'joana@sge.com',
    '5500000000000',
    '$2y$10$mdHs0cHHjpcJYLJUMSuulekCPorzh.PgAufi4UTFqKeHokSnjb59S',
    1,
    'ATIVO'
)
ON DUPLICATE KEY UPDATE 
    `password_hash` = '$2y$10$mdHs0cHHjpcJYLJUMSuulekCPorzh.PgAufi4UTFqKeHokSnjb59S',
    `status` = 'ATIVO';
