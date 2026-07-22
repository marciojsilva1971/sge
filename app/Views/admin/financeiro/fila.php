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

<!-- Secção 2: Gastos com Combustível & Viagens de Campo -->
<div class="panel-card" style="border: 1px solid rgba(45, 212, 191, 0.3);">
    <div class="card-header" style="background: rgba(13, 148, 136, 0.1); padding: 14px 16px; border-bottom: 1px solid rgba(45, 212, 191, 0.2);">
        <h3 style="color: #2dd4bf; font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            ⛽ Gastos com Combustível & Viagens de Campo (<?= count($pendingTravels) ?>)
        </h3>
    </div>
    
    <?php if (empty($pendingTravels)): ?>
        <p style="text-align: center; color: var(--text-secondary); padding: 20px; font-size: 13px;">Nenhum gasto com combustível aguardando aprovação.</p>
    <?php else: ?>
        <?php foreach ($pendingTravels as $tr): ?>
            <div style="background-color: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 20px; margin: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 12px;">
                    <div>
                        <span style="font-size: 11px; color: var(--accent-teal-hover); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Relatório de Viagem #<?= $tr['id'] ?></span>
                        <h4 style="font-size: 16px; font-weight: 700; margin-top: 2px; color: #f8fafc;"><?= htmlspecialchars($tr['purpose']) ?></h4>
                        <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-top: 4px;">
                            Colaborador: <strong><?= htmlspecialchars($tr['user_name']) ?></strong> (<?= htmlspecialchars($tr['celular']) ?>) &bull; Placa: <strong><?= htmlspecialchars(strtoupper($tr['vehicle_plate'] ?? 'N/I')) ?></strong>
                            <?php if (!empty($tr['initial_km'])): ?>
                                &bull; KM: <strong><?= number_format($tr['initial_km'], 0, ',', '.') ?> KM</strong> &rarr; <strong><?= !empty($tr['final_km']) ? number_format($tr['final_km'], 0, ',', '.') . ' KM' : 'Pendente' ?></strong>
                                <?php if (!empty($tr['final_km'])): ?>
                                    (<span style="color: #4ade80; font-weight: 700;"><?= number_format($tr['final_km'] - $tr['initial_km'], 0, ',', '.') ?> KM rodados</span>)
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                        <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-top: 2px;">
                            Período: <?= date('d/m/Y', strtotime($tr['start_date'])) ?> até <?= date('d/m/Y', strtotime($tr['end_date'])) ?>
                        </span>
                    </div>

                    <!-- Formulário de Aprovação & Vinculação de Conta e Categoria SPCE -->
                    <div style="background: rgba(30, 41, 59, 0.8); border: 1px solid #334155; border-radius: 10px; padding: 12px; max-width: 440px; width: 100%;">
                        <div style="font-size: 11px; font-weight: 700; color: #2dd4bf; margin-bottom: 8px;">
                            ⚖️ Vinculação Financeira & Fiscal (TSE):
                        </div>
                        <form action="<?= $this->baseUrl('admin/financeiro/aprovar') ?>" method="POST" style="display: flex; flex-direction: column; gap: 8px;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="type" value="travel">
                            <input type="hidden" name="id" value="<?= $tr['id'] ?>">

                            <div style="display: flex; gap: 6px;">
                                <select name="bank_account_id" required style="font-size: 11px; padding: 6px; border-radius: 6px; background: #0f172a; color: #fff; border: 1px solid #475569; flex: 1;">
                                    <option value="">-- Conta Bancária Origem * --</option>
                                    <?php foreach ($bankAccounts as $acc): ?>
                                        <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?> (Saldo: R$ <?= number_format($acc['balance'], 2, ',', '.') ?>)</option>
                                    <?php endforeach; ?>
                                </select>

                                <select name="spce_category_id" required style="font-size: 11px; padding: 6px; border-radius: 6px; background: #0f172a; color: #fff; border: 1px solid #475569; flex: 1;">
                                    <option value="">-- Categoria SPCE * --</option>
                                    <?php foreach ($spceCategories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= strpos($cat['code'], '44') !== false ? 'selected' : '' ?>><?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div style="display: flex; gap: 6px; margin-top: 4px;">
                                <button type="submit" class="btn btn-success btn-sm" style="flex: 1; font-weight: 700; font-size: 11px; padding: 6px;">🚀 Aprovar e Lançar Despesa</button>
                                <button type="button" class="btn btn-secondary btn-sm btn-danger-hover" style="font-size: 11px; padding: 6px;" onclick="rejeitarRegistro('travel', <?= $tr['id'] ?>)">Rejeitar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div style="padding-left: 16px; border-left: 3px solid var(--accent-teal);">
                    <h5 style="font-size: 12px; font-weight: 700; margin-bottom: 8px; color: #94a3b8; text-transform: uppercase;">Recibos de Combustível / Cupons Fiscais Anexados:</h5>
                    <table class="table" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>Data Recibo</th>
                                <th>Posto / Fornecedor</th>
                                <th>CNPJ</th>
                                <th>Categoria SPCE</th>
                                <th>Valor (R$)</th>
                                <th>Comprovante</th>
                                <th style="text-align: center;">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tr['receipts'])): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-secondary);">Nenhum recibo anexado a esta viagem.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tr['receipts'] as $rec): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($rec['receipt_date'])) ?></td>
                                        <td style="font-weight: 600;"><?= htmlspecialchars($rec['supplier_name'] ?? 'Posto de Combustível') ?></td>
                                        <td style="font-family: monospace; font-size: 11px;"><?= htmlspecialchars($rec['supplier_cnpj']) ?></td>
                                        <td><?= htmlspecialchars($rec['spce_code'] ?? '44') ?> - <?= htmlspecialchars($rec['spce_desc'] ?? 'Combustíveis e lubrificantes') ?></td>
                                        <td style="font-weight: 700; color: #4ade80;">R$ <?= number_format($rec['value'], 2, ',', '.') ?></td>
                                        <td>
                                            <a href="<?= $this->baseUrl('admin/financeiro/comprovante?id=' . $rec['id'] . '&type=travel') ?>" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 10px; padding: 2px 6px; min-height: 24px;">
                                                📄 Abrir Cripto
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn btn-warning btn-sm" style="font-size: 10px; padding: 2px 6px; min-height: 24px; background: #eab308; color: #0f172a; font-weight: 700;" onclick='editarReciboCombustivelAdmin(<?= json_encode($rec, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                ✏️ Editar
                                            </button>
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
                    <input type="text" id="admin_edit_supplier_cnpj_cpf" name="supplier_cnpj_cpf" oninput="formatarCnpjCpf(this); if (this.value.replace(/\D/g, '').length === 14) consultarCnpjModalAdmin(this, document.getElementById('admin_edit_supplier_name'), document.getElementById('admin_edit_cnpj_info'));" maxlength="18" placeholder="00.000.000/0001-00" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                    <div id="admin_edit_cnpj_info" style="margin-top: 4px; font-size: 11px;"></div>
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

    function editarReciboCombustivelAdmin(rec) {
        document.getElementById('edit_receipt_id').value = rec.id;
        document.getElementById('edit_receipt_supplier_cnpj').value = rec.supplier_cnpj || '';
        formatarCnpjCpf(document.getElementById('edit_receipt_supplier_cnpj'));
        
        document.getElementById('edit_receipt_supplier_name').value = rec.supplier_name || '';
        document.getElementById('edit_receipt_date').value = rec.receipt_date || '';
        document.getElementById('edit_receipt_spce_category_id').value = rec.spce_category_id || '';

        const valFloat = parseFloat(rec.value || 0);
        document.getElementById('edit_receipt_value').value = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valFloat);

        document.getElementById('adminEditTravelReceiptModal').style.display = 'flex';
    }

    function consultarCnpjModalAdmin(inputCnpj, inputName, divInfo) {
        if (!inputCnpj) return;
        const clean = inputCnpj.value.replace(/\D/g, "");
        if (clean.length !== 14) return;

        if (divInfo) {
            divInfo.innerHTML = '<span style="color: #38bdf8; font-size: 11px;">🔍 Consultando Receita Federal...</span>';
        }

        fetch('<?= $this->baseUrl("api/cnpj/consultar") ?>?cnpj=' + clean)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (inputName) inputName.value = data.razao_social;
                    if (divInfo) {
                        divInfo.innerHTML = `<span style="color: #22c55e; font-size: 11px; font-weight: 600;">✔ ${data.razao_social}</span> <span style="color: #94a3b8; font-size: 10px;">(${data.municipio}/${data.uf})</span>`;
                    }
                } else {
                    if (inputName) inputName.value = '';
                    if (divInfo) {
                        divInfo.innerHTML = `<span style="color: #ef4444; font-size: 11px;">⚠️ CNPJ não localizado na Receita Federal.</span>`;
                    }
                    const querManter = confirm("CNPJ (" + clean + ") não foi localizado na base pública da Receita Federal.\n\nDeseja manter este CNPJ e preencher o Nome / Razão Social da empresa manualmente?");
                    if (querManter) {
                        if (inputName) inputName.focus();
                    } else {
                        inputCnpj.value = '';
                        inputCnpj.focus();
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (divInfo) {
                    divInfo.innerHTML = '<span style="color: #ef4444; font-size: 11px;">⚠️ Erro ao consultar Receita Federal.</span>';
                }
            });
    }

    function fecharAdminEditReceiptModal() {
        document.getElementById('adminEditTravelReceiptModal').style.display = 'none';
    }
</script>

<!-- MODAL DE EDIÇÃO DE RECIBO DE COMBUSTÍVEL PELO ADMINISTRADOR -->
<div id="adminEditTravelReceiptModal" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 1px solid var(--accent-teal); border-radius: 16px; max-width: 520px; width: 100%; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #2dd4bf;">✏️ Editar Recibo de Combustível / Posto</h3>
            <button type="button" onclick="fecharAdminEditReceiptModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <form action="<?= $this->baseUrl('admin/financeiro/viagem/recibo/editar') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="edit_receipt_id" name="receipt_id" value="">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">CNPJ do Posto *</label>
                    <input type="text" id="edit_receipt_supplier_cnpj" name="supplier_cnpj" oninput="formatarCnpjCpf(this); if (this.value.replace(/\D/g, '').length === 14) consultarCnpjModalAdmin(this, document.getElementById('edit_receipt_supplier_name'), document.getElementById('edit_receipt_cnpj_info'));" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                    <div id="edit_receipt_cnpj_info" style="margin-top: 4px; font-size: 11px;"></div>
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Data do Recibo *</label>
                    <input type="date" id="edit_receipt_date" name="receipt_date" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 600;">Nome / Razão Social do Posto</label>
                <input type="text" id="edit_receipt_supplier_name" name="supplier_name" placeholder="Ex: Auto Posto Alvorada Ltda" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Valor (R$) *</label>
                    <input type="text" id="edit_receipt_value" name="value" onkeyup="formatarMoeda(this)" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #4ade80; font-size: 14px; font-weight: 700;">
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Categoria SPCE *</label>
                    <select id="edit_receipt_spce_category_id" name="spce_category_id" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 12px;">
                        <?php foreach ($spceCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 16px;">
                <button type="button" class="btn btn-secondary flex-1" onclick="fecharAdminEditReceiptModal()">Cancelar</button>
                <button type="submit" class="btn btn-teal flex-1" style="background: #eab308; color: #0f172a; font-weight: 800;">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>
