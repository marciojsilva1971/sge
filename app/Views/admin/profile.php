<?php
/**
 * View: Perfil do Administrador / Usuário do Painel
 */
?>

<div class="panel-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 28px; color: #fff; max-width: 650px; margin: 0 auto;">

    <!-- Cabeçalho do Perfil -->
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="font-size: 24px; font-weight: 700; background: linear-gradient(135deg, var(--accent-indigo-hover), var(--accent-teal-hover)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 6px;">
            Meu Perfil Administrativo
        </h2>
        <p style="font-size: 14px; color: var(--text-secondary);">
            Gerencie suas informações pessoais, avatar e altere sua senha de acesso
        </p>
    </div>

    <form action="<?= $this->baseUrl('admin/profile') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <!-- Foto do Perfil com Pré-Visualização -->
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 28px;">
            <div style="position: relative; width: 110px; height: 110px; margin-bottom: 12px;">
                <div id="avatarPreviewContainer" style="width: 110px; height: 110px; border-radius: 50%; overflow: hidden; border: 3px solid var(--accent-teal-hover); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4); display: flex; align-items: center; justify-content: center; background: #0f172a;">
                    <?php if (!empty($userFull['profile_photo_path'])): ?>
                        <img id="avatarImagePreview" src="<?= $this->baseUrl($userFull['profile_photo_path']) ?>" alt="Foto de Perfil" style="width: 100%; height: 100%; object-fit: cover;">
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
                📷 Selecionar Foto de Perfil
            </label>
            <span style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">JPG, PNG ou WEBP (máx. 10MB)</span>
        </div>

        <!-- Seção 1: Dados Pessoais -->
        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
            <div style="font-size: 15px; font-weight: 600; color: var(--accent-teal-hover); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <span>👤</span> Dados Pessoais
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="name" style="font-size: 13px; color: #fff; display: block; margin-bottom: 6px;">Nome Completo <span style="color: var(--error-color);">*</span></label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($userFull['name'] ?? $user['name']) ?>" required style="background: #1e293b; color: #fff; border: 1px solid #334155; padding: 10px 14px; border-radius: 8px; width: 100%;">
            </div>

             <div class="form-group" style="margin-bottom: 16px;">
                  <label for="celular" style="font-size: 13px; color: #fff; display: block; margin-bottom: 6px;">Celular / WhatsApp</label>
                  <?php
                  $celCru = $userFull['celular'] ?? $user['celular'] ?? '';
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
                  <input type="text" id="celular" name="celular" class="form-control" value="<?= htmlspecialchars($celMasked) ?>" placeholder="(00) 90000-0000" style="background: #1e293b; color: #fff; border: 1px solid #334155; padding: 10px 14px; border-radius: 8px; width: 100%;">
              </div>
 
             <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                 <div class="form-group">
                     <label style="font-size: 13px; color: var(--text-secondary); display: block; margin-bottom: 6px;">E-mail (Login)</label>
                     <input type="email" class="form-control" value="<?= htmlspecialchars($userFull['email'] ?? $user['email'] ?? '') ?>" readonly style="background: rgba(255, 255, 255, 0.03); color: #94a3b8; cursor: not-allowed; border: 1px solid rgba(255, 255, 255, 0.08); padding: 10px 14px; border-radius: 8px; width: 100%;">
                 </div>
                 <div class="form-group">
                     <label style="font-size: 13px; color: var(--text-secondary); display: block; margin-bottom: 6px;">Nível de Acesso (Papel)</label>
                     <input type="text" class="form-control" value="<?= htmlspecialchars($user['role_name']) ?>" readonly style="background: rgba(255, 255, 255, 0.03); color: #94a3b8; cursor: not-allowed; border: 1px solid rgba(255, 255, 255, 0.08); padding: 10px 14px; border-radius: 8px; width: 100%;">
                 </div>
             </div>
         </div>
 
         <!-- Seção 2: Alteração de Senha -->
         <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 20px; margin-bottom: 28px;">
             <div style="font-size: 15px; font-weight: 600; color: var(--accent-teal-hover); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                 <span>🔒</span> Segurança e Alteração de Senha
             </div>
 
             <div class="form-group" style="margin-bottom: 16px;">
                 <label for="senha_atual" style="font-size: 13px; color: #fff; display: block; margin-bottom: 6px;">Senha Atual</label>
                 <input type="password" id="senha_atual" name="senha_atual" class="form-control" placeholder="Informe se for alterar a senha" style="background: #1e293b; color: #fff; border: 1px solid #334155; padding: 10px 14px; border-radius: 8px; width: 100%;">
             </div>
 
             <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                 <div class="form-group">
                     <label for="nova_senha" style="font-size: 13px; color: #fff; display: block; margin-bottom: 6px;">Nova Senha</label>
                     <input type="password" id="nova_senha" name="nova_senha" class="form-control" placeholder="Mínimo 8 caracteres" style="background: #1e293b; color: #fff; border: 1px solid #334155; padding: 10px 14px; border-radius: 8px; width: 100%;">
                 </div>
                 <div class="form-group">
                     <label for="confirmar_senha" style="font-size: 13px; color: #fff; display: block; margin-bottom: 6px;">Confirmar Nova Senha</label>
                     <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" placeholder="Repita a nova senha" style="background: #1e293b; color: #fff; border: 1px solid #334155; padding: 10px 14px; border-radius: 8px; width: 100%;">
                 </div>
             </div>
             <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-top: 8px;">
                 Requisitos: Mínimo 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais.
             </span>
         </div>
 
         <!-- Botão de Ação -->
         <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 16px; font-weight: 600; border-radius: 10px; background: linear-gradient(135deg, var(--accent-teal-hover), #0d9488); border: none; box-shadow: 0 4px 15px rgba(13, 148, 136, 0.4); cursor: pointer; transition: all 0.2s ease;">
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
