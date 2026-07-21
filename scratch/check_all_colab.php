<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT id, nome_completo, cpf, celular_whatsapp, email, status FROM colaboradores");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "TOTAL DE COLABORADORES NO BANCO LOCAL: " . count($all) . "\n";
    foreach ($all as $c) {
        print_r($c);
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
