<!-- Módulo de Carga Inicial & Conciliação Bancária com Extratos em PDF -->
<div class="conciliacao-container" style="padding: 10px;">
    
    <!-- Subnav Tabs -->
    <?php require __DIR__ . '/_nav_tabs.php'; ?>

    <div class="welcome-banner" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); margin-bottom: 25px;">
        <h2 style="color: #38bdf8; margin: 0 0 8px 0; font-size: 24px; display: flex; align-items: center; gap: 10px;">
            <span>🏦</span> Carga Inicial & Conciliação Bancária
        </h2>
        <p style="color: #94a3b8; margin: 0; font-size: 14px;">
            Realize a carga dos valores disponibilizados para a campanha e ajustes de saldo para manter o sistema 100% conciliado com os extratos bancários oficiais. Cada alteração exige obrigatoriamente o anexo do extrato em PDF.
        </p>
    </div>

    <!-- Cards com Saldos Atuais das Contas -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 25px;">
        <?php foreach ($accounts as $acc): ?>
            <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="color: #38bdf8; font-size: 14px; font-weight: bold;"><?= htmlspecialchars($acc['name']) ?></span>
                        <span style="background: #0f172a; color: #94a3b8; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; border: 1px solid #334155;">
                            <?= htmlspecialchars($acc['fund_type'] ?? 'FUNDO') ?>
                        </span>
                    </div>
                    <div style="font-size: 12px; color: #94a3b8; margin-bottom: 8px;">
                        Ag: <strong><?= htmlspecialchars($acc['agency']) ?></strong> | C/C: <strong><?= htmlspecialchars($acc['account_number']) ?></strong> (<?= htmlspecialchars($acc['bank_name']) ?>)
                    </div>
                    <div style="font-size: 22px; font-weight: 800; color: <?= floatval($acc['balance']) >= 0 ? '#4ade80' : '#f87171' ?>; line-height: 1.2;">
                        R$ <?= number_format($acc['balance'], 2, ',', '.') ?>
                    </div>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 10px; border-top: 1px dashed #334155; padding-top: 8px;">
                    Última atualização: <?= date('d/m/Y H:i', strtotime($acc['updated_at'] ?? $acc['created_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Formulário de Carga e Ajuste de Saldo Bancário -->
    <div style="background: #1e293b; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #f8fafc; margin: 0 0 15px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
            <span>➕</span> Lançar Carga Inicial / Ajuste de Conciliação de Saldo
        </h3>
        <p style="color: #94a3b8; font-size: 13px; margin-bottom: 20px;">
            Selecione a conta, informe o tipo de operação, o valor e anexe o <strong>Extrato Bancário Oficial em formato PDF</strong> correspondente.
        </p>

        <form method="POST" action="<?= $this->baseUrl('admin/financeiro/conciliacao/ajustar') ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px;">
                
                <div>
                    <label style="display: block; color: #cbd5e1; font-size: 12px; font-weight: bold; margin-bottom: 5px;">Conta Bancária *</label>
                    <select name="bank_account_id" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px; border-radius: 6px; font-size: 13px;">
                        <option value="">-- Selecione a Conta --</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>">
                                <?= htmlspecialchars($acc['name']) ?> (Saldo atual: R$ <?= number_format($acc['balance'], 2, ',', '.') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; color: #cbd5e1; font-size: 12px; font-weight: bold; margin-bottom: 5px;">Tipo de Operação *</label>
                    <select name="adjustment_type" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px; border-radius: 6px; font-size: 13px;">
                        <option value="CARGA_INICIAL">🏁 Carga Inicial da Campanha (Define Saldo)</option>
                        <option value="CONCILIACAO">⚖️ Ajuste de Conciliação Extrato (Define Saldo Final)</option>
                        <option value="AJUSTE_CREDITO">➕ Ajuste a Crédito (Soma ao Saldo)</option>
                        <option value="AJUSTE_DEBITO">➖ Ajuste a Débito (Subtrai do Saldo)</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; color: #cbd5e1; font-size: 12px; font-weight: bold; margin-bottom: 5px;">Valor da Operação (R$) *</label>
                    <input type="text" name="adjustment_amount" required placeholder="0,00" onkeyup="formatCurrencyBrl(this)" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #4ade80; font-weight: bold; padding: 10px; border-radius: 6px; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; color: #facc15; font-size: 12px; font-weight: bold; margin-bottom: 5px;">Extrato Bancário (PDF Obrigatório) *</label>
                    <input type="file" name="statement_pdf" accept="application/pdf" required style="width: 100%; background: #0f172a; border: 1px solid #eab308; color: #f8fafc; padding: 8px; border-radius: 6px; font-size: 12px;">
                </div>

            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #cbd5e1; font-size: 12px; font-weight: bold; margin-bottom: 5px;">Motivo / Justificativa do Ajuste *</label>
                <textarea name="reason" rows="2" required placeholder="Descreva detalhadamente o motivo da carga ou conciliação conforme o extrato anexado (ex: Carga do Fundo Eleitoral conforme extrato do Banco do Brasil do dia 15/08)..." style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px; border-radius: 6px; font-size: 13px;"></textarea>
            </div>

            <div style="text-align: right;">
                <button type="submit" class="btn btn-teal" style="font-weight: bold; padding: 10px 24px; font-size: 13px; border-radius: 6px;">
                    💾 Registrar Carga / Ajuste com Extrato PDF
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela de Histórico de Cargas e Conciliações Registradas -->
    <div style="background: #1e293b; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); padding: 20px;">
        <h3 style="color: #f8fafc; margin: 0 0 15px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
            <span>📜</span> Histórico de Cargas e Conciliações Registradas (Extratos Arquivados)
        </h3>

        <div class="table-responsive">
            <table class="table table-striped" style="width: 100%; border-collapse: collapse; color: #cbd5e1; font-size: 13px;">
                <thead>
                    <tr style="background: #0f172a; text-align: left;">
                        <th style="padding: 10px;">Data/Hora</th>
                        <th style="padding: 10px;">Conta Bancária</th>
                        <th style="padding: 10px;">Operação</th>
                        <th style="padding: 10px;">Saldo Anterior</th>
                        <th style="padding: 10px;">Valor Operação</th>
                        <th style="padding: 10px;">Novo Saldo</th>
                        <th style="padding: 10px;">Extrato Bancário</th>
                        <th style="padding: 10px;">Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($adjustments)): ?>
                        <tr>
                            <td colspan="8" style="padding: 15px; text-align: center; color: #64748b;">Nenhuma carga inicial ou conciliação registrada até o momento.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($adjustments as $adj): ?>
                            <tr style="border-bottom: 1px solid #334155;">
                                <td style="padding: 10px;"><?= date('d/m/Y H:i', strtotime($adj['created_at'])) ?></td>
                                <td style="padding: 10px; font-weight: bold; color: #f8fafc;">
                                    <?= htmlspecialchars($adj['bank_name']) ?>
                                    <div style="font-size: 11px; color: #64748b;">Ag: <?= htmlspecialchars($adj['agency']) ?> | C/C: <?= htmlspecialchars($adj['account_number']) ?></div>
                                </td>
                                <td style="padding: 10px;">
                                    <?php
                                        $badges = [
                                            'CARGA_INICIAL' => ['bg' => '#1e3a8a', 'fg' => '#93c5fd', 'label' => '🏁 Carga Inicial'],
                                            'CONCILIACAO'   => ['bg' => '#14532d', 'fg' => '#86efac', 'label' => '⚖️ Conciliação'],
                                            'AJUSTE_CREDITO'=> ['bg' => '#064e3b', 'fg' => '#6ee7b7', 'label' => '➕ Crédito'],
                                            'AJUSTE_DEBITO' => ['bg' => '#7f1d1d', 'fg' => '#fca5a5', 'label' => '➖ Débito']
                                        ];
                                        $b = $badges[$adj['adjustment_type']] ?? ['bg' => '#334155', 'fg' => '#cbd5e1', 'label' => $adj['adjustment_type']];
                                    ?>
                                    <span style="background: <?= $b['bg'] ?>; color: <?= $b['fg'] ?>; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                                        <?= $b['label'] ?>
                                    </span>
                                </td>
                                <td style="padding: 10px;">R$ <?= number_format($adj['old_balance'], 2, ',', '.') ?></td>
                                <td style="padding: 10px; font-weight: bold; color: #38bdf8;">R$ <?= number_format($adj['adjustment_amount'], 2, ',', '.') ?></td>
                                <td style="padding: 10px; font-weight: bold; color: #4ade80;">R$ <?= number_format($adj['new_balance'], 2, ',', '.') ?></td>
                                <td style="padding: 10px;">
                                    <a href="<?= $this->baseUrl('admin/financeiro/conciliacao/download?id=' . $adj['id']) ?>" target="_blank" class="btn btn-sm btn-secondary" style="font-size: 11px; padding: 4px 8px; text-decoration: none; border-color: #3b82f6; color: #60a5fa;">
                                        📄 Extrato PDF
                                    </a>
                                </td>
                                <td style="padding: 10px; font-size: 12px;">
                                    <?= htmlspecialchars($adj['created_by_name']) ?>
                                    <div style="font-size: 10px; color: #64748b;" title="<?= htmlspecialchars($adj['reason']) ?>">
                                        💬 <?= htmlspecialchars(mb_strimwidth($adj['reason'], 0, 30, '...')) ?>
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

<script>
function formatCurrencyBrl(input) {
    let value = input.value.replace(/\D/g, '');
    value = (value / 100).toFixed(2) + '';
    value = value.replace('.', ',');
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = value;
}
</script>
