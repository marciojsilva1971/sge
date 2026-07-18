<!-- Formulário de Login Seguro -->
<div class="auth-card">
    <h3>Acesse sua Conta</h3>
    <p class="card-subtitle">Insira suas credenciais cadastradas abaixo</p>

    <!-- Alertas Locais caso existam -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <span class="alert-icon">⚠</span>
            <span class="alert-message"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✓</span>
            <span class="alert-message"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= $this->baseUrl('login') ?>" method="POST" class="auth-form">
        <!-- Token CSRF Obrigatório -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="form-group">
            <label for="email">E-mail Corporativo</label>
            <input type="email" id="email" name="email" required placeholder="exemplo@campanha.com.br" autocomplete="username">
        </div>

        <div class="form-group">
            <label for="password">Senha de Acesso</label>
            <input type="password" id="password" name="password" required placeholder="Digite sua senha" autocomplete="current-password">
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Entrar no Sistema
        </button>
    </form>
</div>
