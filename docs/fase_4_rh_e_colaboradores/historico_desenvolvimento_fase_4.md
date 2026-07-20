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
  1. **Serviço de CNPJ (`App\Services\CnpjService.php`):** Criado serviço que consulta em tempo real bases públicas gratuitas da Receita Federal (**BrasilAPI** com fallback para **ReceitaWS**), retornando a Razão Social, Nome Fantasia e Endereço Completo do fornecedor.
  2. **Endpoint API (`GET/POST /api/cnpj/consultar`):** Disponibilizada rota para autocompletar dados em tempo real no frontend.
  3. **Leitura Óptica da Imagem (Tesseract.js OCR):** Integrada a biblioteca Tesseract.js nas telas de envio de comprovante (`portal/viagem.php` e `admin/financeiro/despesas.php`). Ao selecionar a foto do cupom/nota fiscal, o sistema faz o escaneamento inteligente da imagem, identifica a sequência de CNPJ e busca a Razão Social automaticamente sem necessidade de digitação pelo usuário.
  4. **Autocompletar de Fornecedores (`admin/financeiro/fornecedores.php`):** Ao digitar o CNPJ de um novo fornecedor, a Razão Social, Nome Fantasia e Endereço são preenchidos automaticamente na tela.
