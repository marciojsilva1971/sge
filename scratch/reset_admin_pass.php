<?php
// Script Scratch para Redefinição de Senha do Administrador

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (preg_match('/^"(.*)"$/', $value, $m)) $value = $m[1];
            elseif (preg_match("/^'(.*)'$/", $value, $m)) $value = $m[1];
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$dbname = $_ENV['DB_NAME'] ?? 'sge';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $adminEmail = 'admin@sge.com';
    $novaSenha = 'Admin@12345Password';
    $newHash = password_hash($novaSenha, PASSWORD_DEFAULT);

    // Garante que o perfil ADMINISTRADOR existe
    $stmtRole = $pdo->query("SELECT id FROM roles WHERE name = 'ADMINISTRADOR' LIMIT 1");
    $role = $stmtRole->fetch();
    $roleId = $role ? $role['id'] : 1;

    // Atualiza a senha e o status do usuário admin@sge.com
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = :hash, status = 'ATIVO', role_id = :role_id WHERE email = :email");
    $stmt->execute([
        'hash'    => $newHash,
        'role_id' => $roleId,
        'email'   => $adminEmail
    ]);

    if ($stmt->rowCount() > 0) {
        echo "SUCESSO: Senha e status do administrador ({$adminEmail}) atualizados com sucesso!\n";
    } else {
        // Se o e-mail admin@sge.com não existir, insere um novo
        $stmtInsert = $pdo->prepare("INSERT INTO usuarios (name, email, celular, password_hash, role_id, status) VALUES ('Administrador SGE', :email, '5541999999999', :hash, :role_id, 'ATIVO')");
        $stmtInsert->execute([
            'email'   => $adminEmail,
            'hash'    => $newHash,
            'role_id' => $roleId
        ]);
        echo "SUCESSO: Administrador ({$adminEmail}) criado com sucesso!\n";
    }

    echo "Novas Credenciais de Acesso:\n";
    echo "  E-mail: {$adminEmail}\n";
    echo "  Senha : {$novaSenha}\n";

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
