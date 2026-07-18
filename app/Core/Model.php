<?php
namespace App\Core;

use PDO;
use Exception;

/**
 * Model base com operações CRUD simplificadas.
 */
abstract class Model {
    protected PDO $db;
    protected string $table = '';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Busca todos os registros da tabela correspondente.
     */
    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Busca um registro pelo seu ID.
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ? $result : null;
    }

    /**
     * Insere um novo registro na tabela.
     */
    public function create(array $data): int {
        $columns = implode(', ', array_map(fn($col) => "`{$col}`", array_keys($data)));
        $placeholders = implode(', ', array_map(fn($col) => ":{$col}", array_keys($data)));

        $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza um registro existente pelo ID.
     */
    public function update(int $id, array $data): bool {
        $fields = '';
        foreach (array_keys($data) as $column) {
            $fields .= "`{$column}` = :{$column}, ";
        }
        $fields = rtrim($fields, ', ');

        $sql = "UPDATE `{$this->table}` SET {$fields} WHERE id = :id_filter";
        $stmt = $this->db->prepare($sql);

        // Mescla os dados com o filtro de ID
        $params = array_merge($data, ['id_filter' => $id]);

        return $stmt->execute($params);
    }

    /**
     * Exclui um registro pelo ID.
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
