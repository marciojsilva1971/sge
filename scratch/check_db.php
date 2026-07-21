<?php
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("DESCRIBE usuarios");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($rows);
