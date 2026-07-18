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

        <!-- Conteúdo Principal da View -->
        <?= $content ?>

        <div class="auth-footer">
            &copy; 2026 - Campanha Eleitoral Protegida &bull; Conformidade LGPD & TSE
        </div>
    </div>

</body>
</html>
