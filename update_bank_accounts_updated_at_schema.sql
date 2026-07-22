-- ========================================================
-- SCRIPT DE ESTRUTURA: ATUALIZAÇÃO TABELA BANK_ACCOUNTS
-- Módulo: Financeiro / Conciliação Bancária
-- Adiciona coluna updated_at para registrar o timestamp de atualização de saldo
-- Data: 2026-07-22
-- ========================================================

ALTER TABLE `bank_accounts` 
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
