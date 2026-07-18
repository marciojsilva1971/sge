<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Services\AuditLogger;

/**
 * Controlador de Autenticação e Segurança.
 */
class AuthController extends Controller {
    /**
     * Exibe o formulário de login.
     */
    public function loginForm(): void {
        if ($this->getLoggedUser() !== null) {
            $this->redirect('/admin/dashboard');
        }

        $this->render('auth/login', [
            'csrf_token' => Session::csrfToken(),
            'error'      => Session::getFlash('error'),
            'success'    => Session::getFlash('success')
        ], 'auth');
    }

    /**
     * Processa a tentativa de login.
     */
    public function login(): void {
        // Valida CSRF
        $this->validatePostCsrf();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            Session::setFlash('error', 'Preencha todos os campos obrigatórios.');
            $this->redirect('/login');
        }

        // Medida simples contra Brute Force (Atraso incremental com base em tentativas falhas na sessão)
        $failedAttempts = Session::get('login_attempts', 0);
        if ($failedAttempts >= 5) {
            // Atraso de 2 segundos para dificultar ataques automatizados
            sleep(2);
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            // Conta tentativas falhas na sessão
            Session::set('login_attempts', $failedAttempts + 1);
            
            // Log de auditoria para tentativas de login com usuário inexistente
            AuditLogger::log('LOGIN_FAIL_USER_NOT_FOUND', 'usuarios', null, null, ['email' => $email]);
            
            Session::setFlash('error', 'E-mail ou senha incorretos.');
            $this->redirect('/login');
        }

        // Verifica status da conta
        if ($user['status'] === 'PENDENTE') {
            Session::setFlash('error', 'Sua conta ainda não foi ativada. Verifique seu e-mail/WhatsApp para ativar sua conta.');
            $this->redirect('/login');
        }

        if ($user['status'] === 'INATIVO') {
            AuditLogger::log('LOGIN_FAIL_USER_BLOCKED', 'usuarios', $user['id'], null, ['email' => $email]);
            Session::setFlash('error', 'Sua conta está desativada. Entre em contato com o administrador.');
            $this->redirect('/login');
        }

        // Verifica a senha hash
        if (!password_verify($password, $user['password_hash'])) {
            Session::set('login_attempts', $failedAttempts + 1);
            
            AuditLogger::log('LOGIN_FAIL_INVALID_PASSWORD', 'usuarios', $user['id'], null, ['email' => $email]);
            
            Session::setFlash('error', 'E-mail ou senha incorretos.');
            $this->redirect('/login');
        }

        // Login Bem-sucedido: Limpa tentativas falhas
        Session::remove('login_attempts');

        // Prevenção contra Session Fixation: Regenera o ID de sessão
        Session::regenerate();

        // Salva dados mínimos e permissões na sessão
        Session::set('user', [
            'id'               => $user['id'],
            'name'             => $user['name'],
            'email'            => $user['email'],
            'celular'          => $user['celular'],
            'role_id'          => $user['role_id'],
            'role_name'        => $user['role_name'],
            'role_permissions' => $user['role_permissions'],
            'profile_photo'    => $user['profile_photo_path']
        ]);

        if (($user['role_name'] ?? '') === 'COLABORADOR_CAMPO') {
            $this->redirect('/portal');
        } else {
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Realiza o logout do usuário.
     */
    public function logout(): void {
        $user = $this->getLoggedUser();
        if ($user) {
            AuditLogger::log('LOGOUT', 'usuarios', $user['id']);
        }
        
        Session::destroy();
        
        // Reinicializa a sessão para salvar a mensagem flash
        Session::start();
        Session::setFlash('success', 'Você saiu do sistema com segurança.');
        $this->redirect('/login');
    }
}
