<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use PDO;
use Exception;

/**
 * Serviço de Log de Auditoria Imutável (em conformidade com LGPD e regras do TSE).
 */
class AuditLogger {
    /**
     * Grava uma ação na tabela de logs de auditoria.
     */
    public static function log(
        string $action, 
        string $tableName, 
        ?int $recordId = null, 
        ?array $oldValues = null, 
        ?array $newValues = null
    ): void {
        try {
            $db = Database::getInstance();
            
            // Tenta obter o usuário logado a partir da sessão
            $loggedUser = Session::get('user');
            $userId = $loggedUser ? (int)$loggedUser['id'] : null;

            // Coleta dados de rede
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown/CLI';

            $sql = "INSERT INTO `logs_auditoria` 
                    (`user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`) 
                    VALUES (:user_id, :action, :table_name, :record_id, :old_values, :new_values, :ip_address, :user_agent)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'user_id'    => $userId,
                'action'     => strtoupper($action),
                'table_name' => $tableName,
                'record_id'  => $recordId,
                'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $ipAddress,
                'user_agent' => substr($userAgent, 0, 255)
            ]);
        } catch (Exception $e) {
            // Em caso de falha no log de auditoria, registra no error log do PHP
            error_log("Falha ao registrar log de auditoria: " . $e->getMessage());
        }
    }
}
