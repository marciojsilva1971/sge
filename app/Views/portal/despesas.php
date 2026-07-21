<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px;">Meus Gastos</h2>
    <p class="subtitle" style="font-size: 12px;">Lance e acompanhe suas despesas de campo pendentes de vinculação financeira/fiscal.</p>
</div>

<!-- Cards de Resumo Pessoal -->
<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 24px;">
    <div class="panel-card" style="padding: 12px; margin-bottom: 0; text-align: center; background: rgba(30, 41, 59, 0.4);">
        <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Total Lançado</span>
        <span style="font-size: 14px; font-weight: bold; color: var(--text-primary);">R$ <?= number_format($totalLaunched, 2, ',', '.') ?></span>
    </div>
    <div class="panel-card" style="padding: 12px; margin-bottom: 0; text-align: center; background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.2);">
        <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Aguardando Vínculo</span>
        <span style="font-size: 14px; font-weight: bold; color: var(--warning-color);"><?= $pendingCount ?></span>
    </div>
    <div class="panel-card" style="padding: 12px; margin-bottom: 0; text-align: center; background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2);">
        <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Aprovados</span>
        <span style="font-size: 14px; font-weight: bold; color: var(--success-color);"><?= $approvedCount ?></span>
    </div>
</div>

<!-- Formulário de Lançamento -->
<div class="panel-card" style="padding: 16px; margin-bottom: 24px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
        <h3 style="font-size: 14px; font-weight: 600;">Lançar Novo Gasto</h3>
    </div>

    <form action="<?= $this->baseUrl('portal/despesas') ?>" method="POST" enctype="multipart/form-data" id="expenseForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

        <!-- 1º PASSO: UPLOAD / DIGITALIZAÇÃO DO COMPROVANTE -->
        <div class="form-group" style="background: rgba(13, 148, 136, 0.08); border: 2px dashed var(--accent-teal); padding: 14px; border-radius: 12px; margin-bottom: 16px;">
            <label for="comprovante" style="font-size: 13px; font-weight: 700; color: var(--accent-teal-hover); display: flex; align-items: center; gap: 6px;">
                📸 1º PASSO: Anexar / Fotografar Comprovante Fiscal (JPEG, PNG ou PDF)
            </label>
            <p style="font-size: 11px; color: var(--text-secondary); margin-bottom: 8px;">
                Anexe a foto da nota/cupom fiscal primeiro. O sistema lerá a imagem via OCR para autocompletar o CNPJ e o nome do fornecedor!
            </p>
            <input type="file" id="comprovante" name="comprovante" accept="image/*,application/pdf" required style="padding: 6px; font-size: 12px; width: 100%;">
            
            <button type="button" id="btn-scan-ocr" style="margin-top: 10px; background: var(--accent-teal); color: #0f172a; font-weight: 700; width: 100%; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; transition: all 0.2s;">
                🔍 Digitalizar e Ler Comprovante (OCR)
            </button>

            <!-- Status do OCR -->
            <div id="ocr_status_badge" style="margin-top: 10px; display: none;"></div>

            <!-- Preview do Comprovante selecionado -->
            <div id="preview-container" style="display: none; border-radius: 10px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); background-color: #0c1524; text-align: center; padding: 10px; margin-top: 10px; position: relative;">
                <img id="preview-image" src="" alt="Comprovante" style="max-width: 100%; max-height: 180px; object-fit: contain; display: none; margin: 0 auto; border-radius: 8px;">
                <div id="pdf-preview-text" style="display: none; font-size: 12px; color: var(--text-secondary); padding: 12px 0;">
                    <span style="font-size: 28px; display: block; margin-bottom: 4px;">📄</span>
                    Arquivo PDF Selecionado
                </div>
                <div style="font-size: 10px; color: var(--text-secondary); margin-top: 4px;" id="file-name-preview"></div>
            </div>
        </div>

        <!-- DADOS DO FORNECEDOR (CNPJ + NOME) -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
                <label for="supplier_cnpj_cpf">CPF ou CNPJ do Fornecedor</label>
                <input type="text" id="supplier_cnpj_cpf" name="supplier_cnpj_cpf" placeholder="00.000.000/0001-00 ou 000.000.000-00" required>
                <div id="cnpj_supplier_info" style="margin-top: 4px; font-size: 11px;"></div>
            </div>
            <div class="form-group">
                <label for="supplier_name">Razão Social / Nome do Fornecedor</label>
                <input type="text" id="supplier_name" name="supplier_name" placeholder="Ex: Auto Posto Silva Ltda" required style="font-weight: 500;">
            </div>
        </div>

        <!-- DEMAIS DADOS DA DESPESA -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
                <label for="expense_type_id">Tipo de Gasto</label>
                <select id="expense_type_id" name="expense_type_id" required style="width: 100%; border-radius: 6px; padding: 10px; background: #0f172a; border: 1px solid #334155; color: #fff; box-sizing: border-box; font-family: inherit; font-size: 13px;">
                    <option value="">-- Selecione o Tipo de Gasto --</option>
                    <?php foreach ($expenseTypes as $type): ?>
                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="value">Valor (R$)</label>
                <input type="text" id="value" name="value" placeholder="R$ 0,00" required style="font-weight: 600; color: var(--warning-color);">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
            <div class="form-group">
                <label for="description">Finalidade do Gasto / Descrição</label>
                <input type="text" id="description" name="description" placeholder="Ex: Alimentação da equipe de panfletagem" required>
            </div>
            <div class="form-group">
                <label for="date_incurred">Data do Gasto</label>
                <input type="date" id="date_incurred" name="date_incurred" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <button type="submit" id="submitBtn" class="btn btn-teal btn-block" style="margin-top: 10px;">
            Confirmar e Enviar Despesa
        </button>
    </form>
</div>

<!-- Histórico de Lançamentos -->
<div class="panel-card" style="padding: 16px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
        <h3 style="font-size: 14px; font-weight: 600;">Histórico de Gastos Enviados</h3>
    </div>

    <?php if (empty($expenses)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhum gasto lançado até o momento.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($expenses as $exp): ?>
                <div style="padding: 12px; background: rgba(15, 23, 42, 0.4); border-radius: 10px; border: 1px solid rgba(255,255,255,0.04); font-size: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-weight: 600; color: var(--text-primary);"><?= date('d/m/Y', strtotime($exp['date_incurred'])) ?></span>
                        <div>
                            <?php if ($exp['status'] === 'APROVADO' || $exp['status'] === 'PAGO'): ?>
                                <span class="badge badge-success">Homologada</span>
                            <?php elseif ($exp['status'] === 'REJEITADO'): ?>
                                <span class="badge badge-danger" title="<?= htmlspecialchars($exp['notes'] ?? 'Sem justificativa') ?>">Recusada</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pendente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p style="color: var(--text-primary); font-weight: 500; margin-bottom: 4px;">
                        <span class="badge badge-secondary" style="font-size: 10px; padding: 2px 6px; background-color: #334155; color: var(--text-primary); border-radius: 4px; margin-right: 6px;"><?= htmlspecialchars($exp['expense_type_name'] ?? 'Sem Tipo') ?></span>
                        <?= htmlspecialchars($exp['description']) ?>
                    </p>
                    <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-secondary); font-size: 11px;">
                        <span>Fornecedor: <?= htmlspecialchars($exp['supplier_name']) ?></span>
                        <span style="font-weight: 600; color: var(--warning-color);">R$ <?= number_format($exp['value'], 2, ',', '.') ?></span>
                    </div>
                    <?php if ($exp['status'] === 'REJEITADO' && !empty($exp['notes'])): ?>
                        <div style="margin-top: 8px; padding: 8px; background-color: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 6px; color: var(--error-color); font-size: 11px;">
                            <strong>Motivo da rejeição:</strong> <?= htmlspecialchars($exp['notes']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Tesseract.js OCR -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputCnpjCpf = document.getElementById('supplier_cnpj_cpf');
    const inputSupplierName = document.getElementById('supplier_name');
    const cnpjInfoDiv = document.getElementById('cnpj_supplier_info');
    const ocrStatusBadge = document.getElementById('ocr_status_badge');
    const inputValue = document.getElementById('value');
    const inputComprovante = document.getElementById('comprovante');
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');
    const pdfPreviewText = document.getElementById('pdf-preview-text');
    const fileNamePreview = document.getElementById('file-name-preview');

    function consultarCnpj(cleanCnpj) {
        if (cleanCnpj.length !== 14) return;
        if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = '<span style="color: var(--accent-teal); font-weight: 500;">🔍 Buscando Razão Social na Receita Federal...</span>';

        fetch('<?= $this->baseUrl("api/cnpj/consultar") ?>?cnpj=' + cleanCnpj)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (inputSupplierName) inputSupplierName.value = data.razao_social;
                    if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = `<span style="color: #22c55e; font-weight: 600;">✔ ${data.razao_social}</span> <span style="color: #94a3b8;">(${data.municipio}/${data.uf})</span>`;
                } else {
                    if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = `<span style="color: #ef4444;">⚠️ ${data.message || 'CNPJ não encontrado'}</span>`;
                }
            })
            .catch(err => {
                console.error(err);
                if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = '<span style="color: #ef4444;">⚠️ Erro ao consultar Receita Federal</span>';
            });
    }

    // Máscara dinâmica CPF / CNPJ e consulta automática
    inputCnpjCpf.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else {
            value = value.slice(0, 14);
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
        }
        e.target.value = value;

        const clean = value.replace(/\D/g, '');
        if (clean.length === 14) {
            consultarCnpj(clean);
        } else {
            if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = '';
        }
    });

    // Máscara dinâmica Moeda BRL
    inputValue.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (!value) {
            e.target.value = '';
            return;
        }
        value = (parseFloat(value) / 100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        e.target.value = 'R$ ' + value;
    });

    const btnScanOcr = document.getElementById('btn-scan-ocr');

    function executarDigitalizacaoOCR() {
        const file = inputComprovante ? inputComprovante.files[0] : null;

        if (!file) {
            if (ocrStatusBadge) {
                ocrStatusBadge.style.display = 'block';
                ocrStatusBadge.innerHTML = `
                    <div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">⚠️</span>
                        <span>Por favor, escolha um arquivo ou tire uma foto primeiro no botão acima!</span>
                    </div>
                `;
            }
            return;
        }

        if (ocrStatusBadge) {
            ocrStatusBadge.style.display = 'block';
            ocrStatusBadge.innerHTML = `
                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">⏳</span>
                    <span>Iniciando motor de leitura OCR... Por favor, aguarde um instante.</span>
                </div>
            `;
        }

        if (file.type === 'application/pdf') {
            if (ocrStatusBadge) {
                ocrStatusBadge.innerHTML = `
                    <div style="padding: 10px 12px; background: rgba(56, 189, 248, 0.15); border: 1px solid #38bdf8; border-radius: 8px; color: #7dd3fc; font-weight: 600; font-size: 12px; display: flex; flex-direction: column; gap: 4px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 16px;">📄</span>
                            <span>Arquivo PDF anexado com sucesso!</span>
                        </div>
                        <div style="font-size: 11px; font-weight: normal; color: #bae6fd; margin-left: 24px;">
                            Por favor, informe o CNPJ e a Razão Social do Fornecedor nos campos abaixo.
                        </div>
                    </div>
                `;
            }
            return;
        }

        const rodarOCR = () => {
            if (!window.Tesseract) {
                exibirAvisoManual();
                return;
            }

            Tesseract.recognize(file, 'por', {
                logger: m => {
                    if (m.status === 'recognizing text' && ocrStatusBadge) {
                        const pct = Math.round((m.progress || 0) * 100);
                        ocrStatusBadge.innerHTML = `
                            <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">🔍</span>
                                <span>Lendo comprovante via OCR (${pct}%)... Processando imagem.</span>
                            </div>
                        `;
                    }
                }
            }).then(({ data: { text } }) => {
                console.log("Texto extraído via OCR:", text);
                const match = text.match(/(\d{2}[\.\s]?\d{3}[\.\s]?\d{3}[\/\s]?\d{4}[\-\s]?\d{2})/);
                if (match) {
                    const detectedCnpj = match[0].replace(/\D/g, "");
                    if (detectedCnpj.length === 14) {
                        if (inputCnpjCpf) inputCnpjCpf.value = detectedCnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
                        consultarCnpj(detectedCnpj);
                        if (ocrStatusBadge) {
                            ocrStatusBadge.innerHTML = `
                                <div style="padding: 10px 12px; background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; border-radius: 8px; color: #4ade80; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px;">✅</span>
                                    <span>CNPJ (${detectedCnpj}) identificado! Buscando dados na Receita Federal...</span>
                                </div>
                            `;
                        }
                    } else {
                        exibirAvisoManual();
                    }
                } else {
                    exibirAvisoManual();
                }
            }).catch(err => {
                console.error("Erro OCR:", err);
                exibirAvisoManual();
            });
        };

        if (typeof Tesseract === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
            script.onload = rodarOCR;
            script.onerror = () => exibirAvisoManual();
            document.head.appendChild(script);
        } else {
            rodarOCR();
        }
    }

    function exibirAvisoManual() {
        if (ocrStatusBadge) {
            ocrStatusBadge.innerHTML = `
                <div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px; display: flex; flex-direction: column; gap: 4px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">⚠️</span>
                        <span>Não foi possível identificar o CNPJ nesta foto automaticamente.</span>
                    </div>
                    <div style="font-size: 11px; font-weight: normal; color: #fef08a; margin-left: 24px;">
                        Por favor, digite o CNPJ e o Nome do Fornecedor nos campos abaixo.
                    </div>
                </div>
            `;
        }
    }

    // Preview do Comprovante e Escaneamento OCR
    inputComprovante.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) {
            previewContainer.style.display = 'none';
            if (ocrStatusBadge) ocrStatusBadge.style.display = 'none';
            return;
        }

        previewContainer.style.display = 'block';
        fileNamePreview.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;

        if (file.type.match('image.*')) {
            const reader = new FileReader();
            reader.onload = (event) => {
                previewImage.src = event.target.result;
                previewImage.style.display = 'block';
                pdfPreviewText.style.display = 'none';
            };
            reader.readAsDataURL(file);
        } else {
            previewImage.style.display = 'none';
            pdfPreviewText.style.display = 'block';
            pdfPreviewText.innerHTML = file.type === 'application/pdf' ? '<span style="font-size: 28px; display: block; margin-bottom: 4px;">📄</span>Arquivo PDF Selecionado' : '<span style="font-size: 28px; display: block; margin-bottom: 4px;">📄</span>Arquivo Selecionado';
        }

        executarDigitalizacaoOCR();
    });

    if (btnScanOcr) {
        btnScanOcr.addEventListener('click', function() {
            executarDigitalizacaoOCR();
        });
    }
});
</script>
