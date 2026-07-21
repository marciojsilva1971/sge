<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Services\EncryptionService;
use App\Services\CnpjService;
use Exception;
use PDO;

class PortalController extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    /**
     * Dashboard Principal do Colaborador (Mobile)
     */
    public function index(): void {
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        // 1. Busca relatórios de viagem recentes do usuário
        $stmtTravel = $db->prepare(
            "SELECT * FROM `travel_reports` WHERE user_id = :user_id ORDER BY start_date DESC LIMIT 5"
        );
        $stmtTravel->execute(['user_id' => $user['id']]);
        $recentTravels = $stmtTravel->fetchAll();

        // 2. Busca atividades de militância recentes do usuário
        $stmtMilitancy = $db->prepare(
            "SELECT * FROM `militancy_activities` WHERE user_id = :user_id ORDER BY activity_date DESC LIMIT 5"
        );
        $stmtMilitancy->execute(['user_id' => $user['id']]);
        $recentMilitancy = $stmtMilitancy->fetchAll();

        $this->render('portal/index', [
            'user' => $user,
            'recentTravels' => $recentTravels,
            'recentMilitancy' => $recentMilitancy
        ], 'portal');
    }

    /**
     * Tela de Relatórios e Despesas de Viagem (Mobile)
     */
    public function travel(): void {
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        // Busca todas as viagens do usuário
        $stmtTravel = $db->prepare(
            "SELECT * FROM `travel_reports` WHERE user_id = :user_id ORDER BY start_date DESC"
        );
        $stmtTravel->execute(['user_id' => $user['id']]);
        $travelReports = $stmtTravel->fetchAll();

        // Para os relatórios em andamento, busca seus recibos atrelados
        $reportsWithReceipts = [];
        foreach ($travelReports as $report) {
            $stmtReceipts = $db->prepare(
                "SELECT r.*, c.code AS spce_code, c.description AS spce_desc 
                 FROM `travel_receipts` r 
                 JOIN `spce_categories` c ON r.spce_category_id = c.id 
                 WHERE r.travel_report_id = :id"
            );
            $stmtReceipts->execute(['id' => $report['id']]);
            $report['receipts'] = $stmtReceipts->fetchAll();
            $reportsWithReceipts[] = $report;
        }

        // Busca categorias de despesas
        $spceCategories = $db->query("SELECT id, code, description FROM `spce_categories` WHERE type = 'DESPESA' ORDER BY code ASC")->fetchAll();

        $this->render('portal/viagem', [
            'user' => $user,
            'travelReports' => $reportsWithReceipts,
            'spceCategories' => $spceCategories,
            'csrf_token' => Session::csrfToken()
        ], 'portal');
    }

    /**
     * Lançamento de Novo Relatório de Viagem (POST)
     */
    public function addTravel(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $purpose = trim($_POST['purpose'] ?? '');
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $vehicle_plate = trim($_POST['vehicle_plate'] ?? '');

            if (empty($purpose) || empty($start_date) || empty($end_date)) {
                throw new Exception("Objetivo, data inicial e data final são campos obrigatórios.");
            }

            $stmt = $db->prepare(
                "INSERT INTO `travel_reports` (user_id, purpose, start_date, end_date, vehicle_plate, status) 
                 VALUES (:user_id, :purpose, :start_date, :end_date, :vehicle_plate, 'EM_ANDAMENTO')"
            );
            $stmt->execute([
                'user_id' => $user['id'],
                'purpose' => $purpose,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'vehicle_plate' => empty($vehicle_plate) ? null : $vehicle_plate
            ]);

            Session::setFlash('success', 'Relatório de viagem iniciado! Adicione os cupons fiscais abaixo.');
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao iniciar viagem: ' . $e->getMessage());
        }

        $this->redirect('/portal/viagem');
    }

    /**
     * Adicionar Recibo de Combustível / Despesa de Viagem (POST)
     */
    public function addTravelReceipt(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $travel_report_id = intval($_POST['travel_report_id'] ?? 0);
            $supplier_cnpj = trim($_POST['supplier_cnpj'] ?? '');
            $receipt_date = $_POST['receipt_date'] ?? '';
            $value = $this->parseBrlCurrency($_POST['value'] ?? '0');
            $spce_category_id = intval($_POST['spce_category_id'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');

            if ($travel_report_id <= 0 || empty($supplier_cnpj) || empty($receipt_date) || $value <= 0 || $spce_category_id <= 0) {
                throw new Exception("Todos os campos do recibo são obrigatórios.");
            }

            // Valida propriedade da viagem e status
            $stmtCheck = $db->prepare("SELECT id, status FROM `travel_reports` WHERE id = :id AND user_id = :user_id LIMIT 1");
            $stmtCheck->execute(['id' => $travel_report_id, 'user_id' => $user['id']]);
            $report = $stmtCheck->fetch();

            if (!$report) {
                throw new Exception("Relatório de viagem não encontrado ou não pertence a você.");
            }
            if ($report['status'] !== 'EM_ANDAMENTO') {
                throw new Exception("Este relatório já foi enviado ou finalizado.");
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
                throw new Exception("A foto do comprovante fiscal é obrigatória.");
            }

            $storageDir = dirname(__DIR__, 2) . '/storage/uploads';

            // Salva e criptografa a foto principal (primeiro arquivo)
            $mainFile = $allFiles[0];
            $mainCryptoData = EncryptionService::encryptAndSaveUploadedFile($mainFile, $storageDir);

            // Grava o recibo principal
            $stmt = $db->prepare(
                "INSERT INTO `travel_receipts` (travel_report_id, supplier_cnpj, receipt_date, value, spce_category_id, encrypted_file_path, iv, status, notes) 
                 VALUES (:travel_report_id, :supplier_cnpj, :receipt_date, :value, :spce_category_id, :encrypted_file_path, :iv, 'PENDENTE', :notes)"
            );
            $stmt->execute([
                'travel_report_id' => $travel_report_id,
                'supplier_cnpj' => $supplier_cnpj,
                'receipt_date' => $receipt_date,
                'value' => $value,
                'spce_category_id' => $spce_category_id,
                'encrypted_file_path' => $mainCryptoData['encrypted_file_path'],
                'iv' => $mainCryptoData['iv'],
                'notes' => empty($notes) ? null : $notes
            ]);

            // 2. Processa fotos adicionais enviadas (a partir do 2º arquivo)
            $totalArquivos = count($allFiles);
            for ($i = 1; $i < count($allFiles); $i++) {
                $extraFile = $allFiles[$i];
                $extraCrypto = EncryptionService::encryptAndSaveUploadedFile($extraFile, $storageDir);
                $stmtExtra = $db->prepare(
                    "INSERT INTO `travel_receipts` (travel_report_id, supplier_cnpj, receipt_date, value, spce_category_id, encrypted_file_path, iv, status, notes) 
                     VALUES (:travel_report_id, :supplier_cnpj, :receipt_date, :value, :spce_category_id, :encrypted_file_path, :iv, 'PENDENTE', :notes)"
                );
                $stmtExtra->execute([
                    'travel_report_id' => $travel_report_id,
                    'supplier_cnpj' => $supplier_cnpj,
                    'receipt_date' => $receipt_date,
                    'value' => $value,
                    'spce_category_id' => $spce_category_id,
                    'encrypted_file_path' => $extraCrypto['encrypted_file_path'],
                    'iv' => $extraCrypto['iv'],
                    'notes' => 'Foto Adicional do Comprovante (Detalhamento)'
                ]);
            }

            Session::setFlash('success', "Recibo registrado com sucesso! ({$totalArquivos} foto(s) anexada(s))");
            $this->redirect('/portal/viagem?envio_sucesso=1');
            return;
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao adicionar recibo: ' . $e->getMessage());
        }

        $this->redirect('/portal/viagem');
    }

    /**
     * Enviar Relatório de Viagem para Fila de Aprovação (POST)
     */
    public function submitTravelReport(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $id = intval($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception("ID inválido.");
            }

            // Valida propriedade da viagem e status
            $stmtCheck = $db->prepare("SELECT id, status FROM `travel_reports` WHERE id = :id AND user_id = :user_id LIMIT 1");
            $stmtCheck->execute(['id' => $id, 'user_id' => $user['id']]);
            $report = $stmtCheck->fetch();

            if (!$report) {
                throw new Exception("Relatório de viagem não encontrado.");
            }
            if ($report['status'] !== 'EM_ANDAMENTO') {
                throw new Exception("Este relatório já foi enviado.");
            }

            // Verifica se há pelo menos um recibo
            $stmtRecCheck = $db->prepare("SELECT COUNT(*) FROM `travel_receipts` WHERE travel_report_id = :id");
            $stmtRecCheck->execute(['id' => $id]);
            $receiptCount = intval($stmtRecCheck->fetchColumn());

            if ($receiptCount === 0) {
                throw new Exception("Você deve adicionar pelo menos um cupom fiscal/recibo antes de enviar o relatório.");
            }

            // Atualiza status para 'ENVIADO'
            $stmt = $db->prepare("UPDATE `travel_reports` SET status = 'ENVIADO' WHERE id = :id");
            $stmt->execute(['id' => $id]);

            Session::setFlash('success', 'Relatório de viagem enviado com sucesso para auditoria e reembolso!');
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao enviar relatório: ' . $e->getMessage());
        }

        $this->redirect('/portal/viagem');
    }

    /**
     * Tela de Registro de Atividades de Militância (Mobile)
     */
    public function militancy(): void {
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        // Busca atividades recentes do usuário
        $stmt = $db->prepare("SELECT * FROM `militancy_activities` WHERE user_id = :user_id ORDER BY activity_date DESC");
        $stmt->execute(['user_id' => $user['id']]);
        $activities = $stmt->fetchAll();

        $this->render('portal/militancia', [
            'user' => $user,
            'activities' => $activities,
            'csrf_token' => Session::csrfToken()
        ], 'portal');
    }

    /**
     * Lançamento de Atividade de Militância com Georreferenciamento e Criptografia (POST)
     */
    public function addMilitancy(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $description = trim($_POST['description'] ?? '');
            $activity_date = $_POST['activity_date'] ?? '';
            $permitirSemGps = !empty($_POST['permitir_sem_gps']);
            $latitude = floatval($_POST['latitude'] ?? 0);
            $longitude = floatval($_POST['longitude'] ?? 0);

            if (empty($description) || empty($activity_date)) {
                throw new Exception("A descrição e a data da atividade são obrigatórias.");
            }

            if (!$permitirSemGps && ($latitude === 0.0 || $longitude === 0.0)) {
                throw new Exception("As coordenadas GPS são obrigatórias, ou marque a opção de enviar sem GPS.");
            }

            // Auto-cria a tabela de fotos adicionais de militância se não existir
            $db->exec("CREATE TABLE IF NOT EXISTS `militancy_photos` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `militancy_id` INT NOT NULL,
                `encrypted_photo_path` VARCHAR(255) NOT NULL,
                `original_name` VARCHAR(255) NOT NULL,
                `iv` VARCHAR(64) NOT NULL,
                `mime_type` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`militancy_id`) REFERENCES `militancy_activities` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $allFiles = [];

            // 1. Processa arquivos enviados por $_FILES (múltiplos ou simples)
            foreach ($_FILES as $key => $fileInfo) {
                if (empty($fileInfo['name'])) continue;

                if (is_array($fileInfo['name'])) {
                    foreach ($fileInfo['name'] as $idx => $fname) {
                        if (!empty($fname) && isset($fileInfo['error'][$idx]) && $fileInfo['error'][$idx] === UPLOAD_ERR_OK) {
                            $allFiles[] = [
                                'name' => $fname,
                                'type' => $fileInfo['type'][$idx],
                                'tmp_name' => $fileInfo['tmp_name'][$idx],
                                'error' => $fileInfo['error'][$idx],
                                'size' => $fileInfo['size'][$idx]
                            ];
                        }
                    }
                } elseif ($fileInfo['error'] === UPLOAD_ERR_OK && !empty($fileInfo['tmp_name'])) {
                    $allFiles[] = $fileInfo;
                }
            }

            // 2. Se também houver foto compactada via canvas base64
            if (!empty($_POST['foto_base64'])) {
                $base64Data = $_POST['foto_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                    $type = strtolower($matches[1]);
                    $data = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1));
                    if ($data !== false) {
                        $tmpFile = tempnam(sys_get_temp_dir(), 'militancy_');
                        file_put_contents($tmpFile, $data);
                        array_unshift($allFiles, [
                            'tmp_name' => $tmpFile,
                            'name' => 'militancia_foto_' . time() . '.' . $type,
                            'type' => 'image/' . $type,
                            'error' => UPLOAD_ERR_OK,
                            'size' => strlen($data),
                            'is_tmp' => true
                        ]);
                    }
                }
            }

            if (empty($allFiles)) {
                throw new Exception("O envio de pelo menos uma foto de comprovação é obrigatório.");
            }

            $storageDir = dirname(__DIR__, 2) . '/storage/uploads';

            // Primeira foto é salva em militancy_activities
            $mainFile = $allFiles[0];
            $mainCryptoData = EncryptionService::encryptAndSaveUploadedFile($mainFile, $storageDir);
            if (!empty($mainFile['is_tmp'])) {
                @unlink($mainFile['tmp_name']);
            }

            // Insere no banco
            $stmt = $db->prepare(
                "INSERT INTO `militancy_activities` (user_id, activity_date, description, encrypted_photo_path, iv, latitude, longitude, status) 
                 VALUES (:user_id, :activity_date, :description, :encrypted_photo_path, :iv, :latitude, :longitude, 'PENDENTE')"
            );
            $stmt->execute([
                'user_id' => $user['id'],
                'activity_date' => $activity_date,
                'description' => $description,
                'encrypted_photo_path' => $mainCryptoData['encrypted_file_path'],
                'iv' => $mainCryptoData['iv'],
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);
            $militancyId = $db->lastInsertId();

            // Grava demais fotos em militancy_photos se houver mais de 1 arquivo
            if (count($allFiles) > 1) {
                $stmtExtra = $db->prepare(
                    "INSERT INTO `militancy_photos` (militancy_id, encrypted_photo_path, original_name, iv, mime_type) 
                     VALUES (:militancy_id, :encrypted_photo_path, :original_name, :iv, :mime_type)"
                );
                for ($i = 1; $i < count($allFiles); $i++) {
                    $extraFile = $allFiles[$i];
                    $extraCrypto = EncryptionService::encryptAndSaveUploadedFile($extraFile, $storageDir);
                    if (!empty($extraFile['is_tmp'])) {
                        @unlink($extraFile['tmp_name']);
                    }
                    $stmtExtra->execute([
                        'militancy_id' => $militancyId,
                        'encrypted_photo_path' => $extraCrypto['encrypted_file_path'],
                        'original_name' => $extraCrypto['original_name'],
                        'iv' => $extraCrypto['iv'],
                        'mime_type' => $extraCrypto['mime_type']
                    ]);
                }
            }

            $countArquivos = count($allFiles);
            $msgSucesso = $permitirSemGps 
                ? "Atividade de panfletagem ({$countArquivos} foto(s)) registrada com sucesso para validação manual (sem GPS)!" 
                : "Atividade de panfletagem ({$countArquivos} foto(s)) registrada com sucesso com foto criptografada e GPS!";
            Session::setFlash('success', $msgSucesso);
            $this->redirect('/portal/militancia?envio_sucesso=1');
            return;
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao salvar atividade: ' . $e->getMessage());
        }

        $this->redirect('/portal/militancia');
    }

    /**
     * Tela de despesas cadastradas pelo próprio militante (Mobile)
     */
    public function expenses(): void {
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        // Busca todas as despesas lançadas pelo próprio colaborador com o nome do tipo
        $stmt = $db->prepare(
            "SELECT d.*, s.corporate_name AS supplier_name, s.cnpj_cpf AS supplier_cnpj_cpf, cc.doc_id, et.name AS expense_type_name
             FROM `despesas` d
             LEFT JOIN `suppliers` s ON d.supplier_id = s.id
             LEFT JOIN (SELECT expense_id, MIN(id) AS doc_id FROM `comprovantes_cripto` GROUP BY expense_id) cc ON d.id = cc.expense_id
             LEFT JOIN `expense_types` et ON d.expense_type_id = et.id
             WHERE d.user_id = :user_id
             ORDER BY d.created_at DESC"
        );
        $stmt->execute(['user_id' => $user['id']]);
        $expenses = $stmt->fetchAll();

        // Busca todos os tipos de despesas cadastrados
        $expenseTypes = $db->query("SELECT * FROM `expense_types` ORDER BY name ASC")->fetchAll();

        // Estatísticas pessoais (sem expor o saldo ou limites gerais da campanha)
        $totalLaunched = 0.0;
        $pendingCount = 0;
        $approvedCount = 0;
        $rejectedCount = 0;

        foreach ($expenses as $exp) {
            $totalLaunched += floatval($exp['value']);
            if ($exp['status'] === 'PENDENTE') {
                $pendingCount++;
            } elseif ($exp['status'] === 'APROVADO' || $exp['status'] === 'PAGO') {
                $approvedCount++;
            } elseif ($exp['status'] === 'REJEITADO') {
                $rejectedCount++;
            }
        }

        $this->render('portal/despesas', [
            'user' => $user,
            'expenses' => $expenses,
            'expenseTypes' => $expenseTypes,
            'totalLaunched' => $totalLaunched,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'csrf_token' => Session::csrfToken()
        ], 'portal');
    }

    /**
     * Correção e reenvio de gasto pelo colaborador (POST)
     */
    public function updateExpense(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $expense_id = intval($_POST['expense_id'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $supplier_cnpj_cpf = preg_replace('/\D/', '', $_POST['supplier_cnpj_cpf'] ?? '');
            $supplier_name = trim($_POST['supplier_name'] ?? '');
            $value = $this->parseBrlCurrency($_POST['value'] ?? '0');
            $date_incurred = $_POST['date_incurred'] ?? '';
            $expense_type_id = (int)($_POST['expense_type_id'] ?? 0);

            if ($expense_id <= 0 || empty($description) || empty($supplier_cnpj_cpf) || empty($supplier_name) || $value <= 0 || empty($date_incurred) || $expense_type_id <= 0) {
                throw new Exception("Todos os campos do gasto são obrigatórios para a correção.");
            }

            // Verifica se a despesa pertence ao colaborador
            $stmtCheck = $db->prepare("SELECT id, status FROM `despesas` WHERE id = :id AND user_id = :user_id LIMIT 1");
            $stmtCheck->execute(['id' => $expense_id, 'user_id' => $user['id']]);
            $exp = $stmtCheck->fetch();

            if (!$exp) {
                throw new Exception("Despesa não encontrada ou você não tem permissão para alterá-la.");
            }

            $db->beginTransaction();

            // Cadastra/Obtém fornecedor pelo CNPJ/CPF
            $stmtSupplier = $db->prepare("SELECT id FROM `suppliers` WHERE cnpj_cpf = :cnpj_cpf LIMIT 1");
            $stmtSupplier->execute(['cnpj_cpf' => $supplier_cnpj_cpf]);
            $supplier = $stmtSupplier->fetch();

            if ($supplier) {
                $supplierId = $supplier['id'];
                $stmtUpSup = $db->prepare("UPDATE `suppliers` SET corporate_name = :name WHERE id = :id");
                $stmtUpSup->execute(['name' => $supplier_name, 'id' => $supplierId]);
            } else {
                $stmtInsertSupplier = $db->prepare(
                    "INSERT INTO `suppliers` (cnpj_cpf, corporate_name, status) 
                     VALUES (:cnpj_cpf, :corporate_name, 'ATIVO')"
                );
                $stmtInsertSupplier->execute([
                    'cnpj_cpf' => $supplier_cnpj_cpf,
                    'corporate_name' => $supplier_name
                ]);
                $supplierId = $db->lastInsertId();
            }

            // Atualiza os dados da despesa e reverte o status de REJEITADO para PENDENTE
            $stmtUpdate = $db->prepare(
                "UPDATE `despesas` 
                 SET description = :description, 
                     supplier_id = :supplier_id, 
                     value = :value, 
                     date_incurred = :date_incurred, 
                     expense_type_id = :expense_type_id, 
                     status = 'PENDENTE', 
                     notes = CONCAT('Corrigido pelo colaborador em ', DATE_FORMAT(NOW(), '%d/%m/%Y %H:%i')) 
                 WHERE id = :id"
            );
            $stmtUpdate->execute([
                'description' => $description,
                'supplier_id' => $supplierId,
                'value' => $value,
                'date_incurred' => $date_incurred,
                'expense_type_id' => $expense_type_id,
                'id' => $expense_id
            ]);

            // Se foi anexado um novo comprovante/foto
            if (isset($_FILES['novo_comprovante']) && $_FILES['novo_comprovante']['error'] === UPLOAD_ERR_OK && !empty($_FILES['novo_comprovante']['tmp_name'])) {
                $storageDir = dirname(__DIR__, 2) . '/storage/uploads';
                $cryptoData = EncryptionService::encryptAndSaveUploadedFile($_FILES['novo_comprovante'], $storageDir);

                $stmtCrypto = $db->prepare(
                    "INSERT INTO `comprovantes_cripto` (expense_id, encrypted_file_path, original_name, iv, mime_type) 
                     VALUES (:expense_id, :encrypted_file_path, :original_name, :iv, :mime_type)"
                );
                $stmtCrypto->execute([
                    'expense_id' => $expense_id,
                    'encrypted_file_path' => $cryptoData['encrypted_file_path'],
                    'original_name' => $cryptoData['original_name'],
                    'iv' => $cryptoData['iv'],
                    'mime_type' => $cryptoData['mime_type']
                ]);
            }

            // Log
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (:user_id, 'FIELD_EXPENSE_CORRECT', 'despesas', :record_id, :new_values, :ip_address, :user_agent)"
            );
            $stmtLog->execute([
                'user_id' => $user['id'],
                'record_id' => $expense_id,
                'new_values' => json_encode(['description' => $description, 'value' => $value], JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            $db->commit();
            Session::setFlash('success', 'Gasto corrigido e reenviado para a Fila de Aprovação com sucesso!');
            $this->redirect('/portal/despesas?envio_sucesso=1');
            return;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Session::setFlash('error', 'Erro ao corrigir gasto: ' . $e->getMessage());
        }

        $this->redirect('/portal/despesas');
    }

    /**
     * Lançamento de despesa pelo militante (POST)
     */
    public function addExpense(): void {
        $this->validatePostCsrf();
        $db = Database::getInstance();
        $user = $this->getLoggedUser();

        try {
            $description = trim($_POST['description'] ?? '');
            $supplier_cnpj_cpf = preg_replace('/\D/', '', $_POST['supplier_cnpj_cpf'] ?? '');
            $supplier_name = trim($_POST['supplier_name'] ?? '');
            $value = $this->parseBrlCurrency($_POST['value'] ?? '0');
            $date_incurred = $_POST['date_incurred'] ?? '';
            $expense_type_id = (int)($_POST['expense_type_id'] ?? 0);

            if (empty($description) || empty($supplier_cnpj_cpf) || empty($supplier_name) || $value <= 0 || empty($date_incurred) || $expense_type_id <= 0) {
                throw new Exception("Todos os campos do gasto, incluindo o Tipo de Gasto, são obrigatórios.");
            }

            $stmtCheckType = $db->prepare("SELECT id FROM `expense_types` WHERE id = :id LIMIT 1");
            $stmtCheckType->execute(['id' => $expense_type_id]);
            if (!$stmtCheckType->fetch()) {
                throw new Exception("O tipo de despesa selecionado é inválido.");
            }

            $db->beginTransaction();

            // 1. Verifica se fornecedor já existe pelo CNPJ/CPF, se não, cadastra automático
            $stmtSupplier = $db->prepare("SELECT id FROM `suppliers` WHERE cnpj_cpf = :cnpj_cpf LIMIT 1");
            $stmtSupplier->execute(['cnpj_cpf' => $supplier_cnpj_cpf]);
            $supplier = $stmtSupplier->fetch();

            if ($supplier) {
                $supplierId = $supplier['id'];
            } else {
                $stmtInsertSupplier = $db->prepare(
                    "INSERT INTO `suppliers` (cnpj_cpf, corporate_name, status) 
                     VALUES (:cnpj_cpf, :corporate_name, 'ATIVO')"
                );
                $stmtInsertSupplier->execute([
                    'cnpj_cpf' => $supplier_cnpj_cpf,
                    'corporate_name' => $supplier_name
                ]);
                $supplierId = $db->lastInsertId();
            }

            // 2. Processa a foto principal do cabeçalho com CNPJ (passada no 1º Passo para OCR)
            $mainFile = null;
            if (isset($_FILES['comprovante'])) {
                if (is_array($_FILES['comprovante']['name'])) {
                    if ($_FILES['comprovante']['error'][0] === UPLOAD_ERR_OK && !empty($_FILES['comprovante']['tmp_name'][0])) {
                        $mainFile = [
                            'name' => $_FILES['comprovante']['name'][0],
                            'type' => $_FILES['comprovante']['type'][0],
                            'tmp_name' => $_FILES['comprovante']['tmp_name'][0],
                            'error' => $_FILES['comprovante']['error'][0],
                            'size' => $_FILES['comprovante']['size'][0]
                        ];
                    }
                } elseif ($_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
                    $mainFile = $_FILES['comprovante'];
                }
            }

            if (!$mainFile) {
                throw new Exception("A foto do cabeçalho com o CNPJ do comprovante é obrigatória.");
            }

            $storageDir = dirname(__DIR__, 2) . '/storage/uploads';
            $mainCryptoData = EncryptionService::encryptAndSaveUploadedFile($mainFile, $storageDir);

            // 3. Cadastra a despesa
            $stmtExpense = $db->prepare(
                "INSERT INTO `despesas` (description, supplier_id, bank_account_id, value, date_incurred, payment_method, status, spce_category_id, user_id, expense_type_id) 
                 VALUES (:description, :supplier_id, NULL, :value, :date_incurred, NULL, 'PENDENTE', NULL, :user_id, :expense_type_id)"
            );
            $stmtExpense->execute([
                'description' => $description,
                'supplier_id' => $supplierId,
                'value' => $value,
                'date_incurred' => $date_incurred,
                'user_id' => $user['id'],
                'expense_type_id' => $expense_type_id
            ]);
            $expenseId = $db->lastInsertId();

            // 4. Cadastra a 1ª foto (cabeçalho/CNPJ) em comprovantes_cripto
            $stmtCrypto = $db->prepare(
                "INSERT INTO `comprovantes_cripto` (expense_id, encrypted_file_path, original_name, iv, mime_type) 
                 VALUES (:expense_id, :encrypted_file_path, :original_name, :iv, :mime_type)"
            );
            $stmtCrypto->execute([
                'expense_id' => $expenseId,
                'encrypted_file_path' => $mainCryptoData['encrypted_file_path'],
                'original_name' => $mainCryptoData['original_name'],
                'iv' => $mainCryptoData['iv'],
                'mime_type' => $mainCryptoData['mime_type']
            ]);

            // 5. Processa fotos adicionais enviadas (sem OCR)
            if (isset($_FILES['fotos_adicionais']) && is_array($_FILES['fotos_adicionais']['name'])) {
                foreach ($_FILES['fotos_adicionais']['name'] as $idx => $fname) {
                    if ($_FILES['fotos_adicionais']['error'][$idx] === UPLOAD_ERR_OK && !empty($_FILES['fotos_adicionais']['tmp_name'][$idx])) {
                        $extraFile = [
                            'name' => $fname,
                            'type' => $_FILES['fotos_adicionais']['type'][$idx],
                            'tmp_name' => $_FILES['fotos_adicionais']['tmp_name'][$idx],
                            'error' => $_FILES['fotos_adicionais']['error'][$idx],
                            'size' => $_FILES['fotos_adicionais']['size'][$idx]
                        ];
                        $extraCrypto = EncryptionService::encryptAndSaveUploadedFile($extraFile, $storageDir);
                        $stmtCrypto->execute([
                            'expense_id' => $expenseId,
                            'encrypted_file_path' => $extraCrypto['encrypted_file_path'],
                            'original_name' => $extraCrypto['original_name'],
                            'iv' => $extraCrypto['iv'],
                            'mime_type' => $extraCrypto['mime_type']
                        ]);
                    }
                }
            }

            // 5. Registra na auditoria
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (:user_id, 'FIELD_EXPENSE_CREATE', 'despesas', :record_id, :new_values, :ip_address, :user_agent)"
            );
            $stmtLog->execute([
                'user_id' => $user['id'],
                'record_id' => $expenseId,
                'new_values' => json_encode([
                    'description' => $description,
                    'value' => $value,
                    'supplier_cnpj_cpf' => $supplier_cnpj_cpf
                ], JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            $db->commit();
            Session::setFlash('success', 'Gasto cadastrado com comprovante enviado com sucesso!');
            $this->redirect('/portal/despesas?envio_sucesso=1');
            return;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Session::setFlash('error', 'Erro ao cadastrar gasto: ' . $e->getMessage());
        }

        $this->redirect('/portal/despesas');
    }

    /**
     * Endpoint API JSON para consulta pública de CNPJ.
     */
    public function consultarCnpj(): void {
        header('Content-Type: application/json; charset=utf-8');
        $cnpj = $_GET['cnpj'] ?? $_POST['cnpj'] ?? '';

        if (empty($cnpj)) {
            echo json_encode(['success' => false, 'message' => 'Informe o número do CNPJ.']);
            exit;
        }

        $result = CnpjService::consultar($cnpj);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
