-- Adiciona colunas para salvar a situação cadastral (Receita/TSE) dos colaboradores
ALTER TABLE `colaboradores`
ADD COLUMN `tse_regularidade_json` TEXT NULL AFTER `optin_ip`,
ADD COLUMN `tse_regularidade_data` DATETIME NULL AFTER `tse_regularidade_json`;
