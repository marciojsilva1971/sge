<div class="page-header">
    <div>
        <h2>Painel Financeiro da Campanha</h2>
        <p class="subtitle">Visão geral do fluxo de caixa, receitas e limite de gastos eleitorais.</p>
    </div>
    <div>
        <a href="<?= $this->baseUrl('admin/financeiro/despesas') ?>" class="btn btn-secondary" style="margin-right: 10px;">
            💸 Lançar Despesa
        </a>
        <a href="<?= $this->baseUrl('admin/financeiro/fornecedores') ?>" class="btn btn-teal">
            👥 Fornecedores
        </a>
    </div>
</div>

<?php include __DIR__ . '/_nav_tabs.php'; ?>

<!-- KPIs Grid -->
<div class="kpis-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="color: var(--success-color);">💰</div>
        <div class="kpi-details">
            <div class="kpi-title">Caixa Total</div>
            <div class="kpi-value" style="color: var(--success-color);">R$ <?= number_format($totalBalance, 2, ',', '.') ?></div>
            <div class="kpi-subtext">Soma de todas as contas ativas</div>
        </div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-icon" style="color: var(--info-color);">🏛️</div>
        <div class="kpi-details">
            <div class="kpi-title">Saldo FEFC</div>
            <div class="kpi-value">R$ <?= number_format($fefcBalance, 2, ',', '.') ?></div>
            <div class="kpi-subtext">Fundo Eleitoral (Público)</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="color: var(--warning-color);">⭐</div>
        <div class="kpi-details">
            <div class="kpi-title">Saldo Partidário</div>
            <div class="kpi-value">R$ <?= number_format($partidarioBalance, 2, ',', '.') ?></div>
            <div class="kpi-subtext">Fundo Partidário</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="color: #a78bfa;">👤</div>
        <div class="kpi-details">
            <div class="kpi-title">Outros Recursos</div>
            <div class="kpi-value">R$ <?= number_format($outrosBalance, 2, ',', '.') ?></div>
            <div class="kpi-subtext">Doações PF e Recursos Próprios</div>
        </div>
    </div>
</div>

<!-- Progresso do Limite de Gastos -->
<div class="panel-card" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <span style="font-weight: 600; font-size: 14px;">Limite Máximo de Gastos da Campanha (TSE)</span>
        <span style="font-weight: 700; color: var(--accent-teal-hover); font-size: 15px;">
            R$ <?= number_format($totalSpentCombined, 2, ',', '.') ?> / R$ <?= number_format($spendingLimit, 2, ',', '.') ?> (<?= number_format($limitPercentage, 1) ?>%)
        </span>
    </div>
    <div class="progress-bar-container" style="height: 12px; border-radius: 6px;">
        <div class="progress-bar" style="width: <?= $limitPercentage ?>%; border-radius: 6px; background: linear-gradient(90deg, var(--accent-indigo) 0%, var(--accent-teal) 100%);"></div>
    </div>
    <div style="margin-top: 8px; font-size: 11px; color: var(--text-secondary);">
        * Apenas despesas aprovadas ou pagas entram no cômputo do limite.
    </div>
</div>

<div class="dashboard-sections">
    
    <!-- Lançamento de Receita -->
    <div class="panel-card flex-1">
        <div class="card-header">
            <h3>Lançar Nova Receita / Doação</h3>
        </div>
        <form action="<?= $this->baseUrl('admin/financeiro') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="form-group">
                <label for="description">Descrição do Lançamento</label>
                <input type="text" id="description" name="description" placeholder="Ex: Doação Eleitoral de Pessoa Física" required>
            </div>

            <div style="display: flex; gap: 16px;">
                <div class="form-group flex-1">
                    <label for="value">Valor (R$)</label>
                    <input type="text" id="value" name="value" placeholder="R$ 0,00" required style="font-size: 16px; font-weight: 600; color: var(--accent-teal-hover);" oninput="formatarMoeda(this);">
                </div>
                <div class="form-group flex-1">
                    <label for="date_received">Data de Recebimento</label>
                    <input type="date" id="date_received" name="date_received" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div style="display: flex; gap: 16px;">
                <div class="form-group flex-1">
                    <label for="bank_account_id">Conta Bancária de Destino</label>
                    <select id="bank_account_id" name="bank_account_id" required>
                        <option value="">Selecione a conta...</option>
                        <?php foreach ($bankAccounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?> (R$ <?= number_format($acc['balance'], 2, ',', '.') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group flex-1">
                    <label for="spce_category_id">Categoria SPCE/TSE</label>
                    <select id="spce_category_id" name="spce_category_id" required>
                        <option value="">Selecione a categoria...</option>
                        <?php foreach ($receitaCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 16px;">
                <div class="form-group flex-1">
                    <label for="donor_name">Nome do Doador</label>
                    <input type="text" id="donor_name" name="donor_name" placeholder="Nome completo" required>
                </div>
                <div class="form-group flex-1">
                    <label for="donor_cpf">CPF do Doador</label>
                    <input type="text" id="donor_cpf" name="donor_cpf" placeholder="000.000.000-00" required>
                </div>
            </div>

            <button type="submit" class="btn btn-teal btn-block" style="margin-top: 10px;">
                📥 Confirmar e Lançar Receita
            </button>
        </form>
    </div>

    <!-- Receitas Recentes -->
    <div class="panel-card flex-1">
        <div class="card-header">
            <h3>Doações & Receitas Registradas</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Doador / Descrição</th>
                        <th>Conta / Fonte</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentRevenues)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary);">Nenhuma doação cadastrada ainda.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentRevenues as $rev): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($rev['date_received'])) ?></td>
                                <td>
                                    <div class="user-log-name"><?= htmlspecialchars($rev['donor_name']) ?></div>
                                    <span style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($rev['description']) ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($rev['bank_name']) ?></span>
                                </td>
                                <td style="color: var(--success-color); font-weight: 600;">
                                    + R$ <?= number_format($rev['value'], 2, ',', '.') ?>
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
    // Máscara básica para CPF
    document.getElementById('donor_cpf').addEventListener('input', function (e) {
        var value = e.target.value.replace(/\D/g, "");
        if (value.length > 11) value = value.slice(0, 11);
        if (value.length > 9) {
            value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4");
        } else if (value.length > 6) {
            value = value.replace(/^(\d{3})(\d{3})(\d{1,3})$/, "$1.$2.$3");
        } else if (value.length > 3) {
            value = value.replace(/^(\d{3})(\d{1,3})$/, "$1.$2");
        }
        e.target.value = value;
    });

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
</script>
