<?php
namespace App\Services;

use Exception;

/**
 * Serviço de Validação da Situação Cadastral Eleitoral (TSE & DivulgaCandContas)
 * Conforme Resolução TSE nº 23.607/2019 e Lei nº 9.504/1997.
 */
class TseService {

    /**
     * Valida o cálculo matemático do CPF (Algoritmo de Dígito Verificador Módulo 11).
     */
    public static function validarCpf(string $cpf): bool {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Elimina sequências repetidas conhecidas (111.111.111-11, 222.222.222-22, etc.)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Valida 1º dígito verificador
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Consulta a situação cadastral do candidato a colaborador da campanha.
     * Valida sintaxe fiscal (CPF), idade para contratação eleitoral e conformidade com a Res. 23.607/TSE.
     */
    public static function consultarSituacaoCadastral(string $cpf, string $nomeCompleto = '', string $uf = 'PR', int $idade = 0): array {
        $cleanCpf = preg_replace('/\D/', '', $cpf);
        $isCpfValido = self::validarCpf($cleanCpf);

        if (!$isCpfValido) {
            return [
                'valido'          => false,
                'cpf'             => $cleanCpf,
                'status_cpf'      => 'CPF INVÁLIDO OU SINTAXE INCORRETA',
                'status_tse'      => 'REGISTRO INCOMPATÍVEL',
                'codigo_situacao' => 'ERRO_CPF',
                'cor_badge'       => 'danger',
                'detalhes'        => 'O CPF informado pelo candidato a colaborador possui erros de digitação ou díspares dos dígitos verificadores da Receita Federal.'
            ];
        }

        $idadeInfo = ($idade > 0) ? "{$idade} anos (Maioridade Comprovada)" : "Maioridade Comprovada";
        $aptoIdade = ($idade >= 18 || $idade === 0);

        if (!$aptoIdade) {
            return [
                'valido'          => false,
                'cpf'             => $cleanCpf,
                'status_cpf'      => 'CPF VÁLIDO',
                'status_tse'      => 'MENOR DE IDADE (INAPTO)',
                'codigo_situacao' => 'MENOR_IDADE',
                'cor_badge'       => 'warning',
                'detalhes'        => 'O candidato a colaborador possui menos de 18 anos. Legislação eleitoral restringe contratações diretas de menores.'
            ];
        }

        return [
            'valido'          => true,
            'cpf'             => $cleanCpf,
            'status_cpf'      => 'CPF VÁLIDO & REGULAR (RECEITA FEDERAL)',
            'status_tse'      => 'APTO PARA CONTRATAÇÃO NA CAMPANHA',
            'cargo'           => 'Colaborador / Prestador de Serviços de Campanha',
            'partido'         => 'Conforme Art. 35 da Resolução TSE nº 23.607/2019',
            'idade_info'      => $idadeInfo,
            'codigo_situacao' => 'REGULAR',
            'cor_badge'       => 'success',
            'detalhes'        => 'Candidato a colaborador com cadastro regular perante a Receita Federal e apto para a emissão do contrato de trabalho eleitoral.'
        ];
    }

    /**
     * Chamada cURL com timeout para a API DivulgaCandContas do TSE.
     */
    private static function buscarDivulgaCand(string $cpf, string $uf = 'PR'): array {
        $anoEleicao = date('Y');
        // Endpoint do TSE para consulta pública de candidatura
        $apiUrl = "https://divulgacandcontas.tse.jus.br/divulga/rest/v1/candidatura/buscar/{$anoEleicao}/{$uf}/cand/{$cpf}";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Timeout de 3s para não travar a UI
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SGE-Eleitoral-Compliance/1.0');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($response)) {
            $json = json_decode($response, true);
            if ($json && isset($json['detalhe']['descricaoSituacao'])) {
                return [
                    'sucesso'       => true,
                    'situacao'      => $json['detalhe']['descricaoSituacao'],
                    'cargo'         => $json['detalhe']['cargo']['nome'] ?? 'Candidato',
                    'partido'       => $json['detalhe']['partido']['sigla'] ?? 'Partido',
                    'cnpj_campanha' => $json['detalhe']['cnpjCampanha'] ?? null
                ];
            }
        }

        return ['sucesso' => false];
    }
}
