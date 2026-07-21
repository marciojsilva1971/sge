<?php
/**
 * View: Perfil do Colaborador (Mobile Portal)
 */
?>

<div class="panel-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 24px; color: #fff; margin-bottom: 24px;">

    <!-- Cabeçalho do Perfil -->
    <div style="text-align: center; margin-bottom: 24px;">
        <h2 style="font-size: 22px; font-weight: 700; background: linear-gradient(135deg, var(--accent-indigo-hover), var(--accent-teal-hover)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 4px;">
            Meu Perfil de Colaborador
        </h2>
        <p style="font-size: 13px; color: var(--text-secondary);">
            Gerencie seus dados pessoais, foto de avatar e credenciais de acesso
        </p>
    </div>

    <!-- Formulário Principal de Atualização de Perfil -->
    <form action="<?= $this->baseUrl('portal/perfil') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <!-- Foto do Rosto / Avatar com Pré-Visualização -->
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 28px;">
            <div style="position: relative; width: 110px; height: 110px; margin-bottom: 12px;">
                <div id="avatarPreviewContainer" style="width: 110px; height: 110px; border-radius: 50%; overflow: hidden; border: 3px solid var(--accent-teal-hover); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4); display: flex; align-items: center; justify-content: center; background: #0f172a;">
                    <?php if (!empty($userFull['profile_photo_path'])): ?>
                        <img id="avatarImagePreview" src="<?= $this->baseUrl($userFull['profile_photo_path']) ?>" alt="Foto de Perfil" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php elseif (!empty($colaborador['foto_rosto_path'])): ?>
                        <img id="avatarImagePreview" src="<?= $this->baseUrl($colaborador['foto_rosto_path']) ?>" alt="Foto de Perfil" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div id="avatarPlaceholder" style="font-size: 38px; font-weight: 700; color: var(--accent-teal-hover);">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <img id="avatarImagePreview" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                    <?php endif; ?>
                </div>
                <!-- Botão Sobreposto de Câmera -->
                <label for="foto_rosto" style="position: absolute; bottom: 0; right: 0; background: var(--accent-teal-hover); color: #fff; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.5); font-size: 16px; border: 2px solid #0f172a;" title="Alterar Foto">
                    📷
                </label>
            </div>

            <input type="file" id="foto_rosto" name="foto_rosto" accept="image/jpeg,image/png,image/webp" style="display: none;" onchange="previewAvatarImage(this)">
            <label for="foto_rosto" class="btn btn-secondary" style="font-size: 12px; padding: 6px 14px; border-radius: 20px; cursor: pointer; border: 1px solid var(--accent-teal-hover); color: var(--accent-teal-hover);">
                📷 Alterar Foto do Rosto
            </label>
            <span style="font-size: 10px; color: var(--text-secondary); margin-top: 4px;">Formats: JPG, PNG ou WEBP (máx. 10MB)</span>
        </div>

        <!-- Seção 1: Dados Pessoais Cadastrados (Apenas Leitura) -->
        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
            <div style="font-size: 14px; font-weight: 600; color: var(--accent-teal-hover); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                <span>📋</span> Dados de Cadastro (RH)
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Nome Completo</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($colaborador['nome_completo'] ?? $user['name']) ?>" readonly style="background: rgba(255, 255, 255, 0.03); color: #94a3b8; cursor: not-allowed; border-color: rgba(255, 255, 255, 0.08);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">CPF</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($colaborador['cpf'] ?? 'Não informado') ?>" readonly style="background: rgba(255, 255, 255, 0.03); color: #94a3b8; cursor: not-allowed; border-color: rgba(255, 255, 255, 0.08);">
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">RG</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($colaborador['rg'] ?? 'Não informado') ?>" readonly style="background: rgba(255, 255, 255, 0.03); color: #94a3b8; cursor: not-allowed; border-color: rgba(255, 255, 255, 0.08);">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">E-mail de Acesso</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background: rgba(255, 255, 255, 0.03); color: #94a3b8; cursor: not-allowed; border-color: rgba(255, 255, 255, 0.08);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Função / Papel</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($colaborador['papel_eleitoral'] ?? $user['role_name']) ?>" readonly style="background: rgba(255, 255, 255, 0.03); color: #94a3b8; cursor: not-allowed; border-color: rgba(255, 255, 255, 0.08);">
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Status RH</label>
                    <span class="badge badge-success" style="display: block; padding: 8px; text-align: center; border-radius: 8px; font-weight: 600;">
                        ✓ <?= htmlspecialchars($colaborador['status_homologacao'] ?? 'ATIVO') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Seção 2: Contato e Dados Editáveis -->
        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
            <div style="font-size: 14px; font-weight: 600; color: var(--accent-teal-hover); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                <span>📱</span> Contato & PIX
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label for="celular" style="font-size: 12px; color: #fff; display: block; margin-bottom: 4px;">Celular / WhatsApp <span style="color: var(--error-color);">*</span></label>
                <input type="text" id="celular" name="celular" class="form-control" value="<?= htmlspecialchars($colaborador['celular_whatsapp'] ?? $user['celular'] ?? '') ?>" required placeholder="(00) 90000-0000">
            </div>

            <div class="form-group">
                <label style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Chave PIX Cadastrada</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($colaborador['chave_pix'] ?? 'Não informada') ?>" readonly style="background: rgba(255, 255, 255, 0.03); color: #94a3b8; cursor: not-allowed; border-color: rgba(255, 255, 255, 0.08);">
            </div>
        </div>

        <!-- Seção 3: Alteração de Senha de Acesso -->
        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
            <div style="font-size: 14px; font-weight: 600; color: var(--accent-teal-hover); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                <span>🔒</span> Alterar Senha de Acesso (Opcional)
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label for="senha_atual" style="font-size: 12px; color: #fff; display: block; margin-bottom: 4px;">Senha Atual</label>
                <input type="password" id="senha_atual" name="senha_atual" class="form-control" placeholder="Informe apenas se for alterar a senha">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label for="nova_senha" style="font-size: 12px; color: #fff; display: block; margin-bottom: 4px;">Nova Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" class="form-control" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label for="confirmar_senha" style="font-size: 12px; color: #fff; display: block; margin-bottom: 4px;">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" placeholder="Repita a nova senha">
                </div>
            </div>
        </div>

        <!-- Botão de Ação -->
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 15px; font-weight: 600; border-radius: 12px; background: linear-gradient(135deg, var(--accent-teal-hover), #0d9488); border: none; box-shadow: 0 4px 15px rgba(13, 148, 136, 0.4); cursor: pointer;">
            💾 Salvar Alterações do Perfil
        </button>

    </form>
</div>

<!-- Script de Pré-Visualização de Imagem JS -->
<script>
function previewAvatarImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        if (file.size > 10 * 1024 * 1024) {
            alert('A foto selecionada excede o limite de 10MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('avatarImagePreview');
            const placeholder = document.getElementById('avatarPlaceholder');

            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            }
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    }
}
</script>
