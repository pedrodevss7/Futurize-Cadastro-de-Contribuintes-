<?php
$hash = '$argon2id$v=19$m=65536,t=4,p=1$RGZjL3hHejVjMDA3bGdFTw$o5N+CSKUdgvQF0/cxjYSH0NI1qfTpu4l8g8wxj5p61c';

$senhas = ['123456', 'admin123', 'senha123', 'root', 'teste', 'abc123']; // lista para testar

foreach ($senhas as $senha) {
    if (password_verify($senha, $hash)) {
        echo "Senha correta: $senha\n";
        exit;
    }
}

echo "Nenhuma das senhas testadas corresponde.\n";
