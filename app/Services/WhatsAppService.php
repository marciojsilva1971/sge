<?php
namespace App\Services;

/**
 * Serviço de Integração com Z-API e Geração de Links de Click-to-Chat do WhatsApp.
 */
class WhatsAppService {
    /**
     * Limpa o telefone e adiciona o DDI 55 (Brasil) se necessário.
     */
    public static function formatPhone(string $phone): string {
        // Remove qualquer caractere que não seja número
        $cleaned = preg_replace('/\D/', '', $phone);

        // Se começar com 55 e tiver mais de 11 dígitos (ex: 55011999999999), remove o 55 temporariamente para tratar o 0 do DDD
        if (str_starts_with($cleaned, '55') && strlen($cleaned) > 11) {
            $cleaned = substr($cleaned, 2);
        }

        // Se começar com zero (ex: 011999999999 ou 041999999999), remove o zero à esquerda do DDD
        $cleaned = ltrim($cleaned, '0');

        // Se o número tiver 10 ou 11 dígitos (ex: 11999999999 ou 4199999999), adiciona o DDI 55 do Brasil
        if (strlen($cleaned) === 10 || strlen($cleaned) === 11) {
            $cleaned = '55' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Dispara mensagem automática de forma assíncrona/silenciosa via Z-API.
     */
    public static function send(string $phone, string $message): bool {
        $formattedPhone = self::formatPhone($phone);
        $apiUrl = $_ENV['ZAPI_URL'] ?? 'https://api.z-api.io/instances/3F3442BBA01DD1E1BB8B82171A0617F6/token/615EDEA93296491518BBA31A/send-text';
        $clientToken = $_ENV['ZAPI_CLIENT_TOKEN'] ?? 'F328a02a354f54675adaa95b599db9eabS';

        $payload = [
            'phone'   => $formattedPhone,
            'message' => $message
        ];

        // Disparo assíncrono simulado via cURL com timeout curto
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Client-Token: ' . $clientToken
        ]);

        // Timeout estrito de 2 segundos para conexão e envio total para não travar a requisição do usuário
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        
        // Ignora verificação SSL local se houver problemas de certificado auto-assinado no XAMPP
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("Erro de cURL no envio de WhatsApp: " . $curlError);
            return false;
        }

        if ($httpCode !== 200) {
            error_log("Erro na resposta da Z-API. Código HTTP: {$httpCode}. Resposta: {$response}");
            return false;
        }

        return true;
    }

    /**
     * Gera o link do Click-to-Chat para envio manual.
     */
    public static function generateClickToChat(string $phone, string $message): string {
        $formattedPhone = self::formatPhone($phone);
        return 'https://wa.me/' . $formattedPhone . '?text=' . urlencode($message);
    }
}
