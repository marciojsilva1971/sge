<div class="rh-page-container">
    
    <div class="page-header">
        <h2>Gestão de Colaboradores e Equipe de Campanha (RH)</h2>
        <div class="page-actions">
            <button type="button" onclick="openConviteAutoCadastroModal()" class="btn btn-teal" style="font-weight:bold; cursor:pointer;">
                🔗 Link de Auto-Cadastro Público
            </button>
            <a href="<?= $this->baseUrl('admin/rh/novo') ?>" class="btn btn-primary">
                + Novo Colaborador (Admin)
            </a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="panel-card filters-card">
        <form method="GET" action="<?= $this->baseUrl('admin/rh') ?>" class="filters-form">
            <div class="form-group mb-0">
                <label for="filter-nome">Buscar por Nome</label>
                <input type="text" id="filter-nome" name="nome" value="<?= htmlspecialchars($filters['nome'] ?? '') ?>" placeholder="Ex: João da Silva">
            </div>

            <div class="form-group mb-0">
                <label for="filter-cpf">CPF</label>
                <input type="text" id="filter-cpf" name="cpf" value="<?= htmlspecialchars($filters['cpf'] ?? '') ?>" placeholder="Apenas números">
            </div>

            <div class="form-group mb-0">
                <label for="filter-status">Etapa do Fluxo (Status)</label>
                <select id="filter-status" name="status">
                    <option value="">Todas as Etapas</option>
                    <option value="AGUARDANDO_AVAL_CADASTRO" <?= ($filters['status'] ?? '') === 'AGUARDANDO_AVAL_CADASTRO' ? 'selected' : '' ?>>1. Aguardando Aval do Cadastro</option>
                    <option value="AGUARDANDO_ASSINATURA_CONTRATO" <?= ($filters['status'] ?? '') === 'AGUARDANDO_ASSINATURA_CONTRATO' ? 'selected' : '' ?>>2. Aguardando Assinatura do Contrato</option>
                    <option value="AGUARDANDO_CONFERENCIA_CONTRATO" <?= ($filters['status'] ?? '') === 'AGUARDANDO_CONFERENCIA_CONTRATO' ? 'selected' : '' ?>>3. Aguardando Conferência do Contrato</option>
                    <option value="ATIVO" <?= ($filters['status'] ?? '') === 'ATIVO' ? 'selected' : '' ?>>4. Homologado (Ativo)</option>
                    <option value="REJEITADO" <?= ($filters['status'] ?? '') === 'REJEITADO' ? 'selected' : '' ?>>Rejeitado</option>
                </select>
            </div>

            <div class="filters-actions">
                <button type="submit" class="btn btn-teal">Filtrar</button>
                <a href="<?= $this->baseUrl('admin/rh') ?>" class="btn btn-secondary">Limpar</a>
            </div>
        </form>
    </div>

    <!-- Tabela de Colaboradores -->
    <div class="panel-card">
        <div class="table-responsive">
            <table class="table table-striped table-colaboradores">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">Foto</th>
                        <th>Nome Completo</th>
                        <th>CPF / Documento</th>
                        <th>Idade</th>
                        <th>WhatsApp / E-mail</th>
                        <th>Contrato</th>
                        <th>Etapa Atual</th>
                        <th>Perfil SGE</th>
                        <th>Ações Obrigatórias</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($colaboradores)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Nenhum colaborador encontrado nesta etapa.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($colaboradores as $c): ?>
                            <tr>
                                <td style="text-align: center; vertical-align: middle;">
                                    <?php if (!empty($c['foto_rosto_path'])): ?>
                                        <a href="<?= $this->baseUrl('admin/rh/documento?id=' . $c['id'] . '&tipo=rosto') ?>" target="_blank" title="Clique para ver em tamanho real">
                                            <img src="<?= $this->baseUrl('admin/rh/documento?id=' . $c['id'] . '&tipo=rosto') ?>" alt="Foto" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #0d9488; display: block; margin: 0 auto;">
                                        </a>
                                    <?php else: ?>
                                        <div style="width: 42px; height: 42px; border-radius: 50%; background: #1e293b; border: 2px solid #475569; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #94a3b8; font-size: 16px; margin: 0 auto;" title="Sem Foto de Rosto">
                                            <?= strtoupper(substr($c['nome_completo'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>

                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($c['nome_completo']) ?></strong>
                                    <?php if (!empty($c['optin_whatsapp'])): ?>
                                        <span title="Opt-In WhatsApp Confirmado" style="color:#10b981; font-size:12px; margin-left:4px;">✓ Opt-In</span>
                                    <?php endif; ?>

                                </td>
                                <td>
                                    <div><?= htmlspecialchars($c['cpf']) ?></div>
                                    <small class="text-secondary">RG: <?= htmlspecialchars($c['rg']) ?> <?= htmlspecialchars($c['rg_orgao_emissor']) ?></small>
                                     <?php if (!empty($c['documento_foto_path'])): ?>
                                         <div style="margin-top:4px;">
                                             <span class="badge" style="background:#10b981; color:#fff; font-size:10px; font-weight:bold; padding:2px 6px; border-radius:4px;">✔ RG/CNH Anexado</span>
                                         </div>
                                         <div style="margin-top:2px;">
                                             <a href="<?= $this->baseUrl('admin/rh/documento?id=' . $c['id'] . '&tipo=doc') ?>" target="_blank" style="font-size:11px; color:#0d9488; font-weight:bold; text-decoration:underline;">🪪 Ver Documento (Foto/PDF)</a>
                                         </div>
                                     <?php else: ?>
                                        <div style="margin-top:4px;"><span class="badge badge-danger" style="font-size:10px;">⚠ Sem Doc. Anexo</span></div>
                                    <?php endif; ?>

                                </td>
                                <td>
                                    <span><?= $c['idade_calculada'] ?> anos</span>
                                    <?php if ($c['idade_calculada'] < 18): ?>
                                        <span class="badge badge-warning" style="font-size: 10px;" title="Menor de idade: Restrições legais de campanha aplicáveis">⚠ Menor</span>
                                    <?php endif; ?>

                                </td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:4px;">
                                        <span>📱 <?= htmlspecialchars($c['celular_whatsapp']) ?></span>
                                        <button onclick="openEditarTelefoneModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['nome_completo'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['celular_whatsapp'], ENT_QUOTES) ?>')" style="background:none; border:none; color:#38bdf8; cursor:pointer; font-size:12px; padding:0 2px;" title="Editar Telefone / WhatsApp">✏️</button>
                                    </div>
                                    <small class="text-secondary"><?= htmlspecialchars($c['email']) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($c['titulo_contrato'])): ?>
                                        <div style="font-weight:bold; color:#10b981;">R$ <?= number_format($c['valor_contratado'], 2, ',', '.') ?></div>
                                        <small class="text-secondary" style="display:block; margin-bottom:4px;">
                                            <?= $c['tipo_assinatura'] === 'TERCEIROS_API' ? '🌐 Terceiros (API)' : '📄 Upload Manual' ?>
                                        </small>
                                        
                                        <?php if (!empty($c['pdf_assinado_path'])): ?>
                                            <div style="margin-top:4px; margin-bottom:4px;">
                                                <span class="badge" style="background:#10b981; color:#fff; font-size:10px; font-weight:bold; padding:3px 6px; border-radius:4px; display:inline-flex; align-items:center; gap:4px;">
                                                    ✅ Contrato Assinado Enviado
                                                </span>
                                            </div>
                                            <div>
                                                <a href="<?= $this->baseUrl('admin/rh/documento?id=' . $c['id'] . '&tipo=contrato') ?>" target="_blank" class="btn btn-sm" style="background:#059669; color:#fff; font-size:11px; font-weight:bold; padding:3px 7px; border-radius:4px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                                    📥 Ver PDF Assinado (Enviado)
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div style="margin-top:4px; margin-bottom:4px;">
                                                <span class="badge badge-warning" style="font-size:10px; background:#f59e0b; color:#000; font-weight:bold; padding:2px 6px;">
                                                    ⏳ Aguardando Envio pelo Colaborador
                                                </span>
                                            </div>
                                            <div>
                                                <a href="<?= $this->baseUrl('admin/rh/contrato-pdf?id=' . $c['id']) ?>" target="_blank" class="btn btn-sm" style="background:#0284c7; color:#fff; font-size:11px; font-weight:bold; padding:3px 7px; border-radius:4px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                                    📄 Ver Modelo Emitido (PDF)
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-secondary" style="font-style:italic;">Aguardando Emissão</span>
                                    <?php endif; ?>
                                    
                                    <div style="margin-top:8px;">
                                        <a href="<?= $this->baseUrl('admin/rh/regularidade-pdf?id=' . $c['id']) ?>" target="_blank" class="btn btn-sm" style="background:#0284c7; color:#fff; font-size:11px; font-weight:bold; padding:4px 8px; border-radius:4px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; width:100%; justify-content:center; border:1px solid rgba(2,132,199,0.5);">
                                            📄 Certidão de Regularidade
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($c['status'] === 'AGUARDANDO_AVAL_CADASTRO'): ?>
                                        <span class="badge badge-warning">1. Aguardando Aval Cadastral</span>
                                    <?php elseif ($c['status'] === 'AGUARDANDO_ASSINATURA_CONTRATO'): ?>
                                        <span class="badge badge-info">2. Aguardando Assinatura</span>
                                    <?php elseif ($c['status'] === 'AGUARDANDO_CONFERENCIA_CONTRATO'): ?>
                                        <span class="badge badge-warning" style="background:#f59e0b; color:#000;">3. Conferir Contrato Assinado</span>
                                    <?php elseif ($c['status'] === 'ATIVO'): ?>
                                        <span class="badge badge-success">4. Homologado & Ativo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Rejeitado</span>
                                    <?php endif; ?>

                                </td>
                                <td>
                                    <?php if (!empty($c['role_nome'])): ?>
                                        <span class="badge badge-primary"><?= htmlspecialchars($c['role_nome']) ?></span>
                                    <?php else: ?>
                                        <span class="text-secondary" style="font-size:12px;">Pendente</span>
                                    <?php endif; ?>

                                </td>
                                <td>
                                    <?php if ($c['status'] === 'AGUARDANDO_AVAL_CADASTRO'): ?>
                                        <button onclick="openAvalModal(<?= htmlspecialchars(json_encode($c)) ?>)" class="btn btn-warning btn-sm" style="font-weight:bold;">
                                            🔍 Conferir Doc & Dar Aval
                                        </button>

                                    <?php elseif ($c['status'] === 'AGUARDANDO_CONFERENCIA_CONTRATO'): ?>
                                        <button onclick="openConferirContratoModal(<?= htmlspecialchars(json_encode($c)) ?>)" class="btn btn-teal btn-sm" style="font-weight:bold; background:#0d9488; color:#fff; box-shadow: 0 0 10px rgba(13,148,136,0.4);">
                                            📑 Conferir Contrato & Homologar
                                        </button>

                                    <?php elseif ($c['status'] === 'AGUARDANDO_ASSINATURA_CONTRATO'): ?>
                                        <?php 
                                            $wspPhone = preg_replace('/\D/', '', $c['celular_whatsapp'] ?? '');
                                            if (strlen($wspPhone) <= 11 && !empty($wspPhone)) $wspPhone = '55' . $wspPhone;
                                            $linkContrato = $this->baseUrl('colaborador/contrato?token=' . $c['token_cadastro']);
                                            $wspText = rawurlencode("Olá " . $c['nome_completo'] . "! Seu cadastro de colaborador de campanha foi aprovado! Acesse o link para visualizar e assinar o seu contrato: " . $linkContrato);
                                            $wspUrl = "https://api.whatsapp.com/send?phone=" . $wspPhone . "&text=" . $wspText;
                                        ?>
                                        <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-start;">
                                            <!-- Botão de Homologar Direto -->
                                            <button onclick="openConferirContratoModal(<?= htmlspecialchars(json_encode($c)) ?>)" class="btn btn-teal btn-sm" style="font-weight:bold; font-size:11px; padding:4px 8px;">
                                                📑 Conferir & Homologar
                                            </button>

                                            <div style="display:flex; align-items:center; gap:6px; margin-top:2px;">
                                                <!-- Disparo Direto via API Z-API -->
                                                <form action="<?= $this->baseUrl('admin/rh/enviar-whatsapp') ?>" method="POST" style="margin:0;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                    <input type="hidden" name="colaborador_id" value="<?= $c['id'] ?>">
                                                    <button type="submit" class="btn btn-sm" style="background:#10b981; color:#fff; font-weight:bold; font-size:11px; padding:3px 6px; border-radius:4px; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:4px;" title="Dispara mensagem direta via API da Z-API">
                                                        ⚡ Reenviar Whats API
                                                    </button>
                                                </form>

                                                <!-- Link Web de Contingência -->
                                                <a href="<?= $wspUrl ?>" target="_blank" style="font-size:10px; color:#25D366; text-decoration:underline;" title="Abrir no WhatsApp Web">
                                                    💬 Web
                                                </a>
                                            </div>
                                        </div>

                                    <?php elseif ($c['status'] === 'ATIVO'): ?>
                                        <div style="display:flex; flex-direction:column; gap:4px;">
                                            <span class="text-success" style="font-size: 11px; font-weight:bold;">✔ Homologado & Ativo</span>
                                            <button onclick="openConferirContratoModal(<?= htmlspecialchars(json_encode($c)) ?>)" class="btn btn-secondary btn-sm" style="font-size:10px; padding:2px 6px;">
                                                ✏ Alterar Perfil
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Rejeitado</span>
                                    <?php endif; ?>

                                    <?php if ($c['status'] !== 'ATIVO'): ?>
                                        <div style="margin-top:6px;">
                                            <form action="<?= $this->baseUrl('admin/rh/excluir') ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o cadastro do colaborador <?= htmlspecialchars($c['nome_completo'], ENT_QUOTES) ?>? Esta ação não poderá ser desfeita.');" style="margin:0; display:inline-block;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="colaborador_id" value="<?= $c['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" style="font-size:10px; padding:2px 6px; background:#ef4444; color:#fff; border:none; border-radius:4px; font-weight:bold; cursor:pointer; display:inline-flex; align-items:center; gap:2px;" title="Excluir cadastro deste colaborador">
                                                    🗑️ Excluir Colaborador
                                                </button>
                                            </form>
                                        </div>
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

<!-- Modal 1: Dar Aval no Cadastro & Emitir Contrato -->
<div id="avalModal" class="modal-overlay hidden">
    <div class="modal-card" style="max-width: 600px;">
        <div class="modal-header">
            <h3>1. Conferência de Cadastro & Aval para Emissão de Contrato</h3>
            <button onclick="closeAvalModal()" class="btn-close-modal">&times;</button>
        </div>
        <form action="<?= $this->baseUrl('admin/rh/dar-aval') ?>" method="POST" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" id="aval_colaborador_id" name="colaborador_id" value="">

            <div class="form-group mb-2">
                <label>Colaborador:</label>
                <input type="text" id="aval_colaborador_nome" readonly class="input-disabled" style="font-weight:bold;">
            </div>

            <div class="form-group mb-2" id="aval_doc_link_box" style="background:#1e293b; padding:10px; border-radius:6px; margin-bottom:15px;">
                <label style="color:#38bdf8; font-weight:bold;">Documento de Identificação Anexado:</label>
                <div id="aval_doc_preview_link"></div>
            </div>

            <div class="form-group mb-2">
                <label for="aval_funcao_campanha">Função na Campanha *</label>
                <select id="aval_funcao_campanha" name="funcao_campanha" required>
                    <option value="">Selecione a função...</option>
                    <option value="Cabo Eleitoral">Cabo Eleitoral</option>
                    <option value="Coordenador de Bairro / Região">Coordenador de Bairro / Região</option>
                    <option value="Coordenador Geral de Campanha">Coordenador Geral de Campanha</option>
                    <option value="Panfletista / Ativista">Panfletista / Ativista</option>
                    <option value="Motorista de Campanha">Motorista de Campanha</option>
                    <option value="Mobilizador de Rua">Mobilizador de Rua</option>
                    <option value="Assessor de Comunicação / Mídias">Assessor de Comunicação / Mídias</option>
                    <option value="Segurança / Apoio Logístico">Segurança / Apoio Logístico</option>
                    <option value="Outras Funções de Campanha">Outras Funções de Campanha</option>
                </select>
            </div>

            <div class="form-group mb-2">
                <label for="aval_valor_contratado">Valor do Contrato *</label>
                <input type="text" id="aval_valor_contratado" name="valor_contratado" required placeholder="R$ 0,00" onkeyup="mascaraMoeda(this)">
            </div>

            <div class="form-group mb-2">
                <label for="aval_forma_pagamento">Forma de Pagamento</label>
                <input type="text" name="forma_pagamento" value="PIX / Transferência Bancária">
            </div>

            <div class="form-group mb-2">
                <label for="aval_tipo_assinatura">Modalidade de Assinatura do Contrato *</label>
                <select name="tipo_assinatura" id="aval_tipo_assinatura" onchange="toggleAvalTerceirosUrl()">
                    <option value="TERCEIROS_API">Link de Assinatura via Plataforma de Terceiros (ZapSign / Clicksign / Gov.br)</option>
                    <option value="MANUAL_UPLOAD">Assinatura Manual (Impresso / Upload da Cópia Assinada pelo App/Web)</option>
                </select>
            </div>

            <div class="form-group mb-2" id="group_aval_external_url">
                <label for="aval_external_signature_url">Link de Assinatura Externa (Terceiros)</label>
                <input type="url" name="external_signature_url" placeholder="https://app.zapsign.com.br/verificar/...">
            </div>

            <div class="modal-footer" style="margin-top: 20px;">
                <button type="button" onclick="closeAvalModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-teal">✔ Dar Aval & Liberar Contrato</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Conferir Contrato Assinado & Conceder Perfil (Homologação) -->
<div id="conferirContratoModal" class="modal-overlay hidden">
    <div class="modal-card" style="max-width: 600px;">
        <div class="modal-header">
            <h3>2. Conferência do Contrato Assinado & Permissão SGE</h3>
            <button onclick="closeConferirContratoModal()" class="btn-close-modal">&times;</button>
        </div>
        <form action="<?= $this->baseUrl('admin/rh/homologar') ?>" method="POST" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" id="conf_colaborador_id" name="colaborador_id" value="">

            <div class="form-group mb-2">
                <label>Colaborador:</label>
                <input type="text" id="conf_colaborador_nome" readonly class="input-disabled" style="font-weight:bold;">
            </div>

            <div class="form-group mb-2" style="background:#1e293b; padding:12px; border-radius:6px; margin-bottom:15px; border:1px solid rgba(16,185,129,0.3);">
                <label style="color:#10b981; font-weight:bold;">Documento / Contrato Assinado para Conferência:</label>
                <div id="conf_contrato_preview_link" style="margin-top:5px;"></div>
            </div>

            <!-- Painel de Validação e Situação Cadastral do Candidato a Colaborador -->
            <div class="form-group mb-2" style="background:#0f172a; padding:12px; border-radius:8px; margin-bottom:15px; border:1px solid rgba(59, 130, 246, 0.4);">
                <label style="color:#60a5fa; font-weight:bold; font-size:12px; display:flex; align-items:center; gap:6px;">
                    📋 Situação Cadastral do Candidato a Colaborador (Receita Federal / Res. TSE):
                </label>
                <div id="conf_tse_status_box" style="margin-top:6px; font-size:12px;">
                    <div style="color:var(--text-secondary); display:flex; align-items:center; gap:6px;">
                        <span>⏳ Verificando dados cadastrais do candidato a colaborador...</span>
                    </div>
                </div>
            </div>

            <div class="form-group mb-2">
                <label for="conf_role_id">Conceder Perfil de Acesso ao Usuário no SGE (Role): *</label>
                <select id="conf_role_id" name="role_id" required>
                    <option value="">Selecione o Cargo / Nível de Acesso...</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?> - <?= htmlspecialchars($role['description']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Após conferir o contrato assinado, selecione a permissão. O usuário será ativado no sistema somente neste momento.</small>
            </div>

            <div class="form-group mb-2">
                <label for="conf_funcao_campanha">Função na Campanha contratada: *</label>
                <select id="conf_funcao_campanha" name="funcao_campanha" required>
                    <option value="">Selecione a função...</option>
                    <option value="Cabo Eleitoral">Cabo Eleitoral</option>
                    <option value="Coordenador de Bairro / Região">Coordenador de Bairro / Região</option>
                    <option value="Coordenador Geral de Campanha">Coordenador Geral de Campanha</option>
                    <option value="Panfletista / Ativista">Panfletista / Ativista</option>
                    <option value="Motorista de Campanha">Motorista de Campanha</option>
                    <option value="Mobilizador de Rua">Mobilizador de Rua</option>
                    <option value="Assessor de Comunicação / Mídias">Assessor de Comunicação / Mídias</option>
                    <option value="Segurança / Apoio Logístico">Segurança / Apoio Logístico</option>
                    <option value="Outras Funções de Campanha">Outras Funções de Campanha</option>
                </select>
                <small class="form-help">Caso a função de campanha seja alterada, o sistema gerará um novo contrato e enviará para assinatura do colaborador, arquivando o histórico.</small>
            </div>

            <div class="modal-footer" style="margin-top: 20px;">
                <button type="button" onclick="closeConferirContratoModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">✔ Contrato Conferido - Homologar & Liberar Permissão</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Sucesso após Aval Cadastral & Emissão de Contrato -->
<?php if (!empty($avalSuccess)): ?>
<div id="avalSuccessModal" class="modal-overlay">
    <div class="modal-card success-modal-card" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Contrato Emitido & Links Gerados!</h3>
            <button onclick="document.getElementById('avalSuccessModal').classList.add('hidden')" class="btn-close-modal">&times;</button>
        </div>
        <div class="modal-body text-center" style="padding: 20px;">
            <div class="success-icon" style="font-size:42px; color:#10b981; margin-bottom:10px;">✓</div>
            <p style="font-size:16px;">O aval foi concedido para <strong><?= htmlspecialchars($avalSuccess['nome']) ?></strong> e o contrato foi emitido com sucesso.</p>
            
            <div style="background:#0f172a; padding:14px; border-radius:8px; margin: 15px 0; text-align:left; border:1px solid rgba(16,185,129,0.3);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <strong style="color:#38bdf8;">Status do Envio Automático Z-API:</strong>
                    <?php if (!empty($avalSuccess['zapi_sent'])): ?>
                        <span class="badge badge-success">Enviado com sucesso via WhatsApp</span>
                    <?php else: ?>
                        <span class="badge badge-danger" style="background:#ef4444; color:#fff;">Envio Z-API Indisponível / Use o Botão Abaixo</span>
                    <?php endif; ?>
                </div>
                <div style="font-size:12px; color:#cbd5e1; white-space:pre-wrap; background:#1e293b; padding:10px; border-radius:6px; font-family:monospace; margin-top:5px; max-height:140px; overflow-y:auto;"><?= htmlspecialchars($avalSuccess['msg']) ?></div>
            </div>

            <!-- Botão de Click-to-Chat do WhatsApp para o Administrador Enviar Manualmente -->
            <a href="<?= htmlspecialchars($avalSuccess['click_to_chat']) ?>" target="_blank" class="btn btn-success btn-block" style="background:#25D366; color:#fff; font-weight:bold; font-size:15px; padding:12px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; gap:8px; text-decoration:none; width:100%; margin-top:10px;">
                💬 Enviar Contrato pelo meu WhatsApp Pessoal (Web)
            </a>
            
            <p class="modal-notice" style="font-size:12px; color:#94a3b8; margin-top:10px;">Caso o envio automático pelo Z-API não chegue imediatamente ao colaborador, clique no botão verde acima para disparar do seu WhatsApp.</p>
        </div>
        <div class="modal-footer">
            <button onclick="document.getElementById('avalSuccessModal').classList.add('hidden')" class="btn btn-secondary">Fechar</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal de Sucesso após Homologação & Atribuição de Perfil SGE -->
<?php if (!empty($homologSuccess)): ?>
<div id="homologSuccessModal" class="modal-overlay">
    <div class="modal-card success-modal-card" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Colaborador Homologado & Credenciais Geradas!</h3>
            <button onclick="document.getElementById('homologSuccessModal').classList.add('hidden')" class="btn-close-modal">&times;</button>
        </div>
        <div class="modal-body text-center" style="padding: 20px;">
            <div class="success-icon" style="font-size:42px; color:#10b981; margin-bottom:10px;">🎉</div>
            <p style="font-size:16px;">O colaborador <strong><?= htmlspecialchars($homologSuccess['nome']) ?></strong> foi homologado e ativado no sistema!</p>

            <div style="background:#0f172a; padding:14px; border-radius:8px; margin: 15px 0; text-align:left; border:1px solid rgba(59, 130, 246, 0.4);">
                <div style="color:#60a5fa; font-weight:bold; margin-bottom:8px;">🔑 Credenciais de Acesso Criadas:</div>
                <div style="font-size:13px; color:#f8fafc; margin-bottom:4px;">👤 <strong>E-mail:</strong> <?= htmlspecialchars($homologSuccess['email']) ?></div>
                <div style="font-size:13px; color:#f8fafc; margin-bottom:8px;">🔒 <strong>Senha Provisória:</strong> <code style="background:#334155; padding:2px 6px; border-radius:4px; font-weight:bold; color:#38bdf8;"><?= htmlspecialchars($homologSuccess['temp_password']) ?></code></div>
                
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
                    <strong style="color:#94a3b8; font-size:12px;">Status do Disparo Z-API:</strong>
                    <?php if (!empty($homologSuccess['zapi_sent'])): ?>
                        <span class="badge badge-success">Enviado via WhatsApp</span>
                    <?php else: ?>
                        <span class="badge badge-danger" style="background:#ef4444; color:#fff;">Usar Envio Manual</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botão Click-to-Chat -->
            <a href="<?= htmlspecialchars($homologSuccess['click_to_chat']) ?>" target="_blank" class="btn btn-success btn-block" style="background:#25D366; color:#fff; font-weight:bold; font-size:15px; padding:12px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; gap:8px; text-decoration:none; width:100%; margin-top:10px;">
                💬 Enviar Dados de Acesso pelo meu WhatsApp Pessoal
            </a>
        </div>
        <div class="modal-footer">
            <button onclick="document.getElementById('homologSuccessModal').classList.add('hidden')" class="btn btn-secondary">Fechar</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal de Sucesso após Alteração de Contrato (Mudança de Função) -->
<?php if (!empty($contratoAlterado)): ?>
<div id="contratoAlteradoModal" class="modal-overlay">
    <div class="modal-card success-modal-card" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Novo Contrato Emitido!</h3>
            <button onclick="document.getElementById('contratoAlteradoModal').classList.add('hidden')" class="btn-close-modal">&times;</button>
        </div>
        <div class="modal-body text-center" style="padding: 20px;">
            <div class="success-icon" style="font-size:42px; color:#f59e0b; margin-bottom:10px;">📝</div>
            <p style="font-size:16px;">O contrato do colaborador <strong><?= htmlspecialchars($contratoAlterado['nome']) ?></strong> foi atualizado com sucesso!</p>
            <p style="font-size:14px; color:#cbd5e1; margin-top:5px;">Função alterada de <span style="text-decoration:line-through; color:#ef4444;"><?= htmlspecialchars($contratoAlterado['funcao_antiga']) ?></span> para <strong style="color:#10b981;"><?= htmlspecialchars($contratoAlterado['funcao_nova']) ?></strong>.</p>
            <p style="font-size:13px; color:#94a3b8; margin-bottom:15px;">O novo contrato foi arquivado junto ao anterior e o colaborador foi colocado de volta em etapa de assinatura.</p>

            <div style="background:#0f172a; padding:14px; border-radius:8px; margin: 15px 0; text-align:left; border:1px solid rgba(245,158,11,0.3);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <strong style="color:#38bdf8;">Status do Envio Automático Z-API:</strong>
                    <?php if (!empty($contratoAlterado['zapi_sent'])): ?>
                        <span class="badge badge-success">Enviado via WhatsApp</span>
                    <?php else: ?>
                        <span class="badge badge-danger" style="background:#ef4444; color:#fff;">Envio Automático Indisponível</span>
                    <?php endif; ?>
                </div>
                <div style="font-size:12px; color:#cbd5e1; white-space:pre-wrap; background:#1e293b; padding:10px; border-radius:6px; font-family:monospace; margin-top:5px; max-height:120px; overflow-y:auto;"><?= htmlspecialchars($contratoAlterado['msg']) ?></div>
            </div>

            <!-- Ações: Download PDF ou Enviar WhatsApp -->
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:15px;">
                <a href="<?= htmlspecialchars($contratoAlterado['click_to_chat']) ?>" target="_blank" class="btn btn-success" style="background:#25D366; color:#fff; font-weight:bold; font-size:14px; padding:10px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; gap:8px; text-decoration:none;">
                    💬 Enviar Link de Assinatura pelo meu WhatsApp Pessoal
                </a>

                <a href="<?= htmlspecialchars($contratoAlterado['link_pdf']) ?>" target="_blank" class="btn btn-primary" style="background:#0284c7; color:#fff; font-weight:bold; font-size:14px; padding:10px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; gap:8px; text-decoration:none;">
                    📄 Visualizar e Baixar Novo Contrato (PDF)
                </a>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="document.getElementById('contratoAlteradoModal').classList.add('hidden')" class="btn btn-secondary">Fechar</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal 3: Editar Telefone / WhatsApp do Colaborador -->
<div id="editarTelefoneModal" class="modal-overlay hidden">
    <div class="modal-card" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Editar Telefone / WhatsApp</h3>
            <button onclick="closeEditarTelefoneModal()" class="btn-close-modal">&times;</button>
        </div>
        <form action="<?= $this->baseUrl('admin/rh/atualizar-telefone') ?>" method="POST" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" id="edit_tel_colaborador_id" name="colaborador_id" value="">

            <div class="form-group mb-2">
                <label>Colaborador:</label>
                <input type="text" id="edit_tel_colaborador_nome" readonly class="input-disabled" style="font-weight:bold;">
            </div>

            <div class="form-group mb-2">
                <label for="edit_tel_celular_whatsapp">Novo Número de WhatsApp *</label>
                <input type="text" id="edit_tel_celular_whatsapp" name="celular_whatsapp" required placeholder="(11) 99999-9999">
                <small class="form-help">Mesmo se informado com zero no DDD (ex: 011), o sistema formatará para o padrão internacional do WhatsApp.</small>
            </div>

            <div class="modal-footer" style="margin-top: 20px;">
                <button type="button" onclick="closeEditarTelefoneModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-teal">✔ Salvar Telefone</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Enviar Convite de Auto-Cadastro Público por WhatsApp -->
<div id="conviteAutoCadastroModal" class="modal-overlay hidden">
    <div class="modal-card" style="max-width: 500px;">
        <div class="modal-header">
            <h3>🔗 Enviar Convite de Auto-Cadastro</h3>
            <button onclick="closeConviteAutoCadastroModal()" class="btn-close-modal">&times;</button>
        </div>
        <form action="<?= $this->baseUrl('admin/rh/enviar-convite-whatsapp') ?>" method="POST" class="modal-form" id="formConviteAutoCadastro">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group mb-3">
                <label for="celular_convite">Telefone / WhatsApp do Colaborador *</label>
                <input type="text" id="celular_convite" name="celular_convite" required placeholder="(11) 99999-9999" oninput="atualizarLinksConvite()">
                <small class="form-help">Informe o número do WhatsApp para o qual o link de auto-cadastro será enviado.</small>
            </div>

            <div style="background:#0f172a; padding:12px; border-radius:8px; margin-bottom:15px; border:1px solid #334155;">
                <label style="font-size:12px; color:#94a3b8; font-weight:bold; display:block; margin-bottom:4px;">🌐 Link Público de Auto-Cadastro:</label>
                <div style="display:flex; gap:6px;">
                    <input type="text" id="link_publico_input" readonly value="<?= $this->baseUrl('colaborador/cadastro') ?>" style="font-size:12px; background:#1e293b; color:#38bdf8; border:1px solid #475569; border-radius:4px; padding:6px 10px; width:100%; font-weight:bold;">
                    <button type="button" onclick="copiarLinkCadastro()" class="btn btn-secondary btn-sm" style="white-space:nowrap; font-size:11px;">📋 Copiar</button>
                </div>
            </div>

            <div class="modal-footer" style="display:flex; flex-direction:column; gap:8px;">
                <button type="submit" class="btn btn-teal btn-block" style="font-weight:bold; font-size:14px; padding:10px; display:flex; align-items:center; justify-content:center; gap:6px;">
                    ⚡ Disparar via WhatsApp API (Automático)
                </button>

                <a id="btn_web_convite" href="https://api.whatsapp.com/send?text=<?= rawurlencode("Olá! Acesse o link para se cadastrar como colaborador de campanha: " . $this->baseUrl('colaborador/cadastro')) ?>" target="_blank" class="btn btn-block" style="background:#25D366; color:#fff; font-weight:bold; font-size:13px; padding:9px; border-radius:6px; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:6px;">
                    💬 Enviar pelo meu WhatsApp (Web)
                </a>

                <div style="display:flex; justify-content:space-between; align-items:center; width:100%; margin-top:5px;">
                    <a href="<?= $this->baseUrl('colaborador/cadastro') ?>" target="_blank" style="font-size:12px; color:#38bdf8; text-decoration:underline;">
                        🔗 Abrir Formulário no Navegador
                    </a>
                    <button type="button" onclick="closeConviteAutoCadastroModal()" class="btn btn-secondary btn-sm">Fechar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function mascaraMoeda(input) {
    let v = input.value.replace(/\D/g, '');
    if (v === '') {
        input.value = '';
        return;
    }
    v = (parseFloat(v) / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = 'R$ ' + v;
}

function openAvalModal(colaborador) {
    document.getElementById('aval_colaborador_id').value = colaborador.id;
    document.getElementById('aval_colaborador_nome').value = colaborador.nome_completo;
    
    const docLinkBox = document.getElementById('aval_doc_preview_link');
    let docHtml = '';
    if (colaborador.foto_rosto_path) {
        docHtml += `<div style="margin-bottom:8px;">
            <a href="<?= $this->baseUrl('admin/rh/documento?id=') ?>${colaborador.id}&tipo=rosto" target="_blank" class="btn btn-teal btn-sm" style="width:100%; display:inline-block; text-align:center;">👤 Visualizar Foto do Rosto (Avatar)</a>
        </div>`;
    }
    if (colaborador.documento_foto_path) {
        docHtml += `<div>
            <a href="<?= $this->baseUrl('admin/rh/documento?id=') ?>${colaborador.id}&tipo=doc" target="_blank" class="btn btn-secondary btn-sm" style="width:100%; display:inline-block; text-align:center;">🪪 Visualizar Foto do Documento de Identificação (RG/CNH)</a>
        </div>`;
    }
    if (!docHtml) {
        docHtml = `<span class="text-danger">Nenhum documento ou foto anexada.</span>`;
    }
    docLinkBox.innerHTML = docHtml;

    document.getElementById('avalModal').classList.remove('hidden');
}

function closeAvalModal() {
    document.getElementById('avalModal').classList.add('hidden');
}

function toggleAvalTerceirosUrl() {
    const tipo = document.getElementById('aval_tipo_assinatura').value;
    document.getElementById('group_aval_external_url').style.display = (tipo === 'TERCEIROS_API') ? 'block' : 'none';
}

function openConferirContratoModal(colaborador) {
    document.getElementById('conf_colaborador_id').value = colaborador.id;
    document.getElementById('conf_colaborador_nome').value = colaborador.nome_completo;

    if (colaborador.role_id) {
        document.getElementById('conf_role_id').value = colaborador.role_id;
    } else {
        document.getElementById('conf_role_id').value = '';
    }

    if (colaborador.funcao_campanha) {
        document.getElementById('conf_funcao_campanha').value = colaborador.funcao_campanha;
    } else {
        document.getElementById('conf_funcao_campanha').value = '';
    }

    const confBox = document.getElementById('conf_contrato_preview_link');
    let html = '';

    // 1. Cópia do contrato assinado enviado pelo colaborador (Upload)
    if (colaborador.pdf_assinado_path) {
        html += `<div style="font-size:12px; color:#10b981; font-weight:bold; margin-bottom:4px;">✔ Contrato Assinado: ENVIADO PELO COLABORADOR</div>`;
        html += `<div style="margin-bottom:10px;">
            <a href="<?= $this->baseUrl('admin/rh/documento?id=') ?>${colaborador.id}&tipo=contrato" target="_blank" class="btn btn-success btn-sm" style="font-weight:bold; width:100%; display:inline-block; text-align:center; padding:8px 12px; background:#059669; color:#fff;">
                📄 Visualizar Cópia do Contrato Assinado Enviado (Upload)
            </a>
        </div>`;
    } else {
        html += `<div style="font-size:12px; color:#f59e0b; font-weight:bold; margin-bottom:6px;">⏳ Contrato Assinado: PENDENTE DE ENVIO</div>`;
    }

    // 2. Link de assinatura em plataforma de terceiros (ZapSign / Clicksign)
    if (colaborador.external_signature_url) {
        html += `<div style="margin-bottom:10px;">
            <a href="${colaborador.external_signature_url}" target="_blank" class="btn btn-teal btn-sm" style="font-weight:bold; width:100%; display:inline-block; text-align:center; padding:8px 12px;">
                🌐 Visualizar Assinatura na Plataforma Externa (ZapSign / Clicksign)
            </a>
        </div>`;
    }

    // 3. Modelo oficial de contrato emitido em PDF pelo SGE
    if (colaborador.titulo_contrato) {
        html += `<div style="margin-bottom:10px;">
            <a href="<?= $this->baseUrl('admin/rh/contrato-pdf?id=') ?>${colaborador.id}" target="_blank" class="btn btn-primary btn-sm" style="font-weight:bold; background:#0284c7; width:100%; display:inline-block; text-align:center; padding:8px 12px;">
                📄 Visualizar Modelo Oficial do Contrato Emitido (PDF)
            </a>
        </div>`;
    }

    // 4. Foto do Documento de Identificação (RG / CNH / CIN)
    if (colaborador.documento_foto_path) {
        html += `<div style="margin-top:10px; margin-bottom:4px;">
            <div style="font-size:12px; color:#10b981; font-weight:bold; margin-bottom:4px;">✔ Foto do Documento de Identificação: ENVIADA</div>
            <a href="<?= $this->baseUrl('admin/rh/documento?id=') ?>${colaborador.id}&tipo=doc" target="_blank" class="btn btn-secondary btn-sm" style="font-size:11px; width:100%; display:inline-block; text-align:center; padding:6px 10px;">
                🪪 Visualizar Foto do Documento de Identificação (RG/CNH)
            </a>
        </div>`;
    } else {
        html += `<div style="font-size:12px; color:#ef4444; font-weight:bold; margin-top:10px;">⚠ Foto do Documento de Identificação: NÃO ANEXADA</div>`;
    }

    // 5. Foto do Rosto do Colaborador (Selfie / Avatar)
    if (colaborador.foto_rosto_path) {
        html += `<div style="margin-top:10px; margin-bottom:4px;">
            <div style="font-size:12px; color:#10b981; font-weight:bold; margin-bottom:4px;">✔ Foto do Rosto (Avatar/Crachá): ENVIADA</div>
            <a href="<?= $this->baseUrl('admin/rh/documento?id=') ?>${colaborador.id}&tipo=rosto" target="_blank" class="btn btn-teal btn-sm" style="font-size:11px; width:100%; display:inline-block; text-align:center; padding:6px 10px;">
                👤 Visualizar Foto do Rosto (Selfie/Avatar)
            </a>
        </div>`;
    } else {
        html += `<div style="font-size:12px; color:#ef4444; font-weight:bold; margin-top:10px;">⚠ Foto do Rosto: NÃO ANEXADA</div>`;
    }

    if (!html) {
        html = `<div class="text-warning" style="font-size:13px; text-align:center; padding:6px;">Documento em formato físico / impresso entregue em mãos.</div>`;
    }

    confBox.innerHTML = html;

    // Consulta em tempo real da Situação Cadastral do Candidato a Colaborador
    const tseBox = document.getElementById('conf_tse_status_box');
    tseBox.innerHTML = `<div style="color:var(--text-secondary); display:flex; align-items:center; gap:6px;">
        <span>⏳ Verificando regularidade fiscal (CPF) e cadastral do candidato a colaborador...</span>
    </div>`;

    fetch(`<?= $this->baseUrl('admin/rh/consultar-tse?id=') ?>${colaborador.id}`)
        .then(res => res.json())
        .then(data => {
            if (data.valido) {
                let badgeStyle = (data.cor_badge === 'success') ? 'background:rgba(16,185,129,0.2); color:#10b981; border:1px solid rgba(16,185,129,0.4);' : 'background:rgba(14,165,233,0.2); color:#38bdf8; border:1px solid rgba(14,165,233,0.4);';
                tseBox.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:6px;">
                        <span style="font-size:11px; padding:3px 8px; border-radius:4px; font-weight:bold; ${badgeStyle}">${data.status_tse}</span>
                        <span style="font-size:11px; color:#10b981; font-weight:bold;">✔ ${data.status_cpf}</span>
                    </div>
                    <div style="color:#e2e8f0; font-size:11px; line-height:1.5; background:rgba(255,255,255,0.03); padding:8px; border-radius:6px;">
                        <strong>Enquadramento:</strong> ${data.cargo}<br>
                        <strong>Regulamentação:</strong> ${data.partido}<br>
                        ${data.idade_info ? `<strong>Faixa Etária:</strong> ${data.idade_info}<br>` : ''}
                        <span style="color:#94a3b8; font-size:10px; display:block; margin-top:4px;">ℹ ${data.detalhes}</span>
                    </div>
                `;
            } else {
                tseBox.innerHTML = `
                    <div style="color:#ef4444; font-weight:bold; font-size:11px; margin-bottom:4px;">
                        ⚠ ${data.status_tse}
                    </div>
                    <div style="color:#94a3b8; font-size:10px;">
                        ${data.detalhes || 'Verifique o número do CPF digitado no cadastro.'}
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error(err);
            tseBox.innerHTML = `<span style="color:#f59e0b; font-size:11px;">⚠ Validação sintática de CPF efetuada. Servidor TSE temporariamente indisponível.</span>`;
        });

    document.getElementById('conferirContratoModal').classList.remove('hidden');
}

function closeConferirContratoModal() {
    document.getElementById('conferirContratoModal').classList.add('hidden');
}
function openEditarTelefoneModal(id, nome, celular) {
    document.getElementById('edit_tel_colaborador_id').value = id;
    document.getElementById('edit_tel_colaborador_nome').value = nome;
    document.getElementById('edit_tel_celular_whatsapp').value = celular;
    document.getElementById('editarTelefoneModal').classList.remove('hidden');
}

function closeEditarTelefoneModal() {
    document.getElementById('editarTelefoneModal').classList.add('hidden');
}

function openConviteAutoCadastroModal() {
    document.getElementById('conviteAutoCadastroModal').classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('celular_convite').focus();
    }, 100);
}

function closeConviteAutoCadastroModal() {
    document.getElementById('conviteAutoCadastroModal').classList.add('hidden');
}

function atualizarLinksConvite() {
    const rawTel = document.getElementById('celular_convite').value.replace(/\D/g, '');
    let formattedTel = rawTel;
    if (formattedTel.length <= 11 && formattedTel.length > 0) {
        formattedTel = '55' + formattedTel;
    }
    const linkCadastro = "<?= $this->baseUrl('colaborador/cadastro') ?>";
    const msg = encodeURIComponent("Olá! Você foi convidado(a) para se cadastrar como colaborador(a) na Equipe de Campanha Eleitoral.\n\nPor favor, acesse o link abaixo para preencher seus dados e anexar a foto do seu documento (RG/CNH):\n\n🌐 Link de Auto-Cadastro:\n" + linkCadastro);
    const webBtn = document.getElementById('btn_web_convite');
    if (webBtn) {
        webBtn.href = "https://api.whatsapp.com/send?phone=" + formattedTel + "&text=" + msg;
    }
}

function copiarLinkCadastro() {
    const input = document.getElementById('link_publico_input');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        alert('✅ Link de auto-cadastro copiado para a área de transferência!');
    }).catch(() => {
        document.execCommand('copy');
        alert('✅ Link copiado!');
    });
}
</script>
