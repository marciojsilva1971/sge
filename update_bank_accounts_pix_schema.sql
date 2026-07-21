-- Script SQL: Adição da Coluna de Chave PIX nas Contas Bancárias (SGE)
-- Permite armazenar a Chave PIX vinculada a cada conta bancária de campanha.

ALTER TABLE `bank_accounts` ADD COLUMN `pix_key` VARCHAR(100) NULL AFTER `account_number`;
