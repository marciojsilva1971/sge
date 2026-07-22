<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px;">Comprovação de Militância</h2>
    <p class="subtitle" style="font-size: 12px;">Registre e comprove suas atividades de panfletagem com foto criptografada e geolocalização por GPS.</p>
</div>

<!-- MODAL DE CONFIRMAÇÃO PÓS-ENVIO DE MILITÂNCIA -->
<?php if (isset($_GET['envio_sucesso']) && $_GET['envio_sucesso'] == '1'): ?>
<div id="modalSucessoEnvio" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 2px solid #22c55e; border-radius: 16px; max-width: 450px; width: 100%; padding: 24px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <div style="width: 60px; height: 60px; background: rgba(34, 197, 94, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; color: #4ade80; font-size: 32px; font-weight: bold;">
            ✓
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: #f8fafc; margin-bottom: 8px;">Atividade Cadastrada com Sucesso!</h3>
        <p style="font-size: 13px; color: #94a3b8; margin-bottom: 20px; line-height: 1.5;">
            Sua comprovação de militância foi gravada com foto criptografada e coordenadas GPS. O que deseja fazer agora?
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <button type="button" onclick="fecharEPrepararNovoMilitancia()" style="background: #22c55e; color: #0f172a; font-weight: 800; padding: 12px; border-radius: 10px; border: none; text-decoration: none; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                ➕ Cadastrar uma Nova Atividade de Militância
            </button>
            <a href="<?= $this->baseUrl('portal/despesas') ?>" style="background: rgba(255, 255, 255, 0.08); color: #f8fafc; font-weight: 600; padding: 12px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.15); text-decoration: none; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                📊 Ir para "Meus Gastos" (Acompanhar Status)
            </a>
        </div>
    </div>
</div>
<script>
    function fecharEPrepararNovoMilitancia() {
        const modal = document.getElementById('modalSucessoEnvio');
        if (modal) modal.style.display = 'none';
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        const firstInput = document.getElementById('description');
        if (firstInput) {
            firstInput.value = '';
            firstInput.focus();
        }
    }
</script>
<?php endif; ?>

<!-- Formulário de Registro de Atividade -->
<div class="panel-card" style="padding: 16px; margin-bottom: 24px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
        <h3 style="font-size: 14px; font-weight: 600;">Registrar Atividade de Campo</h3>
    </div>
    
    <form action="<?= $this->baseUrl('portal/militancia') ?>" method="POST" enctype="multipart/form-data" id="militancyForm" onsubmit="return validarEnvio();">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        
        <!-- Coordenadas Geográficas (Preenchidas automaticamente por JavaScript) -->
        <input type="hidden" id="latitude" name="latitude" value="">
        <input type="hidden" id="longitude" name="longitude" value="">
        <!-- Foto Compactada em Base64 -->
        <input type="hidden" id="foto_base64" name="foto_base64" value="">

        <div class="form-group">
            <label for="description">Descrição da Atividade</label>
            <textarea id="description" name="description" rows="3" placeholder="Descreva a atividade realizada (Ex: Panfletagem e caminhada com líderes de bairro na Praça Central)..." required></textarea>
        </div>

        <div class="form-group">
            <label for="activity_date">Data da Atividade</label>
            <input type="date" id="activity_date" name="activity_date" value="<?= date('Y-m-d') ?>" required>
        </div>

        <!-- Indicador de Status do GPS -->
        <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                <label style="margin: 0;">Georreferenciamento (GPS)</label>
                <button type="button" onclick="abrirGpsModal()" style="background: none; border: none; color: #38bdf8; font-size: 11px; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">
                    ❓ Como ativar o GPS?
                </button>
            </div>
            <div id="gps-status" style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 10px 14px; background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px; font-size: 12px; color: var(--warning-color);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="spinner" id="gps-spinner" style="border: 2px solid rgba(255,255,255,0.1); border-top-color: var(--warning-color); border-radius: 50%; width: 14px; height: 14px; animation: spin 1s linear infinite;"></span>
                    <span id="gps-text">Obtendo coordenadas do dispositivo...</span>
                </div>
                <button type="button" id="btn-ajuda-gps" onclick="abrirGpsModal()" style="display: none; background: rgba(56, 189, 248, 0.15); border: 1px solid #38bdf8; color: #38bdf8; border-radius: 6px; padding: 4px 10px; font-size: 11px; font-weight: 600; cursor: pointer; white-space: nowrap;">
                    Ver instruções
                </button>
            </div>

            <!-- Checkbox para Permitir Envio sem GPS -->
            <div style="margin-top: 10px; padding: 10px 12px; background: rgba(15, 23, 42, 0.6); border: 1px dashed rgba(245, 158, 11, 0.4); border-radius: 10px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 12px; color: #f8fafc; font-weight: 500; margin: 0;">
                    <input type="checkbox" id="permitir_sem_gps" name="permitir_sem_gps" value="1" style="width: 18px; height: 18px; margin-top: 1px; accent-color: #f59e0b;" onchange="checarHabilitacaoForm();">
                    <div>
                        <span style="color: #fbbf24; font-weight: 600;">⚠️ Enviar comprovação sem coordenadas de GPS</span>
                        <p style="font-size: 11px; color: #94a3b8; margin-top: 2px; margin-bottom: 0;">
                            Marque esta opção se não for possível obter o GPS no seu dispositivo. A atividade ficará pendente de validação manual pela coordenação.
                        </p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Upload de Foto(s) e Compactação -->
        <div class="form-group">
            <label for="foto-input">Tirar/Selecionar Foto(s) de Comprovação</label>
            <p style="font-size: 11px; color: #94a3b8; margin-top: 2px; margin-bottom: 6px;">
                Você pode selecionar ou tirar foto de mais de um comprovante para esta mesma atividade.
            </p>
            <input type="file" id="foto-input" name="fotos[]" multiple accept="image/*" style="padding: 4px; font-size: 12px; margin-bottom: 8px;">
            
            <!-- Galeria de Miniaturas Visuais (Thumbnails) -->
            <div id="galeria-miniaturas-container" style="display: none; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 8px; margin-top: 8px; margin-bottom: 12px;"></div>
        </div>

        <button type="submit" id="submitBtn" class="btn btn-teal btn-block" disabled style="opacity: 0.5; cursor: not-allowed; margin-top: 10px;">
            🔒 Enviar Comprovação Criptografada
        </button>
    </form>
</div>

<!-- Atividades Anteriores -->
<div class="panel-card" style="padding: 16px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
        <h3 style="font-size: 14px; font-weight: 600;">Seu Histórico de Atividades</h3>
    </div>

    <?php if (empty($activities)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhuma atividade de militância enviada.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($activities as $act): ?>
                <div style="padding: 12px; background: rgba(15,23,42,0.4); border-radius: 10px; border: 1px solid rgba(255,255,255,0.04); font-size: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-weight: 600; color: var(--text-primary);"><?= date('d/m/Y', strtotime($act['activity_date'])) ?></span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <?php if ($act['status'] === 'APROVADO'): ?>
                                <span class="badge badge-success">Homologado</span>
                            <?php elseif ($act['status'] === 'REJEITADO'): ?>
                                <span class="badge badge-danger">Recusado</span>
                                <button type="button" class="btn btn-warning btn-sm" 
                                    data-militancia="<?= htmlspecialchars(json_encode($act, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                    onclick="abrirModalEditarMilitanciaElemento(this)"
                                    style="font-size: 10px; padding: 2px 8px; font-weight: 700; background: #eab308; color: #0f172a; border: none; border-radius: 6px; cursor: pointer;">
                                    ✏️ Editar
                                </button>
                            <?php else: ?>
                                <span class="badge badge-warning">Pendente</span>
                                <button type="button" class="btn btn-secondary btn-sm" 
                                    data-militancia="<?= htmlspecialchars(json_encode($act, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                    onclick="abrirModalEditarMilitanciaElemento(this)"
                                    style="font-size: 10px; padding: 2px 8px; font-weight: 600; background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; cursor: pointer;">
                                    ✏️ Editar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p style="color: var(--text-secondary); line-height: 1.4; margin-bottom: 8px;"><?= htmlspecialchars($act['description']) ?></p>
                    <div style="font-size: 10px; color: var(--text-secondary);">
                        📍 Lat/Long: <?= ($act['latitude'] == 0 && $act['longitude'] == 0) ? 'Sem GPS (Envio Manual)' : htmlspecialchars($act['latitude'] . ', ' . $act['longitude']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL DE EDIÇÃO DE ATIVIDADE DE MILITÂNCIA -->
<div id="modalEditarMilitanciaPage" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 1px solid #38bdf8; border-radius: 16px; max-width: 500px; width: 100%; padding: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #38bdf8;">✏️ Editar Atividade de Militância</h3>
            <button type="button" onclick="fecharModalEditarMilitanciaPage()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <form action="<?= $this->baseUrl('portal/militancia/editar') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="edit_page_militancy_id" name="militancy_id" value="">

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 600;">Descrição / Histórico da Atividade *</label>
                <textarea id="edit_page_militancy_description" name="description" rows="3" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;"></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 600;">Data da Atividade *</label>
                <input type="date" id="edit_page_militancy_date" name="activity_date" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
            </div>

            <!-- Upload de Fotos/Arquivos Adicionais com Criptografia e Previsualização (Thumbs) -->
            <div class="form-group" style="margin-bottom: 14px; background: rgba(15, 23, 42, 0.6); border: 1px dashed #38bdf8; padding: 12px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <label style="font-size: 12px; font-weight: 700; color: #38bdf8; margin: 0; display: flex; align-items: center; gap: 6px;">
                        📸 Anexar Nova(s) Foto(s) / Comprovantes
                    </label>
                    <span id="edit_militancy_file_count_page" style="font-size: 11px; font-weight: 700; color: #4ade80; display: none;"></span>
                </div>
                <p style="font-size: 11px; color: #94a3b8; margin: 0 0 8px 0;">
                    Você pode selecionar um ou mais arquivos. As fotos serão criptografadas em AES-256 e salvas na galeria da atividade.
                </p>
                <input type="file" id="edit_militancy_files_page" name="foto_militancia[]" accept="image/*, application/pdf" multiple style="width: 100%; padding: 6px; font-size: 12px; color: #fff; background: #1e293b; border: 1px solid #475569; border-radius: 6px;" onchange="gerarPreviewThumbsEditPage(this, 'edit_militancy_file_count_page', 'edit_militancy_thumbs_page')">
                
                <!-- Container de Miniaturas (Thumbs Grid) -->
                <div id="edit_militancy_thumbs_page" style="display: none; grid-template-columns: repeat(auto-fill, minmax(85px, 1fr)); gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(255, 255, 255, 0.15);"></div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 16px;">
                <button type="button" class="btn btn-secondary flex-1" onclick="fecharModalEditarMilitanciaPage()">Cancelar</button>
                <button type="submit" class="btn btn-teal flex-1" style="background: #eab308; color: #0f172a; font-weight: 800;">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
    function gerarPreviewThumbsEditPage(input, badgeId, containerId) {
        FileAccumulatorManager.handleFileSelect(input, badgeId, containerId);
    }

    function abrirModalEditarMilitanciaElemento(btn) {
        if (!btn) return;
        const rawData = btn.getAttribute('data-militancia');
        if (!rawData) return;
        try {
            const act = JSON.parse(rawData);
            document.getElementById('edit_page_militancy_id').value = act.id;
            document.getElementById('edit_page_militancy_description').value = act.description || '';
            document.getElementById('edit_page_militancy_date').value = act.activity_date || '';

            const inp = document.getElementById('edit_militancy_files_page');
            if (inp) {
                FileAccumulatorManager.resetStore('edit_militancy_files_page');
                inp.files = FileAccumulatorManager.getStore('edit_militancy_files_page').files;
                FileAccumulatorManager.renderThumbs(inp, 'edit_militancy_file_count_page', 'edit_militancy_thumbs_page');
            }

            document.getElementById('modalEditarMilitanciaPage').style.display = 'flex';
        } catch(e) {
            console.error(e);
        }
    }

    function fecharModalEditarMilitanciaPage() {
        document.getElementById('modalEditarMilitanciaPage').style.display = 'none';
    }
</script>

<!-- Modal de Instruções de Autorização de GPS -->
<div id="gpsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(6px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #0f172a; border: 1px solid #334155; border-radius: 16px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); display: flex; flex-direction: column;">
        
        <!-- Header -->
        <div style="padding: 16px 20px; border-bottom: 1px solid #1e293b; display: flex; align-items: center; justify-content: space-between; background: #0b1120;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">📍</span>
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #f8fafc; margin: 0;">Como Autorizar o Acesso ao GPS</h3>
                    <p style="font-size: 11px; color: #94a3b8; margin: 2px 0 0 0;">Selecione o seu tipo de dispositivo abaixo:</p>
                </div>
            </div>
            <button type="button" onclick="fecharGpsModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; font-weight: bold; cursor: pointer; padding: 0 4px;">✖</button>
        </div>

        <!-- Abas (Tabs) -->
        <div style="display: flex; background: #1e293b; border-bottom: 1px solid #334155; padding: 4px; gap: 4px;">
            <button type="button" id="tab-btn-apple" class="gps-tab-btn" onclick="switchGpsTab('apple')" style="flex: 1; padding: 10px; background: #0f172a; color: #38bdf8; border: 1px solid #38bdf8; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">
                🍏 Apple (iOS)
            </button>
            <button type="button" id="tab-btn-android" class="gps-tab-btn" onclick="switchGpsTab('android')" style="flex: 1; padding: 10px; background: transparent; color: #94a3b8; border: 1px solid transparent; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">
                🤖 Android
            </button>
            <button type="button" id="tab-btn-pc" class="gps-tab-btn" onclick="switchGpsTab('pc')" style="flex: 1; padding: 10px; background: transparent; color: #94a3b8; border: 1px solid transparent; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">
                💻 PC / Computador
            </button>
        </div>

        <!-- Conteúdo das Abas -->
        <div style="padding: 18px 20px; overflow-y: auto;">
            
            <!-- Conteúdo Apple (iOS) -->
            <div id="content-apple" class="gps-tab-content">
                <h4 style="font-size: 13px; font-weight: 700; color: #38bdf8; margin: 0 0 10px 0;">Passo a passo no iPhone / iPad (Safari ou Chrome):</h4>
                <ol style="font-size: 12px; color: #cbd5e1; padding-left: 20px; line-height: 1.6; margin: 0 0 14px 0;">
                    <li style="margin-bottom: 6px;">Abra o aplicativo <strong>Ajustes</strong> do iPhone.</li>
                    <li style="margin-bottom: 6px;">Acesse <strong>Privacidade e Segurança</strong> ➔ <strong>Serviços de Localização</strong>.</li>
                    <li style="margin-bottom: 6px;">Certifique-se de que a chave <strong>Serviços de Localização</strong> está ativada.</li>
                    <li style="margin-bottom: 6px;">Na lista de apps, toque no navegador (<strong>Safari</strong> ou <strong>Chrome</strong>).</li>
                    <li style="margin-bottom: 6px;">Selecione <strong>"Durante o Uso do App"</strong> e ative <strong>Localização Precisa</strong>.</li>
                    <li style="margin-bottom: 6px;">Volte ao navegador, toque no ícone de <strong>Cadeado 🔒</strong> ou <strong>aA</strong> na barra de endereço (topo) e escolha <strong>Permitir Localização</strong>.</li>
                </ol>
            </div>

            <!-- Conteúdo Android -->
            <div id="content-android" class="gps-tab-content" style="display: none;">
                <h4 style="font-size: 13px; font-weight: 700; color: #4ade80; margin: 0 0 10px 0;">Passo a passo no Celular Android (Chrome / Samsung):</h4>
                <ol style="font-size: 12px; color: #cbd5e1; padding-left: 20px; line-height: 1.6; margin: 0 0 14px 0;">
                    <li style="margin-bottom: 6px;">Deslize a barra de notificações (topo do celular) para baixo e ative o ícone <strong>Localização / GPS</strong>.</li>
                    <li style="margin-bottom: 6px;">No Chrome ou navegador, toque no ícone de <strong>Cadeado 🔒</strong> ou <strong>Configurações</strong> no canto esquerdo da barra de endereço.</li>
                    <li style="margin-bottom: 6px;">Toque em <strong>Permissões</strong> ➔ <strong>Localização</strong>.</li>
                    <li style="margin-bottom: 6px;">Altere para <strong>"Permitir"</strong>.</li>
                    <li style="margin-bottom: 6px;">Atualize a página (puxe a tela para baixo).</li>
                </ol>
            </div>

            <!-- Conteúdo PC / Notebook -->
            <div id="content-pc" class="gps-tab-content" style="display: none;">
                <h4 style="font-size: 13px; font-weight: 700; color: #f59e0b; margin: 0 0 10px 0;">Passo a passo no Computador / PC (Windows / Mac):</h4>
                <ol style="font-size: 12px; color: #cbd5e1; padding-left: 20px; line-height: 1.6; margin: 0 0 14px 0;">
                    <li style="margin-bottom: 6px;">Clique no ícone de <strong>Cadeado 🔒</strong> ou <strong>Ajustes de Site</strong> à esquerda do endereço Web (URL) no topo do navegador.</li>
                    <li style="margin-bottom: 6px;">Altere a chave ao lado de <strong>Localização</strong> para <strong>Permitir</strong>.</li>
                    <li style="margin-bottom: 6px;">No Windows: Vá em <i>Início ➔ Configurações ➔ Privacidade ➔ Localização</i> e ative a localização do dispositivo.</li>
                    <li style="margin-bottom: 6px;">Recarregue a página (pressione F5).</li>
                </ol>
            </div>

            <!-- Alerta para Envio Sem GPS -->
            <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); padding: 12px; border-radius: 10px; margin-top: 10px;">
                <p style="font-size: 11px; color: #fbbf24; margin: 0; font-weight: 600;">
                    💡 Não é possível utilizar o GPS neste dispositivo?
                </p>
                <p style="font-size: 11px; color: #cbd5e1; margin: 4px 0 10px 0; line-height: 1.4;">
                    Você pode marcar a autorização manual para enviar a comprovação de atividade mesmo sem localização GPS.
                </p>
                <button type="button" onclick="ativarEnvioSemGpsEMefecharModal()" style="width: 100%; background: #f59e0b; color: #0f172a; font-weight: 800; padding: 10px; border: none; border-radius: 8px; font-size: 12px; cursor: pointer;">
                    ☑️ Ativar Opção "Enviar Sem GPS" e Fechar
                </button>
            </div>

        </div>

        <!-- Footer do Modal -->
        <div style="padding: 12px 20px; border-top: 1px solid #1e293b; display: flex; justify-content: flex-end; gap: 10px; background: #0b1120; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
            <button type="button" onclick="tentarNovamenteGps()" style="background: #0284c7; color: #fff; border: none; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                🔄 Tentar GPS Novamente
            </button>
            <button type="button" onclick="fecharGpsModal()" style="background: #334155; color: #fff; border: none; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                Fechar
            </button>
        </div>

    </div>
</div>

<!-- Canvas oculto para compressão de imagem -->
<canvas id="compressCanvas" style="display: none;"></canvas>

<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
    // Gerenciamento de Abas e Modal de GPS
    function abrirGpsModal() {
        const modal = document.getElementById('gpsModal');
        if (modal) modal.style.display = 'flex';
    }

    function fecharGpsModal() {
        const modal = document.getElementById('gpsModal');
        if (modal) modal.style.display = 'none';
    }

    function switchGpsTab(platform) {
        const platforms = ['apple', 'android', 'pc'];
        platforms.forEach(p => {
            const btn = document.getElementById('tab-btn-' + p);
            const content = document.getElementById('content-' + p);
            if (btn && content) {
                if (p === platform) {
                    btn.style.background = '#0f172a';
                    btn.style.color = (p === 'apple') ? '#38bdf8' : ((p === 'android') ? '#4ade80' : '#f59e0b');
                    btn.style.borderColor = (p === 'apple') ? '#38bdf8' : ((p === 'android') ? '#4ade80' : '#f59e0b');
                    content.style.display = 'block';
                } else {
                    btn.style.background = 'transparent';
                    btn.style.color = '#94a3b8';
                    btn.style.borderColor = 'transparent';
                    content.style.display = 'none';
                }
            }
        });
    }

    function ativarEnvioSemGpsEMefecharModal() {
        const chk = document.getElementById('permitir_sem_gps');
        if (chk) {
            chk.checked = true;
            checarHabilitacaoForm();
        }
        fecharGpsModal();
    }

    function tentarNovamenteGps() {
        fecharGpsModal();
        iniciarCapturaGps();
    }

    // Captura automática de GPS (Geolocalização HTML5)
    function iniciarCapturaGps() {
        const gpsStatus = document.getElementById('gps-status');
        const gpsSpinner = document.getElementById('gps-spinner');
        const gpsText = document.getElementById('gps-text');
        const btnAjuda = document.getElementById('btn-ajuda-gps');

        if (gpsStatus) {
            gpsStatus.style.backgroundColor = 'rgba(245, 158, 11, 0.1)';
            gpsStatus.style.borderColor = 'rgba(245, 158, 11, 0.2)';
            gpsStatus.style.color = 'var(--warning-color)';
        }
        if (gpsSpinner) gpsSpinner.style.display = 'inline-block';
        if (gpsText) gpsText.innerText = 'Obtendo coordenadas do dispositivo...';
        if (btnAjuda) btnAjuda.style.display = 'none';

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lon;
                    
                    if (gpsStatus) {
                        gpsStatus.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                        gpsStatus.style.borderColor = 'rgba(16, 185, 129, 0.2)';
                        gpsStatus.style.color = 'var(--success-color)';
                    }
                    if (gpsSpinner) gpsSpinner.style.display = 'none';
                    if (gpsText) gpsText.innerText = `📍 GPS Ativo: ${lat.toFixed(5)}, ${lon.toFixed(5)}`;
                    
                    checarHabilitacaoForm();
                },
                (error) => {
                    console.error("Erro ao obter GPS: ", error);
                    let erroMsg = "Falha ao obter localização. ";
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            erroMsg += "Autorize o acesso ao GPS no seu navegador/celular.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            erroMsg += "Sinal de GPS indisponível.";
                            break;
                        case error.TIMEOUT:
                            erroMsg += "Tempo limite de resposta do GPS excedido.";
                            break;
                        default:
                            erroMsg += "GPS não disponível.";
                            break;
                    }
                    
                    if (gpsStatus) {
                        gpsStatus.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
                        gpsStatus.style.borderColor = 'rgba(239, 68, 68, 0.2)';
                        gpsStatus.style.color = 'var(--error-color)';
                    }
                    if (gpsSpinner) gpsSpinner.style.display = 'none';
                    if (gpsText) gpsText.innerText = erroMsg;
                    if (btnAjuda) btnAjuda.style.display = 'inline-block';

                    // Abre o modal de instruções automaticamente se houver erro ou negação
                    abrirGpsModal();
                    checarHabilitacaoForm();
                },
                { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
            );
        } else {
            if (gpsStatus) {
                gpsStatus.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
                gpsStatus.style.borderColor = 'rgba(239, 68, 68, 0.2)';
                gpsStatus.style.color = 'var(--error-color)';
            }
            if (gpsSpinner) gpsSpinner.style.display = 'none';
            if (gpsText) gpsText.innerText = "Seu dispositivo não possui suporte a GPS.";
            if (btnAjuda) btnAjuda.style.display = 'inline-block';

            abrirGpsModal();
            checarHabilitacaoForm();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        iniciarCapturaGps();
    });

    // Acumulador de Múltiplos Arquivos usando DataTransfer API
    const fotoInput = document.getElementById('foto-input');
    const galeriaContainer = document.getElementById('galeria-miniaturas-container');
    const arquivosAcumulados = new DataTransfer();

    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            if (newFiles.length === 0) return;

            newFiles.forEach(file => {
                arquivosAcumulados.items.add(file);
            });

            fotoInput.files = arquivosAcumulados.files;
            atualizarGaleriaMiniaturas();
            processarFotoBase64(arquivosAcumulados.files[0]);
            checarHabilitacaoForm();
        });
    }

    function atualizarGaleriaMiniaturas() {
        if (!galeriaContainer) return;
        galeriaContainer.innerHTML = '';

        if (arquivosAcumulados.files.length === 0) {
            galeriaContainer.style.display = 'none';
            document.getElementById('foto_base64').value = '';
            return;
        }

        galeriaContainer.style.display = 'grid';

        Array.from(arquivosAcumulados.files).forEach((file, index) => {
            const card = document.createElement('div');
            card.style.cssText = 'position: relative; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 4px; text-align: center; overflow: hidden;';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.innerHTML = '✖';
            removeBtn.style.cssText = 'position: absolute; top: 2px; right: 2px; background: #ef4444; color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; font-weight: bold;';
            removeBtn.onclick = (event) => {
                event.stopPropagation();
                removerArquivoGaleria(index);
            };
            card.appendChild(removeBtn);

            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.style.cssText = 'width: 100%; height: 70px; object-fit: cover; border-radius: 4px; display: block; margin-bottom: 4px;';
                const reader = new FileReader();
                reader.onload = (e) => { img.src = e.target.result; };
                reader.readAsDataURL(file);
                card.appendChild(img);
            } else {
                const icon = document.createElement('div');
                icon.style.cssText = 'height: 70px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #38bdf8; background: #1e293b; border-radius: 4px; margin-bottom: 4px;';
                icon.innerText = '📄';
                card.appendChild(icon);
            }

            const nameLabel = document.createElement('div');
            nameLabel.style.cssText = 'font-size: 9px; color: #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 500;';
            nameLabel.innerText = file.name;
            card.appendChild(nameLabel);

            galeriaContainer.appendChild(card);
        });
    }

    function removerArquivoGaleria(index) {
        const dt = new DataTransfer();
        const files = Array.from(arquivosAcumulados.files);
        files.forEach((file, idx) => {
            if (idx !== index) {
                dt.items.add(file);
            }
        });

        arquivosAcumulados.items.clear();
        Array.from(dt.files).forEach(f => arquivosAcumulados.items.add(f));
        fotoInput.files = arquivosAcumulados.files;

        atualizarGaleriaMiniaturas();
        if (arquivosAcumulados.files.length > 0) {
            processarFotoBase64(arquivosAcumulados.files[0]);
        }
        checarHabilitacaoForm();
    }

    function processarFotoBase64(file) {
        if (!file || !file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.getElementById('compressCanvas');
                const ctx = canvas.getContext('2d');

                const MAX_WIDTH = 1000;
                const MAX_HEIGHT = 1000;
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > MAX_WIDTH) {
                        height *= MAX_WIDTH / width;
                        width = MAX_WIDTH;
                    }
                } else {
                    if (height > MAX_HEIGHT) {
                        width *= MAX_HEIGHT / height;
                        height = MAX_HEIGHT;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.75);
                document.getElementById('foto_base64').value = dataUrl;
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    function checarHabilitacaoForm() {
        const lat = document.getElementById('latitude').value;
        const lon = document.getElementById('longitude').value;
        const base64 = document.getElementById('foto_base64').value;
        const chkSemGps = document.getElementById('permitir_sem_gps');
        const submitBtn = document.getElementById('submitBtn');

        const temGps = (lat !== '' && lon !== '' && lat !== '0' && lon !== '0');
        const permiteSemGps = (chkSemGps && chkSemGps.checked);
        const temFoto = (arquivosAcumulados.files.length > 0 || base64 !== '');

        if ((temGps || permiteSemGps) && temFoto) {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        } else {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
        }
    }

    function validarEnvio() {
        const lat = document.getElementById('latitude').value;
        const lon = document.getElementById('longitude').value;
        const base64 = document.getElementById('foto_base64').value;
        const chkSemGps = document.getElementById('permitir_sem_gps');

        const temGps = (lat !== '' && lon !== '' && lat !== '0' && lon !== '0');
        const permiteSemGps = (chkSemGps && chkSemGps.checked);
        const temFoto = (arquivosAcumulados.files.length > 0 || base64 !== '');

        if (!temGps && !permiteSemGps) {
            alert("Aguarde a obtenção do GPS ou marque a opção 'Enviar comprovação sem coordenadas de GPS'.");
            return false;
        }
        if (!temFoto) {
            alert("Por favor, selecione ou tire pelo menos uma foto de comprovação.");
            return false;
        }
        return true;
    }
</script>
