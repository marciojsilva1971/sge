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
                    <?php 
                    $photoPath = !empty($userFull['profile_photo_path']) ? $userFull['profile_photo_path'] : (!empty($colaborador['foto_rosto_path']) ? $colaborador['foto_rosto_path'] : null);
                    ?>
                    <div id="avatarPlaceholder" style="font-size: 38px; font-weight: 700; color: var(--accent-teal-hover); display: <?= $photoPath ? 'none' : 'block' ?>;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <img id="avatarImagePreview" src="<?= $photoPath ? $this->baseUrl($photoPath) : '' ?>" alt="Foto de Perfil" style="width: 100%; height: 100%; object-fit: cover; display: <?= $photoPath ? 'block' : 'none' ?>;">
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
                     <?php
                     $cpfVal = $colaborador['cpf'] ?? '';
                     $cpfMasked = 'Não informado';
                     if (!empty($cpfVal)) {
                         $cpfLimpo = preg_replace('/\D/', '', $cpfVal);
                         if (strlen($cpfLimpo) === 11) {
                             $cpfMasked = substr($cpfLimpo, 0, 3) . '.' . substr($cpfLimpo, 3, 3) . '.' . substr($cpfLimpo, 6, 3) . '-' . substr($cpfLimpo, 9, 2);
                         } else {
                             $cpfMasked = $cpfVal;
                         }
                     }
                     ?>
                     <input type="text" class="form-control" value="<?= htmlspecialchars($cpfMasked) ?>" readonly style="background: rgba(255, 255, 255, 0.03); color: #94a3b8; cursor: not-allowed; border-color: rgba(255, 255, 255, 0.08);">
                 </div>
                 <div class="form-group">
                     <label style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">RG</label>
                     <?php
                     $rgVal = $colaborador['rg'] ?? '';
                     $rgMasked = 'Não informado';
                     if (!empty($rgVal)) {
                         $rgLimpo = preg_replace('/[^\dXx]/', '', $rgVal);
                         if (strlen($rgLimpo) === 9) {
                             $rgMasked = substr($rgLimpo, 0, 2) . '.' . substr($rgLimpo, 2, 3) . '.' . substr($rgLimpo, 5, 3) . '-' . substr($rgLimpo, 8, 1);
                         } elseif (strlen($rgLimpo) === 8) {
                             $rgMasked = substr($rgLimpo, 0, 1) . '.' . substr($rgLimpo, 1, 3) . '.' . substr($rgLimpo, 4, 3) . '-' . substr($rgLimpo, 7, 1);
                         } else {
                             $rgMasked = $rgVal;
                         }
                     }
                     ?>
                     <input type="text" class="form-control" value="<?= htmlspecialchars($rgMasked) ?>" readonly style="background: rgba(255, 255, 255, 0.03); color: #94a3b8; cursor: not-allowed; border-color: rgba(255, 255, 255, 0.08);">
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
                 <?php
                 $celCru = $colaborador['celular_whatsapp'] ?? $user['celular'] ?? '';
                 $celMasked = '';
                 if (!empty($celCru)) {
                     $celLimpo = preg_replace('/\D/', '', $celCru);
                     if (strlen($celLimpo) === 11) {
                         $celMasked = '(' . substr($celLimpo, 0, 2) . ') ' . substr($celLimpo, 2, 5) . '-' . substr($celLimpo, 7, 4);
                     } elseif (strlen($celLimpo) === 10) {
                         $celMasked = '(' . substr($celLimpo, 0, 2) . ') ' . substr($celLimpo, 2, 4) . '-' . substr($celLimpo, 6, 4);
                     } else {
                         $celMasked = $celCru;
                     }
                 }
                 ?>
                 <input type="text" id="celular" name="celular" class="form-control" value="<?= htmlspecialchars($celMasked) ?>" required placeholder="(00) 90000-0000">
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

<!-- Script de Pré-Visualização e Compressão de Imagem JS -->
<script>
function previewAvatarImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Se a imagem for muito pequena (ex: < 50KB), pula a compressão
        if (file.size < 50 * 1024 && (file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/webp')) {
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
            return;
        }

        // Realiza redimensionamento e compressão via Canvas para evitar estourar o php.ini
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                const max_size = 400; // Tamanho máximo da largura ou altura para o avatar

                if (width > height) {
                    if (width > max_size) {
                        height *= max_size / width;
                        width = max_size;
                    }
                } else {
                    if (height > max_size) {
                        width *= max_size / height;
                        height = max_size;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(function(blob) {
                    const compressedFile = new File([blob], "avatar.jpg", { type: "image/jpeg" });
                    
                    // Substitui o arquivo no input file para envio no form
                    try {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        input.files = dataTransfer.files;
                    } catch (err) {
                        console.error("Erro ao definir input.files: ", err);
                    }

                    // Exibe a imagem comprimida no preview
                    const reader2 = new FileReader();
                    reader2.onload = function(e2) {
                        const previewImg = document.getElementById('avatarImagePreview');
                        const placeholder = document.getElementById('avatarPlaceholder');
                        if (previewImg) {
                            previewImg.src = e2.target.result;
                            previewImg.style.display = 'block';
                        }
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                    };
                    reader2.readAsDataURL(compressedFile);
                }, 'image/jpeg', 0.85);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Aplica máscara dinâmica no input de Celular
document.addEventListener('DOMContentLoaded', function() {
    const celularInput = document.getElementById('celular');
    if (celularInput) {
        celularInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 6) {
                e.target.value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
            } else if (value.length > 2) {
                e.target.value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
            } else if (value.length > 0) {
                e.target.value = `(${value}`;
            } else {
                e.target.value = value;
            }
        });
    }
});
</script>
