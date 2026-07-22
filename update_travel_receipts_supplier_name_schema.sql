-- Migration SQL: Adiciona suporte a Nome/Razao Social do Posto nos Recibos de Combustivel
ALTER TABLE `travel_receipts` ADD COLUMN `supplier_name` VARCHAR(255) NULL AFTER `supplier_cnpj`;
