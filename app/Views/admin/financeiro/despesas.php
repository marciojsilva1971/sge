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

            <!-- 1º PASSO: UPLOAD / DIGITALIZAÇÃO DO COMPROVANTE (OCR) -->
            <div class="form-group" style="background: rgba(13, 148, 136, 0.08); border: 2px dashed var(--accent-teal); padding: 14px; border-radius: 12px; margin-bottom: 16px;">
                <label for="comprovante" style="font-size: 13px; font-weight: 700; color: var(--accent-teal-hover); display: flex; align-items: center; gap: 6px;">
                    📸 1º PASSO: Anexar / Digitalizar Comprovante Fiscal (PDF, PNG, JPG)
                </label>
                <p style="font-size: 11px; color: var(--text-secondary); margin-bottom: 8px;">
                    Anexe o comprovante primeiro. O sistema fará o escaneamento OCR para identificar o CNPJ e selecionar automaticamente o fornecedor!
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

            <div class="form-group">
                <label for="description">Descrição do Pagamento / Finalidade</label>
                <input type="text" id="description" name="description" placeholder="Ex: Impressão de 10.000 Santinhos de Militância" required>
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
    const compInputAdmin = document.getElementById('comprovante');
    const btnScanOcrAdmin = document.getElementById('btn-scan-ocr');
    const ocrStatusBadgeAdmin = document.getElementById('ocr_status_badge');

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

        if (ocrStatusBadgeAdmin) {
            ocrStatusBadgeAdmin.style.display = 'block';
            ocrStatusBadgeAdmin.innerHTML = `
                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">⏳</span>
                    <span>Iniciando motor de leitura OCR... Por favor, aguarde.</span>
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

            Tesseract.recognize(file, 'por', {
                logger: m => {
                    if (m.status === 'recognizing text' && ocrStatusBadgeAdmin) {
                        const pct = Math.round((m.progress || 0) * 100);
                        ocrStatusBadgeAdmin.innerHTML = `
                            <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">🔍</span>
                                <span>Lendo comprovante via OCR (${pct}%)... Processando.</span>
                            </div>
                        `;
                    }
                }
            }).then(({ data: { text } }) => {
                const match = text.match(/(\d{2}[\.\s]?\d{3}[\.\s]?\d{3}[\/\s]?\d{4}[\-\s]?\d{2})/);
                if (match) {
                    const cleanCnpj = match[0].replace(/\D/g, "");
                    fetch('<?= $this->baseUrl("api/cnpj/consultar") ?>?cnpj=' + cleanCnpj)
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && ocrStatusBadgeAdmin) {
                                ocrStatusBadgeAdmin.innerHTML = `
                                    <div style="padding: 10px 12px; background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; border-radius: 8px; color: #4ade80; font-weight: 600; font-size: 12px;">
                                        ✅ CNPJ Lido: ${data.cnpj} - ${data.razao_social}
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
                                ocrStatusBadgeAdmin.innerHTML = `<div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px;">⚠️ CNPJ Lido (${match[0]}), selecione o fornecedor manualmente.</div>`;
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
                return;
            }

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
