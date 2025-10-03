<?php
// Script CLI para criar um utilizador com password hashed na tabela `users`.
// Uso: php create_user.php username password

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';

if (!$pdo) {
    echo "Erro: ligação à BD não disponível. Verifique `inc/config.php` e se o servidor MySQL está a correr.\n";
    exit(1);
}

$username = $argv[1] ?? null;
$password = $argv[2] ?? null;

if (!$username || !$password) {
    echo "Uso: php create_user.php <username> <password>\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, NOW())');
    $stmt->execute([$username, $hash]);
    echo "Utilizador criado: $username\n";
} catch (PDOException $e) {
    echo "Erro ao criar utilizador: " . $e->getMessage() . "\n";
}
?>