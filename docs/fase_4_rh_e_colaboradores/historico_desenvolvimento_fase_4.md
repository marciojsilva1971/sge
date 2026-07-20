# Histórico da Linha de Desenvolvimento - Fase 4: Módulo de RH e Colaboradores

Este documento mantém o registro permanente de todas as conversas, decisões técnicas, alinhamentos e alterações implementadas na **Fase 4** do Sistema de Gestão Eleitoral (SGE), conforme estabelecido no `AGENTS.md`.

---

## 📅 Sessão 1: Modelagem e Arquitetura Inicial
* **Alinhamento:** Definição do escopo do módulo de RH para equipe de campanha.

---

## 📅 Sessão 2: Core Backend, Controllers e Views Administrativas
* **Implementações:** Model, Controller e Views para RH.

---

## 📅 Sessão 3: Documento de Identificação e Thumbnail Dynamic Preview
* **Implementações:** Adicionado campo de upload de foto de identificação e visualizador de thumbnail.

---

## 📅 Sessão 4: Atualização de Diretrizes (`AGENTS.md`)
* **Ação Realizada:** Registro contínuo de histórico obrigatório.

---

## 📅 Sessão 5: Fluxo de Aval, Emissão, Assinatura e Conferência de Contrato
* **Implementações:** Fluxo em 4 etapas estritas com liberação de conta de usuário SGE no estágio final.

---

## 📅 Sessão 6: Elaboração do Teste de Mesa (Desk Check)
* **Ação Realizada:** Matriz de cenários registrada em `docs/fase_4_rh_e_colaboradores/teste_de_mesa_fluxo_rh.md`.

---

## 📅 Sessão 7: Lista Suspensa de Funções, Máscara de Moeda e Ajustes de UI
* **Ação Realizada:** Atualização de views, botões de ação e lógica em `RhController.php`.

---

## 📅 Sessão 8: Correção de Compatibilidade de Herança em Controller (`parseBrlCurrency`)
* **Ação Realizada:** Alterado o modificador de acesso de `private` para `protected` em `RhController.php`.

---

## 📅 Sessão 9: Disparo Direto de Mensagens via API da Z-API (WhatsApp)
* **Ação Realizada:** Envio automático no aval e botão de disparo manual direto via Z-API.

---

## 📅 Sessão 10: Rota Segura de Visualização de Documentos (`GET /admin/rh/documento`)
* **Ação Realizada:** Criado o endpoint de streaming seguro para fotos de identificação e contratos assinados.

---

## 📅 Sessão 11: Resolução de Carregamento de CSS/Estilos em Dispositivos Móveis e Acesso via IP (`192.168.x.x`)
* **Ação Realizada:** Atualizada a resolução de URLs com o método dinâmico `Controller::baseUrl()`.

---

## 📅 Sessão 12: Visualização de Contrato em PDF na Gestão de Colaboradores
* **Ação Realizada:** Implementada a view oficial de contrato em PDF (`contrato_pdf.php`) e rotas dedicadas.

---

## 📅 Sessão 13: Ajuste de Resolução de Domínio Web (`APP_URL`) e Links Duplos no WhatsApp (PDF + Envio)
* **Ação Realizada:** Priorização do `APP_URL` de produção e mensagens no WhatsApp com download em PDF e portal.

---

## 📅 Sessão 14: Correção de Redirecionamento Indevido para Login no Portal Público do Colaborador
* **Ação Realizada:** Implementada a rota pública tokenizada `GET /colaborador/documento` e validação CSRF amigável.

---

## 📅 Sessão 15: Exibição de Alertas e Confirmação de Sucesso no Envio do Contrato Assinado
* **Ação Realizada:** Adicionado bloco de alertas flash em `auth.php` e banner visual de sucesso na tela de contrato do colaborador.

---

## 📅 Sessão 16: Conferência do Contrato Assinado, Homologação e Geração/Envio de Senha Provisória via WhatsApp
* **Ação Realizada:** Implementada a geração de senha provisória e notificação de credenciais via Z-API.

---

## 📅 Sessão 17: Ajuste na Tabela da Gestão de RH (`/admin/rh`) para Acesso Direto à Homologação
* **Ação Realizada:** Atualizada a coluna Ações Obrigatórias em `app/Views/admin/rh/index.php`.

---

## 📅 Sessão 18: Visualização de Todos os Documentos e Contratos no Modal de Homologação
* **Ação Realizada:** Incluídos todos os botões de checagem de documentos no modal.

---

## 📅 Sessão 19: Correção do Erro 404 após Autenticação de Colaboradores de Campo
* **Ação Realizada:** Mapeadas todas as rotas do `PortalController` em `public/index.php`.

---

## 📅 Sessão 20: Reordenação do Menu de Navegação do Portal para o Topo da Tela
* **Ação Realizada:** Atualizado o layout `app/Views/layouts/portal.php` movendo a barra de navegação para o topo.

---

## 📅 Sessão 21: Correção do Erro 404 no Envio de Cupons Fiscais (`/portal/viagem/receipt`)
* **Ação Realizada:** Adicionadas as rotas `receipt` e `submit` no `public/index.php`.

---

## 📅 Sessão 22: Consulta Cadastral do Candidato a Colaborador de Campanha
* **Ação Realizada:** Criado o serviço `App\Services\TseService.php` para checagem de CPF, maioridade legal e Resolução TSE 23.607/2019.

---

## 📅 Sessão 23: Cadastro de Teste de Mesa - Colaborador 1 (CPF 141.019.908-83)
* **Ação Realizada:** Inserido no banco o registro `Colaborador 1` com contrato assinado no status `AGUARDANDO_CONFERENCIA_CONTRATO`.

---

## 📅 Sessão 24: Preparação dos Artefatos de Deploy e Homologação
* **Ação Realizada:** Criado [guia_deploy_fase_4.md](file:///c:/xampp/htdocs/sge/docs/fase_4_rh_e_colaboradores/guia_deploy_fase_4.md) e [walkthrough.md](file:///C:/Users/marci/.gemini/antigravity/brain/01f1fa21-dd59-4151-9384-c3be62a3c6e3/walkthrough.md).

---

## 📅 Sessão 25: Publicação Oficial no GitHub (`git push`) e Disparo de Deploy CI/CD
* **Ação Realizada:** Código publicado com sucesso para `https://github.com/marciojsilva1971/sge.git` sob a tag de commit `primeira versao rh`.

---

## 📅 Sessão 26: Redefinição de Senha e Ajuste de Tolerância de Sessão VPS
* **Ações Implementadas:** Ajustados `User.php` (LEFT JOIN) e `Session.php` (Proxy Headers), com ferramenta `scratch/reset_admin_pass.php` para VPS.

---

## 📅 Sessão 27: Badges Visuais e Destaque de Arquivos Enviados no Painel de RH
* **Ações Implementadas:** Atualizada a tabela administrativa e o modal de conferência com os badges verdes `✔ RG/CNH Anexado` e `✅ Contrato Assinado Enviado`.

---

## 📅 Sessão 28: Exibição Explícita da Opção "Gestão de RH" no Sidebar Lateral (`main.php`)
* **Situação:** O usuário enviou imagem do menu lateral onde o item "Gestão de RH" não estava visível.
* **Solução:** Atualizada a condição em `app/Views/layouts/main.php` para garantir que o menu `📇 Gestão de RH` seja visível incondicionalmente para perfil `ADMINISTRADOR` ou usuários com a permissão `invite_user`.
* **Código Publicado:** `commit 7f9eeb0`.
