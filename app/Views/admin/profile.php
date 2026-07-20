<div class="profile-page-container">
    
    <div class="page-header">
        <h2>Meu Perfil de Usuário</h2>
        <p class="subtitle">Mantenha seus dados e credenciais atualizados para garantir a segurança de acesso.</p>
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

    <div class="panel-card profile-card">
        <form action="<?= $this->baseUrl('admin/profile') ?>" method="POST" enctype="multipart/form-data" class="profile-form">
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="profile-layout-grid">
                
                <!-- Coluna da Foto de Perfil -->
                <div class="profile-photo-col text-center">
                    <div class="avatar-upload-container">
                        <div class="avatar-preview-wrapper">
                            <?php if (!empty($details['profile_photo_path'])): ?>
                                <div id="avatarPreview" class="avatar-preview" style="background-image: url('<?= $this->baseUrl($details['profile_photo_path']) ?>'); background-size: cover; background-position: center;"></div>
                            <?php else: ?>
                                <div id="avatarPreview" class="avatar-preview-placeholder"><?= strtoupper(substr($details['name'], 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/png, image/jpeg, image/webp" class="file-input-hidden">
                        <label for="profile_photo" class="btn btn-secondary btn-sm">Alterar Foto de Perfil</label>
                        <div class="file-info-text">Tamanho máximo: 2MB. Apenas JPG, PNG ou WEBP.</div>
                    </div>

                    <div class="user-meta-info">
                        <h3><?= htmlspecialchars($details['name']) ?></h3>
                        <span class="badge badge-info"><?= htmlspecialchars($details['role_name']) ?></span>
                        <div class="meta-date">Cadastrado em: <?= date('d/m/Y', strtotime($details['created_at'])) ?></div>
                    </div>
                </div>

                <!-- Coluna de Formulário de Dados -->
                <div class="profile-fields-col">
                    <div class="fields-section">
                        <h4>Informações de Contato</h4>
                        
                        <div class="form-group">
                            <label for="email">E-mail de Acesso (Não alterável)</label>
                            <input type="email" id="email" value="<?= htmlspecialchars($details['email']) ?>" disabled class="input-disabled">
                        </div>

                        <div class="form-group">
                            <label for="name">Nome Completo</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($details['name']) ?>" required placeholder="Digite seu nome completo">
                        </div>

                        <div class="form-group">
                            <label for="celular">Celular (WhatsApp)</label>
                            <input type="text" id="celular" name="celular" value="<?= htmlspecialchars($details['celular']) ?>" required placeholder="Ex: 41999999999">
                        </div>
                    </div>

                    <div class="fields-section mt-4">
                        <h4>Alterar Senha de Acesso</h4>
                        <p class="section-notice">Preencha os campos abaixo apenas se desejar modificar sua senha atual.</p>

                        <div class="form-group">
                            <label for="password">Nova Senha Forte</label>
                            <input type="password" id="password" name="password" placeholder="Digite uma nova senha forte" autocomplete="new-password">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirmar Nova Senha</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirme a nova senha" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="profile-actions text-right">
                        <button type="submit" class="btn btn-primary">
                            Salvar Alterações
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>

</div>

<!-- JavaScript para Preview Instantâneo de Foto -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const photoInput = document.getElementById('profile_photo');
    const avatarPreview = document.getElementById('avatarPreview');

    photoInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('A foto de perfil excede o tamanho máximo de 2MB.');
                photoInput.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => {
                // Remove placeholder text se existir
                avatarPreview.innerText = '';
                avatarPreview.style.backgroundImage = `url('${event.target.result}')`;
                avatarPreview.style.backgroundSize = 'cover';
                avatarPreview.style.backgroundPosition = 'center';
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
