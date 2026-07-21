<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isFila = strpos($currentUri, '/fila') !== false;
$isTipos = strpos($currentUri, '/tipos-despesas') !== false;
$isDespesas = strpos($currentUri, '/despesas') !== false;
$isFornecedores = strpos($currentUri, '/fornecedores') !== false;
$isContratos = strpos($currentUri, '/contratos') !== false;
$isGeral = !$isFila && !$isTipos && !$isDespesas && !$isFornecedores && !$isContratos;
?>
<div class="fin-subnav-tabs" style="display: flex; gap: 8px; margin-bottom: 20px; background: #0f172a; padding: 8px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.08); flex-wrap: wrap;">
    <a href="<?= $this->baseUrl('admin/financeiro') ?>" class="btn btn-sm <?= $isGeral ? 'btn-teal' : 'btn-secondary' ?>" style="font-size: 12px; font-weight: bold; border-radius: 6px; padding: 6px 12px;">
        📊 Visão Geral & Receitas
    </a>
    <a href="<?= $this->baseUrl('admin/financeiro/fila') ?>" class="btn btn-sm <?= $isFila ? 'btn-teal' : 'btn-secondary' ?>" style="font-size: 12px; font-weight: bold; border-radius: 6px; padding: 6px 12px;">
        ⚖️ Fila de Aprovações
    </a>
    <a href="<?= $this->baseUrl('admin/financeiro/tipos-despesas') ?>" class="btn btn-sm <?= $isTipos ? 'btn-teal' : 'btn-secondary' ?>" style="font-size: 12px; font-weight: bold; border-radius: 6px; padding: 6px 12px;">
        🏷️ Tipos de Despesas
    </a>
    <a href="<?= $this->baseUrl('admin/financeiro/despesas') ?>" class="btn btn-sm <?= $isDespesas ? 'btn-teal' : 'btn-secondary' ?>" style="font-size: 12px; font-weight: bold; border-radius: 6px; padding: 6px 12px;">
        💸 Despesas Cadastradas
    </a>
    <a href="<?= $this->baseUrl('admin/financeiro/fornecedores') ?>" class="btn btn-sm <?= $isFornecedores ? 'btn-teal' : 'btn-secondary' ?>" style="font-size: 12px; font-weight: bold; border-radius: 6px; padding: 6px 12px;">
        🏢 Fornecedores
    </a>
    <a href="<?= $this->baseUrl('admin/financeiro/contratos') ?>" class="btn btn-sm <?= $isContratos ? 'btn-teal' : 'btn-secondary' ?>" style="font-size: 12px; font-weight: bold; border-radius: 6px; padding: 6px 12px;">
        📄 Contratos por Tempo Determinado
    </a>
</div>
