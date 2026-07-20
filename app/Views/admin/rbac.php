<div class="rbac-page-container">
    
    <div class="page-header">
        <h2>Configuração de Perfis e Permissões (RBAC)</h2>
        <p class="subtitle">Gerencie as ações permitidas para cada nível de acesso no sistema.</p>
    </div>

    <!-- Alertas Visuais de Sucesso ou Erro -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="padding: 14px 18px; background-color: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.4); border-radius: 8px; color: #15803d; font-weight: 600; font-size: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 20px;">✅</span>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="padding: 14px 18px; background-color: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 8px; color: #b91c1c; font-weight: 600; font-size: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 20px;">⚠️</span>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= $this->baseUrl('admin/rbac') ?>" method="POST" class="rbac-form">
        <!-- Token CSRF -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="roles-rbac-grid">
            <?php foreach ($roles as $role): ?>
                <div class="panel-card role-rbac-card">
                    <div class="role-card-header">
                        <h3><?= htmlspecialchars($role['name']) ?></h3>
                        <span class="role-desc"><?= htmlspecialchars($role['description'] ?: 'Perfil de Acesso do Sistema') ?></span>
                    </div>
                    
                    <div class="role-card-body">
                        <h4>Ações e Permissões</h4>
                        <div class="permissions-list">
                            <?php foreach ($availablePermissions as $key => $label): ?>
                                <label class="checkbox-container">
                                    <input type="checkbox" name="permissions[<?= $role['id'] ?>][<?= $key ?>]" value="1" 
                                        <?= !empty($role['permissions'][$key]) ? 'checked' : '' ?>
                                        <?= $role['name'] === 'ADMINISTRADOR' && $key === 'configure_rbac' ? 'disabled' : '' ?>
                                    >
                                    <span class="checkmark"></span>
                                    <span class="checkbox-label"><?= htmlspecialchars($label) ?></span>
                                </label>
                                
                                <!-- Mantém o input oculto para o admin para impedir que ele perca o acesso acidentalmente ao desmarcar a própria permissão de rbac -->
                                <?php if ($role['name'] === 'ADMINISTRADOR' && $key === 'configure_rbac'): ?>
                                    <input type="hidden" name="permissions[<?= $role['id'] ?>][<?= $key ?>]" value="1">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="rbac-actions text-right">
            <button type="submit" class="btn btn-primary">
                Salvar Alterações de Acesso
            </button>
        </div>
    </form>

</div>
