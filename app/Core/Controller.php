<?php
namespace App\Core;

use Exception;

/**
 * Controller base com suporte a Views, Layouts, Redirecionamento e Segurança CSRF.
 */
abstract class Controller {
    /**
     * Retorna a URL base do aplicativo.
     */
    protected function baseUrl(string $path = ''): string {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost/sge/public/';
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
     * Converte uma string formatada em Real Brasileiro (R$) para float de forma robusta.
     */
    protected function parseBrlCurrency(string $rawValue): float {
        // Remove todos os caracteres que não são dígitos, vírgula, ponto ou sinal de menos
        $clean = preg_replace('/[^\d,.-]/', '', $rawValue);
        if (strpos($clean, ',') !== false) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        }
        return floatval($clean);
    }
}
