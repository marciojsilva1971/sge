<?php
/**
 * Script de Cadastro de Usuário via Navegador - SGE
 * 
 * Acesse via URL no navegador:
 *  Local:   http://localhost/sge/public/add_user_joana.php (ou http://localhost/sge/add_user_joana.php)
 *  Remoto:  https://seu-dominio.com/add_user_joana.php
 * 
 * ATENÇÃO: Remova ou exclua este arquivo após a execução por motivos de segurança!
 */

// 1. Carrega o arquivo .env (na raiz do projeto)
$envFile = dirname(__DIR__) . '/.env';
if (!file_exists($envFile)) {
    die("<h2 style='color:red;'>Erro: Arquivo .env não encontrado em {$envFile}</h2>");
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

$statusMessage = '';
$statusType = 'success';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Dados do Usuário
    $name     = 'Joana';
    $email    = 'joana@sge.com';
    $celular  = '5541999999999';
    $password = 'Joana@2026';
    $hash     = password_hash($password, PASSWORD_DEFAULT);

    // Role de Administrador
    $stmtRole = $pdo->query("SELECT id FROM `roles` WHERE name = 'ADMINISTRADOR' LIMIT 1");
    $role = $stmtRole->fetch();
    $roleId = $role ? (int)$role['id'] : 1;

    // Inserir ou Atualizar
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
        $statusMessage = "Usuário <strong>{$email}</strong> já existia (ID {$existingUser['id']}). Senha redefinida e conta ativada com sucesso!";
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
        $statusMessage = "Usuário <strong>{$email}</strong> cadastrado com sucesso com ID <strong>{$newId}</strong>!";
    }
} catch (Exception $e) {
    $statusType = 'error';
    $statusMessage = "Erro ao executar ação no banco de dados: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro do Usuário Joana - SGE</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #1e293b;
            border-radius: 12px;
            padding: 2.5rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            border: 1px solid #334155;
        }
        h2 {
            margin-top: 0;
            color: #38bdf8;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid #22c55e;
            color: #4ade80;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #f87171;
        }
        .details {
            background: #0f172a;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details td {
            padding: 0.5rem 0;
        }
        .details td.label {
            color: #94a3b8;
            font-weight: 600;
        }
        .warning {
            color: #fbbf24;
            font-size: 0.85rem;
            text-align: center;
            border-top: 1px solid #334155;
            padding-top: 1rem;
        }
        .btn {
            display: inline-block;
            background: #0284c7;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 1rem;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }
        .btn:hover {
            background: #0369a1;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>SGE - Gestão de Acessos</h2>
        
        <div class="alert alert-<?= $statusType ?>">
            <?= $statusMessage ?>
        </div>

        <?php if ($statusType === 'success'): ?>
        <div class="details">
            <table>
                <tr>
                    <td class="label">E-mail:</td>
                    <td>joana@sge.com</td>
                </tr>
                <tr>
                    <td class="label">Senha:</td>
                    <td><strong>Joana@2026</strong></td>
                </tr>
                <tr>
                    <td class="label">Perfil:</td>
                    <td>ADMINISTRADOR</td>
                </tr>
                <tr>
                    <td class="label">Status:</td>
                    <td><span style="color:#4ade80;">● ATIVO</span></td>
                </tr>
            </table>
        </div>
        <a href="login" class="btn">Ir para a Tela de Login</a>
        <?php endif; ?>

        <div class="warning">
            ⚠️ <strong>Recomendação de Segurança:</strong> Apague o arquivo <code>public/add_user_joana.php</code> do servidor após o uso.
        </div>
    </div>
</body>
</html>
