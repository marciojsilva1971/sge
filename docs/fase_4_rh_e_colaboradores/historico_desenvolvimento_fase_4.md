# Histórico da Linha de Desenvolvimento - Fase 4: Gestão de RH & Colaboradores

Este documento mantém o registro permanente de todas as implementações do **Módulo de RH e Gestão de Colaboradores**, abrangendo admissão, documentos, fluxo de contratação em 4 etapas e notificações via WhatsApp (Z-API).

---

## 📅 Sessão 1: Modelagem e Admissão de Colaboradores
* **Ações:**
  - Criação da tabela `colaboradores` com campos para dados pessoais, CPF, RG, PIS/PASEP, endereço, chave PIX, dados bancários e papel eleitoral.
  - Implementação da tela `/admin/rh` para listagem, busca e filtragem de colaboradores.

---

## 📅 Sessão 2: Upload de Documentos de Identificação e Contrato
* **Ações:**
  - Adicionado suporte a upload de documentos de identificação (RG/CNH/CPF) e contratos assinados.
  - Criado o diretório seguro `storage/uploads/rh/` com proteção de acesso direto.
  - Implementado endpoint seguro `/admin/rh/documentos/download` com verificação de autenticação antes de servir os arquivos.

---

## 📅 Sessão 3: Fluxo Estrito de Contratação em 4 Etapas
* **Ações:**
  - Implementado o ciclo de vida do colaborador:
    1. `PENDENTE_AVAL` (Aguardando análise e aprovação da liderança da campanha).
    2. `PENDENTE_EMISSAO` (Aprovado, aguardando confecção e emissão do contrato de prestação de serviços).
    3. `PENDENTE_ASSINATURA` (Contrato emitido, aguardando assinatura do colaborador).
    4. `PENDENTE_CONFERENCIA` (Contrato assinado anexado, aguardando conferência final do RH).
    5. `ATIVO` (Colaborador homologado, com acesso liberado ao Portal do Colaborador).
  - Implementados modals e botões de avanço de etapa com log de auditoria em cada transição.

---

## 📅 Sessão 4: Integração Oficial Z-API (Notificações por WhatsApp com Opt-In)
* **Ações:**
  - Implementada a classe `ZApiService` para envio automático de notificações via WhatsApp.
  - Disparo de mensagem no momento da liberação do contrato (`PENDENTE_ASSINATURA`) contendo link para visualização.
  - Disparo de mensagem com as credenciais de acesso ao portal assim que o cadastro é homologado (`ATIVO`).
  - Respeito estrito ao fluxo de Opt-In e LGPD para evitar banimento do número do candidato.

---

## 📅 Sessão 5: Geração Automatizada do Manual do Colaborador (PDF)
* **Ações:**
  - Criado o roteiro e gerador do **Manual de Cadastro e Uso do Portal do Colaborador SGE** em formato PDF.
  - Disponibilizado para o RH encaminhar aos novos contratados durante a admissão.

---

## 📅 Sessão 6: Cadastro de Foto do Rosto (Selfie/Avatar) para Colaboradores
* **Ações:**
  - Atualizada a estrutura da tabela `colaboradores` (coluna `foto_rosto_path`) e criado script `update_colaboradores_foto_rosto_schema.sql`.
  - Adicionada a funcionalidade de upload de foto do rosto (Selfie / Avatar) com pré-visualização instantânea (thumbnail circular) nos formulários de auto-cadastro público e cadastro administrativo.
  - Atualizado o método `homologarEAtribuirPerfil()` no `Colaborador.php` para sincronizar automaticamente a foto do rosto com o campo `profile_photo_path` na tabela `usuarios`, garantindo avatar em todas as telas acessíveis do SGE.
  - Atualizada a listagem de colaboradores (`/admin/rh`) com coluna dedicada para exibição do avatar redondo com iniciais como fallback e links nos modais de conferência.
  - Atualizado o cabeçalho global (`app/Views/layouts/main.php`) e regras CSS (`public/css/style.css`).

---

## 📅 Sessão 7: Página de Perfil do Colaborador no Portal (/portal/perfil)
* **Ações:**
  - Mapeadas as rotas `GET /portal/perfil` e `POST /portal/perfil` no `public/index.php`.
  - Criada a visão `app/Views/portal/perfil.php` em estilo mobile glassmorphism, permitindo ao colaborador visualizar seus dados cadastrais do RH (Nome, CPF, RG, E-mail, Papel e PIX).
  - Implementada funcionalidade para inclusão/alteração da foto do perfil (rosto/avatar) diretamente pelo portal com preview instantâneo via JS, sincronizando os dados simultaneamente em `usuarios.profile_photo_path` e `colaboradores.foto_rosto_path`.
  - Adicionada a opção para atualização de celular/WhatsApp e alteração segura de senha de acesso com validação da senha atual.
  - Atualizado o layout do portal (`app/Views/layouts/portal.php`) para exibir o avatar do colaborador no topo superior direito e incluir o item **👤 Perfil** na barra de navegação principal.

---

## 📅 Sessão 8: Resolução de Cache/Exibição de Avatares e Perfil Administrativo
* **Ações:**
  - **Correção da Exibição de Fotos (Bug 404):** Identificado que a pasta `storage/avatares` ficava fora do document root público do servidor (`public/`), gerando erros 404. O diretório de salvamento de avatares foi unificado para `public/uploads/profiles/`, permitindo o carregamento imediato e direto pelo navegador (com fallback robusto nos controladores para manter a compatibilidade).
  - **Validação de Uploads:** Implementada checagem robusta de códigos de erro de upload no PHP (`upload_max_filesize`, `post_max_size`, etc.), impedindo que uploads que falhem no servidor passem como "sucesso" sem atualizar o banco de dados.
  - **Perfil do Administrador (/admin/profile):** Implementados os métodos `profile()` e `updateProfile()` em `AdminController.php` e a view correspondente `app/Views/admin/profile.php`, dando aos administradores a mesma capacidade de atualizar dados pessoais, celular, foto de perfil e senha com os mesmos critérios de segurança.
  - **Ajustes de Layout:** Tornada a área de perfil do topo do layout principal administrativa (`layouts/main.php`) clicável para direcionar ao perfil administrativo, e simplificada a lógica de renderização do avatar no layout do portal do colaborador.
  - **Compressão Client-Side (Canvas):** Implementado redimensionamento e compressão automática de imagem via JavaScript (HTML5 Canvas) antes do envio do formulário tanto na tela do colaborador quanto na tela do administrador. Isso garante que a foto do rosto seja enviada com cerca de 30KB, contornando limitações de `upload_max_filesize` e `post_max_size` (limite do `php.ini` de 2MB no XAMPP).
  - **Correção dos Warnings do Admin Profile:** Corrigido o erro de variável indefinida `$userFull` adicionando fallback seguro para a variável de sessão `$user` em caso de dados nulos do banco e corrigindo o envio da variável no método `profile()` do `AdminController.php`.
  - **Processamento de Campos no AdminController:** Ajustada a ação `updateProfile()` do `AdminController.php` para aceitar `foto_rosto` (correspondendo ao input do formulário) e limpar caracteres especiais do número do celular, propagando as alterações na tabela `colaboradores` se houver vínculo do usuário como colaborador de campo.

---

## 📅 Sessão 9: Formatação e Máscaras de Entrada nos Formulários de Perfil
* **Ações:**
  - **Formatação de Exibição no Servidor (PHP)**: Adicionada lógica de formatação no load inicial nas views do portal do colaborador e do administrador. CPF, RG e Celular agora são exibidos formatados no padrão brasileiro (`000.000.000-00`, `00.000.000-0` ou `0.000.000-0`, e `(00) 00000-0000`) mesmo que salvos como texto cru no banco de dados.
  - **Máscara Dinâmica Client-Side (JavaScript)**: Implementado event listener no input `#celular` em ambas as views. Conforme o usuário digita ou apaga caracteres, o valor é formatado automaticamente em tempo real para a máscara telefônica nacional `(XX) XXXXX-XXXX`, impedindo inserção de letras ou caracteres inadequados.
  - **Compatibilidade do Backend**: O backend continua a receber e limpar os caracteres especiais via regex antes da persistência no banco de dados, garantindo integridade referencial.
