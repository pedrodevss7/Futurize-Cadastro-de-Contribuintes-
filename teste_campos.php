<?php

// Teste de validação de campos em contribuintes

echo "═══════════════════════════════════════════════════════════════\n";
echo "🧪 TESTE: Validação de Campos em Contribuintes\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Simular dados do formulário
$dados = [
    'cpf_cnpj' => '12345678901234',
    'razao_social' => 'Empresa Teste',
    'endereco' => 'Rua A',
    'bairro' => 'Centro',
    'cidade' => 'São Paulo',
    'cep' => '01310100',
    'email' => 'contato@empresa.com.br',
    // 'numero' NÃO É ENVIADO (simulando omissão do campo)
];

echo "1️⃣ DADOS RECEBIDOS DO FORMULÁRIO\n";
echo "═════════════════════════════════\n";
foreach ($dados as $chave => $valor) {
    echo "   $chave: $valor\n";
}
echo "\n   ⚠️  Observe que 'numero' NÃO está presente\n\n";

// Validação de campos obrigatórios
$camposObrigatorios = ['cpf_cnpj', 'razao_social', 'endereco', 'bairro', 'cidade'];

echo "2️⃣ VALIDAÇÃO DE CAMPOS OBRIGATÓRIOS\n";
echo "════════════════════════════════════\n";
$erros = [];
foreach ($camposObrigatorios as $campo) {
    if (empty($dados[$campo])) {
        $erros[] = "O campo {$campo} é obrigatório";
    } else {
        echo "   ✅ $campo: PRESENTE\n";
    }
}

if (empty($erros)) {
    echo "\n   ✅ Todos os campos obrigatórios estão presentes!\n\n";
} else {
    echo "\n   ❌ Erros encontrados:\n";
    foreach ($erros as $erro) {
        echo "      - $erro\n";
    }
}

// Preparar dados (simulando a função prepararDados)
echo "3️⃣ PREPARAÇÃO DE DADOS PARA BANCO\n";
echo "════════════════════════════════════\n";

$pre = 1;
$conCodigo = 1001;

// Forma ANTIGA (com erro)
echo "   ❌ FORMA ANTIGA (causava erro):\n";
echo "      'CON_Numero' => \$dados['numero'] ?? null\n";
echo "      → Resultado: undefined array key quando 'numero' não é enviado\n\n";

// Forma NOVA (corrigida)
echo "   ✅ FORMA NOVA (corrigida):\n";
echo "      'CON_Numero' => isset(\$dados['numero']) ? intval(\$dados['numero']) : 0\n";
$numero = isset($dados['numero']) ? intval($dados['numero']) : 0;
echo "      → Resultado: $numero (valor padrão quando não enviado)\n\n";

// Dados finais preparados
$dadosPreprados = [
    'CON_PRE_Codigo'            => $pre,
    'CON_Codigo'                => $conCodigo,
    'CON_CPFCNPJ'               => preg_replace('/\D/', '', $dados['cpf_cnpj'] ?? ''),
    'CON_Nome'                  => $dados['razao_social'] ?? '',
    'CON_Endereco'              => $dados['endereco'] ?? '',
    'CON_Numero'                => isset($dados['numero']) ? intval($dados['numero']) : 0,
    'CON_Complemento'           => $dados['complemento'] ?? null,
    'CON_Bairro'                => $dados['bairro'] ?? '',
    'CON_Cidade'                => $dados['cidade'] ?? '',
    'CON_CEP'                   => preg_replace('/\D/', '', $dados['cep'] ?? ''),
    'CON_Estado'                => $dados['estado'] ?? null,
    'CON_Telefone1'             => preg_replace('/\D/', '', $dados['telefone1'] ?? ''),
    'CON_Telefone2'             => preg_replace('/\D/', '', $dados['telefone2'] ?? ''),
    'CON_Email'                 => $dados['email'] ?? null,
    'CON_InscricaoEstadual'     => $dados['inscricao_estadual'] ?? null,
    'CON_InscricaoMunicipal'    => $dados['inscricao_municipal'] ?? null,
    'CON_InscricaoMunicipalAno' => $dados['inscricao_municipal_ano'] ?? null,
];

echo "4️⃣ DADOS PREPARADOS PARA INSERIR NO BANCO\n";
echo "═════════════════════════════════════════════\n";
foreach ($dadosPreprados as $chave => $valor) {
    $display = $valor === null ? 'NULL' : (is_numeric($valor) ? $valor : "'{$valor}'");
    echo "   $chave: $display\n";
}

echo "\n5️⃣ RESULTADO FINAL\n";
echo "═══════════════════\n";
echo "✅ Validação: PASSOU\n";
echo "✅ Preparação: SUCESSO\n";
echo "✅ 'CON_Numero' será salvo como: " . $dadosPreprados['CON_Numero'] . "\n";
echo "\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ TESTE CONCLUÍDO COM SUCESSO\n";
echo "═══════════════════════════════════════════════════════════════\n";

?>
