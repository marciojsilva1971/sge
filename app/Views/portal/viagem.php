<script>
function formatarMoeda(input) {
    if (!input) return;
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    value = (value / 100).toFixed(2) + '';
    value = value.replace(".", ",");
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
    input.value = "R$ " + value;
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#value, #edit_value, .input-moeda').forEach(el => {
        el.addEventListener('input', function() { formatarMoeda(this); });
        el.addEventListener('keyup', function() { formatarMoeda(this); });
        el.addEventListener('change', function() { formatarMoeda(this); });
        el.addEventListener('blur', function() { formatarMoeda(this); });
    });
});
</script>

<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px;">Controle de Viagens & Combustível</h2>
    <p class="subtitle" style="font-size: 12px;">Monitore suas viagens e anexe comprovantes para reembolso de combustível.</p>
</div>

<!-- 1. Viagem Ativa (Em Andamento) -->
<?php 
$activeReport = null;
foreach ($travelReports as $report) {
    if ($report['status'] === 'EM_ANDAMENTO') {
        $activeReport = $report;
        break;
    }
}
$modo = $_GET['modo'] ?? ($modo ?? '');
?>

<?php if ($activeReport): ?>
    <?php if ($modo === 'adicionar_gasto'): ?>
        <!-- MODAL DE CONFIRMAÇÃO PÓS-ENVIO DO COMPROVANTE -->
        <?php if (isset($_GET['envio_sucesso']) && $_GET['envio_sucesso'] == '1'): ?>
        <div id="modalSucessoEnvio" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
            <div style="background: #0f172a; border: 2px solid #22c55e; border-radius: 16px; max-width: 450px; width: 100%; padding: 24px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
                <div style="width: 60px; height: 60px; background: rgba(34, 197, 94, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; color: #4ade80; font-size: 32px; font-weight: bold;">
                    ✓
                </div>
                <h3 style="font-size: 18px; font-weight: 700; color: #f8fafc; margin-bottom: 8px;">Comprovante Cadastrado com Sucesso!</h3>
                <p style="font-size: 13px; color: #94a3b8; margin-bottom: 20px; line-height: 1.5;">
                    Seu cupom fiscal foi registrado e criptografado com sucesso no sistema. O que deseja fazer agora?
                </p>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="button" onclick="fecharEPrepararNovoViagem()" style="background: #22c55e; color: #0f172a; font-weight: 800; padding: 12px; border-radius: 10px; border: none; text-decoration: none; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        ➕ Cadastrar um Novo Comprovante nesta Viagem
                    </button>
                    <a href="<?= $this->baseUrl('portal/despesas') ?>" style="background: rgba(255, 255, 255, 0.08); color: #f8fafc; font-weight: 600; padding: 12px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.15); text-decoration: none; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        📊 Ir para "Meus Gastos" (Acompanhar Status)
                    </a>
                </div>
            </div>
        </div>
        <script>
            function fecharEPrepararNovoViagem() {
                const modal = document.getElementById('modalSucessoEnvio');
                if (modal) modal.style.display = 'none';
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?modo=adicionar_gasto";
                window.history.replaceState({path: cleanUrl}, '', cleanUrl);
                const firstInput = document.querySelector('#foto_cnpj_ocr, input[name="supplier_cnpj"]');
                if (firstInput) firstInput.focus();
            }
        </script>
        <?php endif; ?>

        <!-- TELA DE CADASTRAR GASTO DE COMBUSTÍVEL -->
        <div class="panel-card" style="border-color: var(--accent-teal); padding: 16px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px; margin-bottom: 14px;">
                <div>
                    <span style="font-size: 10px; color: var(--accent-teal-hover); text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">Cadastrar Combustível &bull; Viagem Ativa</span>
                    <h3 style="font-size: 15px; font-weight: 600; margin-top: 2px; color: #f8fafc;"><?= htmlspecialchars($activeReport['purpose']) ?></h3>
                    <span style="font-size: 11px; color: var(--text-secondary);">
                        Placa: <?= htmlspecialchars($activeReport['vehicle_plate'] ?? '-') ?> &bull; KM Inicial: <?= number_format(intval($activeReport['initial_km'] ?? 0), 0, ',', '.') ?> KM
                    </span>
                </div>
                <a href="<?= $this->baseUrl('portal/viagem') ?>" style="font-size: 12px; color: var(--accent-teal); text-decoration: none; font-weight: 700;">
                    ⬅️ Voltar ao Menu
                </a>
            </div>

            <!-- Formulário para Adicionar Cupom Fiscal -->
            <form action="<?= $this->baseUrl('portal/viagem/receipt') ?>" method="POST" enctype="multipart/form-data" style="margin-bottom: 20px; background: rgba(15,23,42,0.3); padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="travel_report_id" value="<?= $activeReport['id'] ?>">

                <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

                <!-- 1º PASSO: CAPTURA DO CNPJ -->
                <div id="bloco-captura-cnpj" class="form-group" style="background: rgba(13, 148, 136, 0.08); border: 2px dashed var(--accent-teal); padding: 14px; border-radius: 12px; margin-bottom: 16px;">
                    <label for="foto_cnpj_ocr" style="font-size: 13px; font-weight: 700; color: var(--accent-teal-hover); display: flex; align-items: center; gap: 6px;">
                        📸 1º PASSO: Fotografe ou envie um arquivo em detalhe do CNPJ da empresa impresso no cupom.
                    </label>
                    <p style="font-size: 11px; color: var(--text-secondary); margin-bottom: 8px;">
                        Caso seja reconhecido, preencheremos o CNPJ e o nome da empresa automaticamente, mas você poderá alterar se necessário.
                    </p>
                    <input type="file" id="foto_cnpj_ocr" accept="image/*, application/pdf" style="padding: 6px; font-size: 12px; width: 100%;">
                    
                    <button type="button" id="btn-scan-ocr" style="margin-top: 10px; background: var(--accent-teal); color: #0f172a; font-weight: 700; width: 100%; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; transition: all 0.2s;">
                        🔍 Digitalizar e Ler CNPJ (OCR)
                    </button>

                    <div id="ocr_status_badge" style="margin-top: 10px; display: none;"></div>

                    <div style="margin-top: 10px; text-align: center;">
                        <button type="button" id="btn-pular-ocr" style="background: transparent; border: none; color: var(--text-secondary); font-size: 11px; text-decoration: underline; cursor: pointer;">
                            Ou clique aqui para digitar o CNPJ manualmente
                        </button>
                    </div>
                </div>

                <!-- CONTAINER REVELADO APÓS LEITURA DO CNPJ OU DIGITAÇÃO MANUAL -->
                <div id="dados-despesa-container" style="display: none; margin-top: 16px; transition: all 0.3s ease;">
                    
                    <div id="ocr_notice_banner" style="display: none; background: rgba(245, 158, 11, 0.15); border-left: 4px solid #f59e0b; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px;">
                        <p style="font-size: 12px; font-weight: 700; color: #fbbf24; margin: 0; line-height: 1.4;">
                            ⚠️ Não foi possível efetuar a leitura automática do CNPJ no comprovante. Por favor, digite o CNPJ e a Razão Social da empresa manualmente nos campos abaixo.
                        </p>
                    </div>

                    <div style="background: rgba(56, 189, 248, 0.12); border-left: 4px solid #38bdf8; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px;">
                        <p style="font-size: 13px; font-weight: 700; color: #7dd3fc; margin: 0; line-height: 1.4;">
                            📸 Envie ou fotografe o cupom fiscal de forma que seja possivel a visualização de todas as despesas e o total. Você pode enviar mais de um arquivo ou foto
                        </p>
                    </div>

                    <div style="display: flex; gap: 10px; margin-bottom: 14px;">
                        <div class="form-group flex-1">
                            <label for="supplier_cnpj" style="font-size: 12px; font-weight: 600;">CNPJ do Posto de Combustível (editável)</label>
                            <input type="text" id="supplier_cnpj" name="supplier_cnpj" placeholder="00.000.000/0001-00" required style="font-weight: 600;">
                            <div id="cnpj_supplier_info" style="margin-top: 4px; font-size: 11px;"></div>
                        </div>
                        <div class="form-group flex-1">
                            <label for="supplier_name" style="font-size: 12px; font-weight: 600;">Nome / Razão Social da Empresa (editável)</label>
                            <input type="text" id="supplier_name" name="supplier_name" placeholder="Ex: Auto Posto Alvorada Ltda" style="font-weight: 500;">
                        </div>
                    </div>

                    <div class="form-group" style="background: rgba(15, 23, 42, 0.5); border: 1px dashed rgba(255, 255, 255, 0.2); padding: 12px; border-radius: 10px; margin-bottom: 14px;">
                        <label for="comprovante" style="font-size: 12px; font-weight: 700; color: #4ade80;">
                            📁 Fotos / Comprovante(s) do Cupom Fiscal (Aceita 1 ou mais arquivos)
                        </label>
                        <p style="font-size: 11px; color: var(--text-secondary); margin-top: 4px; margin-bottom: 6px;">
                            Clique em "Escolher arquivos" quantas vezes precisar para anexar todas as fotos ou páginas do cupom fiscal.
                        </p>
                        <input type="file" id="comprovante" name="comprovante[]" accept="image/*, application/pdf" multiple required style="padding: 6px; font-size: 12px; width: 100%; margin-top: 4px;">
                        <div id="comprovante-count-badge" style="font-size: 11px; font-weight: 600; color: #4ade80; margin-top: 6px; display: none;"></div>
                        <div id="galeria-miniaturas-container" style="display: none; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(255, 255, 255, 0.15);"></div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <div class="form-group flex-1">
                            <label for="value" style="font-size: 12px; font-weight: 700; color: var(--accent-teal-hover);">Valor Total do Cupom (R$) *</label>
                            <input type="text" id="value" name="value" class="input-moeda" placeholder="R$ 0,00" required style="font-size: 16px; font-weight: 700; color: var(--accent-teal-hover);" oninput="formatarMoeda(this);" onkeyup="formatarMoeda(this);" onchange="formatarMoeda(this);">
                        </div>
                        <div class="form-group flex-1">
                            <label for="receipt_date" style="font-size: 12px;">Data do Recibo</label>
                            <input type="date" id="receipt_date" name="receipt_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="spce_category_id" style="font-size: 12px;">Categoria SPCE</label>
                        <select id="spce_category_id" name="spce_category_id" required style="font-size: 13px;">
                            <?php foreach ($spceCategories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= strpos($cat['code'], '44') !== false ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes" style="font-size: 12px;">Notas / Observações (Opcional)</label>
                        <input type="text" id="notes" name="notes" placeholder="Ex: Abastecimento equipe de campo">
                    </div>

                    <button type="submit" style="width: 100%; background: #22c55e; color: #0f172a; font-weight: 800; padding: 12px; border: none; border-radius: 10px; font-size: 14px; cursor: pointer; margin-top: 10px;">
                        💾 Confirmar e Enviar Despesa
                    </button>
                </div>
            </form>

            <!-- Cupons já adicionados nesta viagem -->
            <h5 style="font-size: 12px; font-weight: 600; margin-bottom: 8px; color: var(--text-secondary);">Cupons Anexados Nesta Viagem:</h5>
            <?php if (empty($activeReport['receipts'])): ?>
                <p style="font-size: 11px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhum cupom adicionado ainda.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                    <?php foreach ($activeReport['receipts'] as $rec): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: rgba(15,23,42,0.5); border-radius: 8px; font-size: 12px;">
                            <div>
                                <strong>R$ <?= number_format($rec['value'], 2, ',', '.') ?></strong>
                                <div style="font-size: 10px; color: var(--text-secondary);">Posto CNPJ: <?= htmlspecialchars($rec['supplier_cnpj']) ?></div>
                            </div>
                            <div style="font-size: 11px; color: var(--text-secondary);">
                                <?= date('d/m/Y', strtotime($rec['receipt_date'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- BOTÕES DE NAVEGAÇÃO AO FINAL DA TELA DE CADASTRAR COMPROVANTE -->
            <div style="display: flex; gap: 10px; margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px;">
                <a href="<?= $this->baseUrl('portal/viagem') ?>" style="flex: 1; text-align: center; background: rgba(255,255,255,0.08); color: #fff; padding: 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none;">
                    ⬅️ Voltar ao Menu
                </a>
                <a href="<?= $this->baseUrl('portal/viagem?modo=encerrar') ?>" style="flex: 1; text-align: center; background: #dc2626; color: #fff; padding: 12px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none;">
                    🏁 Encerrar esta Viagem &rarr;
                </a>
            </div>
        </div>

    <?php elseif ($modo === 'encerrar'): ?>
        <!-- TELA EXCLUSIVA DE ENCERRAMENTO DA VIAGEM -->
        <div class="panel-card" style="border-color: #ef4444; padding: 16px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px; margin-bottom: 14px;">
                <div>
                    <span style="font-size: 10px; color: #fca5a5; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">Encerramento de Viagem</span>
                    <h3 style="font-size: 16px; font-weight: 700; margin-top: 2px; color: #f8fafc;"><?= htmlspecialchars($activeReport['purpose']) ?></h3>
                    <span style="font-size: 11px; color: var(--text-secondary);">
                        Placa: <?= htmlspecialchars($activeReport['vehicle_plate'] ?? '-') ?> &bull; KM Inicial: <?= number_format(intval($activeReport['initial_km'] ?? 0), 0, ',', '.') ?> KM
                    </span>
                </div>
                <a href="<?= $this->baseUrl('portal/viagem') ?>" style="font-size: 12px; color: #fca5a5; text-decoration: none; font-weight: 700;">
                    ⬅️ Voltar ao Menu
                </a>
            </div>

            <!-- Resumo dos cupons anexados -->
            <h5 style="font-size: 12px; font-weight: 600; margin-bottom: 8px; color: var(--text-secondary);">Cupons Fiscal(is) Anexados (Total: <?= count($activeReport['receipts'] ?? []) ?>):</h5>
            <?php if (empty($activeReport['receipts'])): ?>
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: 8px; padding: 12px; color: #fca5a5; font-size: 12px; margin-bottom: 16px; text-align: center;">
                    ⚠️ Você precisa cadastrar pelo menos um cupom fiscal de combustível antes de encerrar esta viagem.
                    <div style="margin-top: 8px;">
                        <a href="<?= $this->baseUrl('portal/viagem?modo=adicionar_gasto') ?>" style="color: #fff; font-weight: 700; text-decoration: underline;">
                            ➕ Clique aqui para Cadastrar Cupom de Combustível
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                    <?php foreach ($activeReport['receipts'] as $rec): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: rgba(15,23,42,0.5); border-radius: 8px; font-size: 12px;">
                            <div>
                                <strong>R$ <?= number_format($rec['value'], 2, ',', '.') ?></strong>
                                <div style="font-size: 10px; color: var(--text-secondary);">Posto CNPJ: <?= htmlspecialchars($rec['supplier_cnpj']) ?></div>
                            </div>
                            <div style="font-size: 11px; color: var(--text-secondary);">
                                <?= date('d/m/Y', strtotime($rec['receipt_date'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Formulário de Encerramento (Data Final + KM Final) -->
            <form action="<?= $this->baseUrl('portal/viagem/submit') ?>" method="POST" style="margin-top: 16px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" value="<?= $activeReport['id'] ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label for="end_date_submit" style="font-size: 12px; font-weight: 700; color: #4ade80;">
                            📅 Data Final da Viagem *
                        </label>
                        <input type="date" id="end_date_submit" name="end_date" value="<?= date('Y-m-d') ?>" required style="font-size: 13px; font-weight: 600; color: #f8fafc; width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569;">
                    </div>

                    <div class="form-group">
                        <label for="final_km" style="font-size: 12px; font-weight: 700; color: #4ade80;">
                            📏 Hodômetro Final (KM) *
                        </label>
                        <input type="number" id="final_km" name="final_km" placeholder="Ex: 45350" min="<?= intval($activeReport['initial_km'] ?? 0) ?>" required style="font-size: 13px; font-weight: 600; color: #f8fafc; width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569;">
                    </div>
                </div>
                <span style="font-size: 10px; color: var(--text-secondary); display: block; margin-top: -6px; margin-bottom: 12px;">
                    * Informe a data de encerramento e o hodômetro final do veículo (Resolução TSE nº 23.607/2019).
                </span>

                <button type="submit" class="btn btn-primary btn-block" style="background: #dc2626; border: none; font-weight: 800; padding: 12px;" <?= empty($activeReport['receipts']) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                    🚀 Finalizar e Enviar Relatório de Viagem
                </button>
            </form>
        </div>

    <?php else: ?>
        <!-- MENU PRINCIPAL DA VIAGEM EM ANDAMENTO (TELA INICIAL AO CLICAR EM "VIAGENS") -->
        <div class="panel-card" style="border: 2px solid var(--accent-teal); padding: 24px; margin-bottom: 24px; text-align: center;">
            <div style="width: 56px; height: 56px; background: rgba(13, 148, 136, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; font-size: 30px; color: var(--accent-teal);">
                🚗
            </div>
            <span style="font-size: 11px; color: var(--accent-teal-hover); text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">Você possui 1 Viagem Ativa em Andamento</span>
            <h3 style="font-size: 18px; font-weight: 700; margin-top: 4px; margin-bottom: 6px; color: #f8fafc;"><?= htmlspecialchars($activeReport['purpose']) ?></h3>
            <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 20px;">
                Placa: <strong style="color: #fff;"><?= htmlspecialchars($activeReport['vehicle_plate'] ?? '-') ?></strong> &bull; 
                KM Inicial: <strong style="color: #fff;"><?= number_format(intval($activeReport['initial_km'] ?? 0), 0, ',', '.') ?> KM</strong> &bull; 
                Início: <strong style="color: #fff;"><?= date('d/m/Y', strtotime($activeReport['start_date'])) ?></strong>
            </p>

            <p style="font-size: 13px; font-weight: 700; color: #e2e8f0; margin-bottom: 16px;">
                O que você deseja fazer agora?
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-top: 10px;">
                <a href="<?= $this->baseUrl('portal/viagem?modo=adicionar_gasto') ?>" style="background: rgba(13, 148, 136, 0.2); border: 2px solid var(--accent-teal); padding: 20px 16px; border-radius: 14px; text-decoration: none; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;">
                    <span style="font-size: 32px;">⛽</span>
                    <span style="font-size: 14px; font-weight: 800; color: #5eead4;">Cadastrar Novo Gasto de Combustível</span>
                    <span style="font-size: 11px; color: #94a3b8; text-align: center;">Fotografar e anexar cupom fiscal / abastecimento</span>
                </a>

                <a href="<?= $this->baseUrl('portal/viagem?modo=encerrar') ?>" style="background: rgba(239, 68, 68, 0.15); border: 2px solid #ef4444; padding: 20px 16px; border-radius: 14px; text-decoration: none; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;">
                    <span style="font-size: 32px;">🏁</span>
                    <span style="font-size: 14px; font-weight: 800; color: #fca5a5;">Encerrar Viagem em Aberto</span>
                    <span style="font-size: 11px; color: #94a3b8; text-align: center;">Informar Data Final e Hodômetro Final (KM)</span>
                </a>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Formulário para Iniciar Nova Viagem -->
    <div class="panel-card" style="padding: 16px; margin-bottom: 24px;">
        <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
            <h3 style="font-size: 14px; font-weight: 600;">Iniciar Novo Relatório de Viagem</h3>
        </div>
        <form action="<?= $this->baseUrl('portal/viagem') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group" style="margin-bottom: 12px;">
                <label for="purpose">Finalidade / Objetivo da Viagem *</label>
                <input type="text" id="purpose" name="purpose" placeholder="Ex: Panfletagem e carreatas no setor norte" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label for="start_date">Data Inicial *</label>
                    <input type="date" id="start_date" name="start_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="vehicle_plate" style="font-weight: 700; color: var(--accent-teal-hover);">Placa do Veículo * (Exigência TSE)</label>
                    <input type="text" id="vehicle_plate" name="vehicle_plate" placeholder="AAA-0A00" required style="text-transform: uppercase; font-weight: 700;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="initial_km" style="font-weight: 700; color: var(--accent-teal-hover);">Hodômetro Inicial (KM) *</label>
                <input type="number" id="initial_km" name="initial_km" placeholder="Ex: 45100" min="0" required style="font-weight: 700;">
            </div>

            <button type="submit" class="btn btn-teal btn-block">
                🚗 Iniciar Registro de Viagem
            </button>
        </form>
    </div>
<?php endif; ?>

<!-- Histórico de Viagens Anteriores -->
<div class="panel-card" style="padding: 16px;">
    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 12px;">
        <h3 style="font-size: 14px; font-weight: 600;">Histórico de Viagens</h3>
    </div>

    <?php if (empty($travelReports)): ?>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; padding: 10px 0;">Nenhuma viagem anterior registrada.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($travelReports as $tr): ?>
                <?php if ($tr['status'] === 'EM_ANDAMENTO') continue; ?>
                <div style="padding: 12px; background: rgba(15,23,42,0.4); border-radius: 10px; border: 1px solid rgba(255,255,255,0.04);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <span style="font-size: 13px; font-weight: 600; display: block;"><?= htmlspecialchars($tr['purpose']) ?></span>
                            <span style="font-size: 11px; color: var(--accent-teal-hover); display: block; margin-top: 2px;">
                                🚘 Placa: <strong><?= htmlspecialchars(strtoupper($tr['vehicle_plate'])) ?></strong>
                                <?php if (!empty($tr['initial_km'])): ?>
                                    &bull; Hodômetro: <?= number_format($tr['initial_km'], 0, ',', '.') ?> KM &rarr; <?= !empty($tr['final_km']) ? number_format($tr['final_km'], 0, ',', '.') . ' KM' : '---' ?>
                                    <?php if (!empty($tr['final_km'])): ?>
                                        (<strong><?= number_format($tr['final_km'] - $tr['initial_km'], 0, ',', '.') ?> KM rodados</strong>)
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                            <span style="font-size: 10px; color: var(--text-secondary); display: block; margin-top: 2px;">
                                Período: <?= date('d/m/Y', strtotime($tr['start_date'])) ?> &rarr; <?= date('d/m/Y', strtotime($tr['end_date'])) ?>
                            </span>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                            <?php if ($tr['status'] === 'APROVADO'): ?>
                                <span class="badge badge-success">Aprovada</span>
                            <?php elseif ($tr['status'] === 'REJEITADO'): ?>
                                <span class="badge badge-danger">Rejeitada</span>
                                <button type="button" class="btn btn-warning btn-sm" style="font-size: 10px; padding: 2px 8px; font-weight: 700; background: #eab308; color: #0f172a;" onclick='abrirModalEditarViagem(<?= json_encode($tr, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    ✏️ Editar
                                </button>
                            <?php else: ?>
                                <span class="badge badge-info">Auditoria</span>
                                <button type="button" class="btn btn-secondary btn-sm" style="font-size: 10px; padding: 2px 8px; font-weight: 600; background: rgba(255,255,255,0.1); color: #fff;" onclick='abrirModalEditarViagem(<?= json_encode($tr, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    ✏️ Editar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL DE EDIÇÃO DE RELATÓRIO DE VIAGEM EM VIAGEM.PHP -->
<div id="modalEditarViagemPage" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 1px solid var(--accent-indigo); border-radius: 16px; max-width: 500px; width: 100%; padding: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #818cf8;">✏️ Editar Relatório de Viagem</h3>
            <button type="button" onclick="fecharModalEditarViagemPage()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <form action="<?= $this->baseUrl('portal/viagem/editar') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="edit_page_travel_id" name="travel_id" value="">

            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 600;">Objetivo da Viagem *</label>
                <input type="text" id="edit_page_travel_purpose" name="purpose" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Data Início *</label>
                    <input type="date" id="edit_page_travel_start_date" name="start_date" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; font-weight: 600;">Data Fim *</label>
                    <input type="date" id="edit_page_travel_end_date" name="end_date" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                <div class="form-group">
                    <label style="font-size: 11px; font-weight: 600;">Placa Veículo *</label>
                    <input type="text" id="edit_page_travel_vehicle_plate" name="vehicle_plate" required placeholder="AAA-0A00" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 12px; text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label style="font-size: 11px; font-weight: 600;">KM Inicial *</label>
                    <input type="number" id="edit_page_travel_initial_km" name="initial_km" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 12px;">
                </div>
                <div class="form-group">
                    <label style="font-size: 11px; font-weight: 600;">KM Final</label>
                    <input type="number" id="edit_page_travel_final_km" name="final_km" placeholder="KM Final" style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 12px;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 16px;">
                <button type="button" class="btn btn-secondary flex-1" onclick="fecharModalEditarViagemPage()">Cancelar</button>
                <button type="submit" class="btn btn-teal flex-1" style="background: #eab308; color: #0f172a; font-weight: 800;">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalEditarViagem(tr) {
        document.getElementById('edit_page_travel_id').value = tr.id;
        document.getElementById('edit_page_travel_purpose').value = tr.purpose || '';
        document.getElementById('edit_page_travel_start_date').value = tr.start_date || '';
        document.getElementById('edit_page_travel_end_date').value = tr.end_date || '';
        document.getElementById('edit_page_travel_vehicle_plate').value = tr.vehicle_plate || '';
        document.getElementById('edit_page_travel_initial_km').value = tr.initial_km || '';
        document.getElementById('edit_page_travel_final_km').value = tr.final_km || '';
        document.getElementById('modalEditarViagemPage').style.display = 'flex';
    }

    function fecharModalEditarViagemPage() {
        document.getElementById('modalEditarViagemPage').style.display = 'none';
    }
</script>

<script>
function formatarMoeda(input) {
    if (!input) return;
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    value = (value / 100).toFixed(2) + '';
    value = value.replace(".", ",");
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
    input.value = "R$ " + value;
}

    // Máscara CNPJ do Posto (com verificação de existência no DOM)
    var supplierCnpjElem = document.getElementById('supplier_cnpj');
    if (supplierCnpjElem) {
        supplierCnpjElem.addEventListener('input', function (e) {
            var value = e.target.value.replace(/\D/g, "");
            if (value.length > 14) value = value.slice(0, 14);

            if (value.length > 11) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
            } else if (value.length > 9) {
                value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4");
            } else if (value.length > 6) {
                value = value.replace(/^(\d{3})(\d{3})(\d{1,3})$/, "$1.$2.$3");
            } else if (value.length > 3) {
                value = value.replace(/^(\d{3})(\d{1,3})$/, "$1.$2");
            }
            e.target.value = value;
        });
    }

    // Máscara básica de Placa
    var plateInput = document.getElementById('vehicle_plate');
    if (plateInput) {
        plateInput.addEventListener('input', function (e) {
            var value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g, "");
            if (value.length > 8) value = value.slice(0, 8);
            e.target.value = value;
        });
    }

    // Consulta pública de CNPJ em tempo real
    const cnpjInput = document.getElementById('supplier_cnpj');
    const cnpjInfoDiv = document.getElementById('cnpj_supplier_info');
    const supplierNameInput = document.getElementById('supplier_name');

    function consultarCnpjServico(cnpjVal, isOcr = false) {
        const clean = cnpjVal.replace(/\D/g, "");
        if (clean.length !== 14) return;

        if (cnpjInfoDiv) {
            cnpjInfoDiv.innerHTML = '<span style="color: var(--accent-teal); font-weight: 500;">🔍 Consultando Receita Federal...</span>';
        }

        fetch('<?= $this->baseUrl("api/cnpj/consultar") ?>?cnpj=' + clean)
            .then(res => res.json())
            .then(data => {
                if (data.success && (data.razao_social || (data.company && (data.company.corporate_name || data.company.trade_name)))) {
                    const razaoSocial = data.razao_social || (data.company.corporate_name || data.company.trade_name);
                    const formattedCnpj = clean.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");

                    if (cnpjInput) cnpjInput.value = formattedCnpj;
                    if (supplierNameInput) supplierNameInput.value = razaoSocial;
                    if (cnpjInfoDiv) {
                        cnpjInfoDiv.innerHTML = `<span style="color: #22c55e; font-weight: 600;">✔ ${razaoSocial}</span>`;
                    }
                    if (isOcr) {
                        if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
                        if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'none';
                        if (dadosContainer) {
                            dadosContainer.style.display = 'block';
                            dadosContainer.scrollIntoView({ behavior: 'smooth' });
                        }
                        setTimeout(() => {
                            alert("✅ CNPJ e Razão Social lidos com sucesso!\n\n" + razaoSocial + " (" + formattedCnpj + ")");
                        }, 100);
                    }
                } else {
                    if (isOcr) {
                        // Preserva o CNPJ preenchido para o usuario visualizar e ajustar se necessário
                        if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
                        if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'block';
                        if (dadosContainer) {
                            dadosContainer.style.display = 'block';
                            dadosContainer.scrollIntoView({ behavior: 'smooth' });
                        }
                        if (supplierNameInput) {
                            supplierNameInput.value = '';
                            supplierNameInput.focus();
                        }
                        if (cnpjInfoDiv) {
                            cnpjInfoDiv.innerHTML = `<span style="color: #f59e0b; font-weight: 500;">⚠️ CNPJ capturado. Por favor, informe a Razão Social ao lado.</span>`;
                        }
                        setTimeout(() => {
                            alert("⚠️ CNPJ (" + cnpjInput.value + ") lido do comprovante, mas não localizado na base da Receita Federal.\n\nVocê pode ajustar o CNPJ se houver dígito impreciso ou digitar a Razão Social da empresa manualmente no campo ao lado.");
                        }, 100);
                    } else {
                        // Digitação manual de CNPJ não encontrado na Receita Federal
                        if (supplierNameInput) supplierNameInput.value = '';
                        if (cnpjInfoDiv) {
                            cnpjInfoDiv.innerHTML = `<span style="color: #ef4444; font-weight: 500;">⚠️ CNPJ não localizado na Receita Federal.</span>`;
                        }
                        const formattedCnpj = clean.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
                        const querManter = confirm("CNPJ (" + formattedCnpj + ") não foi localizado na base pública da Receita Federal.\n\nDeseja manter este CNPJ e preencher o Nome / Razão Social da empresa manualmente?");
                        if (querManter) {
                            if (supplierNameInput) supplierNameInput.focus();
                        } else {
                            if (cnpjInput) {
                                cnpjInput.value = '';
                                cnpjInput.focus();
                            }
                        }
                    }
                }
            })
            .catch(err => {
                console.error('Erro na consulta do CNPJ:', err);
                if (isOcr) {
                    exibirAvisoEPreenchimentoManual("⚠️ Erro ao conectar com base da Receita. Os campos foram mantidos em branco para preenchimento manual.");
                } else {
                    if (cnpjInfoDiv) {
                        cnpjInfoDiv.innerHTML = '<span style="color: #ef4444;">⚠️ Erro ao conectar com base da Receita</span>';
                    }
                }
            });
    }

    if (cnpjInput) {
        cnpjInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length > 14) value = value.slice(0, 14);

            if (value.length > 12) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
            }
            e.target.value = value;

            if (value.replace(/\D/g, "").length === 14) {
                consultarCnpjServico(value, false);
            }
        });
    }

    const fotoCnpjInput = document.getElementById('foto_cnpj_ocr');
    const btnScanOcr = document.getElementById('btn-scan-ocr');
    const btnPularOcr = document.getElementById('btn-pular-ocr');
    const statusBadge = document.getElementById('ocr_status_badge');
    const dadosContainer = document.getElementById('dados-despesa-container');
    const blocoCapturaCnpj = document.getElementById('bloco-captura-cnpj');
    const ocrNoticeBanner = document.getElementById('ocr_notice_banner');
    const inputComprovante = document.getElementById('comprovante');
    const badgeComprovante = document.getElementById('comprovante-count-badge');

    function exibirAvisoEPreenchimentoManual(mensagem = null) {
        if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
        if (dadosContainer) {
            dadosContainer.style.display = 'block';
            dadosContainer.scrollIntoView({ behavior: 'smooth' });
        }
        if (ocrNoticeBanner) {
            ocrNoticeBanner.style.display = 'block';
            if (mensagem) {
                const p = ocrNoticeBanner.querySelector('p');
                if (p) p.innerText = mensagem;
            }
        }
        if (cnpjInput) {
            cnpjInput.value = '';
            cnpjInput.focus();
        }
        if (supplierNameInput) {
            supplierNameInput.value = '';
        }
        setTimeout(() => {
            alert(mensagem || "⚠️ Não foi possível efetuar a leitura automática do CNPJ no comprovante.\n\nPor favor, digite o CNPJ e a Razão Social da empresa manualmente nos campos abaixo.");
        }, 100);
    }

    function revelarEtapa2() {
        if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
        if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'none';
        if (dadosContainer) {
            dadosContainer.style.display = 'block';
            dadosContainer.scrollIntoView({ behavior: 'smooth' });
        }
        if (cnpjInput) cnpjInput.focus();
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

    // Extrator inteligente de CNPJ com tolerância avançada a ruídos comuns de OCR
    function extrairCNPJDoTexto(text) {
        if (!text) return null;

        console.log("=== TEXTO OCR EXTRAÍDO (VIAGEM) ===");
        console.log(text);

        function normalizarTextoOCR(txt) {
            return txt
                .replace(/[OoQ]/g, '0')
                .replace(/[Il|!L]/g, '1')
                .replace(/[Zz]/g, '2')
                .replace(/[Ss$]/g, '5')
                .replace(/[Bb]/g, '8')
                .replace(/[Gg]/g, '6');
        }

        // METODO 1: Procura específica pela palavra-chave CNPJ / C.N.P.J / MF / CGC
        const cnpjKeywordRegex = /(?:CNPJ|C\.?N\.?P\.?J\.?|MF|CGC)[\s\:\.\-\/]*([0-9OolI|!LsSZzBbGg\.\-\/\s]{14,35})/gi;
        let match;
        while ((match = cnpjKeywordRegex.exec(text)) !== null) {
            if (match[1]) {
                let norm = normalizarTextoOCR(match[1]);
                let digitsOnly = norm.replace(/\D/g, '');
                for (let i = 0; i <= digitsOnly.length - 14; i++) {
                    let sub = digitsOnly.substring(i, i + 14);
                    if (validarCNPJ(sub)) {
                        console.log("✓ CNPJ identificado via Método 1 (Palavra-Chave + Validador):", sub);
                        return sub;
                    }
                }
                if (digitsOnly.length >= 14) {
                    let fallbackSub = digitsOnly.substring(0, 14);
                    console.log("✓ CNPJ identificado via Método 1 (Candidato direto pós-CNPJ):", fallbackSub);
                    return fallbackSub;
                }
            }
        }

        // METODO 2: Padrão visual clássico de CNPJ (XX.XXX.XXX/XXXX-XX) no texto inteiro
        const padraoVisualRegex = /\d{2}[\.\s]*\d{3}[\.\s]*\d{3}[\/\s]*\d{4}[\-\s]*\d{2}/g;
        const matchesVisuais = text.match(padraoVisualRegex) || [];
        for (let raw of matchesVisuais) {
            let digits = raw.replace(/\D/g, '');
            if (digits.length === 14) {
                console.log("✓ CNPJ identificado via Método 2 (Padrão Visual):", digits);
                return digits;
            }
        }

        // METODO 3: Varredura de 14 dígitos válidos no texto completo normalizado
        const textoNorm = normalizarTextoOCR(text);
        const todosDigitos = textoNorm.replace(/\D/g, '');
        for (let i = 0; i <= todosDigitos.length - 14; i++) {
            let sub = todosDigitos.substring(i, i + 14);
            if (validarCNPJ(sub)) {
                console.log("✓ CNPJ identificado via Método 3 (Varredura 14 dígitos):", sub);
                return sub;
            }
        }

        console.log("❌ Nenhum CNPJ localizado no texto extraído.");
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
            if (statusBadge) {
                statusBadge.style.display = 'block';
                statusBadge.innerHTML = `
                    <div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">⚠️</span>
                        <span>Por favor, escolha uma foto do CNPJ primeiro no campo acima!</span>
                    </div>
                `;
            }
            return;
        }

        if (statusBadge) {
            statusBadge.style.display = 'block';
            statusBadge.innerHTML = `
                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">⏳</span>
                    <span>Lendo foto do CNPJ via OCR... Por favor, aguarde.</span>
                </div>
            `;
        }

        if (file.type === 'application/pdf') {
            exibirAvisoEPreenchimentoManual("📄 Arquivo PDF anexado. Por favor, informe o CNPJ e os dados da empresa nos campos abaixo.");
            return;
        }

        const rodarOCR = async () => {
            const ok = await garantirTesseractCarregado();
            if (!ok || typeof Tesseract === 'undefined') {
                exibirAvisoEPreenchimentoManual();
                return;
            }

            try {
                const worker = await Tesseract.createWorker('por');
                await worker.setParameters({
                    tessedit_pageseg_mode: '6', // PSM 6: Bloco único de texto (ideal para recibos/cupons)
                });

                const ret = await worker.recognize(file);
                await worker.terminate();

                console.log("Texto extraído via OCR (PSM 6):", ret.data.text);
                let detectedResult = extrairCNPJDoTexto(ret.data.text);

                if (!detectedResult) {
                    // Fallback PSM 3 (auto)
                    const workerAuto = await Tesseract.createWorker('por');
                    const retAuto = await workerAuto.recognize(file);
                    await workerAuto.terminate();

                    console.log("Texto extraído via OCR (PSM Auto):", retAuto.data.text);
                    detectedResult = extrairCNPJDoTexto(retAuto.data.text);
                }

                if (detectedResult && detectedResult.cnpj) {
                    const detectedCnpj = detectedResult.cnpj;
                    const isValidoDV = detectedResult.valido;

                    // Preenche o campo de CNPJ com a captura do OCR
                    if (cnpjInput) {
                        let clean = detectedCnpj.replace(/\D/g, "");
                        if (clean.length === 14) {
                            cnpjInput.value = clean.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
                        } else {
                            cnpjInput.value = detectedCnpj;
                        }
                    }

                    if (isValidoDV) {
                        consultarCnpjServico(detectedCnpj, true);
                    } else {
                        // Se o OCR capturou o CNPJ mas o DV ficou impreciso no OCR, MANTÉM o CNPJ preenchido para o usuário conferir/ajustar
                        if (blocoCapturaCnpj) blocoCapturaCnpj.style.display = 'none';
                        if (ocrNoticeBanner) ocrNoticeBanner.style.display = 'block';
                        if (dadosContainer) {
                            dadosContainer.style.display = 'block';
                            dadosContainer.scrollIntoView({ behavior: 'smooth' });
                        }
                        if (cnpjInfoDiv) cnpjInfoDiv.innerHTML = '<span style="color: #f59e0b; font-weight: 500;">⚠️ Confira o número do CNPJ acima.</span>';
                        if (cnpjInput) cnpjInput.focus();
                        setTimeout(() => {
                            alert("⚠️ O sistema identificou o CNPJ (" + cnpjInput.value + ") no comprovante, mas alguns dígitos podem precisar de confirmação.\n\nPor favor, confira o número e altere se necessário.");
                        }, 100);
                    }
                } else {
                    exibirAvisoEPreenchimentoManual();
                }
            } catch (err) {
                console.error("Erro OCR Tesseract:", err);
                exibirAvisoEPreenchimentoManual();
            }
        };

        rodarOCR();
    }

    async function garantirTesseractCarregado() {
        if (typeof Tesseract !== 'undefined') return true;
        return new Promise((resolve) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
            script.onload = () => resolve(true);
            script.onerror = () => resolve(false);
            document.head.appendChild(script);
        });
    }
    }

    if (btnScanOcr) {
        btnScanOcr.addEventListener('click', function() {
            executarDigitalizacaoOCR();
        });
    }
</script>
