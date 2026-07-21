<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px;">Controle de Viagens & Combustível</h2>
    <p class="subtitle" style="font-size: 12px;">Monitore suas viagens e anexe comprovantes para reembolso de combustível.</p>
</div>

<!-- 1. Viagem Ativa (Em Andamento) -->
<?php 
$activeReport = null;
foreach ($travelReports as $report) {
    if ($report['status'] === 'EM_ANDAMENTO') {
        $activeReport = $report;
        break;
    }
}
?>

<?php if ($activeReport): ?>
    <div class="panel-card" style="border-color: var(--accent-teal); padding: 16px; margin-bottom: 24px;">
        <div class="card-header" style="border-color: rgba(255,255,255,0.08); padding-bottom: 10px; margin-bottom: 12px;">
            <span style="font-size: 10px; color: var(--accent-teal-hover); text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">Viagem ativa em andamento</span>
            <h3 style="font-size: 15px; font-weight: 600; margin-top: 2px;"><?= htmlspecialchars($activeReport['purpose']) ?></h3>
            <span style="font-size: 11px; color: var(--text-secondary);">
                Placa: <?= htmlspecialchars($activeReport['vehicle_plate'] ?? 'Não informada') ?> &bull; Início: <?= date('d/m/Y', strtotime($activeReport['start_date'])) ?>
            </span>
        </div>

        <!-- Formulário para Adicionar Cupom Fiscal -->
        <h4 style="font-size: 13px; font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">Anexar Novo Cupom Fiscal / Recibo:</h4>
        <form action="<?= $this->baseUrl('portal/viagem/receipt') ?>" method="POST" enctype="multipart/form-data" style="margin-bottom: 20px; background: rgba(15,23,42,0.3); padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="travel_report_id" value="<?= $activeReport['id'] ?>">

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

            <!-- 1º PASSO: UPLOAD / DIGITALIZAÇÃO DO COMPROVANTE -->
            <div class="form-group" style="background: rgba(13, 148, 136, 0.08); border: 2px dashed var(--accent-teal); padding: 14px; border-radius: 12px; margin-bottom: 16px;">
                <label for="comprovante" style="font-size: 13px; font-weight: 700; color: var(--accent-teal-hover); display: flex; align-items: center; gap: 6px;">
                    📸 1º PASSO: Anexar / Fotografar Comprovante(s) Fiscal(is) (JPEG, PNG ou PDF)
                </label>
                <p style="font-size: 11px; color: var(--text-secondary); margin-bottom: 8px;">
                    Tire uma ou mais fotos legíveis da nota/cupom fiscal onde estejam discriminadas as despesas e seus valores!
                </p>
                <input type="file" id="comprovante" name="comprovante[]" accept="image/*, application/pdf" multiple required style="padding: 6px; font-size: 12px; width: 100%;">
                
                <button type="button" id="btn-scan-ocr" style="margin-top: 10px; background: var(--accent-teal); color: #0f172a; font-weight: 700; width: 100%; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; transition: all 0.2s;">
                    🔍 Digitalizar e Ler Comprovante (OCR)
                </button>

                <!-- Status do OCR / Notificação Imediata -->
                <div id="ocr_status_badge" style="margin-top: 10px; display: none;"></div>

                <!-- Preview da miniatura do arquivo -->
                <div id="comprovante-preview-container" style="display: none; margin-top: 10px; align-items: center; gap: 12px; background: rgba(15, 23, 42, 0.6); padding: 10px; border-radius: 8px; border: 1px dashed var(--border-color);">
                    <div id="comprovante-preview-thumb" style="width: 45px; height: 45px; border-radius: 6px; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; font-size: 18px; background-color: rgba(255,255,255,0.05);"></div>
                    <div style="flex: 1; min-width: 0;">
                        <div id="comprovante-preview-name" style="font-size: 11px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
                        <div id="comprovante-preview-size" style="font-size: 9px; color: var(--text-secondary);"></div>
                    </div>
                </div>
            </div>

            <!-- CONTAINER REVELADO APÓS UPLOAD / DIGITALIZAÇÃO -->
            <div id="dados-despesa-container" style="display: none !important; margin-top: 16px; transition: all 0.3s ease;">
                <!-- DADOS DO FORNECEDOR (CNPJ + RAZÃO SOCIAL) -->
                <div style="display: flex; gap: 10px;">
                    <div class="form-group flex-1">
                        <label for="supplier_cnpj" style="font-size: 12px;">CNPJ do Posto de Combustível</label>
                        <input type="text" id="supplier_cnpj" name="supplier_cnpj" placeholder="00.000.000/0001-00" required>
                        <div id="cnpj_supplier_info" style="margin-top: 4px; font-size: 11px;"></div>
                    </div>
                    <div class="form-group flex-1">
                        <label for="supplier_name" style="font-size: 12px;">Nome / Razão Social da Empresa</label>
                        <input type="text" id="supplier_name" name="supplier_name" placeholder="Ex: Auto Posto Alvorada Ltda" style="font-weight: 500;">
                    </div>
                </div>

                <!-- VALOR E DATA -->
                <div style="display: flex; gap: 10px;">
                    <div class="form-group flex-1">
                        <label for="value" style="font-size: 12px;">Valor do Cupom (R$)</label>
                        <input type="text" id="value" name="value" placeholder="R$ 0,00" required style="font-size: 16px; font-weight: 600; color: var(--accent-teal-hover);" oninput="formatarMoeda(this);">
                    </div>
                    <div class="form-group flex-1">
                        <label for="receipt_date" style="font-size: 12px;">Data do Recibo</label>
                        <input type="date" id="receipt_date" name="receipt_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="spce_category_id" style="font-size: 12px;">Categoria SPCE</label>
                    <select id="spce_category_id" name="spce_category_id" required style="font-size: 13px;">
                        <?php foreach ($spceCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= strpos($cat['code'], '44') !== false ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes" style="font-size: 12px;">Notas / Observações (Opcional)</label>
                    <input type="text" id="notes" name="notes" placeholder="Ex: Abastecimento equipe de campo">
                </div>

                <button type="submit" class="btn btn-teal btn-block btn-sm">
                    ➕ Confirmar e Enviar Cupom
                </button>
            </div>
        </form>

        <!-- Cupons já adicionados nesta viagem -->
        <h5 style="font-size: 12px; font-weight: 600; margin-bottom: 8px; color: var(--text-secondary);">Cupons Anexados:</h5>
        <?php if (empty($activeReport['receipts'])): ?>
            <p style="font-size: 11px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhum cupom adicionado a esta viagem.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                <?php foreach ($activeReport['receipts'] as $rec): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: rgba(15,23,42,0.5); border-radius: 8px; font-size: 12px;">
                        <div>
                            <strong>R$ <?= number_format($rec['value'], 2, ',', '.') ?></strong>
                            <div style="font-size: 10px; color: var(--text-secondary);">Posto CNPJ: <?= htmlspecialchars($rec['supplier_cnpj']) ?></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary);">
                            <?= date('d/m/Y', strtotime($rec['receipt_date'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Botão para Finalizar e Enviar Relatório -->
        <form action="<?= $this->baseUrl('portal/viagem/submit') ?>" method="POST" style="margin-top: 16px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= $activeReport['id'] ?>">
            <button type="submit" class="btn btn-primary btn-block" <?= empty($activeReport['receipts']) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                🚀 Finalizar e Enviar Relatório de Viagem
            </button>
            <?php if (empty($activeReport['receipts'])): ?>
                <div style="text-align: center; font-size: 10px; color: var(--error-color); margin-top: 4px;">
                    * Adicione pelo menos um cupom fiscal para poder enviar.
                </div>
            <?php endif; ?>
        </form>
    </div>
<?php else: ?>
    <!-- Formulário para Iniciar Nova Viagem -->
    <div class="panel-card" style="padding: 16px; margin-bottom: 24px;">
        <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
            <h3 style="font-size: 14px; font-weight: 600;">Iniciar Novo Relatório de Viagem</h3>
        </div>
        <form action="<?= $this->baseUrl('portal/viagem') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="purpose">Finalidade / Objetivo da Viagem</label>
                <input type="text" id="purpose" name="purpose" placeholder="Ex: Panfletagem na Zona Norte" required>
            </div>

            <div style="display: flex; gap: 10px;">
                <div class="form-group flex-1">
                    <label for="start_date">Data Inicial</label>
                    <input type="date" id="start_date" name="start_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group flex-1">
                    <label for="end_date">Data Final</label>
                    <input type="date" id="end_date" name="end_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="vehicle_plate">Placa do Veículo (Opcional)</label>
                <input type="text" id="vehicle_plate" name="vehicle_plate" placeholder="AAA-0A00">
            </div>

            <button type="submit" class="btn btn-teal btn-block">
                🚗 Iniciar Registro de Viagem
            </button>
        </form>
    </div>
<?php endif; ?>

<!-- Histórico de Viagens Anteriores -->
<div class="panel-card" style="padding: 16px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
        <h3 style="font-size: 14px; font-weight: 600;">Histórico de Viagens</h3>
    </div>

    <?php if (empty($travelReports)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhuma viagem anterior registrada.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($travelReports as $tr): ?>
                <?php if ($tr['status'] === 'EM_ANDAMENTO') continue; ?>
                <div style="padding: 12px; background: rgba(15,23,42,0.4); border-radius: 10px; border: 1px solid rgba(255,255,255,0.04);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <span style="font-size: 13px; font-weight: 600; display: block;"><?= htmlspecialchars($tr['purpose']) ?></span>
                            <span style="font-size: 10px; color: var(--text-secondary);">
                                Período: <?= date('d/m/Y', strtotime($tr['start_date'])) ?> &rarr; <?= date('d/m/Y', strtotime($tr['end_date'])) ?>
                            </span>
                        </div>
                        <div>
                            <?php if ($tr['status'] === 'APROVADO'): ?>
                                <span class="badge badge-success">Aprovada</span>
                            <?php elseif ($tr['status'] === 'REJEITADO'): ?>
                                <span class="badge badge-danger">Rejeitada</span>
                            <?php else: ?>
                                <span class="badge badge-info">Auditoria</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // Máscara CNPJ do Posto
    document.getElementById('supplier_cnpj').addEventListener('input', function (e) {
        var value = e.target.value.replace(/\D/g, "");
        if (value.length > 14) value = value.slice(0, 14);

        if (value.length > 11) {
            value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
        } else if (value.length > 9) {
            value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4");
        } else if (value.length > 6) {
            value = value.replace(/^(\d{3})(\d{3})(\d{1,3})$/, "$1.$2.$3");
        } else if (value.length > 3) {
            value = value.replace(/^(\d{3})(\d{1,3})$/, "$1.$2");
        }
        e.target.value = value;
    });

    // Máscara básica de Placa
    var plateInput = document.getElementById('vehicle_plate');
    if (plateInput) {
        plateInput.addEventListener('input', function (e) {
            var value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g, "");
            if (value.length > 8) value = value.slice(0, 8);
            e.target.value = value;
        });
    }

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

<!-- Biblioteca Tesseract.js para leitura OCR de comprovantes no navegador -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
    // Consulta pública de CNPJ em tempo real
    const cnpjInput = document.getElementById('supplier_cnpj');
    const cnpjInfoDiv = document.getElementById('cnpj_supplier_info');
    const supplierNameInput = document.getElementById('supplier_name');

    function consultarCnpjServico(cnpjVal) {
        const clean = cnpjVal.replace(/\D/g, "");
        if (clean.length !== 14) return;

        if (cnpjInfoDiv) {
            cnpjInfoDiv.innerHTML = '<span style="color: var(--accent-teal); font-weight: 500;">🔍 Consultando Receita Federal...</span>';
        }

        fetch('<?= $this->baseUrl("api/cnpj/consultar") ?>?cnpj=' + clean)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cnpjInput.value = data.cnpj;
                    if (supplierNameInput) {
                        supplierNameInput.value = data.razao_social;
                    }
                    if (cnpjInfoDiv) {
                        cnpjInfoDiv.innerHTML = `<span style="color: #22c55e; font-weight: 600;">✔ ${data.razao_social}</span> <span style="color: #94a3b8;">(${data.municipio}/${data.uf})</span>`;
                    }
                    if (statusBadge) {
                        statusBadge.innerHTML = `
                            <div style="padding: 12px; background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; border-radius: 10px; color: #4ade80; font-weight: 600; font-size: 12px; display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 18px;">✅</span>
                                    <span>Empresa Identificada: <strong>${data.razao_social}</strong> (${data.cnpj})</span>
                                </div>
                                <div style="background: rgba(13, 148, 136, 0.3); border-left: 3px solid var(--accent-teal); padding: 8px 10px; border-radius: 6px; color: #5eead4; font-weight: 500; font-size: 11px;">
                                    📸 <strong>Atenção:</strong> Certifique-se de anexar uma ou mais fotos nítidas do comprovante fiscal onde estejam perfeitamente discriminadas todas as despesas e seus respectivos valores.
                                </div>
                            </div>
                        `;
                    }
                } else {
                    if (cnpjInfoDiv) {
                        cnpjInfoDiv.innerHTML = `<span style="color: #ef4444; font-weight: 500;">⚠️ ${data.message || 'CNPJ não encontrado na Receita Federal'}</span>`;
                    }
                    if (statusBadge) {
                        statusBadge.innerHTML = `
                            <div style="padding: 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 10px; color: #fde047; font-weight: 600; font-size: 12px; display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 18px;">⚠️</span>
                                    <span>CNPJ Lido (${clean}), mas empresa não localizada na Receita Federal.</span>
                                </div>
                                <div style="background: rgba(13, 148, 136, 0.3); border-left: 3px solid var(--accent-teal); padding: 8px 10px; border-radius: 6px; color: #5eead4; font-weight: 500; font-size: 11px;">
                                    📸 <strong>Atenção:</strong> Preencha o nome da empresa abaixo e certifique-se de anexar uma ou mais fotos do comprovante discriminando todas as despesas e valores.
                                </div>
                            </div>
                        `;
                    }
                }
            })
            .catch(err => {
                console.error('Erro na consulta do CNPJ:', err);
                if (cnpjInfoDiv) {
                    cnpjInfoDiv.innerHTML = '<span style="color: #ef4444;">⚠️ Erro ao conectar com base da Receita</span>';
                }
            });
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
                consultarCnpjServico(value);
            }
        });
    }

    const compInput = document.getElementById('comprovante');
    const btnScanOcr = document.getElementById('btn-scan-ocr');
    const statusBadge = document.getElementById('ocr_status_badge');
    const container = document.getElementById('comprovante-preview-container');
    const thumb = document.getElementById('comprovante-preview-thumb');
    const namePreview = document.getElementById('comprovante-preview-name');
    const sizePreview = document.getElementById('comprovante-preview-size');
    const dadosContainer = document.getElementById('dados-despesa-container');

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

    function executarDigitalizacaoOCR() {
        const file = compInput ? compInput.files[0] : null;

        if (!file) {
            if (statusBadge) {
                statusBadge.style.display = 'block';
                statusBadge.innerHTML = `
                    <div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">⚠️</span>
                        <span>Por favor, escolha um arquivo ou tire uma foto primeiro no botão acima!</span>
                    </div>
                `;
            }
            return;
        }

        // Exibir formulário de despesa
        if (dadosContainer) dadosContainer.style.display = 'block';

        if (statusBadge) {
            statusBadge.style.display = 'block';
            statusBadge.innerHTML = `
                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">⏳</span>
                    <span>Iniciando otimizador de imagem e motor OCR... Por favor, aguarde.</span>
                </div>
            `;
        }

        if (file.type === 'application/pdf') {
            if (statusBadge) {
                statusBadge.innerHTML = `
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

            otimizarImagemParaOCR(file, function(processedFile) {
                Tesseract.recognize(processedFile, 'por', {
                    logger: m => {
                        if (m.status === 'recognizing text' && statusBadge) {
                            const pct = Math.round((m.progress || 0) * 100);
                            statusBadge.innerHTML = `
                                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px;">🔍</span>
                                    <span>Lendo comprovante via OCR (${pct}%)... Processando nitidez.</span>
                                </div>
                            `;
                        }
                    }
                }).then(({ data: { text } }) => {
                    console.log("Texto extraído via OCR:", text);
                    const detectedCnpj = extrairCNPJDoTexto(text);
                    if (detectedCnpj) {
                        if (cnpjInput) cnpjInput.value = detectedCnpj;
                        consultarCnpjServico(detectedCnpj);
                        if (statusBadge) {
                            statusBadge.innerHTML = `
                                <div style="padding: 10px 12px; background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; border-radius: 8px; color: #4ade80; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px;">✅</span>
                                    <span>CNPJ (${detectedCnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5")}) validado e lido! Buscando dados na Receita Federal...</span>
                                </div>
                            `;
                        }
                    } else {
                        exibirAvisoManual();
                    }
                }).catch(err => {
                    console.error("Erro OCR Tesseract:", err);
                    exibirAvisoManual();
                });
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
        if (dadosContainer) dadosContainer.style.display = 'block';
        if (statusBadge) {
            statusBadge.innerHTML = `
                <div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px; display: flex; flex-direction: column; gap: 4px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">⚠️</span>
                        <span>Não foi possível identificar o CNPJ nesta foto automaticamente.</span>
                    </div>
                    <div style="font-size: 11px; font-weight: normal; color: #fef08a; margin-left: 24px;">
                        Por favor, digite o CNPJ e o Nome da Empresa nos campos abaixo.
                    </div>
                </div>
            `;
        }
    }

    if (compInput) {
        compInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) {
                if (container) container.style.display = 'none';
                if (statusBadge) statusBadge.style.display = 'none';
                if (dadosContainer) dadosContainer.style.display = 'none';
                return;
            }

            if (dadosContainer) dadosContainer.style.display = 'block';

            if (container) {
                container.style.display = 'flex';
                if (namePreview) namePreview.textContent = file.name;
                if (sizePreview) sizePreview.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
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

            // Dispara automaticamente no evento change
            executarDigitalizacaoOCR();
        });
    }

    if (btnScanOcr) {
        btnScanOcr.addEventListener('click', function() {
            executarDigitalizacaoOCR();
        });
    }
</script>
