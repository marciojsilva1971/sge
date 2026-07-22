<div class="welcome-banner" style="text-align: center; margin-bottom: 20px;">
    <h2 style="font-size: 22px;">Olá, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>!</h2>
    <p class="subtitle" style="font-size: 13px;">Bem-vindo ao seu portal de campo. Selecione uma ação abaixo para iniciar.</p>
</div>

<!-- Grid de Ações Rápidas com Status/Indicadores e Edição -->
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 24px;">
    
    <!-- Viagens -->
    <a href="<?= $this->baseUrl('portal/viagem') ?>" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px 8px; background: rgba(30, 41, 59, 0.75); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 16px; text-decoration: none; color: #fff; text-align: center; position: relative; transition: var(--transition-fast);" onmouseover="this.style.borderColor='var(--accent-indigo)'" onmouseout="this.style.borderColor='rgba(99, 102, 241, 0.3)'">
        <span style="font-size: 26px; margin-bottom: 4px;">🚗</span>
        <span style="font-size: 13px; font-weight: 700; display: block;">Viagens</span>
        <span style="font-size: 10px; color: var(--text-secondary); margin-top: 1px;">Reembolso / Combustível</span>

        <!-- Badges de Situação -->
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 4px; margin-top: 8px;">
            <?php if (!empty($travelStats['pending'])): ?>
                <span style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid #38bdf8; font-size: 9px; padding: 2px 6px; border-radius: 10px; font-weight: 700;">⏳ <?= $travelStats['pending'] ?> em análise</span>
            <?php endif; ?>
            <?php if (!empty($travelStats['rejected'])): ?>
                <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; font-size: 9px; padding: 2px 6px; border-radius: 10px; font-weight: 700;">❌ <?= $travelStats['rejected'] ?> recusada(s)</span>
            <?php endif; ?>
            <?php if (!empty($travelStats['open'])): ?>
                <span style="background: rgba(234, 179, 8, 0.2); color: #fde047; border: 1px solid #eab308; font-size: 9px; padding: 2px 6px; border-radius: 10px; font-weight: 700;">🟢 <?= $travelStats['open'] ?> aberta(s)</span>
            <?php endif; ?>
        </div>
    </a>

    <!-- Militância -->
    <a href="<?= $this->baseUrl('portal/militancia') ?>" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px 8px; background: rgba(30, 41, 59, 0.75); border: 1px solid rgba(45, 212, 191, 0.3); border-radius: 16px; text-decoration: none; color: #fff; text-align: center; position: relative; transition: var(--transition-fast);" onmouseover="this.style.borderColor='var(--accent-teal)'" onmouseout="this.style.borderColor='rgba(45, 212, 191, 0.3)'">
        <span style="font-size: 26px; margin-bottom: 4px;">📢</span>
        <span style="font-size: 13px; font-weight: 700; display: block;">Militância</span>
        <span style="font-size: 10px; color: var(--text-secondary); margin-top: 1px;">Panfletagem / Campo</span>

        <!-- Badges de Situação -->
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 4px; margin-top: 8px;">
            <?php if (!empty($militancyStats['pending'])): ?>
                <span style="background: rgba(234, 179, 8, 0.2); color: #fde047; border: 1px solid #eab308; font-size: 9px; padding: 2px 6px; border-radius: 10px; font-weight: 700;">⏳ <?= $militancyStats['pending'] ?> pendente(s)</span>
            <?php endif; ?>
            <?php if (!empty($militancyStats['rejected'])): ?>
                <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; font-size: 9px; padding: 2px 6px; border-radius: 10px; font-weight: 700;">❌ <?= $militancyStats['rejected'] ?> recusada(s)</span>
            <?php endif; ?>
        </div>
    </a>

    <!-- Outros Gastos -->
    <a href="<?= $this->baseUrl('portal/outros') ?>" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px 8px; background: rgba(30, 41, 59, 0.75); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 16px; text-decoration: none; color: #fff; text-align: center; position: relative; transition: var(--transition-fast);" onmouseover="this.style.borderColor='#38bdf8'" onmouseout="this.style.borderColor='rgba(56, 189, 248, 0.3)'">
        <span style="font-size: 26px; margin-bottom: 4px;">📦</span>
        <span style="font-size: 13px; font-weight: 700; display: block;">Outros Gastos</span>
        <span style="font-size: 10px; color: var(--text-secondary); margin-top: 1px;">Lançamento Direto</span>

        <!-- Badges de Situação -->
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 4px; margin-top: 8px;">
            <?php if (!empty($outrosStats['pending'])): ?>
                <span style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid #38bdf8; font-size: 9px; padding: 2px 6px; border-radius: 10px; font-weight: 700;">⏳ <?= $outrosStats['pending'] ?> pendente(s)</span>
            <?php endif; ?>
            <?php if (!empty($outrosStats['rejected'])): ?>
                <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; font-size: 9px; padding: 2px 6px; border-radius: 10px; font-weight: 700;">❌ <?= $outrosStats['rejected'] ?> recusado(s)</span>
            <?php endif; ?>
        </div>
    </a>

    <!-- Meus Gastos -->
    <a href="<?= $this->baseUrl('portal/despesas') ?>" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px 8px; background: rgba(30, 41, 59, 0.75); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 16px; text-decoration: none; color: #fff; text-align: center; position: relative; transition: var(--transition-fast);" onmouseover="this.style.borderColor='var(--warning-color)'" onmouseout="this.style.borderColor='rgba(245, 158, 11, 0.3)'">
        <span style="font-size: 26px; margin-bottom: 4px;">💸</span>
        <span style="font-size: 13px; font-weight: 700; display: block;">Meus Gastos</span>
        <span style="font-size: 10px; color: var(--text-secondary); margin-top: 1px;">Acompanhar Status</span>

        <!-- Badges de Situação -->
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 4px; margin-top: 8px;">
            <?php if (!empty($expenseStats['pending'])): ?>
                <span style="background: rgba(234, 179, 8, 0.2); color: #fde047; border: 1px solid #eab308; font-size: 9px; padding: 2px 6px; border-radius: 10px; font-weight: 700;">⏳ <?= $expenseStats['pending'] ?> pendente(s)</span>
            <?php endif; ?>
            <?php if (!empty($expenseStats['rejected'])): ?>
                <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; font-size: 9px; padding: 2px 6px; border-radius: 10px; font-weight: 700;">❌ <?= $expenseStats['rejected'] ?> recusado(s)</span>
            <?php endif; ?>
        </div>
    </a>

</div>

<!-- Atividades de Militância Recentes com Botão de Edição -->
<div class="panel-card" style="padding: 16px; margin-bottom: 20px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 14px; font-weight: 600;">Suas Atividades de Panfletagem</h3>
        <a href="<?= $this->baseUrl('portal/militancia') ?>" style="font-size: 11px; color: #38bdf8; text-decoration: none; font-weight: 600;">+ Cadastrar Nova</a>
    </div>
    
    <?php if (empty($recentMilitancy)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhuma atividade registrada.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($recentMilitancy as $act): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: rgba(15, 23, 42, 0.6); border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div>
                        <span style="font-size: 12px; font-weight: 600; display: block; color: #f8fafc;"><?= htmlspecialchars($act['description']) ?></span>
                        <span style="font-size: 10px; color: var(--text-secondary);"><?= date('d/m/Y', strtotime($act['activity_date'])) ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <?php if ($act['status'] === 'APROVADO'): ?>
                            <span class="badge badge-success" style="font-size: 9px; padding: 2px 6px;">Homologada</span>
                        <?php elseif ($act['status'] === 'REJEITADO'): ?>
                            <span class="badge badge-danger" style="font-size: 9px; padding: 2px 6px;">Recusada</span>
                            <button type="button" class="btn btn-warning btn-sm" style="font-size: 10px; padding: 2px 8px; font-weight: 700; background: #eab308; color: #0f172a;" onclick='abrirModalEditarMilitancia(<?= json_encode($act, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                ✏️ Editar
                            </button>
                        <?php else: ?>
                            <span class="badge badge-warning" style="font-size: 9px; padding: 2px 6px;">Pendente</span>
                            <button type="button" class="btn btn-secondary btn-sm" style="font-size: 10px; padding: 2px 8px; font-weight: 600; background: rgba(255,255,255,0.1); color: #fff;" onclick='abrirModalEditarMilitancia(<?= json_encode($act, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                ✏️ Editar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Viagens Recentes com Botão de Edição -->
<div class="panel-card" style="padding: 16px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 14px; font-weight: 600;">Seus Relatórios de Viagem</h3>
        <a href="<?= $this->baseUrl('portal/viagem') ?>" style="font-size: 11px; color: #38bdf8; text-decoration: none; font-weight: 600;">+ Nova Viagem</a>
    </div>

    <?php if (empty($recentTravels)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhuma viagem registrada.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($recentTravels as $tr): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: rgba(15, 23, 42, 0.6); border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div>
                        <span style="font-size: 12px; font-weight: 600; display: block; color: #f8fafc;"><?= htmlspecialchars($tr['purpose']) ?></span>
                        <span style="font-size: 10px; color: var(--text-secondary);">
                            <?= date('d/m/Y', strtotime($tr['start_date'])) ?> &rarr; <?= date('d/m/Y', strtotime($tr['end_date'])) ?> &bull; Placa: <strong><?= htmlspecialchars(strtoupper($tr['vehicle_plate'] ?? 'N/I')) ?></strong>
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <?php if ($tr['status'] === 'APROVADO'): ?>
                            <span class="badge badge-success" style="font-size: 9px; padding: 2px 6px;">Aprovada</span>
                        <?php elseif ($tr['status'] === 'REJEITADO'): ?>
                            <span class="badge badge-danger" style="font-size: 9px; padding: 2px 6px;">Rejeitada</span>
                            <button type="button" class="btn btn-warning btn-sm" style="font-size: 10px; padding: 2px 8px; font-weight: 700; background: #eab308; color: #0f172a;" onclick='abrirModalEditarViagem(<?= json_encode($tr, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                ✏️ Editar
                            </button>
                        <?php elseif ($tr['status'] === 'ENVIADO'): ?>
                            <span class="badge badge-info" style="font-size: 9px; padding: 2px 6px;">Auditoria</span>
                            <button type="button" class="btn btn-secondary btn-sm" style="font-size: 10px; padding: 2px 8px; font-weight: 600; background: rgba(255,255,255,0.1); color: #fff;" onclick='abrirModalEditarViagem(<?= json_encode($tr, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                ✏️ Editar
                            </button>
                        <?php else: ?>
                            <span class="badge badge-warning" style="font-size: 9px; padding: 2px 6px;">Aberto</span>
                            <button type="button" class="btn btn-secondary btn-sm" style="font-size: 10px; padding: 2px 8px; font-weight: 600; background: rgba(255,255,255,0.1); color: #fff;" onclick='abrirModalEditarViagem(<?= json_encode($tr, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                ✏️ Editar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL DE EDIÇÃO DE ATIVIDADE DE MILITÂNCIA -->
<div id="modalEditarMilitancia" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 1px solid #38bdf8; border-radius: 16px; max-width: 500px; width: 100%; padding: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #38bdf8;">✏️ Editar Atividade de Militância</h3>
            <button type="button" onclick="fecharModalEditarMilitancia()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <form action="<?= $this->baseUrl('portal/militancia/editar') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="edit_militancy_id" name="militancy_id" value="">

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 600;">Descrição / Histórico da Atividade *</label>
                <textarea id="edit_militancy_description" name="description" rows="3" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;"></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 600;">Data da Atividade *</label>
                <input type="date" id="edit_militancy_date" name="activity_date" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
            </div>

            <!-- Upload de Fotos/Arquivos Adicionais com Criptografia e Previsualização (Thumbs) -->
            <div class="form-group" style="margin-bottom: 14px; background: rgba(15, 23, 42, 0.6); border: 1px dashed #38bdf8; padding: 12px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <label style="font-size: 12px; font-weight: 700; color: #38bdf8; margin: 0; display: flex; align-items: center; gap: 6px;">
                        📸 Anexar Nova(s) Foto(s) / Comprovantes
                    </label>
                    <span id="edit_militancy_file_count_index" style="font-size: 11px; font-weight: 700; color: #4ade80; display: none;"></span>
                </div>
                <p style="font-size: 11px; color: #94a3b8; margin: 0 0 8px 0;">
                    Você pode selecionar um ou mais arquivos. As fotos serão criptografadas em AES-256 e salvas na galeria da atividade.
                </p>
                <input type="file" id="edit_militancy_files_index" name="foto_militancia[]" accept="image/*, application/pdf" multiple style="width: 100%; padding: 6px; font-size: 12px; color: #fff; background: #1e293b; border: 1px solid #475569; border-radius: 6px;" onchange="gerarPreviewThumbsEdit(this, 'edit_militancy_file_count_index', 'edit_militancy_thumbs_index')">
                
                <!-- Container de Miniaturas (Thumbs Grid) -->
                <div id="edit_militancy_thumbs_index" style="display: none; grid-template-columns: repeat(auto-fill, minmax(85px, 1fr)); gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(255, 255, 255, 0.15);"></div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 16px;">
                <button type="button" class="btn btn-secondary flex-1" onclick="fecharModalEditarMilitancia()">Cancelar</button>
                <button type="submit" class="btn btn-teal flex-1" style="background: #eab308; color: #0f172a; font-weight: 800;">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DE EDIÇÃO DE RELATÓRIO DE VIAGEM -->
<div id="modalEditarViagem" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 1px solid var(--accent-indigo); border-radius: 16px; max-width: 500px; width: 100%; padding: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #818cf8;">✏️ Editar Relatório de Viagem / Combustível</h3>
            <button type="button" onclick="fecharModalEditarViagem()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <form action="<?= $this->baseUrl('portal/viagem/editar') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="edit_travel_id" name="travel_id" value="">

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 600;">Objetivo da Viagem *</label>
                <input type="text" id="edit_travel_purpose" name="purpose" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Data Início *</label>
                    <input type="date" id="edit_travel_start_date" name="start_date" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Data Fim *</label>
                    <input type="date" id="edit_travel_end_date" name="end_date" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 11px; font-weight: 600;">Placa Veículo *</label>
                    <input type="text" id="edit_travel_vehicle_plate" name="vehicle_plate" required placeholder="AAA-0A00" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 12px; text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label style="font-size: 11px; font-weight: 600;">KM Inicial *</label>
                    <input type="number" id="edit_travel_initial_km" name="initial_km" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 12px;">
                </div>
                <div class="form-group">
                    <label style="font-size: 11px; font-weight: 600;">KM Final</label>
                    <input type="number" id="edit_travel_final_km" name="final_km" placeholder="KM Final" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 12px;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 16px;">
                <button type="button" class="btn btn-secondary flex-1" onclick="fecharModalEditarViagem()">Cancelar</button>
                <button type="submit" class="btn btn-teal flex-1" style="background: #eab308; color: #0f172a; font-weight: 800;">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
    function gerarPreviewThumbsEdit(input, badgeId, containerId) {
        const badge = document.getElementById(badgeId);
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '';
        if (input.files && input.files.length > 0) {
            container.style.display = 'grid';
            if (badge) {
                badge.style.display = 'inline-block';
                badge.innerText = '📎 ' + input.files.length + ' arquivo(s) selecionado(s)';
            }

            Array.from(input.files).forEach((file) => {
                const box = document.createElement('div');
                box.style.cssText = 'background: rgba(30, 41, 59, 0.9); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 8px; padding: 6px; text-align: center; overflow: hidden; font-size: 10px;';

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.style.cssText = 'width: 100%; height: 65px; object-fit: cover; border-radius: 4px; margin-bottom: 4px; display: block;';
                    box.appendChild(img);
                } else {
                    const docIcon = document.createElement('div');
                    docIcon.innerHTML = '📄 PDF';
                    docIcon.style.cssText = 'height: 65px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #38bdf8; font-size: 13px; background: rgba(15, 23, 42, 0.5); border-radius: 4px; margin-bottom: 4px;';
                    box.appendChild(docIcon);
                }

                const label = document.createElement('span');
                label.style.cssText = 'color: #cbd5e1; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 500;';
                label.innerText = file.name;
                box.appendChild(label);

                container.appendChild(box);
            });
        } else {
            container.style.display = 'none';
            if (badge) badge.style.display = 'none';
        }
    }

    function abrirModalEditarMilitancia(act) {
        document.getElementById('edit_militancy_id').value = act.id;
        document.getElementById('edit_militancy_description').value = act.description || '';
        document.getElementById('edit_militancy_date').value = act.activity_date || '';
        
        // Reseta campo de arquivo e thumbs
        const inp = document.getElementById('edit_militancy_files_index');
        if (inp) {
            inp.value = '';
            gerarPreviewThumbsEdit(inp, 'edit_militancy_file_count_index', 'edit_militancy_thumbs_index');
        }

        document.getElementById('modalEditarMilitancia').style.display = 'flex';
    }

    function fecharModalEditarMilitancia() {
        document.getElementById('modalEditarMilitancia').style.display = 'none';
    }

    function abrirModalEditarViagem(tr) {
        document.getElementById('edit_travel_id').value = tr.id;
        document.getElementById('edit_travel_purpose').value = tr.purpose || '';
        document.getElementById('edit_travel_start_date').value = tr.start_date || '';
        document.getElementById('edit_travel_end_date').value = tr.end_date || '';
        document.getElementById('edit_travel_vehicle_plate').value = tr.vehicle_plate || '';
        document.getElementById('edit_travel_initial_km').value = tr.initial_km || '';
        document.getElementById('edit_travel_final_km').value = tr.final_km || '';
        document.getElementById('modalEditarViagem').style.display = 'flex';
    }

    function fecharModalEditarViagem() {
        document.getElementById('modalEditarViagem').style.display = 'none';
    }
</script>
