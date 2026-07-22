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

    <!-- Overlay para fechar sidebar no mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Container Geral -->
    <div class="admin-container">
        
        <!-- Sidebar Esquerda -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div>
                    <div class="logo">SGE</div>
                    <span class="logo-subtitle">Gestão Eleitoral</span>
                </div>
                <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Fechar Menu">✕</button>
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
                            <span class="nav-label">Financeiro</span>
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
                <div class="topbar-left" style="display: flex; align-items: center; gap: 12px;">
                    <button type="button" class="mobile-menu-btn" id="mobileMenuToggle" aria-label="Abrir Menu">
                        <span>☰</span>
                    </button>
                    <div class="topbar-title">
                        Painel SGE
                    </div>
                </div>
                
                <a href="<?= $this->baseUrl('admin/profile') ?>" class="user-profile-menu" style="text-decoration: none; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                    <div class="user-info text-right">
                        <div class="user-name" style="color: #fff; font-weight: 600;"><?= htmlspecialchars($user['name']) ?></div>
                        <div class="user-role" style="font-size: 11px; color: var(--accent-teal-hover);"><?= htmlspecialchars($user['role_name']) ?></div>
                    </div>
                    <div class="avatar-wrapper">
                        <?php 
                        $userAvatar = !empty($user['profile_photo_path']) ? $user['profile_photo_path'] : (!empty($user['profile_photo']) ? $user['profile_photo'] : null);
                        ?>
                        <?php if ($userAvatar): ?>
                            <img src="<?= $this->baseUrl($userAvatar) ?>" alt="Avatar" class="avatar-img" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #0d9488;">
                        <?php else: ?>
                            <div class="avatar-placeholder" style="width: 40px; height: 40px; border-radius: 50%; background: rgba(13, 148, 136, 0.2); border: 2px solid #0d9488; color: #0d9488; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                </a>
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

    <!-- Script de Interatividade para a Sidebar e Responsividade Mobile -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('mobileMenuToggle');
            const closeBtn = document.getElementById('sidebarCloseBtn');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            function openSidebar() {
                if (sidebar) sidebar.classList.add('mobile-open');
                if (overlay) overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (overlay) overlay.addEventListener('click', closeSidebar);

            window.addEventListener('resize', () => {
                if (window.innerWidth > 992) {
                    closeSidebar();
                }
            });

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
