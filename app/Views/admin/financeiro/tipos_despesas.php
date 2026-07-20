<div class="page-header">
    <div>
        <h2>Tipos de Despesas de Campo</h2>
        <p class="subtitle">Gerencie os tipos de despesas que os colaboradores de campo podem selecionar ao lançar novos gastos.</p>
    </div>
    <div>
        <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="btn btn-secondary">
            ⬅️ Voltar ao Financeiro
        </a>
    </div>
</div>

<?php include __DIR__ . '/_nav_tabs.php'; ?>

<div class="dashboard-sections">
    
    <!-- Formulário Cadastro/Edição -->
    <div class="panel-card flex-1">
        <div class="card-header">
            <h3 id="form-title">Cadastrar Novo Tipo</h3>
        </div>
        <form id="expense-type-form" action="<?= $this->baseUrl('admin/financeiro/tipos-despesas') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="type-id" name="id" value="">

            <div class="form-group">
                <label for="type-name">Nome do Tipo</label>
                <input type="text" id="type-name" name="name" placeholder="Ex: Hospedagem, Alimentação..." required>
            </div>

            <div class="form-group">
                <label for="type-description">Descrição (Opcional)</label>
                <textarea id="type-description" name="description" placeholder="Descreva brevemente para que serve este tipo de despesa..." rows="4" style="width: 100%; border-radius: 6px; padding: 10px; background: #0f172a; border: 1px solid #334155; color: #fff; box-sizing: border-box; resize: vertical; font-family: inherit; font-size: 13px;"></textarea>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-teal flex-1">
                    💾 Salvar Tipo
                </button>
                <button type="button" id="btn-cancel-edit" class="btn btn-secondary" style="display: none;" onclick="resetForm()">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Tipos Existentes -->
    <div class="panel-card flex-1">
        <div class="card-header">
            <h3>Tipos Cadastrados</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th style="width: 150px; text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($types)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 20px;">Nenhum tipo de despesa cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($types as $t): ?>
                            <tr id="type-row-<?= $t['id'] ?>">
                                <td><?= $t['id'] ?></td>
                                <td style="font-weight: 600; color: var(--text-primary);" class="row-name"><?= htmlspecialchars($t['name']) ?></td>
                                <td style="font-size: 12px; color: var(--text-secondary);" class="row-desc"><?= htmlspecialchars($t['description'] ?? '-') ?></td>
                                <td>
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="editType(<?= $t['id'] ?>, '<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($t['description'] ?? '', ENT_QUOTES) ?>')" style="padding: 4px 8px; font-size: 11px;">
                                            ✏️
                                        </button>
                                        <form action="<?= $this->baseUrl('admin/financeiro/tipos-despesas/excluir') ?>" method="POST" onsubmit="return confirmarExclusao('<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>');" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                            <button type="submit" class="btn btn-secondary btn-sm btn-danger-hover" style="padding: 4px 8px; font-size: 11px;">
                                                🗑️
                                            </button>
                                        </form>
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
function editType(id, name, description) {
    document.getElementById('form-title').innerText = 'Editar Tipo de Despesa';
    document.getElementById('type-id').value = id;
    document.getElementById('type-name').value = name;
    document.getElementById('type-description').value = description;
    
    // Altera a action do form para editar
    document.getElementById('expense-type-form').action = '<?= $this->baseUrl('admin/financeiro/tipos-despesas/editar') ?>';
    
    // Exibe o botão de cancelar
    document.getElementById('btn-cancel-edit').style.display = 'inline-block';
    
    // Efeito visual na linha selecionada
    document.querySelectorAll('tbody tr').forEach(tr => tr.style.background = 'none');
    document.getElementById('type-row-' + id).style.background = 'rgba(20, 184, 166, 0.1)';
    
    // Rola suavemente até o formulário
    document.getElementById('expense-type-form').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Cadastrar Novo Tipo';
    document.getElementById('type-id').value = '';
    document.getElementById('type-name').value = '';
    document.getElementById('type-description').value = '';
    
    // Volta a action do form para cadastrar
    document.getElementById('expense-type-form').action = '<?= $this->baseUrl('admin/financeiro/tipos-despesas') ?>';
    
    // Oculta o botão de cancelar
    document.getElementById('btn-cancel-edit').style.display = 'none';
    
    // Limpa destaque de linha
    document.querySelectorAll('tbody tr').forEach(tr => tr.style.background = 'none');
}

function confirmarExclusao(name) {
    return confirm("Deseja realmente excluir o tipo de despesa '" + name + "'?\nRegistros de despesas já existentes manterão seus valores mas ficarão sem categoria vinculada.");
}
</script>
