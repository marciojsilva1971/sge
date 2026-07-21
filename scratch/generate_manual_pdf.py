import os
from fpdf import FPDF

class ManualPDF(FPDF):
    def header(self):
        # Cabeçalho Elegante
        self.set_fill_color(15, 23, 42) # Slate Dark Blue
        self.rect(0, 0, 210, 24, 'F')
        
        self.set_font("Helvetica", "B", 12)
        self.set_text_color(20, 184, 166) # Accent Teal
        self.set_xy(10, 6)
        self.cell(0, 6, "SGE - SISTEMA DE GESTAO ELEITORAL")
        
        self.set_font("Helvetica", "", 8.5)
        self.set_text_color(226, 232, 240)
        self.set_xy(10, 13)
        self.cell(0, 6, "PROCEDIMENTO OPERACIONAL PADRAO (POP) - MODULO DE RH & COLABORADORES")
        self.ln(12)

    def footer(self):
        self.set_y(-15)
        self.set_font("Helvetica", "I", 8)
        self.set_text_color(148, 163, 184)
        self.cell(0, 10, f"Pagina {self.page_no()}/{{nb}} - Documento Gerado para Homologacao e Conferência Interna", align="C")

def generate_pdf():
    pdf = ManualPDF(orientation="P", unit="mm", format="A4")
    pdf.alias_nb_pages()
    pdf.set_margins(12, 28, 12)
    pdf.set_auto_page_break(auto=True, margin=18)
    pdf.add_page()

    # Título Principal
    pdf.set_font("Helvetica", "B", 15)
    pdf.set_text_color(15, 23, 42)
    pdf.cell(0, 8, "Manual de Cadastro e Contratacao de Colaboradores de Campanha")
    pdf.ln(8)
    pdf.set_draw_color(20, 184, 166)
    pdf.set_line_width(0.8)
    pdf.line(12, pdf.get_y(), 198, pdf.get_y())
    pdf.ln(5)

    # Box de Alerta Legal / Compliance
    pdf.set_fill_color(241, 245, 249)
    pdf.set_draw_color(203, 213, 225)
    pdf.set_line_width(0.3)
    pdf.rect(12, pdf.get_y(), 186, 22, 'DF')
    
    start_y = pdf.get_y()
    pdf.set_xy(15, start_y + 2)
    pdf.set_font("Helvetica", "B", 9)
    pdf.set_text_color(185, 28, 28) # Red alert
    pdf.cell(0, 5, "IMPORTANTE - CONFORMIDADE LEGAL E ELEITORAL (Res. TSE n 23.607/2019 e LGPD):")
    pdf.set_xy(15, start_y + 8)
    pdf.set_font("Helvetica", "", 8.5)
    pdf.set_text_color(51, 65, 85)
    pdf.multi_cell(180, 4.5, "Todos os colaboradores contratados para prestacao de servicos de campanha devem possuir contrato formal assinado, CPF em situacao regular na Receita Federal e consentimento explicito (Opt-in) para tratamento de dados pessoais e comunicacao via WhatsApp.")
    pdf.set_y(start_y + 26)

    # Seção: Fluxo em 4 Etapas
    pdf.set_font("Helvetica", "B", 11)
    pdf.set_text_color(15, 23, 42)
    pdf.cell(0, 7, "1. Fluxo de Contratacao em 4 Etapas")
    pdf.ln(7)

    steps = [
        ("Etapa 1: Auto-Cadastro Publico", "O colaborador preenche os dados cadastrais e anexa a foto do RG/CNH/CIN no portal publico."),
        ("Etapa 2: Aval Cadastral do RH", "O gestor analisa o cadastro, define a funcao, valor e emite o contrato formal em PDF."),
        ("Etapa 3: Assinatura e Envio", "O colaborador e notificado via WhatsApp (Z-API), assina a via impressa e envia a foto/PDF."),
        ("Etapa 4: Homologacao & Acesso", "O RH valida o CPF/TSE, confere os documentos e libera o login com senha provisoria via WhatsApp.")
    ]

    for title, desc in steps:
        pdf.set_fill_color(248, 250, 252)
        pdf.set_draw_color(226, 232, 240)
        pdf.rect(12, pdf.get_y(), 186, 11, 'DF')
        
        curr_y = pdf.get_y()
        pdf.set_xy(15, curr_y + 1.5)
        pdf.set_font("Helvetica", "B", 8.5)
        pdf.set_text_color(15, 118, 110) # Teal Dark
        pdf.cell(55, 4, title)
        pdf.set_font("Helvetica", "", 8.5)
        pdf.set_text_color(51, 65, 85)
        pdf.cell(0, 4, f"- {desc}")
        pdf.set_y(curr_y + 13)

    pdf.ln(3)

    # Seção: Detalhamento do Procedimento Operacional
    pdf.set_font("Helvetica", "B", 11)
    pdf.set_text_color(15, 23, 42)
    pdf.cell(0, 7, "2. Detalhamento do Procedimento Operacional")
    pdf.ln(7)

    items = [
        ("Etapa 1 - Auto-Cadastro Publico (/colaborador/cadastro):", [
            "Preenchimento obrigatório: Nome Completo, CPF, RG, Orgao Emissor, Data de Nascimento e Nome da Mae.",
            "Informacoes Financeiras: Chave PIX, Banco, Agencia e Conta Corrente para recebimento de repasses.",
            "Upload de Comprovante: Anexacao obrigatoria de foto legivel do RG, CNH ou CIN.",
            "Termos LGPD: Aceite expresso de opt-in para envio de contratos e notificacoes pelo WhatsApp."
        ]),
        ("Etapa 2 - Aval Cadastral & Emissao (/admin/rh):", [
            "Acesso do Gestor ao painel Gestao de RH e localizacao do cadastro pendente.",
            "Preenchimento da Funcao na Campanha (Cabo Eleitoral, Panfletista, Motorista, Coordenador).",
            "Definicao do Valor Contratado (R$) e Vigencia de Inicio e Termino do Contrato.",
            "Disparo Automatico: O SGE gera o PDF e envia o link direto ao WhatsApp do colaborador via Z-API."
        ]),
        ("Etapa 3 - Assinatura e Devolucao do Documento:", [
            "Recebimento da mensagem no celular com os links do contrato em PDF e do portal de envio.",
            "Impressao e Assinatura fisica da via contratual pelo colaborador de campanha.",
            "Upload da via assinada pelo celular no portal publico tokenizado sem necessidade de login."
        ]),
        ("Etapa 4 - Conferência, Validacao TSE e Homologacao Final:", [
            "Abertura do modal de conferência pelo RH no painel administrativo.",
            "Verificacao das sinalizacoes visuais: [ RG/CNH Anexado ] e [ Contrato Assinado Enviado ].",
            "Consulta automatica de conformidade cadastral (CPF Regular na Receita Federal e Resolucao TSE 23.607).",
            "Liberacao de Acesso: Definicao da role do usuario e envio automatico de senha provisoria via WhatsApp."
        ])
    ]

    for section_title, bullets in items:
        pdf.set_font("Helvetica", "B", 9)
        pdf.set_text_color(30, 41, 59)
        pdf.cell(0, 5, section_title)
        pdf.ln(5)
        pdf.set_font("Helvetica", "", 8.5)
        pdf.set_text_color(71, 85, 105)
        for bullet in bullets:
            pdf.cell(5)
            pdf.cell(3, 4.5, "-")
            pdf.multi_cell(175, 4.5, f" {bullet}")
        pdf.ln(2)

    # Seção: Tabela de Status do Sistema
    pdf.add_page()
    pdf.set_font("Helvetica", "B", 11)
    pdf.set_text_color(15, 23, 42)
    pdf.cell(0, 7, "3. Tabela de Status do Colaborador no SGE")
    pdf.ln(7)

    # Cabeçalho da Tabela
    pdf.set_fill_color(15, 23, 42)
    pdf.set_font("Helvetica", "B", 8.5)
    pdf.set_text_color(255, 255, 255)
    pdf.cell(58, 7, " Status no Sistema", 1, fill=True)
    pdf.cell(64, 7, " Significado Operacional", 1, fill=True)
    pdf.cell(64, 7, " Proxima Acao Requerida", 1, fill=True)
    pdf.ln(7)

    table_data = [
        ("AGUARDANDO_AVAL", "Novo auto-cadastro publico realizado", "RH deve conferir documento e emitir contrato"),
        ("AGUARDANDO_ASSINATURA_CONTRATO", "Contrato emitido e notificado via WhatsApp", "Colaborador deve assinar e enviar o arquivo"),
        ("AGUARDANDO_CONFERENCIA_CONTRATO", "Colaborador enviou o contrato assinado", "RH deve realizar a homologacao no modal"),
        ("HOMOLOGADO", "Colaborador aprovado com acesso liberado", "Pronto para lancamentos e atividades no SGE"),
        ("REJEITADO", "Cadastro ou contrato recusado pelo RH", "Necessita de correcao de dados ou reenvio")
    ]

    pdf.set_font("Helvetica", "", 8)
    pdf.set_text_color(30, 41, 59)
    for i, (st, sig, act) in enumerate(table_data):
        bg = (248, 250, 252) if i % 2 == 0 else (255, 255, 255)
        pdf.set_fill_color(*bg)
        pdf.cell(58, 6.5, f" {st}", 1, fill=True)
        pdf.cell(64, 6.5, f" {sig}", 1, fill=True)
        pdf.cell(64, 6.5, f" {act}", 1, fill=True)
        pdf.ln(6.5)

    pdf.ln(8)

    # Seção: Suporte e Tratamento de Exceções
    pdf.set_font("Helvetica", "B", 11)
    pdf.set_text_color(15, 23, 42)
    pdf.cell(0, 7, "4. Suporte e Tratamento de Excecoes")
    pdf.ln(7)
    
    exc_items = [
        ("Falha no Envio do WhatsApp:", "Utilize o botao manual [ Enviar WhatsApp ] na linha do colaborador para disparar o link novamente."),
        ("Documento ou Foto Ilegivel:", "O gestor pode rejeitar o cadastro com a devida justificativa e encaminhar o link para novo envio."),
        ("Esquecimento de Senha:", "O administrador pode redefinir o acesso no menu Usuarios ou disparar uma nova senha provisoria.")
    ]

    for title, desc in exc_items:
        pdf.set_font("Helvetica", "B", 8.5)
        pdf.set_text_color(15, 118, 110)
        pdf.cell(48, 5, f" {title}")
        pdf.set_font("Helvetica", "", 8.5)
        pdf.set_text_color(51, 65, 85)
        pdf.multi_cell(138, 5, desc)
        pdf.ln(2)

    pdf.ln(10)
    
    # Assinaturas / Controle de Aprovação
    pdf.set_font("Helvetica", "B", 9)
    pdf.set_text_color(15, 23, 42)
    pdf.cell(0, 5, "CONTROLE DE APROVACAO DO PROCEDIMENTO OPERACIONAL:")
    pdf.ln(14)

    pdf.set_font("Helvetica", "", 8)
    pdf.set_draw_color(148, 163, 184)
    pdf.line(15, pdf.get_y(), 95, pdf.get_y())
    pdf.line(110, pdf.get_y(), 190, pdf.get_y())
    
    pdf.set_xy(15, pdf.get_y() + 2)
    pdf.cell(80, 4, "Coordenacao de Recursos Humanos (RH)", align="C")
    pdf.set_xy(110, pdf.get_y())
    pdf.cell(80, 4, "Assessoria Juridica e Financeira da Campanha", align="C")
    pdf.ln(8)

    # Salvando em ambos os caminhos
    output_path_repo = "docs/fase_4_rh_e_colaboradores/Manual_Cadastro_Colaborador_SGE.pdf"
    os.makedirs(os.path.dirname(output_path_repo), exist_ok=True)
    pdf.output(output_path_repo)

    output_path_brain = r"C:\Users\marci\.gemini\antigravity\brain\01f1fa21-dd59-4151-9384-c3be62a3c6e3\Manual_Cadastro_Colaborador_SGE.pdf"
    pdf.output(output_path_brain)

    print("PDF gerado com sucesso!")

if __name__ == "__main__":
    generate_pdf()
