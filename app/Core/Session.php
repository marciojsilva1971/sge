<?php
namespace App\Core;

/**
 * Classe utilitária para gerenciamento seguro de sessões, mensagens flash e CSRF.
 */
class Session {
    /**
     * Inicializa a sessão com parâmetros de segurança estritos.
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurações seguras para Cookies de Sessão
            ini_set('session.cookie_httponly', '1'); // Impede acesso via JavaScript (XSS)
            ini_set('session.use_only_cookies', '1'); // Força uso apenas de cookies (impede passar ID por URL)
            
            // Define SameSite=Strict para mitigar CSRF
            $cookieParams = [
                'lifetime' => 0, // Expira quando o navegador fecha
                'path'     => '/',
                'domain'   => '',
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Apenas HTTPS se disponível
                'httponly' => true,
                'samesite' => 'Strict'
            ];

            session_set_cookie_params($cookieParams);
            session_start();
        }

        // Prevenção contra Session Hijacking (Sequestro de Sessão)
        self::checkSessionIntegrity();
    }

    /**
     * Regenera o ID da sessão para evitar Session Fixation.
     */
    public static function regenerate(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            // Atualiza os metadados da sessão após a regeneração
            self::set('user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
            self::set('ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
            self::set('last_activity', time());
        }
    }

    /**
     * Verifica a integridade da sessão comparando IP e User-Agent.
     */
    protected static function checkSessionIntegrity(): void {
        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!self::has('user_agent')) {
            self::set('user_agent', $currentUserAgent);
        }
        if (!self::has('ip_address')) {
            self::set('ip_address', $currentIp);
        }

        // Se o IP ou o User Agent mudou repentinamente, destrói a sessão por suspeita de roubo
        if (self::get('user_agent') !== $currentUserAgent || self::get('ip_address') !== $currentIp) {
            self::destroy();
            self::start();
            self::setFlash('error', 'Sessão encerrada por motivos de segurança (mudança de rede detectada).');
        }

        // Proteção contra inatividade (Expirar sessão após 15 minutos)
        $timeout = 900; // 15 minutos
        if (self::has('last_activity')) {
            $inactiveTime = time() - self::get('last_activity');
            if ($inactiveTime > $timeout) {
                self::destroy();
                self::start();
                self::setFlash('error', 'Sessão expirada por inatividade. Faça login novamente.');
            }
        }
        self::set('last_activity', time());
    }

    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    /**
     * Define uma mensagem rápida (flash) que expira na próxima requisição.
     */
    public static function setFlash(string $key, mixed $message): void {
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Retorna e remove a mensagem rápida (flash) da sessão.
     */
    public static function getFlash(string $key): mixed {
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }

    /**
     * Encerra a sessão atual e limpa todos os dados.
     */
    public static function destroy(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(), 
                    '', 
                    time() - 42000,
                    $params["path"], 
                    $params["domain"],
                    $params["secure"], 
                    $params["httponly"]
                );
            }
            session_destroy();
        }
    }

    /**
     * Gera um token CSRF único e armazena na sessão se não existir.
     */
    public static function csrfToken(): string {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }

    /**
     * Valida se o token CSRF enviado coincide com o da sessão.
     */
    public static function validateCsrf(?string $token): bool {
        if (!$token || !self::has('csrf_token')) {
            return false;
        }
        return hash_equals(self::get('csrf_token'), $token);
    }
}
