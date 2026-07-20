<?php
// Front Controller - Sistema de Gestão Eleitoral (SGE)

// 1. Carrega e analisa o arquivo .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove aspas se presentes
            if (preg_match('/^"(.*)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }
            
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// 2. Configura tratamento de erros baseado no ambiente
$appEnv = $_ENV['APP_ENV'] ?? 'production';
if ($appEnv === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

// 3. Autoload PSR-4 básico para classes do namespace App\
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// 4. Inicializa Sessão Segura
\App\Core\Session::start();

// 5. Configuração de Roteamento
$router = new \App\Core\Router();

// Rotas de Autenticação
$router->get('/', 'AuthController@loginForm');
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Rota de Ativação (Convite)
$router->get('/ativar', 'ActivationController@activationForm');
$router->post('/ativar', 'ActivationController@activate');

// Painel de Administração
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/users', 'AdminController@users');
$router->post('/admin/users/invite', 'AdminController@invite');
$router->post('/admin/users/delete', 'AdminController@delete');
$router->post('/admin/users/activate-direct', 'AdminController@activateDirect');
$router->get('/admin/rbac', 'AdminController@rbac');
$router->post('/admin/rbac', 'AdminController@updateRbac');
$router->get('/admin/profile', 'AdminController@profile');
$router->post('/admin/profile', 'AdminController@updateProfile');

// Módulo Financeiro Administrativo
$router->get('/admin/financeiro', 'FinanceController@index');
$router->get('/admin/financeiro/fornecedores', 'FinanceController@suppliers');
$router->post('/admin/financeiro/fornecedores', 'FinanceController@addSupplier');
$router->get('/admin/financeiro/despesas', 'FinanceController@expenses');
$router->post('/admin/financeiro/despesas', 'FinanceController@addExpense');
$router->get('/admin/financeiro/fila', 'FinanceController@queue');
$router->post('/admin/financeiro/aprovar', 'FinanceController@approve');
$router->post('/admin/financeiro/rejeitar', 'FinanceController@reject');
$router->get('/admin/financeiro/comprovante', 'FinanceController@comprovante');
$router->get('/admin/financeiro/tipos-despesas', 'FinanceController@expenseTypes');
$router->post('/admin/financeiro/tipos-despesas', 'FinanceController@addExpenseType');
$router->post('/admin/financeiro/tipos-despesas/editar', 'FinanceController@editExpenseType');
$router->post('/admin/financeiro/tipos-despesas/excluir', 'FinanceController@deleteExpenseType');

// Módulo de RH e Gestão de Colaboradores
$router->get('/admin/rh', 'RhController@index');
$router->get('/admin/rh/novo', 'RhController@create');
$router->post('/admin/rh/novo', 'RhController@store');
$router->post('/admin/rh/dar-aval', 'RhController@darAval');
$router->post('/admin/rh/homologar', 'RhController@homologar');
$router->post('/admin/rh/enviar-whatsapp', 'RhController@enviarWhatsAppApi');
$router->get('/admin/rh/documento', 'RhController@documento');
$router->get('/admin/rh/contrato-pdf', 'RhController@contratoPdf');
$router->get('/admin/rh/consultar-tse', 'RhController@consultarTse');

// Rotas Públicas do Colaborador (Auto-cadastro e Contrato)
$router->get('/colaborador/cadastro', 'RhController@publicCadastro');
$router->post('/colaborador/cadastro', 'RhController@publicStore');
$router->get('/colaborador/contrato', 'RhController@publicContrato');
$router->get('/colaborador/contrato-pdf', 'RhController@publicContratoPdf');
$router->get('/colaborador/documento', 'RhController@publicDocumento');
$router->post('/colaborador/contrato/upload', 'RhController@uploadManualSignature');

// Portal do Colaborador de Campo / Militância (Mobile)
$router->get('/portal', 'PortalController@index');
$router->get('/portal/viagem', 'PortalController@travel');
$router->post('/portal/viagem', 'PortalController@addTravel');
$router->post('/portal/viagem/receipt', 'PortalController@addTravelReceipt');
$router->post('/portal/viagem/recibo', 'PortalController@addTravelReceipt');
$router->post('/portal/viagem/submit', 'PortalController@submitTravelReport');
$router->post('/portal/viagem/enviar', 'PortalController@submitTravelReport');
$router->get('/portal/militancia', 'PortalController@militancy');
$router->post('/portal/militancia', 'PortalController@addMilitancy');
$router->get('/portal/despesas', 'PortalController@expenses');
$router->post('/portal/despesas', 'PortalController@addExpense');

// 6. Despacha a rota solicitada
try {
    $router->dispatch();
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    if ($appEnv === 'development') {
        echo "<div style='background-color:#f8d7da;color:#721c24;padding:15px;border:1px solid #f5c6cb;margin:20px;font-family:sans-serif;'>";
        echo "<strong>Erro na Aplicação (Modo de Desenvolvimento):</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
        echo "<strong>Linha:</strong> " . $e->getLine() . " no arquivo " . $e->getFile() . "<br><br>";
        echo "<strong>Trace:</strong><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
        echo "Ocorreu um erro interno no servidor. Por favor, tente novamente mais tarde.";
    }
}
