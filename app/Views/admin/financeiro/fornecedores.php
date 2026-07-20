<div class="page-header">
    <div>
        <h2>Gerenciamento de Fornecedores</h2>
        <p class="subtitle">Cadastro e consulta de credores, prestadores de serviço e gráficas da campanha.</p>
    </div>
    <div>
        <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="btn btn-secondary">
            ⬅️ Voltar ao Financeiro
        </a>
    </div>
</div>

<?php include __DIR__ . '/_nav_tabs.php'; ?>

<div class="dashboard-sections">
    
    <!-- Cadastro de Fornecedor -->
    <div class="panel-card flex-1">
        <div class="card-header">
            <h3>Cadastrar Novo Fornecedor</h3>
        </div>
        <form action="<?= $this->baseUrl('admin/financeiro/fornecedores') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="cnpj_cpf">CNPJ ou CPF (Apenas números ou formatado)</label>
                <input type="text" id="cnpj_cpf" name="cnpj_cpf" placeholder="00.000.000/0001-00 ou 000.000.000-00" required>
                <div id="cnpj_fornecedor_status" style="margin-top: 6px; font-size: 11px;"></div>
            </div>

            <div class="form-group">
                <label for="corporate_name">Razão Social / Nome do Fornecedor</label>
                <input type="text" id="corporate_name" name="corporate_name" placeholder="Ex: Gráfica Alvorada Ltda" required>
            </div>

            <div class="form-group">
                <label for="trade_name">Nome Fantasia (Opcional)</label>
                <input type="text" id="trade_name" name="trade_name" placeholder="Ex: Gráfica Alvorada">
            </div>

            <div class="form-group">
                <label for="address">Endereço Completo</label>
                <input type="text" id="address" name="address" placeholder="Rua, Número, Bairro, Cidade - UF">
            </div>

            <div style="display: flex; gap: 16px;">
                <div class="form-group flex-1">
                    <label for="phone">Telefone de Contato</label>
                    <input type="text" id="phone" name="phone" placeholder="(41) 99999-9999">
                </div>
                <div class="form-group flex-1">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="fornecedor@email.com">
                </div>
            </div>

            <button type="submit" class="btn btn-teal btn-block" style="margin-top: 10px;">
                💾 Salvar Fornecedor
            </button>
        </form>
    </div>

    <!-- Lista de Fornecedores -->
    <div class="panel-card flex-1">
        <div class="card-header">
            <h3>Fornecedores Cadastrados</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>CNPJ/CPF</th>
                        <th>Razão Social / Fantasia</th>
                        <th>Contato</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($suppliers)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary);">Nenhum fornecedor cadastrado ainda.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($suppliers as $sup): ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: 500;">
                                    <?= htmlspecialchars($sup['cnpj_cpf']) ?>
                                </td>
                                <td>
                                    <div class="user-log-name"><?= htmlspecialchars($sup['corporate_name']) ?></div>
                                    <?php if (!empty($sup['trade_name'])): ?>
                                        <span style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($sup['trade_name']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 12px;"><?= htmlspecialchars($sup['phone'] ?? '-') ?></div>
                                    <div style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($sup['email'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <?php if ($sup['status'] === 'ATIVO'): ?>
                                        <span class="badge badge-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inativo</span>
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

<script>
    const cnpjCpfInput = document.getElementById('cnpj_cpf');
    const statusDiv = document.getElementById('cnpj_fornecedor_status');
    const corpNameInput = document.getElementById('corporate_name');
    const tradeNameInput = document.getElementById('trade_name');
    const addressInput = document.getElementById('address');

    // Máscara dinâmica CNPJ/CPF e consulta de CNPJ
    if (cnpjCpfInput) {
        cnpjCpfInput.addEventListener('input', function (e) {
            var value = e.target.value.replace(/\D/g, "");
            if (value.length > 14) value = value.slice(0, 14);

            if (value.length > 11) {
                // CNPJ Mask
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
            } else if (value.length > 9) {
                // CPF Mask
                value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4");
            } else if (value.length > 6) {
                value = value.replace(/^(\d{3})(\d{3})(\d{1,3})$/, "$1.$2.$3");
            } else if (value.length > 3) {
                value = value.replace(/^(\d{3})(\d{1,3})$/, "$1.$2");
            }
            e.target.value = value;

            const cleanDigits = value.replace(/\D/g, "");
            if (cleanDigits.length === 14) {
                if (statusDiv) {
                    statusDiv.innerHTML = '<span style="color: var(--accent-teal); font-weight: 500;">🔍 Buscando Razão Social na Receita Federal...</span>';
                }
                
                fetch('<?= $this->baseUrl("api/cnpj/consultar") ?>?cnpj=' + cleanDigits)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (corpNameInput && !corpNameInput.value) corpNameInput.value = data.razao_social;
                            if (tradeNameInput && !tradeNameInput.value && data.nome_fantasia) tradeNameInput.value = data.nome_fantasia;
                            if (addressInput && !addressInput.value && data.endereco_completo) addressInput.value = data.endereco_completo;
                            
                            if (statusDiv) {
                                statusDiv.innerHTML = `<span style="color: #22c55e; font-weight: 600;">✔ Dados Carregados (${data.fonte}): ${data.razao_social}</span>`;
                            }
                        } else {
                            if (statusDiv) {
                                statusDiv.innerHTML = `<span style="color: #ef4444;">⚠️ ${data.message || 'CNPJ não encontrado'}</span>`;
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Erro na consulta do CNPJ:', err);
                        if (statusDiv) statusDiv.innerHTML = '<span style="color: #ef4444;">⚠️ Erro ao consultar Receita Federal</span>';
                    });
            } else {
                if (statusDiv) statusDiv.innerHTML = '';
            }
        });
    }
</script>
