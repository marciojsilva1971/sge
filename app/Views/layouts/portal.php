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
            padding-bottom: 30px;
        }
        .portal-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 16px;
        }
        .portal-header {
            display: flex;
            flex-direction: column;
            padding: 14px 16px;
            background: rgba(30, 41, 59, 0.9);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 100;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }
        .portal-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin-bottom: 12px;
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

        /* Top Navigation Tabs */
        .top-nav {
            display: flex;
            justify-content: space-around;
            align-items: center;
            width: 100%;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 10px;
            gap: 4px;
        }
        .top-nav-item {
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
            padding: 6px 2px;
            border-radius: 8px;
        }
        .top-nav-item:hover, .top-nav-item.active {
            color: var(--accent-teal-hover);
            background: rgba(16, 185, 129, 0.15);
        }
        .top-nav-icon {
            font-size: 18px;
            margin-bottom: 2px;
        }

        /* Style adjustments for alerts */
        .portal-container .alert {
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

    <!-- Portal Header & Navigation Bar (Posicionados no Topo) -->
    <header class="portal-header">
        <div class="portal-header-top">
            <div class="portal-title">SGE Colaborador</div>
            <div class="portal-user" style="display: flex; align-items: center; gap: 10px;">
                <div style="text-align: right;">
                    <span style="color: #fff; font-weight: 600;"><?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></span>
                    <div style="font-size: 10px; color: var(--accent-teal-hover);"><?= htmlspecialchars($user['role_name']) ?></div>
                </div>
                <a href="<?= $this->baseUrl('portal/perfil') ?>" class="avatar-wrapper" style="text-decoration: none;">
                    <?php 
                    $photoPath = !empty($user['profile_photo_path']) ? $user['profile_photo_path'] : (!empty($user['profile_photo']) ? $user['profile_photo'] : null);
                    ?>
                    <?php if ($photoPath): ?>
                        <img src="<?= $this->baseUrl($photoPath) ?>" alt="Avatar" class="avatar-img-sm" style="width: 34px; height: 34px; border: 2px solid var(--accent-teal-hover); border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div class="avatar-placeholder-sm" style="width: 34px; height: 34px; font-size: 14px; background: rgba(16, 185, 129, 0.2); border: 2px solid var(--accent-teal-hover); color: var(--accent-teal-hover); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Links Principais no Topo -->
        <?php
        $navStats = ['viagem' => 0, 'militancia' => 0, 'despesas' => 0];
        if (isset($user['id'])) {
            $db = \App\Core\Database::getInstance();
            $uId = (int)$user['id'];
            $navStats['viagem'] = (int)$db->query("SELECT COUNT(*) FROM `travel_reports` WHERE user_id = {$uId} AND status IN ('ENVIADO', 'REJEITADO', 'EM_ANDAMENTO')")->fetchColumn();
            $navStats['militancia'] = (int)$db->query("SELECT COUNT(*) FROM `militancy_activities` WHERE user_id = {$uId} AND status IN ('PENDENTE', 'REJEITADO')")->fetchColumn();
            $navStats['despesas'] = (int)$db->query("SELECT COUNT(*) FROM `despesas` WHERE user_id = {$uId} AND status IN ('PENDENTE', 'REJEITADO')")->fetchColumn();
        }
        ?>
        <nav class="top-nav">
            <a href="<?= $this->baseUrl('portal') ?>" class="top-nav-item" id="nav-home">
                <span class="top-nav-icon">🏠</span>
                <span>Início</span>
            </a>
            <a href="<?= $this->baseUrl('portal/viagem') ?>" class="top-nav-item" id="nav-viagem">
                <span class="top-nav-icon" style="position: relative;">
                    🚗
                    <?php if (!empty($navStats['viagem'])): ?>
                        <span style="position: absolute; top: -4px; right: -8px; background: #ef4444; color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 10px; line-height: 1; border: 1px solid #0f172a;"><?= $navStats['viagem'] ?></span>
                    <?php endif; ?>
                </span>
                <span>Viagens</span>
            </a>
            <a href="<?= $this->baseUrl('portal/militancia') ?>" class="top-nav-item" id="nav-militancia">
                <span class="top-nav-icon" style="position: relative;">
                    📢
                    <?php if (!empty($navStats['militancia'])): ?>
                        <span style="position: absolute; top: -4px; right: -8px; background: #ef4444; color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 10px; line-height: 1; border: 1px solid #0f172a;"><?= $navStats['militancia'] ?></span>
                    <?php endif; ?>
                </span>
                <span>Militância</span>
            </a>
            <a href="<?= $this->baseUrl('portal/outros') ?>" class="top-nav-item" id="nav-outros">
                <span class="top-nav-icon">📦</span>
                <span>Outros</span>
            </a>
            <a href="<?= $this->baseUrl('portal/despesas') ?>" class="top-nav-item" id="nav-despesas">
                <span class="top-nav-icon" style="position: relative;">
                    💸
                    <?php if (!empty($navStats['despesas'])): ?>
                        <span style="position: absolute; top: -4px; right: -8px; background: #ef4444; color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 10px; line-height: 1; border: 1px solid #0f172a;"><?= $navStats['despesas'] ?></span>
                    <?php endif; ?>
                </span>
                <span>Gastos</span>
            </a>
            <a href="<?= $this->baseUrl('portal/perfil') ?>" class="top-nav-item" id="nav-perfil">
                <span class="top-nav-icon">👤</span>
                <span>Perfil</span>
            </a>
            <a href="<?= $this->baseUrl('logout') ?>" class="top-nav-item" style="color: var(--error-color);">
                <span class="top-nav-icon">🚪</span>
                <span>Sair</span>
            </a>
        </nav>
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

    <!-- Active Navigation Highlight & File Accumulator Manager -->
    <script>
        window.FileAccumulatorManager = window.FileAccumulatorManager || {
            stores: {},

            getStore: function(inputId) {
                if (!this.stores[inputId]) {
                    this.stores[inputId] = new DataTransfer();
                }
                return this.stores[inputId];
            },

            resetStore: function(inputId) {
                this.stores[inputId] = new DataTransfer();
            },

            handleFileSelect: function(inputElem, badgeElemId, containerElemId) {
                if (!inputElem) return;
                const inputId = inputElem.id || inputElem.name || 'default_file_input';
                const store = this.getStore(inputId);

                if (inputElem.files && inputElem.files.length > 0) {
                    Array.from(inputElem.files).forEach(file => {
                        let exists = false;
                        for (let i = 0; i < store.files.length; i++) {
                            let f = store.files[i];
                            if (f.name === file.name && f.size === file.size && f.lastModified === file.lastModified) {
                                exists = true;
                                break;
                            }
                        }
                        if (!exists) {
                            store.items.add(file);
                        }
                    });
                }

                inputElem.files = store.files;
                this.renderThumbs(inputElem, badgeElemId, containerElemId);
            },

            removeFileIndex: function(inputElem, indexToRemove, badgeElemId, containerElemId) {
                if (!inputElem) return;
                const inputId = inputElem.id || inputElem.name || 'default_file_input';
                const currentStore = this.getStore(inputId);
                const newStore = new DataTransfer();

                for (let i = 0; i < currentStore.files.length; i++) {
                    if (i !== indexToRemove) {
                        newStore.items.add(currentStore.files[i]);
                    }
                }

                this.stores[inputId] = newStore;
                inputElem.files = newStore.files;

                this.renderThumbs(inputElem, badgeElemId, containerElemId);
            },

            renderThumbs: function(inputElem, badgeElemId, containerElemId) {
                const badge = document.getElementById(badgeElemId);
                const container = document.getElementById(containerElemId);
                if (!container) return;

                container.innerHTML = '';
                const files = inputElem.files;

                if (files && files.length > 0) {
                    container.style.display = 'grid';
                    if (badge) {
                        badge.style.display = 'inline-block';
                        badge.innerText = '📎 ' + files.length + ' arquivo(s) selecionado(s)';
                    }

                    Array.from(files).forEach((file, idx) => {
                        const box = document.createElement('div');
                        box.style.cssText = 'background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 8px; padding: 6px; text-align: center; position: relative; overflow: hidden; font-size: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.3);';

                        // Botão de Exclusão (✕)
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.innerHTML = '✕';
                        removeBtn.title = 'Remover este arquivo';
                        removeBtn.style.cssText = 'position: absolute; top: 3px; right: 3px; background: #ef4444; color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.5); padding: 0;';
                        removeBtn.onclick = (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            FileAccumulatorManager.removeFileIndex(inputElem, idx, badgeElemId, containerElemId);
                        };
                        box.appendChild(removeBtn);

                        if (file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.src = URL.createObjectURL(file);
                            img.style.cssText = 'width: 100%; height: 75px; object-fit: cover; border-radius: 4px; margin-bottom: 4px; display: block;';
                            box.appendChild(img);
                        } else {
                            const docIcon = document.createElement('div');
                            docIcon.innerHTML = '📄 PDF';
                            docIcon.style.cssText = 'height: 75px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #38bdf8; font-size: 13px; background: rgba(15, 23, 42, 0.5); border-radius: 4px; margin-bottom: 4px;';
                            box.appendChild(docIcon);
                        }

                        const label = document.createElement('span');
                        label.style.cssText = 'color: #cbd5e1; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 500; padding: 0 2px;';
                        label.innerText = file.name;
                        box.appendChild(label);

                        container.appendChild(box);
                    });
                } else {
                    container.style.display = 'none';
                    if (badge) badge.style.display = 'none';
                }
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            const path = window.location.pathname;
            const items = document.querySelectorAll('.top-nav-item');
            items.forEach(item => item.classList.remove('active'));

            if (path.endsWith('portal') || path.endsWith('portal/')) {
                document.getElementById('nav-home').classList.add('active');
            } else if (path.includes('portal/viagem')) {
                document.getElementById('nav-viagem').classList.add('active');
            } else if (path.includes('portal/militancia')) {
                document.getElementById('nav-militancia').classList.add('active');
            } else if (path.includes('portal/outros')) {
                document.getElementById('nav-outros').classList.add('active');
            } else if (path.includes('portal/despesas')) {
                document.getElementById('nav-despesas').classList.add('active');
            } else if (path.includes('portal/perfil')) {
                document.getElementById('nav-perfil').classList.add('active');
            }
        });
    </script>
</body>
</html>
