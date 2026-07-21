<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px;">Comprovação de Militância</h2>
    <p class="subtitle" style="font-size: 12px;">Registre e comprove suas atividades de panfletagem com foto criptografada e geolocalização por GPS.</p>
</div>

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

        <!-- Upload de Foto e Compactação -->
        <div class="form-group">
            <label for="foto-input">Tirar/Selecionar Foto de Comprovação</label>
            <input type="file" id="foto-input" accept="image/*" required style="padding: 4px; font-size: 12px; margin-bottom: 10px;">
            
            <!-- Preview da foto compactada -->
            <div id="preview-container" style="display: none; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); background-color: #000; text-align: center; position: relative;">
                <img id="preview-image" src="" alt="Preview da Imagem" style="max-width: 100%; max-height: 250px; object-fit: contain; display: block; margin: 0 auto;">
                <div style="position: absolute; top: 10px; right: 10px; background: rgba(15,23,42,0.85); padding: 4px 8px; border-radius: 6px; font-size: 10px; color: var(--success-color);">
                    Optimized (Canvas)
                </div>
            </div>
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
                        <div>
                            <?php if ($act['status'] === 'APROVADO'): ?>
                                <span class="badge badge-success">Homologado</span>
                            <?php elseif ($act['status'] === 'REJEITADO'): ?>
                                <span class="badge badge-danger">Recusado</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pendente</span>
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

    // Compressão de Imagens no Cliente usando Canvas
    const fotoInput = document.getElementById('foto-input');
    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

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
                    
                    document.getElementById('preview-image').src = dataUrl;
                    document.getElementById('preview-container').style.display = 'block';
                    
                    checarHabilitacaoForm();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function checarHabilitacaoForm() {
        const lat = document.getElementById('latitude').value;
        const lon = document.getElementById('longitude').value;
        const base64 = document.getElementById('foto_base64').value;
        const chkSemGps = document.getElementById('permitir_sem_gps');
        const submitBtn = document.getElementById('submitBtn');

        const temGps = (lat !== '' && lon !== '' && lat !== '0' && lon !== '0');
        const permiteSemGps = (chkSemGps && chkSemGps.checked);
        const temFoto = (base64 !== '');

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

        if (!temGps && !permiteSemGps) {
            alert("Aguarde a obtenção do GPS ou marque a opção 'Enviar comprovação sem coordenadas de GPS'.");
            return false;
        }
        if (base64 === '') {
            alert("Por favor, selecione ou tire uma foto de comprovação.");
            return false;
        }
        return true;
    }
</script>
