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

            <!-- 1º PASSO: UPLOAD / DIGITALIZAÇÃO DO COMPROVANTE -->
            <div class="form-group" style="background: rgba(13, 148, 136, 0.08); border: 2px dashed var(--accent-teal); padding: 14px; border-radius: 12px; margin-bottom: 16px;">
                <label for="comprovante" style="font-size: 13px; font-weight: 700; color: var(--accent-teal-hover); display: flex; align-items: center; gap: 6px;">
                    📸 1º PASSO: Anexar / Fotografar Comprovante Fiscal (JPEG, PNG ou PDF)
                </label>
                <p style="font-size: 11px; color: var(--text-secondary); margin-bottom: 8px;">
                    Tire uma foto legível da nota/cupom fiscal. O sistema lerá a imagem via OCR para autocompletar o CNPJ e o nome do posto!
                </p>
                <input type="file" id="comprovante" name="comprovante" accept="image/*, application/pdf" required style="padding: 6px; font-size: 12px; width: 100%;">
                
                <!-- Preview da miniatura do arquivo -->
                <div id="comprovante-preview-container" style="display: none; margin-top: 10px; align-items: center; gap: 12px; background: rgba(15, 23, 42, 0.6); padding: 10px; border-radius: 8px; border: 1px dashed var(--border-color);">
                    <div id="comprovante-preview-thumb" style="width: 45px; height: 45px; border-radius: 6px; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; font-size: 18px; background-color: rgba(255,255,255,0.05);"></div>
                    <div style="flex: 1; min-width: 0;">
                        <div id="comprovante-preview-name" style="font-size: 11px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
                        <div id="comprovante-preview-size" style="font-size: 9px; color: var(--text-secondary);"></div>
                    </div>
                </div>
            </div>

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
                ➕ Adicionar Cupom
            </button>
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
                } else {
                    if (cnpjInfoDiv) {
                        cnpjInfoDiv.innerHTML = `<span style="color: #ef4444; font-weight: 500;">⚠️ ${data.message || 'CNPJ não encontrado na Receita Federal'}</span>`;
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
    if (compInput) {
        compInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const container = document.getElementById('comprovante-preview-container');
            const thumb = document.getElementById('comprovante-preview-thumb');
            const name = document.getElementById('comprovante-preview-name');
            const size = document.getElementById('comprovante-preview-size');

            if (!file) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'flex';
            name.textContent = file.name;
            size.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    thumb.style.backgroundImage = "url('" + evt.target.result + "')";
                    thumb.textContent = "";
                };
                reader.readAsDataURL(file);

                // Executa OCR inteligente na foto do comprovante para detectar o CNPJ
                if (window.Tesseract && cnpjInfoDiv) {
                    cnpjInfoDiv.innerHTML = '<span style="color: var(--accent-teal); font-weight: 600;">🤖 Lendo imagem para detectar CNPJ (OCR)...</span>';
                    
                    Tesseract.recognize(file, 'por', {
                        logger: m => console.log(m)
                    }).then(({ data: { text } }) => {
                        console.log("Texto extraído via OCR:", text);
                        // Regex para identificar padrões de CNPJ
                        const match = text.match(/(\d{2}[\.\s]?\d{3}[\.\s]?\d{3}[\/\s]?\d{4}[\-\s]?\d{2})/);
                        if (match) {
                            const detectedCnpj = match[0].replace(/\D/g, "");
                            if (detectedCnpj.length === 14) {
                                cnpjInput.value = detectedCnpj;
                                consultarCnpjServico(detectedCnpj);
                            } else {
                                cnpjInfoDiv.innerHTML = '<span style="color: #94a3b8;">Nenhum CNPJ detectado automaticamente na imagem. Digite se necessário.</span>';
                            }
                        } else {
                            cnpjInfoDiv.innerHTML = '<span style="color: #94a3b8;">Nenhum CNPJ lido na foto. Por favor, confirme o número.</span>';
                        }
                    }).catch(err => {
                        console.error("Erro OCR Tesseract:", err);
                        cnpjInfoDiv.innerHTML = '<span style="color: #94a3b8;">Não foi possível ler a imagem com OCR.</span>';
                    });
                }

            } else if (file.type === 'application/pdf') {
                thumb.style.backgroundImage = 'none';
                thumb.textContent = '📄';
            } else {
                thumb.style.backgroundImage = 'none';
                thumb.textContent = '📎';
            }
        });
    }
</script>
