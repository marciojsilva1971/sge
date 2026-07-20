<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGE - Painel Administrativo</title>
    <!-- CSS Global -->
    <link rel="stylesheet" href="<?= $this->baseUrl('css/style.css') ?>">
</head>
<body class="admin-body">

    <!-- Container Geral -->
    <div class="admin-container">
        
        <!-- Sidebar Esquerda -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">SGE</div>
                <span class="logo-subtitle">Gestão Eleitoral</span>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="<?= $this->baseUrl('admin/dashboard') ?>" class="nav-item">
                            <span class="nav-icon">📊</span>
                            <span class="nav-label">Dashboard</span>
                        </a>
                    </li>
                    <?php if (($user['role_name'] ?? '') === 'ADMINISTRADOR' || !empty($user['role_permissions']['invite_user']) || ($user['role_id'] ?? 0) == 1 || ($user['email'] ?? '') === 'admin@sge.com'): ?>
                    <li>
                        <a href="<?= $this->baseUrl('admin/users') ?>" class="nav-item">
                            <span class="nav-icon">👥</span>
                            <span class="nav-label">Usuários</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= $this->baseUrl('admin/rh') ?>" class="nav-item">
                            <span class="nav-icon">📇</span>
                            <span class="nav-label">Gestão de RH</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (($user['role_name'] ?? '') === 'ADMINISTRADOR' || ($user['role_name'] ?? '') === 'FINANCEIRO' || ($user['role_id'] ?? 0) == 1 || ($user['email'] ?? '') === 'admin@sge.com'): ?>
                    <li>
                        <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="nav-item">
                            <span class="nav-icon">💰</span>
                            <span class="nav-label">Módulo Financeiro</span>
                        </a>
                    </li>
                    <li style="padding-left: 14px; border-left: 2px solid rgba(20, 184, 166, 0.3); margin-left: 10px;">
                        <a href="<?= $this->baseUrl('admin/financeiro/fila') ?>" class="nav-item" style="font-size: 13px; padding: 6px 10px;">
                            <span class="nav-icon" style="font-size: 14px;">⚖️</span>
                            <span class="nav-label">Fila de Aprovações</span>
                        </a>
                    </li>
                    <li style="padding-left: 14px; border-left: 2px solid rgba(20, 184, 166, 0.3); margin-left: 10px;">
                        <a href="<?= $this->baseUrl('admin/financeiro/tipos-despesas') ?>" class="nav-item" style="font-size: 13px; padding: 6px 10px;">
                            <span class="nav-icon" style="font-size: 14px;">🏷️</span>
                            <span class="nav-label">Tipos de Despesas</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($user['role_permissions']['configure_rbac'])): ?>
                    <li>
                        <a href="<?= $this->baseUrl('admin/rbac') ?>" class="nav-item">
                            <span class="nav-icon">🔑</span>
                            <span class="nav-label">Permissões (RBAC)</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?= $this->baseUrl('admin/profile') ?>" class="nav-item">
                            <span class="nav-icon">👤</span>
                            <span class="nav-label">Meu Perfil</span>
                        </a>
                    </li>
                    <li class="logout-li">
                        <a href="<?= $this->baseUrl('logout') ?>" class="nav-item logout-btn">
                            <span class="nav-icon">🚪</span>
                            <span class="nav-label">Sair</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Área Principal Direita -->
        <main class="main-content">
            
            <!-- Topbar Superior -->
            <header class="topbar">
                <div class="topbar-title">
                    Painel SGE
                </div>
                
                <div class="user-profile-menu">
                    <div class="user-info text-right">
                        <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                        <div class="user-role"><?= htmlspecialchars($user['role_name']) ?></div>
                    </div>
                    <div class="avatar-wrapper">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img src="<?= $this->baseUrl($user['profile_photo']) ?>" alt="Avatar" class="avatar-img">
                        <?php else: ?>
                            <div class="avatar-placeholder"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- Conteúdo da Página -->
            <div class="content-wrapper">
                
                <!-- Alertas / Mensagens Flash -->
                <?php if ($flashSuccess = \App\Core\Session::getFlash('success')): ?>
                    <div class="alert alert-success">
                        <span class="alert-icon">✓</span>
                        <span class="alert-message"><?= htmlspecialchars($flashSuccess) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($flashError = \App\Core\Session::getFlash('error')): ?>
                    <div class="alert alert-error">
                        <span class="alert-icon">⚠</span>
                        <span class="alert-message"><?= htmlspecialchars($flashError) ?></span>
                    </div>
                <?php endif; ?>

                <?= $content ?>

            </div>

            <!-- Rodapé Administrativo -->
            <footer class="admin-footer">
                Sistema de Gestão Eleitoral &bull; Desenvolvimento Seguro PHP Puro &bull; &copy; 2026
            </footer>
        </main>

    </div>

    <!-- Script de Interatividade para a Sidebar Ativa -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-item');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (currentPath.endsWith(href) || (href !== '#' && currentPath.indexOf(href) !== -1)) {
                    link.classList.add('active');
                }
            });
        });
    </script>

</body>
</html>
