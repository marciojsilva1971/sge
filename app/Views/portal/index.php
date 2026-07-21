<div class="welcome-banner" style="text-align: center; margin-bottom: 24px;">
    <h2 style="font-size: 22px;">Olá, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>!</h2>
    <p class="subtitle" style="font-size: 13px;">Bem-vindo ao seu portal de campo. Selecione uma ação abaixo para iniciar.</p>
</div>

<!-- Grid de Ações Rápidas -->
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 24px;">
    
    <a href="<?= $this->baseUrl('portal/viagem') ?>" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px 8px; background: rgba(30, 41, 59, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; text-decoration: none; color: #fff; text-align: center; transition: var(--transition-fast);" onmouseover="this.style.borderColor='var(--accent-indigo)'" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.08)'">
        <span style="font-size: 26px; margin-bottom: 6px;">🚗</span>
        <span style="font-size: 13px; font-weight: 600; display: block;">Viagens</span>
        <span style="font-size: 10px; color: var(--text-secondary); margin-top: 2px;">Reembolso</span>
    </a>

    <a href="<?= $this->baseUrl('portal/militancia') ?>" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px 8px; background: rgba(30, 41, 59, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; text-decoration: none; color: #fff; text-align: center; transition: var(--transition-fast);" onmouseover="this.style.borderColor='var(--accent-teal)'" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.08)'">
        <span style="font-size: 26px; margin-bottom: 6px;">📢</span>
        <span style="font-size: 13px; font-weight: 600; display: block;">Militância</span>
        <span style="font-size: 10px; color: var(--text-secondary); margin-top: 2px;">Panfletagem</span>
    </a>

    <a href="<?= $this->baseUrl('portal/outros') ?>" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px 8px; background: rgba(30, 41, 59, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; text-decoration: none; color: #fff; text-align: center; transition: var(--transition-fast);" onmouseover="this.style.borderColor='#38bdf8'" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.08)'">
        <span style="font-size: 26px; margin-bottom: 6px;">📦</span>
        <span style="font-size: 13px; font-weight: 600; display: block;">Outros Gastos</span>
        <span style="font-size: 10px; color: var(--text-secondary); margin-top: 2px;">Lançamento Direto</span>
    </a>

    <a href="<?= $this->baseUrl('portal/despesas') ?>" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px 8px; background: rgba(30, 41, 59, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; text-decoration: none; color: #fff; text-align: center; transition: var(--transition-fast);" onmouseover="this.style.borderColor='var(--warning-color)'" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.08)'">
        <span style="font-size: 26px; margin-bottom: 6px;">💸</span>
        <span style="font-size: 13px; font-weight: 600; display: block;">Meus Gastos</span>
        <span style="font-size: 10px; color: var(--text-secondary); margin-top: 2px;">Acompanhar Status</span>
    </a>

</div>

<!-- Atividades de Militância Recentes -->
<div class="panel-card" style="padding: 16px; margin-bottom: 20px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
        <h3 style="font-size: 14px; font-weight: 600;">Suas Atividades de Panfletagem</h3>
    </div>
    
    <?php if (empty($recentMilitancy)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhuma atividade registrada.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($recentMilitancy as $act): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: rgba(15, 23, 42, 0.4); border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.04);">
                    <div>
                        <span style="font-size: 12px; font-weight: 600; display: block;"><?= htmlspecialchars($act['description']) ?></span>
                        <span style="font-size: 10px; color: var(--text-secondary);"><?= date('d/m/Y', strtotime($act['activity_date'])) ?></span>
                    </div>
                    <div>
                        <?php if ($act['status'] === 'APROVADO'): ?>
                            <span class="badge badge-success" style="font-size: 9px; padding: 1px 4px;">Homologada</span>
                        <?php elseif ($act['status'] === 'REJEITADO'): ?>
                            <span class="badge badge-danger" style="font-size: 9px; padding: 1px 4px;">Recusada</span>
                        <?php else: ?>
                            <span class="badge badge-warning" style="font-size: 9px; padding: 1px 4px;">Pendente</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Viagens Recentes -->
<div class="panel-card" style="padding: 16px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
        <h3 style="font-size: 14px; font-weight: 600;">Seus Relatórios de Viagem</h3>
    </div>

    <?php if (empty($recentTravels)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhuma viagem registrada.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($recentTravels as $tr): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: rgba(15, 23, 42, 0.4); border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.04);">
                    <div>
                        <span style="font-size: 12px; font-weight: 600; display: block;"><?= htmlspecialchars($tr['purpose']) ?></span>
                        <span style="font-size: 10px; color: var(--text-secondary);"><?= date('d/m/Y', strtotime($tr['start_date'])) ?> &rarr; <?= date('d/m/Y', strtotime($tr['end_date'])) ?></span>
                    </div>
                    <div>
                        <?php if ($tr['status'] === 'APROVADO'): ?>
                            <span class="badge badge-success" style="font-size: 9px; padding: 1px 4px;">Aprovada</span>
                        <?php elseif ($tr['status'] === 'REJEITADO'): ?>
                            <span class="badge badge-danger" style="font-size: 9px; padding: 1px 4px;">Rejeitada</span>
                        <?php elseif ($tr['status'] === 'ENVIADO'): ?>
                            <span class="badge badge-info" style="font-size: 9px; padding: 1px 4px;">Auditoria</span>
                        <?php else: ?>
                            <span class="badge badge-warning" style="font-size: 9px; padding: 1px 4px;">Aberto</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
