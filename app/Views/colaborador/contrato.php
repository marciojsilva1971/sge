<div class="auth-card" style="max-width: 680px; margin: 30px auto;">
    <div class="auth-header">
        <div class="auth-logo">SGE</div>
        <h2>Acompanhamento de Cadastro & Contrato</h2>
        <p class="auth-subtitle">Olá, <strong><?= htmlspecialchars($colaborador['nome_completo']) ?></strong>!</p>
    </div>

    <!-- Indicador de Etapas -->
    <div class="contrato-status-box" style="margin-bottom: 25px;">
        <div style="background-color: rgba(15, 23, 42, 0.7); padding: 18px; border-radius: 10px; border: 1px solid rgba(13, 148, 136, 0.3);">
            <p style="margin-bottom: 8px; font-size:13px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Etapa Atual do seu Cadastro:</p>
            
            <?php if ($colaborador['status'] === 'AGUARDANDO_AVAL_CADASTRO'): ?>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge badge-warning" style="font-size:15px; padding:8px 14px;">🔍 Etapa 1: Aguardando Aval do Cadastro</span>
                </div>
                <p style="font-size:13px; color:#cbd5e1; margin-top:10px; line-height:1.4;">
                    Seus dados cadastrais e a foto do documento de identificação foram recebidos com sucesso e estão em análise pela equipe de RH da campanha. Assim que o administrador der o aval, o contrato de prestação de serviços será liberado nesta tela.
                </p>
                <div style="margin-top:15px; text-align:center;">
                    <button onclick="window.location.reload()" class="btn btn-secondary btn-sm" style="padding:8px 14px; font-weight:bold;">
                        🔄 Atualizar / Verificar Se o Contrato Foi Liberado
                    </button>
                </div>

            <?php elseif ($colaborador['status'] === 'AGUARDANDO_ASSINATURA_CONTRATO'): ?>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge badge-info" style="font-size:15px; padding:8px 14px;">📝 Etapa 2: Aguardando Assinatura do Contrato</span>
                </div>
                <p style="font-size:13px; color:#cbd5e1; margin-top:10px; line-height:1.4;">
                    Seu cadastro foi conferido e aprovado! O contrato de prestação de serviços está pronto para sua assinatura abaixo.
                </p>

            <?php elseif ($colaborador['status'] === 'AGUARDANDO_CONFERENCIA_CONTRATO'): ?>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge badge-warning" style="font-size:15px; padding:8px 14px; background:#f59e0b; color:#000;">📑 Etapa 3: Aguardando Conferência do Contrato pelo Admin</span>
                </div>
                <p style="font-size:13px; color:#cbd5e1; margin-top:10px; line-height:1.4;">
                    Recebemos seu contrato assinado! O administrador da campanha está realizando a conferência final da assinatura para conceder o perfil de acesso no sistema SGE.
                </p>

            <?php elseif ($colaborador['status'] === 'ATIVO'): ?>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge badge-success" style="font-size:15px; padding:8px 14px;">✔ Etapa 4: Cadastro & Contrato 100% Homologados</span>
                </div>
                <p style="font-size:13px; color:#cbd5e1; margin-top:10px; line-height:1.4;">
                    Parabéns! Seu contrato e seu cadastro foram 100% conferidos e homologados. Suas credenciais de acesso ao sistema SGE foram ativadas.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Se o Contrato Foi Liberado/Emitido -->
    <?php if ($contrato): ?>
        <div class="contrato-details" style="background-color: rgba(30, 41, 59, 0.7); padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(13, 148, 136, 0.4);">
            <h4 style="color: var(--teal-primary); margin-bottom: 10px;"><?= htmlspecialchars($contrato['titulo_contrato']) ?></h4>
            <p><strong>Função na Campanha:</strong> <?= htmlspecialchars($contrato['funcao_campanha']) ?></p>
            <p><strong>Valor do Contrato:</strong> R$ <?= number_format($contrato['valor_contratado'], 2, ',', '.') ?></p>
            <p><strong>Período da Prestação:</strong> <?= date('d/m/Y', strtotime($contrato['data_inicio'])) ?> até <?= date('d/m/Y', strtotime($contrato['data_fim'])) ?></p>
            <p><strong>Forma de Pagamento:</strong> <?= htmlspecialchars($contrato['forma_pagamento']) ?></p>
            
            <?php 
                $meuLinkContrato = $this->baseUrl('colaborador/contrato?token=' . $colaborador['token_cadastro']);
                $wspShareText = rawurlencode("Link do meu contrato de campanha no SGE: " . $meuLinkContrato);
            ?>
            <div style="margin-top:15px; padding-top:12px; border-top:1px dashed rgba(255,255,255,0.1); display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                <span style="font-size:12px; color:#94a3b8;">Link Deste Contrato:</span>
                <input type="text" readonly value="<?= $meuLinkContrato ?>" style="flex:1; min-width:200px; background:#0f172a; border:1px solid #334155; color:#38bdf8; padding:5px 8px; font-size:12px; border-radius:4px;">
                <a href="https://api.whatsapp.com/send?text=<?= $wspShareText ?>" target="_blank" class="btn btn-sm" style="background:#25D366; color:#fff; font-weight:bold; font-size:12px; text-decoration:none;">
                    📲 Guardar no Meu WhatsApp
                </a>
                <a href="<?= $this->baseUrl('colaborador/contrato-pdf?token=' . $colaborador['token_cadastro']) ?>" target="_blank" class="btn btn-sm" style="background:#0284c7; color:#fff; font-weight:bold; font-size:12px; text-decoration:none;">
                    📄 Baixar / Imprimir Contrato (PDF)
                </a>
            </div>
        </div>

        <?php if ($colaborador['status'] === 'AGUARDANDO_ASSINATURA_CONTRATO'): ?>

            <?php if ($contrato['tipo_assinatura'] === 'TERCEIROS_API' && !empty($contrato['external_signature_url'])): ?>
                <!-- Opção 1: Assinatura Eletrônica em Plataforma Terceira -->
                <div class="alert alert-info text-center" style="background-color: rgba(13, 148, 136, 0.15); border-color: var(--teal-primary);">
                    <h4>✍ Assinatura Digital Eletrônica</h4>
                    <p style="margin: 10px 0; font-size: 14px;">Clique no botão abaixo para efetuar a assinatura eletrônica do seu contrato na plataforma parceira:</p>
                    <a href="<?= htmlspecialchars($contrato['external_signature_url']) ?>" target="_blank" class="btn btn-teal btn-block" style="padding: 12px; font-size: 16px; font-weight:bold;">
                        🔗 Clique Aqui para Assinar no ZapSign / Plataforma
                    </a>
                </div>
            <?php endif; ?>

            <!-- Opção 2: Upload de Cópia Assinada (Impresso / Foto no App) -->
            <div class="upload-manual-box" style="background-color: rgba(15, 23, 42, 0.8); padding: 20px; border-radius: 8px; border: 1px dashed var(--teal-primary); margin-top: 15px;">
                <h4>📄 Envio do Contrato Assinado (Foto ou PDF pelo App/Navegador)</h4>
                <p style="font-size: 13px; color: #94a3b8; margin: 8px 0 15px 0;">
                    Se optou pela assinatura física/impressa, tire uma foto nítida ou digitalize o contrato assinado e envie o arquivo abaixo (PDF, PNG ou JPG):
                </p>

                <form action="<?= $this->baseUrl('colaborador/contrato/upload') ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($colaborador['token_cadastro']) ?>">

                    <div class="form-group mb-3">
                        <input type="file" name="contrato_assinado" required accept=".pdf,.jpg,.jpeg,.png" style="background: #1e293b; padding: 10px; border-radius: 6px; width: 100%; border: 1px solid #334155;">
                    </div>

                    <button type="submit" class="btn btn-teal btn-block" style="padding: 10px; font-size: 15px;">
                        📤 Encaminhar Contrato Assinado
                    </button>
                </form>
            </div>

        <?php elseif ($contrato['status_contrato'] === 'ASSINADO' || !empty($contrato['pdf_assinado_path']) || $colaborador['status'] === 'AGUARDANDO_CONFERENCIA_CONTRATO'): ?>
            <div class="alert alert-success text-center" style="background-color: rgba(16, 185, 129, 0.15); border: 2px solid #10b981; border-radius: 8px; padding: 22px; margin-top: 20px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);">
                <h3 style="color: #10b981; margin-bottom: 8px; font-size: 18px; font-weight: bold;">🎉 Contrato Assinado Enviado com Sucesso!</h3>
                <p style="font-size: 14px; color: #e2e8f0; line-height: 1.5; margin-bottom: 12px;">
                    Sua cópia assinada do contrato foi recebida e encaminhada para conferência da equipe de RH e coordenação de campanha.
                </p>
                <div style="font-size: 13px; color: #94a3b8; margin-bottom: 15px;">
                    Status Atual: <strong>Aguardando Homologação e Concessão de Acesso pelo Administrador.</strong>
                </div>
                <?php if (!empty($contrato['pdf_assinado_path'])): ?>
                    <a href="<?= $this->baseUrl('colaborador/documento?token=' . $colaborador['token_cadastro'] . '&tipo=contrato') ?>" target="_blank" class="btn btn-teal" style="display: inline-flex; align-items: center; gap: 6px; font-weight: bold; padding: 10px 18px; font-size: 14px; text-decoration: none;">
                        📄 Visualizar Cópia Assinada Enviada
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
