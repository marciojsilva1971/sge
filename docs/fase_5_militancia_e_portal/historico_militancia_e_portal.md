# Histórico da Linha de Desenvolvimento - Fase 5: Militância & Portal do Colaborador

Este documento mantém o registro de todas as implementações referentes ao **Portal do Colaborador**, acompanhamento de atividades de campo (militância), telemetria com geolocalização (GPS) e envio de despesas de viagens/combustível com OCR.

---

## 📅 Sessão 1: Portal Mobile do Colaborador
* **Ações:**
  - Interface responsiva com design mobile-first para acesso por smartphones dos cabos eleitorais e militantes de campo.
  - Exibição de resumo de atividades, saldo de reembolsos e atalhos para envio de comprovação.

---

## 📅 Sessão 2: Comprovação de Militância com Geolocalização (GPS)
* **Ações:**
  - Captura automatizada de coordenadas de Latitude e Longitude via HTML5 Geolocation API.
  - **Modal Orientativo Multi-plataforma:** Modal interativo com abas paso a passo para ativação do GPS no Android (Chrome), iOS (Safari) e Computadores (Edge/Chrome).
  - **Checkbox Sem GPS:** Opção para envio de atividade sem coordenadas nos casos em que a localização física do dispositivo não puder ser obtida.
  - **Galeria e Múltiplos Uploads:** Acumulador e seletor de fotos da atividade de campo com preview dinâmico antes do envio.

---

## 📅 Sessão 3: Envio de Despesas do Colaborador (Viagens, Combustível & Outros Gastos com OCR)
* **Ações:**
  - Formulário para lançamento de despesas de viagens, hospedagem, alimentação e combustível.
  - **Leitura Inteligente (OCR Tesseract):** Leitura de cupons fiscais e notas para preenchimento automático do valor, data e CNPJ do fornecedor.
  - **Fluxo de Reprovação e Reenvio:** Caso a despesa seja reprovada pelo financeiro com uma justificativa, o colaborador recebe o alerta no portal e pode corrigir os dados ou anexar um novo comprovante.
