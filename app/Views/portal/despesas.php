<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px;">Meus Gastos</h2>
    <p class="subtitle" style="font-size: 12px;">Lance e acompanhe suas despesas de campo pendentes de vinculação financeira/fiscal.</p>
</div>

<!-- Cards de Resumo Pessoal -->
<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 24px;">
    <div class="panel-card" style="padding: 12px; margin-bottom: 0; text-align: center; background: rgba(30, 41, 59, 0.4);">
        <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Total Lançado</span>
        <span style="font-size: 14px; font-weight: bold; color: var(--text-primary);">R$ <?= number_format($totalLaunched, 2, ',', '.') ?></span>
    </div>
    <div class="panel-card" style="padding: 12px; margin-bottom: 0; text-align: center; background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.2);">
        <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Aguardando Vínculo</span>
        <span style="font-size: 14px; font-weight: bold; color: var(--warning-color);"><?= $pendingCount ?></span>
    </div>
    <div class="panel-card" style="padding: 12px; margin-bottom: 0; text-align: center; background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2);">
        <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Aprovados</span>
        <span style="font-size: 14px; font-weight: bold; color: var(--success-color);"><?= $approvedCount ?></span>
    </div>
</div>

<!-- Formulário de Lançamento -->
<div class="panel-card" style="padding: 16px; margin-bottom: 24px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
        <h3 style="font-size: 14px; font-weight: 600;">Lançar Novo Gasto</h3>
    </div>

    <form action="<?= $this->baseUrl('portal/despesas') ?>" method="POST" enctype="multipart/form-data" id="expenseForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="form-group">
            <label for="expense_type_id">Tipo de Gasto</label>
            <select id="expense_type_id" name="expense_type_id" required style="width: 100%; border-radius: 6px; padding: 10px; background: #0f172a; border: 1px solid #334155; color: #fff; box-sizing: border-box; font-family: inherit; font-size: 13px;">
                <option value="">-- Selecione o Tipo de Gasto --</option>
                <?php foreach ($expenseTypes as $type): ?>
                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Finalidade do Gasto / Descrição</label>
            <textarea id="description" name="description" rows="2" placeholder="Descreva para que foi feito o gasto (Ex: Alimentação da equipe de panfletagem no almoço)..." required></textarea>
        </div>

        <div class="form-group">
            <label for="supplier_cnpj_cpf">CPF ou CNPJ do Fornecedor</label>
            <input type="text" id="supplier_cnpj_cpf" name="supplier_cnpj_cpf" placeholder="00.000.000/0001-00 ou 000.000.000-00" required>
        </div>

        <div class="form-group">
            <label for="supplier_name">Razão Social ou Nome do Fornecedor</label>
            <input type="text" id="supplier_name" name="supplier_name" placeholder="Ex: Auto Posto Silva Ltda" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
                <label for="value">Valor (R$)</label>
                <input type="text" id="value" name="value" placeholder="R$ 0,00" required style="font-weight: 600; color: var(--warning-color);">
            </div>
            <div class="form-group">
                <label for="date_incurred">Data do Gasto</label>
                <input type="date" id="date_incurred" name="date_incurred" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <!-- Seleção de Comprovante com Thumbnail Automático -->
        <div class="form-group" style="margin-bottom: 16px;">
            <label for="comprovante">Foto ou PDF do Comprovante / Cupom Fiscal</label>
            <input type="file" id="comprovante" name="comprovante" accept="image/*,application/pdf" required style="padding: 4px; font-size: 12px; margin-bottom: 10px;">
            
            <!-- Preview da foto/arquivo logo abaixo -->
            <div id="preview-container" style="display: none; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); background-color: #0c1524; text-align: center; padding: 12px; position: relative;">
                <img id="preview-image" src="" alt="Comprovante" style="max-width: 100%; max-height: 180px; object-fit: contain; display: none; margin: 0 auto; border-radius: 8px;">
                <div id="pdf-preview-text" style="display: none; font-size: 12px; color: var(--text-secondary); padding: 16px 0;">
                    <span style="font-size: 32px; display: block; margin-bottom: 6px;">📄</span>
                    Arquivo PDF Selecionado
                </div>
                <div style="font-size: 10px; color: var(--text-secondary); margin-top: 6px;" id="file-name-preview"></div>
            </div>
        </div>

        <button type="submit" id="submitBtn" class="btn btn-teal btn-block" style="margin-top: 10px;">
            Confirmar e Enviar Despesa
        </button>
    </form>
</div>

<!-- Histórico de Lançamentos -->
<div class="panel-card" style="padding: 16px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
        <h3 style="font-size: 14px; font-weight: 600;">Histórico de Gastos Enviados</h3>
    </div>

    <?php if (empty($expenses)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhum gasto lançado até o momento.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($expenses as $exp): ?>
                <div style="padding: 12px; background: rgba(15, 23, 42, 0.4); border-radius: 10px; border: 1px solid rgba(255,255,255,0.04); font-size: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-weight: 600; color: var(--text-primary);"><?= date('d/m/Y', strtotime($exp['date_incurred'])) ?></span>
                        <div>
                            <?php if ($exp['status'] === 'APROVADO' || $exp['status'] === 'PAGO'): ?>
                                <span class="badge badge-success">Homologada</span>
                            <?php elseif ($exp['status'] === 'REJEITADO'): ?>
                                <span class="badge badge-danger" title="<?= htmlspecialchars($exp['notes'] ?? 'Sem justificativa') ?>">Recusada</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pendente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p style="color: var(--text-primary); font-weight: 500; margin-bottom: 4px;">
                        <span class="badge badge-secondary" style="font-size: 10px; padding: 2px 6px; background-color: #334155; color: var(--text-primary); border-radius: 4px; margin-right: 6px;"><?= htmlspecialchars($exp['expense_type_name'] ?? 'Sem Tipo') ?></span>
                        <?= htmlspecialchars($exp['description']) ?>
                    </p>
                    <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-secondary); font-size: 11px;">
                        <span>Fornecedor: <?= htmlspecialchars($exp['supplier_name']) ?></span>
                        <span style="font-weight: 600; color: var(--warning-color);">R$ <?= number_format($exp['value'], 2, ',', '.') ?></span>
                    </div>
                    <?php if ($exp['status'] === 'REJEITADO' && !empty($exp['notes'])): ?>
                        <div style="margin-top: 8px; padding: 8px; background-color: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 6px; color: var(--error-color); font-size: 11px;">
                            <strong>Motivo da rejeição:</strong> <?= htmlspecialchars($exp['notes']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputCnpjCpf = document.getElementById('supplier_cnpj_cpf');
    const inputValue = document.getElementById('value');
    const inputComprovante = document.getElementById('comprovante');
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');
    const pdfPreviewText = document.getElementById('pdf-preview-text');
    const fileNamePreview = document.getElementById('file-name-preview');

    // Máscara dinâmica CPF / CNPJ
    inputCnpjCpf.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else {
            value = value.slice(0, 14);
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
        }
        e.target.value = value;
    });

    // Máscara dinâmica Moeda BRL
    inputValue.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (!value) {
            e.target.value = '';
            return;
        }
        value = (parseFloat(value) / 100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        e.target.value = 'R$ ' + value;
    });

    // Preview do Comprovante selecionado
    inputComprovante.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) {
            previewContainer.style.display = 'none';
            return;
        }

        previewContainer.style.display = 'block';
        fileNamePreview.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;

        if (file.type.match('image.*')) {
            const reader = new FileReader();
            reader.onload = (event) => {
                previewImage.src = event.target.result;
                previewImage.style.display = 'block';
                pdfPreviewText.style.display = 'none';
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            previewImage.style.display = 'none';
            pdfPreviewText.style.display = 'block';
        } else {
            previewImage.style.display = 'none';
            pdfPreviewText.style.display = 'block';
            pdfPreviewText.innerHTML = '<span style="font-size: 32px; display: block; margin-bottom: 6px;">📄</span>Arquivo selecionado';
        }
    });
});
</script>
