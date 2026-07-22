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
        $totalBalance = floatval($db->query("SELECT COALESCE(SUM(balance), 0) FROM `bank_accounts` WHERE status = 'ATIVA'")->fetchColumn());

        // 2. Saldo por tipo de recurso
        $fefcBalance = floatval($db->query("SELECT COALESCE(SUM(balance), 0) FROM `bank_accounts` WHERE fund_type = 'FEFC' AND status = 'ATIVA'")->fetchColumn());
        $partidarioBalance = floatval($db->query("SELECT COALESCE(SUM(balance), 0) FROM `bank_accounts` WHERE fund_type = 'FUNDO_PARTIDARIO' AND status = 'ATIVA'")->fetchColumn());
        $outrosBalance = floatval($db->query("SELECT COALESCE(SUM(balance), 0) FROM `bank_accounts` WHERE fund_type = 'OUTROS_RECURSOS' AND status = 'ATIVA'")->fetchColumn());

        // 3. Busca Configurações da Campanha (Cargo, UF, Limite de Gastos)
        $campaignSettings = $db->query("SELECT * FROM `campaign_settings` WHERE id = 1")->fetch();
        $spendingLimit = floatval($campaignSettings['spending_limit'] ?? 3168878.60);
        $electoralRole = $campaignSettings['electoral_role'] ?? 'Deputado Federal';
        $uf = $campaignSettings['uf'] ?? 'DF';

        // Total gasto (soma de despesas aprovadas ou pagas)
        $totalSpent = floatval($db->query("SELECT COALESCE(SUM(value), 0) FROM `despesas` WHERE status IN ('APROVADO', 'PAGO')")->fetchColumn());
        // Soma os recibos de viagens aprovados também
        $totalTravelSpent = floatval($db->query("SELECT COALESCE(SUM(value), 0) FROM `travel_receipts` WHERE status = 'APROVADO'")->fetchColumn());
        $totalSpentCombined = $totalSpent + $totalTravelSpent;
        $limitPercentage = ($spendingLimit > 0) ? min(100, round(($totalSpentCombined / $spendingLimit) * 100, 2)) : 0;

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
            'campaignSettings' => $campaignSettings,
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
     * Ação de Editar Fornecedor (POST)
     */
    public function updateSupplier(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $id = (int)($_POST['supplier_id'] ?? 0);
            $cnpj_cpf = preg_replace('/[^0-9]/', '', $_POST['cnpj_cpf'] ?? '');
            $corporate_name = trim($_POST['corporate_name'] ?? '');
            $trade_name = trim($_POST['trade_name'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $status = trim($_POST['status'] ?? 'ATIVO');

            if ($id <= 0) {
                throw new Exception("ID do fornecedor inválido.");
            }

            if (empty($cnpj_cpf) || empty($corporate_name)) {
                throw new Exception("CNPJ/CPF e Razão Social são campos obrigatórios.");
            }

            // Validação simples de CPF/CNPJ
            if (strlen($cnpj_cpf) !== 11 && strlen($cnpj_cpf) !== 14) {
                throw new Exception("CNPJ/CPF inválido. Deve possuir 11 dígitos (CPF) ou 14 dígitos (CNPJ).");
            }

            // Verifica se o fornecedor existe
            $stmtCheckExist = $db->prepare("SELECT id FROM `suppliers` WHERE id = :id LIMIT 1");
            $stmtCheckExist->execute(['id' => $id]);
            if (!$stmtCheckExist->fetch()) {
                throw new Exception("Fornecedor não encontrado.");
            }

            // Verifica duplicidade (excluindo o próprio ID)
            $stmtCheck = $db->prepare("SELECT id FROM `suppliers` WHERE cnpj_cpf = :cnpj_cpf AND id != :id LIMIT 1");
            $stmtCheck->execute(['cnpj_cpf' => $_POST['cnpj_cpf'], 'id' => $id]);
            if ($stmtCheck->fetch()) {
                throw new Exception("Fornecedor com este CNPJ/CPF já cadastrado.");
            }

            $stmt = $db->prepare(
                "UPDATE `suppliers` SET 
                    cnpj_cpf = :cnpj_cpf, 
                    corporate_name = :corporate_name, 
                    trade_name = :trade_name, 
                    address = :address, 
                    phone = :phone, 
                    email = :email,
                    status = :status
                 WHERE id = :id"
            );
            $stmt->execute([
                'cnpj_cpf' => $_POST['cnpj_cpf'], // Mantemos formatado para exibição amigável
                'corporate_name' => $corporate_name,
                'trade_name' => empty($trade_name) ? null : $trade_name,
                'address' => empty($address) ? null : $address,
                'phone' => empty($phone) ? null : $phone,
                'email' => empty($email) ? null : $email,
                'status' => $status,
                'id' => $id
            ]);

            // Grava Log
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, ip_address, user_agent) 
                 VALUES (:user_id, 'UPDATE_SUPPLIER', 'suppliers', :record_id, :ip_address, :user_agent)"
            );
            $stmtLog->execute([
                'user_id' => $user['id'],
                'record_id' => $id,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            Session::setFlash('success', 'Fornecedor atualizado com sucesso!');
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao atualizar fornecedor: ' . $e->getMessage());
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
            "SELECT d.*, 
                    COALESCE(s.corporate_name, 'Fornecedor Desconhecido') AS supplier_name, 
                    s.cnpj_cpf AS supplier_cnpj_cpf, 
                    COALESCE(b.name, 'Não informada') AS bank_name, 
                    c.code AS spce_code, 
                    c.description AS spce_desc, 
                    COALESCE(u.name, 'Colaborador de Campo') AS creator_name, 
                    cc.id AS doc_id, 
                    et.name AS expense_type_name
             FROM `despesas` d
             LEFT JOIN `suppliers` s ON d.supplier_id = s.id
             LEFT JOIN `bank_accounts` b ON d.bank_account_id = b.id
             LEFT JOIN `spce_categories` c ON d.spce_category_id = c.id
             LEFT JOIN `usuarios` u ON d.user_id = u.id
             LEFT JOIN (SELECT expense_id, MIN(id) AS id FROM `comprovantes_cripto` GROUP BY expense_id) cc ON d.id = cc.expense_id
             LEFT JOIN `expense_types` et ON d.expense_type_id = et.id
             ORDER BY d.date_incurred DESC, d.id DESC LIMIT 150"
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

            // Collect all uploaded file items from $_FILES['comprovante'], $_FILES['foto_cnpj_ocr'], and $_FILES['fotos_adicionais']
            $allFiles = [];
            $checkKeys = ['comprovante', 'foto_cnpj_ocr', 'fotos_adicionais'];
            foreach ($checkKeys as $key) {
                if (isset($_FILES[$key])) {
                    if (is_array($_FILES[$key]['name'])) {
                        foreach ($_FILES[$key]['name'] as $idx => $fname) {
                            if (isset($_FILES[$key]['error'][$idx]) && $_FILES[$key]['error'][$idx] === UPLOAD_ERR_OK && !empty($_FILES[$key]['tmp_name'][$idx])) {
                                $allFiles[] = [
                                    'name' => $fname,
                                    'type' => $_FILES[$key]['type'][$idx],
                                    'tmp_name' => $_FILES[$key]['tmp_name'][$idx],
                                    'error' => $_FILES[$key]['error'][$idx],
                                    'size' => $_FILES[$key]['size'][$idx]
                                ];
                            }
                        }
                    } elseif ($_FILES[$key]['error'] === UPLOAD_ERR_OK && !empty($_FILES[$key]['tmp_name'])) {
                        $allFiles[] = $_FILES[$key];
                    }
                }
            }

            if (empty($allFiles)) {
                throw new Exception("O upload da foto do comprovante é obrigatório.");
            }

            $mainFile = $allFiles[0];

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

            // 2. Criptografa e salva a foto principal
            $storageDir = dirname(__DIR__, 2) . '/storage/uploads';
            $mainCryptoData = EncryptionService::encryptAndSaveUploadedFile($mainFile, $storageDir);

            $stmtCripto = $db->prepare(
                "INSERT INTO `comprovantes_cripto` (expense_id, encrypted_file_path, original_name, iv, mime_type) 
                 VALUES (:expense_id, :encrypted_file_path, :original_name, :iv, :mime_type)"
            );
            $stmtCripto->execute([
                'expense_id' => $expenseId,
                'encrypted_file_path' => $mainCryptoData['encrypted_file_path'],
                'original_name' => $mainCryptoData['original_name'],
                'iv' => $mainCryptoData['iv'],
                'mime_type' => $mainCryptoData['mime_type']
            ]);

            // 3. Processa fotos adicionais enviadas (a partir do 2º arquivo)
            for ($i = 1; $i < count($allFiles); $i++) {
                $extraFile = $allFiles[$i];
                $extraCrypto = EncryptionService::encryptAndSaveUploadedFile($extraFile, $storageDir);
                $stmtCripto->execute([
                    'expense_id' => $expenseId,
                    'encrypted_file_path' => $extraCrypto['encrypted_file_path'],
                    'original_name' => $extraCrypto['original_name'],
                    'iv' => $extraCrypto['iv'],
                    'mime_type' => $extraCrypto['mime_type']
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
            $this->redirect('/admin/financeiro/despesas?envio_sucesso=1');
            return;
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

        // 1. Despesas gerais pendentes (status = 'PENDENTE')
        $stmtExpenses = $db->query(
            "SELECT d.*, COALESCE(s.corporate_name, 'Fornecedor Desconhecido') AS supplier_name, s.cnpj_cpf AS supplier_cnpj_cpf, 
                    b.name AS bank_name, c.code AS spce_code, c.description AS spce_desc, 
                    COALESCE(u.name, 'Colaborador de Campo') AS creator_name, cc.doc_id, et.name AS expense_type_name
             FROM `despesas` d
             LEFT JOIN `suppliers` s ON d.supplier_id = s.id
             LEFT JOIN `bank_accounts` b ON d.bank_account_id = b.id
             LEFT JOIN `spce_categories` c ON d.spce_category_id = c.id
             LEFT JOIN `usuarios` u ON d.user_id = u.id
             LEFT JOIN (SELECT expense_id, MIN(id) AS doc_id FROM `comprovantes_cripto` GROUP BY expense_id) cc ON d.id = cc.expense_id
             LEFT JOIN `expense_types` et ON d.expense_type_id = et.id
             WHERE d.status = 'PENDENTE'
             ORDER BY d.date_incurred ASC, d.id ASC"
        );
        $pendingExpenses = $stmtExpenses->fetchAll();

        // 2. Relatórios de viagens pendentes de aprovação (status = 'ENVIADO')
        $stmtTravels = $db->query(
            "SELECT tr.*, COALESCE(u.name, 'Colaborador de Campo') AS user_name, COALESCE(u.celular, 'Não informado') AS celular 
             FROM `travel_reports` tr 
             LEFT JOIN `usuarios` u ON tr.user_id = u.id 
             WHERE tr.status = 'ENVIADO' 
             ORDER BY tr.start_date ASC, tr.id ASC"
        );
        $pendingTravels = $stmtTravels->fetchAll();

        // Buscaremos os recibos de cada viagem pendente
        $travelsWithReceipts = [];
        foreach ($pendingTravels as $tr) {
            $stmtReceipts = $db->prepare(
                "SELECT r.*, c.code AS spce_code, c.description AS spce_desc 
                 FROM `travel_receipts` r 
                 LEFT JOIN `spce_categories` c ON r.spce_category_id = c.id 
                 WHERE r.travel_report_id = :id"
            );
            $stmtReceipts->execute(['id' => $tr['id']]);
            $receipts = $stmtReceipts->fetchAll();
            
            $tr['receipts'] = $receipts;
            $travelsWithReceipts[] = $tr;
        }

        // 3. Atividades de panfletagem / militância pendentes (status = 'PENDENTE')
        $stmtMilitancy = $db->query(
            "SELECT ma.*, COALESCE(u.name, 'Militante de Campo') AS user_name 
             FROM `militancy_activities` ma 
             LEFT JOIN `usuarios` u ON ma.user_id = u.id 
             WHERE ma.status = 'PENDENTE' 
             ORDER BY ma.activity_date ASC, ma.id ASC"
        );
        $pendingMilitancy = $stmtMilitancy->fetchAll();

        // Contas bancárias, tipos e categorias para a vinculação/edição (admin)
        $bankAccounts = $db->query("SELECT id, name, fund_type, balance FROM `bank_accounts` WHERE status = 'ATIVA' ORDER BY name ASC")->fetchAll();
        $spceCategories = $db->query("SELECT id, code, description FROM `spce_categories` WHERE type = 'DESPESA' ORDER BY code ASC")->fetchAll();
        $expenseTypes = $db->query("SELECT * FROM `expense_types` ORDER BY name ASC")->fetchAll();

        $this->render('admin/financeiro/fila', [
            'user' => $user,
            'pendingExpenses' => $pendingExpenses,
            'pendingTravels' => $travelsWithReceipts,
            'pendingMilitancy' => $pendingMilitancy,
            'bankAccounts' => $bankAccounts,
            'spceCategories' => $spceCategories,
            'expenseTypes' => $expenseTypes,
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
                $bankAccountId = intval($_POST['bank_account_id'] ?? 0);
                $spceCategoryId = intval($_POST['spce_category_id'] ?? 0);

                if ($bankAccountId <= 0) {
                    throw new Exception("É necessário selecionar a Conta Bancária Origem antes de aprovar o Relatório de Viagem/Combustível.");
                }

                // Busca o relatório de viagem
                $stmtTravel = $db->prepare("SELECT * FROM `travel_reports` WHERE id = :id LIMIT 1");
                $stmtTravel->execute(['id' => $id]);
                $travel = $stmtTravel->fetch();

                if (!$travel) throw new Exception("Relatório de viagem não encontrado.");
                if ($travel['status'] === 'APROVADO') throw new Exception("Este relatório de viagem já foi aprovado.");

                // Busca todos os recibos atrelados a esta viagem
                $stmtRec = $db->prepare("SELECT * FROM `travel_receipts` WHERE travel_report_id = :id");
                $stmtRec->execute(['id' => $id]);
                $receipts = $stmtRec->fetchAll();

                if (empty($receipts)) {
                    throw new Exception("O relatório de viagem não possui recibos de combustível anexados.");
                }

                $totalFuelValue = 0.00;
                foreach ($receipts as $rec) {
                    $recVal = floatval($rec['value']);
                    $totalFuelValue += $recVal;

                    $catId = !empty($rec['spce_category_id']) ? intval($rec['spce_category_id']) : $spceCategoryId;
                    if ($catId <= 0) $catId = $spceCategoryId;
                    if ($catId <= 0) {
                        throw new Exception("É necessário selecionar a Categoria SPCE antes de aprovar os gastos com combustível.");
                    }

                    $kmStr = "";
                    if (!empty($travel['initial_km']) && !empty($travel['final_km'])) {
                        $kmStr = " (Placa: " . strtoupper($travel['vehicle_plate']) . " - KM " . number_format($travel['initial_km'], 0, ',', '.') . " ➔ " . number_format($travel['final_km'], 0, ',', '.') . " | " . number_format($travel['final_km'] - $travel['initial_km'], 0, ',', '.') . " KM rodados)";
                    } elseif (!empty($travel['vehicle_plate'])) {
                        $kmStr = " (Placa: " . strtoupper($travel['vehicle_plate']) . ")";
                    }

                    $supplierDisplay = !empty($rec['supplier_name']) ? $rec['supplier_name'] : 'Posto de Combustível';
                    $desc = "Reembolso Combustível / Viagem #" . $travel['id'] . $kmStr . " - " . $travel['purpose'];

                    // Insere despesa oficial vinculada no módulo financeiro
                    $stmtInsertExp = $db->prepare(
                        "INSERT INTO `despesas` 
                         (user_id, bank_account_id, spce_category_id, supplier_name, supplier_cnpj_cpf, description, value, date_incurred, status, approved_by, approved_at, notes, created_at, updated_at)
                         VALUES 
                         (:user_id, :bank_account_id, :spce_category_id, :supplier_name, :supplier_cnpj_cpf, :description, :value, :date_incurred, 'PAGO', :approved_by, NOW(), :notes, NOW(), NOW())"
                    );
                    $stmtInsertExp->execute([
                        'user_id' => $travel['user_id'],
                        'bank_account_id' => $bankAccountId,
                        'spce_category_id' => $catId,
                        'supplier_name' => $supplierDisplay,
                        'supplier_cnpj_cpf' => !empty($rec['supplier_cnpj']) ? $rec['supplier_cnpj'] : null,
                        'description' => $desc,
                        'value' => $recVal,
                        'date_incurred' => $rec['receipt_date'],
                        'approved_by' => $user['id'],
                        'notes' => $notes
                    ]);

                    // Atualiza status do recibo para APROVADO
                    $stmtRecUpd = $db->prepare("UPDATE `travel_receipts` SET status = 'APROVADO' WHERE id = :id");
                    $stmtRecUpd->execute(['id' => $rec['id']]);
                }

                // Aprova relatório de viagem
                $stmtUpdate = $db->prepare(
                    "UPDATE `travel_reports` SET status = 'APROVADO', approved_by = :approved_by, approved_at = NOW() WHERE id = :id"
                );
                $stmtUpdate->execute(['approved_by' => $user['id'], 'id' => $id]);

                // Desconta o valor total da conta bancária vinculada
                $stmtBank = $db->prepare("UPDATE `bank_accounts` SET balance = balance - :value WHERE id = :id");
                $stmtBank->execute(['value' => $totalFuelValue, 'id' => $bankAccountId]);

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

    /**
     * Edição direta de despesa pelo Administrador (POST)
     */
    public function updateExpense(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $id = intval($_POST['id'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $supplier_cnpj_cpf = preg_replace('/\D/', '', $_POST['supplier_cnpj_cpf'] ?? '');
            $supplier_name = trim($_POST['supplier_name'] ?? '');
            $value = $this->parseBrlCurrency($_POST['value'] ?? '0');
            $date_incurred = $_POST['date_incurred'] ?? '';
            $expense_type_id = intval($_POST['expense_type_id'] ?? 0);
            $bank_account_id = !empty($_POST['bank_account_id']) ? intval($_POST['bank_account_id']) : null;
            $spce_category_id = !empty($_POST['spce_category_id']) ? intval($_POST['spce_category_id']) : null;
            $notes = trim($_POST['notes'] ?? '');

            if ($id <= 0 || empty($description) || empty($supplier_name) || $value <= 0 || empty($date_incurred)) {
                throw new Exception("Descrição, Fornecedor, Valor e Data são obrigatórios.");
            }

            $db->beginTransaction();

            // Cadastra ou atualiza fornecedor
            $supplierId = null;
            if (!empty($supplier_cnpj_cpf)) {
                $stmtSup = $db->prepare("SELECT id FROM `suppliers` WHERE cnpj_cpf = :cnpj_cpf LIMIT 1");
                $stmtSup->execute(['cnpj_cpf' => $supplier_cnpj_cpf]);
                $s = $stmtSup->fetch();
                if ($s) {
                    $supplierId = $s['id'];
                    $db->prepare("UPDATE `suppliers` SET corporate_name = :name WHERE id = :id")->execute(['name' => $supplier_name, 'id' => $supplierId]);
                } else {
                    $stmtIns = $db->prepare("INSERT INTO `suppliers` (cnpj_cpf, corporate_name, status) VALUES (:cnpj_cpf, :corporate_name, 'ATIVO')");
                    $stmtIns->execute(['cnpj_cpf' => $supplier_cnpj_cpf, 'corporate_name' => $supplier_name]);
                    $supplierId = $db->lastInsertId();
                }
            } else {
                $stmtSup = $db->prepare("SELECT supplier_id FROM `despesas` WHERE id = :id LIMIT 1");
                $stmtSup->execute(['id' => $id]);
                $supplierId = $stmtSup->fetchColumn();
            }

            // Atualiza a despesa
            $stmtUp = $db->prepare(
                "UPDATE `despesas` 
                 SET description = :description, 
                     supplier_id = :supplier_id, 
                     value = :value, 
                     date_incurred = :date_incurred, 
                     expense_type_id = :expense_type_id, 
                     bank_account_id = :bank_account_id, 
                     spce_category_id = :spce_category_id, 
                     notes = :notes 
                 WHERE id = :id"
            );
            $stmtUp->execute([
                'description' => $description,
                'supplier_id' => $supplierId,
                'value' => $value,
                'date_incurred' => $date_incurred,
                'expense_type_id' => $expense_type_id > 0 ? $expense_type_id : null,
                'bank_account_id' => $bank_account_id,
                'spce_category_id' => $spce_category_id,
                'notes' => empty($notes) ? null : $notes,
                'id' => $id
            ]);

            // Auditoria
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (:user_id, 'ADMIN_EXPENSE_EDIT', 'despesas', :record_id, :new_values, :ip_address, :user_agent)"
            );
            $stmtLog->execute([
                'user_id' => $user['id'],
                'record_id' => $id,
                'new_values' => json_encode(['description' => $description, 'value' => $value], JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            $db->commit();
            Session::setFlash('success', 'Gasto/Despesa atualizada com sucesso pelo Administrador!');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Session::setFlash('error', 'Erro ao atualizar despesa: ' . $e->getMessage());
        }

        $redirect = $_POST['redirect_to'] ?? '/admin/financeiro/fila';
        $this->redirect($redirect);
    }



    /**
     * Tela de Gestão de Contratos por Tempo Determinado (GET)
     */
    public function contratos(): void {
        $user = $this->requireAuth();
        $db = Database::getInstance();
        
        $contracts = $db->query(
            "SELECT c.*, s.corporate_name, s.cnpj_cpf, u.name as created_by_name 
             FROM `supplier_contracts` c 
             JOIN `suppliers` s ON c.supplier_id = s.id 
             LEFT JOIN `usuarios` u ON c.created_by = u.id 
             ORDER BY c.created_at DESC"
        )->fetchAll();

        $suppliers = $db->query(
            "SELECT id, corporate_name, cnpj_cpf 
             FROM `suppliers` 
             WHERE status = 'ATIVO' 
             ORDER BY corporate_name ASC"
        )->fetchAll();

        $this->render('admin/financeiro/contratos', [
            'title'      => 'Contratos por Tempo Determinado | SGE',
            'user'       => $user,
            'contracts'  => $contracts,
            'suppliers'  => $suppliers,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    /**
     * Cadastro de Novo Contrato por Tempo Determinado (POST)
     */
    public function addContrato(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $supplier_id = intval($_POST['supplier_id'] ?? 0);
            $contract_number = trim($_POST['contract_number'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $total_amount = $this->parseBrlCurrency($_POST['total_amount'] ?? '0');
            $monthly_amount = !empty($_POST['monthly_amount']) ? $this->parseBrlCurrency($_POST['monthly_amount']) : null;
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';

            if ($supplier_id <= 0 || empty($title) || $total_amount <= 0 || empty($start_date) || empty($end_date)) {
                throw new Exception("Preencha todos os campos obrigatórios (Fornecedor, Objeto, Valor Total e Vigência).");
            }

            if (strtotime($end_date) < strtotime($start_date)) {
                throw new Exception("A data de término do contrato não pode ser anterior à data de início.");
            }

            // Validação e Upload do Arquivo PDF (Opcional)
            $relativePath = null;
            $originalName = null;

            if (isset($_FILES['contract_pdf']) && $_FILES['contract_pdf']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['contract_pdf'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    throw new Exception("O arquivo de contrato deve estar obrigatoriamente no formato PDF.");
                }

                if ($file['size'] > 10 * 1024 * 1024) {
                    throw new Exception("O tamanho do arquivo PDF não pode ultrapassar 10MB.");
                }

                $uploadDir = __DIR__ . '/../../storage/uploads/contratos_fornecedores/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $safeFileName = 'contrato_sup_' . $supplier_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
                $targetPath = $uploadDir . $safeFileName;

                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new Exception("Falha ao salvar o arquivo PDF no servidor.");
                }

                $relativePath = 'storage/uploads/contratos_fornecedores/' . $safeFileName;
                $originalName = basename($file['name']);
            }

            $stmt = $db->prepare(
                "INSERT INTO `supplier_contracts` 
                 (supplier_id, contract_number, title, description, total_amount, monthly_amount, start_date, end_date, status, file_path, file_name, created_by) 
                 VALUES (:supplier_id, :contract_number, :title, :description, :total_amount, :monthly_amount, :start_date, :end_date, 'VIGENTE', :file_path, :file_name, :created_by)"
            );

            $stmt->execute([
                'supplier_id' => $supplier_id,
                'contract_number' => empty($contract_number) ? null : $contract_number,
                'title' => $title,
                'description' => empty($description) ? null : $description,
                'total_amount' => $total_amount,
                'monthly_amount' => $monthly_amount,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'file_path' => $relativePath,
                'file_name' => $originalName,
                'created_by' => $user['id']
            ]);

            $contractId = $db->lastInsertId();

            // Log Audit
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (:user_id, 'CREATE_SUPPLIER_CONTRACT', 'supplier_contracts', :record_id, :new_values, :ip_address, :user_agent)"
            );
            $stmtLog->execute([
                'user_id' => $user['id'],
                'record_id' => $contractId,
                'new_values' => json_encode(['title' => $title, 'supplier_id' => $supplier_id, 'total_amount' => $total_amount], JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            Session::setFlash('success', 'Contrato por Tempo Determinado cadastrado com sucesso!');
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao cadastrar contrato: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/contratos');
    }

    /**
     * Edição de Contrato por Tempo Determinado e Atualização opcional do PDF (POST)
     */
    public function editContrato(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $id = intval($_POST['id'] ?? 0);
            $supplier_id = intval($_POST['supplier_id'] ?? 0);
            $contract_number = trim($_POST['contract_number'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $total_amount = $this->parseBrlCurrency($_POST['total_amount'] ?? '0');
            $monthly_amount = !empty($_POST['monthly_amount']) ? $this->parseBrlCurrency($_POST['monthly_amount']) : null;
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $status = in_array($_POST['status'] ?? '', ['VIGENTE', 'ENCERRADO', 'CANCELADO']) ? $_POST['status'] : 'VIGENTE';

            if ($id <= 0 || $supplier_id <= 0 || empty($title) || $total_amount <= 0 || empty($start_date) || empty($end_date)) {
                throw new Exception("Dados inválidos para edição do contrato.");
            }

            if (strtotime($end_date) < strtotime($start_date)) {
                throw new Exception("A data de término não pode ser anterior à data de início.");
            }

            // Busca contrato atual
            $stmtCur = $db->prepare("SELECT * FROM `supplier_contracts` WHERE id = :id LIMIT 1");
            $stmtCur->execute(['id' => $id]);
            $contract = $stmtCur->fetch();
            if (!$contract) {
                throw new Exception("Contrato não encontrado.");
            }

            $filePath = $contract['file_path'];
            $fileName = $contract['file_name'];

            // Se enviou novo PDF para substituir o atual
            if (isset($_FILES['contract_pdf']) && $_FILES['contract_pdf']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['contract_pdf'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    throw new Exception("O novo arquivo deve estar no formato PDF.");
                }

                if ($file['size'] > 10 * 1024 * 1024) {
                    throw new Exception("O tamanho do arquivo PDF não pode ultrapassar 10MB.");
                }

                $uploadDir = __DIR__ . '/../../storage/uploads/contratos_fornecedores/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $safeFileName = 'contrato_sup_' . $supplier_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
                $targetPath = $uploadDir . $safeFileName;

                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new Exception("Falha ao salvar o novo arquivo PDF no servidor.");
                }

                // Remove o antigo se existia
                $oldFullPath = __DIR__ . '/../../' . $contract['file_path'];
                if (!empty($contract['file_path']) && file_exists($oldFullPath)) {
                    @unlink($oldFullPath);
                }

                $filePath = 'storage/uploads/contratos_fornecedores/' . $safeFileName;
                $fileName = basename($file['name']);
            }

            $stmtUp = $db->prepare(
                "UPDATE `supplier_contracts` 
                 SET supplier_id = :supplier_id, 
                     contract_number = :contract_number, 
                     title = :title, 
                     description = :description, 
                     total_amount = :total_amount, 
                     monthly_amount = :monthly_amount, 
                     start_date = :start_date, 
                     end_date = :end_date, 
                     status = :status, 
                     file_path = :file_path, 
                     file_name = :file_name 
                 WHERE id = :id"
            );

            $stmtUp->execute([
                'supplier_id' => $supplier_id,
                'contract_number' => empty($contract_number) ? null : $contract_number,
                'title' => $title,
                'description' => empty($description) ? null : $description,
                'total_amount' => $total_amount,
                'monthly_amount' => $monthly_amount,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $status,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'id' => $id
            ]);

            // Auditoria
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (:user_id, 'UPDATE_SUPPLIER_CONTRACT', 'supplier_contracts', :record_id, :new_values, :ip_address, :user_agent)"
            );
            $stmtLog->execute([
                'user_id' => $user['id'],
                'record_id' => $id,
                'new_values' => json_encode(['title' => $title, 'status' => $status, 'total_amount' => $total_amount], JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            Session::setFlash('success', 'Contrato atualizado com sucesso!');
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao editar contrato: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/contratos');
    }

    /**
     * Download/Visualização segura do PDF do Contrato (GET)
     */
    public function downloadContrato(): void {
        $user = $this->requireAuth();
        $db = Database::getInstance();
        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            header("HTTP/1.0 404 Not Found");
            echo "Contrato não encontrado.";
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM `supplier_contracts` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $contract = $stmt->fetch();

        if (!$contract) {
            header("HTTP/1.0 404 Not Found");
            echo "Contrato não localizado.";
            exit;
        }

        $fullPath = __DIR__ . '/../../' . $contract['file_path'];

        if (!file_exists($fullPath)) {
            header("HTTP/1.0 404 Not Found");
            echo "Arquivo PDF do contrato não encontrado no servidor.";
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . addslashes($contract['file_name']) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    /**
     * Tela do Módulo de Prestação de Contas & Exportação SPCE (TSE)
     */
    public function spceReport(): void {
        $user = $this->requireAuth();
        $db = Database::getInstance();

        // 1. Doações com alerta de 72h (Pendentes de envio ao TSE)
        $revenues72h = $db->query(
            "SELECT r.*, b.name as bank_name, s.description as spce_category 
             FROM `receitas` r 
             LEFT JOIN `bank_accounts` b ON r.bank_account_id = b.id 
             LEFT JOIN `spce_categories` s ON r.spce_category_id = s.id 
             ORDER BY r.date_received DESC, r.id DESC"
        )->fetchAll();

        // 2. Despesas com fornecedores para auditoria
        $expenses = $db->query(
            "SELECT d.*, s.corporate_name, s.cnpj_cpf, c.description as spce_category,
                    (SELECT COUNT(*) FROM `comprovantes_cripto` cc WHERE cc.expense_id = d.id) as total_anexos
             FROM `despesas` d 
             JOIN `suppliers` s ON d.supplier_id = s.id 
             LEFT JOIN `spce_categories` c ON d.spce_category_id = c.id 
             ORDER BY d.date_incurred DESC"
        )->fetchAll();

        // 3. Contratos por tempo determinado
        $contracts = $db->query(
            "SELECT c.*, s.corporate_name, s.cnpj_cpf 
             FROM `supplier_contracts` c 
             JOIN `suppliers` s ON c.supplier_id = s.id 
             ORDER BY c.start_date DESC"
        )->fetchAll();

        // 4. Auditoria de Inconsistências (Checklist de Conformidade Eleitoral)
        $auditIssues = [];

        // Inconsistência A: Despesas pagas em espécie (DINHEIRO) acima de R$ 300
        foreach ($expenses as $exp) {
            if (strtoupper($exp['payment_method'] ?? '') === 'ESPECIE' && $exp['value'] > 300) {
                $auditIssues[] = [
                    'severity' => 'DANGER',
                    'message' => "Despesa ID #{$exp['id']} ({$exp['description']}) no valor de R$ " . number_format($exp['value'], 2, ',', '.') . " foi paga em espécie acima do limite de R$ 300,00 (Res. TSE 23.607/19)."
                ];
            }
            if ($exp['status'] === 'APROVADO' && intval($exp['total_anexos']) === 0) {
                $auditIssues[] = [
                    'severity' => 'WARNING',
                    'message' => "Despesa aprovada ID #{$exp['id']} ({$exp['description']}) não possui Nota Fiscal/Comprovante anexado."
                ];
            }
        }

        // Inconsistência B: Doações com CPF em formato suspeito/inválido
        foreach ($revenues72h as $rev) {
            $cleanCpf = preg_replace('/[^0-9]/', '', $rev['donor_cpf']);
            if (strlen($cleanCpf) !== 11) {
                $auditIssues[] = [
                    'severity' => 'DANGER',
                    'message' => "Doação ID #{$rev['id']} de '{$rev['donor_name']}' possui CPF inválido/incompleto ({$rev['donor_cpf']})."
                ];
            }
        }

        // Totais consolidados
        $totalReceitas = array_sum(array_column($revenues72h, 'value'));
        $totalDespesas = array_sum(array_column($expenses, 'value'));

        $this->render('admin/financeiro/spce', [
            'title'        => 'Prestação de Contas & Exportação SPCE | SGE',
            'user'         => $user,
            'revenues'     => $revenues72h,
            'expenses'     => $expenses,
            'contracts'    => $contracts,
            'auditIssues'  => $auditIssues,
            'totalReceitas'=> $totalReceitas,
            'totalDespesas'=> $totalDespesas,
            'csrf_token'   => Session::csrfToken()
        ]);
    }

    /**
     * Marca uma doação como enviada/reportada ao TSE no relatório de 72h (POST)
     */
    public function mark72hReported(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $id = intval($_POST['revenue_id'] ?? 0);

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE `receitas` SET tse_status = 'ENVIADO_72H', tse_reported_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);
            Session::setFlash('success', 'Status de envio do relatório de 72h atualizado com sucesso!');
        }

        $this->redirect('/admin/financeiro/spce');
    }

    /**
     * Exporta os relatórios financeiros em formato CSV compatível com o SPCE / Excel (GET)
     */
    public function exportSpceCsv(): void {
        $this->requireAuth();
        $db = Database::getInstance();
        $type = $_GET['type'] ?? 'receitas';

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="SPCE_TSE_' . strtoupper($type) . '_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");

        if ($type === 'receitas') {
            fputcsv($output, ['ID', 'Data Recebimento', 'Doador', 'CPF Doador', 'Valor (R$)', 'Conta Origem', 'Categoria SPCE', 'Status Envio 72h', 'Data Envio TSE'], ';');
            $rows = $db->query(
                "SELECT r.id, r.date_received, r.donor_name, r.donor_cpf, r.value, b.name as bank_name, s.description as spce_category, r.tse_status, r.tse_reported_at 
                 FROM `receitas` r 
                 LEFT JOIN `bank_accounts` b ON r.bank_account_id = b.id 
                 LEFT JOIN `spce_categories` s ON r.spce_category_id = s.id 
                 ORDER BY r.date_received ASC"
            )->fetchAll();

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['id'],
                    date('d/m/Y', strtotime($row['date_received'])),
                    $row['donor_name'],
                    $row['donor_cpf'],
                    number_format($row['value'], 2, ',', '.'),
                    $row['bank_name'] ?? 'N/A',
                    $row['spce_category'] ?? 'Não classificada',
                    $row['tse_status'],
                    $row['tse_reported_at'] ? date('d/m/Y H:i', strtotime($row['tse_reported_at'])) : 'Pendente'
                ], ';');
            }
        } elseif ($type === 'despesas') {
            fputcsv($output, ['ID', 'Data Ocorrência', 'Fornecedor / Favorecido', 'CNPJ / CPF', 'Descrição', 'Valor (R$)', 'Forma Pagamento', 'Categoria SPCE', 'Status', 'Observações'], ';');
            $rows = $db->query(
                "SELECT d.id, d.date_incurred, s.corporate_name, s.cnpj_cpf, d.description, d.value, d.payment_method, c.description as spce_category, d.status, d.notes 
                 FROM `despesas` d 
                 JOIN `suppliers` s ON d.supplier_id = s.id 
                 LEFT JOIN `spce_categories` c ON d.spce_category_id = c.id 
                 ORDER BY d.date_incurred ASC"
            )->fetchAll();

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['id'],
                    date('d/m/Y', strtotime($row['date_incurred'])),
                    $row['corporate_name'],
                    $row['cnpj_cpf'],
                    $row['description'],
                    number_format($row['value'], 2, ',', '.'),
                    $row['payment_method'] ?? 'N/A',
                    $row['spce_category'] ?? 'Não classificada',
                    $row['status'],
                    $row['notes'] ?? ''
                ], ';');
            }
        } elseif ($type === 'contratos') {
            fputcsv($output, ['ID', 'Nº Contrato', 'Empresa / Fornecedor', 'CNPJ/CPF', 'Título / Objeto', 'Valor Total (R$)', 'Valor Mensal (R$)', 'Início Vigência', 'Término Vigência', 'Status', 'PDF Anexo'], ';');
            $rows = $db->query(
                "SELECT c.*, s.corporate_name, s.cnpj_cpf 
                 FROM `supplier_contracts` c 
                 JOIN `suppliers` s ON c.supplier_id = s.id 
                 ORDER BY c.start_date ASC"
            )->fetchAll();

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['contract_number'] ?? 'S/N',
                    $row['corporate_name'],
                    $row['cnpj_cpf'],
                    $row['title'],
                    number_format($row['total_amount'], 2, ',', '.'),
                    $row['monthly_amount'] ? number_format($row['monthly_amount'], 2, ',', '.') : '0,00',
                    date('d/m/Y', strtotime($row['start_date'])),
                    date('d/m/Y', strtotime($row['end_date'])),
                    $row['status'],
                    !empty($row['file_path']) ? 'SIM' : 'NÃO'
                ], ';');
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Dossiê Consolidado de Prestação de Contas para Impressão / PDF do Contador (GET)
     */
    public function exportSpcePdf(): void {
        $user = $this->requireAuth();
        $db = Database::getInstance();

        $bankAccounts = $db->query("SELECT * FROM `bank_accounts` WHERE status = 'ATIVA'")->fetchAll();
        $revenues = $db->query(
            "SELECT r.*, b.name as bank_name, s.description as spce_category 
             FROM `receitas` r 
             LEFT JOIN `bank_accounts` b ON r.bank_account_id = b.id 
             LEFT JOIN `spce_categories` s ON r.spce_category_id = s.id 
             ORDER BY r.date_received ASC"
        )->fetchAll();

        $expenses = $db->query(
            "SELECT d.*, s.corporate_name, s.cnpj_cpf, c.description as spce_category 
             FROM `despesas` d 
             JOIN `suppliers` s ON d.supplier_id = s.id 
             LEFT JOIN `spce_categories` c ON d.spce_category_id = c.id 
             ORDER BY d.date_incurred ASC"
        )->fetchAll();

        $contracts = $db->query(
            "SELECT c.*, s.corporate_name, s.cnpj_cpf 
             FROM `supplier_contracts` c 
             JOIN `suppliers` s ON c.supplier_id = s.id 
             ORDER BY c.start_date ASC"
        )->fetchAll();

        $colaboradores = $db->query(
            "SELECT c.nome_completo, c.cpf, c.status, c.tse_regularidade_json, c.tse_regularidade_data,
                    cc.funcao_campanha, cc.valor_contratado
             FROM `colaboradores` c
             LEFT JOIN `contratos_colaboradores` cc ON cc.colaborador_id = c.id
             ORDER BY c.nome_completo ASC"
        )->fetchAll();

        echo '<!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Dossiê de Prestação de Contas - Eleições 2026 | SGE</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 30px; color: #1e293b; }
                h1, h2, h3 { margin-bottom: 5px; }
                .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
                .section { margin-bottom: 25px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
                th { background-color: #f1f5f9; font-weight: bold; }
                .text-right { text-align: right; }
                .badge { padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
                .badge-success { background: #dcfce7; color: #166534; }
                .badge-danger { background: #fee2e2; color: #991b1b; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="no-print" style="margin-bottom: 20px; text-align: right;">
                <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">🖨️ Imprimir Dossiê / Salvar PDF</button>
            </div>
            <div class="header">
                <h1>SISTEMA DE GESTÃO ELEITORAL - SGE</h1>
                <h2>DOSSIÊ CONSOLIDADO DE PRESTAÇÃO DE CONTAS</h2>
                <p>Gerado em: ' . date('d/m/Y H:i:s') . ' | Emitido por: ' . htmlspecialchars($user['name']) . '</p>
            </div>

            <div class="section">
                <h3>1. Saldos e Contas Bancárias da Campanha</h3>
                <table>
                    <thead>
                        <tr><th>Conta</th><th>Banco</th><th>Agência</th><th>Conta Corrente</th><th>Tipo de Fundo</th><th>Saldo Atual</th></tr>
                    </thead>
                    <tbody>';
                    $totalSaldos = 0;
                    foreach ($bankAccounts as $acc) {
                        $totalSaldos += floatval($acc['balance']);
                        echo '<tr>
                            <td>' . htmlspecialchars($acc['name']) . '</td>
                            <td>' . htmlspecialchars($acc['bank_name']) . '</td>
                            <td>' . htmlspecialchars($acc['agency']) . '</td>
                            <td>' . htmlspecialchars($acc['account_number']) . '</td>
                            <td>' . htmlspecialchars($acc['fund_type']) . '</td>
                            <td class="text-right">R$ ' . number_format($acc['balance'], 2, ',', '.') . '</td>
                        </tr>';
                    }
                    echo '<tr><th colspan="5" class="text-right">Total em Caixa:</th><th class="text-right">R$ ' . number_format($totalSaldos, 2, ',', '.') . '</th></tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h3>2. Receitas e Doações de Campanha</h3>
                <table>
                    <thead>
                        <tr><th>Data</th><th>Doador</th><th>CPF</th><th>Categoria SPCE</th><th>Conta</th><th class="text-right">Valor</th></tr>
                    </thead>
                    <tbody>';
                    $sumRec = 0;
                    foreach ($revenues as $r) {
                        $sumRec += floatval($r['value']);
                        echo '<tr>
                            <td>' . date('d/m/Y', strtotime($r['date_received'])) . '</td>
                            <td>' . htmlspecialchars($r['donor_name']) . '</td>
                            <td>' . htmlspecialchars($r['donor_cpf']) . '</td>
                            <td>' . htmlspecialchars($r['spce_category'] ?? 'Outros') . '</td>
                            <td>' . htmlspecialchars($r['bank_name'] ?? 'N/A') . '</td>
                            <td class="text-right">R$ ' . number_format($r['value'], 2, ',', '.') . '</td>
                        </tr>';
                    }
                    echo '<tr><th colspan="5" class="text-right">Total de Arrecadação:</th><th class="text-right">R$ ' . number_format($sumRec, 2, ',', '.') . '</th></tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h3>3. Despesas e Gastos Eleitorais Cadastrados</h3>
                <table>
                    <thead>
                        <tr><th>Data</th><th>Fornecedor</th><th>CNPJ/CPF</th><th>Descrição</th><th>Pagamento</th><th>Status</th><th class="text-right">Valor</th></tr>
                    </thead>
                    <tbody>';
                    $sumDesp = 0;
                    foreach ($expenses as $e) {
                        $sumDesp += floatval($e['value']);
                        echo '<tr>
                            <td>' . date('d/m/Y', strtotime($e['date_incurred'])) . '</td>
                            <td>' . htmlspecialchars($e['corporate_name']) . '</td>
                            <td>' . htmlspecialchars($e['cnpj_cpf']) . '</td>
                            <td>' . htmlspecialchars($e['description']) . '</td>
                            <td>' . htmlspecialchars($e['payment_method'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($e['status']) . '</td>
                            <td class="text-right">R$ ' . number_format($e['value'], 2, ',', '.') . '</td>
                        </tr>';
                    }
                    echo '<tr><th colspan="6" class="text-right">Total de Gastos:</th><th class="text-right">R$ ' . number_format($sumDesp, 2, ',', '.') . '</th></tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h3>4. Contratos de Fornecedores por Tempo Determinado</h3>
                <table>
                    <thead>
                        <tr><th>Nº Contrato</th><th>Fornecedor</th><th>Objeto / Título</th><th>Vigência</th><th>Status</th><th class="text-right">Valor Total</th></tr>
                    </thead>
                    <tbody>';
                    $sumCont = 0;
                    foreach ($contracts as $c) {
                        $sumCont += floatval($c['total_amount']);
                        echo '<tr>
                            <td>' . htmlspecialchars($c['contract_number'] ?? 'S/N') . '</td>
                            <td>' . htmlspecialchars($c['corporate_name']) . '</td>
                            <td>' . htmlspecialchars($c['title']) . '</td>
                            <td>' . date('d/m/Y', strtotime($c['start_date'])) . ' a ' . date('d/m/Y', strtotime($c['end_date'])) . '</td>
                            <td>' . htmlspecialchars($c['status']) . '</td>
                            <td class="text-right">R$ ' . number_format($c['total_amount'], 2, ',', '.') . '</td>
                        </tr>';
                    }
                    echo '<tr><th colspan="5" class="text-right">Total de Instrumentos Contratuais:</th><th class="text-right">R$ ' . number_format($sumCont, 2, ',', '.') . '</th></tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h3>5. Contratos de Colaboradores e Apoio (Militância de Campanha)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nome Completo</th>
                            <th>CPF</th>
                            <th>Cargo / Papel Eleitoral</th>
                            <th>Status Cadastral</th>
                            <th>Situação Regularidade (TSE/Receita)</th>
                            <th class="text-right">Valor Contratado</th>
                        </tr>
                    </thead>
                    <tbody>';
                    $sumColab = 0;
                    foreach ($colaboradores as $col) {
                        $sumColab += floatval($col['valor_contratado']);
                        
                        // Formatação CPF
                        $cpfRaw = preg_replace('/\D/', '', $col['cpf']);
                        if (strlen($cpfRaw) === 11) {
                            $cpf = substr($cpfRaw, 0, 3) . '.' . substr($cpfRaw, 3, 3) . '.' . substr($cpfRaw, 6, 3) . '-' . substr($cpfRaw, 9, 2);
                        } else {
                            $cpf = htmlspecialchars($col['cpf']);
                        }

                        // Regularidade
                        $regStatus = 'Não Verificado';
                        $regClass = 'badge-danger';
                        if (!empty($col['tse_regularidade_json'])) {
                            $regData = json_decode($col['tse_regularidade_json'], true);
                            if (is_array($regData) && isset($regData['valido'])) {
                                $regStatus = $regData['valido'] ? 'Apto' : 'Inapto';
                                $regClass = $regData['valido'] ? 'badge-success' : 'badge-danger';
                                if (!empty($col['tse_regularidade_data'])) {
                                    $regStatus .= ' (Ref: ' . date('d/m/Y', strtotime($col['tse_regularidade_data'])) . ')';
                                }
                            }
                        }

                        echo '<tr>
                            <td>' . htmlspecialchars($col['nome_completo']) . '</td>
                            <td>' . $cpf . '</td>
                            <td>' . htmlspecialchars($col['funcao_campanha'] ?? 'Apoio / Geral') . '</td>
                            <td>' . htmlspecialchars($col['status']) . '</td>
                            <td><span class="badge ' . $regClass . '">' . $regStatus . '</span></td>
                            <td class="text-right">R$ ' . number_format($col['valor_contratado'], 2, ',', '.') . '</td>
                        </tr>';
                    }
                    echo '<tr><th colspan="5" class="text-right">Total Contratado (Colaboradores):</th><th class="text-right">R$ ' . number_format($sumColab, 2, ',', '.') . '</th></tr>
                    </tbody>
                </table>
            </div>
        </body>
        </html>';
        exit;
    }

    /**
     * Tela de Carga Inicial e Conciliação Bancária com Extratos PDF
     */
    public function reconciliation(): void {
        $user = $this->requireAuth();
        $db = Database::getInstance();

        // Contas Bancárias ativas
        $accounts = $db->query("SELECT * FROM `bank_accounts` ORDER BY name ASC")->fetchAll();

        // Histórico de Ajustes e Cargas de Saldo
        $adjustments = $db->query(
            "SELECT a.*, b.name as bank_name, b.agency, b.account_number, u.name as created_by_name 
             FROM `bank_balance_adjustments` a 
             JOIN `bank_accounts` b ON a.bank_account_id = b.id 
             JOIN `usuarios` u ON a.created_by = u.id 
             ORDER BY a.created_at DESC"
        )->fetchAll();

        $totalBalance = array_sum(array_column($accounts, 'balance'));

        $this->render('admin/financeiro/conciliacao', [
            'title'        => 'Carga & Conciliação Bancária | SGE',
            'user'         => $user,
            'accounts'     => $accounts,
            'adjustments'  => $adjustments,
            'totalBalance' => $totalBalance,
            'csrf_token'   => Session::csrfToken()
        ]);
    }

    /**
     * Processa a Carga Inicial ou Ajuste de Saldo Bancário com Upload Obrigatório de Extrato PDF
     */
    public function adjustBankBalance(): void {
        $user = $this->requireAuth();
        $this->validatePostCsrf();
        $db = Database::getInstance();

        $bankAccountId   = intval($_POST['bank_account_id'] ?? 0);
        $adjustmentType  = trim($_POST['adjustment_type'] ?? '');
        $amountBrl       = trim($_POST['adjustment_amount'] ?? '');
        $reason          = trim($_POST['reason'] ?? '');

        $amount = floatval(str_replace(',', '.', str_replace('.', '', $amountBrl)));

        if ($bankAccountId <= 0 || empty($adjustmentType) || empty($reason)) {
            Session::setFlash('error', 'Por favor, preencha todos os campos obrigatórios do formulário.');
            $this->redirect('/admin/financeiro/conciliacao');
        }

        // Valida conta bancária existente
        $stmt = $db->prepare("SELECT * FROM `bank_accounts` WHERE id = :id");
        $stmt->execute(['id' => $bankAccountId]);
        $account = $stmt->fetch();

        if (!$account) {
            Session::setFlash('error', 'Conta bancária não localizada.');
            $this->redirect('/admin/financeiro/conciliacao');
        }

        // Validação OBRIGATÓRIA do Extrato Bancário em PDF
        if (!isset($_FILES['statement_pdf']) || $_FILES['statement_pdf']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'É obrigatório anexar a cópia do Extrato Bancário em PDF para registrar a carga ou ajuste de saldo.');
            $this->redirect('/admin/financeiro/conciliacao');
        }

        $file = $_FILES['statement_pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($ext !== 'pdf' || ($mime !== 'application/pdf' && $mime !== 'application/x-pdf')) {
            Session::setFlash('error', 'O arquivo do extrato bancário deve estar no formato PDF.');
            $this->redirect('/admin/financeiro/conciliacao');
        }

        if ($file['size'] > 10 * 1024 * 1024) { // 10MB
            Session::setFlash('error', 'O arquivo do extrato excede o limite máximo permitido de 10MB.');
            $this->redirect('/admin/financeiro/conciliacao');
        }

        // Diretório de armazenamento de extratos
        $uploadDir = __DIR__ . '/../../storage/uploads/extratos';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'EXTRATO_' . $bankAccountId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.pdf';
        $filePath = 'storage/uploads/extratos/' . $fileName;
        $destination = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            Session::setFlash('error', 'Falha ao salvar a cópia do extrato bancário no servidor.');
            $this->redirect('/admin/financeiro/conciliacao');
        }

        $oldBalance = floatval($account['balance']);
        $newBalance = $oldBalance;

        if ($adjustmentType === 'CARGA_INICIAL' || $adjustmentType === 'CONCILIACAO') {
            $newBalance = $amount;
        } elseif ($adjustmentType === 'AJUSTE_CREDITO') {
            $newBalance = $oldBalance + $amount;
        } elseif ($adjustmentType === 'AJUSTE_DEBITO') {
            $newBalance = $oldBalance - $amount;
        }

        // Registra transação no banco de dados
        $db->beginTransaction();
        try {
            // 1. Atualiza saldo na conta bancária
            $stmtUpd = $db->prepare("UPDATE `bank_accounts` SET balance = :balance, updated_at = NOW() WHERE id = :id");
            $stmtUpd->execute([
                'balance' => $newBalance,
                'id'      => $bankAccountId
            ]);

            // 2. Insere registro de ajuste no histórico
            $stmtIns = $db->prepare(
                "INSERT INTO `bank_balance_adjustments` 
                 (bank_account_id, adjustment_type, old_balance, adjustment_amount, new_balance, reason, statement_file_path, statement_file_name, created_by) 
                 VALUES (:bank_account_id, :adjustment_type, :old_balance, :adjustment_amount, :new_balance, :reason, :statement_file_path, :statement_file_name, :created_by)"
            );
            $stmtIns->execute([
                'bank_account_id'    => $bankAccountId,
                'adjustment_type'   => $adjustmentType,
                'old_balance'        => $oldBalance,
                'adjustment_amount' => $amount,
                'new_balance'        => $newBalance,
                'reason'             => $reason,
                'statement_file_path'=> $filePath,
                'statement_file_name'=> $file['name'],
                'created_by'         => $user['id']
            ]);

            // 3. Log de auditoria
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (:user_id, 'CARGA_CONCILIACAO_BANCARIA', 'bank_balance_adjustments', :record_id, :new_values, :ip_address, :user_agent)"
            );
            $stmtLog->execute([
                'user_id'    => $user['id'],
                'record_id'  => $db->lastInsertId(),
                'new_values' => json_encode([
                    'conta_id'    => $bankAccountId,
                    'tipo'        => $adjustmentType,
                    'saldo_ant'   => $oldBalance,
                    'saldo_novo'  => $newBalance,
                    'extrato_pdf' => $file['name']
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Unknown'
            ]);

            $db->commit();
            Session::setFlash('success', "Ajuste/Carga de saldo realizado com sucesso! Novo saldo da conta: R$ " . number_format($newBalance, 2, ',', '.'));
        } catch (\Exception $e) {
            $db->rollBack();
            if (file_exists($destination)) {
                @unlink($destination);
            }
            Session::setFlash('error', 'Erro ao salvar alteração de saldo: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/conciliacao');
    }

    /**
     * Download / Streaming seguro do arquivo PDF do Extrato Bancário
     */
    public function downloadStatement(): void {
        $user = $this->requireAuth();
        $db = Database::getInstance();
        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            header("HTTP/1.0 400 Bad Request");
            echo "ID inválido.";
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM `bank_balance_adjustments` WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $adj = $stmt->fetch();

        if (!$adj) {
            header("HTTP/1.0 404 Not Found");
            echo "Registro de ajuste não localizado.";
            exit;
        }

        $fullPath = __DIR__ . '/../../' . $adj['statement_file_path'];

        if (!file_exists($fullPath)) {
            header("HTTP/1.0 404 Not Found");
            echo "Arquivo PDF do extrato bancário não localizado no servidor.";
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . addslashes($adj['statement_file_name']) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    /**
     * Tela de Listagem e Gestão de Contas Bancárias da Campanha.
     */
    public function bankAccounts(): void {
        $user = $this->requireAuth();
        $db = Database::getInstance();

        // Lista todas as contas bancárias com totais por tipo de recurso
        $accounts = $db->query("SELECT * FROM `bank_accounts` ORDER BY id DESC")->fetchAll();

        $totals = [
            'FEFC'             => 0.00,
            'FUNDO_PARTIDARIO' => 0.00,
            'OUTROS_RECURSOS'  => 0.00,
            'TOTAL_CAIXA'      => 0.00
        ];

        foreach ($accounts as $acc) {
            if ($acc['status'] === 'ATIVA') {
                $bal = floatval($acc['balance']);
                $totals[$acc['fund_type']] = ($totals[$acc['fund_type']] ?? 0) + $bal;
                $totals['TOTAL_CAIXA'] += $bal;
            }
        }

        $this->render('admin/financeiro/contas', [
            'user'       => $user,
            'accounts'   => $accounts,
            'totals'     => $totals,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    /**
     * Cadastro de Nova Conta Bancária.
     */
    public function storeBankAccount(): void {
        $user = $this->requireAuth();
        $this->validatePostCsrf();

        if ($user['role_name'] !== 'ADMINISTRADOR' && $user['role_name'] !== 'FINANCEIRO') {
            Session::setFlash('error', 'Sem permissão para cadastrar contas bancárias.');
            $this->redirect('/admin/financeiro/contas');
        }

        try {
            $name          = trim($_POST['name'] ?? '');
            $bank_name     = trim($_POST['bank_name'] ?? '');
            $agency        = trim($_POST['agency'] ?? '');
            $account_number= trim($_POST['account_number'] ?? '');
            $pix_key       = trim($_POST['pix_key'] ?? '');
            $fund_type     = trim($_POST['fund_type'] ?? '');
            $initial_balance = $this->parseBrlCurrency($_POST['balance'] ?? '0');

            if (empty($name) || empty($bank_name) || empty($agency) || empty($account_number) || empty($fund_type)) {
                throw new \Exception("Preencha todos os campos obrigatórios (Nome, Banco, Agência, Conta e Tipo de Fundo).");
            }

            if (!in_array($fund_type, ['FEFC', 'FUNDO_PARTIDARIO', 'OUTROS_RECURSOS'])) {
                throw new \Exception("Tipo de fundo eleitoral inválido.");
            }

            $db = Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO `bank_accounts` (name, bank_name, agency, account_number, pix_key, fund_type, balance, status) 
                 VALUES (:name, :bank_name, :agency, :account_number, :pix_key, :fund_type, :balance, 'ATIVA')"
            );
            $stmt->execute([
                'name'           => $name,
                'bank_name'      => $bank_name,
                'agency'         => $agency,
                'account_number' => $account_number,
                'pix_key'        => !empty($pix_key) ? $pix_key : null,
                'fund_type'      => $fund_type,
                'balance'        => $initial_balance
            ]);

            $accId = $db->lastInsertId();

            \App\Services\AuditLogger::log('CREATE_BANK_ACCOUNT', 'bank_accounts', $accId, null, [
                'name' => $name, 'bank_name' => $bank_name, 'fund_type' => $fund_type, 'balance' => $initial_balance
            ]);

            Session::setFlash('success', 'Conta Bancária cadastrada com sucesso!');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Erro ao cadastrar conta: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/contas');
    }

    /**
     * Edição de Conta Bancária Existente.
     */
    public function updateBankAccount(): void {
        $user = $this->requireAuth();
        $this->validatePostCsrf();

        if ($user['role_name'] !== 'ADMINISTRADOR' && $user['role_name'] !== 'FINANCEIRO') {
            Session::setFlash('error', 'Sem permissão para alterar contas bancárias.');
            $this->redirect('/admin/financeiro/contas');
        }

        try {
            $id            = intval($_POST['id'] ?? 0);
            $name          = trim($_POST['name'] ?? '');
            $bank_name     = trim($_POST['bank_name'] ?? '');
            $agency        = trim($_POST['agency'] ?? '');
            $account_number= trim($_POST['account_number'] ?? '');
            $pix_key       = trim($_POST['pix_key'] ?? '');
            $fund_type     = trim($_POST['fund_type'] ?? '');

            if ($id <= 0 || empty($name) || empty($bank_name) || empty($agency) || empty($account_number) || empty($fund_type)) {
                throw new \Exception("Dados inválidos para alteração da conta bancária.");
            }

            if (!in_array($fund_type, ['FEFC', 'FUNDO_PARTIDARIO', 'OUTROS_RECURSOS'])) {
                throw new \Exception("Tipo de recurso eleitoral inválido.");
            }

            $db = Database::getInstance();
            $stmt = $db->prepare(
                "UPDATE `bank_accounts` 
                 SET name = :name, bank_name = :bank_name, agency = :agency, 
                     account_number = :account_number, pix_key = :pix_key, fund_type = :fund_type 
                 WHERE id = :id"
            );
            $stmt->execute([
                'id'             => $id,
                'name'           => $name,
                'bank_name'      => $bank_name,
                'agency'         => $agency,
                'account_number' => $account_number,
                'pix_key'        => !empty($pix_key) ? $pix_key : null,
                'fund_type'      => $fund_type
            ]);

            \App\Services\AuditLogger::log('UPDATE_BANK_ACCOUNT', 'bank_accounts', $id, null, [
                'name' => $name, 'bank_name' => $bank_name, 'fund_type' => $fund_type
            ]);

            Session::setFlash('success', 'Conta bancária atualizada com sucesso!');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Erro ao atualizar conta: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/contas');
    }

    /**
     * Alternar Status da Conta Bancária (ATIVA / ENCERRADA).
     */
    public function toggleBankAccountStatus(): void {
        $user = $this->requireAuth();
        $this->validatePostCsrf();

        if ($user['role_name'] !== 'ADMINISTRADOR' && $user['role_name'] !== 'FINANCEIRO') {
            Session::setFlash('error', 'Sem permissão para alterar o status de contas bancárias.');
            $this->redirect('/admin/financeiro/contas');
        }

        try {
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new \Exception("Conta bancária não identificada.");
            }

            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM `bank_accounts` WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $acc = $stmt->fetch();

            if (!$acc) {
                throw new \Exception("Conta não localizada.");
            }

            $newStatus = ($acc['status'] === 'ATIVA') ? 'ENCERRADA' : 'ATIVA';

            $stmtUpdate = $db->prepare("UPDATE `bank_accounts` SET status = :status WHERE id = :id");
            $stmtUpdate->execute(['status' => $newStatus, 'id' => $id]);

            \App\Services\AuditLogger::log('TOGGLE_BANK_ACCOUNT_STATUS', 'bank_accounts', $id, ['status' => $acc['status']], ['status' => $newStatus]);

            Session::setFlash('success', 'Status da conta bancária alterado para ' . $newStatus . '!');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Erro ao alterar status da conta: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/contas');
    }

    /**
     * Atualiza dados de um recibo de combustível/viagem (CNPJ, Razão Social, Categoria SPCE, Valor).
     */
    public function updateTravelReceipt(): void {
        $user = $this->requireAuth();
        $this->validatePostCsrf();

        try {
            $id = intval($_POST['receipt_id'] ?? 0);
            $supplier_cnpj = trim($_POST['supplier_cnpj'] ?? '');
            $supplier_name = trim($_POST['supplier_name'] ?? '');
            $spce_category_id = intval($_POST['spce_category_id'] ?? 0);
            $value = $this->parseBrlCurrency($_POST['value'] ?? '0');
            $receipt_date = $_POST['receipt_date'] ?? '';

            if ($id <= 0 || $value <= 0 || empty($receipt_date)) {
                throw new \Exception("Parâmetros inválidos para atualização do recibo.");
            }

            $db = Database::getInstance();
            $stmt = $db->prepare(
                "UPDATE `travel_receipts` 
                 SET supplier_cnpj = :supplier_cnpj, supplier_name = :supplier_name, spce_category_id = :spce_category_id, value = :value, receipt_date = :receipt_date 
                 WHERE id = :id"
            );
            $stmt->execute([
                'supplier_cnpj' => empty($supplier_cnpj) ? null : $supplier_cnpj,
                'supplier_name' => empty($supplier_name) ? null : $supplier_name,
                'spce_category_id' => $spce_category_id > 0 ? $spce_category_id : null,
                'value' => $value,
                'receipt_date' => $receipt_date,
                'id' => $id
            ]);

            \App\Services\AuditLogger::log('UPDATE_TRAVEL_RECEIPT', 'travel_receipts', $id, null, [
                'supplier_cnpj' => $supplier_cnpj, 'supplier_name' => $supplier_name, 'value' => $value
            ]);

            Session::setFlash('success', 'Recibo de combustível atualizado com sucesso!');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Erro ao atualizar recibo: ' . $e->getMessage());
        }

        $this->redirect('/admin/financeiro/fila');
    }
}

