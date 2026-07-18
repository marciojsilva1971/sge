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

        // 3. Fakes para os KPIs financeiros exigidos pelos mockups (em sprints futuras serão reais)
        $kpiFinances = [
            'caixa_total' => 258400.00,
            'fefc'        => 180000.00,
            'fundo_part'  => 50000.00,
            'outros'      => 28400.00,
            'limite_gastos'=> 5000000.00,
            'gasto_atual' => 312000.00,
            'gasto_percent'=> round((312000.00 / 5000000.00) * 100, 2),
            'pendente_aprovacao' => 8
        ];

        $this->render('admin/dashboard', [
            'user'        => $user,
            'stats'       => $stats,
            'recentLogs'  => $recentLogs,
            'kpis'        => $kpiFinances,
            'csrf_token'  => Session::csrfToken()
        ]);
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
            'csrf_token' => Session::csrfToken(),
            'error'      => Session::getFlash('error'),
            'success'    => Session::getFlash('success')
        ]);
    }

    /**
     * Atualiza dados de perfil do próprio usuário logado.
     */
    public function updateProfile(): void {
        $user = $this->requireAuth();
        $this->validatePostCsrf();

        $name = trim($_POST['name'] ?? '');
        $celular = trim($_POST['celular'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($name === '' || $celular === '') {
            Session::setFlash('error', 'Nome e Celular são campos obrigatórios.');
            $this->redirect('/admin/profile');
        }

        $updateData = [
            'name'    => $name,
            'celular' => $celular
        ];

        // Se digitou senha, valida os critérios de senha forte
        if ($password !== '') {
            if (strlen($password) < 8) {
                Session::setFlash('error', 'A senha nova deve possuir no mínimo 8 caracteres.');
                $this->redirect('/admin/profile');
            }
            if (!preg_match('/[A-Z]/', $password) || 
                !preg_match('/[a-z]/', $password) || 
                !preg_match('/[0-9]/', $password) || 
                !preg_match('/[\W]/', $password)) {
                Session::setFlash('error', 'A nova senha deve conter pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial.');
                $this->redirect('/admin/profile');
            }
            if ($password !== $confirmPassword) {
                Session::setFlash('error', 'As senhas digitadas não coincidem.');
                $this->redirect('/admin/profile');
            }
            $updateData['password'] = $password;
        }

        // Processa upload de foto nova (opcional)
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
            $fileName = $_FILES['profile_photo']['name'];
            $fileSize = $_FILES['profile_photo']['size'];
            
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

            if ($fileSize > 2 * 1024 * 1024) {
                Session::setFlash('error', 'A foto de perfil não pode exceder o limite de 2MB.');
                $this->redirect('/admin/profile');
            }

            $newFileName = md5(uniqid('profile_', true)) . '.' . $fileExtension;
            $uploadFileDir = dirname(__DIR__, 2) . '/public/uploads/profiles/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $destPath = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $updateData['profile_photo_path'] = 'uploads/profiles/' . $newFileName;
                
                // Remove a foto antiga física se existir e for diferente do default
                $userModel = new User();
                $currentUser = $userModel->find($user['id']);
                if ($currentUser && !empty($currentUser['profile_photo_path'])) {
                    $oldFilePath = dirname(__DIR__, 2) . '/public/' . $currentUser['profile_photo_path'];
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
            }
        }

        $userModel = new User();
        if ($userModel->updateProfileInfo($user['id'], $updateData)) {
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
}


