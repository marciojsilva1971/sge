<div class="page-header">
    <div>
        <h2>Lançamento de Despesas de Campanha</h2>
        <p class="subtitle">Controle de pagamentos de fornecedores, serviços, material gráfico e aluguéis.</p>
    </div>
    <div>
        <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="btn btn-secondary">
            ⬅️ Voltar ao Financeiro
        </a>
    </div>
</div>

<?php include __DIR__ . '/_nav_tabs.php'; ?>

<div class="dashboard-sections">
    
    <!-- Lançamento de Despesa -->
    <div class="panel-card flex-1">
        <div class="card-header">
            <h3>Lançar Nova Despesa Geral</h3>
        </div>
        <form action="<?= $this->baseUrl('admin/financeiro/despesas') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- 1º PASSO: UPLOAD / DIGITALIZAÇÃO DO COMPROVANTE (CNPJ NÍTIDO) -->
            <div class="form-group" style="background: rgba(13, 148, 136, 0.08); border: 2px dashed var(--accent-teal); padding: 14px; border-radius: 12px; margin-bottom: 16px;">
                <label for="comprovante" style="font-size: 13px; font-weight: 700; color: var(--accent-teal-hover); display: flex; align-items: center; gap: 6px;">
                    📸 1º PASSO: Enviar Foto do Cabeçalho com CNPJ NÍTIDO (para Leitura OCR)
                </label>
                <p style="font-size: 11px; color: var(--text-secondary); margin-bottom: 8px;">
                    Tire uma foto bem nítida e focada no <strong>cabeçalho/topo da nota ou cupom fiscal</strong> onde aparece o CNPJ. O sistema lerá o CNPJ via OCR!
                </p>
                <input type="file" id="comprovante" name="comprovante" accept="application/pdf, image/*" required style="padding: 6px; width: 100%;">
                
                <button type="button" id="btn-scan-ocr" style="margin-top: 10px; background: var(--accent-teal); color: #0f172a; font-weight: 700; width: 100%; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; transition: all 0.2s;">
                    🔍 Digitalizar e Ler Comprovante (OCR)
                </button>

                <!-- Status do OCR -->
                <div id="ocr_status_badge" style="margin-top: 10px; display: none;"></div>

                <!-- Preview da miniatura do arquivo -->
                <div id="comprovante-preview-container" style="display: none; margin-top: 10px; align-items: center; gap: 12px; background: rgba(15, 23, 42, 0.6); padding: 10px; border-radius: 8px; border: 1px dashed var(--border-color);">
                    <div id="comprovante-preview-thumb" style="width: 50px; height: 50px; border-radius: 6px; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; font-size: 20px; background-color: rgba(255,255,255,0.05);"></div>
                    <div style="flex: 1; min-width: 0;">
                        <div id="comprovante-preview-name" style="font-size: 13px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
                        <div id="comprovante-preview-size" style="font-size: 11px; color: var(--text-secondary);"></div>
                    </div>
                </div>
            </div>

            <!-- CONTAINER REVELADO APÓS UPLOAD / DIGITALIZAÇÃO -->
            <div id="dados-despesa-container" style="display: none !important; margin-top: 16px; transition: all 0.3s ease;">
                <div class="form-group">
                    <label for="description">Descrição do Pagamento / Finalidade</label>
                    <input type="text" id="description" name="description" placeholder="Ex: Impressão de 10.000 Santinhos de Militância" required>
                </div>

                <!-- FOTOS ADICIONAIS DAS DESPESAS DISCRIMINADAS (SEM OCR) -->
                <div class="form-group" style="background: rgba(15, 23, 42, 0.4); border: 1px dashed rgba(255, 255, 255, 0.15); padding: 12px; border-radius: 10px; margin-top: 14px; margin-bottom: 14px;">
                    <label for="fotos_adicionais" style="font-size: 12px; font-weight: 700; color: #7dd3fc; display: flex; align-items: center; gap: 6px;">
                        📸 Fotos Adicionais do Comprovante / Itens Discriminados (Opcional - Sem OCR)
                    </label>
                    <p style="font-size: 11px; color: var(--text-secondary); margin-bottom: 6px;">
                        Se o comprovante tiver mais fotos, verso ou discriminação detalhada dos itens e valores, adicione as fotos extras abaixo. <strong>Estas fotos não passam por OCR.</strong>
                    </p>
                    <input type="file" id="fotos_adicionais" name="fotos_adicionais[]" accept="image/*, application/pdf" multiple style="padding: 6px; font-size: 12px; width: 100%;">
                    <div id="fotos-adicionais-preview" style="font-size: 11px; color: #4ade80; margin-top: 4px; display: none;"></div>
                </div>

                <div style="display: flex; gap: 16px;">
                    <div class="form-group flex-1">
                        <label for="supplier_id">Fornecedor / Credor</label>
                        <select id="supplier_id" name="supplier_id" required>
                            <option value="">Selecione o fornecedor...</option>
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['corporate_name']) ?> (CNPJ/CPF: <?= htmlspecialchars($sup['cnpj_cpf']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="bank_account_id">Conta Bancária Origem</label>
                        <select id="bank_account_id" name="bank_account_id" required>
                            <option value="">Selecione a conta...</option>
                            <?php foreach ($bankAccounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?> (Saldo: R$ <?= number_format($acc['balance'], 2, ',', '.') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 16px;">
                    <div class="form-group flex-1">
                        <label for="value">Valor da Despesa (R$)</label>
                        <input type="text" id="value" name="value" placeholder="R$ 0,00" required style="font-size: 16px; font-weight: 600; color: var(--accent-teal-hover);" oninput="formatarMoeda(this);">
                    </div>
                    <div class="form-group flex-1">
                        <label for="date_incurred">Data da Despesa</label>
                        <input type="date" id="date_incurred" name="date_incurred" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div style="display: flex; gap: 16px;">
                    <div class="form-group flex-1">
                        <label for="payment_method">Forma de Pagamento</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Selecione...</option>
                            <option value="PIX">PIX</option>
                            <option value="Transferência Bancária">Transferência Bancária</option>
                            <option value="Boleto Bancário">Boleto Bancário</option>
                            <option value="Débito em Conta">Débito em Conta</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="spce_category_id">Categoria SPCE/TSE</label>
                        <select id="spce_category_id" name="spce_category_id" required>
                            <option value="">Selecione a categoria...</option>
                            <?php foreach ($spceCategories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Observações Internas (Opcional)</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Informações adicionais ou notas de auditoria..."></textarea>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 24px;">
                    <input type="checkbox" id="mark_as_paid" name="mark_as_paid" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="mark_as_paid" style="margin-bottom: 0; font-weight: 500; cursor: pointer;">
                        Efetivar despesa como <strong>PAGO</strong> (Descontar saldo da conta bancária imediatamente)
                    </label>
                </div>

                <button type="submit" class="btn btn-teal btn-block">
                    💾 Confirmar e Enviar Despesa
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Despesas -->
    <div class="panel-card flex-1" style="min-width: 60%;">
        <div class="card-header">
            <h3>Despesas Registradas</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Fornecedor / Categoria</th>
                        <th>Descrição</th>
                        <th>Conta / Pagto</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Comprovante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary);">Nenhuma despesa registrada ainda.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $exp): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($exp['date_incurred'])) ?></td>
                                <td>
                                    <div class="user-log-name"><?= htmlspecialchars($exp['supplier_name']) ?></div>
                                    <span style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($exp['spce_code']) ?> - <?= htmlspecialchars($exp['spce_desc']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($exp['description']) ?></td>
                                <td>
                                    <div style="font-size: 12px; font-weight: 500;"><?= htmlspecialchars($exp['bank_name']) ?></div>
                                    <span style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($exp['payment_method']) ?></span>
                                </td>
                                <td style="font-weight: 600; color: var(--text-primary);">
                                    R$ <?= number_format($exp['value'], 2, ',', '.') ?>
                                </td>
                                <td>
                                    <?php if ($exp['status'] === 'PAGO'): ?>
                                        <span class="badge badge-success">Pago</span>
                                    <?php elseif ($exp['status'] === 'APROVADO'): ?>
                                        <span class="badge badge-info">Aprovado</span>
                                    <?php elseif ($exp['status'] === 'REJEITADO'): ?>
                                        <span class="badge badge-danger">Rejeitado</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($exp['doc_id'])): ?>
                                        <a href="<?= $this->baseUrl('admin/financeiro/comprovante?id=' . $exp['doc_id'] . '&type=expense') ?>" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 8px;">
                                            📄 Ver
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary);">Sem anexo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function formatarMoeda(input) {
        let value = input.value.replace(/\D/g, "");
        if (value === "") {
            input.value = "";
            return;
        }
        value = (parseFloat(value) / 100).toFixed(2);
        let formatted = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
        input.value = formatted;
    }

<!-- Tesseract.js OCR -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
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

    // Extrator inteligente de CNPJ com tolerância a ruídos comuns de OCR
    function extrairCNPJDoTexto(text) {
        if (!text) return null;

        // 1. Tentar encontrar padrões numéricos próximos ao rótulo CNPJ
        const matches = text.match(/(?:CNPJ|C\.N\.P\.J\.?|MF)?[\s\:\.\-\/]*([0-9OolI|sS\.\-\/\s]{14,25})/gi) || [];
        for (let raw of matches) {
            let clean = raw.replace(/[Oo]/g, '0').replace(/[Il|]/g, '1').replace(/[sS]/g, '5').replace(/\D/g, '');
            for (let i = 0; i <= clean.length - 14; i++) {
                let sub = clean.substring(i, i + 14);
                if (validarCNPJ(sub)) return sub;
            }
        }

        // 2. Extração genérica de todas as sequências numéricas limpas
        const apenasNumeros = text.replace(/[Oo]/g, '0').replace(/[Il|]/g, '1').replace(/[sS]/g, '5').replace(/\D/g, ' ');
        const tokens = apenasNumeros.split(/\s+/);
        for (let token of tokens) {
            if (token.length === 14 && validarCNPJ(token)) {
                return token;
            }
        }

        // 3. Varredura por janela deslizante de 14 dígitos no texto acumulado
        const todosDigitos = text.replace(/[Oo]/g, '0').replace(/[Il|]/g, '1').replace(/[sS]/g, '5').replace(/\D/g, '');
        for (let i = 0; i <= todosDigitos.length - 14; i++) {
            let sub = todosDigitos.substring(i, i + 14);
            if (validarCNPJ(sub)) return sub;
        }

        return null;
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
                let maxDim = 1800;
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
                }, 'image/jpeg', 0.92);
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

    const compInputAdmin = document.getElementById('comprovante');
    const btnScanOcrAdmin = document.getElementById('btn-scan-ocr');
    const ocrStatusBadgeAdmin = document.getElementById('ocr_status_badge');
    const dadosContainerAdmin = document.getElementById('dados-despesa-container');
    const inputFotosAdicionaisAdmin = document.getElementById('fotos_adicionais');
    const previewFotosAdicionaisAdmin = document.getElementById('fotos-adicionais-preview');

    if (inputFotosAdicionaisAdmin && previewFotosAdicionaisAdmin) {
        inputFotosAdicionaisAdmin.addEventListener('change', function(e) {
            const count = e.target.files ? e.target.files.length : 0;
            if (count > 0) {
                previewFotosAdicionaisAdmin.style.display = 'block';
                previewFotosAdicionaisAdmin.textContent = `✔ ${count} foto(s) adicional(is) selecionada(s) (sem OCR).`;
            } else {
                previewFotosAdicionaisAdmin.style.display = 'none';
            }
        });
    }

    function executarOCRAdmin() {
        const file = compInputAdmin ? compInputAdmin.files[0] : null;
        const container = document.getElementById('comprovante-preview-container');
        const supplierSelect = document.getElementById('supplier_id');

        if (!file) {
            if (ocrStatusBadgeAdmin) {
                ocrStatusBadgeAdmin.style.display = 'block';
                ocrStatusBadgeAdmin.innerHTML = `
                    <div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px;">
                        ⚠️ Por favor, selecione um arquivo ou tire uma foto primeiro!
                    </div>
                `;
            }
            return;
        }

        // Revelar formulário de despesa
        if (dadosContainerAdmin) dadosContainerAdmin.style.display = 'block';

        if (ocrStatusBadgeAdmin) {
            ocrStatusBadgeAdmin.style.display = 'block';
            ocrStatusBadgeAdmin.innerHTML = `
                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">⏳</span>
                    <span>Iniciando otimizador de imagem e motor OCR... Por favor, aguarde.</span>
                </div>
            `;
        }

        if (file.type === 'application/pdf') {
            if (ocrStatusBadgeAdmin) {
                ocrStatusBadgeAdmin.innerHTML = `
                    <div style="padding: 10px 12px; background: rgba(56, 189, 248, 0.15); border: 1px solid #38bdf8; border-radius: 8px; color: #7dd3fc; font-weight: 600; font-size: 12px;">
                        📄 Arquivo PDF anexado. Por favor, selecione o fornecedor nos campos abaixo.
                    </div>
                `;
            }
            return;
        }

        const rodarOCR = () => {
            if (!window.Tesseract) {
                if (ocrStatusBadgeAdmin) {
                    ocrStatusBadgeAdmin.innerHTML = `<div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px;">⚠️ Por favor, selecione o fornecedor manualmente.</div>`;
                }
                return;
            }

            otimizarImagemParaOCR(file, function(processedFile) {
                Tesseract.recognize(processedFile, 'por', {
                    logger: m => {
                        if (m.status === 'recognizing text' && ocrStatusBadgeAdmin) {
                            const pct = Math.round((m.progress || 0) * 100);
                            ocrStatusBadgeAdmin.innerHTML = `
                                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px;">🔍</span>
                                    <span>Lendo comprovante via OCR (${pct}%)... Processando nitidez.</span>
                                </div>
                            `;
                        }
                    }
                }).then(({ data: { text } }) => {
                    console.log("Texto extraído via OCR:", text);
                    const cleanCnpj = extrairCNPJDoTexto(text);
                    if (cleanCnpj) {
                        fetch('<?= $this->baseUrl("api/cnpj/consultar") ?>?cnpj=' + cleanCnpj)
                            .then(res => res.json())
                            .then(data => {
                                if (data.success && ocrStatusBadgeAdmin) {
                                    ocrStatusBadgeAdmin.innerHTML = `
                                        <div style="padding: 12px; background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; border-radius: 10px; color: #4ade80; font-weight: 600; font-size: 12px; display: flex; flex-direction: column; gap: 8px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 18px;">✅</span>
                                                <span>CNPJ Lido e Validado: <strong>${data.razao_social}</strong> (${data.cnpj})</span>
                                            </div>
                                            <div style="background: rgba(13, 148, 136, 0.3); border-left: 3px solid var(--accent-teal); padding: 8px 10px; border-radius: 6px; color: #5eead4; font-weight: 500; font-size: 11px;">
                                                📸 <strong>Atenção:</strong> Certifique-se de anexar uma ou mais fotos nítidas do comprovante onde estejam discriminadas todas as despesas e valores.
                                            </div>
                                        </div>
                                    `;
                                    let found = false;
                                    for (let option of supplierSelect.options) {
                                        if (option.text.replace(/\D/g, "").includes(cleanCnpj)) {
                                            supplierSelect.value = option.value;
                                            found = true;
                                            break;
                                        }
                                    }
                                    if (!found) {
                                        ocrStatusBadgeAdmin.innerHTML += `
                                            <div style="font-size: 11px; color: #fde047; margin-top: 4px;">
                                                💡 Fornecedor novo. <a href="<?= $this->baseUrl('admin/financeiro/fornecedores') ?>" target="_blank" style="color:#fde047; text-decoration:underline;">Clique para cadastrar</a> ou selecione manualmente.
                                            </div>
                                        `;
                                    }
                                } else if (ocrStatusBadgeAdmin) {
                                    ocrStatusBadgeAdmin.innerHTML = `
                                        <div style="padding: 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 10px; color: #fde047; font-weight: 600; font-size: 12px; display: flex; flex-direction: column; gap: 8px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 18px;">⚠️</span>
                                                <span>CNPJ Lido (${cleanCnpj}), selecione o fornecedor manualmente.</span>
                                            </div>
                                            <div style="background: rgba(13, 148, 136, 0.3); border-left: 3px solid var(--accent-teal); padding: 8px 10px; border-radius: 6px; color: #5eead4; font-weight: 500; font-size: 11px;">
                                                📸 <strong>Atenção:</strong> Anexe uma ou mais fotos do comprovante discriminando todas as despesas e valores.
                                            </div>
                                        </div>
                                    `;
                                }
                            });
                    } else if (ocrStatusBadgeAdmin) {
                        ocrStatusBadgeAdmin.innerHTML = '<div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px;">⚠️ CNPJ não identificado automaticamente. Selecione o fornecedor manualmente.</div>';
                    }
                }).catch(err => {
                    console.error("Erro OCR:", err);
                    if (ocrStatusBadgeAdmin) {
                        ocrStatusBadgeAdmin.innerHTML = '<div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px;">⚠️ Erro ao ler imagem. Selecione o fornecedor manualmente.</div>';
                    }
                });
            });
        };

        if (typeof Tesseract === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
            script.onload = rodarOCR;
            script.onerror = () => {
                if (ocrStatusBadgeAdmin) {
                    ocrStatusBadgeAdmin.innerHTML = '<div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px;">⚠️ Selecione o fornecedor manualmente.</div>';
                }
            };
            document.head.appendChild(script);
        } else {
            rodarOCR();
        }
    }

    if (compInputAdmin) {
        compInputAdmin.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const container = document.getElementById('comprovante-preview-container');
            const thumb = document.getElementById('comprovante-preview-thumb');
            const name = document.getElementById('comprovante-preview-name');
            const size = document.getElementById('comprovante-preview-size');

            if (!file) {
                if (container) container.style.display = 'none';
                if (ocrStatusBadgeAdmin) ocrStatusBadgeAdmin.style.display = 'none';
                if (dadosContainerAdmin) dadosContainerAdmin.style.display = 'none';
                return;
            }

            if (dadosContainerAdmin) dadosContainerAdmin.style.display = 'block';

            if (container) {
                container.style.display = 'flex';
                if (name) name.textContent = file.name;
                if (size) size.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            }

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    if (thumb) {
                        thumb.style.backgroundImage = "url('" + evt.target.result + "')";
                        thumb.textContent = "";
                    }
                };
                reader.readAsDataURL(file);
            } else if (thumb) {
                thumb.style.backgroundImage = 'none';
                thumb.textContent = file.type === 'application/pdf' ? '📄' : '📎';
            }

            executarOCRAdmin();
        });
    }

    if (btnScanOcrAdmin) {
        btnScanOcrAdmin.addEventListener('click', function() {
            executarOCRAdmin();
        });
    }
</script>
