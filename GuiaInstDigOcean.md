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
```bash
sudo apt install apache2 mysql-server php php-curl php-mysql php-mbstring php-xml php-gd php-zip unzip git -y
```
O que faz: Instala em um único lote:
- `apache2`: Servidor HTTP que recebe os acessos dos usuários.
- `mysql-server`: Banco de dados relacional para guardar dados do SGE.
- `php`: Interpretador oficial da linguagem PHP.
- `php-mysql`: Extensão PDO/MySQL para conexão com o banco de dados.
- `php-curl`: Módulo para fazer requisições HTTP externas (API Z-API do WhatsApp).
- `php-gd`: Módulo de processamento de imagens (fotos de perfil e militância).
- `php-mbstring` e `php-xml`: Processamento de texto e dados em UTF-8.
- `git`: Ferramenta de controle de versão para receber o deploy do projeto.
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
sudo mkdir -p /var/www/sge/public/uploads/profiles
sudo mkdir -p /var/www/sge/storage/uploads
sudo mkdir -p /var/www/sge/storage/backups
O que faz: Cria a estrutura oficial onde o código será instalado.
Por que executar:
/var/www/sge/public: Será a ÚNICA pasta visível para a internet.
/var/www/sge/public/uploads/profiles: Armazena as fotos de perfil dos usuários.
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