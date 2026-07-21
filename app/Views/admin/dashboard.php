<!-- Dashboard Principal do SGE -->
<div class="dashboard-container">
    
    <!-- Cabeçalho de Boas-vindas -->
    <div class="welcome-banner">
        <h2>Olá, <?= htmlspecialchars($user['name']) ?>!</h2>
        <p>Bem-vindo ao painel do candidato. Aqui você acompanha a prestação de contas e a auditoria jurídica em tempo real.</p>
    </div>

    <!-- KPIs Row (Cards) -->
    <div class="kpis-grid">
        <!-- Card 1: Saldo de Caixa -->
        <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="kpi-card">
            <div class="kpi-icon">💰</div>
            <div class="kpi-details">
                <div class="kpi-title">Recursos Disponíveis</div>
                <div class="kpi-value">R$ <?= number_format($kpis['caixa_total'], 2, ',', '.') ?></div>
                <div class="kpi-subtext">
                    FEFC: R$ <?= number_format($kpis['fefc'], 2, ',', '.') ?><br>
                    FP: R$ <?= number_format($kpis['fundo_part'], 2, ',', '.') ?><br>
                    Outros: R$ <?= number_format($kpis['outros'], 2, ',', '.') ?>
                </div>
            </div>
        </a>

        <!-- Card 2: Limite de Gastos TSE -->
        <div class="kpi-card" style="position: relative;">
            <a href="<?= $this->baseUrl('admin/financeiro/despesas') ?>" style="text-decoration: none; color: inherit; display: block; flex: 1;">
                <div class="kpi-icon">📊</div>
                <div class="kpi-details">
                    <div class="kpi-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Limite de Gastos da Campanha</span>
                    </div>
                    <div style="font-size: 11px; color: var(--accent-teal-hover); font-weight: 600; margin-bottom: 2px;">
                        🎯 <?= htmlspecialchars($kpis['electoral_role'] ?? 'Deputado Federal') ?> (<?= htmlspecialchars($kpis['uf'] ?? 'DF') ?>)
                    </div>
                    <div class="kpi-value">R$ <?= number_format($kpis['limite_gastos'], 2, ',', '.') ?></div>
                    <div class="kpi-subtext">Consumido: R$ <?= number_format($kpis['gasto_atual'], 2, ',', '.') ?> (<?= $kpis['gasto_percent'] ?>%)</div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?= $kpis['gasto_percent'] ?>%;"></div>
                    </div>
                </div>
            </a>
            <button type="button" onclick="openConfigModal();" class="btn btn-sm btn-secondary" style="position: absolute; top: 12px; right: 12px; font-size: 11px; padding: 4px 8px; z-index: 10;" title="Ajustar Cargo Eleitoral e Limite TSE">
                ⚙️ Ajustar
            </button>
        </div>

        <!-- Card 3: Pendências Financeiras -->
        <a href="<?= $this->baseUrl('admin/financeiro/fila') ?>" class="kpi-card">
            <div class="kpi-icon">⌛</div>
            <div class="kpi-details">
                <div class="kpi-title">Pendentes de Homologação</div>
                <div class="kpi-value"><?= $kpis['pendente_aprovacao'] ?></div>
                <div class="kpi-subtext">Comprovantes e relatórios de campo sob análise</div>
            </div>
        </a>

        <!-- Card 4: Usuários SGE -->
        <a href="<?= $this->baseUrl('admin/users') ?>" class="kpi-card">
            <div class="kpi-icon">👥</div>
            <div class="kpi-details">
                <div class="kpi-title">Colaboradores do Sistema</div>
                <div class="kpi-value"><?= $stats['ATIVO'] + $stats['PENDENTE'] ?></div>
                <div class="kpi-subtext">
                    Ativos: <span class="badge badge-success"><?= $stats['ATIVO'] ?></span><br>
                    Convites Pendentes: <span class="badge badge-warning"><?= $stats['PENDENTE'] ?></span>
                </div>
            </div>
        </a>
    </div>

    <!-- Seção de Duas Colunas: Gráfico/Metas e Logs de Auditoria -->
    <div class="dashboard-sections">
        
        <!-- Bloco de Resumo de SPCE -->
        <div class="panel-card flex-1">
            <div class="card-header">
                <h3>Rateio de Despesas por Rubrica (SPCE/TSE)</h3>
            </div>
            <div class="card-body">
                <div class="chart-mockup-container">
                    <!-- Gráfico circular em puro CSS (radial-gradient) -->
                    <div class="pie-chart-mock"></div>
                    <div class="chart-legend">
                        <div class="legend-item"><span class="color-dot color-teal"></span> Militância de Campo (45%)</div>
                        <div class="legend-item"><span class="color-dot color-indigo"></span> Combustível & Viagens (30%)</div>
                        <div class="legend-item"><span class="color-dot color-orange"></span> Comitê & Materiais (25%)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloco de Auditoria e Logs -->
        <div class="panel-card flex-1">
            <div class="card-header">
                <h3>Trilha de Auditoria (Últimas Ações)</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-logs">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Colaborador</th>
                                <th>Ação Realizada</th>
                                <th>Tabela</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentLogs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Nenhuma ação registrada na auditoria até o momento.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentLogs as $log): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                        <td>
                                            <span class="user-log-name">
                                                <?= htmlspecialchars($log['user_name'] ?? 'Visitante/Convidado') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="action-badge badge-info">
                                                <?= htmlspecialchars($log['action']) ?>
                                            </span>
                                        </td>
                                        <td><code><?= htmlspecialchars($log['table_name']) ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal de Configuração de Cargo e Limite Eleitoral -->
<div id="modalConfigCampanha" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center;">
    <div class="panel-card" style="width: 100%; max-width: 520px; background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #fff;">⚙️ Configurar Cargo & Limite de Gastos (TSE)</h3>
            <button type="button" onclick="closeConfigModal();" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form action="<?= $this->baseUrl('admin/campanha/configuracoes') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="candidate_name" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Nome do Candidato / Campanha</label>
                <input type="text" id="candidate_name" name="candidate_name" value="<?= htmlspecialchars($campaignSettings['candidate_name'] ?? 'Candidato SGE') ?>" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
            </div>

            <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                <div class="form-group flex-1" style="flex: 2;">
                    <label for="electoral_role" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">Cargo Eleitoral Pretendido</label>
                    <select id="electoral_role" name="electoral_role" onchange="selecionarCargoPredefinido(this);" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
                        <option value="Deputado Federal" <?= ($campaignSettings['electoral_role'] ?? '') === 'Deputado Federal' ? 'selected' : '' ?>>Deputado Federal</option>
                        <option value="Deputado Estadual" <?= ($campaignSettings['electoral_role'] ?? '') === 'Deputado Estadual' ? 'selected' : '' ?>>Deputado Estadual</option>
                        <option value="Deputado Distrital" <?= ($campaignSettings['electoral_role'] ?? '') === 'Deputado Distrital' ? 'selected' : '' ?>>Deputado Distrital</option>
                        <option value="Senador" <?= ($campaignSettings['electoral_role'] ?? '') === 'Senador' ? 'selected' : '' ?>>Senador</option>
                        <option value="Governador" <?= ($campaignSettings['electoral_role'] ?? '') === 'Governador' ? 'selected' : '' ?>>Governador</option>
                        <option value="Presidente da República" <?= ($campaignSettings['electoral_role'] ?? '') === 'Presidente da República' ? 'selected' : '' ?>>Presidente da República</option>
                    </select>
                </div>

                <div class="form-group flex-1" style="flex: 1;">
                    <label for="uf" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">UF / Estado</label>
                    <select id="uf" name="uf" class="form-control" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff;" required>
                        <?php 
                        $ufs = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];
                        $currentUf = $campaignSettings['uf'] ?? 'DF';
                        foreach ($ufs as $state):
                        ?>
                            <option value="<?= $state ?>" <?= $currentUf === $state ? 'selected' : '' ?>><?= $state ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="spending_limit" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #cbd5e1;">
                    Limite Máximo de Gastos TSE (R$) <span style="font-weight: 400; color: #94a3b8;">(Digitável)</span>
                </label>
                <input type="text" id="spending_limit" name="spending_limit" value="R$ <?= number_format($campaignSettings['spending_limit'] ?? 3168878.60, 2, ',', '.') ?>" class="form-control" style="width: 100%; padding: 12px; font-size: 16px; font-weight: 700; color: #2dd4bf; border-radius: 6px; border: 1px solid #475569; background: #0f172a;" oninput="formatarMoedaConfig(this);" required>
                <small style="display: block; margin-top: 4px; font-size: 11px; color: #94a3b8;">
                    * Digite o valor estipulado pela Resolução/Portaria do TSE para o cargo e Estado da sua candidatura.
                </small>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closeConfigModal();" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-teal">💾 Salvar Configurações</button>
            </div>
        </form>
    </div>
</div>

<script>
function openConfigModal() {
    document.getElementById('modalConfigCampanha').style.display = 'flex';
}
function closeConfigModal() {
    document.getElementById('modalConfigCampanha').style.display = 'none';
}
function formatarMoedaConfig(input) {
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
function selecionarCargoPredefinido(select) {
    let limitInput = document.getElementById('spending_limit');
    let role = select.value;
    let sugestoes = {
        'Deputado Federal': 'R$ 3.168.878,60',
        'Deputado Estadual': 'R$ 1.270.629,01',
        'Deputado Distrital': 'R$ 1.270.629,01',
        'Senador': 'R$ 5.600.000,00',
        'Governador': 'R$ 7.100.000,00',
        'Presidente da República': 'R$ 88.944.030,80'
    };
    if (sugestoes[role]) {
        limitInput.value = sugestoes[role];
    }
}
</script>
