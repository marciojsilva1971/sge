<div class="auth-card" style="max-width: 700px; margin: 30px auto;">
    <div class="auth-header">
        <div class="auth-logo">SGE</div>
        <h2>Cadastro de Colaborador de Campanha</h2>
        <p class="auth-subtitle">Preencha seus dados cadastrais e dê anuência para o contrato de campanha.</p>
    </div>

    <form action="<?= $this->baseUrl('colaborador/cadastro') ?>" method="POST" enctype="multipart/form-data" class="auth-form form-grid">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <!-- Identificação -->
        <div class="form-section-title span-2">
            <h4 style="color:var(--teal-primary); border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:5px; margin-bottom:10px;">1. Dados de Identificação</h4>
        </div>

        <div class="form-group span-2">
            <label for="nome_completo">Nome Completo *</label>
            <input type="text" id="nome_completo" name="nome_completo" required placeholder="Digite seu nome completo">
        </div>

        <div class="form-group">
            <label for="cpf">CPF *</label>
            <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00">
        </div>

        <div class="form-group">
            <label for="rg">RG *</label>
            <input type="text" id="rg" name="rg" required placeholder="Sua carteira de identidade">
        </div>

        <div class="form-group">
            <label for="rg_orgao_emissor">Órgão Emissor / UF *</label>
            <input type="text" id="rg_orgao_emissor" name="rg_orgao_emissor" required placeholder="Ex: SSP/PR">
        </div>

        <div class="form-group span-2">
            <label for="documento_foto">Documento Oficial com Foto (RG, CNH ou CIN) *</label>
            <input type="file" id="documento_foto" name="documento_foto" required accept=".pdf,.jpg,.jpeg,.png" style="background:#1e293b; padding:8px; border-radius:6px; border:1px solid #334155; width:100%;" onchange="previewDocumento(this)">
            <small class="form-help">Anexe uma foto nítida ou arquivo PDF do seu documento (CNH, RG ou CIN).</small>
            
            <!-- Thumbnail Preview Container -->
            <div id="doc_preview_wrapper" style="display:none; margin-top:12px; padding:12px; background:rgba(30, 41, 59, 0.8); border:1px dashed var(--teal-primary); border-radius:8px; text-align:center;">
                <p style="font-size:12px; color:var(--teal-primary); margin-bottom:8px; font-weight:bold;">📷 Pré-visualização do Documento (Thumbnail):</p>
                <img id="doc_img_thumb" src="" alt="Thumbnail Documento" style="max-height:180px; max-width:100%; border-radius:6px; box-shadow:0 4px 6px rgba(0,0,0,0.4); display:none; margin:0 auto; border:2px solid rgba(13,148,136,0.5);">
                <div id="doc_pdf_badge" style="display:none; padding:12px; background:rgba(15,23,42,0.9); border-radius:6px; color:#38bdf8; font-weight:bold; font-size:13px;">
                    📄 Arquivo PDF Selecionado: <span id="doc_pdf_name" style="color:#ffffff;"></span>
                </div>
            </div>
        </div>

        <div class="form-group span-2">
            <label for="foto_rosto">Foto do seu Rosto (Selfie / Avatar para Crachá e Perfil) *</label>
            <input type="file" id="foto_rosto" name="foto_rosto" required accept=".jpg,.jpeg,.png,.webp" style="background:#1e293b; padding:8px; border-radius:6px; border:1px solid #334155; width:100%;" onchange="previewFotoRosto(this)">
            <small class="form-help">Anexe uma foto nítida e bem iluminada do seu rosto (Selfie). Ela será usada no seu crachá e perfil de acesso do SGE.</small>
            
            <!-- Thumbnail Preview Container para Foto do Rosto -->
            <div id="rosto_preview_wrapper" style="display:none; margin-top:12px; padding:12px; background:rgba(30, 41, 59, 0.8); border:1px dashed var(--teal-primary); border-radius:8px; text-align:center;">
                <p style="font-size:12px; color:var(--teal-primary); margin-bottom:8px; font-weight:bold;">👤 Pré-visualização da Foto de Rosto (Avatar):</p>
                <img id="rosto_img_thumb" src="" alt="Thumbnail Avatar Rosto" style="width:120px; height:120px; border-radius:50%; object-fit:cover; box-shadow:0 4px 10px rgba(0,0,0,0.5); display:none; margin:0 auto; border:3px solid var(--teal-primary);">
            </div>
        </div>

        <div class="form-group">
            <label for="data_nascimento">Data de Nascimento *</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required>
        </div>

        <div class="form-group">
            <label for="celular_whatsapp">Celular (WhatsApp) *</label>
            <input type="text" id="celular_whatsapp" name="celular_whatsapp" required placeholder="DDD + Número">
        </div>

        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" required placeholder="seu@email.com">
        </div>

        <!-- Endereço -->
        <div class="form-section-title span-2" style="margin-top:15px;">
            <h4 style="color:var(--teal-primary); border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:5px; margin-bottom:10px;">2. Endereço</h4>
        </div>

        <div class="form-group">
            <label for="cep">CEP</label>
            <input type="text" id="cep" name="cep" placeholder="00000-000" onkeyup="mascaraCep(this)" onblur="buscarCep(this)" maxlength="9">
            <small id="cep_msg" class="form-help" style="display:none; font-weight:bold;"></small>
        </div>

        <div class="form-group span-2">
            <label for="logradouro">Logradouro / Rua</label>
            <input type="text" id="logradouro" name="logradouro" placeholder="Endereço completo">
        </div>

        <div class="form-group">
            <label for="numero">Número</label>
            <input type="text" id="numero" name="numero" placeholder="123">
        </div>

        <div class="form-group">
            <label for="cidade">Cidade / UF</label>
            <input type="text" id="cidade" name="cidade" placeholder="Cidade">
        </div>

        <!-- Dados Bancários -->
        <div class="form-section-title span-2" style="margin-top:15px;">
            <h4 style="color:var(--teal-primary); border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:5px; margin-bottom:10px;">3. Dados Bancários / PIX</h4>
        </div>

        <div class="form-group span-2">
            <label for="chave_pix">Chave PIX *</label>
            <input type="text" id="chave_pix" name="chave_pix" required placeholder="Sua chave PIX (CPF, Telefone ou E-mail)">
        </div>

        <!-- Anuência LGPD (Opt-in) -->
        <div class="form-group span-2" style="background-color: rgba(15, 23, 42, 0.6); padding: 15px; border-radius: 6px; border: 1px solid rgba(13, 148, 136, 0.3); margin-top: 15px;">
            <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                <input type="checkbox" name="optin_whatsapp" value="1" required style="margin-top: 3px;">
                <span style="font-size: 13px; color: #cbd5e1; line-height: 1.4;">
                    <strong>Declaração de Anuência (Opt-In LGPD):</strong> Autorizo a equipe de campanha a enviar notificações relativas ao contrato de trabalho, comprovantes e avisos através do meu WhatsApp e E-mail fornecidos.
                </span>
            </label>
        </div>

        <div class="form-actions span-2" style="margin-top: 20px;">
            <button type="submit" class="btn btn-teal btn-block" style="padding: 12px; font-size: 16px;">
                Enviar Cadastro e Prosseguir para o Contrato
            </button>
        </div>
    </form>
</div>

<script>
function previewDocumento(input) {
    const wrapper = document.getElementById('doc_preview_wrapper');
    const imgThumb = document.getElementById('doc_img_thumb');
    const pdfBadge = document.getElementById('doc_pdf_badge');
    const pdfName = document.getElementById('doc_pdf_name');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        wrapper.style.display = 'block';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgThumb.src = e.target.result;
                imgThumb.style.display = 'block';
                pdfBadge.style.display = 'none';
            }
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf' || file.name.endsWith('.pdf')) {
            imgThumb.style.display = 'none';
            pdfBadge.style.display = 'block';
            pdfName.textContent = file.name;
        } else {
            wrapper.style.display = 'none';
        }
    } else {
        wrapper.style.display = 'none';
    }
}

function previewFotoRosto(input) {
    const wrapper = document.getElementById('rosto_preview_wrapper');
    const imgThumb = document.getElementById('rosto_img_thumb');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.type.startsWith('image/')) {
            wrapper.style.display = 'block';
            const reader = new FileReader();
            reader.onload = function(e) {
                imgThumb.src = e.target.result;
                imgThumb.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            wrapper.style.display = 'none';
        }
    } else {
        wrapper.style.display = 'none';
    }
}

function mascaraCep(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 5) {
        v = v.replace(/^(\d{5})(\d)/, '$1-$2');
    }
    input.value = v.substring(0, 9);

    const clean = input.value.replace(/\D/g, '');
    if (clean.length === 8) {
        buscarCep(input);
    }
}

function buscarCep(input) {
    const cepClean = input.value.replace(/\D/g, '');
    const msgBox = document.getElementById('cep_msg');

    if (cepClean.length !== 8) return;

    if (msgBox) {
        msgBox.style.display = 'block';
        msgBox.style.color = '#38bdf8';
        msgBox.textContent = '🔍 Buscando endereço nos Correios...';
    }

    fetch(`https://viacep.com.br/ws/${cepClean}/json/`)
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                if (msgBox) {
                    msgBox.style.color = '#ef4444';
                    msgBox.textContent = '⚠ CEP não encontrado nos Correios.';
                }
                return;
            }

            if (msgBox) {
                msgBox.style.color = '#10b981';
                msgBox.textContent = '✔ Endereço preenchido automaticamente!';
                setTimeout(() => { msgBox.style.display = 'none'; }, 4000);
            }

            const logradouroInput = document.getElementById('logradouro');
            const cidadeInput = document.getElementById('cidade');
            const bairroInput = document.getElementById('bairro');
            const ufInput = document.getElementById('uf');
            const numeroInput = document.getElementById('numero');

            if (logradouroInput) logradouroInput.value = data.logradouro || '';
            if (bairroInput) bairroInput.value = data.bairro || '';
            if (ufInput) ufInput.value = data.uf || '';

            if (cidadeInput) {
                if (ufInput) {
                    cidadeInput.value = data.localidade || '';
                } else {
                    cidadeInput.value = (data.localidade && data.uf) ? (data.localidade + ' / ' + data.uf) : (data.localidade || '');
                }
            }

            if (numeroInput) {
                numeroInput.focus();
            }
        })
        .catch(err => {
            console.error(err);
            if (msgBox) {
                msgBox.style.color = '#f59e0b';
                msgBox.textContent = '⚠ Indisponibilidade temporária na busca dos Correios.';
            }
        });
}
</script>
