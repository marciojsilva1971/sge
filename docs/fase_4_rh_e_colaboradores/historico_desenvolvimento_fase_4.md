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
