<?php
namespace App\Services;

use Exception;

class EncryptionService {
    private static ?string $key = null;

    /**
     * Obtém e valida a chave de criptografia (APP_KEY) do .env.
     */
    private static function getKey(): string {
        if (self::$key === null) {
            $key = $_ENV['APP_KEY'] ?? '';
            if (empty($key)) {
                // Tenta carregar do arquivo .env manualmente caso $_ENV não esteja populado
                $envPath = dirname(__DIR__, 2) . '/.env';
                if (file_exists($envPath)) {
                    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (strpos($line, 'APP_KEY=') === 0) {
                            $key = substr($line, 8);
                            break;
                        }
                    }
                }
            }
            
            // Remove aspas se houver
            $key = trim($key, "\"'");
            
            if (strlen($key) < 32) {
                throw new Exception("Chave de criptografia APP_KEY inválida ou não configurada no .env. Deve ter pelo menos 32 caracteres.");
            }
            
            // Usamos a chave em formato raw/derivada para AES-256
            self::$key = substr(hash('sha256', $key, true), 0, 32);
        }
        return self::$key;
    }

    /**
     * Criptografa dados binários (conteúdo do arquivo) usando AES-256-CBC.
     * Retorna um array contendo:
     * - 'ciphertext': O conteúdo criptografado bruto
     * - 'iv': O IV gerado codificado em hexadecimal
     */
    public static function encrypt(string $data): array {
        $key = self::getKey();
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        $ciphertext = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            throw new Exception("Falha na criptografia dos dados.");
        }
        
        return [
            'ciphertext' => $ciphertext,
            'iv' => bin2hex($iv)
        ];
    }

    /**
     * Descriptografa dados binários usando AES-256-CBC e o IV fornecido.
     */
    public static function decrypt(string $ciphertext, string $ivHex): string {
        $key = self::getKey();
        $iv = hex2bin($ivHex);
        
        $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($plaintext === false) {
            throw new Exception("Falha na descriptografia dos dados. Chave ou IV incorretos.");
        }
        
        return $plaintext;
    }

    /**
     * Salva um arquivo de upload criptografando seu conteúdo físico na pasta segura.
     * Retorna array com os dados do arquivo criptografado.
     */
    public static function encryptAndSaveUploadedFile(array $file, string $storageDir): array {
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $fileContent = file_get_contents($file['tmp_name']);
        if ($fileContent === false) {
            throw new Exception("Não foi possível ler o arquivo temporário.");
        }

        // Criptografa
        $crypto = self::encrypt($fileContent);

        // Gera um nome de arquivo físico aleatório único
        $fileName = bin2hex(random_bytes(16)) . '.enc';
        $fullPath = rtrim($storageDir, '/\\') . '/' . $fileName;

        // Salva o binário criptografado
        if (file_put_contents($fullPath, $crypto['ciphertext']) === false) {
            throw new Exception("Erro ao gravar o arquivo criptografado no disco.");
        }

        return [
            'encrypted_file_path' => $fileName,
            'original_name' => $file['name'],
            'iv' => $crypto['iv'],
            'mime_type' => $file['type']
        ];
    }

    /**
     * Lê e descriptografa um arquivo físico criptografado.
     */
    public static function readAndDecryptFile(string $filePath, string $ivHex): string {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo não encontrado no disco: {$filePath}");
        }

        $ciphertext = file_get_contents($filePath);
        if ($ciphertext === false) {
            throw new Exception("Erro ao ler o arquivo criptografado.");
        }

        return self::decrypt($ciphertext, $ivHex);
    }
}
