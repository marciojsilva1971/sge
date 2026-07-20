Identidade e Regras do Projeto:
Você é um desenvolvedor Web Full-Stack especialista em PHP Puro e segurança de sistemas aplicados a campanhas eleitorais. O projeto atual é o Sistema de Gestão Eleitoral (SGE). Leia com atenção as diretrizes de desenvolvimento no arquivo local `AGENTS.md` e o plano geral em `implementation_plan.md`. A segurança da informação é crítica, pois vazamentos podem impactar a elegibilidade do candidato.

Caminho do Projeto:
g:\Meu Drive\____2ERC\Outros\1_Projetos\cds
(O Apache está configurado para servir a pasta /public como raiz em http://localhost/projeto-eleicao/)

Tarefa Atual (Sprint 1): 
Gerar o banco de dados inicial, a estrutura MVC e o Módulo de Usuários juntamente com o Painel de Administração.

Requisitos Técnicos e Funcionais:

1. Banco de Dados (schema.sql):
   Crie o arquivo `schema.sql` na raiz do projeto com as 3 tabelas iniciais:
   - `roles`: Perfis de acesso com suporte a permissões flexíveis (JSON serializado no banco). Perfis iniciais obrigatórios: 'ADMINISTRADOR', 'FINANCEIRO', 'COLABORADOR_CAMPO'.
   - `usuarios`: Nome, e-mail (unique), celular (para envio de WhatsApp), password_hash, role_id, status (ENUM 'ATIVO', 'PENDENTE', 'INATIVO'), profile_photo_path (caminho da foto), token_ativacao_hash (SHA-256 para ativação de convites) e token_expira_em.
   - `logs_auditoria`: Trilha de auditoria imutável registrando id, user_id, action, table_name, record_id, old_values (JSON), new_values (JSON), ip_address, user_agent e created_at.

2. Estrutura Base MVC em PHP Puro:
   Monte a estrutura de pastas e arquivos no padrão MVC:
   - `/config/database.php`: Retorna as credenciais e configuração de conexão PDO.
   - `/app/Core/Database.php`: Singleton seguro para conexão via PDO.
   - `/app/Core/Router.php`: Roteador limpo em PHP.
   - `/app/Core/Controller.php` / `Model.php`: Classes bases.
   - `/public/index.php`: Front Controller que inicializa o roteador.
   - `/public/uploads/profiles/`: Pasta para armazenamento das fotos de perfil.

3. Fluxo de Ativação por Convite (Sem Cadastro Público):
   - Apenas administradores cadastrados podem convidar novos usuários (Nome, E-mail, Celular e Cargo).
   - O sistema cria o usuário como 'PENDENTE', gera um token de ativação criptográfico de 24h e registra no banco.
   - Forneça a rota `/ativar?token=...`. Ao acessar, o usuário insere a senha forte e faz o upload opcional da sua foto de perfil. O sistema altera o status para 'ATIVO' e grava o log de auditoria.

4. Envio de Convite por WhatsApp & E-mail:
   Ao convidar um usuário, o sistema deve prever as formas de envio do link de ativação:
   - **Click-to-Chat (WhatsApp Web/App):** No painel, após salvar o convite, deve ser exibido um botão que abre um link direto (`https://wa.me/TELEFONE?text=MENSAGEM`) contendo o link de ativação personalizado. Isso permite ao Administrador enviar o link diretamente pelo seu WhatsApp pessoal de forma manual e segura.
   - **Envio Automático por API Z-API (Igual ao projeto 2ERC):** Crie a classe/serviço para disparo automático usando a API do **Z-API**.
     - URL de Envio: `https://api.z-api.io/instances/3F3442BBA01DD1E1BB8B82171A0617F6/token/615EDEA93296491518BBA31A/send-text`
     - Token: `F328a02a354f54675adaa95b599db9eabS` (enviado no header `Client-Token`)
     - Payload JSON: `{"phone": "55...", "message": "..."}`
     - Certifique-se de que a função limpe caracteres não numéricos do telefone e garanta a presença do DDI `55` (Brasil).
     - O disparo ocorrerá no servidor ao salvar o convite de forma silenciosa (para não travar a requisição PHP em caso de timeout).
   - **Envio de E-mail:** Simulação ou envio real contendo o link de ativação.

5. Painel Administrativo de Usuários (Frontend & Backend):
   - Autenticação e Login seguro (Prevenção de Session Hijacking, Session Fixation, Brute Force).
   - Tela de Gerenciamento de Usuários: Listagem geral, filtros por nome/cargo/status, botão "Convidar Novo Usuário" (abre modal de convite).
   - Edição de Perfil: Permite ao usuário alterar sua foto de perfil e dados posteriormente.
   - Configuração de RBAC: Edição das permissões associadas a cada cargo no banco de dados.

6. Visual, Estilização e Referência de UI (Pilotos/Mockups):
   O Agent Manager deve criar um arquivo CSS global (`/public/css/style.css`) contendo a identidade visual acordada. O design deve ser premium, responsivo (Mobile-first para ativação e colaborador de campo, Desktop para administração) e usar os seguintes padrões de UI:
   - **Imagens Piloto de Referência:** Você deve analisar e replicar fielmente o layout, a disposição dos componentes, o estilo visual e a hierarquia apresentados nas imagens salvas no diretório raiz do projeto:
     - `dashboard_mockup.png` (Referência para o painel principal)
     - `admin_panel_mockup.png` (Referência para a tela de administração e listagem de usuários)
     - `field_portal_mockup.png` (Referência para a interface responsiva de campo)
   - **Variáveis CSS obrigatórias (no :root):**
     ```css
     --bg-color: #0f172a;              /* Slate gray escuro */
     --card-bg: rgba(30, 41, 59, 0.7); /* Glassmorphism leve */
     --border-color: rgba(255, 255, 255, 0.1);
     --text-primary: #f8fafc;
     --text-secondary: #94a3b8;
     --accent-teal: #0d9488;           /* Destaque principal (Teal) */
     --accent-indigo: #4f46e5;         /* Destaque secundário (Indigo) */
     --success-color: #10b981;         /* Verde de sucesso */
     ```
   - **Efeitos e Tipografia:**
     - Fonte primária: `'Segoe UI', Roboto, Helvetica, Arial, sans-serif` (ou Google Fonts como `Inter` ou `Outfit`).
     - Efeito Glassmorphism em cartões e modais: `backdrop-filter: blur(10px); border: 1px solid var(--border-color); background: var(--card-bg);`.
     - Mobile-first: Os elementos de interação e botões devem possuir área de toque mínima de `44px x 44px`.
     - Transições suaves: Adicione `transition: all 0.3s ease;` para efeitos de hover e foco em botões e inputs.

Escreva o código priorizando a prevenção contra SQL Injection, XSS, CSRF e conformidade com a LGPD.