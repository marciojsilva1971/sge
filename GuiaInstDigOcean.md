🚀 Guia Passo a Passo: Configuração da VPS DigitalOcean para o SGE
Acesse sua VPS pelo terminal usando o comando:

bash
ssh root@IP_DA_SUA_DROPLET
🛠️ ETAPA 1: Atualização do Sistema Operacional
Antes de instalar qualquer programa, é fundamental atualizar o sistema para garantir as últimas correções de segurança.

Comando 1.1:
bash
sudo apt update && sudo apt upgrade -y
O que faz: apt update baixa a lista atualizada de pacotes disponíveis nos repositórios do Ubuntu. O apt upgrade -y baixa e instala todas as atualizações de programas e do Kernel sem pedir confirmação manual (-y).
Por que executar: Garante que o servidor esteja com todas as correções de vulnerabilidades aplicadas antes de receber dados da aplicação.
📦 ETAPA 2: Instalação do Apache, PHP 8.2, MySQL e Git (Stack LAMP)
Agora vamos instalar todos os programas que compõem o servidor web, banco de dados e o interpretador PHP.

Comando 2.1:
bash
sudo apt install apache2 mysql-server php8.2 php8.2-curl php8.2-pdo php8.2-mysql php8.2-mbstring php8.2-xml php8.2-gd php8.2-zip unzip git -y
O que faz: Instala em um único lote:
apache2: Servidor HTTP que recebe os acessos dos usuários.
mysql-server: Banco de dados relacional para guardar dados do SGE.
php8.2: Interpretador da linguagem PHP.
php8.2-pdo e php8.2-mysql: Extensões de conexão segura com o MySQL via PDO.
php8.2-curl: Módulo para fazer requisições HTTP externas (necessário para enviar mensagens no WhatsApp via API Z-API).
php8.2-gd: Módulo de processamento de imagens (usado na compactação de fotos da militância e fotos de perfil).
php8.2-mbstring e php8.2-xml: Processamento de texto e dados em UTF-8.
git: Ferramenta de controle de versão para receber o deploy do projeto.
Por que executar: Instala todos os pré-requisitos que o sistema PHP Puro precisa para rodar.
⚙️ ETAPA 3: Ativação de Módulos e Reinicialização do Apache
O roteamento de URLs do SGE (/login, /ativar, /admin/dashboard) exige que o Apache saiba reescrever URLs amigáveis.

Comando 3.1:
bash
sudo a2enmod rewrite
O que faz: Ativa o módulo mod_rewrite do Apache.
Por que executar: Sem este módulo, ao tentar acessar /admin/dashboard, o Apache retornará o erro 404 Not Found, pois ele procurará uma pasta física em vez de redirecionar para o Front Controller (public/index.php).
Comando 3.2:
bash
sudo systemctl restart apache2
O que faz: Reinicia o serviço do Apache.
Por que executar: Aplica a habilitação do módulo mod_rewrite no servidor ativo.
🗄️ ETAPA 4: Configuração de Segurança do MySQL e Criação do Banco
Agora vamos proteger o servidor MySQL e criar o banco sge com a codificação correta para caracteres em português e emojis.

Comando 4.1 (Segurança do MySQL):
bash
sudo mysql_secure_installation
O que faz: Abre um assistente interativo no terminal.
O que responder durante o assistente:
VALIDATE PASSWORD COMPONENT? Responda n (ou y se quiser definir regras estritas de senha no banco).
New password for root? Defina uma senha forte para o root do MySQL (anote esta senha!).
Remove anonymous users? Responda y (remove contas de teste anônimas).
Disallow root login remotely? Responda y (impede que hackers tentem conectar no MySQL pela internet).
Remove test database? Responda y (apaga bancos de teste).
Reload privilege tables now? Responda y (aplica tudo imediatamente).
Comando 4.2 (Criar Banco e Usuário do SGE):
Acesse o MySQL:

bash
sudo mysql
Cole os comandos SQL abaixo dentro do MySQL:

sql
CREATE DATABASE sge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sge_user'@'localhost' IDENTIFIED BY 'SUA_SENHA_MUITO_FORTE_AQUI';
GRANT ALL PRIVILEGES ON sge.* TO 'sge_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
O que faz:
utf8mb4: Cria o banco com suporte nativo a acentos e emojis (usados no WhatsApp).
CREATE USER: Cria o usuário exclusivo sge_user (evita que a aplicação PHP rode como root do MySQL, seguindo as regras de segurança OWASP).
GRANT ALL PRIVILEGES: Dá acesso ao sge_user apenas no banco sge.
📂 ETAPA 5: Estrutura de Pastas e Isolamento de Segurança (LGPD)
Para atender ao requisito de Segurança por Design e LGPD, a pasta onde ficam os comprovantes criptografados (/storage) jamais pode ficar exposta publicamente na web.

Comando 5.1 (Criar diretórios do projeto):
bash
sudo mkdir -p /var/www/sge/public
sudo mkdir -p /var/www/sge/storage/uploads
sudo mkdir -p /var/www/sge/storage/backups
O que faz: Cria a estrutura oficial onde o código será instalado.
Por que executar:
/var/www/sge/public: Será a ÚNICA pasta visível para a internet.
/var/www/sge/storage: Fica fora da web. É onde salvamos comprovantes fiscais criptografados e backups.
Comando 5.2 (Ajustar permissões de acesso do Apache):
bash
sudo chown -R www-data:www-data /var/www/sge
sudo chmod -R 755 /var/www/sge
sudo chmod -R 775 /var/www/sge/storage /var/www/sge/public/uploads
O que faz:
chown -R www-data:www-data: Define o usuário www-data (conta do sistema que roda o Apache) como dono de todos os arquivos.
chmod 755: Permite leitura e execução segura.
chmod 775: Dá permissão de escrita para que o PHP consiga salvar as fotos de perfil e criar os comprovantes criptografados nas pastas autorizadas.
🌐 ETAPA 6: Configuração do VirtualHost do Apache
Vamos criar o arquivo de site do Apache que conecta o seu domínio diretamente à pasta /public.

Comando 6.1 (Criar arquivo de configuração):
bash
sudo nano /etc/apache2/sites-available/sge.conf
Cole a configuração abaixo (substitua seu-dominio.com.br pelo seu domínio real):

apache
<VirtualHost *:80>
    ServerName seu-dominio.com.br
    ServerAlias www.seu-dominio.com.br
    DocumentRoot /var/www/sge/public
    <Directory /var/www/sge/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/sge_error.log
    CustomLog ${APACHE_LOG_DIR}/sge_access.log combined
</VirtualHost>
(Para salvar no nano: Pressione Ctrl + O, depois ENTER. Para sair: Ctrl + X).

Explicação dos parâmetros do Apache:
DocumentRoot /var/www/sge/public: CRÍTICO PARA SEGURANÇA. Garante que qualquer tentativa de acessar arquivos do sistema via navegador (como .env, scripts SQL ou PHP brutos) seja bloqueada.
Options -Indexes: Desativa a listagem de diretórios (se uma pasta não tiver index, o Apache não mostrará a lista de arquivos para o visitante).
AllowOverride All: Permite o uso do arquivo .htaccess na pasta public.
Comando 6.2 (Ativar o site e desativar a página padrão do Ubuntu):
bash
sudo a2dissite 000-default.conf
sudo a2ensite sge.conf
sudo systemctl reload apache2
O que faz: Desativa a página inicial "Apache2 Ubuntu Default Page" e ativa a configuração oficial do SGE.

Comando 6.3 (Ativação de SSL / HTTPS com Certbot Let's Encrypt - Garante o Cadeado Verde de Segurança):
bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d seu-dominio.com.br -d www.seu-dominio.com.br
O que faz: Emite e instala automaticamente um certificado SSL gratuito e válido para o seu domínio, ativa a porta 443 (HTTPS), redireciona todo o tráfego HTTP para HTTPS e exibe o CADEADO VERDE DE SEGURANÇA no navegador, eliminando qualquer aviso de "Não Seguro" no login.
🛡️ ETAPA 7: Configuração de Segurança com Firewall (UFW)
Para evitar acessos não autorizados ou invasões nas portas do servidor, ativamos o Firewall nativo do Ubuntu.

Comando 7.1:
bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
O que faz:
default deny incoming: Bloqueia qualquer tentativa de conexão vinda da internet por padrão.
allow 22/tcp: Mantém a porta do SSH aberta (para você não perder o acesso ao terminal!).
allow 80/tcp e allow 443/tcp: Libera as portas Web para acesso ao sistema (HTTP e HTTPS).
ufw enable: Ativa o firewall imediatamente.
🗝️ ETAPA 8: Configuração da Chave SSH para Deploy Automático via GitHub
Agora geramos as chaves para que o GitHub Actions consiga atualizar o código na VPS com segurança.

Comando 8.1 (Gerar chave SSH dedicada):
bash
ssh-keygen -t ed25519 -C "deploy-sge-vps"
(Pressione ENTER em todas as perguntas sem digitar senha).

O que faz: Cria um par de chaves criptográficas de alto nível (Ed25519).
Comando 8.2 (Autorizar a chave no servidor):
bash
cat ~/.ssh/id_ed25519.pub >> ~/.ssh/authorized_keys
O que faz: Adiciona a chave pública gerada na lista de chaves autorizadas a logar como root no servidor.
Comando 8.3 (Visualizar as chaves para copiar para o GitHub):
Visualizar a Chave Pública (Para a Deploy Key do GitHub):
bash
cat ~/.ssh/id_ed25519.pub
Visualizar a Chave Privada (Para o GitHub Secret VPS_SSH_KEY):
bash
cat ~/.ssh/id_ed25519
(Guarde esses dois textos para colar no painel de configurações do seu repositório no GitHub).
🎉 Servidor Pronto!
A VPS está totalmente preparada, blindada e configurada no padrão de segurança do projeto SGE!







Chave SSH:

ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIIE/aBcCzn38IRcKlMaWQTeIxAMvASydyBeYrYIRVaSs deploy-sge-vps


-----BEGIN OPENSSH PRIVATE KEY-----
[COLE_SUA_CHAVE_PRIVADA_GERADA_NA_VPS_AQUI]
-----END OPENSSH PRIVATE KEY-----




Com as chaves SSH geradas na etapa anterior, estamos com tudo pronto para realizar o Primeiro Deploy Automático!

Já criei o arquivo de automação do GitHub Actions no seu projeto em: 📂 
.github/workflows/deploy.yml

Siga os passos abaixo na ordem:

📋 Passo a Passo do Primeiro Deploy
Passo 1: Configurar a Deploy Key no GitHub
Acesse o seu repositório no GitHub: https://github.com/SEU_USUARIO/SEU_REPOSITORIO
Clique na aba Settings (no topo) -> Deploy keys (menu lateral esquerdo).
Clique no botão Add deploy key.
Title: VPS DigitalOcean
Key: Cole a sua chave pública:
text
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIIE/aBcCzn38IRcKlMaWQTeIxAMvASydyBeYrYIRVaSs deploy-sge-vps
Clique em Add key.
Passo 2: Configurar os Segredos (Secrets) no GitHub
No mesmo menu Settings do seu repositório GitHub, clique em Secrets and variables (menu lateral) -> Actions.

Clique em New repository secret e crie os 3 segredos:

Segredo 1:

Name: VPS_HOST
Secret: O IP da sua Droplet na DigitalOcean (ex: 143.198.xxx.xxx)
Segredo 2:

Name: VPS_USERNAME
Secret: root
Segredo 3:

Name: VPS_SSH_KEY
Secret: Cole todo o bloco da chave privada (incluindo as linhas BEGIN e END):
text
-----BEGIN OPENSSH PRIVATE KEY-----
[COLE_SUA_CHAVE_PRIVADA_GERADA_NA_VPS_AQUI]
-----END OPENSSH PRIVATE KEY-----
Passo 3: Clonar o Repositório pela 1ª vez na VPS
Acesse o terminal da sua VPS (ssh root@IP_DA_DROPLET) e execute:

bash
# Limpa a pasta temporária para clonar o repositório oficial
sudo rm -rf /var/www/sge
# Clona o seu repositório privado do GitHub
cd /var/www
git clone git@github.com:SEU_USUARIO/SEU_REPOSITORIO.git sge
(Substitua SEU_USUARIO e SEU_REPOSITORIO pelos nomes reais da sua conta GitHub).

Recrie as pastas de uploads e ajuste as permissões:

bash
sudo mkdir -p /var/www/sge/public/uploads/profiles
sudo mkdir -p /var/www/sge/storage/uploads
sudo mkdir -p /var/www/sge/storage/backups
sudo chown -R www-data:www-data /var/www/sge
sudo chmod -R 755 /var/www/sge
sudo chmod -R 775 /var/www/sge/storage /var/www/sge/public/uploads
Passo 4: Criar o arquivo .env na VPS
Na VPS, crie o arquivo de ambiente em /var/www/sge/.env:

bash
sudo nano /var/www/sge/.env
Cole as configurações:

ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sge
DB_USER=sge_user
DB_PASS=SUA_SENHA_DO_SGE_USER
APP_ENV=production
APP_URL=http://IP_DA_SUA_DROPLET/
APP_KEY=8f4a7c8e9b6d5c4b3a2f1e0d9c8b7a6e5d4c3b2a1f0e9d8c7b6a5f4e3d2c1b0a
ZAPI_URL=https://api.z-api.io/instances/3F3442BBA01DD1E1BB8B82171A0617F6/token/615EDEA93296491518BBA31A/send-text
ZAPI_CLIENT_TOKEN=F328a02a354f54675adaa95b599db9eabS
(Para salvar: Ctrl + O, ENTER. Para sair: Ctrl + X).

Passo 5: Executar o Seed Inicial do Banco na VPS
Na VPS, execute o script para popular o banco de dados sge com o schema e perfis:

bash
cd /var/www/sge
php scratch/seed.php
Passo 6: Fazer o Push Local para o GitHub
No terminal do seu computador (dentro da pasta do projeto cds):

bash
git add .
git commit -m "Deploy inicial com GitHub Actions"
git push origin main
🎉 Pronto! O GitHub Actions será acionado automaticamente e a aba Actions no seu GitHub mostrará o status verde do deploy concluído com sucesso na VPS!

