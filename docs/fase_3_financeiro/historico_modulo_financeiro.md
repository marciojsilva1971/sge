# Histórico da Linha de Desenvolvimento - Fase 3: Módulo Financeiro, Contratos & Conciliação Bancária

Este documento mantém o registro permanente de todas as implementações do **Módulo Financeiro**, abrangendo Fila de Aprovação de Gastos, Cadastro de Fornecedores, Contratos de Empresas e Carga/Conciliação de Saldos Bancários.

---

## 📅 Sessão 1: Estruturação das Contas Bancárias e Tipos de Despesas
* **Ações:**
  - Criação das tabelas `bank_accounts`, `expense_types`, `suppliers` e `despesas`.
  - Mapeamento das contas obrigatórias da campanha: Fundo Especial de Financiamento de Campanha (FEFC), Fundo Partidário e Outros Recursos (Doações Privadas).

---

## 📅 Sessão 2: Fila de Aprovações e Gestão de Despesas
* **Ações:**
  - Implementação da tela `/admin/financeiro/fila` para aprovação de despesas lançadas pelos colaboradores ou gestores.
  - Adicionados botões de ação: Aprovar, Reprovar (com justificativa obrigatória) e Solicitar Correção.

---

## 📅 Sessão 3: Módulo de Contratos por Tempo Determinado (Fornecedores & Pessoas Jurídicas)
* **Ações:**
  - **Schema:** Criada a tabela `supplier_contracts` para gestão de contratos celebrados pela campanha com fornecedores.
  - **Fluxo de Cadastro e Anexo PDF:** Implementado upload obrigatório de contratos em PDF (MIME `application/pdf`, até 10MB) salvos em `storage/uploads/contratos/`.
  - **Edição e Substituição de PDF:** Implementado modal de edição `/admin/financeiro/contratos/editar` permitindo atualização de vigência, status, valores e troca do PDF assinado.
  - **Download Seguro:** Criado o endpoint `/admin/financeiro/contratos/download` com streaming direto do PDF.

---

---

## 📅 Sessão 4: Carga Inicial, Conciliação e Ajustes de Saldo Bancário com Extrato PDF
* **Ações:**
  - **Ajuste de UI:** Ajustados os cards KPI da interface financeira e SPCE impedindo quebras irregulares de linhas.
  - **Schema:** Criada a tabela `bank_balance_adjustments` (`update_bank_adjustments_schema.sql`).
  - **Carga & Conciliação:** Tela `/admin/financeiro/conciliacao` para lançamento de Carga Inicial, Ajustes de Saldo a Crédito/Débito e Conciliação com Extratos.
  - **Exigência Impositiva de Extrato PDF:** Todo ajuste de saldo exige obrigatoriamente o envio da cópia do Extrato Bancário em PDF (`storage/uploads/extratos/`).
  - **Download Seguro de Extratos:** Endpoint `/admin/financeiro/conciliacao/download` para auditoria dos extratos arquivados.

---

## 📅 Sessão 5: Correção da Rota de Cadastro de Receitas e Doações (Erro 404)
* **Ações:**
  - **Mapeamento de Rota HTTP:** Adicionado o mapeamento do método `POST` para a URL `/admin/financeiro` no arquivo de rotas (`public/index.php`), direcionando o envio do formulário "Lançar Nova Receita / Doação" para o método `FinanceController::index()`.
  - **Resolução do Erro 404:** O formulário de receita submete via requisição `POST` para a URL `/admin/financeiro`. Sem o registro explícito dessa rota no roteador, ocorria o retorno de 404 Not Found.

---

## 📅 Sessão 6: Dinamização Total dos KPIs Financeiros do Dashboard Principal
* **Ações:**
  - **Substituição de Valores Estáticos por Consultas SQL:** Refatorado o método `AdminController::dashboard()` para buscar em tempo real os saldos totais das contas bancárias ativas (`bank_accounts`), detalhados por FEFC, Fundo Partidário e Outros Recursos (Doações Privadas).
  - **Cômputo Real de Gastos Aprovados:** O indicador de "Limite de Gastos Consumidos" agora é calculated dinamicamente somando todas as despesas aprovadas/pagas (`despesas`) e comprovantes de viagens aprovados (`travel_receipts`).
  - **Ajuste de Chaves na View:** Corrigida a chave na view `app/Views/admin/dashboard.php` garantindo paridade imediata com as atualizações realizadas no banco de dados.

---

## 📅 Sessão 7: Parametrização de Cargo Eleitoral, UF e Limite de Gastos TSE Customizável
* **Ações:**
  - **Schema:** Criada a tabela `campaign_settings` (`update_campaign_settings_schema.sql`) para armazenar o nome do candidato/campanha, cargo eleitoral pretendido (ex: Deputado Federal, Estadual, Senador, Governador, Presidente), UF e o Limite Máximo de Gastos TSE em R$.
  - **Rota & Controlador:** Adicionada a rota `POST /admin/campanha/configuracoes` -> `AdminController::updateCampaignSettings()`, que valida os dados, persiste as alterações e grava registros na trilha de auditoria.
  - **Interface & Modal no Dashboard:** Adicionado o botão *"⚙️ Ajustar"* e um Modal completo no Dashboard (`dashboard.php`) permitindo a troca rápida de cargo, UF e a digitação customizada do teto legal de gastos com sugestões automáticas por cargo.
  - **Dinamização do Painel Financeiro:** O painel financeiro principal (`admin/financeiro/index.php`) passou a exibir o cargo e a UF do candidato na régua de consumo do limite de gastos.

---

## 📅 Sessão 8: Módulo de Cadastro, Edição e Vinculação de Contas Bancárias (SPCE/TSE)
* **Ações:**
  - **Schema:** Adicionada a coluna `pix_key` na tabela `bank_accounts` (`update_bank_accounts_pix_schema.sql`).
  - **Novas Rotas HTTP:** Mapeadas as rotas `/admin/financeiro/contas`, `/admin/financeiro/contas/update` e `/admin/financeiro/contas/toggle-status`.
  - **Controlador Financeiro:** Implementados os métodos `bankAccounts()`, `storeBankAccount()`, `updateBankAccount()` e `toggleBankAccountStatus()` com auditoria e controle de permissões em `FinanceController.php`.
  - **Interface Completa:** Criada a view `app/Views/admin/financeiro/contas.php` com KPIs por tipo de recurso (FEFC, Fundo Partidário, Outros Recursos), tabela interativa de contas ativas/encerradas, modais de cadastro e edição e diálogo `confirm()` para encerramento ou reativação de contas.
  - **Subnavegação:** Adicionada a aba *"💳 Contas Bancárias & Recursos"* na barra de navegação financeira (`_nav_tabs.php`).








