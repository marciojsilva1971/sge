<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGE - Acesso Seguro</title>
    <!-- CSS Global -->
    <link rel="stylesheet" href="<?= $this->baseUrl('css/style.css') ?>">
</head>
<body class="auth-body">

    <div class="auth-container">
        <!-- Logo ou Título do Sistema -->
        <div class="auth-header">
            <div class="auth-logo">SGE</div>
            <h2>Sistema de Gestão Eleitoral</h2>
            <p>Foco em Prestação de Contas e Segurança Jurídica</p>
        </div>

        <!-- Alertas / Mensagens Flash -->
        <?php if ($flashSuccess = \App\Core\Session::getFlash('success')): ?>
            <div class="alert alert-success" style="background-color: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #10b981; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; font-size: 14px; box-shadow: 0 4px 12px rgba(16,185,129,0.2);">
                ✔ <?= htmlspecialchars($flashSuccess) ?>
            </div>
        <?php endif; ?>

        <?php if ($flashError = \App\Core\Session::getFlash('error')): ?>
            <div class="alert alert-error" style="background-color: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #ef4444; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; font-size: 14px; box-shadow: 0 4px 12px rgba(239,68,68,0.2);">
                ⚠ <?= htmlspecialchars($flashError) ?>
            </div>
        <?php endif; ?>

        <!-- Conteúdo Principal da View -->
        <?= $content ?>

        <div class="auth-footer">
            &copy; 2026 - Campanha Eleitoral Protegida &bull; Conformidade LGPD & TSE
        </div>
    </div>

</body>
</html>
