# 📚 Documentação do Sistema de Gestão Eleitoral (SGE)

Bem-vindo ao centro de documentação e histórico de desenvolvimento do **SGE**. Para facilitar a auditoria, rastreabilidade e acompanhamento contínuo de cada módulo, a documentação está organizada por **fases de desenvolvimento e assuntos específicos**.

---

## 📁 Estrutura de Fases e Módulos

### 🛠️ [Fase 1: Infraestrutura, Segurança & Deploy](file:///c:/xampp/htdocs/sge/docs/fase_1_infra_e_deploy/historico_infra_e_deploy.md)
* **Histórico:** [historico_infra_e_deploy.md](file:///c:/xampp/htdocs/sge/docs/fase_1_infra_e_deploy/historico_infra_e_deploy.md)
* **Guia de Instalação VPS:** [GuiaInstDigOcean.md](file:///c:/xampp/htdocs/sge/docs/fase_1_infra_e_deploy/GuiaInstDigOcean.md)
* **Assuntos:** Configuração de servidores (Apache/Nginx), SSL Let's Encrypt, HTTPS forçado, cabeçalhos HSTS, segurança contra vazamentos e cadastramento de administradores.

---

### 🔑 [Fase 2: Gestão de Usuários, Autenticação & RBAC](file:///c:/xampp/htdocs/sge/docs/fase_2_usuarios_e_rbac/historico_usuarios_e_rbac.md)
* **Histórico:** [historico_usuarios_e_rbac.md](file:///c:/xampp/htdocs/sge/docs/fase_2_usuarios_e_rbac/historico_usuarios_e_rbac.md)
* **Assuntos:** Controle de acesso baseado em papéis (ADMINISTRADOR, FINANCEIRO, RH, COLABORADOR), autenticação de sessão e permissões por módulo.

---

### 💰 [Fase 3: Módulo Financeiro, Contratos & Conciliação Bancária](file:///c:/xampp/htdocs/sge/docs/fase_3_financeiro/historico_modulo_financeiro.md)
* **Histórico:** [historico_modulo_financeiro.md](file:///c:/xampp/htdocs/sge/docs/fase_3_financeiro/historico_modulo_financeiro.md)
* **Assuntos:** Fila de aprovações de gastos, despesas cadastradas, cadastro de empresas fornecedoras, gestão de contratos por tempo determinado (PDF), carga inicial e conciliação de saldos bancários com anexo de extratos em PDF.

---

### 👥 [Fase 4: Gestão de RH, Contratação & Colaboradores](file:///c:/xampp/htdocs/sge/docs/fase_4_rh_e_colaboradores/historico_desenvolvimento_fase_4.md)
* **Histórico:** [historico_desenvolvimento_fase_4.md](file:///c:/xampp/htdocs/sge/docs/fase_4_rh_e_colaboradores/historico_desenvolvimento_fase_4.md)
* **Guias & Manuais:**
  - [guia_deploy_fase_4.md](file:///c:/xampp/htdocs/sge/docs/fase_4_rh_e_colaboradores/guia_deploy_fase_4.md)
  - [manual_cadastro_colaborador.md](file:///c:/xampp/htdocs/sge/docs/fase_4_rh_e_colaboradores/manual_cadastro_colaborador.md)
  - [teste_de_mesa_fluxo_rh.md](file:///c:/xampp/htdocs/sge/docs/fase_4_rh_e_colaboradores/teste_de_mesa_fluxo_rh.md)
* **Assuntos:** Admissão e cadastro de colaboradores, fotos de documento, fluxo estrito de contratação em 4 etapas (Aval, Emissão, Assinatura e Conferência/Usuário), envio automático via WhatsApp (Z-API).

---

### 🚩 [Fase 5: Militância & Portal do Colaborador](file:///c:/xampp/htdocs/sge/docs/fase_5_militancia_e_portal/historico_militancia_e_portal.md)
* **Histórico:** [historico_militancia_e_portal.md](file:///c:/xampp/htdocs/sge/docs/fase_5_militancia_e_portal/historico_militancia_e_portal.md)
* **Assuntos:** Portal mobile do colaborador, prestação de contas de atividades de militância com geolocalização GPS, modal orientativo multi-plataforma, acumulador de fotos, despesas de viagens/combustível/outros gastos (OCR) e reenvio de gastos reprovados.

---

### ⚖️ [Fase 6: Prestação de Contas SPCE & TSE](file:///c:/xampp/htdocs/sge/docs/fase_6_prestacao_de_contas_tse/historico_spce_e_tse.md)
* **Histórico:** [historico_spce_e_tse.md](file:///c:/xampp/htdocs/sge/docs/fase_6_prestacao_de_contas_tse/historico_spce_e_tse.md)
* **Assuntos:** Conformidade com a Resolução TSE 23.607/2019, controle impositivo do prazo de 72 horas para doações/receitas, motor de pré-auditoria de inconsistências fiscais/eleitorais, exportações CSV para o SPCE/Excel e Dossiê Consolidado de Prestação de Contas (PDF/Impresso).

---

> 🔒 *Documentação mantida conforme diretrizes do `AGENTS.md`.*
