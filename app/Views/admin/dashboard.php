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
        <a href="<?= $this->baseUrl('admin/financeiro/despesas') ?>" class="kpi-card">
            <div class="kpi-icon">📊</div>
            <div class="kpi-details">
                <div class="kpi-title">Limite de Gastos da Campanha</div>
                <div class="kpi-value">R$ <?= number_format($kpis['limite_gastos'], 2, ',', '.') ?></div>
                <div class="kpi-subtext">Consumido: R$ <?= number_format($kpis['gasto_atual'], 2, ',', '.') ?> (<?= $kpis['gasto_percent'] ?>%)</div>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?= $kpis['gasto_percent'] ?>%;"></div>
                </div>
            </div>
        </a>

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
