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

    <!-- MODAL DE CONFIRMAÇÃO PÓS-ENVIO DO COMPROVANTE -->
    <?php if (isset($_GET['envio_sucesso']) && $_GET['envio_sucesso'] == '1'): ?>
    <div id="modalSucessoEnvio" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
        <div style="background: #0f172a; border: 2px solid #22c55e; border-radius: 16px; max-width: 440px; width: 100%; padding: 24px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <div style="width: 56px; height: 56px; background: rgba(34, 197, 94, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px auto; color: #4ade80; font-size: 28px; font-weight: bold;">
                ✓
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: #f8fafc; margin-bottom: 8px;">Despesa Enviada com Sucesso!</h3>
            <p style="font-size: 13px; color: #94a3b8; margin-bottom: 20px; line-height: 1.4;">
                O cupom fiscal foi registrado e criptografado no sistema. O que deseja fazer agora?
            </p>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="<?= $this->baseUrl('portal/despesas') ?>" style="background: #22c55e; color: #0f172a; font-weight: 800; padding: 12px; border-radius: 8px; text-decoration: none; font-size: 13px; display: block;">
                    📸 Enviar Novo Comprovante / Arquivo
                </a>
                <button type="button" onclick="document.getElementById('modalSucessoEnvio').style.display='none';" style="background: rgba(255, 255, 255, 0.1); color: #f8fafc; font-weight: 600; padding: 12px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2); font-size: 13px; cursor: pointer; display: block; width: 100%;">
                    ✅ Finalizar Envio
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form action="<?= $this->baseUrl('portal/despesas') ?>" method="POST" enctype="multipart/form-data" id="expenseForm">
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
                    Ou clique aqui para digitar o CNPJ manualmente
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

            <!-- DADOS DO FORNECEDOR (CNPJ + RAZÃO SOCIAL - PREENCHIDOS E EDITÁVEIS) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div class="form-group">
                    <label for="supplier_cnpj_cpf">CPF ou CNPJ do Fornecedor (editável)</label>
                    <input type="text" id="supplier_cnpj_cpf" name="supplier_cnpj_cpf" placeholder="00.000.000/0001-00 ou 000.000.000-00" required style="font-weight: 600;">
                    <div id="cnpj_supplier_info" style="margin-top: 4px; font-size: 11px;"></div>
                </div>
                <div class="form-group">
                    <label for="supplier_name">Razão Social / Nome Fantasia (editável)</label>
                    <input type="text" id="supplier_name" name="supplier_name" placeholder="Nome do Fornecedor" required style="font-weight: 500;">
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

            <!-- DEMAIS DADOS DA DESPESA -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
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
                    <label for="value">Valor Total (R$)</label>
                    <input type="text" id="value" name="value" placeholder="R$ 0,00" required style="font-weight: 600; color: var(--warning-color);" oninput="formatarMoeda(this);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label for="description">Finalidade do Gasto / Descrição</label>
                    <input type="text" id="description" name="description" placeholder="Ex: Alimentação da equipe de panfletagem" required>
                </div>
                <div class="form-group">
                    <label for="date_incurred">Data do Gasto</label>
                    <input type="date" id="date_incurred" name="date_incurred" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <button type="submit" id="submitBtn" class="btn btn-teal btn-block" style="margin-top: 10px;">
                Confirmar e Enviar Despesa
            </button>
        </div>
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

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
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
</script>
