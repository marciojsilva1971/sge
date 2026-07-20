<?php
// Script de Semente do Banco de Dados - SGE
// Executar via terminal: php scratch/seed.php

echo "Iniciando semeadura do banco de dados SGE...\n";

// 1. Carrega o arquivo .env
$envFile = dirname(__DIR__) . '/.env';
if (!file_exists($envFile)) {
    die("Erro: Arquivo .env não encontrado em {$envFile}. Crie o .env primeiro.\n");
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $matches)) $value = $matches[1];
        elseif (preg_match("/^'(.*)'$/", $value, $matches)) $value = $matches[1];
        $_ENV[$name] = $value;
    }
}

// 2. Conecta ao banco diretamente informando a base
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$dbname = $_ENV['DB_NAME'] ?? 'sge';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Conexão com o banco de dados '{$dbname}' estabelecida com sucesso.\n";
} catch (PDOException $e) {
    die("Erro de conexão MySQL: " . $e->getMessage() . "\n");
}

// 3. Executa o schema.sql e schema_rh.sql
$schemaPath = dirname(__DIR__) . '/schema.sql';
if (file_exists($schemaPath)) {
    echo "Executando arquivo schema.sql...\n";
    $sql = file_get_contents($schemaPath);
    try {
        $pdo->exec($sql);
        echo "Tabelas principais criadas com sucesso (ou já existentes).\n";
    } catch (PDOException $e) {
        echo "Aviso ao executar schema.sql: " . $e->getMessage() . "\n";
    }
}

$schemaRhPath = dirname(__DIR__) . '/schema_rh.sql';
if (file_exists($schemaRhPath)) {
    echo "Executando arquivo schema_rh.sql...\n";
    $sqlRh = file_get_contents($schemaRhPath);
    try {
        $pdo->exec($sqlRh);
        echo "Tabelas de RH criadas com sucesso (ou já existentes).\n";
    } catch (PDOException $e) {
        echo "Aviso ao executar schema_rh.sql: " . $e->getMessage() . "\n";
    }
}

// 4. Insere perfis iniciais (roles)
echo "Inserindo cargos/perfis iniciais (roles)...\n";
$roles = [
    [
        'name' => 'ADMINISTRADOR',
        'description' => 'Administrador Geral do Sistema SGE',
        'permissions' => json_encode([
            'invite_user'    => true,
            'configure_rbac' => true,
            'edit_profile'   => true
        ], JSON_UNESCAPED_UNICODE)
    ],
    [
        'name' => 'FINANCEIRO',
        'description' => 'Coordenador Financeiro de Campanha',
        'permissions' => json_encode([
            'invite_user'    => false,
            'configure_rbac' => false,
            'edit_profile'   => true
        ], JSON_UNESCAPED_UNICODE)
    ],
    [
        'name' => 'COLABORADOR_CAMPO',
        'description' => 'Colaborador ou Cabo Eleitoral',
        'permissions' => json_encode([
            'invite_user'    => false,
            'configure_rbac' => false,
            'edit_profile'   => true
        ], JSON_UNESCAPED_UNICODE)
    ]
];

$roleIds = [];
$stmtRole = $pdo->prepare("SELECT id FROM `roles` WHERE name = :name LIMIT 1");

foreach ($roles as $r) {
    $stmtRole->execute(['name' => $r['name']]);
    $exist = $stmtRole->fetch();
    
    if ($exist) {
        $roleIds[$r['name']] = (int)$exist['id'];
        echo "Cargo '{$r['name']}' já existe. Ignorado.\n";
    } else {
        $stmtInsert = $pdo->prepare(
            "INSERT INTO `roles` (name, description, permissions) VALUES (:name, :description, :permissions)"
        );
        $stmtInsert->execute([
            'name'        => $r['name'],
            'description' => $r['description'],
            'permissions' => $r['permissions']
        ]);
        $roleIds[$r['name']] = (int)$pdo->lastInsertId();
        echo "Cargo '{$r['name']}' inserido com sucesso.\n";
    }
}

// 5. Insere primeiro Administrador
echo "Criando Administrador Inicial...\n";
$adminEmail = 'admin@sge.com';
$adminPass = 'Admin@12345Password';
$adminPhone = '5541999999999';

$stmtUser = $pdo->prepare("SELECT id FROM `usuarios` WHERE email = :email LIMIT 1");
$stmtUser->execute(['email' => $adminEmail]);
$userExist = $stmtUser->fetch();

if ($userExist) {
    echo "Administrador '{$adminEmail}' já existe. Ignorado.\n";
} else {
    $stmtInsertUser = $pdo->prepare(
        "INSERT INTO `usuarios` (name, email, celular, password_hash, role_id, status) 
         VALUES (:name, :email, :celular, :password_hash, :role_id, 'ATIVO')"
    );
    
    $stmtInsertUser->execute([
        'name'          => 'Administrador SGE',
        'email'         => $adminEmail,
        'celular'       => $adminPhone,
        'password_hash' => password_hash($adminPass, PASSWORD_DEFAULT),
        'role_id'       => $roleIds['ADMINISTRADOR']
    ]);
    
    echo "Administrador Inicial criado com sucesso!\n";
    echo "Credenciais de Acesso:\n";
    echo "  E-mail: {$adminEmail}\n";
    echo "  Senha : {$adminPass}\n";
}

// 6. Insere categorias SPCE/TSE
echo "Inserindo categorias SPCE/TSE...\n";
$spceCategories = [
    ['code' => '12100', 'description' => 'Doações de Pessoas Físicas', 'type' => 'RECEITA'],
    ['code' => '12200', 'description' => 'Recursos Próprios do Candidato', 'type' => 'RECEITA'],
    ['code' => '12300', 'description' => 'Recursos de Partidos Políticos (Fundo Partidário)', 'type' => 'RECEITA'],
    ['code' => '12400', 'description' => 'Recursos de Partidos Políticos (FEFC)', 'type' => 'RECEITA'],
    ['code' => '23100', 'description' => 'Publicidade por Materiais Impressos (Militância/Panfletagem)', 'type' => 'DESPESA'],
    ['code' => '23200', 'description' => 'Locação/Cessão de Bens Imóveis', 'type' => 'DESPESA'],
    ['code' => '23300', 'description' => 'Locação de Veículos', 'type' => 'DESPESA'],
    ['code' => '23400', 'description' => 'Combustíveis e Lubrificantes (Viagem/Militância)', 'type' => 'DESPESA'],
    ['code' => '23500', 'description' => 'Serviços Prestados por Terceiros', 'type' => 'DESPESA']
];

$stmtSpce = $pdo->prepare("SELECT id FROM `spce_categories` WHERE code = :code LIMIT 1");
$stmtInsertSpce = $pdo->prepare("INSERT INTO `spce_categories` (code, description, type) VALUES (:code, :description, :type)");

foreach ($spceCategories as $cat) {
    $stmtSpce->execute(['code' => $cat['code']]);
    if ($stmtSpce->fetch()) {
        echo "Categoria SPCE '{$cat['code']}' já existe. Ignorado.\n";
    } else {
        $stmtInsertSpce->execute($cat);
        echo "Categoria SPCE '{$cat['code']}' inserida.\n";
    }
}

// 7. Insere contas bancárias
echo "Inserindo contas bancárias...\n";
$bankAccounts = [
    [
        'name' => 'Conta FEFC (Banco do Brasil)',
        'bank_name' => 'Banco do Brasil S.A.',
        'agency' => '0001',
        'account_number' => '12345-6',
        'fund_type' => 'FEFC',
        'balance' => 50000.00
    ],
    [
        'name' => 'Conta Fundo Partidário (Caixa)',
        'bank_name' => 'Caixa Econômica Federal',
        'agency' => '0002',
        'account_number' => '65432-1',
        'fund_type' => 'FUNDO_PARTIDARIO',
        'balance' => 25000.00
    ],
    [
        'name' => 'Conta Outros Recursos (Itaú)',
        'bank_name' => 'Itaú Unibanco S.A.',
        'agency' => '0003',
        'account_number' => '98765-4',
        'fund_type' => 'OUTROS_RECURSOS',
        'balance' => 15000.00
    ]
];

$stmtBank = $pdo->prepare("SELECT id FROM `bank_accounts` WHERE account_number = :account_number LIMIT 1");
$stmtInsertBank = $pdo->prepare(
    "INSERT INTO `bank_accounts` (name, bank_name, agency, account_number, fund_type, balance) 
     VALUES (:name, :bank_name, :agency, :account_number, :fund_type, :balance)"
);

foreach ($bankAccounts as $acc) {
    $stmtBank->execute(['account_number' => $acc['account_number']]);
    if ($stmtBank->fetch()) {
        echo "Conta bancária '{$acc['account_number']}' já existe. Ignorado.\n";
    } else {
        $stmtInsertBank->execute($acc);
        echo "Conta bancária '{$acc['account_number']}' inserida.\n";
    }
}

// 8. Insere fornecedores padrão
echo "Inserindo fornecedores padrão...\n";
$suppliers = [
    [
        'cnpj_cpf' => '11.222.333/0001-44',
        'corporate_name' => 'Posto de Combustíveis Rota 10 Ltda',
        'trade_name' => 'Posto Rota 10',
        'address' => 'Rodovia BR-116, Km 100 - Curitiba/PR',
        'phone' => '4133333333',
        'email' => 'contato@rota10.com'
    ],
    [
        'cnpj_cpf' => '55.666.777/0001-88',
        'corporate_name' => 'Gráfica e Editora Alvorada Eireli',
        'trade_name' => 'Gráfica Alvorada',
        'address' => 'Rua das Flores, 500 - Curitiba/PR',
        'phone' => '4134444444',
        'email' => 'comercial@graficaalvorada.com'
    ]
];

$stmtSupplier = $pdo->prepare("SELECT id FROM `suppliers` WHERE cnpj_cpf = :cnpj_cpf LIMIT 1");
$stmtInsertSupplier = $pdo->prepare(
    "INSERT INTO `suppliers` (cnpj_cpf, corporate_name, trade_name, address, phone, email) 
     VALUES (:cnpj_cpf, :corporate_name, :trade_name, :address, :phone, :email)"
);

foreach ($suppliers as $sup) {
    $stmtSupplier->execute(['cnpj_cpf' => $sup['cnpj_cpf']]);
    if ($stmtSupplier->fetch()) {
        echo "Fornecedor '{$sup['cnpj_cpf']}' já existe. Ignorado.\n";
    } else {
        $stmtInsertSupplier->execute($sup);
        echo "Fornecedor '{$sup['cnpj_cpf']}' inserido.\n";
    }
}

// 9. Insere tipos de despesas padrão para colaboradores de campo
echo "Inserindo tipos de despesa padrão...\n";
$expenseTypes = [
    ['name' => 'Alimentação', 'description' => 'Despesas com refeição, lanches e água de militantes de campo'],
    ['name' => 'Combustível', 'description' => 'Abastecimento de veículos oficiais de campanha ou de militância'],
    ['name' => 'Hospedagem', 'description' => 'Hospedagem de equipe ou palestrantes/candidatos em trânsito'],
    ['name' => 'Material de Escritório', 'description' => 'Insumos de comitê de campanha'],
    ['name' => 'Militância', 'description' => 'Pagamentos ou ajudas de custo para militantes'],
    ['name' => 'Outros', 'description' => 'Diversos tipos de gastos de campo não classificados']
];

$stmtType = $pdo->prepare("SELECT id FROM `expense_types` WHERE name = :name LIMIT 1");
$stmtInsertType = $pdo->prepare("INSERT INTO `expense_types` (name, description) VALUES (:name, :description)");

foreach ($expenseTypes as $type) {
    $stmtType->execute(['name' => $type['name']]);
    if ($stmtType->fetch()) {
        echo "Tipo de despesa '{$type['name']}' já existe. Ignorado.\n";
    } else {
        $stmtInsertType->execute($type);
        echo "Tipo de despesa '{$type['name']}' inserido.\n";
    }
}

echo "Semeadura finalizada com sucesso!\n";

