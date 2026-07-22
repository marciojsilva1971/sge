# Histórico da Linha de Desenvolvimento - Fase 1: Infraestrutura, Segurança & Deploy

Este documento mantém o registro permanente de todas as sessões, decisões técnicas e alterações de infraestrutura, servidores, segurança web e implantação em produção.

---

## 📅 Sessão 1: Configuração do Ambiente e Servidor Web (XAMPP / VPS)
* **Ações:**
  - Ajustes de reescrita `.htaccess` para roteamento MVC seguro no Apache.
  - Correção de caminhos absolutos e carregamento de assets via `Controller::baseUrl()`.
  - Resolução de URLs e carregamento correto de CSS para acessos locais e via IP da rede interna (`192.168.x.x`).

---

## 📅 Sessão 2: Forçamento de HTTPS, SSL Certbot e Cadeado de Conexão Segura
* **Ações:**
  - **Redirecionamento 301 Incondicional:** Adicionadas regras no `.htaccess` da raiz e de `public/` para redirecionar tráfego HTTP para HTTPS.
  - **Detecção Dinâmica de SSL:** Atualizado `Controller.php` e `Session.php` para tratar proxies reversos (`HTTP_X_FORWARDED_PROTO`, `HTTP_X_FORWARDED_SSL`).
  - **Cabeçalhos de Segurança HTTP:** Ativados HSTS (`Strict-Transport-Security`), `nosniff` e `SameSite=Strict` nos cookies.
  - **Emissão de SSL na VPS:** Guia de emissão de certificado gratuito Let's Encrypt com Certbot (`sudo certbot --apache`).

---

## 📅 Sessão 3: Cadastramento de Administradores e Controle de Credenciais em Produção
* **Ações:**
  - Criado o script `seed_admin_users.sql` para carga segura de usuários administradores homologados no banco de dados.
  - Definição da política de senhas fortes criptografadas via `Bcrypt`.
  - Definição do perfil `ADMINISTRADOR` no controle de acesso.

---

## 📅 Sessão 4: Implementação de Responsividade Multi-Dispositivo (Tablets & Smartphones)
* **Ações:**
  - **Menu Off-Canvas Drawer:** Adicionado o botão hambúrguer de navegação mobile no cabeçalho superior (`topbar`) e overlay escurecido de foco no layout principal [`app/Views/layouts/main.php`](file:///c:/xampp/htdocs/sge/app/Views/layouts/main.php).
  - **Recolhimento Inteligente da Sidebar:** Em telas menores ou iguais a `992px` (tablets em pé e smartphones), a barra lateral recolhe-se fora da tela e desliza suavemente ao ser ativada pelo usuário.
  - **Sub-navegações por Abas (Touch Scroll):** Configurada a rolagem horizontal por toque (`overflow-x: auto`) para todas as abas dos módulos (Financeiro, RH, Prestação de Contas SPCE), impedindo quebras verticais e mantendo a navegabilidade fluida.
  - **Grid Fluido & Modais Mobile:** Ajustados os cards KPI para colunas adaptativas (`minmax(220px, 1fr)`) e modais com largura responsiva de `95%` em smartphones com rolagem interna de conteúdos longos.
