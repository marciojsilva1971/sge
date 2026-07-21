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
