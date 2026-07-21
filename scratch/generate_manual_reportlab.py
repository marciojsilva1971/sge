import os
from reportlab.lib.pagesizes import letter, A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm, mm
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, HRFlowable, KeepTogether
)
from reportlab.pdfgen import canvas

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_number(num_pages)
            canvas.Canvas.showPage(self)
        canvas.Canvas.save(self)

    def draw_page_number(self, page_count):
        self.saveState()
        
        # Cabeçalho Superior Fixo (Banners Escuros)
        self.setFillColor(colors.HexColor("#0f172a")) # Slate 900
        self.rect(0, 800, 595.27, 42, fill=True, stroke=False)
        
        self.setFillColor(colors.HexColor("#14b8a6")) # Teal 500
        self.setFont("Helvetica-Bold", 11)
        self.drawString(42, 824, "SGE - SISTEMA DE GESTÃO ELEITORAL")
        
        self.setFillColor(colors.HexColor("#e2e8f0")) # Slate 200
        self.setFont("Helvetica", 8.5)
        self.drawString(42, 810, "PROCEDIMENTO OPERACIONAL PADRÃO (POP) — MÓDULO DE RH & COLABORADORES")
        
        # Rodapé Fixo
        self.setStrokeColor(colors.HexColor("#cbd5e1"))
        self.setLineWidth(0.5)
        self.line(42, 45, 553.27, 45)
        
        self.setFillColor(colors.HexColor("#64748b"))
        self.setFont("Helvetica-Oblique", 8)
        self.drawString(42, 32, "Documento Interno de Homologação Operacional e Conformidade Eleitoral")
        page_text = f"Página {self._pageNumber} de {page_count}"
        self.drawRightString(553.27, 32, page_text)
        
        self.restoreState()

def build_pdf():
    pdf_path_repo = "docs/fase_4_rh_e_colaboradores/Manual_Cadastro_Colaborador_SGE_FINAL.pdf"
    pdf_path_brain = r"C:\Users\marci\.gemini\antigravity\brain\01f1fa21-dd59-4151-9384-c3be62a3c6e3\Manual_Cadastro_Colaborador_SGE_FINAL.pdf"
    
    os.makedirs(os.path.dirname(pdf_path_repo), exist_ok=True)
    
    doc = SimpleDocTemplate(
        pdf_path_repo,
        pagesize=A4,
        leftMargin=42, # 1.5 cm
        rightMargin=42,
        topMargin=60,  # 2.1 cm
        bottomMargin=55
    )

    styles = getSampleStyleSheet()
    
    # Custom Styles
    style_title = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=15,
        leading=18,
        textColor=colors.HexColor("#0f172a"),
        spaceAfter=4
    )
    
    style_subtitle = ParagraphStyle(
        'DocSubtitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9.5,
        leading=12,
        textColor=colors.HexColor("#0f766e"),
        spaceAfter=10
    )

    style_h2 = ParagraphStyle(
        'SectionH2',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=11.5,
        leading=14,
        textColor=colors.HexColor("#0f172a"),
        spaceBefore=10,
        spaceAfter=6
    )

    style_body = ParagraphStyle(
        'BodyDark',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12,
        textColor=colors.HexColor("#334155")
    )

    style_alert_title = ParagraphStyle(
        'AlertTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9,
        leading=11,
        textColor=colors.HexColor("#b91c1c"),
        spaceAfter=3
    )

    style_alert_body = ParagraphStyle(
        'AlertBody',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=11,
        textColor=colors.HexColor("#334155")
    )

    style_step_title = ParagraphStyle(
        'StepTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11,
        textColor=colors.HexColor("#0f766e")
    )

    style_step_desc = ParagraphStyle(
        'StepDesc',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=11,
        textColor=colors.HexColor("#334155")
    )

    style_section_item = ParagraphStyle(
        'SectionItem',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9,
        leading=12,
        textColor=colors.HexColor("#1e293b"),
        spaceBefore=4,
        spaceAfter=2
    )

    style_bullet = ParagraphStyle(
        'BulletText',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.2,
        leading=11.5,
        textColor=colors.HexColor("#475569"),
        leftIndent=12,
        firstLineIndent=-8,
        spaceAfter=2
    )

    elements = []

    # Título Principal
    elements.append(Paragraph("Manual de Cadastro e Contratação de Colaboradores", style_title))
    elements.append(Paragraph("Guia Operacional Passo a Passo — Auto-Cadastro, Emissão de Contrato, Assinatura e Homologação", style_subtitle))
    elements.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor("#14b8a6"), spaceAfter=10))

    # Box de Alerta Legal
    alert_content = [
        [Paragraph("IMPORTANTE — CONFORMIDADE LEGAL E ELEITORAL (Res. TSE nº 23.607/2019 e LGPD):", style_alert_title)],
        [Paragraph("Todos os colaboradores contratados para prestação de serviços de campanha devem possuir contrato formal assinado, CPF em situação regular na Receita Federal e consentimento explícito (Opt-in) para tratamento de dados pessoais e comunicação via WhatsApp.", style_alert_body)]
    ]
    
    table_alert = Table(alert_content, colWidths=[511.27])
    table_alert.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f8fafc")),
        ('BOX', (0,0), (-1,-1), 0.8, colors.HexColor("#cbd5e1")),
        ('LINELEFT', (0,0), (0,-1), 3.5, colors.HexColor("#ef4444")),
        ('PADDING', (0,0), (-1,-1), 7),
        ('BOTTOMPADDING', (0,0), (-1,-1), 6),
    ]))
    elements.append(table_alert)
    elements.append(Spacer(1, 10))

    # Seção 1: Fluxo em 4 Etapas
    elements.append(Paragraph("1. Fluxo de Contratação em 4 Etapas", style_h2))
    
    steps_data = [
        [
            Paragraph("Etapa 1: Auto-Cadastro Público", style_step_title),
            Paragraph("O colaborador preenche os dados cadastrais e anexa a foto legível do documento oficial (RG, CNH ou CIN) no portal público.", style_step_desc)
        ],
        [
            Paragraph("Etapa 2: Aval Cadastral do RH", style_step_title),
            Paragraph("O gestor analisa o cadastro no SGE, define a função, valor contratado, vigência e gera a minuta oficial em PDF.", style_step_desc)
        ],
        [
            Paragraph("Etapa 3: Assinatura & Devolução", style_step_title),
            Paragraph("O colaborador é notificado via WhatsApp (Z-API), baixa o PDF, imprime, assina e envia a foto/PDF da cópia assinada.", style_step_desc)
        ],
        [
            Paragraph("Etapa 4: Homologação & Acesso", style_step_title),
            Paragraph("O RH valida o CPF/TSE, confere os documentos no modal e libera a conta de acesso ao SGE com senha provisória via WhatsApp.", style_step_desc)
        ]
    ]

    table_steps = Table(steps_data, colWidths=[150, 361.27])
    table_steps.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#ffffff")),
        ('ROWBACKGROUNDS', (0,0), (-1,-1), [colors.HexColor("#f8fafc"), colors.HexColor("#ffffff")]),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#e2e8f0")),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor("#f1f5f9")),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('PADDING', (0,0), (-1,-1), 6),
    ]))
    elements.append(table_steps)
    elements.append(Spacer(1, 12))

    # Seção 2: Detalhamento Operacional
    elements.append(Paragraph("2. Detalhamento do Procedimento Operacional", style_h2))

    proc_data = [
        ("Etapa 1 — Auto-Cadastro Público (/colaborador/cadastro)", [
            "<b>Preenchimento de Dados Pessoais:</b> Nome Completo, CPF, RG, Órgão Emissor, Data de Nascimento e Nome da Mãe.",
            "<b>Informações Bancárias:</b> Chave PIX, Banco, Agência e Conta Corrente para repasse de verbas.",
            "<b>Comprovante Obrigatório:</b> Anexação de foto clara do documento de identidade oficial (RG/CNH/CIN).",
            "<b>Consentimento LGPD:</b> Aceite obrigatório dos termos de notificação e envio de contratos via WhatsApp."
        ]),
        ("Etapa 2 — Aval Cadastral & Emissão do Contrato (/admin/rh)", [
            "<b>Análise pelo RH:</b> Acesso do gestor à tabela do SGE e conferência inicial dos dados e documento.",
            "<b>Condições Contratuais:</b> Definição da Função (ex: Cabo Eleitoral, Panfletista, Motorista), Valor (R$) e Período.",
            "<b>Disparo Automático:</b> O sistema compila o PDF e dispara mensagem no WhatsApp do colaborador via Z-API com o link direto."
        ]),
        ("Etapa 3 — Assinatura e Devolução pelo Colaborador", [
            "<b>Notificação no Celular:</b> O candidato recebe o alerta no WhatsApp com link seguro para baixar o contrato.",
            "<b>Impressão e Assinatura:</b> O colaborador realiza a assinatura física no documento impresso.",
            "<b>Upload Tokenizado:</b> Envio da foto ou PDF do documento assinado diretamente pelo celular no portal público."
        ]),
        ("Etapa 4 — Conferência, Validação TSE & Homologação Final", [
            "<b>Conferência no Modal:</b> O RH clica em <i>Conferir Contrato & Homologar</i> no painel do SGE.",
            "<b>Badges de Verificação:</b> Confirmação dos indicadores verdes [✔ RG/CNH Anexado] e [✅ Contrato Assinado Enviado].",
            "<b>Checagem Cadastral TSE:</b> Validação automática de CPF Regular na Receita Federal e conformidade com a Res. TSE 23.607.",
            "<b>Liberação de Acesso:</b> Definição do perfil de usuário no SGE e envio automático da senha provisória via WhatsApp."
        ])
    ]

    for title, bullets in proc_data:
        elements.append(Paragraph(title, style_section_item))
        for bullet in bullets:
            elements.append(Paragraph(f"•  {bullet}", style_bullet))
        elements.append(Spacer(1, 4))

    elements.append(Spacer(1, 6))

    # Seção 3: Tabela de Status
    elements.append(KeepTogether([
        Paragraph("3. Tabela de Status do Colaborador no SGE", style_h2),
        Spacer(1, 4)
    ]))

    status_table_data = [
        [
            Paragraph("<b>Status no Sistema</b>", ParagraphStyle('TH1', parent=style_body, textColor=colors.white, fontName='Helvetica-Bold')),
            Paragraph("<b>Significado Operacional</b>", ParagraphStyle('TH2', parent=style_body, textColor=colors.white, fontName='Helvetica-Bold')),
            Paragraph("<b>Próxima Ação Requerida</b>", ParagraphStyle('TH3', parent=style_body, textColor=colors.white, fontName='Helvetica-Bold'))
        ],
        [
            Paragraph("<b>AGUARDANDO_AVAL</b>", style_body),
            Paragraph("Novo auto-cadastro público realizado pelo colaborador.", style_body),
            Paragraph("RH deve conferir documento e emitir o contrato.", style_body)
        ],
        [
            Paragraph("<b>AGUARDANDO_ASSINATURA_CONTRATO</b>", style_body),
            Paragraph("Contrato emitido e notificado via WhatsApp.", style_body),
            Paragraph("Colaborador deve assinar e enviar o comprovante.", style_body)
        ],
        [
            Paragraph("<b>AGUARDANDO_CONFERENCIA_CONTRATO</b>", style_body),
            Paragraph("Colaborador enviou a cópia do contrato assinado.", style_body),
            Paragraph("RH deve realizar a homologação no modal.", style_body)
        ],
        [
            Paragraph("<b>HOMOLOGADO</b>", style_body),
            Paragraph("Colaborador aprovado com conta liberada no SGE.", style_body),
            Paragraph("Pronto para lançamentos de despesas e atividades.", style_body)
        ],
        [
            Paragraph("<b>REJEITADO</b>", style_body),
            Paragraph("Cadastro ou contrato recusado pelo RH.", style_body),
            Paragraph("Necessita de correção de dados ou novo envio.", style_body)
        ]
    ]

    table_status = Table(status_table_data, colWidths=[160, 175, 176.27])
    table_status.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#0f172a")),
        ('TEXTCOLOR', (0,0), (-1,0), colors.white),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#cbd5e1")),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor("#e2e8f0")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.HexColor("#f8fafc"), colors.HexColor("#ffffff")]),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('PADDING', (0,0), (-1,-1), 5.5),
    ]))
    elements.append(table_status)
    elements.append(Spacer(1, 10))

    # Seção 4: Suporte e Tratamento de Exceções
    elements.append(KeepTogether([
        Paragraph("4. Suporte e Tratamento de Exceções", style_h2),
        Paragraph("•  <b>Falha no Envio do WhatsApp:</b> Utilize o botão manual <i>[📲 Enviar WhatsApp]</i> na linha do colaborador no painel para renotificar o link.", style_bullet),
        Paragraph("•  <b>Documento ou Foto Ilegível:</b> O gestor pode rejeitar o cadastro informando a justificativa para que o candidato realize o novo envio.", style_bullet),
        Paragraph("•  <b>Redefinição de Acesso:</b> Em caso de perda de senha pelo colaborador, o administrador pode redefinir o acesso no menu <i>Usuários</i> ou disparar nova senha provisória.", style_bullet),
        Spacer(1, 14),
        
        # Controle de Assinaturas
        Paragraph("<b>CONTROLE DE APROVAÇÃO DO PROCEDIMENTO OPERACIONAL:</b>", ParagraphStyle('AppTitle', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=8.5, textColor=colors.HexColor("#0f172a"))),
        Spacer(1, 24)
    ]))

    sig_data = [
        [
            Paragraph("____________________________________________<br/><b>Coordenação de Recursos Humanos (RH)</b>", ParagraphStyle('Sig1', parent=style_body, alignment=1)),
            Paragraph("____________________________________________<br/><b>Assessoria Jurídica e Financeira</b>", ParagraphStyle('Sig2', parent=style_body, alignment=1))
        ]
    ]
    table_sig = Table(sig_data, colWidths=[255, 256.27])
    table_sig.setStyle(TableStyle([
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
    ]))
    elements.append(table_sig)

    # Compilando o PDF com NumberedCanvas para numeração de páginas perfeita
    doc.build(elements, canvasmaker=NumberedCanvas)
    
    # Copiando para o diretório do brain
    import shutil
    shutil.copy(pdf_path_repo, pdf_path_brain)

    print("PDF compilado com sucesso via ReportLab!")

if __name__ == "__main__":
    build_pdf()
