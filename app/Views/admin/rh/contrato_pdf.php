<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato_Campanha_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $colaborador['nome_completo']) ?>.pdf</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #525659;
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            color: #111827;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .no-print-bar {
            width: 100%;
            max-width: 800px;
            background: #1e293b;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .no-print-bar .title {
            color: #38bdf8;
            font-weight: bold;
            font-size: 14px;
        }

        .btn-print {
            background-color: #10b981;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back {
            background-color: #475569;
            color: #ffffff;
            border: none;
            padding: 8px 14px;
            font-size: 13px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .pdf-page {
            background: #ffffff;
            width: 100%;
            max-width: 800px;
            min-height: 1050px;
            padding: 50px 60px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            border-radius: 4px;
            line-height: 1.6;
            font-size: 13px;
        }

        .header-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 25px;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            line-height: 1.4;
        }

        .section-title {
            font-weight: bold;
            margin-top: 18px;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-size: 12px;
            color: #0f172a;
        }

        p {
            text-align: justify;
            margin-bottom: 12px;
            text-indent: 25px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 15px 0;
            font-size: 12px;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-box td {
            padding: 4px 0;
        }

        .signatures {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            gap: 40px;
        }

        .signature-block {
            flex: 1;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 6px;
        }

        .footer-note {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print-bar { display: none !important; }
            .pdf-page {
                box-shadow: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
            @page {
                size: A4;
                margin: 20mm 15mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div class="title">📄 Visualização Oficial do Contrato (PDF)</div>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()" class="btn-print">
                🖨️ Imprimir / Salvar em PDF
            </button>
            <?php if (isset($_GET['public'])): ?>
                <a href="<?= $this->baseUrl('colaborador/contrato?token=' . $colaborador['token_cadastro']) ?>" class="btn-back">⬅ Voltar</a>
            <?php else: ?>
                <a href="<?= $this->baseUrl('admin/rh') ?>" class="btn-back">⬅ Voltar ao RH</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="pdf-page">
        <div class="header-title">
            INSTRUMENTO PARTICULAR DE CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE CAMPANHA ELEITORAL &ndash; ELEIÇÕES 2026
        </div>

        <p>
            Pelo presente instrumento particular, de um lado a <strong>CAMPANHA ELEITORAL 2026</strong>, doravante denominada simplesmente <strong>CONTRATANTE</strong>, e de outro lado:
        </p>

        <div class="info-box">
            <table>
                <tr>
                    <td style="width:140px;"><strong>NOME COMPLETO:</strong></td>
                    <td><?= htmlspecialchars($colaborador['nome_completo']) ?></td>
                </tr>
                <tr>
                    <td><strong>CPF:</strong></td>
                    <td><?= htmlspecialchars($colaborador['cpf']) ?></td>
                </tr>
                <tr>
                    <td><strong>RG / ÓRGÃO:</strong></td>
                    <td><?= htmlspecialchars($colaborador['rg']) ?> <?= htmlspecialchars($colaborador['rg_orgao_emissor']) ?></td>
                </tr>
                <tr>
                    <td><strong>ENDEREÇO:</strong></td>
                    <td><?= htmlspecialchars($colaborador['endereco_completo'] ?? 'Não informado') ?>, CEP <?= htmlspecialchars($colaborador['cep'] ?? '') ?></td>
                </tr>
                <tr>
                    <td><strong>WHATSAPP:</strong></td>
                    <td><?= htmlspecialchars($colaborador['celular_whatsapp']) ?></td>
                </tr>
            </table>
        </div>

        <p>
            Doravante denominado(a) simplesmente <strong>CONTRATADO(A)</strong>, têm entre si justo e avençado o seguinte Contrato de Prestação de Serviços por Prazo Determinado, que se regerá pelas cláusulas a seguir dispostas:
        </p>

        <div class="section-title">CLÁUSULA PRIMEIRA &ndash; DO OBJETO</div>
        <p>
            O presente contrato tem por objeto a prestação de serviços de apoio temporário à campanha eleitoral do(a) candidato(a), exercendo o(a) CONTRATADO(A) a função de <strong><?= htmlspecialchars($contrato['funcao_campanha']) ?></strong>, desenvolvendo atividades de mobilização, apoio logístico e divulgação eleitoral em conformidade com as diretrizes da coordenação de campanha.
        </p>

        <div class="section-title">CLÁUSULA SEGUNDA &ndash; DO VALOR E DA FORMA DE PAGAMENTO</div>
        <p>
            Pela prestação dos serviços acordados na Cláusula Primeira, o(a) CONTRATANTE pagará ao(à) CONTRATADO(A) a quantia total bruta ajustada de <strong>R$ <?= number_format($contrato['valor_contratado'], 2, ',', '.') ?></strong> (<?= htmlspecialchars($contrato['valor_extenso'] ?? 'Valor Ajustado em Contrato') ?>), que será quitada mediante a modalidade de <strong><?= htmlspecialchars($contrato['forma_pagamento']) ?></strong>.
        </p>

        <div class="section-title">CLÁUSULA TERCEIRA &ndash; DA VIGÊNCIA</div>
        <p>
            O presente instrumento terá vigência determinada a partir de <strong><?= date('d/m/Y', strtotime($contrato['data_inicio'])) ?></strong> até <strong><?= date('d/m/Y', strtotime($contrato['data_fim'])) ?></strong>, encerrando-se pleno jure ao término do período fixado ou com o encerramento do período eleitoral.
        </p>

        <div class="section-title">CLÁUSULA QUARTA &ndash; DA NATUREZA JURÍDICA E CONFORMIDADE ELEITORAL</div>
        <p>
            As partes declaram expressamente que a presente contratação é realizada nos termos do art. 100 da Lei nº 9.504/1997 (Lei das Eleições), não gerando vínculo empregatício de qualquer natureza com a campanha ou com o candidato, destinando-se a prestação de serviços exclusivamente à prestação de contas perante a Justiça Eleitoral.
        </p>

        <div class="section-title">CLÁUSULA QUINTA &ndash; DA PROTEÇÃO DE DADOS (LGPD)</div>
        <p>
            O(A) CONTRATADO(A) autoriza o tratamento de seus dados pessoais para fins exclusivos de registros administrativos, contábeis e de prestação de contas de campanha eleitoral junto ao Tribunal Superior Eleitoral (TSE), nos termos da Lei nº 13.709/2018 (LGPD).
        </p>

        <div style="margin-top: 40px; text-align: center;">
            <p style="text-align: center; text-indent: 0;">E, por estarem assim justos e contratados, firmam o presente instrumento.</p>
            <p style="text-align: center; text-indent: 0; margin-top: 10px;"><strong>Data da Emissão:</strong> <?= date('d/m/Y', strtotime($contrato['created_at'] ?? date('Y-m-d'))) ?></p>
        </div>

        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line"></div>
                <strong>CONTRATANTE</strong><br>
                <span>Coordenação Financeira / Eleitoral</span>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <strong><?= htmlspecialchars($colaborador['nome_completo']) ?></strong><br>
                <span>CONTRATADO(A) - CPF: <?= htmlspecialchars($colaborador['cpf']) ?></span>
            </div>
        </div>

        <div class="footer-note">
            Documento gerado eletronicamente pelo Sistema de Gestão Eleitoral (SGE) &bull; Código de Autenticidade: <?= strtoupper(substr(md5($colaborador['token_cadastro'] . ($contrato['id'] ?? 0)), 0, 16)) ?>
        </div>
    </div>

</body>
</html>
