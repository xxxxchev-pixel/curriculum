<?php
require_once __DIR__ . '/../config.php';
$mysqli = db_connect();
$data = [
    'nome' => 'Teste User',
    'email' => 'testeuser@example.local',
    'password' => 'Abc12345',
    'telefone' => '912345678',
    'morada' => 'Rua Teste 1',
    'nif' => '123456789',
    'codigo_postal' => '1000-001'
];
$res = register_user($mysqli, $data);
echo 'register_user returned: ' . ($res ? 'true' : 'false') . "\n";
// Show inserted user
$r = $mysqli->query("SELECT id, nome, email, created_at FROM users WHERE email = 'testeuser@example.local'");
$u = $r->fetch_assoc();
print_r($u);
$mysqli->close();
