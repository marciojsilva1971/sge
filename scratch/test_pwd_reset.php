<?php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/User.php';

$userModel = new App\Models\User();
$all = $userModel->all();

echo "Usuários no banco:\n";
foreach ($all as $u) {
    echo "- ID {$u['id']}: {$u['name']} ({$u['email']}) [{$u['status']}]\n";
}

if (!empty($all)) {
    $target = $all[0];
    echo "\nTestando alteração de senha no usuário ID {$target['id']} ({$target['name']})...\n";
    
    $newPass = "Senha@2026Strong!";
    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    
    $updated = $userModel->update($target['id'], ['password_hash' => $hash]);
    echo "Resultado do update: " . ($updated ? "SUCESSO" : "FALHA") . "\n";
    
    $check = $userModel->find($target['id']);
    $verified = password_verify($newPass, $check['password_hash']);
    echo "Verificação com password_verify: " . ($verified ? "SENHA VÁLIDA!" : "FALHOU!") . "\n";
}
