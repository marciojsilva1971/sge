<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $stmt = $db->query("
        SELECT id, nome_completo, cpf, celular_whatsapp, email, status, created_at 
        FROM colaboradores 
        WHERE status != 'ATIVO' OR status IS NULL
        ORDER BY id DESC
    ");
    $colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "COLABORADORES NÃO HOMOLOGADOS (" . count($colaboradores) . " encontrados):\n";
    echo str_repeat("=", 80) . "\n";
    foreach ($colaboradores as $c) {
        echo sprintf(
            "ID: %d | Nome: %-30s | Status: %-32s | Whats: %s\n",
            $c['id'],
            $c['nome_completo'],
            $c['status'] ?? 'PENDENTE',
            $c['celular_whatsapp']
        );
    }
    echo str_repeat("=", 80) . "\n";
} catch (Exception $e) {
    echo "Erro ao consultar banco de dados: " . $e->getMessage() . "\n";
}
