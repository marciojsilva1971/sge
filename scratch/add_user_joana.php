<?php
/**
 * Script de Cadastro / Redefinição Direta de Usuário 'joana' no Banco de Dados
 * 
 * Executar via Terminal / SSH no servidor:
 * php scratch/add_user_joana.php
 */

echo "======================================================\n";
echo " CADASTRO DIRETO DE USUÁRIO - SGE (joana@sge.com)\n";
echo "======================================================\n\n";

// 1. Carrega o arquivo .env
$envFile = dirname(__DIR__) . '/.env';
if (!file_exists($envFile)) {
    die("Erro: Arquivo .env não encontrado no caminho: {$envFile}\n");
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

$host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port   = $_ENV['DB_PORT'] ?? '3306';
$user   = $_ENV['DB_USER'] ?? 'root';
$pass   = $_ENV['DB_PASS'] ?? '';
$dbname = $_ENV['DB_NAME'] ?? 'sge';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "Conexão com o banco de dados '{$dbname}' em '{$host}' estabelecida.\n";
} catch (PDOException $e) {
    die("Erro de conexão MySQL: " . $e->getMessage() . "\n");
}

// 2. Dados do usuário joana
$name     = 'Joana';
$email    = 'joana@sge.com';
$celular  = '5541999999999';
$password = 'Joana@2026';
$hash     = password_hash($password, PASSWORD_DEFAULT);

// Busca a role de ADMINISTRADOR
$stmtRole = $pdo->query("SELECT id FROM `roles` WHERE name = 'ADMINISTRADOR' LIMIT 1");
$role = $stmtRole->fetch();
$roleId = $role ? (int)$role['id'] : 1;

// 3. Verifica existência e insere/atualiza
$stmtCheck = $pdo->prepare("SELECT id FROM `usuarios` WHERE email = :email LIMIT 1");
$stmtCheck->execute(['email' => $email]);
$existingUser = $stmtCheck->fetch();

if ($existingUser) {
    $stmtUpdate = $pdo->prepare(
        "UPDATE `usuarios` 
         SET password_hash = :hash, status = 'ATIVO', role_id = :role_id, name = :name
         WHERE id = :id"
    );
    $stmtUpdate->execute([
        'hash'    => $hash,
        'role_id' => $roleId,
        'name'    => $name,
        'id'      => $existingUser['id']
    ]);
    echo "\n✔ Usuário '{$email}' já existia (ID {$existingUser['id']}).\n";
    echo "✔ Senha atualizada com sucesso para: {$password}\n";
    echo "✔ Status alterado para ATIVO.\n";
} else {
    $stmtInsert = $pdo->prepare(
        "INSERT INTO `usuarios` (name, email, celular, password_hash, role_id, status)
         VALUES (:name, :email, :celular, :password_hash, :role_id, 'ATIVO')"
    );
    $stmtInsert->execute([
        'name'          => $name,
        'email'         => $email,
        'celular'       => $celular,
        'password_hash' => $hash,
        'role_id'       => $roleId
    ]);
    $newId = $pdo->lastInsertId();
    echo "\n✔ Usuário '{$email}' cadastrado com sucesso com ID {$newId}!\n";
}

echo "\n------------------------------------------------------\n";
echo " CREDENCIAIS DE ACESSO:\n";
echo "   E-mail: {$email}\n";
echo "   Senha : {$password}\n";
echo "   Cargo : ADMINISTRADOR\n";
echo "======================================================\n";
