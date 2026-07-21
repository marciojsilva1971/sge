<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certidao_Regularidade_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $colaborador['nome_completo']) ?>.pdf</title>
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
            position: relative;
        }

        .header-section {
            border-bottom: 2px solid #1e293b;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-title-container {
            flex: 1;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            line-height: 1.4;
        }

        .header-subtitle {
            font-size: 11px;
            color: #475569;
            margin-top: 4px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .seal-badge {
            border: 2px solid #10b981;
            color: #10b981;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 6px;
            letter-spacing: 1px;
            background: rgba(16, 185, 129, 0.05);
            transform: rotate(-3deg);
            white-space: nowrap;
        }

        .seal-badge.danger {
            border-color: #ef4444;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.05);
        }

        .seal-badge.warning {
            border-color: #f59e0b;
            color: #f59e0b;
            background: rgba(245, 158, 11, 0.05);
        }

        .section-title {
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-size: 12px;
            color: #0f172a;
            border-left: 3px solid #0284c7;
            padding-left: 8px;
        }

        p {
            text-align: justify;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 25px 0;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }

        .info-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-align: left;
            padding: 8px 12px;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-table td {
            padding: 10px 12px;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-table td.label {
            font-weight: bold;
            width: 200px;
            color: #1e293b;
        }

        .status-box {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 12px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .status-box.success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #065f46;
        }

        .status-box.danger {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #991b1b;
        }

        .status-box.warning {
            background-color: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #92400e;
        }

        .signatures {
            margin-top: 80px;
            display: flex;
            justify-content: center;
        }

        .signature-block {
            width: 320px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 6px;
        }

        .footer-note {
            margin-top: 60px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        .badge-inline {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        .badge-inline.success {
            background: #10b981;
            color: #ffffff;
        }

        .badge-inline.danger {
            background: #ef4444;
            color: #ffffff;
        }

        .badge-inline.warning {
            background: #f59e0b;
            color: #000000;
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
        <div class="title">📄 Certidão de Regularidade Cadastral (Receita / TSE)</div>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()" class="btn-print">
                🖨️ Imprimir / Salvar em PDF
            </button>
            <a href="<?= $this->baseUrl('admin/rh') ?>" class="btn-back">⬅ Voltar ao RH</a>
        </div>
    </div>

    <div class="pdf-page">
        <div class="header-section">
            <div class="header-title-container">
                <div class="header-title">CAMPANHA ELEITORAL 2026</div>
                <div class="header-subtitle">CONTROLE INTERNO E COMPLIANCE ELEITORAL</div>
            </div>
            <div>
                <?php 
                $badgeClass = ($regularidade['cor_badge'] ?? 'success'); 
                $badgeText = ($regularidade['valido'] ?? false) ? 'Regular / Apto' : 'Irregular / Inapto';
                ?>
                <div class="seal-badge <?= htmlspecialchars($badgeClass) ?>">
                    <?= htmlspecialchars($badgeText) ?>
                </div>
            </div>
        </div>

        <p>
            Certificamos, para os fins de instrução de contas e auditoria eleitoral, em conformidade com o <strong>Art. 35 da Resolução TSE nº 23.607/2019</strong> e a <strong>Lei nº 9.504/1997 (Lei das Eleições)</strong>, que o CPF e as informações cadastrais do(a) candidato(a) a colaborador(a) de campanha listado(a) abaixo foram submetidos a verificação de regularidade e conformidade.
        </p>

        <div class="section-title">Dados do Colaborador</div>
        <table class="info-table">
            <thead>
                <tr>
                    <th colspan="2">Ficha Cadastral</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="label">Nome Completo:</td>
                    <td><?= htmlspecialchars($colaborador['nome_completo']) ?></td>
                </tr>
                <tr>
                    <td class="label">CPF:</td>
                    <td>
                        <?php 
                        $cpfRaw = preg_replace('/\D/', '', $colaborador['cpf']);
                        if (strlen($cpfRaw) === 11) {
                            echo substr($cpfRaw, 0, 3) . '.' . substr($cpfRaw, 3, 3) . '.' . substr($cpfRaw, 6, 3) . '-' . substr($cpfRaw, 9, 2);
                        } else {
                            echo htmlspecialchars($colaborador['cpf']);
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Documento de Identidade:</td>
                    <td><?= htmlspecialchars($colaborador['rg']) ?> (<?= htmlspecialchars($colaborador['rg_orgao_emissor']) ?>)</td>
                </tr>
                <tr>
                    <td class="label">Data de Nascimento / Idade:</td>
                    <td><?= date('d/m/Y', strtotime($colaborador['data_nascimento'])) ?> (<?= $colaborador['idade_calculada'] ?> anos)</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Resultado da Verificação de Regularidade</div>
        <table class="info-table">
            <thead>
                <tr>
                    <th colspan="2">Resultados Cadastrais e Legais</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="label">Situação do CPF (Receita Federal):</td>
                    <td>
                        <span class="badge-inline <?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($regularidade['status_cpf']) ?></span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Elegibilidade Eleitoral (TSE):</td>
                    <td>
                        <strong><?= htmlspecialchars($regularidade['status_tse']) ?></strong>
                    </td>
                </tr>
                <tr>
                    <td class="label">Enquadramento SPCE / Campanha:</td>
                    <td>
                        <?= htmlspecialchars($regularidade['cargo'] ?? 'Colaborador de Campanha') ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Fundamentação Normativa:</td>
                    <td>
                        <?= htmlspecialchars($regularidade['partido'] ?? 'Conforme Resolução TSE nº 23.607/2019') ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Data/Hora da Consulta:</td>
                    <td>
                        <?= date('d/m/Y H:i:s', strtotime($colaborador['tse_regularidade_data'])) ?> (Fuso de Brasília)
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Detalhamento Técnico</div>
        <div class="status-box <?= htmlspecialchars($badgeClass) ?>">
            <strong>Parecer de Compliance:</strong><br>
            <?= htmlspecialchars($regularidade['detalhes']) ?>
        </div>

        <p style="margin-top: 30px;">
            A presente certidão é emitida com base nos dados públicos disponibilizados pela Receita Federal do Brasil e pelas bases de candidaturas e contas eleitorais da Justiça Eleitoral, em conformidade com as diretrizes da LGPD (Lei Geral de Proteção de Dados) para fins exclusivos de auditoria administrativa de campanha.
        </p>

        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line"></div>
                <strong>Coordenação de Compliance e Gestão de RH</strong><br>
                <span>Campanha Eleitoral Oficial 2026</span>
            </div>
        </div>

        <div class="footer-note">
            Certidão gerada eletronicamente pelo Sistema de Gestão Eleitoral (SGE)<br>
            Código de Autenticidade para Auditoria: <strong><?= strtoupper(hash('sha256', $colaborador['cpf'] . $colaborador['tse_regularidade_data'])) ?></strong>
        </div>
    </div>

</body>
</html>
