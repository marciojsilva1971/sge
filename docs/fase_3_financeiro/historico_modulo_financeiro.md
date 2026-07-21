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

## 📅 Sessão 4: Carga Inicial, Conciliação e Ajustes de Saldo Bancário com Extrato PDF
* **Ações:**
  - **Ajuste de UI:** Ajustados os cards KPI da interface financeira e SPCE impedindo quebras irregulares de linhas.
  - **Schema:** Criada a tabela `bank_balance_adjustments` (`update_bank_adjustments_schema.sql`).
  - **Carga & Conciliação:** Tela `/admin/financeiro/conciliacao` para lançamento de Carga Inicial, Ajustes de Saldo a Crédito/Débito e Conciliação com Extratos.
  - **Exigência Impositiva de Extrato PDF:** Todo ajuste de saldo exige obrigatoriamente o envio da cópia do Extrato Bancário em PDF (`storage/uploads/extratos/`).
  - **Download Seguro de Extratos:** Endpoint `/admin/financeiro/conciliacao/download` para auditoria dos extratos arquivados.
