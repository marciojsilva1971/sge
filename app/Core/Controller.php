<?php
namespace App\Core;

use Exception;

/**
 * Controller base com suporte a Views, Layouts, Redirecionamento e Segurança CSRF.
 */
abstract class Controller {
    /**
     * Retorna a URL base do aplicativo.
     * Prioriza a URL configurada no .env (quando a aplicação está na Web em produção)
     * e realiza detecção dinâmica por IP/Host durante o desenvolvimento e testes em rede local.
     */
    protected function baseUrl(string $path = ''): string {
        $envUrl = $_ENV['APP_URL'] ?? '';

        $isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1')) ||
                   (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                   (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
                   (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        // Se APP_URL estiver configurado no .env para um domínio público da web (não localhost), utiliza ele
        if (!empty($envUrl) && strpos($envUrl, 'localhost') === false) {
            $baseUrl = $envUrl;
            // Se o servidor responde em HTTPS, força https:// no APP_URL mesmo que estivesse com http:// no .env
            if ($isHttps && strpos($baseUrl, 'http://') === 0) {
                $baseUrl = preg_replace('/^http:\/\//i', 'https://', $baseUrl);
            }
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            // Em desenvolvimento local ou testes via IP local (192.168.x.x), detecta o host e esquema dinamicamente
            $scheme = $isHttps ? 'https' : 'http';

            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $dir = str_replace('\\', '/', dirname($scriptName));
            $baseSubdir = ($dir === '/' || $dir === '.' || $dir === '\\') ? '' : '/' . trim($dir, '/');

            $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $baseSubdir . '/';
        } else {
            $baseUrl = $envUrl ?: 'http://localhost/sge/public/';
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Renderiza uma view dentro de um layout específico.
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void {
        // Extrai variáveis para torná-las utilizáveis na view
        extract($data);

        // Caminho da view
        $viewPath = dirname(__DIR__, 2) . "/app/Views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new Exception("View '{$view}' não encontrada em: " . $viewPath);
        }

        // Renderiza o conteúdo da view em um buffer
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Renderiza o layout correspondente
        $layoutPath = dirname(__DIR__, 2) . "/app/Views/layouts/{$layout}.php";
        if (!file_exists($layoutPath)) {
            throw new Exception("Layout '{$layout}' não encontrado em: " . $layoutPath);
        }

        require $layoutPath;
    }

    /**
     * Redireciona para uma rota interna do projeto.
     */
    protected function redirect(string $path): void {
        header("Location: " . $this->baseUrl($path));
        exit;
    }

    /**
     * Retorna uma resposta JSON.
     */
    protected function json(mixed $data, int $statusCode = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Valida o token CSRF de requisições POST para evitar Cross-Site Request Forgery.
     */
    protected function validatePostCsrf(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Session::validateCsrf($token)) {
                Session::setFlash('error', 'Token de segurança inválido ou expirado (CSRF).');
                $this->redirect('/');
            }
        }
    }

    /**
     * Retorna o usuário atualmente autenticado ou null.
     */
    protected function getLoggedUser(): ?array {
        return Session::get('user');
    }

    /**
     * Garante que o usuário esteja autenticado, caso contrário redireciona para login.
     */
    protected function requireAuth(): array {
        $user = $this->getLoggedUser();
        if (!$user) {
            Session::setFlash('error', 'Acesso restrito. Por favor, faça login.');
            $this->redirect('/login');
        }
        return $user;
    }

    /**
     * Garante que o usuário possua determinada permissão no RBAC.
     */
    protected function requirePermission(string $permission): void {
        $user = $this->requireAuth();
        $permissions = $user['role_permissions'] ?? [];
        
        if (empty($permissions[$permission])) {
            Session::setFlash('error', 'Você não tem permissão para acessar esta funcionalidade.');
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Converte uma string formatada em Real Brasileiro (R$) para float de forma robusta e precisa.
     */
    protected function parseBrlCurrency(string $rawValue): float {
        $rawValue = trim((string)$rawValue);
        if ($rawValue === '') {
            return 0.0;
        }

        // Remove símbolos de moeda (R$), espaços normais e inquebráveis
        $clean = preg_replace('/[^\d,.-]/u', '', $rawValue);
        if (empty($clean)) {
            return 0.0;
        }

        // Caso haja ponto e vírgula simultaneamente (ex: 1.500,50 ou 1,500.50)
        if (strpos($clean, ',') !== false && strpos($clean, '.') !== false) {
            if (strrpos($clean, ',') > strrpos($clean, '.')) {
                // Padrão Brasileiro: 1.500,50
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                // Padrão Americano: 1,500.50
                $clean = str_replace(',', '', $clean);
            }
        } elseif (strpos($clean, ',') !== false) {
            // Vírgula como separador decimal: 150,50
            $clean = str_replace(',', '.', $clean);
        }

        return round((float)$clean, 2);
    }
}
