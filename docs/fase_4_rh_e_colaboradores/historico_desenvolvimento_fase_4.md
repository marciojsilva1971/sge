# Histórico da Linha de Desenvolvimento - Fase 4: Módulo de RH e Colaboradores

Este documento mantém o registro permanente de todas as conversas, decisões técnicas, alinhamentos e alterações implementadas na **Fase 4** do Sistema de Gestão Eleitoral (SGE), conforme estabelecido no `AGENTS.md`.

---

## 📅 Sessão 1: Modelagem e Arquitetura Inicial
* **Alinhamento:** Definição do escopo do módulo de RH para equipe de campanha.

---

## 📅 Sessão 2: Core Backend, Controllers e Views Administrativas
* **Implementações:** Model, Controller e Views para RH.

---

## 📅 Sessão 3: Documento de Identificação e Thumbnail Dynamic Preview
* **Implementações:** Adicionado campo de upload de foto de identificação e visualizador de thumbnail.

---

## 📅 Sessão 4: Atualização de Diretrizes (`AGENTS.md`)
* **Ação Realizada:** Registro contínuo de histórico obrigatório.

---

## 📅 Sessão 5: Fluxo de Aval, Emissão, Assinatura e Conferência de Contrato
* **Implementações:** Fluxo em 4 etapas estritas com liberação de conta de usuário SGE no estágio final.

---

## 📅 Sessão 6: Elaboração do Teste de Mesa (Desk Check)
* **Ação Realizada:** Matriz de cenários registrada em `docs/fase_4_rh_e_colaboradores/teste_de_mesa_fluxo_rh.md`.

---

## 📅 Sessão 7: Lista Suspensa de Funções, Máscara de Moeda e Ajustes de UI
* **Ação Realizada:** Atualização de views, botões de ação e lógica em `RhController.php`.

---

## 📅 Sessão 8: Correção de Compatibilidade de Herança em Controller (`parseBrlCurrency`)
* **Ação Realizada:** Alterado o modificador de acesso de `private` para `protected` em `RhController.php`.

---

## 📅 Sessão 9: Disparo Direto de Mensagens via API da Z-API (WhatsApp)
* **Ação Realizada:** Envio automático no aval e botão de disparo manual direto via Z-API.

---

## 📅 Sessão 10: Rota Segura de Visualização de Documentos (`GET /admin/rh/documento`)
* **Ação Realizada:** Criado o endpoint de streaming seguro para fotos de identificação e contratos assinados.

---

## 📅 Sessão 11: Resolução de Carregamento de CSS/Estilos em Dispositivos Móveis e Acesso via IP (`192.168.x.x`)
* **Ação Realizada:** Atualizada a resolução de URLs com o método dinâmico `Controller::baseUrl()`.

---

## 📅 Sessão 12: Visualização de Contrato em PDF na Gestão de Colaboradores
* **Ação Realizada:** Implementada a view oficial de contrato em PDF (`contrato_pdf.php`) e rotas dedicadas.

---

## 📅 Sessão 13: Ajuste de Resolução de Domínio Web (`APP_URL`) e Links Duplos no WhatsApp (PDF + Envio)
* **Ação Realizada:** Priorização do `APP_URL` de produção e mensagens no WhatsApp com download em PDF e portal.

---

## 📅 Sessão 14: Correção de Redirecionamento Indevido para Login no Portal Público do Colaborador
* **Ação Realizada:** Implementada a rota pública tokenizada `GET /colaborador/documento` e validação CSRF amigável.

---

## 📅 Sessão 15: Exibição de Alertas e Confirmação de Sucesso no Envio do Contrato Assinado
* **Ação Realizada:** Adicionado bloco de alertas flash em `auth.php` e banner visual de sucesso na tela de contrato do colaborador.

---

## 📅 Sessão 16: Conferência do Contrato Assinado, Homologação e Geração/Envio de Senha Provisória via WhatsApp
* **Ação Realizada:** Implementada a geração de senha provisória e notificação de credenciais via Z-API.

---

## 📅 Sessão 17: Ajuste na Tabela da Gestão de RH (`/admin/rh`) para Acesso Direto à Homologação
* **Ação Realizada:** Atualizada a coluna Ações Obrigatórias em `app/Views/admin/rh/index.php`.

---

## 📅 Sessão 18: Visualização de Todos os Documentos e Contratos no Modal de Homologação
* **Ação Realizada:** Incluídos todos os botões de checagem de documentos no modal.

---

## 📅 Sessão 19: Correção do Erro 404 após Autenticação de Colaboradores de Campo
* **Ação Realizada:** Mapeadas todas as rotas do `PortalController` em `public/index.php`.

---

## 📅 Sessão 20: Reordenação do Menu de Navegação do Portal para o Topo da Tela
* **Ação Realizada:** Atualizado o layout `app/Views/layouts/portal.php` movendo a barra de navegação para o topo.

---

## 📅 Sessão 21: Correção do Erro 404 no Envio de Cupons Fiscais (`/portal/viagem/receipt`)
* **Ação Realizada:** Adicionadas as rotas `receipt` e `submit` no `public/index.php`.

---

## 📅 Sessão 22: Consulta Cadastral do Candidato a Colaborador de Campanha
* **Ação Realizada:** Criado o serviço `App\Services\TseService.php` para checagem de CPF, maioridade legal e Resolução TSE 23.607/2019.

---

## 📅 Sessão 23: Cadastro de Teste de Mesa - Colaborador 1 (CPF 141.019.908-83)
* **Ação Realizada:** Inserido no banco o registro `Colaborador 1` com contrato assinado no status `AGUARDANDO_CONFERENCIA_CONTRATO`.

---

## 📅 Sessão 24: Preparação dos Artefatos de Deploy e Homologação
* **Ação Realizada:** Criado [guia_deploy_fase_4.md](file:///c:/xampp/htdocs/sge/docs/fase_4_rh_e_colaboradores/guia_deploy_fase_4.md) e [walkthrough.md](file:///C:/Users/marci/.gemini/antigravity/brain/01f1fa21-dd59-4151-9384-c3be62a3c6e3/walkthrough.md).

---

## 📅 Sessão 25: Publicação Oficial no GitHub (`git push`) e Disparo de Deploy CI/CD
* **Ação Realizada:** Código publicado com sucesso para `https://github.com/marciojsilva1971/sge.git` sob a tag de commit `primeira versao rh`.

---

## 📅 Sessão 26: Redefinição de Senha e Ajuste de Tolerância de Sessão VPS
* **Ações Implementadas:** Ajustados `User.php` (LEFT JOIN) e `Session.php` (Proxy Headers), com ferramenta `scratch/reset_admin_pass.php` para VPS.

---

## 📅 Sessão 27: Badges Visuais e Destaque de Arquivos Enviados no Painel de RH
* **Ações Implementadas:** Atualizada a tabela administrativa e o modal de conferência com os badges verdes `✔ RG/CNH Anexado` e `✅ Contrato Assinado Enviado`.

---

## 📅 Sessão 28: Exibição Redundante do Menu de RH e Atualização de Sessão em Produção
* **Ação Realizada:** Adicionadas travas redundantes em `app/Views/layouts/main.php` garantindo a visibilidade do menu de RH.

---

## 📅 Sessão 29: Unificação do Módulo Financeiro e Sub-Navegação por Abas
* **Ação Realizada:** Criada a barra de abas horizontais unificada (`_nav_tabs.php`) presente em todas as subpáginas financeiras.

---

## 📅 Sessão 30: Simplificação do Menu Lateral (Remoção dos Links Repetidos de Fila e Tipos de Despesas)
* **Ação Realizada:** Removidos os links avulsos da barra lateral, mantendo a navegação limpa através das abas no topo da página.

---

## 📅 Sessão 31: Blindagem da Automação de Deploy (`.github/workflows/deploy.yml`)
* **Ação Realizada:** Refatorado o script SSH do GitHub Actions para executar `git fetch origin main && git reset --hard origin/main`.

---

## 📅 Sessão 32: Recompilação Executiva do PDF via ReportLab (Novo Arquivo Sem Trava de Leitura)
* **Ação Realizada:** Gerado `Manual_Cadastro_Colaborador_SGE_FINAL.pdf` com ReportLab Engine 5.0 sem cortes laterais.

---

## 📅 Sessão 33: Correção e Aprimoramento da Alteração e Redefinição de Senha de Usuários
* **Ação Realizada:** Atualizada a validação de caracteres especiais para aceitar underline (`_`) e adicionado modal administrativo em `/admin/users`.

---

## 📅 Sessão 34: Exibição Garantida de Mensagens de Confirmação no Botão "Salvar Alterações"
* **Ação Realizada:** Adicionados blocos de alerta proeminentes com ícones de status (`✅ Perfil atualizado com sucesso!` e `⚠️ Falha/Erro`) no topo de `admin/profile.php`, `admin/users.php` e `admin/rbac.php`.

---

## 📅 Sessão 35: Geração Completa de Mensagens de WhatsApp na Primeira Conferência de Documentos do Colaborador
* **Ação Realizada:** Ajustada a inclusão de links (PDF, Portal e Assinatura Externa) e criada a modal visual `#avalSuccessModal` com o botão **`💬 Enviar Contrato pelo meu WhatsApp Pessoal`**.

---

## 📅 Sessão 36: Sanitização de DDD com Zero Inicial (ex: `011`) e Recursos de Edição de Telefone
* **Ação Realizada:** Atualizado `WhatsAppService::formatPhone` para remover `0` do DDD e adicionado modal de edição rápida de telefone em `/admin/rh`.

---

## 📅 Sessão 37: Correção de Método de Busca no Modelo de Contratos (`getContratoPorColaborador`)
* **Ação Realizada:** Adicionada a declaração do método `getContratoPorColaborador()` em `Contrato.php`.

---

## 📅 Sessão 38: Correção da Sincronização de Telefone com a Tabela de Usuários (`celular`)
* **Ação Realizada:** Corrigido o nome da coluna para `celular` na tabela `usuarios`.

---

## 📅 Sessão 39: Botão e Recurso de Exclusão de Colaboradores Pendentes de Homologação
* **Ação Realizada:** Adicionado botão `🗑️ Excluir Colaborador` e fluxo backend de remoção de contrato/arquivos e colaborador com auditoria.

---

## 📅 Sessão 40: Correção de Importação da Classe `AuditLogger` em `RhController.php`
* **Ação Realizada:** Adicionada a instrução `use App\Services\AuditLogger;` no `RhController.php`.

---

## 📅 Sessão 41: Janela Interativa para Envio do Link de Auto-Cadastro via WhatsApp
* **Ação Realizada:** Criado modal interativo de digitação do WhatsApp para envio do link de auto-cadastro via Z-API ou Web.

---

## 📅 Sessão 42: Auto-Preenchimento Inteligente de Endereço via CEP (ViaCEP / Correios)
* **Ação Realizada:** Implementada a busca automática de CEP (ViaCEP/Correios) com preenchimento em tempo real de Logradouro, Bairro, Cidade e UF.

---

## 📅 Sessão 43: Padronização Global do Fuso Horário para América/São Paulo (UTC-3)
* **Ação Realizada:** Configurado `date_default_timezone_set('America/Sao_Paulo')` no PHP e `time_zone = '-03:00'` no PDO MySQL.

---

## 📅 Sessão 44: Correção do Botão `🔑 Senha` na Gestão de Usuários (`/admin/users`)
* **Ação Realizada:** Corrigido o posicionamento do `#resetPasswordModal` para fora do bloco condicional do convite e exposta a função global `window.closeResetPwdModal`.

---

## 📅 Sessão 45: Automação por OCR e Consulta Cadastral de CNPJ em Comprovantes de Compra
* **Ações Implementadas:**
  1. **Serviço de CNPJ (`App\Services\CnpjService.php`):** Consulta em tempo real bases públicas gratuitas da Receita Federal (**BrasilAPI** e **ReceitaWS**).
  2. **Endpoint API (`GET/POST /api/cnpj/consultar`):** Disponibilizada rota para autocompletar dados em tempo real no frontend.
  3. **Leitura Óptica da Imagem (Tesseract.js OCR):** Integrada a biblioteca Tesseract.js para identificação de CNPJ.

---

## 📅 Sessão 46: Reorganização do Layout de Despesas (Digitalização como 1º Passo e Nome do Fornecedor)
* **Ações Implementadas:** Reordenado formulário com upload de comprovante no topo (1º passo) e adicionado campo Nome / Razão Social do Fornecedor.

---

## 📅 Sessão 47: Indicador de Progresso OCR (0-100%) e Alerta Claro de Preenchimento Manual
* **Ações Implementadas:** Adicionada barra de progresso de leitura e instrução visual para preenchimento manual em imagens desfocadas/PDFs.

---

## 📅 Sessão 48: Notificação Síncrona Instantânea (0ms) no Upload de Arquivos
* **Ações Implementadas:** Garantido aparecimento imediato do badge `#ocr_status_badge` assim que o arquivo é selecionado.

---

## 📅 Sessão 49: Botão Dedicado "🔍 Digitalizar e Ler Comprovante (OCR)" e Script Tesseract Garantido
* **Ações Implementadas:** Adicionado o botão de escaneamento manual em todos os formulários e garantia da tag do Tesseract.js via CDN.

---

## 📅 Sessão 50: Motor de OCR de Alta Precisão (Validador Módulo 11 de CNPJ + Filtro Canvas Grayscale/Contraste)
* **Ações Implementadas:** Validador Módulo 11 de CNPJ, substituição de ruídos de leitura de cupons térmicos (O->0, I->1, S->5) e filtro HTML5 Canvas.

---

## 📅 Sessão 51: Revelação Progressiva do Formulário (Progressive Form Disclosure)
* **Ações Implementadas:** Ocultação inicial dos campos de formulário e revelação automática pós-leitura/upload.

---

## 📅 Sessão 52: Solicitação Explícita de Fotos Discriminadas e Suporte a Múltiplos Arquivos
* **Ações Implementadas:** Atualização dos formulários para suporte a upload múltiplo e alertas de orientação.

---

## 📅 Sessão 53: Fluxo de Upload em 2 Etapas (1º Foto CNPJ Nítida com OCR + Fotos Adicionais sem OCR)
* **Ações Implementadas:**
  1. **1º Passo (Leitura de CNPJ via OCR):** Orientação clara para o usuário tirar 1 foto focada exclusivamente no cabeçalho/CNPJ nítido do comprovante fiscal (`comprovante`). O OCR é executado apenas nesta imagem, garantindo altíssima taxa de acerto.
  2. **2º Passo (Fotos Adicionais dos Itens Discriminados - Sem OCR):** Dentro do formulário revelado, criado um campo específico para inclusão de 1 ou mais fotos extras (`fotos_adicionais[]`). **Essas imagens adicionais ignoram o OCR**, evitando lentidão no navegador e garantindo que todas as evidências fiscais detalhadas sejam anexadas.
  3. **Backend Consolidado:** Controllers refatorados para salvar e criptografar (AES-256) tanto a foto principal quanto a lista de fotos adicionais para o mesmo lançamento fiscal.

---

## 📅 Sessão 54: Refinamento UX do Fluxo em 2 Etapas com Transição de Blocos e Modal Pós-Envio
* **Ações Implementadas:**
  1. **Ajuste nos Textos Orientativos:**
     - **Etapa 1:** *"Fotografe ou envie um arquivo em detalhe do CNPJ da empresa impresso no cupom. Caso seja reconhecido, preencheremos o CNPJ e o nome da empresa automaticamente, mas você poderá alterar se necessário."*
     - **Etapa 2:** *"Envie ou fotografe o cupom fiscal de forma que seja possivel a visualização de todas as despesas e o total. Você pode enviar mais de um arquivo ou foto"*.
  2. **Escondimento/Transição de Blocos:** O bloco de captura do CNPJ (`#bloco-captura-cnpj`) é ocultado após a leitura ou ao clicar em "Pular OCR", revelando a Etapa 2 com os campos editáveis.
  3. **Modal de Confirmação Pós-Envio:** Modal interativo oferecendo opções entre enviar um novo comprovante ou finalizar a submissão.

---

## 📅 Sessão 55: Galeria de Miniaturas Visuais (Thumbnails) e Acumulador de Múltiplos Uploads (DataTransfer)
* **Ações Implementadas:**
  1. **Galeria de Miniaturas Visuais (`#galeria-miniaturas-container`):** Exibição em tempo real de cards com pré-visualização de imagem (ou ícone PDF), nome do arquivo, tamanho em KB e botão vermelho de exclusão individual (`✖`).
  2. **Acumulador Inteligente de Arquivos (`DataTransfer API`):** Resolvido o problema nativo dos navegadores onde abrir a caixa de diálogo "Escolher arquivos" uma segunda vez substituía a seleção anterior. Agora, novos arquivos são acumulados dinamicamente na lista sem perder os anteriores.
  3. **Resiliência Backend em Controllers:** Refatorados `PortalController.php` e `FinanceController.php` para agrupar e salvar dinamicamente todos os comprovantes enviados (sob qualquer nome de campo), mantendo a criptografia AES-256 e auditoria.

---

## 📅 Sessão 56: Formatação de Moeda BRL e Sanitização Segura para Armazenamento no Banco
* **Ações Implementadas:**
  1. **Máscara Moeda no Frontend (`oninput="formatarMoeda(this)"`):** Padronizada em todos os formulários de despesas (`portal/despesas.php`, `portal/viagem.php`, `admin/financeiro/despesas.php`), aplicando formatação automática em tempo real em formato BRL (`R$ 1.234,56`).
  2. **Sanitização Backend (`parseBrlCurrency` com `round(..., 2)`):** Atualizado o método em `Controller.php` e `RhController.php` para sanitizar qualquer string monetária (removendo símbolos, pontos de milhar e convertendo vírgulas em ponto decimal), aplicando o arredondamento preciso em 2 casas decimais.
  3. **Integridade de Dados:** Garantido que os dados sejam gravados no MySQL como tipos numéricos exatos (`DECIMAL(12,2)`), assegurando precisão em cálculos financeiros futuros (somas, relatórios e prestação de contas do TSE/SPCE).

---

## 📅 Sessão 57: Modal Educativo de Autorização de GPS e Opção de Envio sem Geolocalização
* **Ações Implementadas:**
  1. **Modal de Instruções Multi-Plataforma (`#gpsModal`):** Criada interface modal interativa em `portal/militancia.php` dividida por abas navegáveis para **Apple (iOS / Safari / Chrome)**, **Android (Chrome / Samsung)** e **PC / Computador (Windows / Mac / Chrome / Edge)**.
  2. **Abertura Automática & Reativa:** O modal é disparado automaticamente quando o navegador bloqueia/falha na obtenção do GPS, e pode ser reaberto via link ou botão *"❓ Como ativar o GPS?"*.
  3. **Checkbox de Envio Sem GPS (`permitir_sem_gps`):** Adicionada a opção manual no formulário e dentro do modal (`☑️ Ativar Envio Sem GPS`). Quando ativada, a validação libera o botão de submissão mesmo sem coordenadas, registrando a atividade no banco (`PortalController.php`) como pendente de análise manual.

---

## 📅 Sessão 58: Múltiplos Uploads e Acumulador de Fotos em Comprovação de Militância
* **Ações Implementadas:**
  1. **Acumulador de Fotos (`DataTransfer API`):** Implementado no formulário de militância (`portal/militancia.php`) o mesmo padrão de seleção cumulativa utilizado nos módulos financeiro e de viagens. O militante pode selecionar ou fotografar múltiplos comprovantes sucessivos sem perder os anteriores.
  2. **Galeria de Miniaturas Visuais (`#galeria-miniaturas-container`):** Exibição em tempo real de cards com imagem do comprovante, nome do arquivo e botão de exclusão individual (`✖`).
  3. **Persistência Multi-Anexo Backend (`militancy_photos`):** Refatorado `PortalController::addMilitancy()` para auto-criar a tabela de suporte `militancy_photos` e processar e criptografar individualmente com AES-256 todas as fotos enviadas pelo colaborador.





