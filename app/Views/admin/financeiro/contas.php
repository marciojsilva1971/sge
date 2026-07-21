<div class="dashboard-container">
    
    <!-- Cabeçalho do Módulo de Contas Bancárias -->
    <div class="welcome-banner" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2>🏦 Contas Bancárias & Vinculação de Recursos (SPCE/TSE)</h2>
            <p>Gerenciamento das contas bancárias de campanha e vinculação com FEFC, Fundo Partidário e Outros Recursos.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="btn btn-secondary">
                ⬅️ Voltar ao Financeiro
            </a>
            <button type="button" onclick="openNewAccountModal();" class="btn btn-teal">
                ➕ Nova Conta Bancária
            </button>
        </div>
    </div>

    <!-- KPIs / Resumo por Tipo de Recurso -->
    <div class="kpis-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
        
        <div class="kpi-card">
            <div class="kpi-icon" style="color: var(--success-color, #10b981);">💰</div>
            <div class="kpi-details">
                <div class="kpi-title">Total Geral em Caixa</div>
                <div class="kpi-value" style="color: var(--success-color, #10b981);">R$ <?= number_format($totals['TOTAL_CAIXA'] ?? 0, 2, ',', '.') ?></div>
                <div class="kpi-subtext">Soma de todas as contas ativas</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="color: #3b82f6;">🏛️</div>
            <div class="kpi-details">
                <div class="kpi-title">Saldo FEFC (Público)</div>
                <div class="kpi-value">R$ <?= number_format($totals['FEFC'] ?? 0, 2, ',', '.') ?></div>
                <div class="kpi-subtext">Fundo Eleitoral de Financiamento</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="color: #f59e0b;">⭐</div>
            <div class="kpi-details">
                <div class="kpi-title">Fundo Partidário</div>
                <div class="kpi-value">R$ <?= number_format($totals['FUNDO_PARTIDARIO'] ?? 0, 2, ',', '.') ?></div>
                <div class="kpi-subtext">Recursos do Fundo Partidário</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="color: #a855f7;">👤</div>
            <div class="kpi-details">
                <div class="kpi-title">Outros Recursos (Privado)</div>
                <div class="kpi-value">R$ <?= number_format($totals['OUTROS_RECURSOS'] ?? 0, 2, ',', '.') ?></div>
                <div class="kpi-subtext">Doações PF e Recursos Próprios</div>
            </div>
        </div>

    </div>

    <!-- Tabela de Contas Bancárias Cadastradas -->
    <div class="panel-card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3>Contas Bancárias de Campanha Registradas</h3>
            <span style="font-size: 12px; color: var(--text-secondary, #94a3b8);">Total de <?= count($accounts) ?> conta(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome da Conta / Identificador</th>
                            <th>Banco</th>
                            <th>Agência / Conta</th>
                            <th>Chave PIX</th>
                            <th>Recurso Eleitoral Vinculado</th>
                            <th>Saldo Atual (R$)</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($accounts)): ?>
                            <tr>
                                <td colspan="9" class="text-center" style="padding: 24px; color: #94a3b8;">
                                    Nenhuma conta bancária cadastrada até o momento. Clique em <strong>"➕ Nova Conta Bancária"</strong> para cadastrar.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($accounts as $acc): ?>
                                <tr>
                                    <td>#<?= $acc['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($acc['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($acc['bank_name']) ?></td>
                                    <td>
                                        <code style="background: #0f172a; padding: 3px 6px; border-radius: 4px; color: #38bdf8;">
                                            Ag: <?= htmlspecialchars($acc['agency']) ?> | CC: <?= htmlspecialchars($acc['account_number']) ?>
                                        </code>
                                    </td>
                                    <td>
                                        <?php if (!empty($acc['pix_key'])): ?>
                                            <span style="font-size: 12px; color: #2dd4bf;">🔑 <?= htmlspecialchars($acc['pix_key']) ?></span>
                                        <?php else: ?>
                                            <span style="color: #64748b; font-size: 11px;">Não cadastrada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($acc['fund_type'] === 'FEFC'): ?>
                                            <span class="badge badge-info" style="background: #1e3a8a; color: #93c5fd; border: 1px solid #3b82f6;">🏛️ FEFC (Fundo Eleitoral)</span>
                                        <?php elseif ($acc['fund_type'] === 'FUNDO_PARTIDARIO'): ?>
                                            <span class="badge badge-warning" style="background: #451a03; color: #fde047; border: 1px solid #f59e0b;">⭐ Fundo Partidário</span>
                                        <?php else: ?>
                                            <span class="badge badge-success" style="background: #3b0764; color: #e9d5ff; border: 1px solid #a855f7;">👤 Outros Recursos</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight: 700; color: <?= floatval($acc['balance']) >= 0 ? '#10b981' : '#ef4444' ?>;">
                                        R$ <?= number_format($acc['balance'], 2, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?php if ($acc['status'] === 'ATIVA'): ?>
                                            <span class="badge badge-success">ATIVA</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">ENCERRADA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px;">
                                            <button type="button" 
                                                    onclick='openEditAccountModal(<?= json_encode($acc, JSON_HEX_APOS | JSON_HEX_QUOT) ?>);' 
                                                    class="btn btn-sm btn-secondary" 
                                                    title="Editar Dados da Conta">
                                                ✏️ Editar
                                            </button>

                                            <form action="<?= $this->baseUrl('admin/financeiro/contas/toggle-status') ?>" method="POST" onsubmit="return confirm('Deseja realmente <?= $acc['status'] === 'ATIVA' ? 'ENCERRAR' : 'REATIVAR' ?> esta conta bancária?');" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                <input type="hidden" name="id" value="<?= $acc['id'] ?>">
                                                <button type="submit" class="btn btn-sm <?= $acc['status'] === 'ATIVA' ? 'btn-danger' : 'btn-teal' ?>">
                                                    <?= $acc['status'] === 'ATIVA' ? '🔒 Encerrar' : '🔓 Reativar' ?>
                                                </button>
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
    </div>

</div>

<!-- Modal: Cadastro de Nova Conta Bancária -->
<div id="modalNewAccount" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center;">
    <div class="panel-card" style="width: 100%; max-width: 560px; background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #fff;">➕ Cadastrar Nova Conta Bancária</h3>
            <button type="button" onclick="closeNewAccountModal();" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form action="<?= $this->baseUrl('admin/financeiro/contas') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="name" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Nome / Identificador da Conta</label>
                <input type="text" id="name" name="name" placeholder="Ex: Conta Corrente FEFC Banco do Brasil" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
            </div>

            <div style="display: flex; gap: 14px; margin-bottom: 14px;">
                <div class="form-group flex-1">
                    <label for="bank_name" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Banco / Instituição</label>
                    <input type="text" id="bank_name" name="bank_name" placeholder="Ex: Banco do Brasil (001)" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
                </div>

                <div class="form-group flex-1">
                    <label for="fund_type" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Fonte do Recurso Vinculado</label>
                    <select id="fund_type" name="fund_type" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
                        <option value="OUTROS_RECURSOS">👤 Outros Recursos (Doações PF / Próprios)</option>
                        <option value="FEFC">🏛️ FEFC (Fundo Especial de Campanha)</option>
                        <option value="FUNDO_PARTIDARIO">⭐ Fundo Partidário</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 14px; margin-bottom: 14px;">
                <div class="form-group flex-1">
                    <label for="agency" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Agência Bancária</label>
                    <input type="text" id="agency" name="agency" placeholder="Ex: 1234-5" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
                </div>

                <div class="form-group flex-1">
                    <label for="account_number" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Número da Conta Corrente</label>
                    <input type="text" id="account_number" name="account_number" placeholder="Ex: 98765-4" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
                </div>
            </div>

            <div style="display: flex; gap: 14px; margin-bottom: 20px;">
                <div class="form-group flex-1">
                    <label for="pix_key" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Chave PIX da Conta <span style="font-weight: normal; color: #94a3b8;">(Opcional)</span></label>
                    <input type="text" id="pix_key" name="pix_key" placeholder="Ex: CNPJ da campanha ou Chave Aleatória" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;">
                </div>

                <div class="form-group flex-1">
                    <label for="balance" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Saldo Inicial (R$)</label>
                    <input type="text" id="balance" name="balance" value="R$ 0,00" class="form-control" style="width: 100%; padding: 10px; font-weight: 700; color: #2dd4bf; border-radius: 6px; border: 1px solid #475569; background: #0f172a;" oninput="formatCurrency(this);">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closeNewAccountModal();" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-teal">💾 Cadastrar Conta</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edição de Conta Bancária -->
<div id="modalEditAccount" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center;">
    <div class="panel-card" style="width: 100%; max-width: 560px; background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #fff;">✏️ Editar Conta Bancária</h3>
            <button type="button" onclick="closeEditAccountModal();" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form action="<?= $this->baseUrl('admin/financeiro/contas/update') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="edit_id" name="id" value="">

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="edit_name" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Nome / Identificador da Conta</label>
                <input type="text" id="edit_name" name="name" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
            </div>

            <div style="display: flex; gap: 14px; margin-bottom: 14px;">
                <div class="form-group flex-1">
                    <label for="edit_bank_name" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Banco / Instituição</label>
                    <input type="text" id="edit_bank_name" name="bank_name" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
                </div>

                <div class="form-group flex-1">
                    <label for="edit_fund_type" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Fonte do Recurso Vinculado</label>
                    <select id="edit_fund_type" name="fund_type" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
                        <option value="OUTROS_RECURSOS">👤 Outros Recursos (Doações PF / Próprios)</option>
                        <option value="FEFC">🏛️ FEFC (Fundo Especial de Campanha)</option>
                        <option value="FUNDO_PARTIDARIO">⭐ Fundo Partidário</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 14px; margin-bottom: 14px;">
                <div class="form-group flex-1">
                    <label for="edit_agency" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Agência Bancária</label>
                    <input type="text" id="edit_agency" name="agency" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
                </div>

                <div class="form-group flex-1">
                    <label for="edit_account_number" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Número da Conta Corrente</label>
                    <input type="text" id="edit_account_number" name="account_number" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="edit_pix_key" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Chave PIX da Conta <span style="font-weight: normal; color: #94a3b8;">(Opcional)</span></label>
                <input type="text" id="edit_pix_key" name="pix_key" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closeEditAccountModal();" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-teal">💾 Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewAccountModal() {
    document.getElementById('modalNewAccount').style.display = 'flex';
}
function closeNewAccountModal() {
    document.getElementById('modalNewAccount').style.display = 'none';
}
function openEditAccountModal(acc) {
    document.getElementById('edit_id').value = acc.id;
    document.getElementById('edit_name').value = acc.name;
    document.getElementById('edit_bank_name').value = acc.bank_name;
    document.getElementById('edit_agency').value = acc.agency;
    document.getElementById('edit_account_number').value = acc.account_number;
    document.getElementById('edit_pix_key').value = acc.pix_key || '';
    document.getElementById('edit_fund_type').value = acc.fund_type;

    document.getElementById('modalEditAccount').style.display = 'flex';
}
function closeEditAccountModal() {
    document.getElementById('modalEditAccount').style.display = 'none';
}
function formatCurrency(input) {
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
</script>
