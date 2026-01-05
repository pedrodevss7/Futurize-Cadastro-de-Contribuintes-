<?php

// Teste de validação de senhas
echo "═══════════════════════════════════════════════════════════════\n";
echo "🧪 TESTE DE VALIDAÇÃO DE SENHAS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Simular as senhas usadas no seeder
$senhas = [
    'admin123' => 'admin',
    'servidor123' => 'servidor',
];

// Gerar hashes para comparação
foreach ($senhas as $senha => $usuario) {
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    echo "Usuário: $usuario\n";
    echo "Senha:   $senha\n";
    echo "Hash:    $hash\n";
    echo "Verifi:  " . (password_verify($senha, $hash) ? "✅ VÁLIDA" : "❌ INVÁLIDA") . "\n";
    echo "\n";
}

// Informações de acesso
echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ CREDENCIAIS DE ACESSO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "👨‍💼 ADMIN\n";
echo "├─ Username: admin\n";
echo "├─ Email:    admin@futurize.com\n";
echo "├─ Senha:    admin123\n";
echo "└─ Tipo:     admin\n\n";

echo "👨‍💻 SERVIDOR\n";
echo "├─ Username: servidor\n";
echo "├─ Email:    servidor@futurize.com\n";
echo "├─ Senha:    servidor123\n";
echo "└─ Tipo:     servidor\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "📝 NOTAS IMPORTANTES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "1. As senhas usadas são:\n";
echo "   - admin123 (para usuário admin)\n";
echo "   - servidor123 (para usuário servidor)\n\n";

echo "2. Se receber 'usuário e senha inválidos':\n";
echo "   a) Verificar se o usuário está 'ativo' no banco\n";
echo "   b) Confirmar que está enviando a senha correta\n";
echo "   c) Verificar se a URL do login está correta\n\n";

echo "3. Para resetar um usuário:\n";
echo "   DELETE FROM usuarios WHERE username='admin';\n";
echo "   Depois execute: php spark db:seed UsuariosSeeder\n\n";

?>
