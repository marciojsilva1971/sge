-- Atualização do Schema do Banco de Dados - SGE 2026
-- Adiciona a coluna foto_rosto_path na tabela colaboradores

ALTER TABLE `colaboradores` 
ADD COLUMN `foto_rosto_path` VARCHAR(255) NULL AFTER `documento_foto_path`;
