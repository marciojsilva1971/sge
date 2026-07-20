<?php
namespace App\Services;

use Exception;

/**
 * Serviço de Consulta Pública de CNPJ (BrasilAPI e ReceitaWS).
 */
class CnpjService {

    /**
     * Consulta dados cadastrais do CNPJ na receita/bases públicas.
     * 
     * @param string $cnpj
     * @return array
     */
    public static function consultar(string $cnpj): array {
        $cleanCnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cleanCnpj) !== 14) {
            return [
                'success' => false,
                'message' => 'CNPJ inválido. Um CNPJ válido possui 14 dígitos.'
            ];
        }

        // 1. Tenta consulta na BrasilAPI (Gratuito, rápido e sem limite)
        $brasilApiResult = self::queryBrasilApi($cleanCnpj);
        if ($brasilApiResult['success']) {
            return $brasilApiResult;
        }

        // 2. Fallback para ReceitaWS
        $receitaWsResult = self::queryReceitaWs($cleanCnpj);
        if ($receitaWsResult['success']) {
            return $receitaWsResult;
        }

        return [
            'success' => false,
            'message' => 'Não foi possível localizar o CNPJ nas bases públicas da Receita Federal.'
        ];
    }

    private static function queryBrasilApi(string $cnpj): array {
        $url = "https://brasilapi.com.br/api/cnpj/v1/" . $cnpj;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SGE-Eleitoral/1.0');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($response)) {
            $data = json_decode($response, true);
            if (is_array($data) && !empty($data['razao_social'])) {
                $logradouro = trim(($data['descricao_tipo_de_logradouro'] ?? '') . ' ' . ($data['logradouro'] ?? ''));
                $enderecoComp = trim($logradouro . ', ' . ($data['numero'] ?? 'S/N') . ' - ' . ($data['bairro'] ?? '') . ' - ' . ($data['municipio'] ?? '') . '/' . ($data['uf'] ?? ''));

                return [
                    'success' => true,
                    'cnpj' => self::formatCnpj($cnpj),
                    'razao_social' => $data['razao_social'] ?? '',
                    'nome_fantasia' => $data['nome_fantasia'] ?? '',
                    'cnae' => $data['cnae_fiscal_descricao'] ?? '',
                    'logradouro' => $logradouro,
                    'numero' => $data['numero'] ?? '',
                    'bairro' => $data['bairro'] ?? '',
                    'municipio' => $data['municipio'] ?? '',
                    'uf' => $data['uf'] ?? '',
                    'endereco_completo' => $enderecoComp,
                    'fonte' => 'BrasilAPI'
                ];
            }
        }

        return ['success' => false];
    }

    private static function queryReceitaWs(string $cnpj): array {
        $url = "https://receitaws.com.br/v1/cnpj/" . $cnpj;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SGE-Eleitoral/1.0');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($response)) {
            $data = json_decode($response, true);
            if (is_array($data) && ($data['status'] ?? '') === 'OK' && !empty($data['nome'])) {
                $enderecoComp = trim(($data['logradouro'] ?? '') . ', ' . ($data['numero'] ?? 'S/N') . ' - ' . ($data['bairro'] ?? '') . ' - ' . ($data['municipio'] ?? '') . '/' . ($data['uf'] ?? ''));

                return [
                    'success' => true,
                    'cnpj' => self::formatCnpj($cnpj),
                    'razao_social' => $data['nome'] ?? '',
                    'nome_fantasia' => $data['fantasia'] ?? '',
                    'cnae' => $data['atividade_principal'][0]['text'] ?? '',
                    'logradouro' => $data['logradouro'] ?? '',
                    'numero' => $data['numero'] ?? '',
                    'bairro' => $data['bairro'] ?? '',
                    'municipio' => $data['municipio'] ?? '',
                    'uf' => $data['uf'] ?? '',
                    'endereco_completo' => $enderecoComp,
                    'fonte' => 'ReceitaWS'
                ];
            }
        }

        return ['success' => false];
    }

    public static function formatCnpj(string $cnpj): string {
        $clean = preg_replace('/\D/', '', $cnpj);
        if (strlen($clean) === 14) {
            return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $clean);
        }
        return $cnpj;
    }
}
