<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Services\EncryptionService;
use PDO;
use Exception;

class FinanceController extends Controller {

    public function __construct() {
        $this->requireAuth();
        $user = $this->getLoggedUser();
        // Apenas Admin ou Financeiro podem acessar a área administrativa financeira
        if ($user['role_name'] !== 'ADMINISTRADOR' && $user['role_name'] !== 'FINANCEIRO') {
            Session::setFlash('error', 'Acesso restrito ao módulo financeiro.');
            if (($user['role_name'] ?? '') === 'COLABORADOR_CAMPO') {
                $this->redirect('/portal');
            } else {
                $this->redirect('/admin/dashboard');
            }
        }
    }

    /**
     * Dashboard Financeiro Geral (Receitas e Visão Geral de Contas)
     */
    public function index(): void {
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        // Se for requisição POST, trata o lançamento de doação (receita)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validatePostCsrf();
            try {
                $description = trim($_POST['description'] ?? '');
                $value = $this->parseBrlCurrency($_POST['value'] ?? '0');
                $date_received = $_POST['date_received'] ?? '';
                $bank_account_id = intval($_POST['bank_account_id'] ?? 0);
                $spce_category_id = intval($_POST['spce_category_id'] ?? 0);
                $donor_name = trim($_POST['donor_name'] ?? '');
                $donor_cpf = trim($_POST['donor_cpf'] ?? '');

                if (empty($description) || $value <= 0 || empty($date_received) || $bank_account_id <= 0 || $spce_category_id <= 0 || empty($donor_name) || empty($donor_cpf)) {
                    throw new Exception("Todos os campos de receita/doação são obrigatórios.");
                }

                $db->beginTransaction();

                // 1. Insere a receita
                $stmt = $db->prepare(
                    "INSERT INTO `receitas` (description, value, date_received, bank_account_id, spce_category_id, donor_name, donor_cpf) 
                     VALUES (:description, :value, :date_received, :bank_account_id, :spce_category_id, :donor_name, :donor_cpf)"
                );
                $stmt->execute([
                    'description' => $description,
                    'value' => $value,
                    'date_received' => $date_received,
                    'bank_account_id' => $bank_account_id,
                    'spce_category_id' => $spce_category_id,
                    'donor_name' => $donor_name,
                    'donor_cpf' => $donor_cpf
                ]);
                $receitaId = $db->lastInsertId();

                // 2. Atualiza o saldo da conta bancária
                $stmtBank = $db->prepare("UPDATE `bank_accounts` SET balance = balance + :value WHERE id = :id");
                $stmtBank->execute(['value' => $value, 'id' => $bank_account_id]);

                // 3. Registra log de auditoria
                $stmtLog = $db->prepare(
                    "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                     VALUES (:user_id, 'CREATE_REVENUE', 'receitas', :record_id, :new_values, :ip_address, :user_agent)"
                );
                $stmtLog->execute([
                    'user_id' => $user['id'],
                    'record_id' => $receitaId,
                    'new_values' => json_encode($_POST, JSON_UNESCAPED_UNICODE),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                ]);

                $db->commit();
                Session::setFlash('success', 'Receita/Doação registrada com sucesso e saldo atualizado!');
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                Session::setFlash('error', 'Erro ao registrar receita: ' . $e->getMessage());
            }
            $this->redirect('/admin/financeiro');
        }

        // Busca KPIs de Campanha
        // 1. Saldo total em caixa
        $totalBalance = floatval($db->query("SELECT SUM(balance) FROM `bank_accounts` WHERE status = 'ATIVA'")->fetchColumn());

        // 2. Saldo por tipo de recurso
        $fefcBalance = floatval($db->query("SELECT SUM(balance) FROM `bank_accounts` WHERE fund_type = 'FEFC' AND status = 'ATIVA'")->fetchColumn());
        $partidarioBalance = floatval($db->query("SELECT SUM(balance) FROM `bank_accounts` WHERE fund_type = 'FUNDO_PARTIDARIO' AND status = 'ATIVA'")->fetchColumn());
        $outrosBalance = floatval($db->query("SELECT SUM(balance) FROM `bank_accounts` WHERE fund_type = 'OUTROS_RECURSOS' AND status = 'ATIVA'")->fetchColumn());

        // 3. Limite de gastos da campanha (Exemplo: R$ 150.000,00)
        $spendingLimit = 150000.00;
        // Total gasto (soma de despesas aprovadas ou pagas)
        $totalSpent = floatval($db->query("SELECT SUM(value) FROM `despesas` WHERE status IN ('APROVADO', 'PAGO')")->fetchColumn());
        // Soma os recibos de viagens aprovados também
        $totalTravelSpent = floatval($db->query("SELECT SUM(value) FROM `travel_receipts` WHERE status = 'APROVADO'")->fetchColumn());
        $totalSpentCombined = $totalSpent + $totalTravelSpent;
        $limitPercentage = ($spendingLimit > 0) ? min(100, ($totalSpentCombined / $spendingLimit) * 100) : 0;

        // Lista de Contas Bancárias
        $bankAccounts = $db->query("SELECT * FROM `bank_accounts` ORDER BY name ASC")->fetchAll();

        // Lista de Categorias de Receita
        $receitaCategories = $db->query("SELECT * FROM `spce_categories` WHERE type = 'RECEITA' ORDER BY code ASC")->fetchAll();

        // Listagem de Receitas Recentes
        $stmtRevenues = $db->query(
            "SELECT r.*, b.name AS bank_name, c.code AS spce_code, c.description AS spce_desc 
             FROM `receitas` r 
             JOIN `bank_accounts` b ON r.bank_account_id = b.id 
             JOIN `spce_categories` c ON r.spce_category_id = c.id 
             ORDER BY r.date_received DESC, r.id DESC LIMIT 50"
        );
        $recentRevenues = $stmtRevenues->fetchAll();

        $this->render('admin/financeiro/index', [
            'user' => $user,
            'totalBalance' => $totalBalance,
            'fefcBalance' => $fefcBalance,
            'partidarioBalance' => $partidarioBalance,
            'outrosBalance' => $outrosBalance,
            'spendingLimit' => $spendingLimit,
            'totalSpentCombined' => $totalSpentCombined,
            'limitPercentage' => $limitPercentage,
            'bankAccounts' => $bankAccounts,
            'receitaCategories' => $receitaCategories,
            'recentRevenues' => $recentRevenues,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    /**
     * Cadastro e Listagem de Fornecedores
     */
    public function suppliers(): void {
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        // Listagem de Fornecedores
        $suppliers = $db->query("SELECT * FROM `suppliers` ORDER BY corporate_name ASC")->fetchAll();

        $this->render('admin/financeiro/fornecedores', [
            'user' => $user,
            'suppliers' => $suppliers,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    /**
     * Ação de Adicionar Fornecedor (POST)
     */
    public function addSupplier(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $cnpj_cpf = preg_replace('/[^0-9]/', '', $_POST['cnpj_cpf'] ?? '');
            $corporate_name = trim($_POST['corporate_name'] ?? '');
            $trade_name = trim($_POST['trade_name'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (empty($cnpj_cpf) || empty($corporate_name)) {
                throw new Exception("CNPJ/CPF e Razão Social são campos obrigatórios.");
            }

            // Validação simples de CPF/CNPJ
            if (strlen($cnpj_cpf) !== 11 && strlen($cnpj_cpf) !== 14) {
                throw new Exception("CNPJ/CPF inválido. Deve possuir 11 dígitos (CPF) ou 14 dígitos (CNPJ).");
            }

            // Verifica duplicidade
            $stmtCheck = $db->prepare("SELECT id FROM `suppliers` WHERE cnpj_cpf = :cnpj_cpf LIMIT 1");
            $stmtCheck->execute(['cnpj_cpf' => $_POST['cnpj_cpf']]);
            if ($stmtCheck->fetch()) {
                throw new Exception("Fornecedor com este CNPJ/CPF já cadastrado.");
            }

            $stmt = $db->prepare(
                "INSERT INTO `suppliers` (cnpj_cpf, corporate_name, trade_name, address, phone, email) 
                 VALUES (:cnpj_cpf, :corporate_name, :trade_name, :address, :phone, :email)"
            );
            $stmt->execute([
                'cnpj_cpf' => $_POST['cnpj_cpf'], // Mantemos formatado para exibição amigável
                'corporate_name' => $corporate_name,
                'trade_name' => empty($trade_name) ? null : $trade_name,
                'address' => empty($address) ? null : $address,
                'phone' => empty($phone) ? null : $phone,
                'email' => empty($email) ? null : $email
            ]);
            $supplierId = $db->lastInsertId();

            // Grava Log
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, ip_address, user_agent) 
                 VALUES (:user_id, 'CREATE_SUPPLIER', 'suppliers', :record_id, :ip_address, :user_agent)"
            );
            $stmtLog->execute([
                'user_id' => $user['id'],
                'record_id' => $supplierId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            Session::setFlash('success', 'Fornecedor cadastrado com sucesso!');
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao cadastrar fornecedor: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/fornecedores');
    }

    /**
     * Cadastro e Listagem de Despesas Gerais
     */
    public function expenses(): void {
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        // Busca fornecedores ativos
        $suppliers = $db->query("SELECT id, corporate_name, cnpj_cpf FROM `suppliers` WHERE status = 'ATIVO' ORDER BY corporate_name ASC")->fetchAll();
        
        // Busca contas bancárias ativas
        $bankAccounts = $db->query("SELECT id, name, fund_type, balance FROM `bank_accounts` WHERE status = 'ATIVA' ORDER BY name ASC")->fetchAll();

        // Busca categorias de despesas SPCE
        $spceCategories = $db->query("SELECT id, code, description FROM `spce_categories` WHERE type = 'DESPESA' ORDER BY code ASC")->fetchAll();

        // Listagem de despesas gerais
        $stmtExpenses = $db->query(
            "SELECT d.*, s.corporate_name AS supplier_name, b.name AS bank_name, c.code AS spce_code, 
                    c.description AS spce_desc, u.name AS creator_name, cc.id AS doc_id
             FROM `despesas` d
             JOIN `suppliers` s ON d.supplier_id = s.id
             JOIN `bank_accounts` b ON d.bank_account_id = b.id
             JOIN `spce_categories` c ON d.spce_category_id = c.id
             JOIN `usuarios` u ON d.user_id = u.id
             LEFT JOIN `comprovantes_cripto` cc ON d.id = cc.expense_id
             ORDER BY d.date_incurred DESC, d.id DESC LIMIT 100"
        );
        $expenses = $stmtExpenses->fetchAll();

        $this->render('admin/financeiro/despesas', [
            'user' => $user,
            'suppliers' => $suppliers,
            'bankAccounts' => $bankAccounts,
            'spceCategories' => $spceCategories,
            'expenses' => $expenses,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    /**
     * Lançamento de Despesa Geral (POST)
     */
    public function addExpense(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $description = trim($_POST['description'] ?? '');
            $supplier_id = intval($_POST['supplier_id'] ?? 0);
            $bank_account_id = intval($_POST['bank_account_id'] ?? 0);
            $value = $this->parseBrlCurrency($_POST['value'] ?? '0');
            $date_incurred = $_POST['date_incurred'] ?? '';
            $payment_method = trim($_POST['payment_method'] ?? '');
            $spce_category_id = intval($_POST['spce_category_id'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            $mark_as_paid = isset($_POST['mark_as_paid']) ? 1 : 0;

            if (empty($description) || $supplier_id <= 0 || $bank_account_id <= 0 || $value <= 0 || empty($date_incurred) || empty($payment_method) || $spce_category_id <= 0) {
                throw new Exception("Todos os campos principais da despesa são obrigatórios.");
            }

            // Normaliza upload de comprovante(s) fiscal(is) (1 ou mais arquivos)
            $filesList = [];
            if (isset($_FILES['comprovante'])) {
                if (is_array($_FILES['comprovante']['name'])) {
                    foreach ($_FILES['comprovante']['name'] as $idx => $fname) {
                        if ($_FILES['comprovante']['error'][$idx] === UPLOAD_ERR_OK && !empty($_FILES['comprovante']['tmp_name'][$idx])) {
                            $filesList[] = [
                                'name' => $fname,
                                'type' => $_FILES['comprovante']['type'][$idx],
                                'tmp_name' => $_FILES['comprovante']['tmp_name'][$idx],
                                'error' => $_FILES['comprovante']['error'][$idx],
                                'size' => $_FILES['comprovante']['size'][$idx]
                            ];
                        }
                    }
                } elseif ($_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
                    $filesList[] = $_FILES['comprovante'];
                }
            }

            if (empty($filesList)) {
                throw new Exception("O upload de pelo menos um comprovante fiscal é obrigatório.");
            }

            $db->beginTransaction();

            $status = $mark_as_paid ? 'PAGO' : 'PENDENTE';

            // 1. Grava a despesa
            $stmt = $db->prepare(
                "INSERT INTO `despesas` (description, supplier_id, bank_account_id, value, date_incurred, payment_method, status, spce_category_id, user_id, notes) 
                 VALUES (:description, :supplier_id, :bank_account_id, :value, :date_incurred, :payment_method, :status, :spce_category_id, :user_id, :notes)"
            );
            $stmt->execute([
                'description' => $description,
                'supplier_id' => $supplier_id,
                'bank_account_id' => $bank_account_id,
                'value' => $value,
                'date_incurred' => $date_incurred,
                'payment_method' => $payment_method,
                'status' => $status,
                'spce_category_id' => $spce_category_id,
                'user_id' => $user['id'],
                'notes' => empty($notes) ? null : $notes
            ]);
            $expenseId = $db->lastInsertId();

            // 2. Criptografa e salva todas as fotos dos comprovantes físicos
            $storageDir = dirname(__DIR__, 2) . '/storage/uploads';
            foreach ($filesList as $singleFile) {
                $cryptoData = EncryptionService::encryptAndSaveUploadedFile($singleFile, $storageDir);

                $stmtCripto = $db->prepare(
                    "INSERT INTO `comprovantes_cripto` (expense_id, encrypted_file_path, original_name, iv, mime_type) 
                     VALUES (:expense_id, :encrypted_file_path, :original_name, :iv, :mime_type)"
                );
                $stmtCripto->execute([
                    'expense_id' => $expenseId,
                    'encrypted_file_path' => $cryptoData['encrypted_file_path'],
                    'original_name' => $cryptoData['original_name'],
                    'iv' => $cryptoData['iv'],
                    'mime_type' => $cryptoData['mime_type']
                ]);
            }

            // 3. Se foi marcado como Pago, atualiza saldo da conta correspondente
            if ($status === 'PAGO') {
                // Checa saldo antes
                $stmtCheck = $db->prepare("SELECT balance FROM `bank_accounts` WHERE id = :id");
                $stmtCheck->execute(['id' => $bank_account_id]);
                $currBalance = floatval($stmtCheck->fetchColumn());
                
                // Nós permitimos saldo negativo para a campanha se necessário, mas lançamos alerta
                $stmtBank = $db->prepare("UPDATE `bank_accounts` SET balance = balance - :value WHERE id = :id");
                $stmtBank->execute(['value' => $value, 'id' => $bank_account_id]);
            }

            // Grava Log de Auditoria
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (:user_id, 'CREATE_EXPENSE', 'despesas', :record_id, :new_values, :ip_address, :user_agent)"
            );
            $stmtLog->execute([
                'user_id' => $user['id'],
                'record_id' => $expenseId,
                'new_values' => json_encode(['description' => $description, 'value' => $value, 'status' => $status], JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            $db->commit();
            Session::setFlash('success', 'Despesa lançada com comprovante criptografado com sucesso!');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Session::setFlash('error', 'Erro ao lançar despesa: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/despesas');
    }

    /**
     * Fila de Aprovação Financeira / Fiscal (Viagens e Militância)
     */
    public function queue(): void {
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        // 1. Despesas gerais pendentes de aprovação (usando LEFT JOIN para contas, categorias e tipos de despesa)
        $stmtExpenses = $db->query(
            "SELECT d.*, s.corporate_name AS supplier_name, b.name AS bank_name, c.code AS spce_code, 
                    c.description AS spce_desc, u.name AS creator_name, cc.id AS doc_id, et.name AS expense_type_name
             FROM `despesas` d
             JOIN `suppliers` s ON d.supplier_id = s.id
             LEFT JOIN `bank_accounts` b ON d.bank_account_id = b.id
             LEFT JOIN `spce_categories` c ON d.spce_category_id = c.id
             JOIN `usuarios` u ON d.user_id = u.id
             LEFT JOIN `comprovantes_cripto` cc ON d.id = cc.expense_id
             LEFT JOIN `expense_types` et ON d.expense_type_id = et.id
             WHERE d.status = 'PENDENTE'
             ORDER BY d.date_incurred ASC, d.id ASC"
        );
        $pendingExpenses = $stmtExpenses->fetchAll();

        // 2. Relatórios de viagens pendentes de aprovação (status = 'ENVIADO')
        $stmtTravels = $db->query(
            "SELECT tr.*, u.name AS user_name, u.celular 
             FROM `travel_reports` tr 
             JOIN `usuarios` u ON tr.user_id = u.id 
             WHERE tr.status = 'ENVIADO' 
             ORDER BY tr.start_date ASC"
        );
        $pendingTravels = $stmtTravels->fetchAll();

        // Buscaremos os recibos de cada viagem pendente
        $travelsWithReceipts = [];
        foreach ($pendingTravels as $tr) {
            $stmtReceipts = $db->prepare(
                "SELECT r.*, c.code AS spce_code, c.description AS spce_desc 
                 FROM `travel_receipts` r 
                 JOIN `spce_categories` c ON r.spce_category_id = c.id 
                 WHERE r.travel_report_id = :id"
            );
            $stmtReceipts->execute(['id' => $tr['id']]);
            $receipts = $stmtReceipts->fetchAll();
            
            $tr['receipts'] = $receipts;
            $travelsWithReceipts[] = $tr;
        }

        // 3. Atividades de panfletagem / militância pendentes (status = 'PENDENTE')
        $stmtMilitancy = $db->query(
            "SELECT ma.*, u.name AS user_name 
             FROM `militancy_activities` ma 
             JOIN `usuarios` u ON ma.user_id = u.id 
             WHERE ma.status = 'PENDENTE' 
             ORDER BY ma.activity_date ASC"
        );
        $pendingMilitancy = $stmtMilitancy->fetchAll();

        // Contas bancárias e categorias para a vinculação (admin)
        $bankAccounts = $db->query("SELECT id, name, fund_type, balance FROM `bank_accounts` WHERE status = 'ATIVA' ORDER BY name ASC")->fetchAll();
        $spceCategories = $db->query("SELECT id, code, description FROM `spce_categories` WHERE type = 'DESPESA' ORDER BY code ASC")->fetchAll();

        $this->render('admin/financeiro/fila', [
            'user' => $user,
            'pendingExpenses' => $pendingExpenses,
            'pendingTravels' => $travelsWithReceipts,
            'pendingMilitancy' => $pendingMilitancy,
            'bankAccounts' => $bankAccounts,
            'spceCategories' => $spceCategories,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    /**
     * Ação de Aprovação (POST)
     */
    public function approve(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $type = $_POST['type'] ?? '';
            $id = intval($_POST['id'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');

            if ($id <= 0 || !in_array($type, ['expense', 'travel', 'militancy'])) {
                throw new Exception("Parâmetros inválidos para aprovação.");
            }

            $db->beginTransaction();

            if ($type === 'expense') {
                // Aprova despesa geral e muda status para PAGO (diminuindo saldo)
                $stmtGet = $db->prepare("SELECT bank_account_id, value, status FROM `despesas` WHERE id = :id LIMIT 1");
                $stmtGet->execute(['id' => $id]);
                $exp = $stmtGet->fetch();

                if (!$exp) throw new Exception("Despesa não encontrada.");
                if ($exp['status'] !== 'PENDENTE') throw new Exception("Esta despesa já foi processada.");

                $bankAccountId = $exp['bank_account_id'];

                // Se a despesa veio de colaborador de campo, precisamos vincular conta e categoria
                if (empty($bankAccountId)) {
                    $bankAccountId = intval($_POST['bank_account_id'] ?? 0);
                    $spceCategoryId = intval($_POST['spce_category_id'] ?? 0);

                    if ($bankAccountId <= 0 || $spceCategoryId <= 0) {
                        throw new Exception("É necessário selecionar a Conta Bancária e Categoria SPCE antes de aprovar uma despesa de campo.");
                    }

                    // Atualiza a despesa com as vinculações
                    $stmtUpdateLink = $db->prepare(
                        "UPDATE `despesas` SET bank_account_id = :bank_account_id, spce_category_id = :spce_category_id WHERE id = :id"
                    );
                    $stmtUpdateLink->execute([
                        'bank_account_id' => $bankAccountId,
                        'spce_category_id' => $spceCategoryId,
                        'id' => $id
                    ]);
                }

                // Atualiza despesa para paga
                $stmtUpdate = $db->prepare(
                    "UPDATE `despesas` SET status = 'PAGO', approved_by = :approved_by, approved_at = NOW(), notes = :notes WHERE id = :id"
                );
                $stmtUpdate->execute([
                    'approved_by' => $user['id'],
                    'notes' => empty($notes) ? null : $notes,
                    'id' => $id
                ]);

                // Desconta do saldo da conta bancária
                $stmtBank = $db->prepare("UPDATE `bank_accounts` SET balance = balance - :value WHERE id = :id");
                $stmtBank->execute(['value' => $exp['value'], 'id' => $bankAccountId]);

            } elseif ($type === 'travel') {
                // Aprova relatório de viagem e seus recibos
                $stmtUpdate = $db->prepare(
                    "UPDATE `travel_reports` SET status = 'APROVADO', approved_by = :approved_by, approved_at = NOW() WHERE id = :id"
                );
                $stmtUpdate->execute(['approved_by' => $user['id'], 'id' => $id]);

                // Atualiza todos os recibos atrelados
                $stmtReceipts = $db->prepare("UPDATE `travel_receipts` SET status = 'APROVADO' WHERE travel_report_id = :id");
                $stmtReceipts->execute(['id' => $id]);

            } elseif ($type === 'militancy') {
                // Aprova atividade de militância
                $stmtUpdate = $db->prepare("UPDATE `militancy_activities` SET status = 'APROVADO' WHERE id = :id");
                $stmtUpdate->execute(['id' => $id]);
            }

            // Grava Log
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (:user_id, :action, :table_name, :record_id, :new_values, :ip_address, :user_agent)"
            );
            $actionStr = 'APPROVE_' . strtoupper($type);
            $tableName = ($type === 'expense') ? 'despesas' : (($type === 'travel') ? 'travel_reports' : 'militancy_activities');
            
            $stmtLog->execute([
                'user_id' => $user['id'],
                'action' => $actionStr,
                'table_name' => $tableName,
                'record_id' => $id,
                'new_values' => json_encode(['notes' => $notes], JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            $db->commit();
            Session::setFlash('success', 'Aprovação realizada com sucesso!');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Session::setFlash('error', 'Erro ao aprovar: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/fila');
    }

    /**
     * Ação de Rejeição (POST)
     */
    public function reject(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $type = $_POST['type'] ?? '';
            $id = intval($_POST['id'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');

            if ($id <= 0 || !in_array($type, ['expense', 'travel', 'militancy'])) {
                throw new Exception("Parâmetros inválidos para rejeição.");
            }

            if (empty($notes)) {
                throw new Exception("É necessário fornecer uma justificativa/observação para a rejeição.");
            }

            $db->beginTransaction();

            if ($type === 'expense') {
                $stmtUpdate = $db->prepare(
                    "UPDATE `despesas` SET status = 'REJEITADO', approved_by = :approved_by, approved_at = NOW(), notes = :notes WHERE id = :id"
                );
                $stmtUpdate->execute([
                    'approved_by' => $user['id'],
                    'notes' => $notes,
                    'id' => $id
                ]);
            } elseif ($type === 'travel') {
                $stmtUpdate = $db->prepare(
                    "UPDATE `travel_reports` SET status = 'REJEITADO', approved_by = :approved_by, approved_at = NOW() WHERE id = :id"
                );
                $stmtUpdate->execute(['approved_by' => $user['id'], 'id' => $id]);

                // Atualiza todos os recibos atrelados
                $stmtReceipts = $db->prepare("UPDATE `travel_receipts` SET status = 'REJEITADO', notes = :notes WHERE travel_report_id = :id");
                $stmtReceipts->execute(['notes' => $notes, 'id' => $id]);
            } elseif ($type === 'militancy') {
                $stmtUpdate = $db->prepare("UPDATE `militancy_activities` SET status = 'REJEITADO' WHERE id = :id");
                $stmtUpdate->execute(['id' => $id]);
            }

            // Log
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (:user_id, :action, :table_name, :record_id, :new_values, :ip_address, :user_agent)"
            );
            $actionStr = 'REJECT_' . strtoupper($type);
            $tableName = ($type === 'expense') ? 'despesas' : (($type === 'travel') ? 'travel_reports' : 'militancy_activities');

            $stmtLog->execute([
                'user_id' => $user['id'],
                'action' => $actionStr,
                'table_name' => $tableName,
                'record_id' => $id,
                'new_values' => json_encode(['notes' => $notes], JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            $db->commit();
            Session::setFlash('success', 'Solicitação rejeitada com sucesso.');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Session::setFlash('error', 'Erro ao rejeitar: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/fila');
    }

    /**
     * Rota de Descriptografia de Comprovante em Tempo de Execução
     * GET /admin/financeiro/comprovante?id=...&type=expense|travel|militancy
     */
    public function comprovante(): void {
        $db = Database::getInstance();

        try {
            $id = intval($_GET['id'] ?? 0);
            $type = $_GET['type'] ?? 'expense';

            if ($id <= 0) {
                throw new Exception("ID inválido.");
            }

            $storageDir = dirname(__DIR__, 2) . '/storage/uploads/';
            $filePath = '';
            $iv = '';
            $mimeType = '';
            $originalName = '';

            if ($type === 'expense') {
                $stmt = $db->prepare("SELECT * FROM `comprovantes_cripto` WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $doc = $stmt->fetch();
                if (!$doc) throw new Exception("Documento não encontrado.");

                $filePath = $storageDir . $doc['encrypted_file_path'];
                $iv = $doc['iv'];
                $mimeType = $doc['mime_type'];
                $originalName = $doc['original_name'];

            } elseif ($type === 'travel') {
                $stmt = $db->prepare("SELECT * FROM `travel_receipts` WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $doc = $stmt->fetch();
                if (!$doc) throw new Exception("Recibo de viagem não encontrado.");

                $filePath = $storageDir . $doc['encrypted_file_path'];
                $iv = $doc['iv'];
                $mimeType = 'image/jpeg'; // fotos de celular são jpeg/png por padrão
                $originalName = basename($doc['encrypted_file_path']) . '.jpg';

            } elseif ($type === 'militancy') {
                $stmt = $db->prepare("SELECT * FROM `militancy_activities` WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $doc = $stmt->fetch();
                if (!$doc) throw new Exception("Atividade de panfletagem não encontrada.");

                $filePath = $storageDir . $doc['encrypted_photo_path'];
                $iv = $doc['iv'];
                $mimeType = 'image/jpeg';
                $originalName = basename($doc['encrypted_photo_path']) . '.jpg';
            }

            // Descriptografa o conteúdo
            $decryptedContent = EncryptionService::readAndDecryptFile($filePath, $iv);

            // Limpa buffers anteriores
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Envia cabeçalhos HTTP apropriados
            header("Content-Type: " . $mimeType);
            header("Content-Disposition: inline; filename=\"" . rawurlencode($originalName) . "\"");
            header("Content-Length: " . strlen($decryptedContent));
            header("Cache-Control: private, max-age=86400");

            echo $decryptedContent;
            exit;

        } catch (Exception $e) {
            http_response_code(404);
            echo "Erro ao carregar comprovante descriptografado: " . htmlspecialchars($e->getMessage());
            exit;
        }
    }

    public function expenseTypes(): void {
        $db = Database::getInstance();
        $user = $this->getLoggedUser();
        
        $types = $db->query("SELECT * FROM `expense_types` ORDER BY name ASC")->fetchAll();
        
        $this->render('admin/financeiro/tipos_despesas', [
            'user' => $user,
            'types' => $types,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    public function addExpenseType(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            Session::setFlash('error', 'O nome do tipo de despesa é obrigatório.');
            $this->redirect('admin/financeiro/tipos-despesas');
        }
        
        $stmt = $db->prepare("SELECT id FROM `expense_types` WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        if ($stmt->fetch()) {
            Session::setFlash('error', 'Já existe um tipo de despesa com este nome.');
            $this->redirect('admin/financeiro/tipos-despesas');
        }
        
        $stmtInsert = $db->prepare("INSERT INTO `expense_types` (name, description) VALUES (:name, :description)");
        $stmtInsert->execute(['name' => $name, 'description' => $description]);
        
        $newId = $db->lastInsertId();
        
        \App\Services\AuditLogger::log('ADD_EXPENSE_TYPE', 'expense_types', $newId, null, [
            'name' => $name,
            'description' => $description
        ]);
        
        Session::setFlash('success', 'Tipo de despesa cadastrado com sucesso!');
        $this->redirect('admin/financeiro/tipos-despesas');
    }

    public function editExpenseType(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name) || $id <= 0) {
            Session::setFlash('error', 'Dados inválidos para edição.');
            $this->redirect('admin/financeiro/tipos-despesas');
        }
        
        $stmtOld = $db->prepare("SELECT * FROM `expense_types` WHERE id = :id LIMIT 1");
        $stmtOld->execute(['id' => $id]);
        $old = $stmtOld->fetch();
        if (!$old) {
            Session::setFlash('error', 'Tipo de despesa não encontrado.');
            $this->redirect('admin/financeiro/tipos-despesas');
        }
        
        if ($old['name'] !== $name) {
            $stmt = $db->prepare("SELECT id FROM `expense_types` WHERE name = :name LIMIT 1");
            $stmt->execute(['name' => $name]);
            if ($stmt->fetch()) {
                Session::setFlash('error', 'Já existe outro tipo de despesa com este nome.');
                $this->redirect('admin/financeiro/tipos-despesas');
            }
        }
        
        $stmtUpdate = $db->prepare("UPDATE `expense_types` SET name = :name, description = :description WHERE id = :id");
        $stmtUpdate->execute(['name' => $name, 'description' => $description, 'id' => $id]);
        
        \App\Services\AuditLogger::log('EDIT_EXPENSE_TYPE', 'expense_types', $id, $old, [
            'name' => $name,
            'description' => $description
        ]);
        
        Session::setFlash('success', 'Tipo de despesa atualizado com sucesso!');
        $this->redirect('admin/financeiro/tipos-despesas');
    }

    public function deleteExpenseType(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            Session::setFlash('error', 'ID inválido para exclusão.');
            $this->redirect('admin/financeiro/tipos-despesas');
        }
        
        $stmtOld = $db->prepare("SELECT * FROM `expense_types` WHERE id = :id LIMIT 1");
        $stmtOld->execute(['id' => $id]);
        $old = $stmtOld->fetch();
        if (!$old) {
            Session::setFlash('error', 'Tipo de despesa não encontrado.');
            $this->redirect('admin/financeiro/tipos-despesas');
        }
        
        $stmtDelete = $db->prepare("DELETE FROM `expense_types` WHERE id = :id");
        $stmtDelete->execute(['id' => $id]);
        
        \App\Services\AuditLogger::log('DELETE_EXPENSE_TYPE', 'expense_types', $id, $old, null);
        
        Session::setFlash('success', 'Tipo de despesa excluído com sucesso!');
        $this->redirect('admin/financeiro/tipos-despesas');
    }
}
