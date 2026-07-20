<div class="rbac-page-container">
    
    <div class="page-header">
        <h2>Configuração de Perfis e Permissões (RBAC)</h2>
        <p class="subtitle">Gerencie as ações permitidas para cada nível de acesso no sistema.</p>
    </div>

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
