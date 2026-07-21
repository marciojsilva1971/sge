<?php
namespace App\Models;

use App\Core\Model;
use App\Services\AuditLogger;
use Exception;
use DateTime;

/**
 * Modelo de Colaboradores de Campanha (RH).
 */
class Colaborador extends Model {
    protected string $table = 'colaboradores';

    /**
     * Calcula a idade exata em anos a partir da Data de Nascimento.
     */
    public static function calcularIdade(string $dataNascimento): int {
        $birth = new DateTime($dataNascimento);
        $today = new DateTime('today');
        return $birth->diff($today)->y;
    }

    /**
     * Busca um colaborador pelo CPF.
     */
    public function findByCpf(string $cpf): ?array {
        $cleanCpf = preg_replace('/[^\d]/', '', $cpf);
        $sql = "SELECT * FROM `{$this->table}` WHERE REPLACE(REPLACE(cpf, '.', ''), '-', '') = :cpf LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cpf' => $cleanCpf]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Busca um colaborador pelo token seguro de cadastro/link público.
     */
    public function findByToken(string $token): ?array {
        $sql = "SELECT * FROM `{$this->table}` WHERE token_cadastro = :token LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Lista colaboradores com filtros e dados do usuário/contrato associado.
     */
    public function getColaboradoresCompleto(array $filters = []): array {
        $sql = "SELECT c.*, u.name as usuario_nome, u.email as usuario_email, u.role_id, r.name as role_nome,
                       cc.id as contrato_id, cc.titulo_contrato, cc.valor_contratado, cc.status_contrato, cc.pdf_assinado_path, cc.tipo_assinatura, cc.external_signature_url,
                       cc.funcao_campanha, cc.forma_pagamento, cc.data_inicio, cc.data_fim
                FROM `{$this->table}` c
                LEFT JOIN `usuarios` u ON c.usuario_id = u.id
                LEFT JOIN `roles` r ON u.role_id = r.id
                LEFT JOIN `contratos_colaboradores` cc ON cc.colaborador_id = c.id";

        $where = [];
        $params = [];

        if (!empty($filters['nome'])) {
            $where[] = "c.nome_completo LIKE :nome";
            $params['nome'] = '%' . $filters['nome'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['cpf'])) {
            $cleanCpf = preg_replace('/[^\d]/', '', $filters['cpf']);
            $where[] = "REPLACE(REPLACE(c.cpf, '.', ''), '-', '') LIKE :cpf";
            $params['cpf'] = '%' . $cleanCpf . '%';
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY c.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cadastra um novo colaborador no sistema (via RH ou Auto-cadastro).
     */
    public function cadastrarColaborador(array $data): int {
        $idade = self::calcularIdade($data['data_nascimento']);
        
        if ($idade < 16) {
            throw new Exception("Cadastro não permitido: Colaborador com idade inferior a 16 anos ($idade anos).");
        }

        $token = bin2hex(random_bytes(32));

        $insertData = [
            'nome_completo'    => trim($data['nome_completo']),
            'cpf'              => trim($data['cpf']),
            'rg'                  => trim($data['rg']),
            'rg_orgao_emissor'    => trim($data['rg_orgao_emissor'] ?? 'SSP'),
            'documento_foto_path' => $data['documento_foto_path'] ?? null,
            'foto_rosto_path'     => $data['foto_rosto_path'] ?? null,
            'data_nascimento'     => $data['data_nascimento'],
            'idade_calculada'  => $idade,
            'celular_whatsapp' => trim($data['celular_whatsapp']),
            'email'            => trim(strtolower($data['email'])),
            'cep'              => $data['cep'] ?? null,
            'logradouro'       => $data['logradouro'] ?? null,
            'numero'           => $data['numero'] ?? null,
            'complemento'      => $data['complemento'] ?? null,
            'bairro'           => $data['bairro'] ?? null,
            'cidade'           => $data['cidade'] ?? null,
            'uf'               => $data['uf'] ?? null,
            'banco_codigo'     => $data['banco_codigo'] ?? null,
            'banco_nome'       => $data['banco_nome'] ?? null,
            'agencia'          => $data['agencia'] ?? null,
            'conta'            => $data['conta'] ?? null,
            'tipo_conta'       => $data['tipo_conta'] ?? 'CORRENTE',
            'chave_pix'        => $data['chave_pix'] ?? null,
            'optin_whatsapp'   => !empty($data['optin_whatsapp']) ? 1 : 0,
            'optin_timestamp'  => !empty($data['optin_whatsapp']) ? date('Y-m-d H:i:s') : null,
            'optin_ip'         => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'token_cadastro'   => $token,
            'status'           => $data['status'] ?? 'AGUARDANDO_AVAL_CADASTRO'
        ];

        $colaboradorId = $this->create($insertData);

        AuditLogger::log(
            'CREATE_COLABORADOR',
            $this->table,
            $colaboradorId,
            null,
            ['nome' => $insertData['nome_completo'], 'cpf' => $insertData['cpf'], 'idade' => $idade]
        );

        return $colaboradorId;
    }

    /**
     * O Administrador confere o cadastro/documento com foto, dá o aval e emite o contrato para o colaborador assinar.
     */
    public function darAvalEEmitirContrato(int $colaboradorId, array $contratoData): int {
        $colaborador = $this->find($colaboradorId);
        if (!$colaborador) {
            throw new Exception("Colaborador não encontrado.");
        }

        $this->db->beginTransaction();
        try {
            // Atualiza status do colaborador para AGUARDANDO_ASSINATURA_CONTRATO
            $this->update($colaboradorId, [
                'status' => 'AGUARDANDO_ASSINATURA_CONTRATO'
            ]);

            // Emite o contrato
            $contratoModel = new Contrato();
            $contratoData['colaborador_id'] = $colaboradorId;
            $contratoId = $contratoModel->emitirContrato($contratoData);

            AuditLogger::log(
                'DAR_AVAL_E_EMITIR_CONTRATO',
                $this->table,
                $colaboradorId,
                ['status' => $colaborador['status']],
                ['status' => 'AGUARDANDO_ASSINATURA_CONTRATO', 'contrato_id' => $contratoId]
            );

            $this->db->commit();
            return $contratoId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Homologar Colaborador e Atribuir Perfil de Usuário no SGE.
     * Gera uma senha provisória válida para o primeiro acesso e retorna os dados de homologação.
     */
    public function homologarEAtribuirPerfil(int $colaboradorId, int $roleId): array {
        $colaborador = $this->find($colaboradorId);
        if (!$colaborador) {
            throw new Exception("Colaborador não encontrado.");
        }

        $this->db->beginTransaction();
        try {
            // Gera uma senha provisória legível e segura (Ex: Sge@A7x9B2)
            $tempPassword = 'Sge@' . substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
            $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

            $userModel = new User();
            $user = $userModel->findByEmail($colaborador['email']);

            if (!$user) {
                // Cria a nova conta de usuário no SGE com status ATIVO e role_id atribuída pelo Admin
                $userData = [
                    'name'               => $colaborador['nome_completo'],
                    'email'              => $colaborador['email'],
                    'celular'            => $colaborador['celular_whatsapp'],
                    'password_hash'      => $passwordHash,
                    'role_id'            => $roleId,
                    'status'             => 'ATIVO',
                    'profile_photo_path' => $colaborador['foto_rosto_path'] ?? null
                ];
                $userId = $userModel->create($userData);
                $isNewUser = true;
            } else {
                $userId = $user['id'];
                // Atualiza a senha provisória, a role e o status para ATIVO se já existir usuário
                $updateFields = [
                    'role_id'       => $roleId,
                    'password_hash' => $passwordHash,
                    'status'        => 'ATIVO'
                ];
                if (!empty($colaborador['foto_rosto_path'])) {
                    $updateFields['profile_photo_path'] = $colaborador['foto_rosto_path'];
                }
                $userModel->update($userId, $updateFields);
                $isNewUser = false;
            }

            // Atualiza status do colaborador para ATIVO e vincula usuario_id
            $this->update($colaboradorId, [
                'usuario_id' => $userId,
                'status'     => 'ATIVO'
            ]);

            AuditLogger::log(
                'HOMOLOGAR_COLABORADOR',
                $this->table,
                $colaboradorId,
                ['status' => $colaborador['status']],
                ['status' => 'ATIVO', 'usuario_id' => $userId, 'role_id' => $roleId]
            );

            $this->db->commit();

            return [
                'user_id'       => $userId,
                'temp_password' => $tempPassword,
                'is_new_user'   => $isNewUser
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
