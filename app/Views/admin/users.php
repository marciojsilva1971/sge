<div class="users-page-container">
    
    <div class="page-header">
        <h2>Gerenciamento de Usuários e Acessos</h2>
        <button id="btnOpenInviteModal" class="btn btn-primary">
            + Cadastrar Novo Usuário
        </button>
    </div>

    <!-- Alertas Visuais de Sucesso ou Erro -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="padding: 14px 18px; background-color: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.4); border-radius: 8px; color: #15803d; font-weight: 600; font-size: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 20px;">✅</span>
            <span><?= $success ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="padding: 14px 18px; background-color: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 8px; color: #b91c1c; font-weight: 600; font-size: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 20px;">⚠️</span>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Barra de Filtros -->
    <div class="panel-card filters-card">
        <form method="GET" action="<?= $this->baseUrl('admin/users') ?>" class="filters-form" id="filters-form">
            <div class="form-group mb-0">
                <label for="filter-name">Buscar por Nome</label>
                <input type="text" id="filter-name" name="name" value="<?= htmlspecialchars($filters['name']) ?>" placeholder="Ex: Márcio">
            </div>

            <div class="form-group mb-0">
                <label for="filter-role">Cargo</label>
                <select id="filter-role" name="role_id">
                    <option value="">Todos os Cargos</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= $filters['role_id'] == $role['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-0">
                <label for="filter-status">Status</label>
                <select id="filter-status" name="status">
                    <option value="">Todos os Status</option>
                    <option value="ATIVO" <?= $filters['status'] === 'ATIVO' ? 'selected' : '' ?>>Ativo</option>
                    <option value="PENDENTE" <?= $filters['status'] === 'PENDENTE' ? 'selected' : '' ?>>Pendente</option>
                    <option value="INATIVO" <?= $filters['status'] === 'INATIVO' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div class="filters-actions">
                <button type="submit" class="btn btn-teal">Filtrar</button>
                <a href="<?= $this->baseUrl('admin/users') ?>" class="btn btn-secondary">Limpar</a>
            </div>
        </form>
    </div>

    <!-- Tabela de Listagem -->
    <div class="panel-card" id="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-users">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Celular</th>
                        <th>Cargo</th>
                        <th>Status</th>
                        <th>Cadastro em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Nenhum usuário correspondente aos filtros foi encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="avatar-sm">
                                        <?php if (!empty($u['profile_photo_path'])): ?>
                                            <img src="<?= $this->baseUrl($u['profile_photo_path']) ?>" alt="Avatar" class="avatar-img-sm">
                                        <?php else: ?>
                                            <div class="avatar-placeholder-sm"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['celular']) ?></td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($u['role_name']) ?></span></td>
                                <td>
                                    <?php if ($u['status'] === 'ATIVO'): ?>
                                        <span class="badge badge-success">Ativo</span>
                                    <?php elseif ($u['status'] === 'PENDENTE'): ?>
                                        <span class="badge badge-warning">Pendente</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                                        <button type="button" class="btn btn-secondary btn-sm btn-open-reset-pwd" data-id="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['name']) ?>" style="padding: 4px 8px; font-size: 12px; margin-right: 5px;">
                                            🔑 Senha
                                        </button>
                                        <?php if ($u['status'] === 'PENDENTE'): ?>
                                            <form action="<?= $this->baseUrl('admin/users/activate-direct') ?>" method="POST" style="display:inline; margin-right: 5px;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="btn btn-teal btn-sm" style="padding: 4px 8px; font-size: 12px;">
                                                    Ativar (Testes)
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="<?= $this->baseUrl('admin/users/delete') ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="button" class="btn btn-secondary btn-sm btn-delete-confirm" style="color: var(--error-color); border-color: rgba(239, 68, 68, 0.2); background-color: rgba(239, 68, 68, 0.05); transition: all 0.2s ease;">
                                                Excluir
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-secondary" style="font-size: 12px; font-style: italic;">Atual</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<!-- Modal para Cadastrar Usuário Novo -->
<div id="inviteModal" class="modal-overlay hidden">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Cadastrar Novo Usuário</h3>
            <button id="btnCloseInviteModal" class="btn-close-modal">&times;</button>
        </div>
        <form action="<?= $this->baseUrl('admin/users/create') ?>" method="POST" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="name">Nome Completo</label>
                <input type="text" id="name" name="name" required placeholder="Ex: Márcio Silva">
            </div>

            <div class="form-group">
                <label for="email">E-mail do Usuário</label>
                <input type="email" id="email" name="email" required placeholder="Ex: marcio@campanha.com">
            </div>

            <div class="form-group">
                <label for="celular">Celular (WhatsApp)</label>
                <input type="text" id="celular" name="celular" required placeholder="DDD + Número (Ex: 41999999999)">
            </div>

            <div class="form-group">
                <label for="role_id">Cargo (Perfil de Acesso)</label>
                <select id="role_id" name="role_id" required>
                    <option value="">Selecione o Cargo...</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Senha de Acesso</label>
                <input type="password" id="password" name="password" required placeholder="Digite a senha" autocomplete="new-password">
                <small class="form-help">Mínimo 8 caracteres, contendo maiúscula, minúscula, número e caractere especial.</small>
            </div>

            <div class="modal-footer">
                <button type="button" id="btnCancelInvite" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Cadastrar Usuário</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Sucesso do Convite (Click-to-Chat & Cópia de Link) -->
<?php if (!empty($inviteSuccess)): ?>
<div id="successModal" class="modal-overlay">
    <div class="modal-card success-modal-card">
        <div class="modal-header">
            <h3>Convite Criado com Sucesso!</h3>
            <button onclick="document.getElementById('successModal').classList.add('hidden')" class="btn-close-modal">&times;</button>
        </div>
        <div class="modal-body text-center">
            <div class="success-icon">✓</div>
            <p>O convite para <strong><?= htmlspecialchars($inviteSuccess['name']) ?></strong> foi gerado.</p>
            
            <div class="notification-status">
                <div class="status-item">
                    <span>Notificação Automática Z-API:</span>
                    <?php if ($inviteSuccess['zapi_sent']): ?>
                        <span class="badge badge-success">Enviado com sucesso!</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Falhou (Sem sinal ou indisponível)</span>
                    <?php endif; ?>
                </div>
                <div class="status-item">
                    <span>Notificação E-mail Simulado:</span>
                    <?php if ($inviteSuccess['email_sent']): ?>
                        <span class="badge badge-success">Gravado no Log Local</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Erro técnico</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="activation-link-box">
                <label>Link de Ativação Manual (Expira em 24h):</label>
                <div class="link-copy-wrapper">
                    <input type="text" id="activationLinkText" value="<?= htmlspecialchars($inviteSuccess['activation_link']) ?>" readonly class="input-disabled text-sm">
                    <button onclick="copyActivationLink()" class="btn btn-teal btn-sm">Copiar</button>
                </div>
            </div>

            <!-- Botão de Click-to-Chat do WhatsApp para o Administrador Enviar Manualmente do seu Celular -->
            <a href="<?= htmlspecialchars($inviteSuccess['click_to_chat']) ?>" target="_blank" class="btn btn-success btn-block whatsapp-send-btn">
                💬 Enviar Convite pelo meu WhatsApp Pessoal
            </a>
            
            <p class="modal-notice">Caso o envio automático Z-API falhe, use o botão acima ou copie o link para enviar manualmente ao destinatário.</p>
        </div>
        <div class="modal-footer">
            <button onclick="document.getElementById('successModal').classList.add('hidden')" class="btn btn-secondary">Fechar</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal para Redefinir Senha do Usuário -->
<div id="resetPasswordModal" class="modal-overlay hidden">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Redefinir Senha de Usuário</h3>
            <button id="btnCloseResetPwdModal" onclick="closeResetPwdModal()" class="btn-close-modal">&times;</button>
        </div>
        <form action="<?= $this->baseUrl('admin/users/reset-password') ?>" method="POST" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="reset_user_id" name="user_id" value="">

            <div class="form-group">
                <label>Usuário Selecionado:</label>
                <input type="text" id="reset_user_name" class="input-disabled" readonly style="font-weight:bold;">
            </div>

            <div class="form-group">
                <label for="new_password">Nova Senha Forte</label>
                <input type="password" id="new_password" name="new_password" required placeholder="Digite a nova senha" autocomplete="new-password">
                <small class="form-help">Mínimo 8 caracteres, contendo maiúscula, minúscula, número e caractere especial (@, #, $, _, !).</small>
            </div>

            <div class="modal-footer">
                <button type="button" id="btnCancelResetPwd" onclick="closeResetPwdModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Alterar Senha</button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript para controle de modais e cópia de links -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inviteModal = document.getElementById('inviteModal');
    const btnOpen = document.getElementById('btnOpenInviteModal');
    const btnClose = document.getElementById('btnCloseInviteModal');
    const btnCancel = document.getElementById('btnCancelInvite');

    if (btnOpen) {
        btnOpen.addEventListener('click', () => {
            inviteModal.classList.remove('hidden');
        });
    }

    const closeModal = () => {
        inviteModal.classList.add('hidden');
    };

    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);

    // Controla modal de Redefinição de Senha
    const resetPwdModal = document.getElementById('resetPasswordModal');
    const btnCloseResetPwd = document.getElementById('btnCloseResetPwdModal');
    const btnCancelResetPwd = document.getElementById('btnCancelResetPwd');
    const resetUserIdInput = document.getElementById('reset_user_id');
    const resetUserNameInput = document.getElementById('reset_user_name');

    // Usando Event Delegation para botões de senha que podem ser recriados via AJAX
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-open-reset-pwd');
        if (btn) {
            resetUserIdInput.value = btn.dataset.id;
            resetUserNameInput.value = btn.dataset.name;
            resetPwdModal.classList.remove('hidden');
        }
    });

    window.closeResetPwdModal = function() {
        const modal = document.getElementById('resetPasswordModal');
        if (modal) modal.classList.add('hidden');
    };

    if (btnCloseResetPwd) btnCloseResetPwd.addEventListener('click', window.closeResetPwdModal);
    if (btnCancelResetPwd) btnCancelResetPwd.addEventListener('click', window.closeResetPwdModal);

    // Fecha ao clicar fora do modal card
    inviteModal.addEventListener('click', (e) => {
        if (e.target === inviteModal) {
            closeModal();
        }
    });

    if (resetPwdModal) {
        resetPwdModal.addEventListener('click', (e) => {
            if (e.target === resetPwdModal) {
                closeResetPwdModal();
            }
        });
    }

    // Lógica para exclusão em dois passos usando Event Delegation
    function resetDeleteButton(btn) {
        btn.isConfirmState = false;
        btn.textContent = 'Excluir';
        btn.style.color = 'var(--error-color)';
        btn.style.borderColor = 'rgba(239, 68, 68, 0.2)';
        btn.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
        if (btn.timeoutId) {
            clearTimeout(btn.timeoutId);
            btn.timeoutId = null;
        }
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-delete-confirm');
        if (!btn) return;

        const form = btn.closest('form');
        if (!btn.isConfirmState) {
            e.preventDefault();
            btn.isConfirmState = true;
            btn.textContent = 'Confirmar?';
            btn.style.backgroundColor = 'var(--error-color)';
            btn.style.color = '#ffffff';
            btn.style.borderColor = 'var(--error-color)';

            btn.timeoutId = setTimeout(() => {
                resetDeleteButton(btn);
            }, 4000);
        } else {
            form.submit();
        }
    });

    document.addEventListener('mouseout', (e) => {
        const btn = e.target.closest('.btn-delete-confirm');
        if (!btn) return;

        const related = e.relatedTarget;
        if (related && btn.contains(related)) return;

        if (btn.isConfirmState && !btn.mouseleaveTimeoutId) {
            btn.mouseleaveTimeoutId = setTimeout(() => {
                resetDeleteButton(btn);
                btn.mouseleaveTimeoutId = null;
            }, 2000);
        }
    });

    document.addEventListener('mouseover', (e) => {
        const btn = e.target.closest('.btn-delete-confirm');
        if (!btn) return;
        if (btn.mouseleaveTimeoutId) {
            clearTimeout(btn.mouseleaveTimeoutId);
            btn.mouseleaveTimeoutId = null;
        }
    });

    // Busca/Filtro Dinâmico via AJAX
    let searchTimeout = null;
    const filtersForm = document.getElementById('filters-form');
    const tableContainer = document.getElementById('table-container');

    function performSearch() {
        if (!filtersForm || !tableContainer) return;

        const formData = new FormData(filtersForm);
        const params = new URLSearchParams(formData);

        const newUrl = window.location.pathname + '?' + params.toString();
        window.history.pushState({ path: newUrl }, '', newUrl);

        tableContainer.style.opacity = '0.5';
        tableContainer.style.transition = 'opacity 0.15s ease';

        fetch(newUrl)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableContainer = doc.getElementById('table-container');
                if (newTableContainer) {
                    tableContainer.innerHTML = newTableContainer.innerHTML;
                }
                tableContainer.style.opacity = '1';
            })
            .catch(error => {
                console.error('Erro na pesquisa:', error);
                tableContainer.style.opacity = '1';
            });
    }

    if (filtersForm) {
        filtersForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (searchTimeout) clearTimeout(searchTimeout);
            performSearch();
        });

        const inputs = filtersForm.querySelectorAll('input[type="text"], select');
        inputs.forEach(input => {
            if (input.tagName.toLowerCase() === 'select') {
                input.addEventListener('change', performSearch);
            } else {
                input.addEventListener('input', function() {
                    if (searchTimeout) clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(performSearch, 300);
                });
            }
        });
    }
});

function copyActivationLink() {
    const copyText = document.getElementById("activationLinkText");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // Para dispositivos móveis
    
    navigator.clipboard.writeText(copyText.value)
        .then(() => {
            alert("Link de ativação copiado para a área de transferência!");
        })
        .catch(err => {
            console.error('Falha ao copiar link: ', err);
        });
}
</script>
