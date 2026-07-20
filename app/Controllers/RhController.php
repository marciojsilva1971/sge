<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Colaborador;
use App\Models\Contrato;
use App\Models\Role;
use App\Models\Expense;
use App\Models\Supplier;
use App\Services\WhatsAppService;
use Exception;

/**
 * Controller do Módulo de RH e Gestão de Colaboradores.
 */
class RhController extends Controller {

    /**
     * Dashboard / Listagem Geral do RH (Administrativo).
     */
    public function index(): void {
        $this->requirePermission('invite_user'); // Requer permissão administrativa
        $user = $this->getLoggedUser();

        $colaboradorModel = new Colaborador();
        $filters = [
            'nome'   => $_GET['nome'] ?? '',
            'status' => $_GET['status'] ?? '',
            'cpf'    => $_GET['cpf'] ?? ''
        ];

        $colaboradores = $colaboradorModel->getColaboradoresCompleto($filters);

        $roleModel = new Role();
        $roles = $roleModel->all();

        $this->render('admin/rh/index', [
            'user'           => $user,
            'colaboradores'  => $colaboradores,
            'roles'          => $roles,
            'filters'        => $filters,
            'avalSuccess'    => Session::getFlash('avalSuccess'),
            'homologSuccess' => Session::getFlash('homologSuccess'),
            'error'          => Session::getFlash('error'),
            'success'        => Session::getFlash('success')
        ], 'main');
    }

    /**
     * Formulário de Cadastro de Colaborador (Painel Admin).
     */
    public function create(): void {
        $this->requirePermission('invite_user');
        $user = $this->getLoggedUser();

        $roleModel = new Role();
        $roles = $roleModel->all();

        $this->render('admin/rh/create', [
            'user'  => $user,
            'roles' => $roles
        ], 'main');
    }

    /**
     * Processa o Cadastro de Colaborador (Via Admin).
     */
    public function store(): void {
        $this->requirePermission('invite_user');
        $this->validatePostCsrf();

        try {
            $colaboradorModel = new Colaborador();
            if ($colaboradorModel->findByCpf($_POST['cpf'])) {
                Session::setFlash('error', 'Já existe um colaborador cadastrado com este CPF.');
                $this->redirect('/admin/rh/novo');
            }

            $postData = $_POST;
            $postData['documento_foto_path'] = $this->handleDocumentoUpload('documento_foto');

            $colaboradorId = $colaboradorModel->cadastrarColaborador($postData);

            // Se o admin preencheu dados do contrato no cadastro direto, emite o contrato
            if (!empty($_POST['funcao_campanha']) && !empty($_POST['valor_contratado'])) {
                $contratoData = [
                    'titulo_contrato'        => 'Contrato de Prestação de Serviços de Campanha Eleitoral',
                    'funcao_campanha'        => trim($_POST['funcao_campanha']),
                    'valor_contratado'       => $this->parseBrlCurrency($_POST['valor_contratado']),
                    'forma_pagamento'        => $_POST['forma_pagamento'] ?? 'Transferência Bancária / PIX',
                    'data_inicio'            => $_POST['data_inicio'] ?? date('Y-m-d'),
                    'data_fim'               => $_POST['data_fim'] ?? date('Y-m-d', strtotime('+3 months')),
                    'tipo_assinatura'        => $_POST['tipo_assinatura'] ?? 'TERCEIROS_API',
                    'external_signature_url' => $_POST['external_signature_url'] ?? null
                ];

                $colaboradorModel->darAvalEEmitirContrato($colaboradorId, $contratoData);

                // Disparo automático via Z-API WhatsApp
                $colaborador = $colaboradorModel->find($colaboradorId);
                if ($colaborador && !empty($colaborador['celular_whatsapp'])) {
                    $linkContrato = $this->baseUrl('colaborador/contrato?token=' . $colaborador['token_cadastro']);
                    $linkPdf = $this->baseUrl('colaborador/contrato-pdf?token=' . $colaborador['token_cadastro']);

                    $msg = "Olá " . $colaborador['nome_completo'] . "! Seu cadastro foi aprovado e seu contrato de campanha foi emitido.\n\n";
                    $msg .= "📄 Para BAIXAR e IMPRIMIR o Contrato (PDF):\n" . $linkPdf . "\n\n";
                    $msg .= "🌐 Para enviar o documento assinado ou acompanhar o cadastro:\n" . $linkContrato . "\n\n";
                    $msg .= "Por favor, imprima, assine e envie a cópia digitalizada pelo link acima.";

                    WhatsAppService::send($colaborador['celular_whatsapp'], $msg);
                }
            }

            Session::setFlash('success', 'Colaborador cadastrado com sucesso! Status inicial: Aguardando Aval/Contrato.');
            $this->redirect('/admin/rh');
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao cadastrar colaborador: ' . $e->getMessage());
            $this->redirect('/admin/rh/novo');
        }
    }

    /**
     * Concede o Aval Cadastral e Emitir Contrato para o Colaborador.
     */
    public function darAval(): void {
        $this->requirePermission('invite_user');
        $this->validatePostCsrf();

        $colaboradorId = (int)($_POST['colaborador_id'] ?? 0);

        if (!$colaboradorId || empty($_POST['funcao_campanha']) || empty($_POST['valor_contratado'])) {
            Session::setFlash('error', 'Preencha a função, o valor do contrato e as datas para emitir o contrato.');
            $this->redirect('/admin/rh');
        }

        try {
            $colaboradorModel = new Colaborador();
            $contratoData = [
                'titulo_contrato'        => 'Contrato de Prestação de Serviços de Campanha Eleitoral',
                'funcao_campanha'        => trim($_POST['funcao_campanha']),
                'valor_contratado'       => $this->parseBrlCurrency($_POST['valor_contratado']),
                'forma_pagamento'        => $_POST['forma_pagamento'] ?? 'Transferência Bancária / PIX',
                'data_inicio'            => $_POST['data_inicio'] ?? date('Y-m-d'),
                'data_fim'               => $_POST['data_fim'] ?? date('Y-m-d', strtotime('+3 months')),
                'tipo_assinatura'        => $_POST['tipo_assinatura'] ?? 'TERCEIROS_API',
                'external_signature_url' => $_POST['external_signature_url'] ?? null
            ];

            $colaboradorModel->darAvalEEmitirContrato($colaboradorId, $contratoData);

            // Disparo automático via Z-API API (WhatsApp) e geração de links de envio manual
            $colaborador = $colaboradorModel->find($colaboradorId);
            if ($colaborador && !empty($colaborador['celular_whatsapp'])) {
                $linkContrato = $this->baseUrl('colaborador/contrato?token=' . $colaborador['token_cadastro']);
                $linkPdf = $this->baseUrl('colaborador/contrato-pdf?token=' . $colaborador['token_cadastro']);

                $msg = "Olá " . $colaborador['nome_completo'] . "! Seu cadastro de colaborador de campanha foi aprovado e seu contrato foi emitido.\n\n";
                if (!empty($contratoData['external_signature_url'])) {
                    $msg .= "✍️ Link para Assinatura Digital Externa (Plataforma):\n" . $contratoData['external_signature_url'] . "\n\n";
                }
                $msg .= "📄 Para BAIXAR e IMPRIMIR o Contrato (PDF):\n" . $linkPdf . "\n\n";
                $msg .= "🌐 Para enviar o documento assinado ou acompanhar o cadastro:\n" . $linkContrato . "\n\n";
                $msg .= "Por favor, imprima/assine e envie a cópia pelo link acima.";

                $zapiSent = WhatsAppService::send($colaborador['celular_whatsapp'], $msg);
                $clickToChatUrl = WhatsAppService::generateClickToChat($colaborador['celular_whatsapp'], $msg);

                Session::setFlash('avalSuccess', [
                    'nome'           => $colaborador['nome_completo'],
                    'celular'        => $colaborador['celular_whatsapp'],
                    'msg'            => $msg,
                    'link_pdf'       => $linkPdf,
                    'link_contrato'  => $linkContrato,
                    'zapi_sent'      => $zapiSent,
                    'click_to_chat'  => $clickToChatUrl
                ]);
            }

            Session::setFlash('success', 'Aval concedido e contrato emitido com sucesso! Links gerados.');
            $this->redirect('/admin/rh');
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao conceder aval e emitir contrato: ' . $e->getMessage());
            $this->redirect('/admin/rh');
        }
    }

    /**
     * Reenvia a mensagem de contrato diretamente via API do Z-API (WhatsApp).
     */
    public function enviarWhatsAppApi(): void {
        $this->requirePermission('invite_user');
        $this->validatePostCsrf();

        $colaboradorId = (int)($_POST['colaborador_id'] ?? 0);
        if (!$colaboradorId) {
            Session::setFlash('error', 'Colaborador não informado.');
            $this->redirect('/admin/rh');
        }

        $colaboradorModel = new Colaborador();
        $colaborador = $colaboradorModel->find($colaboradorId);

        if (!$colaborador || empty($colaborador['celular_whatsapp'])) {
            Session::setFlash('error', 'Colaborador não encontrado ou sem WhatsApp cadastrado.');
            $this->redirect('/admin/rh');
        }

        // Busca dados do contrato para resgatar a external_signature_url se existir
        $contratoModel = new Contrato();
        $contrato = $contratoModel->getContratoPorColaborador($colaboradorId);

        $linkContrato = $this->baseUrl('colaborador/contrato?token=' . $colaborador['token_cadastro']);
        $linkPdf = $this->baseUrl('colaborador/contrato-pdf?token=' . $colaborador['token_cadastro']);

        $msg = "Olá " . $colaborador['nome_completo'] . "! Seu cadastro foi aprovado e seu contrato de campanha foi emitido.\n\n";
        if (!empty($contrato['external_signature_url'])) {
            $msg .= "✍️ Link para Assinatura Digital Externa (Plataforma):\n" . $contrato['external_signature_url'] . "\n\n";
        }
        $msg .= "📄 Para BAIXAR e IMPRIMIR o Contrato (PDF):\n" . $linkPdf . "\n\n";
        $msg .= "🌐 Para enviar o documento assinado ou acompanhar o cadastro:\n" . $linkContrato . "\n\n";
        $msg .= "Por favor, imprima/assine e envie a cópia pelo link acima.";

        $sucesso = WhatsAppService::send($colaborador['celular_whatsapp'], $msg);
        $clickToChatUrl = WhatsAppService::generateClickToChat($colaborador['celular_whatsapp'], $msg);

        Session::setFlash('avalSuccess', [
            'nome'           => $colaborador['nome_completo'],
            'celular'        => $colaborador['celular_whatsapp'],
            'msg'            => $msg,
            'link_pdf'       => $linkPdf,
            'link_contrato'  => $linkContrato,
            'zapi_sent'      => $sucesso,
            'click_to_chat'  => $clickToChatUrl
        ]);

        $this->redirect('/admin/rh');
    }

    /**
     * Aprovação / Homologação Final do Colaborador (após conferência do contrato assinado) e Atribuição de Perfil.
     */
    public function homologar(): void {
        $this->requirePermission('invite_user');
        $this->validatePostCsrf();

        $colaboradorId = (int)($_POST['colaborador_id'] ?? 0);
        $roleId = (int)($_POST['role_id'] ?? 0);

        if (!$colaboradorId || !$roleId) {
            Session::setFlash('error', 'Selecione o perfil de acesso (Role) para homologar o colaborador.');
            $this->redirect('/admin/rh');
        }

        try {
            $colaboradorModel = new Colaborador();
            $colaborador = $colaboradorModel->find($colaboradorId);

            if (!$colaborador) {
                Session::setFlash('error', 'Colaborador não encontrado.');
                $this->redirect('/admin/rh');
            }

            $result = $colaboradorModel->homologarEAtribuirPerfil($colaboradorId, $roleId);
            $tempPassword = $result['temp_password'];

            // Envio automático das credenciais provisórias via WhatsApp Z-API
            if (!empty($colaborador['celular_whatsapp'])) {
                $loginUrl = $this->baseUrl('login');
                $msg = "Olá " . $colaborador['nome_completo'] . "! Parabéns, seu contrato de campanha foi conferido e aprovado!\n\n";
                $msg .= "Seu cadastro no Sistema de Gestão Eleitoral (SGE) foi homologado com sucesso.\n\n";
                $msg .= "🔑 DADOS DE ACESSO AO SISTEMA:\n";
                $msg .= "🌐 Link de Acesso: " . $loginUrl . "\n";
                $msg .= "👤 Usuário (E-mail): " . $colaborador['email'] . "\n";
                $msg .= "🔒 Senha Provisória: " . $tempPassword . "\n\n";
                $msg .= "Recomendamos que você efetue seu primeiro acesso e altere sua senha no seu perfil.";

                $zapiSent = WhatsAppService::send($colaborador['celular_whatsapp'], $msg);
                $clickToChatUrl = WhatsAppService::generateClickToChat($colaborador['celular_whatsapp'], $msg);

                Session::setFlash('homologSuccess', [
                    'nome'          => $colaborador['nome_completo'],
                    'celular'       => $colaborador['celular_whatsapp'],
                    'email'         => $colaborador['email'],
                    'temp_password' => $tempPassword,
                    'msg'           => $msg,
                    'zapi_sent'     => $zapiSent,
                    'click_to_chat' => $clickToChatUrl
                ]);
            }

            Session::setFlash('success', 'Contrato conferido! Colaborador homologado com sucesso.');
            $this->redirect('/admin/rh');
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro ao homologar e conceder permissões: ' . $e->getMessage());
            $this->redirect('/admin/rh');
        }
    }

    /**
     * Endpoint AJAX para consultar a situação cadastral na Justiça Eleitoral (TSE) e Receita.
     * Endpoint: GET /admin/rh/consultar-tse
     */
    public function consultarTse(): void {
        $this->requirePermission('invite_user');

        $colaboradorId = intval($_GET['id'] ?? 0);
        $cpf = trim($_GET['cpf'] ?? '');

        $colaboradorModel = new Colaborador();
        $colaborador = null;

        if ($colaboradorId > 0) {
            $colaborador = $colaboradorModel->find($colaboradorId);
        }

        if (!$colaborador && !empty($cpf)) {
            $colaborador = $colaboradorModel->findByCpf($cpf);
        }

        if (!$colaborador) {
            header('Content-Type: application/json');
            echo json_encode([
                'valido'     => false,
                'status_tse' => 'Colaborador não encontrado.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $uf = $colaborador['uf'] ?? 'PR';
        $idade = intval($colaborador['idade_calculada'] ?? 0);
        $resultado = \App\Services\TseService::consultarSituacaoCadastral(
            $colaborador['cpf'],
            $colaborador['nome_completo'],
            $uf,
            $idade
        );

        header('Content-Type: application/json');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Página Pública de Auto-Cadastro do Colaborador (Link Externo via WhatsApp/Email).
     */
    public function publicCadastro(): void {
        $this->render('colaborador/cadastro', [], 'auth');
    }

    /**
     * Processa o Auto-Cadastro Público.
     */
    public function publicStore(): void {
        $this->validatePostCsrf();

        try {
            if (empty($_POST['optin_whatsapp'])) {
                Session::setFlash('error', 'É necessário declarar anuência (Opt-in) para recebimento de mensagens e contratos.');
                $this->redirect('/colaborador/cadastro');
            }

            $colaboradorModel = new Colaborador();
            if ($colaboradorModel->findByCpf($_POST['cpf'])) {
                Session::setFlash('error', 'Já existe um cadastro com este CPF. Entre em contato com a equipe de RH da campanha.');
                $this->redirect('/colaborador/cadastro');
            }

            $postData = $_POST;
            $postData['documento_foto_path'] = $this->handleDocumentoUpload('documento_foto');

            $colaboradorId = $colaboradorModel->cadastrarColaborador($postData);
            $colaborador = $colaboradorModel->find($colaboradorId);

            Session::setFlash('success', 'Cadastro realizado com sucesso! Aguarde o link de assinatura do contrato via WhatsApp/E-mail.');
            $this->redirect('/colaborador/contrato?token=' . $colaborador['token_cadastro']);
        } catch (Exception $e) {
            Session::setFlash('error', 'Erro no cadastro: ' . $e->getMessage());
            $this->redirect('/colaborador/cadastro');
        }
    }

    /**
     * Auxiliar para upload de foto do documento de identificação (RG, CNH, CIN).
     */
    private function handleDocumentoUpload(string $inputName = 'documento_foto'): ?string {
        if (empty($_FILES[$inputName]['name'])) {
            return null;
        }

        $file = $_FILES[$inputName];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowedExts)) {
            throw new Exception("Formato do documento de identificação inválido. Permito somente PDF, PNG ou JPG.");
        }

        $uploadDir = dirname(__DIR__, 2) . '/storage/documentos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'doc_id_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            return 'storage/documentos/' . $fileName;
        }

        return null;
    }

    /**
     * Converte o valor em moeda BRL (ex: "R$ 1.500,50" ou "1.500,00") para um float válido do banco (1500.50).
     */
    protected function parseBrlCurrency($value): float {
        if (is_numeric($value)) {
            return (float)$value;
        }
        if (empty($value)) {
            return 0.0;
        }

        // Limpa R$, espaços e caracteres não numéricos exceto vírgula e ponto
        $clean = preg_replace('/[^\d,\.]/', '', (string)$value);

        // Se contiver ponto de milhar e vírgula decimal (ex: 1.500,50)
        if (strpos($clean, '.') !== false && strpos($clean, ',') !== false) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (strpos($clean, ',') !== false) {
            // Se tiver apenas vírgula (ex: 1500,50)
            $clean = str_replace(',', '.', $clean);
        }

        return (float)$clean;
    }

    /**
     * Página Pública do Contrato (Visualizar / Upload de Assinatura Manual / Link de Terceiros).
     */
    public function publicContrato(): void {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            Session::setFlash('error', 'Link de contrato inválido.');
            $this->redirect('/colaborador/cadastro');
        }

        $colaboradorModel = new Colaborador();
        $colaborador = $colaboradorModel->findByToken($token);

        if (!$colaborador) {
            Session::setFlash('error', 'Cadastro não encontrado.');
            $this->redirect('/colaborador/cadastro');
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->findByColaborador($colaborador['id']);

        $this->render('colaborador/contrato', [
            'colaborador' => $colaborador,
            'contrato'    => $contrato
        ], 'auth');
    }

    /**
     * Upload do PDF/Imagem do Contrato Assinado Manualmente.
     */
    public function uploadManualSignature(): void {
        $token = $_POST['token'] ?? $_GET['token'] ?? '';

        // Validação de CSRF para a página pública sem redirecionar para login
        $csrfToken = $_POST['csrf_token'] ?? null;
        if (!Session::validateCsrf($csrfToken)) {
            Session::setFlash('error', 'Sessão do formulário expirada. Por favor, tente enviar o arquivo novamente.');
            $this->redirect('/colaborador/contrato?token=' . $token);
        }

        $colaboradorModel = new Colaborador();
        $colaborador = $colaboradorModel->findByToken($token);

        if (!$colaborador) {
            Session::setFlash('error', 'Colaborador não identificado.');
            $this->redirect('/colaborador/cadastro');
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->findByColaborador($colaborador['id']);

        if (!$contrato) {
            Session::setFlash('error', 'Contrato não encontrado.');
            $this->redirect('/colaborador/contrato?token=' . $token);
        }

        if (empty($_FILES['contrato_assinado']['name'])) {
            Session::setFlash('error', 'Por favor, selecione a foto ou o PDF do contrato assinado.');
            $this->redirect('/colaborador/contrato?token=' . $token);
        }

        $file = $_FILES['contrato_assinado'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowedExts)) {
            Session::setFlash('error', 'Formato de arquivo inválido. Permito somente PDF, PNG ou JPG.');
            $this->redirect('/colaborador/contrato?token=' . $token);
        }

        // Cria diretório de armazenamento seguro em storage/contratos
        $uploadDir = dirname(__DIR__, 2) . '/storage/contratos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'contrato_assinado_' . $colaborador['id'] . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $relativePath = 'storage/contratos/' . $fileName;
            $contratoModel->registrarAssinatura($contrato['id'], $relativePath);

            Session::setFlash('success', 'Contrato assinado enviado com sucesso! Sua homologação está em análise pela equipe da campanha.');
            $this->redirect('/colaborador/contrato?token=' . $token);
        } else {
            Session::setFlash('error', 'Erro ao salvar o arquivo enviado.');
            $this->redirect('/colaborador/contrato?token=' . $token);
        }
    }

    /**
     * Exibe/servir o documento de identificação (RG/CNH/CIN) ou contrato assinado do colaborador.
     * GET /admin/rh/documento?id=...&tipo=doc|contrato
     */
    public function documento(): void {
        $this->requirePermission('invite_user');

        $id = (int)($_GET['id'] ?? 0);
        $tipo = $_GET['tipo'] ?? 'doc';

        if (!$id) {
            http_response_code(400);
            echo "ID do colaborador não fornecido.";
            exit;
        }

        $colaboradorModel = new Colaborador();
        $colaborador = $colaboradorModel->find($id);

        if (!$colaborador) {
            http_response_code(404);
            echo "Colaborador não encontrado.";
            exit;
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->findByColaborador($id);

        $relativePath = ($tipo === 'contrato') ? ($contrato['pdf_assinado_path'] ?? '') : ($colaborador['documento_foto_path'] ?? '');

        if (empty($relativePath)) {
            http_response_code(404);
            echo "Arquivo não cadastrado para este colaborador.";
            exit;
        }

        $fullPath = dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/\\');

        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo "Arquivo físico não encontrado em disco (" . htmlspecialchars($relativePath) . ").";
            exit;
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'pdf'  => 'application/pdf',
            'gif'  => 'image/gif',
            'webp' => 'image/webp'
        ];

        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

        if (ob_get_level()) {
            ob_end_clean();
        }

        header("Content-Type: " . $mimeType);
        header("Content-Disposition: inline; filename=\"" . rawurlencode(basename($fullPath)) . "\"");
        header("Content-Length: " . filesize($fullPath));
        header("Cache-Control: private, max-age=86400");

        readfile($fullPath);
        exit;
    }

    /**
     * Exibe o documento do Contrato em PDF (Administrativo).
     * GET /admin/rh/contrato-pdf?id=...
     */
    public function contratoPdf(): void {
        $this->requirePermission('invite_user');

        $colaboradorId = (int)($_GET['id'] ?? 0);
        if (!$colaboradorId) {
            Session::setFlash('error', 'Colaborador não informado.');
            $this->redirect('/admin/rh');
        }

        $colaboradorModel = new Colaborador();
        $colaborador = $colaboradorModel->find($colaboradorId);

        if (!$colaborador) {
            Session::setFlash('error', 'Colaborador não encontrado.');
            $this->redirect('/admin/rh');
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->findByColaborador($colaboradorId);

        if (!$contrato) {
            Session::setFlash('error', 'Nenhum contrato emitido para este colaborador ainda.');
            $this->redirect('/admin/rh');
        }

        // Se houver um arquivo PDF assinado enviado e for um arquivo em disco, transmitimos diretamente
        if (!empty($contrato['pdf_assinado_path'])) {
            $fullPath = dirname(__DIR__, 2) . '/' . ltrim($contrato['pdf_assinado_path'], '/\\');
            if (file_exists($fullPath)) {
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    if (ob_get_level()) ob_end_clean();
                    header("Content-Type: application/pdf");
                    header("Content-Disposition: inline; filename=\"Contrato_Assinado_" . $colaborador['id'] . ".pdf\"");
                    header("Content-Length: " . filesize($fullPath));
                    readfile($fullPath);
                    exit;
                }
            }
        }

        require dirname(__DIR__, 2) . '/app/Views/admin/rh/contrato_pdf.php';
        exit;
    }

    /**
     * Exibe o documento do Contrato em PDF (Visão Pública do Colaborador).
     * GET /colaborador/contrato-pdf?token=...
     */
    public function publicContratoPdf(): void {
        $token = trim($_GET['token'] ?? '');
        if (!$token) {
            die("Token inválido.");
        }

        $colaboradorModel = new Colaborador();
        $colaborador = $colaboradorModel->findByToken($token);

        if (!$colaborador) {
            die("Colaborador não encontrado.");
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->findByColaborador($colaborador['id']);

        if (!$contrato) {
            die("Contrato não emitido.");
        }

        if (!empty($contrato['pdf_assinado_path'])) {
            $fullPath = dirname(__DIR__, 2) . '/' . ltrim($contrato['pdf_assinado_path'], '/\\');
            if (file_exists($fullPath)) {
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    if (ob_get_level()) ob_end_clean();
                    header("Content-Type: application/pdf");
                    header("Content-Disposition: inline; filename=\"Contrato_Assinado_" . $colaborador['id'] . ".pdf\"");
                    header("Content-Length: " . filesize($fullPath));
                    readfile($fullPath);
                    exit;
                }
            }
        }

        $_GET['public'] = '1';
        require dirname(__DIR__, 2) . '/app/Views/admin/rh/contrato_pdf.php';
        exit;
    }

    /**
     * Exibe o documento do colaborador para a interface pública (validado por token).
     * GET /colaborador/documento?token=...&tipo=doc|contrato
     */
    public function publicDocumento(): void {
        $token = trim($_GET['token'] ?? '');
        $tipo = $_GET['tipo'] ?? 'contrato';

        if (!$token) {
            http_response_code(400);
            echo "Token do colaborador não informado.";
            exit;
        }

        $colaboradorModel = new Colaborador();
        $colaborador = $colaboradorModel->findByToken($token);

        if (!$colaborador) {
            http_response_code(404);
            echo "Colaborador não encontrado.";
            exit;
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->findByColaborador($colaborador['id']);

        $relativePath = ($tipo === 'contrato') ? ($contrato['pdf_assinado_path'] ?? '') : ($colaborador['documento_foto_path'] ?? '');

        if (empty($relativePath)) {
            http_response_code(404);
            echo "Arquivo de " . htmlspecialchars($tipo) . " não encontrado para este cadastro.";
            exit;
        }

        $fullPath = dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/\\');

        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo "Arquivo físico não encontrado no servidor.";
            exit;
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'pdf'  => 'application/pdf',
            'gif'  => 'image/gif',
            'webp' => 'image/webp'
        ];

        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

        if (ob_get_level()) {
            ob_end_clean();
        }

        header("Content-Type: " . $mimeType);
        header("Content-Disposition: inline; filename=\"" . rawurlencode(basename($fullPath)) . "\"");
        header("Content-Length: " . filesize($fullPath));
        header("Cache-Control: private, max-age=86400");

        readfile($fullPath);
        exit;
    }
}
