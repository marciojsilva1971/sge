<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SGE - Portal do Colaborador</title>
    <!-- CSS Global -->
    <link rel="stylesheet" href="<?= $this->baseUrl('css/style.css') ?>">
    <style>
        /* Mobile-First Adjustments */
        body {
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
            padding-bottom: 80px; /* Space for bottom nav */
        }
        .portal-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 16px;
        }
        .portal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: rgba(30, 41, 59, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 100;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        .portal-title {
            font-size: 18px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent-indigo-hover), var(--accent-teal-hover));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .portal-user {
            font-size: 13px;
            color: var(--text-secondary);
            text-align: right;
        }
        .portal-content {
            margin-top: 10px;
        }
        /* Bottom Navigation Bar (Mobile Native Style) */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: rgba(15, 23, 42, 0.95);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 200;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
        }
        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 500;
            transition: var(--transition-fast);
            flex: 1;
            height: 100%;
        }
        .bottom-nav-item:hover, .bottom-nav-item.active {
            color: var(--accent-teal-hover);
        }
        .bottom-nav-icon {
            font-size: 20px;
            margin-bottom: 2px;
        }
        /* Style adjustments for alerts */
        .portal-container .alert {
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

    <!-- Portal Header -->
    <header class="portal-header">
        <div class="portal-title">SGE Colaborador</div>
        <div class="portal-user">
            <span style="color: #fff; font-weight: 600;"><?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></span>
            <div style="font-size: 10px; color: var(--accent-teal-hover);"><?= htmlspecialchars($user['role_name']) ?></div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="portal-container">
        
        <!-- Flash Messages -->
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

        <main class="portal-content">
            <?= $content ?>
        </main>
    </div>

    <!-- Bottom Navigation Bar -->
    <nav class="bottom-nav">
        <a href="<?= $this->baseUrl('portal') ?>" class="bottom-nav-item active" id="nav-home">
            <span class="bottom-nav-icon">🏠</span>
            <span>Início</span>
        </a>
        <a href="<?= $this->baseUrl('portal/viagem') ?>" class="bottom-nav-item" id="nav-viagem">
            <span class="bottom-nav-icon">🚗</span>
            <span>Viagens</span>
        </a>
        <a href="<?= $this->baseUrl('portal/militancia') ?>" class="bottom-nav-item" id="nav-militancia">
            <span class="bottom-nav-icon">📢</span>
            <span>Militância</span>
        </a>
        <a href="<?= $this->baseUrl('logout') ?>" class="bottom-nav-item" style="color: var(--error-color);">
            <span class="bottom-nav-icon">🚪</span>
            <span>Sair</span>
        </a>
    </nav>

    <!-- Active Navigation Highlight -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const path = window.location.pathname;
            const items = document.querySelectorAll('.bottom-nav-item');
            items.forEach(item => item.classList.remove('active'));

            if (path.endsWith('portal') || path.endsWith('portal/')) {
                document.getElementById('nav-home').classList.add('active');
            } else if (path.includes('portal/viagem')) {
                document.getElementById('nav-viagem').classList.add('active');
            } else if (path.includes('portal/militancia')) {
                document.getElementById('nav-militancia').classList.add('active');
            }
        });
    </script>
</body>
</html>
