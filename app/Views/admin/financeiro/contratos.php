<div class="page-header">
    <div>
        <h2>Contratos por Tempo Determinado</h2>
        <p class="subtitle">Gestão de instrumentos contratuais, vigência e arquivos PDF de contratos com fornecedores e prestadores de serviço.</p>
    </div>
    <div>
        <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="btn btn-secondary">
            ⬅️ Voltar ao Financeiro
        </a>
    </div>
</div>

<?php include __DIR__ . '/_nav_tabs.php'; ?>

<div class="dashboard-sections" style="display: flex; gap: 24px; flex-wrap: wrap;">
    
    <!-- Painel 1: Formulário de Cadastro de Novo Contrato -->
    <div class="panel-card" style="flex: 1; min-width: 320px; background: #1e293b; border-radius: 12px; padding: 20px; border: 1px solid rgba(255,255,255,0.08);">
        <div class="card-header" style="margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
                <span>➕ Cadastrar Novo Contrato</span>
            </h3>
        </div>
        
        <form action="<?= $this->baseUrl('admin/financeiro/contratos') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="supplier_id" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Empresa / Fornecedor Cadastrado *</label>
                <select id="supplier_id" name="supplier_id" required style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                    <option value="">-- Selecione uma Empresa --</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>">
                            <?= htmlspecialchars($sup['corporate_name']) ?> (<?= htmlspecialchars($sup['cnpj_cpf']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($suppliers)): ?>
                    <small style="color: #f59e0b; display: block; margin-top: 4px;">⚠️ Nenhuma empresa cadastrada. <a href="<?= $this->baseUrl('admin/financeiro/fornecedores') ?>" style="color: #38bdf8; text-decoration: underline;">Cadastre um fornecedor primeiro</a>.</small>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 12px; margin-bottom: 14px;">
                <div class="form-group" style="flex: 1;">
                    <label for="contract_number" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Nº do Contrato (Opcional)</label>
                    <input type="text" id="contract_number" name="contract_number" placeholder="Ex: CT-2026/001" style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
                <div class="form-group" style="flex: 2;">
                    <label for="title" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Objeto / Título do Contrato *</label>
                    <input type="text" id="title" name="title" placeholder="Ex: Prestação de Serviços de Publicidade e Mídia" required style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-bottom: 14px;">
                <div class="form-group" style="flex: 1;">
                    <label for="total_amount" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Valor Total do Contrato (R$) *</label>
                    <input type="text" id="total_amount" name="total_amount" placeholder="R$ 0,00" required onkeyup="formatCurrencyBrl(this)" style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="monthly_amount" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Valor Mensal / Parcela (R$)</label>
                    <input type="text" id="monthly_amount" name="monthly_amount" placeholder="R$ 0,00 (Se houver)" onkeyup="formatCurrencyBrl(this)" style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-bottom: 14px;">
                <div class="form-group" style="flex: 1;">
                    <label for="start_date" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Data de Início da Vigência *</label>
                    <input type="date" id="start_date" name="start_date" required style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="end_date" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Data de Término (Prazo Determinado) *</label>
                    <input type="date" id="end_date" name="end_date" required style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="description" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Descrição / Observações Contratuais</label>
                <textarea id="description" name="description" rows="2" placeholder="Ex: Cláusulas principais, condições de pagamento ou detalhes do serviço..." style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px; font-family: inherit; resize: vertical;"></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 18px;">
                <label for="contract_pdf" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Anexar Arquivo do Contrato (PDF Opcional)</label>
                <input type="file" id="contract_pdf" name="contract_pdf" accept="application/pdf" style="width: 100%; padding: 8px; border-radius: 6px; background: #0f172a; border: 1px dashed #38bdf8; color: #f8fafc; font-size: 12px;">
                <small style="color: #94a3b8; font-size: 11px;">Somente arquivos .pdf (Máx: 10MB)</small>
            </div>

            <button type="submit" class="btn btn-teal btn-block" style="width: 100%; padding: 12px; font-weight: bold; font-size: 14px; background: #0d9488; color: white; border: none; border-radius: 8px; cursor: pointer;">
                💾 Cadastrar & Salvar Contrato
            </button>
        </form>
    </div>

    <!-- Painel 2: Tabela de Contratos Cadastrados -->
    <div class="panel-card" style="flex: 2; min-width: 480px; background: #1e293b; border-radius: 12px; padding: 20px; border: 1px solid rgba(255,255,255,0.08);">
        <div class="card-header" style="margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 600; color: #f8fafc;">📋 Contratos por Tempo Determinado</h3>
            <span style="font-size: 12px; background: #334155; color: #cbd5e1; padding: 4px 10px; border-radius: 20px; font-weight: 600;">
                Total: <?= count($contracts) ?>
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-striped" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #334155; text-align: left; font-size: 12px; color: #94a3b8;">
                        <th style="padding: 10px;">Fornecedor</th>
                        <th style="padding: 10px;">Contrato / Objeto</th>
                        <th style="padding: 10px;">Vigência</th>
                        <th style="padding: 10px;">Valor Total</th>
                        <th style="padding: 10px;">Status</th>
                        <th style="padding: 10px; text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contracts)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 24px; color: #94a3b8; font-size: 13px;">
                                Nenhum contrato por tempo determinado cadastrado até o momento.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contracts as $c): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 13px;">
                                <td style="padding: 10px;">
                                    <div style="font-weight: 600; color: #f8fafc;"><?= htmlspecialchars($c['corporate_name']) ?></div>
                                    <div style="font-size: 11px; color: #94a3b8; font-family: monospace;"><?= htmlspecialchars($c['cnpj_cpf']) ?></div>
                                </td>
                                <td style="padding: 10px;">
                                    <div style="font-weight: 500; color: #38bdf8;"><?= htmlspecialchars($c['title']) ?></div>
                                    <?php if (!empty($c['contract_number'])): ?>
                                        <div style="font-size: 11px; color: #cbd5e1;">Nº: <?= htmlspecialchars($c['contract_number']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px; font-size: 12px; color: #cbd5e1;">
                                    📅 <?= date('d/m/Y', strtotime($c['start_date'])) ?> até <br>
                                    🏁 <?= date('d/m/Y', strtotime($c['end_date'])) ?>
                                </td>
                                <td style="padding: 10px; font-weight: 600; color: #22c55e;">
                                    R$ <?= number_format($c['total_amount'], 2, ',', '.') ?>
                                    <?php if (!empty($c['monthly_amount']) && $c['monthly_amount'] > 0): ?>
                                        <div style="font-size: 11px; font-weight: normal; color: #94a3b8;">(R$ <?= number_format($c['monthly_amount'], 2, ',', '.') ?>/mês)</div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px;">
                                    <?php if ($c['status'] === 'VIGENTE'): ?>
                                        <span style="background: rgba(34, 197, 94, 0.15); color: #22c55e; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: bold; border: 1px solid rgba(34, 197, 94, 0.3);">
                                            ● Vigente
                                        </span>
                                    <?php elseif ($c['status'] === 'ENCERRADO'): ?>
                                        <span style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: bold; border: 1px solid rgba(148, 163, 184, 0.3);">
                                            ■ Encerrado
                                        </span>
                                    <?php else: ?>
                                        <span style="background: rgba(239, 68, 68, 0.15); color: #ef4444; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: bold; border: 1px solid rgba(239, 68, 68, 0.3);">
                                            ✖ Cancelado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                        <?php if (!empty($c['file_path'])): ?>
                                            <a href="<?= $this->baseUrl('admin/financeiro/contratos/download?id=' . $c['id']) ?>" target="_blank" class="btn btn-sm" style="background: #0284c7; color: white; border: none; border-radius: 6px; padding: 6px 10px; font-size: 11px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Ver Contrato PDF">
                                                📄 PDF
                                            </a>
                                        <?php else: ?>
                                            <span style="font-size: 11px; color: #64748b; background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 4px;">Sem PDF</span>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm" onclick='abrirModalEditarContrato(<?= json_encode($c) ?>)' style="background: #334155; color: #f8fafc; border: 1px solid #475569; border-radius: 6px; padding: 6px 10px; font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Editar dados do contrato ou anexar PDF">
                                            ✏️ Editar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Edição de Contrato -->
<div id="modalEditarContrato" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: #1e293b; border-radius: 12px; width: 100%; max-width: 600px; padding: 24px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #f8fafc;">✏️ Editar Contrato por Tempo Determinado</h3>
            <button type="button" onclick="fecharModalEditarContrato()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form action="<?= $this->baseUrl('admin/financeiro/contratos/editar') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="edit_id" name="id">

            <div class="form-group" style="margin-bottom: 12px;">
                <label for="edit_supplier_id" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Empresa / Fornecedor *</label>
                <select id="edit_supplier_id" name="supplier_id" required style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['corporate_name']) ?> (<?= htmlspecialchars($sup['cnpj_cpf']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label for="edit_contract_number" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Nº do Contrato</label>
                    <input type="text" id="edit_contract_number" name="contract_number" style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
                <div class="form-group" style="flex: 2;">
                    <label for="edit_title" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Objeto do Contrato *</label>
                    <input type="text" id="edit_title" name="title" required style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label for="edit_total_amount" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Valor Total (R$) *</label>
                    <input type="text" id="edit_total_amount" name="total_amount" required onkeyup="formatCurrencyBrl(this)" style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="edit_monthly_amount" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Valor Mensal (R$)</label>
                    <input type="text" id="edit_monthly_amount" name="monthly_amount" onkeyup="formatCurrencyBrl(this)" style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label for="edit_start_date" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Início da Vigência *</label>
                    <input type="date" id="edit_start_date" name="start_date" required style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="edit_end_date" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Término da Vigência *</label>
                    <input type="date" id="edit_end_date" name="end_date" required style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label for="edit_status" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Status do Contrato *</label>
                <select id="edit_status" name="status" required style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px;">
                    <option value="VIGENTE">VIGENTE (Ativo)</option>
                    <option value="ENCERRADO">ENCERRADO (Finalizado)</option>
                    <option value="CANCELADO">CANCELADO (Anulado)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label for="edit_description" style="font-size: 12px; font-weight: 600; color: #cbd5e1; display: block; margin-bottom: 4px;">Descrição / Observações</label>
                <textarea id="edit_description" name="description" rows="2" style="width: 100%; padding: 10px; border-radius: 6px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px; font-family: inherit; resize: vertical;"></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 18px; background: #0f172a; padding: 12px; border-radius: 8px; border: 1px solid #334155;">
                <label for="edit_contract_pdf" style="font-size: 12px; font-weight: 600; color: #38bdf8; display: block; margin-bottom: 4px;">Substituir Arquivo PDF do Contrato (Opcional)</label>
                <input type="file" id="edit_contract_pdf" name="contract_pdf" accept="application/pdf" style="width: 100%; color: #f8fafc; font-size: 12px;">
                <small style="color: #94a3b8; font-size: 11px; display: block; margin-top: 4px;">Deixe em branco para manter o arquivo PDF atual registrado.</small>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="fecharModalEditarContrato()" class="btn btn-secondary" style="padding: 10px 16px; border-radius: 6px;">Cancelar</button>
                <button type="submit" class="btn btn-teal" style="padding: 10px 20px; border-radius: 6px; background: #0d9488; color: white; border: none; font-weight: bold;">💾 Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
function formatCurrencyBrl(input) {
    let value = input.value.replace(/\D/g, "");
    if (value === "") {
        input.value = "";
        return;
    }
    value = (parseInt(value, 10) / 100).toFixed(2) + "";
    value = value.replace(".", ",");
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
    input.value = "R$ " + value;
}

function abrirModalEditarContrato(c) {
    document.getElementById('edit_id').value = c.id;
    document.getElementById('edit_supplier_id').value = c.supplier_id;
    document.getElementById('edit_contract_number').value = c.contract_number || '';
    document.getElementById('edit_title').value = c.title || '';
    document.getElementById('edit_description').value = c.description || '';
    
    // Formata valores
    if (c.total_amount) {
        let totalFormatted = parseFloat(c.total_amount).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        document.getElementById('edit_total_amount').value = 'R$ ' + totalFormatted;
    }
    if (c.monthly_amount && parseFloat(c.monthly_amount) > 0) {
        let monthlyFormatted = parseFloat(c.monthly_amount).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        document.getElementById('edit_monthly_amount').value = 'R$ ' + monthlyFormatted;
    } else {
        document.getElementById('edit_monthly_amount').value = '';
    }

    document.getElementById('edit_start_date').value = c.start_date;
    document.getElementById('edit_end_date').value = c.end_date;
    document.getElementById('edit_status').value = c.status;

    document.getElementById('modalEditarContrato').style.display = 'flex';
}

function fecharModalEditarContrato() {
    document.getElementById('modalEditarContrato').style.display = 'none';
}
</script>
