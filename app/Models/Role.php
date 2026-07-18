<?php
namespace App\Models;

use App\Core\Model;
use App\Services\AuditLogger;
use PDO;

/**
 * Modelo de Perfis de Acesso (Roles) do Sistema.
 */
class Role extends Model {
    protected string $table = 'roles';

    /**
     * Busca um perfil pelo nome.
     */
    public function findByName(string $name): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        $result = $stmt->fetch();
        return $result ? $result : null;
    }

    /**
     * Atualiza as permissões de um perfil de acesso.
     */
    public function updatePermissions(int $id, array $permissions): bool {
        $role = $this->find($id);
        if (!$role) {
            return false;
        }

        $oldPermissions = json_decode($role['permissions'], true);
        $newPermissionsJson = json_encode($permissions, JSON_UNESCAPED_UNICODE);

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `permissions` = :permissions WHERE `id` = :id");
            $stmt->execute([
                'permissions' => $newPermissionsJson,
                'id'          => $id
            ]);

            // Trilha de auditoria
            AuditLogger::log(
                'UPDATE_ROLE_PERMISSIONS',
                $this->table,
                $id,
                ['permissions' => $oldPermissions],
                ['permissions' => $permissions]
            );

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar permissões do cargo: " . $e->getMessage());
            return false;
        }
    }
}
