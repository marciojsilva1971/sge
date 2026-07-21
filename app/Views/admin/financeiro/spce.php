<!-- Módulo de Prestação de Contas & Exportação SPCE (TSE) -->
<div class="spce-container" style="padding: 10px;">
    
    <!-- Inclui os subnav tabs -->
    <?php require __DIR__ . '/_nav_tabs.php'; ?>

    <div class="welcome-banner" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); margin-bottom: 25px;">
        <h2 style="color: #38bdf8; margin: 0 0 8px 0; font-size: 24px; display: flex; align-items: center; gap: 10px;">
            <span>📋</span> Módulo de Prestação de Contas & Exportação SPCE (TSE)
        </h2>
        <p style="color: #94a3b8; margin: 0; font-size: 14px;">
            Geração de relatórios contábeis, controle do prazo legal de 72 horas para doações e auditoria preventiva para aprovação sem ressalvas no Tribunal Superior Eleitoral.
        </p>
    </div>

    <!-- Cards de Resumo Financeiro & Alertas de 72h -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 25px;">
        
        <div class="kpi-card" style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="color: #94a3b8; font-size: 13px; font-weight: bold; text-transform: uppercase;">Arrecadação Total</span>
                <span style="font-size: 24px;">💰</span>
            </div>
            <div style="font-size: 24px; font-weight: bold; color: #4ade80;">
                R$ <?= number_format($totalReceitas, 2, ',', '.') ?>
            </div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 5px;">
                Total de doações e recursos registrados
            </div>
        </div>

        <div class="kpi-card" style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="color: #94a3b8; font-size: 13px; font-weight: bold; text-transform: uppercase;">Gastos Acumulados</span>
                <span style="font-size: 24px;">💸</span>
            </div>
            <div style="font-size: 24px; font-weight: bold; color: #f87171;">
                R$ <?= number_format($totalDespesas, 2, ',', '.') ?>
            </div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 5px;">
                Total de despesas com fornecedores
            </div>
        </div>

        <div class="kpi-card" style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="color: #94a3b8; font-size: 13px; font-weight: bold; text-transform: uppercase;">Alertas de 72 Horas</span>
                <span style="font-size: 24px;">⌛</span>
            </div>
            <?php 
                $pending72h = array_filter($revenues, function($r) { return ($r['tse_status'] ?? 'PENDENTE') === 'PENDENTE'; });
                $count72h = count($pending72h);
            ?>
            <div style="font-size: 24px; font-weight: bold; color: <?= $count72h > 0 ? '#facc15' : '#4ade80' ?>;">
                <?= $count72h ?> Doaçõ<?= $count72h === 1 ? 'ão' : 'ões' ?>
            </div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 5px;">
                <?= $count72h > 0 ? 'Pendente de envio ao TSE' : 'Todas as doações reportadas' ?>
            </div>
        </div>

        <div class="kpi-card" style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="color: #94a3b8; font-size: 13px; font-weight: bold; text-transform: uppercase;">Conformidade Legal</span>
                <span style="font-size: 24px;">⚖️</span>
            </div>
            <div style="font-size: 24px; font-weight: bold; color: <?= count($auditIssues) > 0 ? '#fb923c' : '#4ade80' ?>;">
                <?= count($auditIssues) ?> <?= count($auditIssues) === 1 ? 'Alerta' : 'Alertas' ?>
            </div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 5px;">
                <?= count($auditIssues) > 0 ? 'Requer atenção do contador' : 'Nenhuma inconsistência fiscal' ?>
            </div>
        </div>

    </div>

    <!-- Central de Exportação para o Contador -->
    <div style="background: #1e293b; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #f8fafc; margin: 0 0 15px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
            <span>📥</span> Central de Exportação para Contabilidade Eleitoral (SPCE)
        </h3>
        <p style="color: #94a3b8; font-size: 13px; margin-bottom: 20px;">
            Faça o download das planilhas em formato formatado para importação no SPCE ou gere o dossiê completo de prestação de contas em formato PDF para impressão.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
            
            <a href="<?= $this->baseUrl('admin/financeiro/spce/export-csv?type=receitas') ?>" style="text-decoration: none;">
                <div style="background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #334155; text-align: center; transition: all 0.2s ease;">
                    <div style="font-size: 28px; margin-bottom: 8px;">📊</div>
                    <div style="color: #38bdf8; font-weight: bold; font-size: 14px;">CSV de Receitas</div>
                    <div style="color: #64748b; font-size: 11px; margin-top: 4px;">Doações e Recursos Privados</div>
                </div>
            </a>

            <a href="<?= $this->baseUrl('admin/financeiro/spce/export-csv?type=despesas') ?>" style="text-decoration: none;">
                <div style="background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #334155; text-align: center; transition: all 0.2s ease;">
                    <div style="font-size: 28px; margin-bottom: 8px;">💸</div>
                    <div style="color: #38bdf8; font-weight: bold; font-size: 14px;">CSV de Despesas</div>
                    <div style="color: #64748b; font-size: 11px; margin-top: 4px;">Gastos e Pagamentos a Fornecedores</div>
                </div>
            </a>

            <a href="<?= $this->baseUrl('admin/financeiro/spce/export-csv?type=contratos') ?>" style="text-decoration: none;">
                <div style="background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #334155; text-align: center; transition: all 0.2s ease;">
                    <div style="font-size: 28px; margin-bottom: 8px;">📄</div>
                    <div style="color: #38bdf8; font-weight: bold; font-size: 14px;">CSV de Contratos</div>
                    <div style="color: #64748b; font-size: 11px; margin-top: 4px;">Instrumentos Contratuais por Prazo</div>
                </div>
            </a>

            <a href="<?= $this->baseUrl('admin/financeiro/spce/dossier') ?>" target="_blank" style="text-decoration: none;">
                <div style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%); padding: 15px; border-radius: 8px; border: 1px solid #3b82f6; text-align: center; transition: all 0.2s ease;">
                    <div style="font-size: 28px; margin-bottom: 8px;">🖨️</div>
                    <div style="color: #ffffff; font-weight: bold; font-size: 14px;">Dossiê Completo (PDF)</div>
                    <div style="color: #93c5fd; font-size: 11px; margin-top: 4px;">Relatório Consolidado para Impressão</div>
                </div>
            </a>

        </div>
    </div>

    <!-- Validação Preventiva de Inconsistências (Checklist de Conformidade) -->
    <?php if (!empty($auditIssues)): ?>
        <div style="background: #1e293b; border-radius: 12px; border: 1px solid #f97316; padding: 20px; margin-bottom: 25px;">
            <h3 style="color: #fb923c; margin: 0 0 15px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                <span>⚠️</span> Validação de Conformidade Eleitoral (Inconsistências Identificadas)
            </h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php foreach ($auditIssues as $issue): ?>
                    <div style="background: #0f172a; padding: 12px 15px; border-radius: 8px; border-left: 4px solid <?= $issue['severity'] === 'DANGER' ? '#ef4444' : '#eab308' ?>; color: #cbd5e1; font-size: 13px;">
                        <strong style="color: <?= $issue['severity'] === 'DANGER' ? '#f87171' : '#fde047' ?>;">
                            <?= $issue['severity'] === 'DANGER' ? '[BLOQUEANTE SPCE]' : '[ALERTA DE AUDITORIA]' ?>
                        </strong> 
                        <?= htmlspecialchars($issue['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tabela de Controle de Doações 72h -->
    <div style="background: #1e293b; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #f8fafc; margin: 0 0 15px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
            <span>⌛</span> Painel de Controle de Doações - Prazo Legal de 72 Horas (TSE)
        </h3>
        <p style="color: #94a3b8; font-size: 12px; margin-bottom: 15px;">
            Todas as doações financeiras recebidas devem ser informadas à Justiça Eleitoral no prazo máximo de 72 horas.
        </p>

        <div class="table-responsive">
            <table class="table table-striped" style="width: 100%; border-collapse: collapse; color: #cbd5e1; font-size: 13px;">
                <thead>
                    <tr style="background: #0f172a; text-align: left;">
                        <th style="padding: 10px;">Data Rec.</th>
                        <th style="padding: 10px;">Doador</th>
                        <th style="padding: 10px;">CPF Doador</th>
                        <th style="padding: 10px;">Valor (R$)</th>
                        <th style="padding: 10px;">Conta Crédito</th>
                        <th style="padding: 10px;">Status Envio 72h</th>
                        <th style="padding: 10px; text-align: center;">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($revenues)): ?>
                        <tr>
                            <td colspan="7" style="padding: 15px; text-align: center; color: #64748b;">Nenhuma receita ou doação cadastrada no sistema.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($revenues as $rev): ?>
                            <tr style="border-bottom: 1px solid #334155;">
                                <td style="padding: 10px;"><?= date('d/m/Y', strtotime($rev['date_received'])) ?></td>
                                <td style="padding: 10px; font-weight: bold; color: #f8fafc;"><?= htmlspecialchars($rev['donor_name']) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($rev['donor_cpf']) ?></td>
                                <td style="padding: 10px; color: #4ade80; font-weight: bold;">R$ <?= number_format($rev['value'], 2, ',', '.') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($rev['bank_name'] ?? 'N/A') ?></td>
                                <td style="padding: 10px;">
                                    <?php if (($rev['tse_status'] ?? 'PENDENTE') === 'ENVIADO_72H'): ?>
                                        <span style="background: #166534; color: #86efac; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                                            ✓ Enviado ao TSE em <?= date('d/m/Y H:i', strtotime($rev['tse_reported_at'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #854d0e; color: #fef08a; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                                            ⌛ Envio Pendente (72h)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <?php if (($rev['tse_status'] ?? 'PENDENTE') === 'PENDENTE'): ?>
                                        <form method="POST" action="<?= $this->baseUrl('admin/financeiro/spce/mark-72h') ?>" style="margin:0;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <input type="hidden" name="revenue_id" value="<?= $rev['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-teal" style="font-size: 11px; padding: 4px 8px;">
                                                Marcar como Enviado ao TSE
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #64748b; font-size: 11px;">Concluído</span>
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
