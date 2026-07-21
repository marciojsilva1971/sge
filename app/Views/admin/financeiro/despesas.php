<div class="page-header">
    <div>
        <h2>Lançamento de Despesas de Campanha</h2>
        <p class="subtitle">Controle de pagamentos de fornecedores, serviços, material gráfico e aluguéis.</p>
    </div>
    <div>
        <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="btn btn-secondary">
            ⬅️ Voltar ao Financeiro
        </a>
    </div>
</div>

<?php include __DIR__ . '/_nav_tabs.php'; ?>

<div class="dashboard-sections">
    
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
                <a href="<?= $this->baseUrl('admin/financeiro/despesas') ?>" style="background: #22c55e; color: #0f172a; font-weight: 800; padding: 12px; border-radius: 8px; text-decoration: none; font-size: 13px; display: block;">
                    📸 Enviar Novo Comprovante / Arquivo
                </a>
                <button type="button" onclick="document.getElementById('modalSucessoEnvio').style.display='none';" style="background: rgba(255, 255, 255, 0.1); color: #f8fafc; font-weight: 600; padding: 12px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2); font-size: 13px; cursor: pointer; display: block; width: 100%;">
                    ✅ Finalizar Envio
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lançamento de Despesa -->
    <div class="panel-card flex-1">
        <div class="card-header">
            <h3>Lançar Nova Despesa Geral</h3>
        </div>
        <form action="<?= $this->baseUrl('admin/financeiro/despesas') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

            <!-- 1º PASSO: CAPTURA DO CNPJ (SERÁ ESCONDIDO APÓS LEITURA DO CNPJ) -->
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
                
                <!-- MENSAGEM SOLICITADA EM DESTAQUE NO TOPO DA ETAPA 2 -->
                <div style="background: rgba(56, 189, 248, 0.12); border-left: 4px solid #38bdf8; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px;">
                    <p style="font-size: 13px; font-weight: 700; color: #7dd3fc; margin: 0; line-height: 1.4;">
                        📸 Envie ou fotografe o cupom fiscal de forma que seja possivel a visualização de todas as despesas e o total. Você pode enviar mais de um arquivo ou foto
                    </p>
                </div>

                <!-- DADOS DO FORNECEDOR (SELEÇÃO OU PREENCHIMENTO EDITÁVEL) -->
                <div style="display: flex; gap: 16px; margin-bottom: 14px;">
                    <div class="form-group flex-1">
                        <label for="supplier_id">Fornecedor / Credor (editável)</label>
                        <select id="supplier_id" name="supplier_id" required style="font-weight: 500;">
                            <option value="">Selecione o fornecedor...</option>
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['corporate_name']) ?> (CNPJ/CPF: <?= htmlspecialchars($sup['cnpj_cpf']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="bank_account_id">Conta Bancária Origem</label>
                        <select id="bank_account_id" name="bank_account_id" required>
                            <option value="">Selecione a conta...</option>
                            <?php foreach ($bankAccounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?> (Saldo: R$ <?= number_format($acc['balance'], 2, ',', '.') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- UPLOAD DAS FOTOS/ARQUIVOS DO COMPROVANTE FISCAL (COM DESPESAS E TOTAL) -->
                <div class="form-group" style="background: rgba(15, 23, 42, 0.5); border: 1px dashed rgba(255, 255, 255, 0.2); padding: 12px; border-radius: 10px; margin-bottom: 14px;">
                    <label for="comprovante" style="font-size: 12px; font-weight: 700; color: #4ade80;">
                        📁 Fotos / Comprovante(s) do Cupom Fiscal (Aceita 1 ou mais arquivos)
                    </label>
                    <p style="font-size: 11px; color: var(--text-secondary); margin-top: 4px; margin-bottom: 6px;">
                        Clique em "Escolher arquivos" quantas vezes precisar para anexar todas as fotos ou páginas do cupom fiscal.
                    </p>
                    <input type="file" id="comprovante" name="comprovante[]" accept="image/*, application/pdf" multiple required style="padding: 6px; font-size: 12px; width: 100%; margin-top: 4px;">
                    <div id="comprovante-count-badge" style="font-size: 11px; font-weight: 600; color: #4ade80; margin-top: 6px; display: none;"></div>
                    <!-- GALERIA DE MINIATURAS DOS ARQUIVOS ANEXADOS -->
                    <div id="galeria-miniaturas-container" style="display: none; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(255, 255, 255, 0.15);"></div>
                </div>

                <div class="form-group">
                    <label for="description">Descrição do Pagamento / Finalidade</label>
                    <input type="text" id="description" name="description" placeholder="Ex: Impressão de 10.000 Santinhos de Militância" required>
                </div>

                <div style="display: flex; gap: 16px;">
                    <div class="form-group flex-1">
                        <label for="value">Valor da Despesa (R$)</label>
                        <input type="text" id="value" name="value" placeholder="R$ 0,00" required style="font-size: 16px; font-weight: 600; color: var(--accent-teal-hover);" oninput="formatarMoeda(this);">
                    </div>
                    <div class="form-group flex-1">
                        <label for="date_incurred">Data da Despesa</label>
                        <input type="date" id="date_incurred" name="date_incurred" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div style="display: flex; gap: 16px;">
                    <div class="form-group flex-1">
                        <label for="payment_method">Forma de Pagamento</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Selecione...</option>
                            <option value="PIX">PIX</option>
                            <option value="Transferência Bancária">Transferência Bancária</option>
                            <option value="Boleto Bancário">Boleto Bancário</option>
                            <option value="Débito em Conta">Débito em Conta</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="spce_category_id">Categoria SPCE/TSE</label>
                        <select id="spce_category_id" name="spce_category_id" required>
                            <option value="">Selecione a categoria...</option>
                            <?php foreach ($spceCategories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['code']) ?> - <?= htmlspecialchars($cat['description']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Observações Internas (Opcional)</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Informações adicionais ou notas de auditoria..."></textarea>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 24px;">
                    <input type="checkbox" id="mark_as_paid" name="mark_as_paid" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="mark_as_paid" style="margin-bottom: 0; font-weight: 500; cursor: pointer;">
                        Efetivar despesa como <strong>PAGO</strong> (Descontar saldo da conta bancária imediatamente)
                    </label>
                </div>

                <button type="submit" class="btn btn-teal btn-block">
                    💾 Confirmar e Enviar Despesa
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Despesas -->
    <div class="panel-card flex-1" style="min-width: 60%;">
        <div class="card-header">
            <h3>Despesas Registradas</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Fornecedor / Categoria</th>
                        <th>Descrição</th>
                        <th>Conta / Pagto</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Comprovante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary);">Nenhuma despesa registrada ainda.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $exp): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($exp['date_incurred'])) ?></td>
                                <td>
                                    <div class="user-log-name"><?= htmlspecialchars($exp['supplier_name']) ?></div>
                                    <span style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($exp['spce_code']) ?> - <?= htmlspecialchars($exp['spce_desc']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($exp['description']) ?></td>
                                <td>
                                    <div style="font-size: 12px; font-weight: 500;"><?= htmlspecialchars($exp['bank_name']) ?></div>
                                    <span style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($exp['payment_method']) ?></span>
                                </td>
                                <td style="font-weight: 600; color: var(--text-primary);">
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
                                <td>
                                    <?php if (!empty($exp['doc_id'])): ?>
                                        <a href="<?= $this->baseUrl('admin/financeiro/comprovante?id=' . $exp['doc_id'] . '&type=expense') ?>" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 8px;">
                                            📄 Ver
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary);">Sem anexo</span>
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

<!-- Tesseract.js OCR -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
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

        // 1. Tentar encontrar padrões numéricos próximos ao rótulo CNPJ
        const matches = text.match(/(?:CNPJ|C\.N\.P\.J\.?|MF)?[\s\:\.\-\/]*([0-9OolI|sS\.\-\/\s]{14,25})/gi) || [];
        for (let raw of matches) {
            let clean = raw.replace(/[Oo]/g, '0').replace(/[Il|]/g, '1').replace(/[sS]/g, '5').replace(/\D/g, '');
            for (let i = 0; i <= clean.length - 14; i++) {
                let sub = clean.substring(i, i + 14);
                if (validarCNPJ(sub)) return sub;
            }
        }

        // 2. Extração genérica de todas as sequências numéricas limpas
        const apenasNumeros = text.replace(/[Oo]/g, '0').replace(/[Il|]/g, '1').replace(/[sS]/g, '5').replace(/\D/g, ' ');
        const tokens = apenasNumeros.split(/\s+/);
        for (let token of tokens) {
            if (token.length === 14 && validarCNPJ(token)) {
                return token;
            }
        }

        // 3. Varredura por janela deslizante de 14 dígitos no texto acumulado
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

    // Acumulador de arquivos via DataTransfer API (evita substituição ao reabrir o seletor)
    const dataTransferComprovantesAdmin = new DataTransfer();

    function renderizarGaleriaMiniaturasAdmin() {
        const galeriaContainer = document.getElementById('galeria-miniaturas-container');
        if (!inputComprovanteAdmin || !galeriaContainer) return;

        // Atualiza o elemento input file com os arquivos acumulados
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

                // Botão de remoção (X)
                const btnRemove = document.createElement('button');
                btnRemove.type = 'button';
                btnRemove.innerHTML = '✖';
                btnRemove.title = 'Remover esta foto';
                btnRemove.style.cssText = 'position: absolute; top: -6px; right: -6px; background: #ef4444; color: #fff; border: none; width: 20px; height: 20px; border-radius: 50%; font-size: 10px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.5);';
                btnRemove.onclick = function(e) {
                    e.stopPropagation();
                    removerArquivoDaGaleriaAdmin(index);
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
