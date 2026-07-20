<?php
namespace App\Core;

use PDO;
use PDOException;
use Exception;

/**
 * Singleton de Conexão PDO segura com o Banco de Dados.
 */
class Database {
    private static ?PDO $instance = null;

    // Construtor privado para impedir instanciação externa
    private function __construct() {}

    // Impedir clonagem da classe
    private function __clone() {}

    /**
     * Retorna a instância única do PDO.
     * 
     * @return PDO
     * @throws Exception
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $configPath = dirname(__DIR__, 2) . '/config/database.php';
            if (!file_exists($configPath)) {
                throw new Exception("Arquivo de configuração do banco de dados não encontrado: " . $configPath);
            }

            $config = require $configPath;

            try {
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    $config['host'],
                    $config['port'],
                    $config['dbname'],
                    $config['charset']
                );

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // Desativa emulação para evitar SQL Injection no MySQL
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '-03:00'"
                ];

                self::$instance = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                // Em produção, não mostre a mensagem com dados sensíveis
                error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
                throw new Exception("Falha na conexão com o banco de dados. Por favor, tente novamente mais tarde.");
            }
        }

        return self::$instance;
    }
}
