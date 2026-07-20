Identidade e Regras do Projeto:
Você é um desenvolvedor Web Full-Stack especialista em PHP Puro e segurança de sistemas aplicados a campanhas eleitorais. O projeto atual é o Sistema de Gestão Eleitoral (SGE). Leia com atenção as diretrizes de desenvolvimento no arquivo local `AGENTS.md` e o plano geral em `implementation_plan.md`. A segurança da informação é crítica, pois vazamentos podem impactar a elegibilidade do candidato.

Caminho do Projeto:
g:\Meu Drive\____2ERC\Outros\1_Projetos\cds

Tarefa Atual (Sprint 2): 
Implementação do Banco de Dados Financeiro, do Mecanismo de Criptografia de Comprovantes no Servidor, e do Módulo Financeiro Centralizador (Receitas, Despesas, Fila de Aprovação e Portal do Colaborador com Geo-comprovação).

Requisitos Técnicos e Funcionais:

1. Atualização do Banco de Dados (schema.sql):
   Adicione as seguintes tabelas ao seu arquivo de modelagem e execute-as no MySQL:
   - `bank_accounts`: `id`, `name`, `bank_name`, `agency`, `account_number`, `fund_type` (ENUM 'FEFC', 'FUNDO_PARTIDARIO', 'OUTROS_RECURSOS'), `balance` (DECIMAL(15,2)), `status` (ENUM 'ATIVA', 'ENCERRADA').
   - `suppliers`: `id`, `cnpj_cpf` (unique), `corporate_name`, `trade_name`, `address`, `phone`, `email`, `status`.
   - `spce_categories`: `id`, `code` (Unique - código de contas do TSE), `description`, `type` (ENUM 'RECEITA', 'DESPESA').
   - `despesas`: `id`, `description`, `supplier_id`, `bank_account_id`, `value`, `date_incurred`, `payment_method`, `status` (ENUM 'PENDENTE', 'APROVADO', 'REJEITADO', 'PAGO'), `spce_category_id`, `user_id` (lançador), `approved_by`, `approved_at`.
   - `comprovantes_cripto`: `id`, `expense_id`, `encrypted_file_path`, `original_name`, `iv`, `mime_type` (armazenamento de arquivos criptografados).
   - `travel_reports`: `id`, `user_id`, `purpose`, `start_date`, `end_date`, `vehicle_plate`, `status` (ENUM 'EM_ANDAMENTO', 'ENVIADO', 'APROVADO', 'REJEITADO'), `approved_by`, `approved_at`.
   - `travel_receipts`: `id`, `travel_report_id`, `supplier_cnpj`, `receipt_date`, `value`, `spce_category_id`, `encrypted_file_path`, `iv`, `status`, `notes`.
   - `militancy_activities`: `id`, `user_id`, `activity_date`, `description`, `encrypted_photo_path`, `iv`, `latitude`, `longitude`, `status`.

2. Criptografia Segura de Comprovantes (Application-Level Encryption):
   - Os comprovantes de gastos e fotos enviados no upload **não** podem ser salvos na pasta `/public`. Devem ser gravados em uma pasta privada fora da raiz pública do servidor (ex: `/storage/uploads/`).
   - Implemente um serviço (`App\Services\EncryptionService`) para criptografar simetricamente o arquivo binário via `AES-256-CBC` utilizando o `APP_KEY` definido no `.env` e gerando um IV dinâmico para cada arquivo.
   - Forneça a rota `/admin/financeiro/comprovante?id=...` para ler, descriptografar o arquivo físico em tempo de execução e enviá-lo ao navegador com os cabeçalhos apropriados de tipo de arquivo (PDF, JPEG, PNG, etc.), restrita a perfis autenticados com permissão.

3. Centralizador Financeiro (Receitas & Despesas Gerais):
   - Lançamento de Doações/Receitas por fonte de recurso e conta bancária correspondente.
   - Cadastro de fornecedores com máscaras de CNPJ/CPF e validação básica.
   - Tela de lançamento de despesas gerais vinculando o fornecedor, conta bancária, categoria SPCE e upload do comprovante que será criptografado automaticamente.

4. Fila de Aprovação Financeira / Painel de Controle (Desktop):
   - Painel gerencial premium baseado no piloto `dashboard_mockup.png` e `admin_panel_mockup.png`.
   - Exibição de estatísticas chaves: Saldo total em caixa, saldo por tipo de recurso (FEFC, Fundo Partidário, Recursos Próprios), e percentual atingido do limite máximo de gastos da campanha.
   - Fila de aprovação de despesas de campo (viagens) e atividades pendentes de análise jurídica/fiscal. 
   - Ação de visualização do comprovante descriptografado em modal e botões para "Aprovar" ou "Rejeitar" (com observação).

5. Portal Móvel do Colaborador / Militante (Mobile-First):
   - Desenvolver a interface baseada na tela piloto `field_portal_mockup.png`.
   - **Registro de Despesa de Viagem:** Formulário vertical limpo com upload da foto do cupom fiscal.
   - **Comprovação de Militância (Panfletagem):** Upload da foto da ação de panfletagem em campo e captura automatizada das coordenadas geográficas (latitude e longitude) via Geolocation API do navegador do celular.
   - **Compressão de Imagens no Cliente:** Compactação JS via canvas no navegador do smartphone do usuário antes de realizar o upload para poupar tráfego de dados (3G/4G) em áreas remotas.

Escreva o código em PHP Puro MVC, respeitando as sessões seguras e o tratamento correto contra vulnerabilidades OWASP Top 10.
