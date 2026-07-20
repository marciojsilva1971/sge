# 📊 Teste de Mesa - Fluxo de RH, Contratos e Permissões (SGE)

O **Teste de Mesa** (Desk Check) é a simulação passo a passo do fluxo de dados e estados do sistema para validação antes da implantação final.

---

## 🎭 Atores do Teste de Mesa
* **Colaborador (Candidato):** `João Pedro Santos` (CPF: `123.456.789-00`, Nasc: `15/05/2000` - 26 anos)
* **Administrador de RH:** `Maria Coordenadora` (Usuário ID: `1`, Perfil: `ADMIN`)
* **Sistema (SGE):** Banco de Dados MySQL (`sge`), Controllers (`RhController`), Models (`Colaborador`, `Contrato`, `User`, `AuditLogger`)

---

## 📋 Cenário 1: Fluxo Principal Completo (Auto-Cadastro -> Aval -> Upload da Foto do Contrato Assinado -> Conferência -> Concessão de Perfil)

| Passo | Ator | Ação / Trigger | Dados de Entrada / Payload | Estado no Banco (`colaboradores`) | Estado no Banco (`contratos_colaboradores`) | Estado no Banco (`usuarios`) | Resultado Visual / Resposta do Sistema |
| :---: | :---: | :--- | :--- | :--- | :--- | :--- | :--- |
| **01** | Colaborador | Acessa `/colaborador/cadastro` no celular | N/A | *Inexistente* | *Inexistente* | *Inexistente* | Form exibido com thumbnail dynamic preview. |
| **02** | Colaborador | Preenche formulário, anexa foto do RG e aceita Opt-In LGPD | `nome_completo`: "João Pedro Santos"<br>`cpf`: "12345678900"<br>`rg`: "1234567"<br>`documento_foto`: `rg_joao.jpg`<br>`optin_whatsapp`: 1 | **Row Criada:**<br>`id`: 1<br>`token_cadastro`: `a8f3b...`<br>`status`: `AGUARDANDO_AVAL_CADASTRO`<br>`documento_foto_path`: `storage/documentos/rg_joao_a8f3.jpg` | *Inexistente* | *Inexistente* | Redireciona para `/colaborador/contrato?token=a8f3b...`. Exibe aviso: *"Etapa 1: Cadastro em análise pelo RH"*. Contrato bloqueado. |
| **03** | Admin | Acessa `/admin/rh` | Filtro: Todos | `status`: `AGUARDANDO_AVAL_CADASTRO` | *Inexistente* | *Inexistente* | Tabela exibe badge **`1. Aguardando Aval Cadastral`** e botão **`🔍 Conferir Doc & Dar Aval`**. Link da foto do RG ativo. |
| **04** | Admin | Clica em `🔍 Conferir Doc & Dar Aval`, verifica RG e preenche dados do contrato | `colaborador_id`: 1<br>`funcao_campanha`: "Cabo Eleitoral"<br>`valor_contratado`: "1.500,00"<br>`tipo_assinatura`: "MANUAL_UPLOAD" | `status`: `AGUARDANDO_ASSINATURA_CONTRATO` | **Row Criada:**<br>`id`: 1<br>`colaborador_id`: 1<br>`valor_contratado`: 1500.00<br>`status_contrato`: `EMITIDO` | *Inexistente* | Modal fecha. Flash message: *"Aval concedido e contrato emitido!"*. Audit log gravado. |
| **05** | Colaborador | Atualiza página `/colaborador/contrato?token=a8f3b...` | Token: `a8f3b...` | `status`: `AGUARDANDO_ASSINATURA_CONTRATO` | `status_contrato`: `EMITIDO` | *Inexistente* | Exibe os detalhes do contrato (R$ 1.500,00) e o formulário para upload da foto do contrato assinado. |
| **06** | Colaborador | Imprime/assina o contrato, tira foto no celular e envia pelo app | `contrato_assinado`: `contrato_assinado_joao.jpg` | `status`: `AGUARDANDO_CONFERENCIA_CONTRATO` | `status_contrato`: `ASSINADO`<br>`pdf_assinado_path`: `storage/contratos/contrato_1_9c2e.jpg`<br>`hash_documento`: `e3b0c442...` | *Inexistente* | Mensagem na tela do colaborador: *"Etapa 3: Contrato recebido! Aguardando conferência do admin."* |
| **07** | Admin | Acessa `/admin/rh` | Filtro: Status | `status`: `AGUARDANDO_CONFERENCIA_CONTRATO` | `status_contrato`: `ASSINADO` | *Inexistente* | Tabela atualiza badge para **`3. Conferir Contrato Assinado`** e botão para **`📑 Conferir Contrato & Conceder Perfil`**. |
| **08** | Admin | Clica no botão, visualiza o contrato enviado, seleciona o perfil (Role) e confirma | `colaborador_id`: 1<br>`role_id`: 3 (Cabo Eleitoral) | **Atualizado:**<br>`status`: `ATIVO`<br>`usuario_id`: 5 | `status_contrato`: `ASSINADO` | **Row Criada em `usuarios`:**<br>`id`: 5<br>`email`: "joao@email.com"<br>`role_id`: 3<br>`status`: `ATIVO` | Flash Message: *"Contrato conferido! Colaborador homologado e permissão de usuário concedida no SGE"*. |

---

## 📋 Cenário 2: Exceção / Regra de Segurança - Tentativa de Cadastro de Menor de 16 Anos

| Passo | Ator | Ação / Trigger | Dados de Entrada | Validação de Código (`Colaborador.php`) | Estado no Banco | Resultado do Sistema |
| :---: | :---: | :--- | :--- | :--- | :--- | :--- |
| **01** | Candidato | Preenche auto-cadastro | Data Nasc: `10/01/2012` (14 anos) | `calcularIdade('2012-01-10')` ➔ Retorna `14`. | **Nenhuma inserção efetuada.** | Lança `Exception("Idade calculada (14 anos). Proibido cadastrar menores de 16 anos").` |
| **02** | Sistema | Captura Exception | N/A | Catch em `RhController` | Rollback de transação. | Redireciona para cadastro com alerta vermelho de proibição legal (Lei 9.504/97). |

---

## 📋 Cenário 3: Validação de Segurança - Tentativa de Homologar Sem Aval do Administrador

| Passo | Ator | Ação / Trigger | Estado Atual no Banco | Validação do Backend (`RhController.php`) | Resultado do Sistema |
| :---: | :---: | :--- | :--- | :--- | :--- |
| **01** | Operador | Tenta forçar requisição POST `/admin/rh/homologar` sem dar aval prévio | `status`: `AGUARDANDO_AVAL_CADASTRO` | O administrador tenta selecionar uma Role sem o contrato ter sido emitido/assinado. | O formulário de homologação valida se o colaborador está no status correto (`AGUARDANDO_CONFERENCIA_CONTRATO`). Se não estiver, bloqueia a criação do usuário em `usuarios`. |

---

## 📌 Conclusão do Teste de Mesa
* **Conformidade de Segurança (OWASP & LGPD):** Zero exposição de acessos prematuros. O usuário em `usuarios` só recebe `status = ATIVO` e `role_id` após validação completa da foto do documento + conferência da assinatura do contrato pelo Admin.
* **Integridade Rastreável:** Trilha de auditoria (`AuditLogger`) registrada nos passos 02, 04, 06 e 08.
