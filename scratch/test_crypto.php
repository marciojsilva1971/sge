<?php
// Script de teste da Criptografia Simétrica - SGE
// Executar via terminal: php scratch/test_crypto.php

echo "Iniciando teste de criptografia...\n";

// 1. Carrega o arquivo .env
$envFile = dirname(__DIR__) . '/.env';
if (!file_exists($envFile)) {
    die("Erro: Arquivo .env não encontrado.\n");
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if (strpos($line, '=') !== false) {
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// 2. Inclui o EncryptionService
require_once dirname(__DIR__) . '/app/Services/EncryptionService.php';

use App\Services\EncryptionService;

$testText = "Texto de teste confidencial para campanha eleitoral 2026. Segredo de Estado!";
echo "Texto original: '{$testText}'\n";

try {
    // Teste 1: Criptografia / Descriptografia em memória
    $res = EncryptionService::encrypt($testText);
    echo "Texto criptografado (binário tamanho): " . strlen($res['ciphertext']) . " bytes\n";
    echo "IV Gerado (Hex): " . $res['iv'] . "\n";

    $decrypted = EncryptionService::decrypt($res['ciphertext'], $res['iv']);
    echo "Texto descriptografado: '{$decrypted}'\n";

    if ($decrypted === $testText) {
        echo "✓ TESTE 1 (MEMÓRIA) PASSOU COM SUCESSO!\n";
    } else {
        echo "❌ TESTE 1 FALHOU!\n";
    }

    // Teste 2: Criptografia / Descriptografia de arquivos em disco
    $storageDir = dirname(__DIR__) . '/storage/test_uploads';
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }

    $tmpFile = tempnam(sys_get_temp_dir(), 'test_upload_');
    file_put_contents($tmpFile, $testText);

    $fileData = [
        'tmp_name' => $tmpFile,
        'name' => 'documento_secreto.txt',
        'type' => 'text/plain'
    ];

    $saved = EncryptionService::encryptAndSaveUploadedFile($fileData, $storageDir);
    echo "Arquivo salvo em disco criptografado: '{$saved['encrypted_file_path']}'\n";

    $fullEncryptedPath = $storageDir . '/' . $saved['encrypted_file_path'];
    $decryptedContent = EncryptionService::readAndDecryptFile($fullEncryptedPath, $saved['iv']);
    echo "Conteúdo lido do arquivo descriptografado: '{$decryptedContent}'\n";

    if ($decryptedContent === $testText) {
        echo "✓ TESTE 2 (DISCO) PASSOU COM SUCESSO!\n";
    } else {
        echo "❌ TESTE 2 FALHOU!\n";
    }

    // Limpa arquivos de teste
    unlink($tmpFile);
    unlink($fullEncryptedPath);
    rmdir($storageDir);
    echo "Limpeza concluída.\n";

} catch (Exception $e) {
    echo "❌ OCORREU UM ERRO DURANTE O TESTE: " . $e->getMessage() . "\n";
}
