<!-- Formulário de Ativação por Convite -->
<div class="auth-card activate-card">
    <h3>Ativação de Acesso</h3>
    <p class="card-subtitle">Olá, <strong><?= htmlspecialchars($user['name']) ?></strong>! Defina suas credenciais para ativar seu perfil.</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <span class="alert-icon">⚠</span>
            <span class="alert-message"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= $this->baseUrl('ativar') ?>" method="POST" enctype="multipart/form-data" class="auth-form">
        <!-- Token CSRF e Token de Convite -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div class="form-group text-center">
            <label>Foto de Perfil (Opcional)</label>
            <div class="avatar-upload-container">
                <div class="avatar-preview-wrapper">
                    <div id="avatarPreview" class="avatar-preview-placeholder">📷</div>
                </div>
                <input type="file" id="profile_photo" name="profile_photo" accept="image/png, image/jpeg, image/webp" class="file-input-hidden">
                <label for="profile_photo" class="btn btn-secondary btn-sm">Selecionar Foto</label>
                <div class="file-info-text">Tamanho máximo: 2MB. Apenas JPG, PNG ou WEBP.</div>
            </div>
        </div>

        <div class="form-group">
            <label for="email_disabled">E-mail Cadastrado</label>
            <input type="text" id="email_disabled" value="<?= htmlspecialchars($user['email']) ?>" disabled class="input-disabled">
        </div>

        <div class="form-group">
            <label for="password">Nova Senha Forte</label>
            <input type="password" id="password" name="password" required placeholder="Crie uma senha forte" autocomplete="new-password">
            
            <!-- Indicadores de Força da Senha -->
            <div class="password-strength-checker">
                <div class="strength-bar"><div id="strengthProgress" class="strength-progress"></div></div>
                <ul class="strength-rules">
                    <li id="rule-length" class="rule-invalid">Mínimo de 8 caracteres</li>
                    <li id="rule-upper" class="rule-invalid">Pelo menos 1 letra maiúscula</li>
                    <li id="rule-lower" class="rule-invalid">Pelo menos 1 letra minúscula</li>
                    <li id="rule-number" class="rule-invalid">Pelo menos 1 número</li>
                    <li id="rule-special" class="rule-invalid">Pelo menos 1 caractere especial (@$!%*?&)</li>
                </ul>
            </div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmar Nova Senha</label>
            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repita a senha" autocomplete="new-password">
            <div id="password-match-error" class="password-match-error"></div>
        </div>

        <button type="submit" id="btn-submit-activation" class="btn btn-primary btn-block">
            Ativar Minha Conta
        </button>
    </form>
</div>

<!-- Scripts de Validação Interativa de Senha e Preview de Avatar -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('btn-submit-activation');
    
    // Regras de força
    const ruleLength = document.getElementById('rule-length');
    const ruleUpper = document.getElementById('rule-upper');
    const ruleLower = document.getElementById('rule-lower');
    const ruleNumber = document.getElementById('rule-number');
    const ruleSpecial = document.getElementById('rule-special');
    
    const strengthProgress = document.getElementById('strengthProgress');
    const matchError = document.getElementById('password-match-error');
    
    // Preview de Imagem
    const photoInput = document.getElementById('profile_photo');
    const avatarPreview = document.getElementById('avatarPreview');

    photoInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('A foto de perfil excede o tamanho máximo de 2MB.');
                photoInput.value = '';
                avatarPreview.style.backgroundImage = 'none';
                avatarPreview.innerText = '📷';
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => {
                avatarPreview.style.backgroundImage = `url('${event.target.result}')`;
                avatarPreview.style.backgroundSize = 'cover';
                avatarPreview.style.backgroundPosition = 'center';
                avatarPreview.innerText = '';
            };
            reader.readAsDataURL(file);
        }
    });

    // Validação da Senha
    passwordInput.addEventListener('input', () => {
        const val = passwordInput.value;
        let score = 0;

        // Comprimento
        const hasLength = val.length >= 8;
        updateRule(ruleLength, hasLength);
        if (hasLength) score++;

        // Maiúscula
        const hasUpper = /[A-Z]/.test(val);
        updateRule(ruleUpper, hasUpper);
        if (hasUpper) score++;

        // Minúscula
        const hasLower = /[a-z]/.test(val);
        updateRule(ruleLower, hasLower);
        if (hasLower) score++;

        // Número
        const hasNumber = /[0-9]/.test(val);
        updateRule(ruleNumber, hasNumber);
        if (hasNumber) score++;

        // Especial
        const hasSpecial = /[\W_]/.test(val);
        updateRule(ruleSpecial, hasSpecial);
        if (hasSpecial) score++;

        // Atualiza a Barra de Progresso
        const percent = (score / 5) * 100;
        strengthProgress.style.width = percent + '%';

        if (score === 5) {
            strengthProgress.style.backgroundColor = '#10b981'; // Verde sucesso
        } else if (score >= 3) {
            strengthProgress.style.backgroundColor = '#fbbf24'; // Amarelo
        } else {
            strengthProgress.style.backgroundColor = '#ef4444'; // Vermelho
        }

        checkMatch();
    });

    confirmInput.addEventListener('input', checkMatch);

    function updateRule(element, isValid) {
        if (isValid) {
            element.classList.remove('rule-invalid');
            element.classList.add('rule-valid');
        } else {
            element.classList.remove('rule-valid');
            element.classList.add('rule-invalid');
        }
    }

    function checkMatch() {
        const pass = passwordInput.value;
        const confirm = confirmInput.value;

        if (confirm === '') {
            matchError.innerText = '';
            return;
        }

        if (pass !== confirm) {
            matchError.innerText = 'As senhas não coincidem.';
            matchError.style.color = '#ef4444';
        } else {
            matchError.innerText = 'As senhas coincidem.';
            matchError.style.color = '#10b981';
        }
    }

    // Caixa de confirmação antes de submeter o formulário
    const form = document.querySelector('.auth-form');
    form.addEventListener('submit', (e) => {
        if (!confirm('Deseja realmente ativar sua conta e cadastrar esta senha?')) {
            e.preventDefault();
        }
    });
});
</script>
