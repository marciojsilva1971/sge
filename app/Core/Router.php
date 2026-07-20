<?php
namespace App\Core;

use Exception;

/**
 * Roteador MVC simples em PHP Puro.
 */
class Router {
    protected array $routes = [];
    protected string $basePath = '';

    public function __construct() {
        // Determina o basePath da URL (ex: /projeto-eleicao)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $this->basePath = rtrim(dirname($scriptName), '/\\');
    }

    /**
     * Adiciona uma rota GET.
     */
    public function get(string $route, string $handler): void {
        $this->addRoute('GET', $route, $handler);
    }

    /**
     * Adiciona uma rota POST.
     */
    public function post(string $route, string $handler): void {
        $this->addRoute('POST', $route, $handler);
    }

    /**
     * Registra a rota interna.
     */
    protected function addRoute(string $method, string $route, string $handler): void {
        // Garante que a rota comece com /
        $route = '/' . ltrim($route, '/');
        $this->routes[] = [
            'method'  => $method,
            'route'   => $route,
            'handler' => $handler
        ];
    }

    /**
     * Despacha a requisição atual para a rota correspondente.
     */
    public function dispatch(): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove a query string da URL (ex: /ativar?token=... -> /ativar)
        if (strpos($requestUri, '?') !== false) {
            $requestUri = explode('?', $requestUri)[0];
        }

        // Remove o basePath se a aplicação estiver em uma subpasta do Apache (ex: /projeto-eleicao/login -> /login)
        if ($this->basePath !== '' && strpos($requestUri, $this->basePath) === 0) {
            $requestUri = substr($requestUri, strlen($this->basePath));
        }

        $requestUri = '/' . ltrim($requestUri, '/');

        // Busca correspondência de rota
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['route'] === $requestUri) {
                $this->executeHandler($route['handler']);
                return;
            }
        }

        // Rota não encontrada
        $this->send404();
    }

    /**
     * Instancia o controlador e chama a ação correspondente.
     */
    protected function executeHandler(string $handler): void {
        list($controllerName, $action) = explode('@', $handler);
        $fullControllerName = "\\App\\Controllers\\" . $controllerName;

        if (!class_exists($fullControllerName)) {
            throw new Exception("Controlador '{$fullControllerName}' não encontrado.");
        }

        $controller = new $fullControllerName();

        if (!method_exists($controller, $action)) {
            throw new Exception("Ação '{$action}' não encontrada no controlador '{$fullControllerName}'.");
        }

        // Executa o método
        $controller->$action();
    }

    /**
     * Envia resposta 404 personalizada.
     */
    protected function send404(): void {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        echo "<!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <title>404 - Página Não Encontrada</title>
            <style>
                body { background-color: #0f172a; color: #f8fafc; font-family: sans-serif; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
                h1 { font-size: 48px; color: #e11d48; margin-bottom: 10px; }
                p { color: #94a3b8; font-size: 18px; margin-bottom: 20px; }
                a { color: #0d9488; text-decoration: none; border: 1px solid #0d9488; padding: 10px 20px; border-radius: 4px; transition: all 0.3s; }
                a:hover { background-color: #0d9488; color: #fff; }
            </style>
        </head>
        <body>
            <h1>404</h1>
            <p>A página que você está procurando não foi encontrada.</p>
            <a href='" . $this->basePath . "/'>Voltar ao Início</a>
        </body>
        </html>";
        exit;
    }
}
