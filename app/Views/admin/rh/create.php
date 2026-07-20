<div class="rh-create-container">
    <div class="page-header">
        <h2>Cadastrar Novo Colaborador de Campanha</h2>
        <a href="<?= $this->baseUrl('admin/rh') ?>" class="btn btn-secondary">&larr; Voltar para a Listagem</a>
    </div>

    <form action="<?= $this->baseUrl('admin/rh/novo') ?>" method="POST" enctype="multipart/form-data" class="panel-card form-grid">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <!-- Seção 1: Dados Pessoais de Identificação -->
        <div class="form-section-title">
            <h3>1. Identificação Pessoal</h3>
        </div>

        <div class="form-group span-2">
            <label for="nome_completo">Nome Completo *</label>
            <input type="text" id="nome_completo" name="nome_completo" required placeholder="Ex: João Pedro Santos">
        </div>

        <div class="form-group">
            <label for="cpf">CPF *</label>
            <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00">
        </div>

        <div class="form-group">
            <label for="rg">RG *</label>
            <input type="text" id="rg" name="rg" required placeholder="00.000.000-0">
        </div>

        <div class="form-group">
            <label for="rg_orgao_emissor">Órgão Emissor *</label>
            <input type="text" id="rg_orgao_emissor" name="rg_orgao_emissor" required placeholder="Ex: SSP/PR">
        </div>

        <div class="form-group span-2">
            <label for="documento_foto">Foto do Documento de Identificação (RG, CNH ou CIN)</label>
            <input type="file" id="documento_foto" name="documento_foto" accept=".pdf,.jpg,.jpeg,.png" onchange="previewDocumentoAdmin(this)">
            <small class="form-help">Anexe uma imagem ou PDF do documento oficial de identificação.</small>

            <!-- Thumbnail Preview Container -->
            <div id="doc_admin_preview_wrapper" style="display:none; margin-top:12px; padding:12px; background:rgba(30, 41, 59, 0.8); border:1px dashed var(--teal-primary); border-radius:8px; text-align:center;">
                <p style="font-size:12px; color:var(--teal-primary); margin-bottom:8px; font-weight:bold;">📷 Pré-visualização do Documento (Thumbnail):</p>
                <img id="doc_admin_img_thumb" src="" alt="Thumbnail Documento" style="max-height:180px; max-width:100%; border-radius:6px; box-shadow:0 4px 6px rgba(0,0,0,0.4); display:none; margin:0 auto; border:2px solid rgba(13,148,136,0.5);">
                <div id="doc_admin_pdf_badge" style="display:none; padding:12px; background:rgba(15,23,42,0.9); border-radius:6px; color:#38bdf8; font-weight:bold; font-size:13px;">
                    📄 Arquivo PDF Selecionado: <span id="doc_admin_pdf_name" style="color:#ffffff;"></span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="data_nascimento">Data de Nascimento *</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required onchange="calcularIdadeVisual()">
            <small id="idade_preview" class="form-help" style="font-weight:bold; color:#0d9488;"></small>
        </div>

        <div class="form-group">
            <label for="celular_whatsapp">Celular (WhatsApp) *</label>
            <input type="text" id="celular_whatsapp" name="celular_whatsapp" required placeholder="DDD + Número (Ex: 41999999999)">
        </div>

        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" required placeholder="exemplo@email.com">
        </div>

        <!-- Seção 2: Endereço Residencial -->
        <div class="form-section-title">
            <h3>2. Endereço Residencial</h3>
        </div>

        <div class="form-group">
            <label for="cep">CEP</label>
            <input type="text" id="cep" name="cep" placeholder="80000-000" onkeyup="mascaraCep(this)" onblur="buscarCep(this)" maxlength="9">
            <small id="cep_msg" class="form-help" style="display:none; font-weight:bold;"></small>
        </div>

        <div class="form-group span-2">
            <label for="logradouro">Logradouro / Rua</label>
            <input type="text" id="logradouro" name="logradouro" placeholder="Rua / Avenida...">
        </div>

        <div class="form-group">
            <label for="numero">Número</label>
            <input type="text" id="numero" name="numero" placeholder="123">
        </div>

        <div class="form-group">
            <label for="bairro">Bairro</label>
            <input type="text" id="bairro" name="bairro" placeholder="Centro">
        </div>

        <div class="form-group">
            <label for="cidade">Cidade</label>
            <input type="text" id="cidade" name="cidade" placeholder="Curitiba">
        </div>

        <div class="form-group">
            <label for="uf">UF</label>
            <input type="text" id="uf" name="uf" placeholder="PR" maxlength="2">
        </div>

        <!-- Seção 3: Dados Bancários para Pagamento -->
        <div class="form-section-title">
            <h3>3. Dados Bancários & PIX</h3>
        </div>

        <div class="form-group">
            <label for="banco_nome">Banco</label>
            <input type="text" id="banco_nome" name="banco_nome" placeholder="Ex: Banco do Brasil, Nubank">
        </div>

        <div class="form-group">
            <label for="agencia">Agência</label>
            <input type="text" id="agencia" name="agencia" placeholder="0001">
        </div>

        <div class="form-group">
            <label for="conta">Conta Corrente / Poupança</label>
            <input type="text" id="conta" name="conta" placeholder="12345-6">
        </div>

        <div class="form-group span-2">
            <label for="chave_pix">Chave PIX (CPF, E-mail ou Telefone)</label>
            <input type="text" id="chave_pix" name="chave_pix" placeholder="Informar preferencialmente a chave PIX do colaborador">
        </div>

        <!-- Seção 4: Contrato de Prestação de Serviços de Campanha -->
        <div class="form-section-title">
            <h3>4. Emissão do Contrato de Prestação de Serviços (Art. 100 Lei 9.504/97)</h3>
        </div>

        <div class="form-group span-2">
            <label for="funcao_campanha">Função na Campanha *</label>
            <select id="funcao_campanha" name="funcao_campanha" required>
                <option value="">Selecione a função...</option>
                <option value="Cabo Eleitoral">Cabo Eleitoral</option>
                <option value="Coordenador de Bairro / Região">Coordenador de Bairro / Região</option>
                <option value="Coordenador Geral de Campanha">Coordenador Geral de Campanha</option>
                <option value="Panfletista / Ativista">Panfletista / Ativista</option>
                <option value="Motorista de Campanha">Motorista de Campanha</option>
                <option value="Mobilizador de Rua">Mobilizador de Rua</option>
                <option value="Assessor de Comunicação / Mídias">Assessor de Comunicação / Mídias</option>
                <option value="Segurança / Apoio Logístico">Segurança / Apoio Logístico</option>
                <option value="Outras Funções de Campanha">Outras Funções de Campanha</option>
            </select>
        </div>

        <div class="form-group">
            <label for="valor_contratado">Valor do Contrato *</label>
            <input type="text" id="valor_contratado" name="valor_contratado" placeholder="R$ 0,00" onkeyup="mascaraMoeda(this)">
        </div>

        <div class="form-group">
            <label for="forma_pagamento">Forma de Pagamento</label>
            <input type="text" id="forma_pagamento" name="forma_pagamento" value="PIX / Transferência Bancária">
        </div>

        <div class="form-group">
            <label for="data_inicio">Data Início</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
            <label for="data_fim">Data Fim</label>
            <input type="date" id="data_fim" name="data_fim" value="<?= date('Y-m-d', strtotime('+2 months')) ?>">
        </div>

        <div class="form-group span-2">
            <label for="tipo_assinatura">Modalidade de Assinatura do Contrato *</label>
            <select id="tipo_assinatura" name="tipo_assinatura" onchange="toggleTerceirosUrl()">
                <option value="TERCEIROS_API">Assinatura por Plataforma de Terceiros (ZapSign / Clicksign / Gov.br)</option>
                <option value="MANUAL_UPLOAD">Assinatura Manual (Impresso / Upload da Cópia Assinada)</option>
            </select>
        </div>

        <div class="form-group span-2" id="group_external_url">
            <label for="external_signature_url">Link de Assinatura Externa (Terceiros)</label>
            <input type="url" id="external_signature_url" name="external_signature_url" placeholder="https://app.zapsign.com.br/verificar/...">
            <small class="form-help">Copie o link direto gerado na sua plataforma de assinatura para envio ao colaborador.</small>
        </div>

        <div class="form-actions span-2" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="<?= $this->baseUrl('admin/rh') ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar e Gerar Contrato</button>
        </div>

    </form>
</div>

<script>
function calcularIdadeVisual() {
    const input = document.getElementById('data_nascimento').value;
    const preview = document.getElementById('idade_preview');
    if (!input) return;

    const birth = new Date(input);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }

    if (age < 16) {
        preview.style.color = '#ef4444';
        preview.textContent = `Idade calculada: ${age} anos (❌ Proibido menor de 16 anos)`;
    } else if (age < 18) {
        preview.style.color = '#f59e0b';
        preview.textContent = `Idade calculada: ${age} anos (⚠ Menor de idade - Atividades restritas)`;
    } else {
        preview.style.color = '#0d9488';
        preview.textContent = `Idade calculada: ${age} anos (✔ Maior de idade)`;
    }
}

function toggleTerceirosUrl() {
    const tipo = document.getElementById('tipo_assinatura').value;
    const group = document.getElementById('group_external_url');
    if (tipo === 'TERCEIROS_API') {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
    }
}

function mascaraMoeda(input) {
    let v = input.value.replace(/\D/g, '');
    if (v === '') {
        input.value = '';
        return;
    }
    v = (parseFloat(v) / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = 'R$ ' + v;
}

function previewDocumentoAdmin(input) {
    const wrapper = document.getElementById('doc_admin_preview_wrapper');
    const imgThumb = document.getElementById('doc_admin_img_thumb');
    const pdfBadge = document.getElementById('doc_admin_pdf_badge');
    const pdfName = document.getElementById('doc_admin_pdf_name');

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
            if (cidadeInput) cidadeInput.value = data.localidade || '';

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
