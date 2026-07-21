<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\Role;
use App\Services\AuditLogger;
use App\Services\WhatsAppService;
use App\Services\EmailService;
use App\Core\Database;
use PDO;

/**
 * Controlador da Área Administrativa e Módulos de Usuários/RBAC.
 */
class AdminController extends Controller {
    public function __construct() {
        $user = $this->requireAuth();
        if (($user['role_name'] ?? '') === 'COLABORADOR_CAMPO') {
            Session::setFlash('error', 'Acesso negado à área administrativa.');
            $this->redirect('/portal');
        }
    }

    /**
     * Dashboard Principal.
     */
    public function dashboard(): void {
        $user = $this->requireAuth();

        // Dados do painel (SGE Dashboard)
        $db = Database::getInstance();

        // 1. Contador de Usuários por status
        $statusCounts = $db->query(
            "SELECT status, COUNT(*) as total FROM `usuarios` GROUP BY status"
        )->fetchAll();
        
        $stats = ['ATIVO' => 0, 'PENDENTE' => 0, 'INATIVO' => 0];
        foreach ($statusCounts as $row) {
            $stats[$row['status']] = (int)$row['total'];
        }

        // 2. Últimos 5 Logs de Auditoria para exibição no painel
        $stmt = $db->query(
            "SELECT l.*, u.name as user_name 
             FROM `logs_auditoria` l 
             LEFT JOIN `usuarios` u ON l.user_id = u.id 
             ORDER BY l.id DESC LIMIT 5"
        );
        $recentLogs = $stmt->fetchAll();

        // 3. Contagem dinâmica de comprovantes e relatórios pendentes de homologação
        $pendingDespesas = (int)$db->query("SELECT COUNT(*) FROM `despesas` WHERE `status` = 'PENDENTE'")->fetchColumn();
        $pendingMilitancy = (int)$db->query("SELECT COUNT(*) FROM `militancy_activities` WHERE `status` = 'PENDENTE'")->fetchColumn();
        $totalPending = $pendingDespesas + $pendingMilitancy;

        // 4. Buscar Configurações da Campanha (Cargo, UF, Limite de Gastos)
        $campaignSettings = $db->query("SELECT * FROM `campaign_settings` WHERE id = 1")->fetch();
        if (!$campaignSettings) {
            $campaignSettings = [
                'candidate_name' => 'Candidato SGE',
                'electoral_role' => 'Deputado Federal',
                'uf'             => 'DF',
                'spending_limit' => 3168878.60
            ];
        }

        // 5. KPIs Financeiros Calculados Dinamicamente do Banco de Dados
        $caixaTotal = floatval($db->query("SELECT COALESCE(SUM(balance), 0) FROM `bank_accounts` WHERE status = 'ATIVA'")->fetchColumn());
        $fefc       = floatval($db->query("SELECT COALESCE(SUM(balance), 0) FROM `bank_accounts` WHERE fund_type = 'FEFC' AND status = 'ATIVA'")->fetchColumn());
        $fundoPart  = floatval($db->query("SELECT COALESCE(SUM(balance), 0) FROM `bank_accounts` WHERE fund_type = 'FUNDO_PARTIDARIO' AND status = 'ATIVA'")->fetchColumn());
        $outros     = floatval($db->query("SELECT COALESCE(SUM(balance), 0) FROM `bank_accounts` WHERE fund_type = 'OUTROS_RECURSOS' AND status = 'ATIVA'")->fetchColumn());

        // Gastos Aprovados ou Pagos (Despesas + Recibos de Viagem)
        $totalDespesas = floatval($db->query("SELECT COALESCE(SUM(value), 0) FROM `despesas` WHERE status IN ('APROVADO', 'PAGO')")->fetchColumn());
        $totalTravel   = floatval($db->query("SELECT COALESCE(SUM(value), 0) FROM `travel_receipts` WHERE status = 'APROVADO'")->fetchColumn());
        $gastoAtual    = $totalDespesas + $totalTravel;

        $limiteGastos  = floatval($campaignSettings['spending_limit']);
        $gastoPercent  = ($limiteGastos > 0) ? min(100, round(($gastoAtual / $limiteGastos) * 100, 2)) : 0;

        $kpiFinances = [
            'caixa_total'        => $caixaTotal,
            'fefc'               => $fefc,
            'fundo_part'         => $fundoPart,
            'outros'             => $outros,
            'limite_gastos'      => $limiteGastos,
            'gasto_atual'        => $gastoAtual,
            'gasto_percent'      => $gastoPercent,
            'pendente_aprovacao' => $totalPending,
            'electoral_role'     => $campaignSettings['electoral_role'],
            'uf'                 => $campaignSettings['uf'],
            'candidate_name'     => $campaignSettings['candidate_name']
        ];

        $this->render('admin/dashboard', [
            'user'             => $user,
            'stats'            => $stats,
            'recentLogs'       => $recentLogs,
            'kpis'             => $kpiFinances,
            'campaignSettings' => $campaignSettings,
            'csrf_token'       => Session::csrfToken()
        ]);
    }

    /**
     * Atualiza as configurações de Cargo Eleitoral, UF e Limite de Gastos da Campanha.
     */
    public function updateCampaignSettings(): void {
        $user = $this->requireAuth();
        $this->validatePostCsrf();

        if ($user['role_name'] !== 'ADMINISTRADOR' && $user['role_name'] !== 'FINANCEIRO') {
            Session::setFlash('error', 'Apenas Administradores ou Financeiro podem alterar as configurações da campanha.');
            $this->redirect('/admin/dashboard');
        }

        try {
            $electoral_role = trim($_POST['electoral_role'] ?? '');
            $uf = strtoupper(trim($_POST['uf'] ?? 'DF'));
            $spending_limit = $this->parseBrlCurrency($_POST['spending_limit'] ?? '0');
            $candidate_name = trim($_POST['candidate_name'] ?? '');

            if (empty($electoral_role) || empty($uf) || $spending_limit <= 0) {
                throw new \Exception("Cargo Eleitoral, UF e Limite de Gastos válido são obrigatórios.");
            }

            $db = Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO `campaign_settings` (id, candidate_name, electoral_role, uf, spending_limit) 
                 VALUES (1, :candidate_name, :electoral_role, :uf, :spending_limit)
                 ON DUPLICATE KEY UPDATE 
                 candidate_name = VALUES(candidate_name),
                 electoral_role = VALUES(electoral_role),
                 uf = VALUES(uf),
                 spending_limit = VALUES(spending_limit)"
            );
            $stmt->execute([
                'candidate_name' => !empty($candidate_name) ? $candidate_name : 'Candidato SGE',
                'electoral_role' => $electoral_role,
                'uf' => $uf,
                'spending_limit' => $spending_limit
            ]);

            // Registra log de auditoria
            $stmtLog = $db->prepare(
                "INSERT INTO `logs_auditoria` (user_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                 VALUES (:user_id, 'UPDATE_CAMPAIGN_SETTINGS', 'campaign_settings', 1, :new_values, :ip_address, :user_agent)"
            );
            $stmtLog->execute([
                'user_id' => $user['id'],
                'new_values' => json_encode($_POST, JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            Session::setFlash('success', 'Configurações de Cargo Eleitoral e Limite de Gastos atualizadas com sucesso!');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? $this->baseUrl('admin/dashboard');
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Tela de Gerenciamento de Usuários.
     */
    public function users(): void {
        $this->requirePermission('invite_user');
        $user = $this->requireAuth();

        // Filtros de pesquisa
        $filters = [
            'name'    => $_GET['name'] ?? '',
            'role_id' => $_GET['role_id'] ?? '',
            'status'  => $_GET['status'] ?? ''
        ];

        $userModel = new User();
        $usersList = $userModel->getUsersWithRoles($filters);

        $roleModel = new Role();
        $rolesList = $roleModel->all();

        // Links de Click-to-Chat armazenados em sessão após um convite bem-sucedido
        $inviteSuccess = Session::getFlash('invite_success_details');

        $this->render('admin/users', [
            'user'          => $user,
            'users'         => $usersList,
            'roles'         => $rolesList,
            'filters'       => $filters,
            'inviteSuccess' => $inviteSuccess,
            'csrf_token'    => Session::csrfToken(),
            'error'         => Session::getFlash('error'),
            'success'       => Session::getFlash('success')
        ]);
    }

    /**
     * Processa o envio de um convite de usuário.
     */
    public function invite(): void {
        $this->requirePermission('invite_user');
        $this->validatePostCsrf();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $celular = trim($_POST['celular'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);

        if ($name === '' || $email === '' || $celular === '' || $roleId === 0) {
            Session::setFlash('error', 'Preencha todos os campos do convite.');
            $this->redirect('/admin/users');
        }

        $userModel = new User();
        
        // Verifica se o e-mail já existe
        if ($userModel->findByEmail($email) !== null) {
            Session::setFlash('error', 'Este e-mail já está cadastrado no sistema.');
            $this->redirect('/admin/users');
        }

        try {
            // Cria o convite no banco e obtém o token bruto
            $token = $userModel->createInvite([
                'name'    => $name,
                'email'   => $email,
                'celular' => $celular,
                'role_id' => $roleId
            ]);

            $activationLink = $this->baseUrl("ativar?token={$token}");
            
            // Mensagem do convite personalizada
            $message = "Olá, {$name}! Você foi convidado para integrar a equipe do Sistema de Gestão Eleitoral (SGE). Para ativar seu acesso de forma segura, acesse o link a seguir e crie sua senha forte: {$activationLink} (Válido por 24 horas).";

            // 1. Envia silenciosamente via Z-API
            $zapiSent = WhatsAppService::send($celular, $message);

            // 2. Envia silenciosamente via E-mail simulado
            $emailSent = EmailService::send($email, "Convite de Acesso - Sistema de Gestão Eleitoral (SGE)", $message);

            // 3. Gera o Click-to-Chat para envio manual
            $clickToChatUrl = WhatsAppService::generateClickToChat($celular, $message);

            // Armazena detalhes na sessão para exibição no modal de confirmação no frontend
            Session::setFlash('invite_success_details', [
                'name'             => $name,
                'email'            => $email,
                'celular'          => $celular,
                'activation_link'  => $activationLink,
                'click_to_chat'    => $clickToChatUrl,
                'zapi_sent'        => $zapiSent,
                'email_sent'       => $emailSent
            ]);

            Session::setFlash('success', 'Convite gerado com sucesso!');
        } catch (\Exception $e) {
            error_log("Erro no convite de usuário: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao processar convite de usuário.');
        }

        $this->redirect('/admin/users');
    }

    /**
     * Processa a criação direta de um usuário do sistema (ativo).
     */
    public function create(): void {
        $this->requirePermission('invite_user');
        $this->validatePostCsrf();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $celular = trim($_POST['celular'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);
        $password = $_POST['password'] ?? '';

        if ($name === '' || $email === '' || $celular === '' || $roleId === 0 || $password === '') {
            Session::setFlash('error', 'Preencha todos os campos para cadastrar o usuário.');
            $this->redirect('/admin/users');
        }

        // Valida força da senha
        if (strlen($password) < 8) {
            Session::setFlash('error', 'A senha deve possuir no mínimo 8 caracteres.');
            $this->redirect('/admin/users');
        }

        if (!preg_match('/[A-Z]/', $password) || 
            !preg_match('/[a-z]/', $password) || 
            !preg_match('/[0-9]/', $password) || 
            !preg_match('/[^a-zA-Z0-9]/', $password)) {
            Session::setFlash('error', 'A senha deve conter pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial (ex: @, #, $, _, !).');
            $this->redirect('/admin/users');
        }

        $userModel = new User();
        
        // Verifica se o e-mail já existe
        if ($userModel->findByEmail($email) !== null) {
            Session::setFlash('error', 'Este e-mail já está cadastrado no sistema.');
            $this->redirect('/admin/users');
        }

        try {
            $userModel->createDirect([
                'name'     => $name,
                'email'    => $email,
                'celular'  => $celular,
                'password' => $password,
                'role_id'  => $roleId
            ]);

            Session::setFlash('success', "Usuário <strong>" . htmlspecialchars($name) . "</strong> cadastrado com sucesso!");
        } catch (\Exception $e) {
            error_log("Erro no cadastro direto de usuário: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao cadastrar usuário.');
        }

        $this->redirect('/admin/users');
    }

    /**
     * Configuração de Perfis e Permissões (RBAC).
     */
    public function rbac(): void {
        $this->requirePermission('configure_rbac');
        $user = $this->requireAuth();

        $roleModel = new Role();
        $rolesList = $roleModel->all();

        // Formata as permissões lidas do JSON para exibição
        foreach ($rolesList as &$role) {
            $role['permissions'] = json_decode($role['permissions'], true) ?? [];
        }

        // Lista de todas as chaves de permissões conhecidas no sistema
        $availablePermissions = [
            'invite_user'    => 'Convidar e Gerenciar Usuários',
            'configure_rbac' => 'Configurar Permissões de Cargos (RBAC)',
            'edit_profile'   => 'Editar Próprio Perfil'
        ];

        $this->render('admin/rbac', [
            'user'                 => $user,
            'roles'                => $rolesList,
            'availablePermissions' => $availablePermissions,
            'csrf_token'           => Session::csrfToken(),
            'error'                => Session::getFlash('error'),
            'success'              => Session::getFlash('success')
        ]);
    }

    /**
     * Atualiza as permissões das roles no banco.
     */
    public function updateRbac(): void {
        $this->requirePermission('configure_rbac');
        $this->validatePostCsrf();

        $postedPermissions = $_POST['permissions'] ?? [];
        $roleModel = new Role();
        $rolesList = $roleModel->all();

        // Chaves de permissões conhecidas
        $availableKeys = ['invite_user', 'configure_rbac', 'edit_profile'];

        foreach ($rolesList as $role) {
            $roleId = (int)$role['id'];
            
            // Monta o novo array de permissões com base no que foi marcado no formulário
            $newPermissions = [];
            foreach ($availableKeys as $key) {
                $newPermissions[$key] = isset($postedPermissions[$roleId][$key]) && $postedPermissions[$roleId][$key] == '1';
            }

            // Salva no banco de dados com log de auditoria automático
            $roleModel->updatePermissions($roleId, $newPermissions);
        }

        // Se o cargo do próprio usuário ativo foi atualizado, vamos recarregar a sessão dele para aplicar as mudanças de imediato
        $loggedUser = $this->getLoggedUser();
        $userModel = new User();
        $refreshedUser = $userModel->findWithRole($loggedUser['id']);
        if ($refreshedUser) {
            Session::set('user', [
                'id'               => $refreshedUser['id'],
                'name'             => $refreshedUser['name'],
                'email'            => $refreshedUser['email'],
                'celular'          => $refreshedUser['celular'],
                'role_id'          => $refreshedUser['role_id'],
                'role_name'        => $refreshedUser['role_name'],
                'role_permissions' => $refreshedUser['role_permissions'],
                'profile_photo'    => $refreshedUser['profile_photo_path']
            ]);
        }

        Session::setFlash('success', 'Permissões dos cargos atualizadas com sucesso.');
        $this->redirect('/admin/rbac');
    }

    /**
     * Tela de Edição do Próprio Perfil.
     */
    public function profile(): void {
        $user = $this->requireAuth();

        $userModel = new User();
        $userDetails = $userModel->findWithRole($user['id']);

        $this->render('admin/profile', [
            'user'       => $user,
            'details'    => $userDetails,
            'userFull'   => $userDetails,
            'csrf_token' => Session::csrfToken(),
            'error'      => Session::getFlash('error'),
            'success'    => Session::getFlash('success')
        ]);
    }

    public function updateProfile(): void {
        $user = $this->requireAuth();
        $this->validatePostCsrf();

        $name = trim($_POST['name'] ?? '');
        $celular = preg_replace('/\D/', '', $_POST['celular'] ?? '');
        
        if ($name === '' || $celular === '') {
            Session::setFlash('error', 'Nome e Celular são campos obrigatórios.');
            $this->redirect('/admin/profile');
        }

        // Busca os dados atuais do usuário no banco
        $userModel = new User();
        $userDb = $userModel->find($user['id']);

        if (!$userDb) {
            Session::setFlash('error', 'Usuário não encontrado.');
            $this->redirect('/admin/profile');
        }

        $updateData = [
            'name'    => $name,
            'celular' => $celular
        ];

        // 1. Alteração de Senha Segura
        $novaSenha = $_POST['nova_senha'] ?? '';
        if (!empty($novaSenha)) {
            $senhaAtual = $_POST['senha_atual'] ?? '';
            if (empty($senhaAtual)) {
                Session::setFlash('error', 'Para alterar a senha, você deve informar a senha atual.');
                $this->redirect('/admin/profile');
            }

            if (!password_verify($senhaAtual, $userDb['password_hash'])) {
                Session::setFlash('error', 'A senha atual informada está incorreta.');
                $this->redirect('/admin/profile');
            }

            if (strlen($novaSenha) < 8) {
                Session::setFlash('error', 'A nova senha deve possuir no mínimo 8 caracteres.');
                $this->redirect('/admin/profile');
            }

            if (!preg_match('/[A-Z]/', $novaSenha) || 
                !preg_match('/[a-z]/', $novaSenha) || 
                !preg_match('/[0-9]/', $novaSenha) || 
                !preg_match('/[^a-zA-Z0-9]/', $novaSenha)) {
                Session::setFlash('error', 'A nova senha deve conter pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial (ex: @, #, $, _, !).');
                $this->redirect('/admin/profile');
            }

            $confirmarSenha = $_POST['confirmar_senha'] ?? '';
            if ($novaSenha !== $confirmarSenha) {
                Session::setFlash('error', 'A confirmação da nova senha não confere.');
                $this->redirect('/admin/profile');
            }

            $updateData['password'] = $novaSenha;
        }

        // 2. Processa upload de foto nova (opcional)
        if (isset($_FILES['foto_rosto']) && $_FILES['foto_rosto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadError = $_FILES['foto_rosto']['error'];
            if ($uploadError !== UPLOAD_ERR_OK) {
                $errorsList = [
                    UPLOAD_ERR_INI_SIZE   => 'O arquivo enviado excede o limite máximo permitido pelo servidor (php.ini).',
                    UPLOAD_ERR_FORM_SIZE  => 'O arquivo excede o limite máximo do formulário.',
                    UPLOAD_ERR_PARTIAL    => 'O upload foi concluído apenas parcialmente.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário ausente no servidor.',
                    UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar arquivo em disco no servidor.',
                    UPLOAD_ERR_EXTENSION  => 'O upload foi interrompido por uma extensão do PHP.'
                ];
                $errorMsg = $errorsList[$uploadError] ?? 'Erro desconhecido (' . $uploadError . ').';
                Session::setFlash('error', 'Erro no upload da foto de perfil: ' . $errorMsg);
                $this->redirect('/admin/profile');
            }

            $fileTmpPath = $_FILES['foto_rosto']['tmp_name'];
            $fileName = $_FILES['foto_rosto']['name'];
            $fileSize = $_FILES['foto_rosto']['size'];
            
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);

            if (!in_array($fileExtension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
                Session::setFlash('error', 'Tipo de arquivo inválido para foto de perfil.');
                $this->redirect('/admin/profile');
            }

            if ($fileSize > 10 * 1024 * 1024) {
                Session::setFlash('error', 'A foto de perfil não pode exceder o limite de 10MB.');
                $this->redirect('/admin/profile');
            }

            $newFileName = md5(uniqid('profile_', true)) . '.' . $fileExtension;
            $uploadFileDir = dirname(__DIR__, 2) . '/public/uploads/profiles/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $destPath = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $updateData['profile_photo_path'] = 'uploads/profiles/' . $newFileName;
                
                // Remove a foto antiga física se existir e for diferente do default
                if (!empty($userDb['profile_photo_path'])) {
                    $oldFilePath = dirname(__DIR__, 2) . '/public/' . $userDb['profile_photo_path'];
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
            }
        }

        // Executa a atualização
        if ($userModel->updateProfileInfo($user['id'], $updateData)) {
            // Se o usuário também for um colaborador, sincroniza seus dados na tabela `colaboradores`
            $db = Database::getInstance();
            $stmtColab = $db->prepare("SELECT id FROM `colaboradores` WHERE usuario_id = :usuario_id LIMIT 1");
            $stmtColab->execute(['usuario_id' => $user['id']]);
            $colabExists = $stmtColab->fetch();

            if ($colabExists) {
                $syncColabSql = "UPDATE `colaboradores` SET celular_whatsapp = :celular";
                $syncParams = ['celular' => $celular, 'usuario_id' => $user['id']];
                if (isset($updateData['profile_photo_path'])) {
                    $syncColabSql .= ", foto_rosto_path = :photo";
                    $syncParams['photo'] = $updateData['profile_photo_path'];
                }
                $syncColabSql .= " WHERE usuario_id = :usuario_id";
                
                $stmtUpdateColab = $db->prepare($syncColabSql);
                $stmtUpdateColab->execute($syncParams);
            }

            // Atualiza sessão com os novos dados
            $refreshed = $userModel->findWithRole($user['id']);
            Session::set('user', [
                'id'               => $refreshed['id'],
                'name'             => $refreshed['name'],
                'email'            => $refreshed['email'],
                'celular'          => $refreshed['celular'],
                'role_id'          => $refreshed['role_id'],
                'role_name'        => $refreshed['role_name'],
                'role_permissions' => $refreshed['role_permissions'],
                'profile_photo'    => $refreshed['profile_photo_path']
            ]);

            Session::setFlash('success', 'Perfil atualizado com sucesso!');
        } else {
            Session::setFlash('error', 'Falha ao atualizar informações do perfil.');
        }

        $this->redirect('/admin/profile');
    }

    /**
     * Processa a exclusão de um usuário do sistema.
     */
    public function delete(): void {
        $this->requirePermission('invite_user');
        $this->validatePostCsrf();

        $loggedUser = $this->requireAuth();
        $targetId = (int)($_POST['id'] ?? 0);

        if ($targetId === 0) {
            Session::setFlash('error', 'Identificador de usuário inválido.');
            $this->redirect('/admin/users');
        }

        // Segurança: Evitar autoexclusão
        if ($targetId === (int)$loggedUser['id']) {
            Session::setFlash('error', 'Ação inválida. Você não pode se autoexcluir do sistema.');
            $this->redirect('/admin/users');
        }

        $userModel = new User();
        $targetUser = $userModel->find($targetId);

        if (!$targetUser) {
            Session::setFlash('error', 'Usuário não encontrado.');
            $this->redirect('/admin/users');
        }

        try {
            // Se possuir foto de perfil física, remove do disco
            if (!empty($targetUser['profile_photo_path'])) {
                $filePath = dirname(__DIR__, 2) . '/public/' . $targetUser['profile_photo_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Exclui do banco
            $userModel->delete($targetId);

            // Grava na trilha de auditoria
            AuditLogger::log(
                'DELETE_USER',
                'usuarios',
                $targetId,
                [
                    'name'    => $targetUser['name'],
                    'email'   => $targetUser['email'],
                    'celular' => $targetUser['celular'],
                    'role_id' => $targetUser['role_id'],
                    'status'  => $targetUser['status']
                ],
                null
            );

            Session::setFlash('success', 'Usuário excluído com sucesso.');
        } catch (\Exception $e) {
            error_log("Erro ao excluir usuário ID {$targetId}: " . $e->getMessage());
            Session::setFlash('error', 'Erro técnico ao tentar excluir o usuário.');
        }

        $this->redirect('/admin/users');
    }

    /**
     * Ativa diretamente um usuário PENDENTE para fins de teste (Define senha padrão).
     */
    public function activateDirect(): void {
        $this->requirePermission('invite_user');
        $this->validatePostCsrf();

        $targetId = (int)($_POST['id'] ?? 0);

        if ($targetId === 0) {
            Session::setFlash('error', 'Identificador de usuário inválido.');
            $this->redirect('/admin/users');
        }

        $userModel = new User();
        $targetUser = $userModel->find($targetId);

        if (!$targetUser) {
            Session::setFlash('error', 'Usuário não encontrado.');
            $this->redirect('/admin/users');
        }

        if ($targetUser['status'] !== 'PENDENTE') {
            Session::setFlash('error', 'Este usuário já está ativo ou inativo.');
            $this->redirect('/admin/users');
        }

        try {
            // Define uma senha padrão de teste forte
            $testPassword = 'Teste@12345Password';
            $passwordHash = password_hash($testPassword, PASSWORD_DEFAULT);

            // Atualiza status e limpa o token de ativação
            $userModel->update($targetId, [
                'status' => 'ATIVO',
                'password_hash' => $passwordHash,
                'token_ativacao_hash' => null,
                'token_expira_em' => null
            ]);

            // Grava na trilha de auditoria
            AuditLogger::log(
                'ACTIVATE_USER_DIRECT_TEST',
                'usuarios',
                $targetId,
                ['status' => 'PENDENTE'],
                ['status' => 'ATIVO', 'auth_method' => 'DIRECT_BYPASS_TEST']
            );

            Session::setFlash('success', "Usuário ativado com sucesso para testes! Senha de acesso definida para: <strong>{$testPassword}</strong>");
        } catch (\Exception $e) {
            error_log("Erro ao ativar diretamente o usuário ID {$targetId}: " . $e->getMessage());
            Session::setFlash('error', 'Erro técnico ao tentar ativar diretamente o usuário.');
        }

        $this->redirect('/admin/users');
    }

    /**
     * Redefine/Altera a senha de qualquer usuário (Ação do Administrador).
     */
    public function resetUserPassword(): void {
        $this->requirePermission('invite_user');
        $this->validatePostCsrf();

        $targetId = (int)($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if ($targetId === 0 || $newPassword === '') {
            Session::setFlash('error', 'Preencha a nova senha do usuário.');
            $this->redirect('/admin/users');
        }

        if (strlen($newPassword) < 8) {
            Session::setFlash('error', 'A nova senha deve possuir no mínimo 8 caracteres.');
            $this->redirect('/admin/users');
        }

        if (!preg_match('/[A-Z]/', $newPassword) || 
            !preg_match('/[a-z]/', $newPassword) || 
            !preg_match('/[0-9]/', $newPassword) || 
            !preg_match('/[^a-zA-Z0-9]/', $newPassword)) {
            Session::setFlash('error', 'A nova senha deve conter pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial (ex: @, #, $, _, !).');
            $this->redirect('/admin/users');
        }

        $userModel = new User();
        $targetUser = $userModel->find($targetId);

        if (!$targetUser) {
            Session::setFlash('error', 'Usuário não encontrado.');
            $this->redirect('/admin/users');
        }

        try {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $userModel->update($targetId, ['password_hash' => $passwordHash]);

            AuditLogger::log(
                'ADMIN_RESET_USER_PASSWORD',
                'usuarios',
                $targetId,
                null,
                ['changed_by' => $this->requireAuth()['id']]
            );

            // Notifica o usuário pelo WhatsApp via Z-API se possuir celular
            if (!empty($targetUser['celular'])) {
                $msg = "Olá, {$targetUser['name']}! Sua senha de acesso ao Sistema de Gestão Eleitoral (SGE) foi redefinida pelo administrador para: {$newPassword}";
                WhatsAppService::send($targetUser['celular'], $msg);
            }

            Session::setFlash('success', "Senha do usuário <strong>" . htmlspecialchars($targetUser['name']) . "</strong> alterada com sucesso!");
        } catch (\Exception $e) {
            error_log("Erro ao redefinir senha do usuário ID {$targetId}: " . $e->getMessage());
            Session::setFlash('error', 'Erro ao alterar a senha do usuário.');
        }

        $this->redirect('/admin/users');
    }
}


