# Guia de Deploy e Homologação - Fase 4 (Módulo de RH e Colaboradores)

Este documento orienta o procedimento de implantação (Deploy) do Sistema de Gestão Eleitoral (SGE) no ambiente de homologação/produção (DigitalOcean VPS).

---

## 🚀 1. Atualização do Banco de Dados no Servidor

Caso as tabelas da **Fase 4** ainda não estejam criadas no MySQL do servidor remoto, execute a estrutura de tabelas contida no arquivo `schema_rh.sql`:

```sql
-- Executar via PHPMyAdmin ou CLI MySQL do servidor
CREATE TABLE IF NOT EXISTS `colaboradores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `nome_completo` VARCHAR(150) NOT NULL,
    `cpf` VARCHAR(14) NOT NULL UNIQUE,
    `rg` VARCHAR(20) NOT NULL,
    `rg_orgao_emissor` VARCHAR(20) NOT NULL,
    `documento_foto_path` VARCHAR(255) NULL,
    `data_nascimento` DATE NOT NULL,
    `idade_calculada` INT NOT NULL,
    `celular_whatsapp` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `cep` VARCHAR(10) NULL,
    `logradouro` VARCHAR(255) NULL,
    `numero` VARCHAR(20) NULL,
    `complemento` VARCHAR(50) NULL,
    `bairro` VARCHAR(100) NULL,
    `cidade` VARCHAR(100) NULL,
    `uf` VARCHAR(2) NULL,
    `banco_codigo` VARCHAR(10) NULL,
    `banco_nome` VARCHAR(100) NULL,
    `agencia` VARCHAR(20) NULL,
    `conta` VARCHAR(30) NULL,
    `tipo_conta` ENUM('CORRENTE', 'POUPANCA') DEFAULT 'CORRENTE',
    `chave_pix` VARCHAR(100) NULL,
    `optin_whatsapp` TINYINT(1) DEFAULT 0,
    `optin_timestamp` DATETIME NULL,
    `optin_ip` VARCHAR(45) NULL,
    `token_cadastro` VARCHAR(64) NULL UNIQUE,
    `status` ENUM('AGUARDANDO_AVAL_CADASTRO', 'AGUARDANDO_ASSINATURA_CONTRATO', 'AGUARDANDO_CONFERENCIA_CONTRATO', 'ATIVO', 'REJEITADO') DEFAULT 'AGUARDANDO_AVAL_CADASTRO',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contratos_colaboradores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `colaborador_id` INT NOT NULL,
    `titulo_contrato` VARCHAR(150) NOT NULL,
    `funcao_campanha` VARCHAR(100) NOT NULL,
    `valor_contratado` DECIMAL(15,2) NOT NULL,
    `forma_pagamento` VARCHAR(100) NOT NULL,
    `data_inicio` DATE NOT NULL,
    `data_fim` DATE NOT NULL,
    `tipo_assinatura` ENUM('TERCEIROS_API', 'MANUAL_UPLOAD') DEFAULT 'TERCEIROS_API',
    `external_signature_url` VARCHAR(255) NULL,
    `pdf_original_path` VARCHAR(255) NULL,
    `pdf_assinado_path` VARCHAR(255) NULL,
    `hash_documento` VARCHAR(64) NULL,
    `status_contrato` ENUM('EMITIDO', 'AGUARDANDO_ASSINATURA', 'ASSINADO', 'CANCELADO') DEFAULT 'EMITIDO',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📦 2. Publicação de Código no Repositório (Git & GitHub Actions)

Se for a primeira vez que você está vinculando o projeto local ao repositório do GitHub na pasta `c:\xampp\htdocs\sge`:

```bash
# 1. Inicializar o repositório Git local
git init

# 2. Conectar com o seu repositório remoto no GitHub (substitua com o link do seu projeto)
git remote add origin https://github.com/SEU_USUARIO_OU_ORGANIZACAO/NOME_DO_REPOSITORIO.git

# 3. Definir a branch principal como main
git branch -M main

# 4. Adicionar todos os arquivos modificados da Fase 4
git add .

# 5. Registrar o commit de entrega da Fase 4
git commit -m "feat(rh): Conclusão do Módulo de RH, navegação do Portal no topo e consulta cadastral de colaboradores"

# 6. Enviar para o repositório remoto no GitHub (dispara a esteira automática do GitHub Actions)
git push -u origin main
```

> **Nota:** Se o repositório já existir no GitHub com arquivos iniciais (ex: `README.md`), você pode executar `git pull origin main --allow-unrelated-histories` antes do `git push`.

---

## ⚙️ 3. Ajuste de Permissões e Diretórios de Upload na VPS

Após a execução do workflow automático do GitHub Actions (`.github/workflows/deploy.yml`), certifique-se de que os diretórios de upload possuem as permissões corretas no servidor Linux:

```bash
# Conectar via SSH na VPS
ssh root@SEU_IP_VPS

# Criar diretórios de upload para contratos e cupons fiscais
mkdir -p /var/www/sge/storage/uploads/contratos
mkdir -p /var/www/sge/storage/uploads/documentos
mkdir -p /var/www/sge/storage/uploads/comprovantes

# Ajustar permissões de escrita para o servidor Web (Nginx / Apache / www-data)
chmod -R 775 /var/www/sge/storage /var/www/sge/public/uploads
chown -R www-data:www-data /var/www/sge
```

---

## 🧪 4. Roteiro de Testes de Homologação em Produção

Após a conclusão da implantação, execute o checklist final:

1. **Gestão de RH (`/admin/rh`):**
   - [ ] Abrir a tela de Gestão de RH.
   - [ ] Verificar a exibição do **Colaborador 1** (`CPF: 141.019.908-83`).
   - [ ] Clicar no botão **`📑 Conferir & Homologar`**.
   - [ ] Confirmar o retorno do painel **`📋 Situação Cadastral do Candidato a Colaborador`** com os selos verdes (`CPF VÁLIDO & REGULAR`, `APTO PARA CONTRATAÇÃO NA CAMPANHA`).
   - [ ] Clicar no link para visualizar a cópia do contrato em PDF.
   - [ ] Conceder perfil e homologar o colaborador (notificação de credenciais via WhatsApp Z-API).

2. **Portal do Colaborador (Mobile/Desktop):**
   - [ ] Efetuar login com as credenciais do colaborador homologado.
   - [ ] Verificar a barra de navegação no **topo da tela** (`Início`, `Viagens`, `Militância`, `Gastos`).
   - [ ] Acessar **Viagens**, iniciar uma viagem e testar a submissão de cupom fiscal com o botão **`+ Adicionar Cupom`** (sem erro 404).
