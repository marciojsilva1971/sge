-- ========================================================
-- SCRIPT DE ESTRUTURA: OBRIGATORIEDADE DE PLACA E HODÔMETRO (KM)
-- Módulo: Portal do Colaborador / Viagens e Reembolso de Combustível
-- Data: 2026-07-22
-- ========================================================

ALTER TABLE `travel_reports` 
MODIFY COLUMN `vehicle_plate` VARCHAR(20) NOT NULL,
ADD COLUMN `initial_km` INT NULL AFTER `vehicle_plate`,
ADD COLUMN `final_km` INT NULL AFTER `initial_km`;
