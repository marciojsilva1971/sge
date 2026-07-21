# Histórico da Linha de Desenvolvimento - Fase 2: Usuários, Autenticação & RBAC

Este documento registra todas as alterações na gestão de usuários, autenticação, perfis de acesso (Role-Based Access Control) e segurança de sessão.

---

## 📅 Sessão 1: Arquitetura de Sessão e Autenticação Criptografada
* **Ações:**
  - Implementação da classe `Session` com gerenciador seguro de sessões PHP (`session_start()`, `session_regenerate_id()`).
  - Proteção anti-CSRF com geração de tokens únicos por requisição `POST` (`Session::csrfToken()`).
  - Hash de senhas utilizando o algoritmo nativo `PASSWORD_BCRYPT` (`password_hash`, `password_verify`).

---

## 📅 Sessão 2: Matriz de Perfis e Controle de Acesso (RBAC)
* **Ações:**
  - Criação dos perfis de acesso na tabela `usuarios`:
    * `ADMINISTRADOR`: Acesso total ao sistema, configurações e relatórios.
    * `FINANCEIRO`: Gestão de despesas, receitas, conciliação e exportação SPCE.
    * `RH`: Gestão de colaboradores, emissão e conferência de contratos.
    * `COLABORADOR`: Acesso exclusivo ao Portal do Colaborador (atividades de militância e envio de reembolsos).
  - Implementação de middleware de verificação de papel no `Controller.php` (`requireRole()`).
