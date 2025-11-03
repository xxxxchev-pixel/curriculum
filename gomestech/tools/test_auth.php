<?php
require_once __DIR__ . '/../config.php';
$mysqli = db_connect();
$email = 'testeuser@example.local';
$password = 'TestPassword123';
$user = authenticate_user($mysqli, $email, $password);
if ($user) {
    echo "AUTH OK: id=" . ($user['id'] ?? 'N/A') . " nome=" . ($user['nome'] ?? 'N/A') . "\n";
} else {
    echo "AUTH FAIL for $email\n";
}
$mysqli->close();

?>