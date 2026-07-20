<?php
namespace App\Models;

use App\Core\Model;
use App\Services\AuditLogger;
use PDO;
use Exception;

/**
 * Modelo de Usuários do Sistema.
 */
class User extends Model {
    protected string $table = 'usuarios';

    /**
     * Busca um usuário pelo e-mail, incluindo permissões do seu cargo.
     */
    public function findByEmail(string $email): ?array {
        $sql = "SELECT u.*, r.name as role_name, r.permissions as role_permissions 
                FROM `{$this->table}` u
                LEFT JOIN `roles` r ON u.role_id = r.id
                WHERE u.email = :email LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();
        
        if ($result) {
            // Decodifica permissões de JSON para array
            $result['role_permissions'] = json_decode($result['role_permissions'] ?? '[]', true) ?? [];
        }
        
        return $result ? $result : null;
    }

    /**
     * Busca um usuário pelo ID, incluindo permissões do seu cargo.
     */
    public function findWithRole(int $id): ?array {
        $sql = "SELECT u.*, r.name as role_name, r.permissions as role_permissions 
                FROM `{$this->table}` u
                LEFT JOIN `roles` r ON u.role_id = r.id
                WHERE u.id = :id LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['role_permissions'] = json_decode($result['role_permissions'], true) ?? [];
        }
        
        return $result ? $result : null;
    }

    /**
     * Busca um usuário pelo token de ativação (SHA-256).
     */
    public function findByActivationToken(string $token): ?array {
        $hash = hash('sha256', $token);
        
        $sql = "SELECT u.*, r.name as role_name 
                FROM `{$this->table}` u
                JOIN `roles` r ON u.role_id = r.id
                WHERE u.token_ativacao_hash = :hash 
                AND u.status = 'PENDENTE'
                AND u.token_expira_em > NOW() 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['hash' => $hash]);
        $result = $stmt->fetch();
        return $result ? $result : null;
    }

    /**
     * Lista todos os usuários juntamente com as roles correspondentes.
     */
    public function getUsersWithRoles(array $filters = []): array {
        $sql = "SELECT u.id, u.name, u.email, u.celular, u.status, u.profile_photo_path, u.created_at, r.name as role_name 
                FROM `{$this->table}` u
                JOIN `roles` r ON u.role_id = r.id";
        
        $where = [];
        $params = [];

        if (!empty($filters['name'])) {
            $where[] = "u.name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['role_id'])) {
            $where[] = "u.role_id = :role_id";
            $params['role_id'] = (int)$filters['role_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "u.status = :status";
            $params['status'] = $filters['status'];
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY u.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cria um convite para um novo usuário (cria registro PENDENTE e gera token).
     */
    public function createInvite(array $userData): string {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        
        // Define expiração do token de ativação para 24 horas
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->db->beginTransaction();
        try {
            $data = [
                'name'                => $userData['name'],
                'email'               => $userData['email'],
                'celular'             => $userData['celular'],
                'password_hash'       => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), // Senha provisória aleatória
                'role_id'             => $userData['role_id'],
                'status'              => 'PENDENTE',
                'token_ativacao_hash' => $tokenHash,
                'token_expira_em'     => $expiresAt
            ];

            $userId = $this->create($data);

            // Grava log de auditoria
            AuditLogger::log(
                'INVITE_USER',
                $this->table,
                $userId,
                null,
                [
                    'name'    => $data['name'],
                    'email'   => $data['email'],
                    'celular' => $data['celular'],
                    'role_id' => $data['role_id']
                ]
            );

            $this->db->commit();
            return $token;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Ativa um usuário pendente definindo sua senha e foto.
     */
    public function activateAccount(int $userId, string $password, ?string $photoPath): bool {
        $user = $this->find($userId);
        if (!$user || $user['status'] !== 'PENDENTE') {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE `{$this->table}` 
                 SET `password_hash` = :pass, 
                     `status` = 'ATIVO', 
                     `profile_photo_path` = :photo, 
                     `token_ativacao_hash` = NULL, 
                     `token_expira_em` = NULL 
                 WHERE `id` = :id"
            );

            $stmt->execute([
                'pass'  => password_hash($password, PASSWORD_DEFAULT),
                'photo' => $photoPath ?: $user['profile_photo_path'],
                'id'    => $userId
            ]);

            // Grava log de auditoria (sem salvar o password hash no log!)
            AuditLogger::log(
                'ACTIVATE_ACCOUNT',
                $this->table,
                $userId,
                ['status' => 'PENDENTE'],
                ['status' => 'ATIVO', 'has_photo' => !empty($photoPath)]
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro na ativação de conta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza dados de perfil do próprio usuário.
     */
    public function updateProfileInfo(int $userId, array $data): bool {
        $oldUser = $this->find($userId);
        if (!$oldUser) {
            return false;
        }

        // Filtra campos editáveis pelo próprio usuário
        $updateData = [];
        $logOld = [];
        $logNew = [];

        if (isset($data['name']) && $data['name'] !== $oldUser['name']) {
            $updateData['name'] = $data['name'];
            $logOld['name'] = $oldUser['name'];
            $logNew['name'] = $data['name'];
        }

        if (isset($data['celular']) && $data['celular'] !== $oldUser['celular']) {
            $updateData['celular'] = $data['celular'];
            $logOld['celular'] = $oldUser['celular'];
            $logNew['celular'] = $data['celular'];
        }

        if (isset($data['profile_photo_path'])) {
            $updateData['profile_photo_path'] = $data['profile_photo_path'];
            $logOld['profile_photo_path'] = $oldUser['profile_photo_path'];
            $logNew['profile_photo_path'] = $data['profile_photo_path'];
        }

        if (!empty($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $logNew['password_changed'] = true;
        }

        if (empty($updateData)) {
            return true;
        }

        $this->db->beginTransaction();
        try {
            $this->update($userId, $updateData);

            AuditLogger::log(
                'UPDATE_PROFILE',
                $this->table,
                $userId,
                $logOld,
                $logNew
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar dados do perfil: " . $e->getMessage());
            return false;
        }
    }
}
