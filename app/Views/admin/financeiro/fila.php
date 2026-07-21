<div class="page-header">
    <div>
        <h2>Fila de Aprovação Financeira / Fiscal</h2>
        <p class="subtitle">Análise jurídica, fiscal e de conformidade de despesas gerais, viagens e atividades de militância.</p>
    </div>
    <div>
        <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="btn btn-secondary">
            ⬅️ Voltar ao Financeiro
        </a>
    </div>
</div>

<?php include __DIR__ . '/_nav_tabs.php'; ?>

<!-- Secção 1: Despesas Gerais Pendentes -->
<div class="panel-card">
    <div class="card-header">
        <h3>Despesas Gerais Aguardando Aprovação (<?= count($pendingExpenses) ?>)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Fornecedor / Categoria</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Lançado por</th>
                    <th>Comprovante</th>
                    <th style="min-width: 250px;">Ações / Vinculação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingExpenses)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 20px;">Nenhuma despesa pendente de aprovação.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingExpenses as $exp): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($exp['date_incurred'])) ?></td>
                            <td>
                                <div class="user-log-name"><?= htmlspecialchars($exp['supplier_name']) ?></div>
                                <?php if (!empty($exp['supplier_cnpj_cpf'])): 
                                    $digitsCnpj = preg_replace('/\D/', '', $exp['supplier_cnpj_cpf']);
                                    $fmtCnpj = (strlen($digitsCnpj) === 14) 
                                        ? preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $digitsCnpj) 
                                        : ((strlen($digitsCnpj) === 11) ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $digitsCnpj) : $exp['supplier_cnpj_cpf']);
                                ?>
                                    <div style="font-size: 11px; color: #94a3b8; font-weight: 600;">CNPJ/CPF: <?= htmlspecialchars($fmtCnpj) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($exp['spce_code'])): ?>
                                    <span style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($exp['spce_code']) ?> - <?= htmlspecialchars($exp['spce_desc']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 9px; padding: 1px 4px;">⚠️ Gasto de Campo (Sem Vínculo)</span>
                                    <?php if (!empty($exp['expense_type_name'])): ?>
                                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Tipo informado: <strong><?= htmlspecialchars($exp['expense_type_name']) ?></strong></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($exp['description']) ?></td>
                            <td style="font-weight: 600; color: var(--warning-color);">R$ <?= number_format($exp['value'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($exp['creator_name']) ?></td>
                            <td>
                                <?php if (!empty($exp['doc_id'])): ?>
                                    <a href="<?= $this->baseUrl('admin/financeiro/comprovante?id=' . $exp['doc_id'] . '&type=expense') ?>" target="_blank" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 11px;">
                                        📄 Ver
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary);">Sem anexo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; width: 100%;">
                                    <form action="<?= $this->baseUrl('admin/financeiro/aprovar') ?>" method="POST" style="display: flex; flex-direction: column; gap: 6px; width: 100%;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="type" value="expense">
                                        <input type="hidden" name="id" value="<?= $exp['id'] ?>">
                                        
                                        <?php if (empty($exp['bank_account_id'])): ?>
                                            <select name="bank_account_id" required style="font-size: 11px; padding: 6px; border-radius: 6px; background: #1e293b; color: #fff; border: 1px solid #475569; width: 100%; box-sizing: border-box;">
                                                <option value="">-- Selecione a Conta Origem --</option>
                                                <?php foreach ($bankAccounts as $acc): ?>
                                                    <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?> (Saldo: R$ <?= number_format($acc['balance'], 2, ',', '.') ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                            
                                            <select name="spce_category_id" required style="font-size: 11px; padding: 6px; border-radius: 6px; background: #1e293b; color: #fff; border: 1px solid #475569; width: 100%; box-sizing: border-box;">
                                                <option value="">-- Selecione a Categoria SPCE --</option>
                                                <?php foreach ($spceCategories as $cat): ?>
                                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                        
                                        <div style="display: flex; gap: 6px;">
                                            <button type="submit" class="btn btn-success btn-sm" style="min-height:30px; font-size: 11px; flex: 1;">Aprovar</button>
                                            <button type="button" class="btn btn-warning btn-sm" style="min-height:30px; font-size: 11px; flex: 1; background: #eab308; color: #0f172a; border: none; font-weight: bold;" onclick='editarDespesaAdmin(<?= json_encode($exp, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>✏️ Editar</button>
                                            <button type="button" class="btn btn-secondary btn-sm btn-danger-hover" style="min-height:30px; font-size: 11px; flex: 1;" onclick="rejeitarRegistro('expense', <?= $exp['id'] ?>)">Rejeitar</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Secção 2: Reembolsos/Despesas de Viagem Pendentes -->
<div class="panel-card">
    <div class="card-header">
        <h3>Despesas e Reembolsos de Viagens (<?= count($pendingTravels) ?>)</h3>
    </div>
    
    <?php if (empty($pendingTravels)): ?>
        <p style="text-align: center; color: var(--text-secondary); padding: 20px; font-size: 13px;">Nenhuma viagem aguardando aprovação.</p>
    <?php else: ?>
        <?php foreach ($pendingTravels as $tr): ?>
            <div style="background-color: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; border-bottom: 1px dashed var(--border-color); padding-bottom: 12px;">
                    <div>
                        <span style="font-size: 12px; color: var(--accent-teal-hover); text-transform: uppercase; font-weight: 600;">Relatório de Viagem #<?= $tr['id'] ?></span>
                        <h4 style="font-size: 15px; font-weight: 600; margin-top: 2px;"><?= htmlspecialchars($tr['purpose']) ?></h4>
                        <span style="font-size: 12px; color: var(--text-secondary);">
                            Colaborador: <strong><?= htmlspecialchars($tr['user_name']) ?></strong> (<?= htmlspecialchars($tr['celular']) ?>) &bull; Placa: <?= htmlspecialchars($tr['vehicle_plate'] ?? 'Não informada') ?>
                        </span>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 12px; color: var(--text-secondary);">Período: <?= date('d/m/Y', strtotime($tr['start_date'])) ?> até <?= date('d/m/Y', strtotime($tr['end_date'])) ?></span>
                        <div style="margin-top: 4px;">
                            <form action="<?= $this->baseUrl('admin/financeiro/aprovar') ?>" method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="type" value="travel">
                                <input type="hidden" name="id" value="<?= $tr['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm" style="margin-right: 8px;">Aprovar Relatório</button>
                            </form>
                            <button class="btn btn-secondary btn-sm" style="background-color: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: var(--error-color);" onclick="rejeitarRegistro('travel', <?= $tr['id'] ?>)">Rejeitar</button>
                        </div>
                    </div>
                </div>

                <div style="padding-left: 16px; border-left: 3px solid var(--accent-indigo);">
                    <h5 style="font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-secondary);">Recibos de Combustível/Despesas Anexados:</h5>
                    <table class="table" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>Data Recibo</th>
                                <th>CNPJ Posto/Fornecedor</th>
                                <th>Categoria SPCE</th>
                                <th>Valor</th>
                                <th style="width: 120px;">Voucher Cripto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tr['receipts'])): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-secondary);">Nenhum recibo anexado a esta viagem.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tr['receipts'] as $rec): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($rec['receipt_date'])) ?></td>
                                        <td style="font-family: monospace;"><?= htmlspecialchars($rec['supplier_cnpj']) ?></td>
                                        <td><?= htmlspecialchars($rec['spce_code']) ?> - <?= htmlspecialchars($rec['spce_desc']) ?></td>
                                        <td style="font-weight: 600; color: var(--text-primary);">R$ <?= number_format($rec['value'], 2, ',', '.') ?></td>
                                        <td>
                                            <a href="<?= $this->baseUrl('admin/financeiro/comprovante?id=' . $rec['id'] . '&type=travel') ?>" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 10px; padding: 2px 6px; min-height: 24px;">
                                                📄 Abrir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Secção 3: Atividades de Militância Pendentes (Panfletagem) -->
<div class="panel-card">
    <div class="card-header">
        <h3>Atividades de Campo e Panfletagem Georreferenciadas (<?= count($pendingMilitancy) ?>)</h3>
    </div>
    
    <?php if (empty($pendingMilitancy)): ?>
        <p style="text-align: center; color: var(--text-secondary); padding: 20px; font-size: 13px;">Nenhuma atividade pendente de aprovação.</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
            <?php foreach ($pendingMilitancy as $act): ?>
                <div style="background-color: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
                    <!-- Foto Criptografada Carregada em Tempo de Execução -->
                    <div style="width: 100%; height: 200px; background-color: #0b1120; position: relative;">
                        <img src="<?= $this->baseUrl('admin/financeiro/comprovante?id=' . $act['id'] . '&type=militancy') ?>" alt="Foto da Militância" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(15,23,42,0.85); padding: 4px 8px; border-radius: 6px; font-size: 10px; color: var(--accent-teal-hover); border: 1px solid rgba(255,255,255,0.1);">
                            🔐 AES-256-CBC
                        </div>
                    </div>
                    
                    <div style="padding: 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-secondary); margin-bottom: 6px;">
                                <span>Colaborador: <strong><?= htmlspecialchars($act['user_name']) ?></strong></span>
                                <span><?= date('d/m/Y', strtotime($act['activity_date'])) ?></span>
                            </div>
                            <h4 style="font-size: 13px; font-weight: 500; margin-bottom: 12px; line-height: 1.4;"><?= htmlspecialchars($act['description']) ?></h4>
                            
                            <!-- Geolocalização Link Google Maps -->
                            <div style="background: rgba(30,41,59,0.5); padding: 8px 12px; border-radius: 8px; margin-bottom: 16px; font-size: 11px;">
                                📍 <strong>Coordenadas GPS:</strong><br>
                                <span style="font-family: monospace; color: var(--text-secondary);"><?= $act['latitude'] ?>, <?= $act['longitude'] ?></span>
                                <div style="margin-top: 4px;">
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= $act['latitude'] ?>,<?= $act['longitude'] ?>" target="_blank" style="color: var(--accent-teal-hover); text-decoration: none; font-weight: 600;">
                                        🗺️ Ver no Google Maps &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 12px;">
                            <form action="<?= $this->baseUrl('admin/financeiro/aprovar') ?>" method="POST" style="flex: 1;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="type" value="militancy">
                                <input type="hidden" name="id" value="<?= $act['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm btn-block">Homologar</button>
                            </form>
                            <button class="btn btn-secondary btn-sm" style="background-color: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: var(--error-color); flex: 1;" onclick="rejeitarRegistro('militancy', <?= $act['id'] ?>)">
                                Rejeitar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para Digitar Justificativa de Rejeição -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 1000; justify-content: center; align-items: center; padding: 20px;">
    <div class="panel-card" style="width: 100%; max-width: 480px; margin-bottom: 0; background-color: #111827;">
        <div class="card-header">
            <h3>Justificativa de Rejeição</h3>
        </div>
        <form action="<?= $this->baseUrl('admin/financeiro/rejeitar') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="reject_type" name="type" value="">
            <input type="hidden" id="reject_id" name="id" value="">

            <div class="form-group">
                <label for="reject_notes">Justificativa (Este texto será gravado nos logs e exibido ao colaborador)</label>
                <textarea id="reject_notes" name="notes" rows="4" placeholder="Descreva os motivos da rejeição (documento ilegível, divergência de valores, etc)..." required></textarea>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 16px;">
                <button type="button" class="btn btn-secondary flex-1" onclick="fecharRejectModal()">Cancelar</button>
                <button type="submit" class="btn btn-teal flex-1" style="background-color: var(--error-color);">Confirmar Rejeição</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Edição Direta pelo Administrador -->
<div id="adminEditExpenseModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(15,23,42,0.85); z-index: 10000; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(4px);">
    <div class="panel-card" style="width: 100%; max-width: 580px; margin-bottom: 0; background-color: #0f172a; border: 2px solid #eab308; max-height: 90vh; overflow-y: auto;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; margin-bottom: 16px; border-bottom: 1px solid #334155;">
            <h3 style="font-size: 16px; font-weight: 700; color: #fde047; margin: 0;">✏️ Editar Gasto (Administrador)</h3>
            <button type="button" onclick="fecharAdminEditModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 18px; cursor: pointer;">✕</button>
        </div>

        <form action="<?= $this->baseUrl('admin/financeiro/despesas/editar') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="redirect_to" value="/admin/financeiro/fila">
            <input type="hidden" id="admin_edit_id" name="id" value="">

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 600;">Descrição do Gasto</label>
                <input type="text" id="admin_edit_description" name="description" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">CPF/CNPJ Fornecedor</label>
                    <input type="text" id="admin_edit_supplier_cnpj_cpf" name="supplier_cnpj_cpf" oninput="formatarCnpjCpf(this)" maxlength="18" placeholder="00.000.000/0001-00" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Razão Social Fornecedor</label>
                    <input type="text" id="admin_edit_supplier_name" name="supplier_name" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Valor (R$)</label>
                    <input type="text" id="admin_edit_value" name="value" onkeyup="formatarMoeda(this)" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px; font-weight: bold;">
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Data do Gasto</label>
                    <input type="date" id="admin_edit_date_incurred" name="date_incurred" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Tipo de Despesa</label>
                    <select id="admin_edit_expense_type_id" name="expense_type_id" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                        <option value="">-- Selecione o Tipo --</option>
                        <?php foreach ($expenseTypes as $type): ?>
                            <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Conta Bancária Origem</label>
                    <select id="admin_edit_bank_account_id" name="bank_account_id" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                        <option value="">-- Selecione a Conta --</option>
                        <?php foreach ($bankAccounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600;">Categoria SPCE</label>
                <select id="admin_edit_spce_category_id" name="spce_category_id" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                    <option value="">-- Selecione a Categoria SPCE --</option>
                    <?php foreach ($spceCategories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 600;">Observações / Notas do Administrador</label>
                <textarea id="admin_edit_notes" name="notes" rows="3" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 12px;"></textarea>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 16px;">
                <button type="button" class="btn btn-secondary flex-1" onclick="fecharAdminEditModal()">Cancelar</button>
                <button type="submit" class="btn btn-teal flex-1" style="background: #eab308; color: #0f172a; font-weight: 800;">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
    function formatarCnpjCpf(input) {
        let value = input.value.replace(/\D/g, "");
        if (value.length > 14) {
            value = value.substring(0, 14);
        }
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        } else {
            value = value.replace(/^(\d{2})(\d)/, "$1.$2");
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
            value = value.replace(/\.(\d{3})(\d)/, ".$1/$2");
            value = value.replace(/(\d{4})(\d)/, "$1-$2");
        }
        input.value = value;
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

    function rejeitarRegistro(type, id) {
        document.getElementById('reject_type').value = type;
        document.getElementById('reject_id').value = id;
        document.getElementById('reject_notes').value = '';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function fecharRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    function editarDespesaAdmin(exp) {
        document.getElementById('admin_edit_id').value = exp.id;
        document.getElementById('admin_edit_description').value = exp.description || '';
        
        const cnpjInput = document.getElementById('admin_edit_supplier_cnpj_cpf');
        if (cnpjInput) {
            cnpjInput.value = exp.supplier_cnpj_cpf || '';
            formatarCnpjCpf(cnpjInput);
        }

        document.getElementById('admin_edit_supplier_name').value = exp.supplier_name || '';
        document.getElementById('admin_edit_date_incurred').value = exp.date_incurred || '';
        document.getElementById('admin_edit_expense_type_id').value = exp.expense_type_id || '';
        document.getElementById('admin_edit_bank_account_id').value = exp.bank_account_id || '';
        document.getElementById('admin_edit_spce_category_id').value = exp.spce_category_id || '';
        document.getElementById('admin_edit_notes').value = exp.notes || '';

        const valFloat = parseFloat(exp.value || 0);
        document.getElementById('admin_edit_value').value = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valFloat);

        document.getElementById('adminEditExpenseModal').style.display = 'flex';
    }

    function fecharAdminEditModal() {
        document.getElementById('adminEditExpenseModal').style.display = 'none';
    }
</script>
