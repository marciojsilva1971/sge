<?php
namespace App\Services;

/**
 * Serviço Simulado para envio de e-mails de ativação em ambiente local.
 */
class EmailService {
    /**
     * Envia o e-mail gravando o conteúdo em um arquivo de log público para fácil visualização local.
     */
    public static function send(string $to, string $subject, string $message): bool {
        $logDir = dirname(__DIR__, 2) . '/public/uploads';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/email_log.txt';
        
        $timestamp = date('Y-m-d H:i:s');
        $divider = str_repeat('-', 80) . "\n";
        
        $content = $divider;
        $content .= "Data/Hora: {$timestamp}\n";
        $content .= "Para: {$to}\n";
        $content .= "Assunto: {$subject}\n";
        $content .= "Mensagem:\n{$message}\n";
        $content .= $divider;

        // Grava no arquivo
        file_put_contents($logFile, $content, FILE_APPEND);
        
        return true;
    }
}
