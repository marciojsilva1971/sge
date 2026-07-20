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
            <label>Georreferenciamento (GPS)</label>
            <div id="gps-status" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px; font-size: 13px; color: var(--warning-color);">
                <span class="spinner" style="border: 2px solid rgba(255,255,255,0.1); border-top-color: var(--warning-color); border-radius: 50%; width: 14px; height: 14px; animation: spin 1s linear infinite;"></span>
                <span id="gps-text">Obtendo coordenadas do dispositivo...</span>
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
                        📍 Lat/Long: <?= $act['latitude'] ?>, <?= $act['longitude'] ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
    // Captura automática de GPS (Geolocalização HTML5)
    document.addEventListener('DOMContentLoaded', () => {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lon;
                    
                    const gpsStatus = document.getElementById('gps-status');
                    gpsStatus.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                    gpsStatus.style.borderColor = 'rgba(16, 185, 129, 0.2)';
                    gpsStatus.style.color = 'var(--success-color)';
                    
                    document.getElementById('gps-text').innerText = `📍 GPS Ativo: ${lat.toFixed(5)}, ${lon.toFixed(5)}`;
                    
                    // Se o GPS está capturado, podemos habilitar o envio se a foto também for fornecida
                    checarHabilitacaoForm();
                },
                (error) => {
                    console.error("Erro ao obter GPS: ", error);
                    let erroMsg = "Falha ao obter localização. ";
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            erroMsg += "Por favor, autorize o acesso ao GPS nas configurações do seu navegador/celular.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            erroMsg += "Sinal GPS indisponível no momento.";
                            break;
                        case error.TIMEOUT:
                            erroMsg += "Tempo limite excedido ao obter sinal de satélite.";
                            break;
                        default:
                            erroMsg += "Erro desconhecido de GPS.";
                            break;
                    }
                    
                    const gpsStatus = document.getElementById('gps-status');
                    gpsStatus.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
                    gpsStatus.style.borderColor = 'rgba(239, 68, 68, 0.2)';
                    gpsStatus.style.color = 'var(--error-color)';
                    document.getElementById('gps-text').innerText = erroMsg;
                    alert("Atenção: A geolocalização é obrigatória para homologação de atividades. Ative seu GPS.");
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        } else {
            const gpsStatus = document.getElementById('gps-status');
            gpsStatus.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
            gpsStatus.style.borderColor = 'rgba(239, 68, 68, 0.2)';
            gpsStatus.style.color = 'var(--error-color)';
            document.getElementById('gps-text').innerText = "Seu dispositivo não possui GPS ou geolocalização suportada.";
        }
    });

    // Compressão de Imagens no Cliente usando Canvas
    const fotoInput = document.getElementById('foto-input');
    fotoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.getElementById('compressCanvas');
                const ctx = canvas.getContext('2d');

                // Define dimensões máximas (Ex: 1000px)
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

                // Exporta como JPEG com compressão de 75%
                const dataUrl = canvas.toDataURL('image/jpeg', 0.75);
                
                // Grava no campo oculto
                document.getElementById('foto_base64').value = dataUrl;
                
                // Exibe preview
                document.getElementById('preview-image').src = dataUrl;
                document.getElementById('preview-container').style.display = 'block';
                
                checarHabilitacaoForm();
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });

    function checarHabilitacaoForm() {
        const lat = document.getElementById('latitude').value;
        const lon = document.getElementById('longitude').value;
        const base64 = document.getElementById('foto_base64').value;
        const submitBtn = document.getElementById('submitBtn');

        if (lat !== '' && lon !== '' && base64 !== '') {
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

        if (lat === '' || lon === '') {
            alert("Aguarde a obtenção das coordenadas de geolocalização ou certifique-se de que o GPS de seu celular está ativo.");
            return false;
        }
        if (base64 === '') {
            alert("Por favor, selecione ou tire uma foto de comprovação.");
            return false;
        }
        return true;
    }
</script>
