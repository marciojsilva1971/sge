<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px;">Meus Gastos</h2>
    <p class="subtitle" style="font-size: 12px;">Lance e acompanhe suas despesas de campo pendentes de vinculação financeira/fiscal.</p>
</div>

<!-- MODAL DE CONFIRMAÇÃO PÓS-CORREÇÃO DE DESPESA -->
<?php if (isset($_GET['envio_sucesso']) && $_GET['envio_sucesso'] == '1'): ?>
<div id="modalSucessoEnvio" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 2px solid #22c55e; border-radius: 16px; max-width: 450px; width: 100%; padding: 24px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <div style="width: 60px; height: 60px; background: rgba(34, 197, 94, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; color: #4ade80; font-size: 32px; font-weight: bold;">
            ✓
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: #f8fafc; margin-bottom: 8px;">Gasto / Correção Enviado com Sucesso!</h3>
        <p style="font-size: 13px; color: #94a3b8; margin-bottom: 20px; line-height: 1.5;">
            Seu registro foi enviado com sucesso para a Fila de Aprovação. Deseja lançar um novo gasto ou acompanhar o status no histórico abaixo?
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="<?= $this->baseUrl('portal/militancia') ?>" style="background: #22c55e; color: #0f172a; font-weight: 800; padding: 12px; border-radius: 10px; text-decoration: none; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                ➕ Cadastrar um Novo Gasto / Comprovante
            </a>
            <button type="button" onclick="fecharModalSucessoDespesas()" style="background: rgba(255, 255, 255, 0.08); color: #f8fafc; font-weight: 600; padding: 12px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.15); text-decoration: none; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                📊 Fechar e Acompanhar no Histórico
            </button>
        </div>
    </div>
</div>
<script>
    function fecharModalSucessoDespesas() {
        const modal = document.getElementById('modalSucessoEnvio');
        if (modal) modal.style.display = 'none';
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
    }
</script>
<?php endif; ?>

<!-- Cards de Resumo Pessoal (4 Colunas Interativas) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-bottom: 24px;">
    <div class="panel-card card-stat-clicavel" onclick="clicarCardResumo('TODOS')" title="Clique para ver todos os gastos" style="padding: 12px; margin-bottom: 0; text-align: center; background: rgba(30, 41, 59, 0.4); cursor: pointer; transition: all 0.2s ease;">
        <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Total Lançado</span>
        <span style="font-size: 13px; font-weight: bold; color: var(--text-primary);">R$ <?= number_format($totalLaunched, 2, ',', '.') ?></span>
    </div>
    <div class="panel-card card-stat-clicavel" onclick="clicarCardResumo('PENDENTE')" title="Clique para ver gastos aguardando vínculo" style="padding: 12px; margin-bottom: 0; text-align: center; background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.2); cursor: pointer; transition: all 0.2s ease;">
        <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Aguardando Vínculo</span>
        <span style="font-size: 14px; font-weight: bold; color: var(--warning-color);"><?= $pendingCount ?></span>
    </div>
    <div class="panel-card card-stat-clicavel" onclick="clicarCardResumo('APROVADO')" title="Clique para ver gastos aprovados" style="padding: 12px; margin-bottom: 0; text-align: center; background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2); cursor: pointer; transition: all 0.2s ease;">
        <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Aprovados</span>
        <span style="font-size: 14px; font-weight: bold; color: var(--success-color);"><?= $approvedCount ?></span>
    </div>
    <div class="panel-card card-stat-clicavel" onclick="clicarCardResumo('REPROVADO')" title="Clique para ver gastos reprovados que exigem correção" style="padding: 12px; margin-bottom: 0; text-align: center; background: rgba(239, 68, 68, 0.08); border-color: rgba(239, 68, 68, 0.3); cursor: pointer; transition: all 0.2s ease;">
        <span style="font-size: 11px; color: #fca5a5; display: block; margin-bottom: 4px;">Reprovados (A Corrigir)</span>
        <span style="font-size: 14px; font-weight: bold; color: #ef4444;"><?= $rejectedCount ?? 0 ?></span>
    </div>
</div>

<!-- Histórico de Lançamentos com Abas de Filtro -->
<div id="historico-gastos-container" class="panel-card" style="padding: 16px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <h3 style="font-size: 14px; font-weight: 600; margin: 0;">Histórico de Gastos Enviados</h3>
        
        <!-- Abas de Filtro de Status -->
        <div style="display: flex; gap: 6px; font-size: 11px;">
            <button type="button" class="btn-aba-filtro active" onclick="filtrarGastosPortal('TODOS', this)" style="padding: 4px 10px; border-radius: 20px; background: #3b82f6; color: #fff; border: none; cursor: pointer; font-weight: 600;">
                📋 Todos (<?= count($expenses) ?>)
            </button>
            <button type="button" class="btn-aba-filtro" onclick="filtrarGastosPortal('PENDENTE', this)" style="padding: 4px 10px; border-radius: 20px; background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
                ⏳ Pendentes (<?= $pendingCount ?>)
            </button>
            <button type="button" class="btn-aba-filtro" onclick="filtrarGastosPortal('APROVADO', this)" style="padding: 4px 10px; border-radius: 20px; background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
                ✅ Aprovados (<?= $approvedCount ?>)
            </button>
            <button type="button" class="btn-aba-filtro" onclick="filtrarGastosPortal('REJEITADO', this)" style="padding: 4px 10px; border-radius: 20px; background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); cursor: pointer; font-weight: 600;">
                ❌ Reprovados (<?= $rejectedCount ?? 0 ?>)
            </button>
        </div>
    </div>

    <?php if (empty($expenses)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhum gasto lançado até o momento.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($expenses as $exp): ?>
                <div class="item-gasto-portal" data-status="<?= htmlspecialchars($exp['status']) ?>" style="padding: 14px; background: <?= $exp['status'] === 'REJEITADO' ? 'rgba(239, 68, 68, 0.08)' : 'rgba(15, 23, 42, 0.4)' ?>; border-radius: 10px; border: 1px solid <?= $exp['status'] === 'REJEITADO' ? 'rgba(239, 68, 68, 0.3)' : 'rgba(255,255,255,0.04)' ?>; font-size: 12px; transition: all 0.2s;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-weight: 600; color: var(--text-primary);"><?= date('d/m/Y', strtotime($exp['date_incurred'])) ?></span>
                        <div>
                            <?php if ($exp['status'] === 'APROVADO' || $exp['status'] === 'PAGO'): ?>
                                <span class="badge badge-success">Homologada</span>
                            <?php elseif ($exp['status'] === 'REJEITADO'): ?>
                                <span class="badge badge-danger" style="background: #ef4444; color: #fff;">❌ Reprovada</span>
                            <?php else: ?>
                                <span class="badge badge-warning">⏳ Pendente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <p style="color: var(--text-primary); font-weight: 600; margin-bottom: 4px; font-size: 13px;">
                        <span class="badge badge-secondary" style="font-size: 10px; padding: 2px 6px; background-color: #334155; color: var(--text-primary); border-radius: 4px; margin-right: 6px;"><?= htmlspecialchars($exp['expense_type_name'] ?? 'Gasto Geral') ?></span>
                        <?= htmlspecialchars($exp['description']) ?>
                    </p>

                    <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-secondary); font-size: 11px; margin-bottom: 6px;">
                        <?php 
                        $digitsCnpjPortal = preg_replace('/\D/', '', $exp['supplier_cnpj_cpf'] ?? '');
                        $fmtCnpjPortal = (strlen($digitsCnpjPortal) === 14) 
                            ? preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $digitsCnpjPortal) 
                            : ((strlen($digitsCnpjPortal) === 11) ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $digitsCnpjPortal) : ($exp['supplier_cnpj_cpf'] ?? 'Sem documento'));
                        ?>
                        <span>Fornecedor: <strong><?= htmlspecialchars($exp['supplier_name']) ?></strong> (<?= htmlspecialchars($fmtCnpjPortal) ?>)</span>
                        <span style="font-weight: 700; color: var(--warning-color); font-size: 13px;">R$ <?= number_format($exp['value'], 2, ',', '.') ?></span>
                    </div>

                    <?php if ($exp['status'] === 'REJEITADO'): ?>
                        <div style="margin-top: 8px; padding: 10px 12px; background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; color: #fca5a5; font-size: 12px; line-height: 1.4;">
                            <strong style="color: #ef4444; font-size: 12px; display: block; margin-bottom: 2px;">⚠️ Motivo da Reprovação pelo Financeiro/Administração:</strong>
                            <?= htmlspecialchars($exp['notes'] ?? 'Nenhuma observação detalhada foi fornecida.') ?>
                        </div>

                        <div style="margin-top: 10px; text-align: right;">
                            <button type="button" onclick='abrirModalCorrigirGasto(<?= json_encode($exp, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="background: #eab308; color: #0f172a; font-weight: 800; border: none; padding: 8px 14px; border-radius: 8px; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                ✏️ Corrigir e Reenviar Gasto
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL DE CORREÇÃO DE GASTO REPROVADO PELO COLABORADOR -->
<div id="modalCorrigirGasto" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 99999; display: none; align-items: center; justify-content: center; padding: 16px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 2px solid #eab308; border-radius: 16px; max-width: 520px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #fde047; margin: 0; display: flex; align-items: center; gap: 8px;">
                ✏️ Corrigir e Reenviar Gasto
            </h3>
            <button type="button" onclick="document.getElementById('modalCorrigirGasto').style.display='none';" style="background: transparent; border: none; color: #94a3b8; font-size: 18px; cursor: pointer; font-weight: bold;">✕</button>
        </div>

        <div id="alertaMotivoReprovacaoModal" style="background: rgba(239, 68, 68, 0.15); border-left: 4px solid #ef4444; padding: 10px; border-radius: 6px; margin-bottom: 14px; font-size: 12px; color: #fca5a5;">
            <strong>Motivo informado:</strong> <span id="textoMotivoReprovacaoModal"></span>
        </div>

        <form action="<?= $this->baseUrl('portal/despesas/corrigir') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="edit_expense_id" name="expense_id" value="">

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 600;">Descrição do Gasto</label>
                <input type="text" id="edit_description" name="description" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">CPF/CNPJ Fornecedor</label>
                    <input type="text" id="edit_supplier_cnpj_cpf" name="supplier_cnpj_cpf" oninput="formatarCnpjCpf(this)" maxlength="18" placeholder="00.000.000/0001-00" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Razão Social Fornecedor</label>
                    <input type="text" id="edit_supplier_name" name="supplier_name" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Valor (R$)</label>
                    <input type="text" id="edit_value" name="value" onkeyup="formatarMoeda(this)" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px; font-weight: bold;">
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Data do Gasto</label>
                    <input type="date" id="edit_date_incurred" name="date_incurred" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600;">Tipo de Despesa</label>
                <select id="edit_expense_type_id" name="expense_type_id" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                    <option value="">-- Selecione o Tipo --</option>
                    <?php foreach ($expenseTypes as $type): ?>
                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 16px; background: rgba(56, 189, 248, 0.08); padding: 10px; border-radius: 8px; border: 1px dashed #38bdf8;">
                <label style="font-size: 12px; font-weight: 700; color: #38bdf8; display: block; margin-bottom: 4px;">
                    📸 Anexar Novo Comprovante / Cupom (Opcional)
                </label>
                <input type="file" name="novo_comprovante" accept="image/*, application/pdf" style="font-size: 11px; color: #cbd5e1;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('modalCorrigirGasto').style.display='none';" style="background: #334155; color: #fff; border: none; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <button type="submit" style="background: #22c55e; color: #0f172a; border: none; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer;">
                    🚀 Reenviar para Aprovação
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
    function formatarCnpjCpf(input) {
        let value = input.value.replace(/\D/g, "");
        if (value.length > 14) {
            value = value.substring(0, 14);
        }
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        } else {
            value = value.replace(/^(\d{2})(\d)/, "$1.$2");
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
            value = value.replace(/\.(\d{3})(\d)/, ".$1/$2");
            value = value.replace(/(\d{4})(\d)/, "$1-$2");
        }
        input.value = value;
    }

    function formatarMoeda(input) {
        let value = input.value.replace(/\D/g, "");
        if (value === "") {
            input.value = "";
            return;
        }
        value = (parseFloat(value) / 100).toFixed(2);
        let formatted = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
        input.value = formatted;
    }

document.addEventListener('DOMContentLoaded', () => {
    const inputCnpjCpf = document.getElementById('supplier_cnpj_cpf');
    const inputSupplierName = document.getElementById('supplier_name');
    const cnpjInfoDiv = document.getElementById('cnpj_supplier_info');
    const ocrStatusBadge = document.getElementById('ocr_status_badge');
    const inputValue = document.getElementById('value');
    const fotoCnpjInput = document.getElementById('foto_cnpj_ocr');
    const btnScanOcr = document.getElementById('btn-scan-ocr');
    const btnPularOcr = document.getElementById('btn-pular-ocr');
    const dadosContainer = document.getElementById('dados-despesa-container');
    const blocoCapturaCnpj = document.getElementById('bloco-captura-cnpj');
    const inputComprovante = document.getElementById('comprovante');
    const badgeComprovante = document.getElementById('comprovante-count-badge');

    function revelarEtapa2() {
        if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
        if (dadosContainer) {
            dadosContainer.style.display = 'block';
            dadosContainer.scrollIntoView({ behavior: 'smooth' });
        }
    }

    if (btnPularOcr) {
        btnPularOcr.addEventListener('click', function() {
            revelarEtapa2();
        });
    }

    // Acumulador de arquivos via DataTransfer API (evita substituição ao reabrir o seletor)
    const dataTransferComprovantes = new DataTransfer();

    function renderizarGaleriaMiniaturas() {
        const galeriaContainer = document.getElementById('galeria-miniaturas-container');
        if (!inputComprovante || !galeriaContainer) return;

        // Atualiza o elemento input file com os arquivos acumulados
        inputComprovante.files = dataTransferComprovantes.files;

        const count = dataTransferComprovantes.files.length;
        if (count > 0) {
            if (badgeComprovante) {
                badgeComprovante.style.display = 'block';
                badgeComprovante.textContent = `✔ ${count} foto(s)/comprovante(s) anexado(s) e pronto(s) para envio.`;
            }
            galeriaContainer.style.display = 'grid';
            galeriaContainer.innerHTML = '';

            Array.from(dataTransferComprovantes.files).forEach((file, index) => {
                const card = document.createElement('div');
                card.style.cssText = 'position: relative; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 6px; display: flex; flex-direction: column; align-items: center; justify-content: space-between; gap: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3);';

                // Botão de remoção (X)
                const btnRemove = document.createElement('button');
                btnRemove.type = 'button';
                btnRemove.innerHTML = '✖';
                btnRemove.title = 'Remover esta foto';
                btnRemove.style.cssText = 'position: absolute; top: -6px; right: -6px; background: #ef4444; color: #fff; border: none; width: 20px; height: 20px; border-radius: 50%; font-size: 10px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.5);';
                btnRemove.onclick = function(e) {
                    e.stopPropagation();
                    removerArquivoDaGaleria(index);
                };

                // Preview Thumbnail
                const thumbDiv = document.createElement('div');
                thumbDiv.style.cssText = 'width: 100%; height: 70px; border-radius: 6px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; overflow: hidden; background-size: cover; background-position: center;';

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        thumbDiv.style.backgroundImage = "url('" + evt.target.result + "')";
                    };
                    reader.readAsDataURL(file);
                } else {
                    thumbDiv.innerHTML = '<span style="font-size: 24px;">📄</span>';
                }

                // Nome e Tamanho
                const infoDiv = document.createElement('div');
                infoDiv.style.cssText = 'width: 100%; text-align: center; font-size: 10px; color: #cbd5e1; word-break: break-all; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; margin-top: 2px;';
                infoDiv.textContent = file.name;

                const sizeDiv = document.createElement('div');
                sizeDiv.style.cssText = 'font-size: 9px; color: #64748b; font-weight: 600;';
                sizeDiv.textContent = (file.size / 1024).toFixed(1) + ' KB';

                card.appendChild(btnRemove);
                card.appendChild(thumbDiv);
                card.appendChild(infoDiv);
                card.appendChild(sizeDiv);

                galeriaContainer.appendChild(card);
            });
        } else {
            if (badgeComprovante) badgeComprovante.style.display = 'none';
            galeriaContainer.style.display = 'none';
            galeriaContainer.innerHTML = '';
        }
    }

    function removerArquivoDaGaleria(index) {
        const dt = new DataTransfer();
        Array.from(dataTransferComprovantes.files).forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        dataTransferComprovantes.items.clear();
        Array.from(dt.files).forEach(f => dataTransferComprovantes.items.add(f));

        renderizarGaleriaMiniaturas();
    }

    if (inputComprovante) {
        inputComprovante.addEventListener('change', function(e) {
            if (e.target.files && e.target.files.length > 0) {
                Array.from(e.target.files).forEach(file => {
                    dataTransferComprovantes.items.add(file);
                });
                renderizarGaleriaMiniaturas();
            }
        });
    }

    function consultarCnpj(cleanCnpj) {
        if (cleanCnpj.length !== 14) return;
        if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = '<span style="color: var(--accent-teal); font-weight: 500;">🔍 Buscando Razão Social na Receita Federal...</span>';

        fetch('<?= $this->baseUrl("api/cnpj/consultar") ?>?cnpj=' + cleanCnpj)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (inputSupplierName) inputSupplierName.value = data.razao_social;
                    if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = `<span style="color: #22c55e; font-weight: 600;">✔ ${data.razao_social}</span> <span style="color: #94a3b8;">(${data.municipio}/${data.uf})</span>`;
                } else {
                    if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = `<span style="color: #ef4444;">⚠️ ${data.message || 'CNPJ não encontrado'}</span>`;
                }
            })
            .catch(err => {
                console.error(err);
                if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = '<span style="color: #ef4444;">⚠️ Erro ao consultar Receita Federal</span>';
            });
    }

    // Máscara dinâmica CPF / CNPJ e consulta automática
    if (inputCnpjCpf) {
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

            const clean = value.replace(/\D/g, '');
            if (clean.length === 14) {
                consultarCnpj(clean);
            } else {
                if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = '';
            }
        });
    }

    // Máscara dinâmica Moeda BRL
    if (inputValue) {
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
    }

    // Validador matemático oficial do Digito Verificador de CNPJ (Módulo 11)
    function validarCNPJ(cnpj) {
        cnpj = String(cnpj || '').replace(/[^\d]+/g, '');
        if (cnpj.length !== 14) return false;
        if (/^(\d)\1+$/.test(cnpj)) return false;

        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (resultado != digitos.charAt(0)) return false;

        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (resultado != digitos.charAt(1)) return false;

        return true;
    }

    // Extrator inteligente de CNPJ com tolerância a ruídos comuns de OCR
    function extrairCNPJDoTexto(text) {
        if (!text) return null;

        const matches = text.match(/(?:CNPJ|C\.N\.P\.J\.?|MF)?[\s\:\.\-\/]*([0-9OolI|sS\.\-\/\s]{14,25})/gi) || [];
        for (let raw of matches) {
            let clean = raw.replace(/[Oo]/g, '0').replace(/[Il|]/g, '1').replace(/[sS]/g, '5').replace(/\D/g, '');
            for (let i = 0; i <= clean.length - 14; i++) {
                let sub = clean.substring(i, i + 14);
                if (validarCNPJ(sub)) return sub;
            }
        }

        const apenasNumeros = text.replace(/[Oo]/g, '0').replace(/[Il|]/g, '1').replace(/[sS]/g, '5').replace(/\D/g, ' ');
        const tokens = apenasNumeros.split(/\s+/);
        for (let token of tokens) {
            if (token.length === 14 && validarCNPJ(token)) {
                return token;
            }
        }

        const todosDigitos = text.replace(/[Oo]/g, '0').replace(/[Il|]/g, '1').replace(/[sS]/g, '5').replace(/\D/g, '');
        for (let i = 0; i <= todosDigitos.length - 14; i++) {
            let sub = todosDigitos.substring(i, i + 14);
            if (validarCNPJ(sub)) return sub;
        }

        return null;
    }

    // Otimizador de nitidez/contraste em Canvas HTML5 para comprovantes fiscais
    function otimizarImagemParaOCR(file, callback) {
        if (!file || !file.type.startsWith('image/')) {
            callback(file);
            return;
        }
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = function() {
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                let maxDim = 1800;
                let width = img.width;
                let height = img.height;
                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                const imgData = ctx.getImageData(0, 0, width, height);
                const d = imgData.data;
                for (let i = 0; i < d.length; i += 4) {
                    let gray = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
                    gray = gray < 135 ? Math.max(0, gray - 35) : Math.min(255, gray + 35);
                    d[i] = gray;
                    d[i + 1] = gray;
                    d[i + 2] = gray;
                }
                ctx.putImageData(imgData, 0, 0);

                canvas.toBlob(function(blob) {
                    URL.revokeObjectURL(url);
                    callback(blob || file);
                }, 'image/jpeg', 0.92);
            } catch (e) {
                URL.revokeObjectURL(url);
                callback(file);
            }
        };
        img.onerror = function() {
            URL.revokeObjectURL(url);
            callback(file);
        };
        img.src = url;
    }

    function executarDigitalizacaoOCR() {
        const file = fotoCnpjInput ? fotoCnpjInput.files[0] : null;

        if (!file) {
            if (ocrStatusBadge) {
                ocrStatusBadge.style.display = 'block';
                ocrStatusBadge.innerHTML = `
                    <div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">⚠️</span>
                        <span>Por favor, escolha uma foto do CNPJ primeiro no campo acima!</span>
                    </div>
                `;
            }
            return;
        }

        if (ocrStatusBadge) {
            ocrStatusBadge.style.display = 'block';
            ocrStatusBadge.innerHTML = `
                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">⏳</span>
                    <span>Lendo foto do CNPJ via OCR... Por favor, aguarde.</span>
                </div>
            `;
        }

        if (file.type === 'application/pdf') {
            revelarEtapa2();
            return;
        }

        const rodarOCR = () => {
            if (!window.Tesseract) {
                exibirAvisoManual();
                return;
            }

            otimizarImagemParaOCR(file, function(processedFile) {
                Tesseract.recognize(processedFile, 'por', {
                    logger: m => {
                        if (m.status === 'recognizing text' && ocrStatusBadge) {
                            const pct = Math.round((m.progress || 0) * 100);
                            ocrStatusBadge.innerHTML = `
                                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px;">🔍</span>
                                    <span>Lendo foto do CNPJ via OCR (${pct}%)...</span>
                                </div>
                            `;
                        }
                    }
                }).then(({ data: { text } }) => {
                    console.log("Texto extraído via OCR:", text);
                    const detectedCnpj = extrairCNPJDoTexto(text);
                    if (detectedCnpj) {
                        if (inputCnpjCpf) inputCnpjCpf.value = detectedCnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
                        consultarCnpj(detectedCnpj);
                        revelarEtapa2();
                    } else {
                        exibirAvisoManual();
                    }
                }).catch(err => {
                    console.error("Erro OCR:", err);
                    exibirAvisoManual();
                });
            });
        };

        if (typeof Tesseract === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
            script.onload = rodarOCR;
            script.onerror = () => exibirAvisoManual();
            document.head.appendChild(script);
        } else {
            rodarOCR();
        }
    }

    function exibirAvisoManual() {
        revelarEtapa2();
    }

    if (fotoCnpjInput) {
        fotoCnpjInput.addEventListener('change', function(e) {
            executarDigitalizacaoOCR();
        });
    }

    if (btnScanOcr) {
        btnScanOcr.addEventListener('click', function() {
            executarDigitalizacaoOCR();
        });
    }
});

function filtrarGastosPortal(status, btnElement) {
    const todosBotoes = document.querySelectorAll('.btn-aba-filtro');
    todosBotoes.forEach(b => {
        b.style.background = 'rgba(255,255,255,0.05)';
        b.style.color = '#94a3b8';
        b.style.fontWeight = 'normal';
    });

    if (btnElement) {
        if (status === 'REPROVADO') {
            btnElement.style.background = '#ef4444';
            btnElement.style.color = '#fff';
        } else {
            btnElement.style.background = '#3b82f6';
            btnElement.style.color = '#fff';
        }
        btnElement.style.fontWeight = '600';
    }

    const items = document.querySelectorAll('.item-gasto-portal');
    items.forEach(item => {
        const itemStatus = item.getAttribute('data-status');
        if (status === 'TODOS') {
            item.style.display = 'block';
        } else if (status === 'APROVADO') {
            if (itemStatus === 'APROVADO' || itemStatus === 'PAGO') {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        } else {
            if (itemStatus === status) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        }
    });
}

function abrirModalCorrigirGasto(exp) {
    document.getElementById('edit_expense_id').value = exp.id;
    document.getElementById('edit_description').value = exp.description || '';
    
    const cnpjInput = document.getElementById('edit_supplier_cnpj_cpf');
    if (cnpjInput) {
        cnpjInput.value = exp.supplier_cnpj_cpf || '';
        formatarCnpjCpf(cnpjInput);
    }

    document.getElementById('edit_supplier_name').value = exp.supplier_name || '';
    document.getElementById('edit_date_incurred').value = exp.date_incurred || '';
    document.getElementById('edit_expense_type_id').value = exp.expense_type_id || '';
    
    const valFloat = parseFloat(exp.value || 0);
    document.getElementById('edit_value').value = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valFloat);

    document.getElementById('textoMotivoReprovacaoModal').textContent = exp.notes || 'Nenhuma observação informada.';
    document.getElementById('modalCorrigirGasto').style.display = 'flex';
}

function clicarCardResumo(status) {
    const abaBotoes = document.querySelectorAll('.btn-aba-filtro');
    let targetBtn = null;
    abaBotoes.forEach(btn => {
        const text = btn.textContent.toUpperCase();
        if (status === 'TODOS' && text.includes('TODOS')) targetBtn = btn;
        if (status === 'PENDENTE' && text.includes('PENDENTES')) targetBtn = btn;
        if (status === 'APROVADO' && text.includes('APROVADOS')) targetBtn = btn;
        if (status === 'REPROVADO' && text.includes('REPROVADOS')) targetBtn = btn;
    });

    filtrarGastosPortal(status, targetBtn);

    const container = document.getElementById('historico-gastos-container');
    if (container) {
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>
