<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\EmailService;
use App\Services\WhatsAppService;

/**
 * Controlador de Ativação de Convites.
 */
class ActivationController extends Controller {
    /**
     * Exibe o formulário de ativação de conta.
     */
    public function activationForm(): void {
        $token = $_GET['token'] ?? '';

        if ($token === '') {
            Session::setFlash('error', 'Token de ativação ausente.');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByActivationToken($token);

        if (!$user) {
            Session::setFlash('error', 'O link de ativação é inválido ou já expirou (limite de 24h). Entre em contato com o administrador.');
            $this->redirect('/login');
        }

        $this->render('auth/activate', [
            'token'      => $token,
            'user'       => $user,
            'csrf_token' => Session::csrfToken(),
            'error'      => Session::getFlash('error')
        ], 'auth');
    }

    /**
     * Processa a ativação da conta.
     */
    public function activate(): void {
        // Valida CSRF
        $this->validatePostCsrf();

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($token === '') {
            Session::setFlash('error', 'Dados insuficientes para ativação.');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByActivationToken($token);

        if (!$user) {
            Session::setFlash('error', 'O link de ativação é inválido ou já expirou.');
            $this->redirect('/login');
        }

        // Validação da Senha Forte
        if (strlen($password) < 8) {
            Session::setFlash('error', 'A senha deve possuir no mínimo 8 caracteres.');
            $this->redirect("/ativar?token={$token}");
        }

        // Regex para letra maiúscula, minúscula, número e caractere especial
        if (!preg_match('/[A-Z]/', $password) || 
            !preg_match('/[a-z]/', $password) || 
            !preg_match('/[0-9]/', $password) || 
            !preg_match('/[\W]/', $password)) {
            Session::setFlash('error', 'A senha deve conter pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial.');
            $this->redirect("/ativar?token={$token}");
        }

        if ($password !== $confirmPassword) {
            Session::setFlash('error', 'As senhas digitadas não coincidem.');
            $this->redirect("/ativar?token={$token}");
        }

        // Processamento de Upload de Foto de Perfil (Opcional)
        $photoPath = null;
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
            $fileName = $_FILES['profile_photo']['name'];
            $fileSize = $_FILES['profile_photo']['size'];
            $fileType = $_FILES['profile_photo']['type'];
            
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

            // Valida Extensão e Mime Type para segurança
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);

            if (!in_array($fileExtension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
                Session::setFlash('error', 'Tipo de arquivo inválido. Permitido apenas JPG, PNG ou WEBP.');
                $this->redirect("/ativar?token={$token}");
            }

            // Valida tamanho do arquivo (Max 2MB)
            if ($fileSize > 2 * 1024 * 1024) {
                Session::setFlash('error', 'A foto de perfil não pode exceder o limite de 2MB.');
                $this->redirect("/ativar?token={$token}");
            }

            // Gera nome de arquivo seguro usando hash
            $newFileName = md5(uniqid('profile_', true)) . '.' . $fileExtension;
            $uploadFileDir = dirname(__DIR__, 2) . '/public/uploads/profiles/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $destPath = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Guarda o caminho relativo para salvar no banco
                $photoPath = 'uploads/profiles/' . $newFileName;
            } else {
                error_log("Falha ao mover arquivo enviado para: " . $destPath);
            }
        }

        // Ativa o usuário
        if ($userModel->activateAccount($user['id'], $password, $photoPath)) {
            // Envia notificações de confirmação
            $confirmMessage = "Olá, {$user['name']}! Sua conta no Sistema de Gestão Eleitoral (SGE) foi ativada com sucesso e sua nova senha foi cadastrada. Você já pode efetuar o login no painel.";
            
            EmailService::send($user['email'], 'Confirmação de Ativação - SGE', $confirmMessage);
            
            if (!empty($user['celular'])) {
                WhatsAppService::send($user['celular'], $confirmMessage);
            }

            Session::setFlash('success', 'Sua conta foi ativada com sucesso! Faça login abaixo.');
            $this->redirect('/login');
        } else {
            Session::setFlash('error', 'Houve um erro técnico ao ativar sua conta. Tente novamente ou contate o administrador.');
            $this->redirect("/ativar?token={$token}");
        }
    }
}
