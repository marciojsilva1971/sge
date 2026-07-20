# ⚖️ Análise Técnica: Assinatura Eletrônica no SGE

Este documento apresenta a fundamentação jurídica brasileira sobre assinaturas eletrônicas e compara as abordagens mais viáveis para integração com o Sistema de Gestão Eleitoral (SGE), focando em conformidade legal, viabilidade operacional e custos.

---

## 1. Fundamentação Jurídica no Brasil

A validade jurídica das assinaturas eletrônicas no país é regulada por duas normas principais:
*   **Medida Provisória nº 2.200-2/2001:** Regulamenta o uso de documentos digitais e estabelece a presunção de validade jurídica das assinaturas eletrônicas, desde que as partes envolvidas concordem com o método escolhido.
*   **Lei nº 14.063/2020:** Classifica as assinaturas eletrônicas em três níveis, definindo onde e como cada um deve ser aplicado:

### Classificação das Assinaturas (Lei 14.063/2020)

1.  **Assinatura Eletrônica Simples:**
    *   **Como funciona:** Associa dados básicos do assinante (como e-mail, login de acesso ou CPF informado manualmente).
    *   **Uso indicado:** Agendamentos de reuniões, aceites internos e operações de baixíssimo risco.
2.  **Assinatura Eletrônica Avançada:**
    *   **Como funciona:** Utiliza meios técnicos para garantir que a assinatura está sob controle exclusivo do assinante e vinculada de forma inequívoca ao documento. Exemplos incluem tokens SMS/WhatsApp (OTP), geolocalização, gravação de IP, biometria facial, selfie ou desenho digitalizado no Canvas.
    *   **Uso indicado:** Contratos de trabalho temporário (RH de campanha), prestação de serviços civis comuns e relatórios de despesas de viagem de campo. **É o modelo mais recomendado para o SGE.**
3.  **Assinatura Eletrônica Qualificada:**
    *   **Como funciona:** Exige obrigatoriamente um certificado digital no padrão **ICP-Brasil** (e-CPF ou e-CNPJ, em cartão, token ou nuvem).
    *   **Uso indicado:** Petições judiciais para advogados, fechamentos contábeis formais, escrituras públicas e atos de alta criticidade judicial. **Inviável de exigir para militantes ou cabos eleitorais temporários devido ao alto custo de emissão individual do e-CPF.**

---

## 2. Comparativo de Abordagens para o SGE

Abaixo, as três principais rotas tecnológicas para implementar a assinatura no SGE:

### Abordagem A: Sistema de Assinatura Avançada Próprio (Interno)
O próprio SGE gera o fluxo de assinatura e armazena os metadados de auditoria.

*   **Fluxo de Operação:**
    1. O SGE gera o contrato e calcula o hash SHA-256 do arquivo.
    2. O militante recebe um link criptografado por e-mail ou WhatsApp.
    3. Ao clicar, visualiza o contrato e digita um código (Token OTP) enviado para o seu celular.
    4. Desenha a assinatura na tela (Canvas) ou tira uma foto (selfie).
    5. O SGE armazena no banco de dados: IP, User Agent, Hash SHA-256 do documento, Token OTP validado, Geolocation e data/hora exatos.
    6. O sistema emite um selo de autenticidade no rodapé com um QR Code que aponta para uma rota pública de verificação do SGE.
*   **Vantagens:**
    *   **Custo Zero:** Sem tarifas recorrentes ou cobrança por documento assinado.
    *   **Independência:** Total controle sobre o banco de dados e a interface.
    *   **Centralização:** Todo o processo ocorre em ambiente próprio controlado.
*   **Desvantagens:**
    *   Necessidade de desenvolver o fluxo de tela, lógica de hashes e envio de tokens (SMS/WhatsApp).

---

### Abordagem B: Integração com APIs Específicas de Assinatura
Utilização de plataformas de mercado especializadas (ZapSign, D4Sign, ClickSign) por meio de requisições REST API do PHP.

*   **Fluxo de Operação:**
    1. O SGE gera o PDF do contrato e envia à API do provedor via cURL.
    2. A API retorna um link exclusivo de assinatura.
    3. O SGE envia esse link para o colaborador ou o exibe na tela dele.
    4. O colaborador assina na interface homologada do provedor externo.
    5. O provedor envia um Webhook de volta ao SGE com o status "signed" e o PDF final assinado.
*   **Principais Plataformas no Brasil:**
    *   **ZapSign:** Focada na simplicidade e no fluxo via WhatsApp. Excelente API, documentação em português e facilidade de coleta.
    *   **D4Sign:** Uma das mais maduras no mercado nacional. Possibilita validações extras integradas como Pix (verificação de identidade em banco), selfie com documento e SMS.
    *   **ClickSign:** Altamente estável, focada em segurança corporativa e com API robusta.
*   **Vantagens:**
    *   **Agilidade de Desenvolvimento:** Lógica de assinatura e coleta terceirizada.
    *   **Validade Jurídica Embalada:** A trilha de auditoria é fornecida por uma autoridade independente com peso no mercado.
*   **Desvantagens:**
    *   **Custo Transacional:** Cobrança recorrente (mensalidade + custo fixo por documento assinado). Em campanhas com centenas de militantes de campo temporários, o custo pode escalar rapidamente.

---

### Abordagem C: Integração com o Gov.br (Assinatura Avançada do Governo)
Aproveitar a infraestrutura pública de assinaturas da conta Gov.br (categorias Prata e Ouro).

*   **Fluxo de Operação:**
    1. O SGE envia o PDF do contrato para a API de assinatura eletrônica do Gov.br.
    2. O cidadão faz login na sua conta Gov.br e autoriza a assinatura por meio de biometria facial ou OTP do aplicativo do governo.
    3. O Gov.br devolve o documento assinado digitalmente ao SGE.
*   **Vantagens:**
    *   **Gratuito:** Infraestrutura pública federal sem custos transacionais.
    *   **Extrema Validade Jurídica:** Aceitação oficial em todos os órgãos da administração pública.
*   **Desvantagens:**
    *   **Complexidade de Login:** Exige que todos os militantes (inclusive com pouca instrução digital) possuam conta Gov.br nível Prata ou Ouro ativa e lembrem suas credenciais.

---

## 🚀 Recomendação Estratégica para o SGE

Para as **Eleições 2026**, visando o melhor equilíbrio entre **custo de campanha**, **facilidade de uso no celular pelos militantes** e **segurança contábil/fiscal**, a estratégia recomendada divide-se em:

1.  **Solução Principal: Módulo Interno de Assinatura Eletrônica Avançada (Abordagem A)**
    *   A maior parte das contratações de militantes de campo (cabos eleitorais) e termos de viagens pode ser assinada eletronicamente dentro do próprio SGE, usando autenticação simples (login e senha de acesso) combinada com validação por hash criptográfico (SHA-256) e logs auditáveis detalhados (IP, data/hora, geolocalização). Isso elimina os custos transacionais das APIs de mercado.
2.  **Solução Secundária: Integração Opcional com D4Sign ou ZapSign (Abordagem B)**
    *   Manter uma classe de integração de API no backend do SGE para contratos de alto valor ou parcerias estratégicas (coordenadores gerais, fornecedores de grande porte), onde uma assinatura certificada por plataforma externa independente traga maior segurança em caso de litígio judicial de grande porte.
