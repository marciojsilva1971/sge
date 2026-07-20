# ⚖️ Legislação Eleitoral - Eleições 2026

Este diretório contém os resumos técnicos da legislação eleitoral brasileira aplicada às **Eleições 2026**, com foco analítico em **gastos de campanha**, **Fundo Especial de Financiamento de Campanha (FEFC - Fundo Eleitoral)**, **Fundo Partidário** e regras de conformidade e prestação de contas perante o Tribunal Superior Eleitoral (TSE).

---

## 📂 Diretório de Documentos

| Legislação | Arquivo de Resumo | Foco Principal |
| :--- | :--- | :--- |
| **Lei nº 9.504/1997 (Lei das Eleições)** | [lei_9504_1997.md](file:///g:/Meu%20Drive/____2ERC/Outros/1_Projetos/cds/legislacao/lei_9504_1997.md) | Origem do FEFC (Art. 16-C), despesas permitidas (Art. 26) e cassação de mandato (Art. 30-A). |
| **Resolução TSE nº 23.607/2019** | [resolucao_tse_23607_2019.md](file:///g:/Meu%20Drive/____2ERC/Outros/1_Projetos/cds/legislacao/resolucao_tse_23607_2019.md) | Normas procedimentais, comprovantes aceitos, limites de combustível e regras de contratação de militância. |
| **Lei nº 9.096/1995 (Lei dos Partidos)** | [lei_9096_1995.md](file:///g:/Meu%20Drive/____2ERC/Outros/1_Projetos/cds/legislacao/lei_9096_1995.md) | Fundo Partidário em campanhas e tratamento diferenciado para devolução de sobras de caixa. |
| **Emenda Constitucional nº 117/2022** | [emenda_constitucional_117_2022.md](file:///g:/Meu%20Drive/____2ERC/Outros/1_Projetos/cds/legislacao/emenda_constitucional_117_2022.md) | Cotas constitucionais obrigatórias de gênero (mínimo 30%) e proporcionalidade racial para negros. |
| **Quitação Eleitoral & Contas de Campanha** | [prestacao_contas_quitacao_eleitoral.md](file:///g:/Meu%20Drive/____2ERC/Outros/1_Projetos/cds/legislacao/prestacao_contas_quitacao_eleitoral.md) | Consequências jurídicas: contas desaprovadas vs. não prestadas e o impacto na elegibilidade do candidato. |
| **Requisitos Técnicos do SPCE** | [requisitos_prestacao_contas_spce.md](file:///g:/Meu%20Drive/____2ERC/Outros/1_Projetos/cds/legislacao/requisitos_prestacao_contas_spce.md) | Lista de peças obrigatórias (Art. 53), prestação parcial, conciliação e assinaturas do contador/advogado. |

---

## 🚫 O que NÃO pode ser feito com recursos do FEFC (Fundo Eleitoral)

* **Saques em Dinheiro (Cash Withdrawals):** Todo pagamento deve ser feito por transferência bancária identificada ou Pix. É vedado sacar dinheiro em espécie da conta do FEFC para realizar pagamentos manuais.
* **Sobras de Campanha no Caixa do Partido:** Qualquer saldo não utilizado oriundo do FEFC deve ser devolvido ao **Tesouro Nacional**. O partido não pode reter essa verba para si após o encerramento do pleito.
* **Despesas sem Documentação Fiscal Idônea:** Não são aceitos recibos simples ou declarações informais para compras de mercadorias. A comprovação exige Nota Fiscal Eletrônica (NF-e) com o CNPJ da campanha.
* **Combustível sem Veículo Vinculado:** Abastecimentos pagos com verba de campanha sem que o veículo correspondente esteja formalmente alugado ou cedido à campanha são considerados desvios e geram glosa.
* **Financiamento de Candidatos de Outras Coligações:** Os recursos do FEFC destinam-se exclusivamente às candidaturas da própria legenda ou coligação.

---

## 💡 Boas Práticas integradas no SGE (Sistema de Gestão Eleitoral)

1. **Validação Automatizada de NF-e:** O sistema deve verificar eletronicamente a validade das notas fiscais inseridas em relação ao CNPJ da campanha e o CNPJ do fornecedor junto à Receita Federal.
2. **Controle Integrado de Deslocamentos:** Cruzar os comprovantes de combustíveis lançados com as placas e contratos de veículos cadastrados no sistema, gerando alertas se houver inconsistência.
3. **Mural de Alerta de Cotas (Gênero/Raça):** Acompanhar em tempo real através do painel financeiro se as transferências de recursos públicos do partido estão respeitando a proporção mínima exigida pela Emenda Constitucional 117/2022.
