<?php
namespace App\Models;

use App\Core\Model;
use App\Services\AuditLogger;
use Exception;

/**
 * Modelo de Contratos de Colaboradores de Campanha.
 */
class Contrato extends Model {
    protected string $table = 'contratos_colaboradores';

    /**
     * Busca o contrato ativo de um colaborador específico.
     */
    public function findByColaborador(int $colaboradorId): ?array {
        $sql = "SELECT * FROM `{$this->table}` WHERE colaborador_id = :colaborador_id ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['colaborador_id' => $colaboradorId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Alias para findByColaborador.
     */
    public function getContratoPorColaborador(int $colaboradorId): ?array {
        return $this->findByColaborador($colaboradorId);
    }

    /**
     * Exclui todos os contratos de um colaborador específico.
     */
    public function deleteByColaborador(int $colaboradorId): bool {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE colaborador_id = :cid");
        return $stmt->execute(['cid' => $colaboradorId]);
    }

    /**
     * Emite um novo contrato para um colaborador.
     */
    public function emitirContrato(array $data): int {
        $insertData = [
            'colaborador_id'         => (int)$data['colaborador_id'],
            'titulo_contrato'        => trim($data['titulo_contrato'] ?? 'Contrato de Prestação de Serviços de Campanha Eleitoral'),
            'funcao_campanha'        => trim($data['funcao_campanha']),
            'valor_contratado'       => (float)$data['valor_contratado'],
            'forma_pagamento'        => trim($data['forma_pagamento'] ?? 'Transferência Bancária / PIX'),
            'data_inicio'            => $data['data_inicio'],
            'data_fim'               => $data['data_fim'],
            'tipo_assinatura'        => $data['tipo_assinatura'] ?? 'TERCEIROS_API',
            'external_signature_url' => $data['external_signature_url'] ?? null,
            'pdf_original_path'      => $data['pdf_original_path'] ?? null,
            'status_contrato'        => 'EMITIDO'
        ];

        $contratoId = $this->create($insertData);

        AuditLogger::log(
            'EMITIR_CONTRATO',
            $this->table,
            $contratoId,
            null,
            [
                'colaborador_id' => $insertData['colaborador_id'],
                'funcao'         => $insertData['funcao_campanha'],
                'valor'          => $insertData['valor_contratado'],
                'tipo'           => $insertData['tipo_assinatura']
            ]
        );

        return $contratoId;
    }

    /**
     * Atualiza o upload do PDF assinado manualmente ou notificação de assinatura de terceiros.
     */
    public function registrarAssinatura(int $contratoId, string $pdfAssinadoPath, ?string $hashSha256 = null): bool {
        $contrato = $this->find($contratoId);
        if (!$contrato) {
            throw new Exception("Contrato não encontrado.");
        }

        $this->db->beginTransaction();
        try {
            $this->update($contratoId, [
                'pdf_assinado_path' => $pdfAssinadoPath,
                'hash_documento'    => $hashSha256 ?: (file_exists($pdfAssinadoPath) ? hash_file('sha256', $pdfAssinadoPath) : null),
                'status_contrato'   => 'ASSINADO'
            ]);

            // Atualiza status do colaborador para AGUARDANDO_CONFERENCIA_CONTRATO (para o admin conferir o contrato assinado)
            $colaboradorModel = new Colaborador();
            $colaboradorModel->update($contrato['colaborador_id'], ['status' => 'AGUARDANDO_CONFERENCIA_CONTRATO']);

            AuditLogger::log(
                'REGISTRAR_ASSINATURA_CONTRATO',
                $this->table,
                $contratoId,
                ['status' => $contrato['status_contrato']],
                ['status' => 'ASSINADO', 'pdf_assinado' => $pdfAssinadoPath]
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
