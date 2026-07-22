<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px; color: #f8fafc; font-weight: 700;">Lançamento de Outros Gastos de Campo</h2>
    <p class="subtitle" style="font-size: 12px; color: #94a3b8;">Cadastre despesas diversas da campanha e anexe comprovantes para encaminhamento à fila de aprovações.</p>
</div>

<!-- MODAL DE CONFIRMAÇÃO PÓS-ENVIO DO COMPROVANTE -->
<?php if (isset($_GET['envio_sucesso']) && $_GET['envio_sucesso'] == '1'): ?>
<div id="modalSucessoEnvio" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 2px solid #22c55e; border-radius: 16px; max-width: 450px; width: 100%; padding: 24px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <div style="width: 60px; height: 60px; background: rgba(34, 197, 94, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; color: #4ade80; font-size: 32px; font-weight: bold;">
            ✓
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: #f8fafc; margin-bottom: 8px;">Gasto Cadastrado com Sucesso!</h3>
        <p style="font-size: 13px; color: #94a3b8; margin-bottom: 20px; line-height: 1.5;">
            Seu gasto foi registrado e encaminhado diretamente para a Fila de Aprovações do Administrador.
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <button type="button" onclick="fecharEPrepararNovoOutroGasto()" style="background: #22c55e; color: #0f172a; font-weight: 800; padding: 12px; border-radius: 10px; border: none; text-decoration: none; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                ➕ Cadastrar um Novo Gasto em Outros
            </button>
            <a href="<?= $this->baseUrl('portal/despesas') ?>" style="background: rgba(255, 255, 255, 0.08); color: #f8fafc; font-weight: 600; padding: 12px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.15); text-decoration: none; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                📊 Ir para "Meus Gastos" (Acompanhar Status)
            </a>
        </div>
    </div>
</div>
<script>
    function fecharEPrepararNovoOutroGasto() {
        const modal = document.getElementById('modalSucessoEnvio');
        if (modal) modal.style.display = 'none';
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        const firstInput = document.querySelector('#foto_cnpj_ocr, #description');
        if (firstInput) firstInput.focus();
    }
</script>
<?php endif; ?>

<!-- Formulário de Cadastro de Outros Gastos -->
<div class="panel-card" style="padding: 16px; margin-bottom: 24px; border: 1px solid rgba(56, 189, 248, 0.3);">
    <h3 style="font-size: 15px; font-weight: 700; color: #38bdf8; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
        <span>📦</span> Lançar Novo Gasto Direto
    </h3>

    <form action="<?= $this->baseUrl('portal/outros') ?>" method="POST" enctype="multipart/form-data" style="margin-bottom: 0;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

        <!-- 1º PASSO: CAPTURA E LEITURA DO CNPJ (OCR) -->
        <div id="bloco-captura-cnpj" class="form-group" style="background: rgba(56, 189, 248, 0.08); border: 2px dashed #38bdf8; padding: 14px; border-radius: 12px; margin-bottom: 16px;">
            <label for="foto_cnpj_ocr" style="font-size: 13px; font-weight: 700; color: #38bdf8; display: flex; align-items: center; gap: 6px;">
                📸 1º PASSO: Fotografe ou envie o comprovante/cupom fiscal com CNPJ.
            </label>
            <p style="font-size: 11px; color: var(--text-secondary); margin-bottom: 8px;">
                O sistema tentará extrair o CNPJ e a Razão Social da empresa automaticamente via OCR.
            </p>
            <input type="file" id="foto_cnpj_ocr" accept="image/*, application/pdf" style="padding: 6px; font-size: 12px; width: 100%;">
            
            <button type="button" id="btn-scan-ocr" style="margin-top: 10px; background: #38bdf8; color: #0f172a; font-weight: 700; width: 100%; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; transition: all 0.2s;">
                🔍 Digitalizar e Ler CNPJ (OCR)
            </button>

            <!-- Status do OCR -->
            <div id="ocr_status_badge" style="margin-top: 10px; display: none;"></div>

            <div style="margin-top: 10px; text-align: center;">
                <button type="button" id="btn-pular-ocr" style="background: transparent; border: none; color: var(--text-secondary); font-size: 11px; text-decoration: underline; cursor: pointer;">
                    Ou clique aqui para preencher os dados manualmente
                </button>
            </div>
        </div>

        <!-- CONTAINER REVELADO APÓS LEITURA DO CNPJ OU CLIQUE EM MANUL -->
        <div id="dados-despesa-container" style="display: none; margin-top: 16px; transition: all 0.3s ease;">
            
            <div style="background: rgba(56, 189, 248, 0.12); border-left: 4px solid #38bdf8; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px;">
                <p style="font-size: 12px; font-weight: 600; color: #7dd3fc; margin: 0; line-height: 1.4;">
                    📸 Confira os dados abaixo e anexe as fotos legíveis do comprovante fiscal (com a discriminação das despesas e valor total).
                </p>
            </div>

            <!-- FORNECEDOR: CNPJ E NOME -->
            <div style="display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap;">
                <div class="form-group flex-1" style="min-width: 220px;">
                    <label for="supplier_cnpj" style="font-size: 12px; font-weight: 600;">CNPJ ou CPF do Fornecedor</label>
                    <input type="text" id="supplier_cnpj" name="supplier_cnpj" placeholder="00.000.000/0001-00" required style="font-weight: 600;" oninput="formatarCnpjCpf(this)">
                    <div id="cnpj_supplier_info" style="margin-top: 4px; font-size: 11px;"></div>
                </div>
                <div class="form-group flex-1" style="min-width: 220px;">
                    <label for="supplier_name" style="font-size: 12px; font-weight: 600;">Nome / Razão Social da Empresa</label>
                    <input type="text" id="supplier_name" name="supplier_name" placeholder="Ex: Gráfica Exemplo Ltda" style="font-weight: 500;">
                </div>
            </div>

            <!-- DESCRIÇÃO DO GASTO -->
            <div class="form-group" style="margin-bottom: 14px;">
                <label for="description" style="font-size: 12px; font-weight: 600;">Descrição / Finalidade do Gasto *</label>
                <input type="text" id="description" name="description" placeholder="Ex: Compra de materiais de escritório / Lanche para a equipe" required style="width: 100%; padding: 8px 12px; border-radius: 8px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
            </div>

            <!-- ANEXO DE COMPROVANTES FISCAIS -->
            <div class="form-group" style="background: rgba(15, 23, 42, 0.5); border: 1px dashed rgba(255, 255, 255, 0.2); padding: 12px; border-radius: 10px; margin-bottom: 14px;">
                <label for="comprovante" style="font-size: 12px; font-weight: 700; color: #4ade80;">
                    📁 Foto(s) / PDF do Comprovante Fiscal *
                </label>
                <p style="font-size: 11px; color: var(--text-secondary); margin-top: 4px; margin-bottom: 6px;">
                    Você pode selecionar um ou mais arquivos.
                </p>
                <input type="file" id="comprovante" name="comprovante[]" accept="image/*, application/pdf" multiple required style="padding: 6px; font-size: 12px; width: 100%; margin-top: 4px;">
                <div id="comprovante-count-badge" style="font-size: 11px; font-weight: 600; color: #4ade80; margin-top: 6px; display: none;"></div>
                <div id="galeria-miniaturas-container" style="display: none; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(255, 255, 255, 0.15);"></div>
            </div>

            <!-- VALOR E DATA -->
            <div style="display: flex; gap: 10px; margin-bottom: 14px;">
                <div class="form-group flex-1">
                    <label for="value" style="font-size: 12px; font-weight: 600;">Valor Total (R$) *</label>
                    <input type="text" id="value" name="value" placeholder="R$ 0,00" required style="font-size: 16px; font-weight: 700; color: #38bdf8;" oninput="formatarMoeda(this);">
                </div>
                <div class="form-group flex-1">
                    <label for="date_incurred" style="font-size: 12px; font-weight: 600;">Data do Gasto *</label>
                    <input type="date" id="date_incurred" name="date_incurred" value="<?= date('Y-m-d') ?>" required style="font-size: 13px;">
                </div>
            </div>

            <!-- CATEGORIA SPCE -->
            <div class="form-group" style="margin-bottom: 14px;">
                <label for="spce_category_id" style="font-size: 12px; font-weight: 600;">Categoria SPCE (TSE)</label>
                <select id="spce_category_id" name="spce_category_id" style="font-size: 13px; width: 100%; padding: 8px; border-radius: 8px; background: #1e293b; border: 1px solid #475569; color: #fff;">
                    <option value="0">-- Selecione se souber --</option>
                    <?php foreach ($spceCategories as $cat): ?>
                        <option value="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- OBSERVAÇÕES -->
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="notes" style="font-size: 12px;">Observações Adicionais (opcional)</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Detalhes sobre a necessidade do gasto..." style="width: 100%; padding: 8px; border-radius: 8px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 12px;"></textarea>
            </div>

            <!-- BOTÃO SUBMIT -->
            <button type="submit" style="background: linear-gradient(135deg, #38bdf8, #0284c7); color: #0f172a; font-weight: 800; width: 100%; border: none; padding: 14px; border-radius: 10px; cursor: pointer; font-size: 14px; box-shadow: 0 4px 14px rgba(56, 189, 248, 0.3); transition: all 0.2s;">
                💾 Confirmar e Enviar para Aprovação
            </button>
        </div>
    </form>
</div>

<!-- HISTÓRICO DE OUTROS GASTOS DO COLABORADOR -->
<div class="panel-card" style="padding: 16px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <h3 style="font-size: 14px; font-weight: 700; color: #f8fafc;">Histórico de Outros Gastos Cadastrados</h3>
    </div>

    <?php if (empty($outrosGastos)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 16px 0;">
            Nenhum gasto nesta categoria registrado até o momento.
        </p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($outrosGastos as $gasto): ?>
                <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div>
                            <span style="font-size: 13px; font-weight: 700; color: #f8fafc; display: block;">
                                <?= htmlspecialchars($gasto['description']) ?>
                            </span>
                            <span style="font-size: 11px; color: var(--text-secondary);">
                                <?= htmlspecialchars($gasto['supplier_name'] ?? 'Fornecedor N/A') ?> &bull; <?= date('d/m/Y', strtotime($gasto['date_incurred'])) ?>
                            </span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 14px; font-weight: 700; color: #38bdf8;">
                                R$ <?= number_format(floatval($gasto['value']), 2, ',', '.') ?>
                            </span>
                            <div style="margin-top: 4px; display: flex; justify-content: flex-end; gap: 6px; align-items: center;">
                                <?php if ($gasto['status'] === 'APROVADO'): ?>
                                    <span class="badge badge-success" style="font-size: 10px;">Aprovado</span>
                                <?php elseif ($gasto['status'] === 'PAGO'): ?>
                                    <span class="badge badge-success" style="font-size: 10px; background: #059669;">Pago</span>
                                <?php elseif ($gasto['status'] === 'REJEITADO'): ?>
                                    <span class="badge badge-danger" style="font-size: 10px;">Recusado</span>
                                    <a href="<?= $this->baseUrl('portal/despesas') ?>" class="btn btn-warning btn-sm" style="font-size: 10px; padding: 2px 6px; font-weight: 700; background: #eab308; color: #0f172a; text-decoration: none;">
                                        ✏️ Corrigir
                                    </a>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 10px;">Na Fila de Aprovação</span>
                                    <a href="<?= $this->baseUrl('portal/despesas') ?>" class="btn btn-secondary btn-sm" style="font-size: 10px; padding: 2px 6px; font-weight: 600; background: rgba(255,255,255,0.1); color: #fff; text-decoration: none;">
                                        ✏️ Editar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($gasto['doc_id'])): ?>
                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(255,255,255,0.08);">
                            <a href="<?= $this->baseUrl('admin/financeiro/comprovante?id=' . $gasto['doc_id']) ?>" target="_blank" style="font-size: 11px; color: #4ade80; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                                📄 Visualizar Comprovante Anexado
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- SCRIPTS DE OCR E ANEXOS -->
<script>
function formatarMoeda(input) {
    if (!input) return;
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    value = (value / 100).toFixed(2) + '';
    value = value.replace(".", ",");
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
    input.value = "R$ " + value;
}

function formatarCnpjCpf(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 14) v = v.substring(0, 14);
    if (v.length <= 11) {
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d)/, '$1-$2');
    }
    input.value = v;
}

document.addEventListener('DOMContentLoaded', () => {
    const btnScanOcr = document.getElementById('btn-scan-ocr');
    const fotoOcrInput = document.getElementById('foto_cnpj_ocr');
    const ocrStatusBadge = document.getElementById('ocr_status_badge');
    const dadosContainer = document.getElementById('dados-despesa-container');
    const blocoCapturaCnpj = document.getElementById('bloco-captura-cnpj');
    const ocrNoticeBanner = document.getElementById('ocr_notice_banner');
    const btnPularOcr = document.getElementById('btn-pular-ocr');
    const cnpjInput = document.getElementById('supplier_cnpj');
    const nameInput = document.getElementById('supplier_name');
    const infoDiv = document.getElementById('cnpj_supplier_info');
    const comprovanteInput = document.getElementById('comprovante');
    const galeriaContainer = document.getElementById('galeria-miniaturas-container');
    const countBadge = document.getElementById('comprovante-count-badge');

    // Botão pular OCR
    if (btnPularOcr) {
        btnPularOcr.addEventListener('click', () => {
            if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
            if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'none';
            dadosContainer.style.display = 'block';
            cnpjInput.focus();
        });
    }

    // Validador matemático oficial do Digito Verificador de CNPJ (Módulo 11)
    function validarCNPJ(cnpj) {
        cnpj = String(cnpj || '').replace(/[^\d]+/g, '');
        if (cnpj.length !== 14) return false;
        if (/^(\d)\1+$/.test(cnpj)) return false;

        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (resultado != digitos.charAt(0)) return false;

        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (resultado != digitos.charAt(1)) return false;

        return true;
    }

    // Extrator inteligente de CNPJ com tolerância avançada a ruídos comuns de OCR
    function extrairCNPJDoTexto(text) {
        if (!text) return null;

        console.log("=== TEXTO OCR EXTRAÍDO (OUTROS GASTOS) ===");
        console.log(text);

        function normalizarTextoOCR(txt) {
            return txt
                .replace(/[OoQ]/g, '0')
                .replace(/[Il|!L]/g, '1')
                .replace(/[Zz]/g, '2')
                .replace(/[Ss$]/g, '5')
                .replace(/[Bb]/g, '8')
                .replace(/[Gg]/g, '6');
        }

        // METODO 1: Procura específica pela palavra-chave CNPJ / C.N.P.J / MF / CGC
        const cnpjKeywordRegex = /(?:CNPJ|C\.?N\.?P\.?J\.?|MF|CGC)[\s\:\.\-\/]*([0-9OolI|!LsSZzBbGg\.\-\/\s]{14,35})/gi;
        let match;
        while ((match = cnpjKeywordRegex.exec(text)) !== null) {
            if (match[1]) {
                let norm = normalizarTextoOCR(match[1]);
                let digitsOnly = norm.replace(/\D/g, '');
                for (let i = 0; i <= digitsOnly.length - 14; i++) {
                    let sub = digitsOnly.substring(i, i + 14);
                    if (validarCNPJ(sub)) {
                        console.log("✓ CNPJ identificado via Método 1 (Palavra-Chave + Validador):", sub);
                        return { cnpj: sub, valido: true };
                    }
                }
                if (digitsOnly.length >= 14) {
                    let candidate = digitsOnly.substring(0, 14);
                    return { cnpj: candidate, valido: validarCNPJ(candidate) };
                }
            }
        }

        // METODO 2: Padrão visual clássico de CNPJ (XX.XXX.XXX/XXXX-XX) no texto inteiro
        const padraoVisualRegex = /\d{2}[\.\s]*\d{3}[\.\s]*\d{3}[\/\s]*\d{4}[\-\s]*\d{2}/g;
        const matchesVisuais = text.match(padraoVisualRegex) || [];
        for (let raw of matchesVisuais) {
            let digits = raw.replace(/\D/g, '');
            if (digits.length === 14) {
                return { cnpj: digits, valido: validarCNPJ(digits) };
            }
        }

        // METODO 3: Varredura de 14 dígitos válidos no texto completo normalizado
        const textoNorm = normalizarTextoOCR(text);
        const todosDigitos = textoNorm.replace(/\D/g, '');
        for (let i = 0; i <= todosDigitos.length - 14; i++) {
            let sub = todosDigitos.substring(i, i + 14);
            if (validarCNPJ(sub)) {
                return { cnpj: sub, valido: true };
            }
        }

        return null;
    }

    async function garantirTesseractCarregado() {
        if (typeof Tesseract !== 'undefined') return true;
        return new Promise((resolve) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
            script.onload = () => resolve(true);
            script.onerror = () => resolve(false);
            document.head.appendChild(script);
        });
    }

    // Otimizador de nitidez/contraste em Canvas HTML5 para comprovantes fiscais
    function otimizarImagemParaOCR(file, callback) {
        if (!file || !file.type.startsWith('image/')) {
            callback(file);
            return;
        }
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = function() {
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                let maxDim = 1500;
                let width = img.width;
                let height = img.height;
                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                const imgData = ctx.getImageData(0, 0, width, height);
                const d = imgData.data;
                for (let i = 0; i < d.length; i += 4) {
                    let gray = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
                    gray = gray < 135 ? Math.max(0, gray - 35) : Math.min(255, gray + 35);
                    d[i] = gray;
                    d[i + 1] = gray;
                    d[i + 2] = gray;
                }
                ctx.putImageData(imgData, 0, 0);

                canvas.toBlob(function(blob) {
                    URL.revokeObjectURL(url);
                    callback(blob || file);
                }, 'image/jpeg', 0.88);
            } catch (e) {
                URL.revokeObjectURL(url);
                callback(file);
            }
        };
        img.onerror = function() {
            URL.revokeObjectURL(url);
            callback(file);
        };
        img.src = url;
    }

    // Processamento de OCR
    if (btnScanOcr) {
        btnScanOcr.addEventListener('click', async () => {
            if (!fotoOcrInput.files || fotoOcrInput.files.length === 0) {
                alert('Por favor, escolha uma foto do comprovante fiscal primeiro.');
                return;
            }

            const ok = await garantirTesseractCarregado();
            if (!ok || typeof Tesseract === 'undefined') {
                alert('Não foi possível carregar a biblioteca de OCR. Por favor, digite os dados manualmente.');
                if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
                if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'block';
                dadosContainer.style.display = 'block';
                return;
            }

            const file = fotoOcrInput.files[0];
            ocrStatusBadge.style.display = 'block';
            ocrStatusBadge.innerHTML = '<span style="color: #38bdf8; font-size: 12px; font-weight: 600;">⏳ Lendo texto do comprovante (OCR)... Por favor aguarde.</span>';
            btnScanOcr.disabled = true;

            otimizarImagemParaOCR(file, async function(processedFile) {
                try {
                    const worker = await Tesseract.createWorker('por');
                    await worker.setParameters({
                        tessedit_pageseg_mode: '6',
                    });

                    const ret = await worker.recognize(processedFile);
                    await worker.terminate();

                    console.log("Texto extraído via OCR (PSM 6):", ret.data.text);
                    let detectedResult = extrairCNPJDoTexto(ret.data.text);

                    if (!detectedResult) {
                        const workerAuto = await Tesseract.createWorker('por');
                        const retAuto = await workerAuto.recognize(processedFile);
                        await workerAuto.terminate();

                        console.log("Texto extraído via OCR (PSM Auto):", retAuto.data.text);
                        detectedResult = extrairCNPJDoTexto(retAuto.data.text);
                    }

                    if (!detectedResult) {
                        const workerOrig = await Tesseract.createWorker('por');
                        const retOrig = await workerOrig.recognize(file);
                        await workerOrig.terminate();

                        console.log("Texto extraído via OCR (Imagem Original):", retOrig.data.text);
                        detectedResult = extrairCNPJDoTexto(retOrig.data.text);
                    }

                    if (detectedResult && detectedResult.cnpj) {
                        const detectedCnpj = detectedResult.cnpj;
                        const isValidoDV = detectedResult.valido;

                        if (cnpjInput) {
                            let clean = detectedCnpj.replace(/\D/g, "");
                            if (clean.length === 14) {
                                cnpjInput.value = clean.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
                            } else {
                                cnpjInput.value = detectedCnpj;
                            }
                        }

                        if (isValidoDV) {
                            ocrStatusBadge.innerHTML = '<span style="color: #38bdf8; font-size: 12px; font-weight: 700;">🔍 Consultando Receita Federal...</span>';
                            consultarCnpjApi(detectedCnpj, true);
                        } else {
                            if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
                            if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'block';
                            dadosContainer.style.display = 'block';
                            if (infoDiv) infoDiv.innerHTML = '<span style="color: #f59e0b;">⚠️ Confira o número do CNPJ acima.</span>';
                            if (cnpjInput) cnpjInput.focus();
                            setTimeout(() => {
                                alert("⚠️ O sistema identificou o CNPJ (" + cnpjInput.value + ") no comprovante, mas alguns dígitos podem precisar de confirmação.\n\nPor favor, confira o número e altere se necessário.");
                            }, 100);
                        }
                    } else {
                        if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
                        if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'block';
                        dadosContainer.style.display = 'block';
                        if (cnpjInput) cnpjInput.focus();
                        setTimeout(() => {
                            alert("⚠️ Não foi possível efetuar a leitura automática do CNPJ no comprovante.\n\nPor favor, digite o CNPJ e a Razão Social da empresa manualmente nos campos abaixo.");
                        }, 100);
                    }
                } catch (err) {
                    console.error("Erro OCR:", err);
                    if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
                    if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'block';
                    dadosContainer.style.display = 'block';
                    if (cnpjInput) cnpjInput.focus();
                    setTimeout(() => {
                        alert("⚠️ Não foi possível efetuar a leitura automática do CNPJ no comprovante.\n\nPor favor, digite o CNPJ e a Razão Social da empresa manualmente nos campos abaixo.");
                    }, 100);
                } finally {
                    btnScanOcr.disabled = false;
                }
            });
        });
    }

    // Consulta de CNPJ via API com suporte a confirmação e limpeza de campos
    async function consultarCnpjApi(cnpj, isOcr = false) {
        const clean = cnpj.replace(/\D/g, '');
        if (clean.length !== 14) return;

        try {
            if (infoDiv) infoDiv.innerHTML = '<span style="color: #38bdf8;">Buscando Razão Social na Receita Federal...</span>';
            const res = await fetch('<?= $this->baseUrl("api/cnpj/consultar") ?>?cnpj=' + clean);
            const data = await res.json();
            if (data.success && (data.company || data.razao_social)) {
                const corporateName = data.razao_social || (data.company ? (data.company.corporate_name || data.company.trade_name) : '');
                if (cnpjInput) {
                    cnpjInput.value = clean.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
                }
                if (nameInput) nameInput.value = corporateName;
                if (infoDiv) infoDiv.innerHTML = '<span style="color: #4ade80;">✓ Empresa: ' + corporateName + '</span>';

                if (isOcr) {
                    if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
                    if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'none';
                    dadosContainer.style.display = 'block';
                    setTimeout(() => {
                        alert("✅ CNPJ e Razão Social lidos com sucesso!\n\n" + corporateName + "\nCNPJ: " + cnpjInput.value);
                    }, 100);
                }
            } else {
                if (isOcr) {
                    if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
                    if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'block';
                    dadosContainer.style.display = 'block';
                    if (nameInput) {
                        nameInput.value = '';
                        nameInput.focus();
                    }
                    if (infoDiv) infoDiv.innerHTML = '<span style="color: #f59e0b;">⚠️ CNPJ capturado. Informe a Razão Social ao lado.</span>';
                    setTimeout(() => {
                        alert("⚠️ CNPJ (" + cnpjInput.value + ") lido do comprovante, mas não localizado na base pública da Receita Federal.\n\nVocê pode ajustar o CNPJ se houver dígito impreciso ou digitar a Razão Social da empresa manualmente no campo ao lado.");
                    }, 100);
                } else {
                    if (nameInput) nameInput.value = '';
                    if (infoDiv) infoDiv.innerHTML = '<span style="color: #ef4444;">⚠️ CNPJ não localizado na Receita Federal.</span>';
                    const querManter = confirm("CNPJ (" + clean + ") não foi localizado na base pública da Receita Federal.\n\nDeseja manter este CNPJ e preencher o Nome / Razão Social da empresa manualmente?");
                    if (querManter) {
                        if (nameInput) nameInput.focus();
                    } else {
                        if (cnpjInput) {
                            cnpjInput.value = '';
                            cnpjInput.focus();
                        }
                    }
                }
            }
        } catch (e) {
            console.error('Erro na consulta CNPJ:', e);
            if (isOcr) {
                if (cnpjInput) cnpjInput.value = '';
                if (nameInput) nameInput.value = '';
                if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
                if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'block';
                dadosContainer.style.display = 'block';
                if (cnpjInput) cnpjInput.focus();
            }
            if (infoDiv) infoDiv.innerHTML = '<span style="color: #ef4444;">⚠️ Erro ao conectar com base da Receita</span>';
        }
    }

    if (cnpjInput) {
        cnpjInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length > 14) value = value.slice(0, 14);

            if (value.length > 12) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
            }
            e.target.value = value;

            if (value.replace(/\D/g, "").length === 14) {
                consultarCnpjApi(value, false);
            }
        });
    }

    // Galeria de miniaturas de arquivos acumulados com exclusão (✕)
    if (comprovanteInput) {
        comprovanteInput.addEventListener('change', () => {
            FileAccumulatorManager.handleFileSelect(comprovanteInput, 'comprovante-count-badge', 'galeria-miniaturas-container');
        });
    }
});
</script>
