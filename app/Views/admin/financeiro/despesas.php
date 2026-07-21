<div class="page-header">
    <div>
        <h2>Lançamento e Acompanhamento de Despesas</h2>
        <p class="subtitle">Controle de pagamentos de fornecedores, serviços, material gráfico e acompanhamento de gastos.</p>
    </div>
    <div>
        <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="btn btn-secondary">
            ⬅️ Voltar ao Financeiro
        </a>
    </div>
</div>

<?php include __DIR__ . '/_nav_tabs.php'; ?>

<!-- NAVEGAÇÃO DE SUB-ABAS (SEPARAÇÃO DE LANÇAMENTO E ACOMPANHAMENTO) -->
<style>
.btn-subtab {
    background: rgba(30, 41, 59, 0.6);
    color: #94a3b8;
    border: 1px solid #334155 !important;
    transition: all 0.2s ease;
}
.btn-subtab.active {
    background: var(--accent-teal, #0d9488) !important;
    color: #0f172a !important;
    border-color: var(--accent-teal, #0d9488) !important;
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
}
.btn-subtab:hover:not(.active) {
    background: rgba(51, 65, 85, 0.8);
    color: #f8fafc;
}
</style>

<?php
    $abaInicial = (isset($_GET['envio_sucesso']) || isset($_GET['tab_lancamento'])) ? 'lancamento' : 'historico';
?>

<div style="display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; flex-wrap: wrap;">
    <button type="button" class="btn-subtab <?= $abaInicial === 'historico' ? 'active' : '' ?>" id="btn-tab-historico" onclick="alternarAbaDespesas('historico')" style="padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
        📋 Acompanhamento de Despesas Cadastradas <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 12px; font-size: 11px;"><?= count($expenses) ?></span>
    </button>
    <button type="button" class="btn-subtab <?= $abaInicial === 'lancamento' ? 'active' : '' ?>" id="btn-tab-lancamento" onclick="alternarAbaDespesas('lancamento')" style="padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
        ➕ Lançar Nova Despesa Geral
    </button>
</div>

<!-- MODAL DE CONFIRMAÇÃO PÓS-ENVIO DO COMPROVANTE -->
<?php if (isset($_GET['envio_sucesso']) && $_GET['envio_sucesso'] == '1'): ?>
<div id="modalSucessoEnvio" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 2px solid #22c55e; border-radius: 16px; max-width: 440px; width: 100%; padding: 24px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <div style="width: 56px; height: 56px; background: rgba(34, 197, 94, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px auto; color: #4ade80; font-size: 28px; font-weight: bold;">
            ✓
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: #f8fafc; margin-bottom: 8px;">Despesa Registrada com Sucesso!</h3>
        <p style="font-size: 13px; color: #94a3b8; margin-bottom: 20px; line-height: 1.4;">
            O comprovante e a despesa financeira foram cadastrados. O que deseja fazer agora?
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <button type="button" onclick="document.getElementById('modalSucessoEnvio').style.display='none'; alternarAbaDespesas('lancamento');" style="background: #22c55e; color: #0f172a; font-weight: 800; padding: 12px; border-radius: 8px; border: none; font-size: 13px; cursor: pointer; width: 100%;">
                ➕ Lançar Outra Despesa
            </button>
            <button type="button" onclick="document.getElementById('modalSucessoEnvio').style.display='none'; alternarAbaDespesas('historico');" style="background: rgba(255, 255, 255, 0.1); color: #f8fafc; font-weight: 600; padding: 12px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2); font-size: 13px; cursor: pointer; display: block; width: 100%;">
                📋 Ver Acompanhamento de Despesas
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SEÇÃO 1: ACOMPANHAMENTO DE DESPESAS CADASTRADAS (LARGURA TOTAL) -->
<div id="section-historico-despesas" style="display: <?= $abaInicial === 'historico' ? 'block' : 'none' ?>;">
    <div class="panel-card" style="width: 100%;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3>📋 Acompanhamento de Despesas Registradas</h3>
            <button type="button" onclick="alternarAbaDespesas('lancamento')" class="btn btn-teal btn-sm" style="font-weight: 700;">
                ➕ Lançar Nova Despesa
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Fornecedor / Credor</th>
                        <th>Descrição / Finalidade</th>
                        <th>Conta / Pagto</th>
                        <th>Valor (R$)</th>
                        <th>Status</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 20px;">Nenhuma despesa registrada até o momento.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $exp): ?>
                            <tr>
                                <td style="white-space: nowrap;"><?= date('d/m/Y', strtotime($exp['date_incurred'])) ?></td>
                                <td>
                                    <div class="user-log-name" style="font-weight: 700; color: #f8fafc;"><?= htmlspecialchars($exp['supplier_name'] ?? 'Fornecedor Desconhecido') ?></div>
                                    <?php if (!empty($exp['supplier_cnpj_cpf'])): 
                                        $digitsCnpj = preg_replace('/\D/', '', $exp['supplier_cnpj_cpf']);
                                        $fmtCnpj = (strlen($digitsCnpj) === 14) 
                                            ? preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $digitsCnpj) 
                                            : ((strlen($digitsCnpj) === 11) ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $digitsCnpj) : $exp['supplier_cnpj_cpf']);
                                    ?>
                                        <div style="font-size: 11px; color: #94a3b8; font-weight: 600;">CNPJ/CPF: <?= htmlspecialchars($fmtCnpj) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 13px; color: #e2e8f0; font-weight: 500;"><?= htmlspecialchars($exp['description'] ?? '') ?></div>
                                    <?php if (!empty($exp['spce_code'])): ?>
                                        <span style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($exp['spce_code'] ?? '') ?> - <?= htmlspecialchars($exp['spce_desc'] ?? '') ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="font-size: 9px; padding: 1px 4px;">⚠️ Gasto de Campo</span>
                                        <?php if (!empty($exp['expense_type_name'])): ?>
                                            <span style="font-size: 11px; color: var(--text-secondary); margin-left: 4px;">(<?= htmlspecialchars($exp['expense_type_name'] ?? '') ?>)</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 12px; font-weight: 500;"><?= htmlspecialchars($exp['bank_name'] ?? 'Não informada') ?></div>
                                    <span style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($exp['payment_method'] ?? 'N/I') ?></span>
                                </td>
                                <td style="font-weight: 700; color: #4ade80; white-space: nowrap;">
                                    R$ <?= number_format($exp['value'], 2, ',', '.') ?>
                                </td>
                                <td>
                                    <?php if ($exp['status'] === 'PAGO'): ?>
                                        <span class="badge badge-success">Pago</span>
                                    <?php elseif ($exp['status'] === 'APROVADO'): ?>
                                        <span class="badge badge-info">Aprovado</span>
                                    <?php elseif ($exp['status'] === 'REJEITADO'): ?>
                                        <span class="badge badge-danger">Rejeitado</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <button type="button" onclick='abrirModalVerDespesa(<?= json_encode($exp, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn btn-secondary btn-sm" style="font-size: 12px; padding: 5px 12px; font-weight: 600; background: #334155; color: #38bdf8; border: 1px solid #475569;">
                                        👁️ Ver Detalhes
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SEÇÃO 2: FORMULÁRIO DE LANÇAMENTO DE DESPESA (LARGURA TOTAL) -->
<div id="section-lancamento-despesa" style="display: <?= $abaInicial === 'lancamento' ? 'block' : 'none' ?>;">
    <div class="panel-card" style="width: 100%; max-width: 900px; margin: 0 auto;">
        <div class="card-header">
            <h3>➕ Lançar Nova Despesa Geral</h3>
        </div>
        <form action="<?= $this->baseUrl('admin/financeiro/despesas') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- 1º PASSO: CAPTURA DO CNPJ (OCR) -->
            <div id="bloco-captura-cnpj" class="form-group" style="background: rgba(13, 148, 136, 0.08); border: 2px dashed var(--accent-teal); padding: 16px; border-radius: 12px; margin-bottom: 16px;">
                <label for="foto_cnpj_ocr" style="font-size: 13px; font-weight: 700; color: var(--accent-teal-hover); display: flex; align-items: center; gap: 6px;">
                    📸 1º PASSO: Fotografe ou envie um arquivo em detalhe do CNPJ da empresa impresso no cupom.
                </label>
                <p style="font-size: 11px; color: var(--text-secondary); margin-bottom: 8px;">
                    Caso seja reconhecido, preencheremos o CNPJ e o nome da empresa automaticamente, mas você poderá alterar se necessário.
                </p>
                <input type="file" id="foto_cnpj_ocr" accept="image/*, application/pdf" style="padding: 8px; font-size: 12px; width: 100%; background: #0f172a; border-radius: 6px; border: 1px solid #334155; color: #fff;">
                
                <button type="button" id="btn-scan-ocr" style="margin-top: 10px; background: var(--accent-teal); color: #0f172a; font-weight: 700; width: 100%; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; transition: all 0.2s;">
                    🔍 Digitalizar e Ler CNPJ (OCR)
                </button>

                <!-- Status do OCR -->
                <div id="ocr_status_badge" style="margin-top: 10px; display: none;"></div>

                <div style="margin-top: 10px; text-align: center;">
                    <button type="button" id="btn-pular-ocr" style="background: transparent; border: none; color: var(--text-secondary); font-size: 11px; text-decoration: underline; cursor: pointer;">
                        Ou clique aqui para digitar os dados manualmente
                    </button>
                </div>
            </div>

            <!-- CONTAINER REVELADO APÓS LEITURA DO CNPJ -->
            <div id="dados-despesa-container" style="display: none; margin-top: 16px; transition: all 0.3s ease;">
                
                <div style="background: rgba(56, 189, 248, 0.12); border-left: 4px solid #38bdf8; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px;">
                    <p style="font-size: 13px; font-weight: 700; color: #7dd3fc; margin: 0; line-height: 1.4;">
                        📸 Envie ou fotografe o cupom fiscal de forma que seja possivel a visualização de todas as despesas e o total. Você pode enviar mais de um arquivo ou foto.
                    </p>
                </div>

                <!-- DADOS DO FORNECEDOR -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label for="supplier_id">Fornecedor / Credor (editável)</label>
                        <select id="supplier_id" name="supplier_id" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                            <option value="">Selecione o fornecedor...</option>
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['corporate_name']) ?> (CNPJ/CPF: <?= htmlspecialchars($sup['cnpj_cpf']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bank_account_id">Conta Bancária Origem</label>
                        <select id="bank_account_id" name="bank_account_id" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                            <option value="">Selecione a conta...</option>
                            <?php foreach ($bankAccounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?> (Saldo: R$ <?= number_format($acc['balance'], 2, ',', '.') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- UPLOAD COMPROVANTE FISCAL -->
                <div class="form-group" style="background: rgba(15, 23, 42, 0.5); border: 1px dashed rgba(255, 255, 255, 0.2); padding: 14px; border-radius: 10px; margin-bottom: 14px;">
                    <label for="comprovante" style="font-size: 12px; font-weight: 700; color: #4ade80;">
                        📁 Fotos / Comprovante(s) do Cupom Fiscal (Aceita 1 ou mais arquivos)
                    </label>
                    <p style="font-size: 11px; color: var(--text-secondary); margin-top: 4px; margin-bottom: 6px;">
                        Clique em "Escolher arquivos" quantas vezes precisar para anexar todas as fotos ou páginas do cupom fiscal.
                    </p>
                    <input type="file" id="comprovante" name="comprovante[]" accept="image/*, application/pdf" multiple required style="padding: 6px; font-size: 12px; width: 100%; margin-top: 4px; background: #0f172a; border-radius: 6px; border: 1px solid #334155; color: #fff;">
                    <div id="comprovante-count-badge" style="font-size: 11px; font-weight: 600; color: #4ade80; margin-top: 6px; display: none;"></div>
                    <!-- GALERIA DE MINIATURAS DOS ARQUIVOS ANEXADOS -->
                    <div id="galeria-miniaturas-container" style="display: none; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(255, 255, 255, 0.15);"></div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="description">Descrição do Pagamento / Finalidade</label>
                    <input type="text" id="description" name="description" placeholder="Ex: Impressão de 10.000 Santinhos de Militância" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label for="value">Valor da Despesa (R$)</label>
                        <input type="text" id="value" name="value" placeholder="R$ 0,00" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #4ade80; font-size: 15px; font-weight: 700;" oninput="formatarMoeda(this);">
                    </div>
                    <div class="form-group">
                        <label for="date_incurred">Data da Despesa</label>
                        <input type="date" id="date_incurred" name="date_incurred" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label for="payment_method">Forma de Pagamento</label>
                        <select id="payment_method" name="payment_method" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                            <option value="">Selecione...</option>
                            <option value="PIX">PIX</option>
                            <option value="Transferência Bancária">Transferência Bancária</option>
                            <option value="Boleto Bancário">Boleto Bancário</option>
                            <option value="Débito em Conta">Débito em Conta</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="spce_category_id">Categoria SPCE/TSE</label>
                        <select id="spce_category_id" name="spce_category_id" required style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 13px;">
                            <option value="">Selecione a categoria...</option>
                            <?php foreach ($spceCategories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="notes">Observações Internas (Opcional)</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Informações adicionais ou notas de auditoria..." style="width: 100%; padding: 8px; border-radius: 6px; background: #1e293b; border: 1px solid #475569; color: #fff; font-size: 12px;"></textarea>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 24px;">
                    <input type="checkbox" id="mark_as_paid" name="mark_as_paid" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="mark_as_paid" style="margin-bottom: 0; font-weight: 500; cursor: pointer; font-size: 13px;">
                        Efetivar despesa como <strong>PAGO</strong> (Descontar saldo da conta bancária imediatamente)
                    </label>
                </div>

                <button type="submit" class="btn btn-teal btn-block" style="padding: 12px; font-weight: 800; font-size: 14px;">
                    💾 Confirmar e Enviar Despesa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DE VISUALIZAÇÃO COMPLETA DA DESPESA -->
<div id="modalVerDespesaAdmin" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 99999; display: none; align-items: center; justify-content: center; padding: 16px; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 2px solid #38bdf8; border-radius: 16px; max-width: 620px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); color: #f8fafc;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 14px; margin-bottom: 18px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #38bdf8; margin: 0; display: flex; align-items: center; gap: 8px;">
                🔍 Detalhes Completos da Despesa
            </h3>
            <button type="button" onclick="fecharModalVerDespesa()" style="background: transparent; border: none; color: #94a3b8; font-size: 20px; cursor: pointer; font-weight: bold;">✕</button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; background: rgba(30, 41, 59, 0.5); padding: 14px; border-radius: 10px; border: 1px solid #334155;">
            <div>
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block;">Data do Gasto</span>
                <strong id="v_date_incurred" style="font-size: 14px; color: #f8fafc;">-</strong>
            </div>
            <div>
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block;">Status da Despesa</span>
                <span id="v_status_badge">-</span>
            </div>
            <div>
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block;">Valor Total</span>
                <strong id="v_value" style="font-size: 16px; color: #4ade80; font-weight: 800;">-</strong>
            </div>
            <div>
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block;">Forma de Pagamento</span>
                <strong id="v_payment_method" style="font-size: 13px; color: #f8fafc;">-</strong>
            </div>
        </div>

        <div style="margin-bottom: 14px;">
            <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Fornecedor / Credor</span>
            <div id="v_supplier_name" style="font-size: 14px; font-weight: 700; color: #f8fafc;">-</div>
            <div id="v_supplier_cnpj" style="font-size: 12px; color: #cbd5e1;">-</div>
        </div>

        <div style="margin-bottom: 14px;">
            <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Descrição / Finalidade</span>
            <div id="v_description" style="font-size: 13px; color: #e2e8f0; background: #1e293b; padding: 10px; border-radius: 8px; border: 1px solid #334155; line-height: 1.4;">-</div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
            <div>
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Conta Bancária Origem</span>
                <div id="v_bank_name" style="font-size: 12px; color: #e2e8f0; font-weight: 600;">-</div>
            </div>
            <div>
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Categoria SPCE/TSE</span>
                <div id="v_spce_category" style="font-size: 12px; color: #e2e8f0; font-weight: 600;">-</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
            <div>
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Tipo de Despesa</span>
                <div id="v_expense_type" style="font-size: 12px; color: #e2e8f0; font-weight: 600;">-</div>
            </div>
            <div>
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Registrado Por</span>
                <div id="v_creator_name" style="font-size: 12px; color: #e2e8f0; font-weight: 600;">-</div>
            </div>
        </div>

        <div id="v_notes_container" style="margin-bottom: 18px; display: none;">
            <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Observações Internas</span>
            <div id="v_notes" style="font-size: 12px; color: #cbd5e1; background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.3); padding: 8px 12px; border-radius: 8px;">-</div>
        </div>

        <div style="display: flex; gap: 12px; border-top: 1px solid #334155; padding-top: 16px;">
            <button type="button" class="btn btn-secondary flex-1" onclick="fecharModalVerDespesa()">Fechar</button>
            <div id="v_anexo_btn_container" class="flex-1"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
    const baseUrlGlobal = '<?= $this->baseUrl('') ?>';

    function alternarAbaDespesas(aba) {
        const sectionLancamento = document.getElementById('section-lancamento-despesa');
        const sectionHistorico = document.getElementById('section-historico-despesas');
        const btnLancamento = document.getElementById('btn-tab-lancamento');
        const btnHistorico = document.getElementById('btn-tab-historico');

        if (aba === 'lancamento') {
            if (sectionLancamento) sectionLancamento.style.display = 'block';
            if (sectionHistorico) sectionHistorico.style.display = 'none';
            if (btnLancamento) btnLancamento.classList.add('active');
            if (btnHistorico) btnHistorico.classList.remove('active');
        } else {
            if (sectionLancamento) sectionLancamento.style.display = 'none';
            if (sectionHistorico) sectionHistorico.style.display = 'block';
            if (btnLancamento) btnLancamento.classList.remove('active');
            if (btnHistorico) btnHistorico.classList.add('active');
        }
    }

    function abrirModalVerDespesa(exp) {
        document.getElementById('v_date_incurred').textContent = exp.date_incurred ? exp.date_incurred.split('-').reverse().join('/') : '-';
        
        // Status Badge
        const statusDiv = document.getElementById('v_status_badge');
        if (exp.status === 'PAGO') {
            statusDiv.innerHTML = '<span class="badge badge-success" style="font-size: 12px;">Pago</span>';
        } else if (exp.status === 'APROVADO') {
            statusDiv.innerHTML = '<span class="badge badge-info" style="font-size: 12px;">Aprovado</span>';
        } else if (exp.status === 'REJEITADO') {
            statusDiv.innerHTML = '<span class="badge badge-danger" style="font-size: 12px;">Rejeitado</span>';
        } else {
            statusDiv.innerHTML = '<span class="badge badge-warning" style="font-size: 12px;">Pendente</span>';
        }

        const valFloat = parseFloat(exp.value || 0);
        document.getElementById('v_value').textContent = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valFloat);

        document.getElementById('v_payment_method').textContent = exp.payment_method || 'Não especificada';
        document.getElementById('v_supplier_name').textContent = exp.supplier_name || 'Fornecedor Não Informado';

        // Format CNPJ/CPF
        let cnpjRaw = (exp.supplier_cnpj_cpf || '').replace(/\D/g, '');
        let fmtCnpj = cnpjRaw;
        if (cnpjRaw.length === 14) {
            fmtCnpj = cnpjRaw.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
        } else if (cnpjRaw.length === 11) {
            fmtCnpj = cnpjRaw.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
        }
        document.getElementById('v_supplier_cnpj').textContent = fmtCnpj ? 'CNPJ/CPF: ' + fmtCnpj : 'CNPJ/CPF: Não cadastrado';

        document.getElementById('v_description').textContent = exp.description || 'Sem descrição';
        document.getElementById('v_bank_name').textContent = exp.bank_name || 'Não informada';

        let spceText = 'Sem categoria vinculada';
        if (exp.spce_code) {
            spceText = exp.spce_code + ' - ' + (exp.spce_desc || '');
        }
        document.getElementById('v_spce_category').textContent = spceText;
        document.getElementById('v_expense_type').textContent = exp.expense_type_name || 'Geral';
        document.getElementById('v_creator_name').textContent = exp.creator_name || 'Colaborador';

        const notesContainer = document.getElementById('v_notes_container');
        if (exp.notes && exp.notes.trim() !== '') {
            notesContainer.style.display = 'block';
            document.getElementById('v_notes').textContent = exp.notes;
        } else {
            notesContainer.style.display = 'none';
        }

        // Botão de Anexo
        const anexoContainer = document.getElementById('v_anexo_btn_container');
        if (exp.doc_id) {
            anexoContainer.innerHTML = `
                <a href="${baseUrlGlobal}admin/financeiro/comprovante?id=${exp.doc_id}&type=expense" target="_blank" class="btn btn-teal" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 700; text-decoration: none; font-size: 13px;">
                    📄 Ver Comprovante / Anexos
                </a>
            `;
        } else {
            anexoContainer.innerHTML = `
                <button type="button" disabled class="btn btn-secondary" style="width: 100%; opacity: 0.6; cursor: not-allowed; font-size: 13px;">
                    🚫 Sem Anexo Cadastrado
                </button>
            `;
        }

        document.getElementById('modalVerDespesaAdmin').style.display = 'flex';
    }

    function fecharModalVerDespesa() {
        document.getElementById('modalVerDespesaAdmin').style.display = 'none';
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

    const fotoCnpjInputAdmin = document.getElementById('foto_cnpj_ocr');
    const btnScanOcrAdmin = document.getElementById('btn-scan-ocr');
    const btnPularOcrAdmin = document.getElementById('btn-pular-ocr');
    const ocrStatusBadgeAdmin = document.getElementById('ocr_status_badge');
    const dadosContainerAdmin = document.getElementById('dados-despesa-container');
    const blocoCapturaCnpjAdmin = document.getElementById('bloco-captura-cnpj');
    const inputComprovanteAdmin = document.getElementById('comprovante');
    const badgeComprovanteAdmin = document.getElementById('comprovante-count-badge');
    const supplierSelect = document.getElementById('supplier_id');

    function revelarEtapa2Admin() {
        if (blocoCapturaCnpjAdmin) blocoCapturaCnpjAdmin.style.display = 'none';
        if (dadosContainerAdmin) {
            dadosContainerAdmin.style.display = 'block';
            dadosContainerAdmin.scrollIntoView({ behavior: 'smooth' });
        }
    }

    if (btnPularOcrAdmin) {
        btnPularOcrAdmin.addEventListener('click', function() {
            revelarEtapa2Admin();
        });
    }

    // Acumulador de arquivos via DataTransfer API
    const dataTransferComprovantesAdmin = new DataTransfer();

    function renderizarGaleriaMiniaturasAdmin() {
        const galeriaContainer = document.getElementById('galeria-miniaturas-container');
        if (!inputComprovanteAdmin || !galeriaContainer) return;

        inputComprovanteAdmin.files = dataTransferComprovantesAdmin.files;

        const count = dataTransferComprovantesAdmin.files.length;
        if (count > 0) {
            if (badgeComprovanteAdmin) {
                badgeComprovanteAdmin.style.display = 'block';
                badgeComprovanteAdmin.textContent = `✔ ${count} foto(s)/comprovante(s) anexado(s) e pronto(s) para envio.`;
            }
            galeriaContainer.style.display = 'grid';
            galeriaContainer.innerHTML = '';

            Array.from(dataTransferComprovantesAdmin.files).forEach((file, index) => {
                const card = document.createElement('div');
                card.style.cssText = 'position: relative; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 6px; display: flex; flex-direction: column; align-items: center; justify-content: space-between; gap: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3);';

                const btnRemove = document.createElement('button');
                btnRemove.type = 'button';
                btnRemove.innerHTML = '✖';
                btnRemove.title = 'Remover esta foto';
                btnRemove.style.cssText = 'position: absolute; top: -6px; right: -6px; background: #ef4444; color: #fff; border: none; width: 20px; height: 20px; border-radius: 50%; font-size: 10px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.5);';
                btnRemove.onclick = function(e) {
                    e.stopPropagation();
                    removerArquivoDaGaleriaAdmin(index);
                };

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
            if (badgeComprovanteAdmin) badgeComprovanteAdmin.style.display = 'none';
            galeriaContainer.style.display = 'none';
            galeriaContainer.innerHTML = '';
        }
    }

    function removerArquivoDaGaleriaAdmin(index) {
        const dt = new DataTransfer();
        Array.from(dataTransferComprovantesAdmin.files).forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        dataTransferComprovantesAdmin.items.clear();
        Array.from(dt.files).forEach(f => dataTransferComprovantesAdmin.items.add(f));

        renderizarGaleriaMiniaturasAdmin();
    }

    if (inputComprovanteAdmin) {
        inputComprovanteAdmin.addEventListener('change', function(e) {
            if (e.target.files && e.target.files.length > 0) {
                Array.from(e.target.files).forEach(file => {
                    dataTransferComprovantesAdmin.items.add(file);
                });
                renderizarGaleriaMiniaturasAdmin();
            }
        });
    }

    function executarOCRAdmin() {
        const file = fotoCnpjInputAdmin ? fotoCnpjInputAdmin.files[0] : null;

        if (!file) {
            if (ocrStatusBadgeAdmin) {
                ocrStatusBadgeAdmin.style.display = 'block';
                ocrStatusBadgeAdmin.innerHTML = `
                    <div style="padding: 10px 12px; background: rgba(234, 179, 8, 0.15); border: 1px solid #eab308; border-radius: 8px; color: #fde047; font-weight: 600; font-size: 12px;">
                        ⚠️ Por favor, escolha uma foto do CNPJ primeiro no campo acima!
                    </div>
                `;
            }
            return;
        }

        if (ocrStatusBadgeAdmin) {
            ocrStatusBadgeAdmin.style.display = 'block';
            ocrStatusBadgeAdmin.innerHTML = `
                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">⏳</span>
                    <span>Lendo foto do CNPJ via OCR... Por favor, aguarde.</span>
                </div>
            `;
        }

        if (file.type === 'application/pdf') {
            revelarEtapa2Admin();
            return;
        }

        const rodarOCR = () => {
            if (!window.Tesseract) {
                revelarEtapa2Admin();
                return;
            }

            otimizarImagemParaOCR(file, function(processedFile) {
                Tesseract.recognize(processedFile, 'por', {
                    logger: m => {
                        if (m.status === 'recognizing text' && ocrStatusBadgeAdmin) {
                            const pct = Math.round((m.progress || 0) * 100);
                            ocrStatusBadgeAdmin.innerHTML = `
                                <div style="padding: 10px 12px; background: rgba(13, 148, 136, 0.2); border: 1px solid var(--accent-teal); border-radius: 8px; color: #5eead4; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px;">🔍</span>
                                    <span>Lendo foto do CNPJ via OCR (${pct}%)...</span>
                                </div>
                            `;
                        }
                    }
                }).then(({ data: { text } }) => {
                    console.log("Texto extraído via OCR:", text);
                    const cleanCnpj = extrairCNPJDoTexto(text);
                    if (cleanCnpj && supplierSelect) {
                        for (let option of supplierSelect.options) {
                            if (option.text.replace(/\D/g, "").includes(cleanCnpj)) {
                                supplierSelect.value = option.value;
                                break;
                            }
                        }
                    }
                    revelarEtapa2Admin();
                }).catch(err => {
                    console.error("Erro OCR:", err);
                    revelarEtapa2Admin();
                });
            });
        };

        if (typeof Tesseract === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
            script.onload = rodarOCR;
            script.onerror = () => revelarEtapa2Admin();
            document.head.appendChild(script);
        } else {
            rodarOCR();
        }
    }

    if (fotoCnpjInputAdmin) {
        fotoCnpjInputAdmin.addEventListener('change', function(e) {
            executarOCRAdmin();
        });
    }

    if (btnScanOcrAdmin) {
        btnScanOcrAdmin.addEventListener('click', function() {
            executarOCRAdmin();
        });
    }
</script>
