<?php
// Script de Atualização do Banco de Dados - SGE (Sprint 2)
// Executar via terminal: php scratch/update_db.php

echo "Iniciando atualização do banco de dados SGE (Sprint 2)...\n";

// 1. Carrega o arquivo .env
$envFile = dirname(__DIR__) . '/.env';
if (!file_exists($envFile)) {
    die("Erro: Arquivo .env não encontrado em {$envFile}. Crie o .env primeiro.\n");
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $matches)) $value = $matches[1];
        elseif (preg_match("/^'(.*)'$/", $value, $matches)) $value = $matches[1];
        $_ENV[$name] = $value;
    }
}

// 2. Conecta ao banco
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$dbname = $_ENV['DB_NAME'] ?? 'sge';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Conexão com o banco de dados '{$dbname}' estabelecida com sucesso.\n";
} catch (PDOException $e) {
    die("Erro de conexão MySQL: " . $e->getMessage() . "\n");
}

// 3. Executa a criação das novas tabelas diretamente
echo "Criando novas tabelas de suporte financeiro e comprovação...\n";

$queries = [
    "CREATE TABLE IF NOT EXISTS `bank_accounts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `bank_name` VARCHAR(100) NOT NULL,
        `agency` VARCHAR(20) NOT NULL,
        `account_number` VARCHAR(30) NOT NULL,
        `fund_type` ENUM('FEFC', 'FUNDO_PARTIDARIO', 'OUTROS_RECURSOS') NOT NULL,
        `balance` DECIMAL(15,2) DEFAULT 0.00,
        `status` ENUM('ATIVA', 'ENCERRADA') DEFAULT 'ATIVA',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS `suppliers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `cnpj_cpf` VARCHAR(20) NOT NULL UNIQUE,
        `corporate_name` VARCHAR(255) NOT NULL,
        `trade_name` VARCHAR(255) NULL,
        `address` VARCHAR(255) NULL,
        `phone` VARCHAR(20) NULL,
        `email` VARCHAR(100) NULL,
        `status` ENUM('ATIVO', 'INATIVO') DEFAULT 'ATIVO',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS `spce_categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `code` VARCHAR(50) NOT NULL UNIQUE,
        `description` VARCHAR(255) NOT NULL,
        `type` ENUM('RECEITA', 'DESPESA') NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS `despesas` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `description` VARCHAR(255) NOT NULL,
        `supplier_id` INT NOT NULL,
        `bank_account_id` INT NOT NULL,
        `value` DECIMAL(15,2) NOT NULL,
        `date_incurred` DATE NOT NULL,
        `payment_method` VARCHAR(50) NOT NULL,
        `status` ENUM('PENDENTE', 'APROVADO', 'REJEITADO', 'PAGO') DEFAULT 'PENDENTE',
        `spce_category_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `approved_by` INT NULL,
        `approved_at` DATETIME NULL,
        `notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
        FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE RESTRICT,
        FOREIGN KEY (`spce_category_id`) REFERENCES `spce_categories` (`id`) ON DELETE RESTRICT,
        FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
        FOREIGN KEY (`approved_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS `comprovantes_cripto` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `expense_id` INT NOT NULL,
        `encrypted_file_path` VARCHAR(255) NOT NULL,
        `original_name` VARCHAR(255) NOT NULL,
        `iv` VARCHAR(64) NOT NULL,
        `mime_type` VARCHAR(100) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`expense_id`) REFERENCES `despesas` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS `travel_reports` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `purpose` VARCHAR(255) NOT NULL,
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `vehicle_plate` VARCHAR(20) NULL,
        `status` ENUM('EM_ANDAMENTO', 'ENVIADO', 'APROVADO', 'REJEITADO') DEFAULT 'EM_ANDAMENTO',
        `approved_by` INT NULL,
        `approved_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
        FOREIGN KEY (`approved_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS `travel_receipts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `travel_report_id` INT NOT NULL,
        `supplier_cnpj` VARCHAR(20) NOT NULL,
        `receipt_date` DATE NOT NULL,
        `value` DECIMAL(15,2) NOT NULL,
        `spce_category_id` INT NOT NULL,
        `encrypted_file_path` VARCHAR(255) NOT NULL,
        `iv` VARCHAR(64) NOT NULL,
        `status` ENUM('PENDENTE', 'APROVADO', 'REJEITADO') DEFAULT 'PENDENTE',
        `notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`travel_report_id`) REFERENCES `travel_reports` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`spce_category_id`) REFERENCES `spce_categories` (`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS `militancy_activities` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `activity_date` DATE NOT NULL,
        `description` TEXT NOT NULL,
        `encrypted_photo_path` VARCHAR(255) NOT NULL,
        `iv` VARCHAR(64) NOT NULL,
        `latitude` DECIMAL(10,8) NOT NULL,
        `longitude` DECIMAL(11,8) NOT NULL,
        `status` ENUM('PENDENTE', 'APROVADO', 'REJEITADO') DEFAULT 'PENDENTE',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS `receitas` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `description` VARCHAR(255) NOT NULL,
        `value` DECIMAL(15,2) NOT NULL,
        `date_received` DATE NOT NULL,
        `bank_account_id` INT NOT NULL,
        `spce_category_id` INT NOT NULL,
        `donor_name` VARCHAR(255) NOT NULL,
        `donor_cpf` VARCHAR(20) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE RESTRICT,
        FOREIGN KEY (`spce_category_id`) REFERENCES `spce_categories` (`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($queries as $sql) {
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        echo "Erro ao executar query: " . $e->getMessage() . "\n";
    }
}

echo "Tabelas criadas com sucesso!\n";
echo "Atualização concluída com sucesso.\n";
