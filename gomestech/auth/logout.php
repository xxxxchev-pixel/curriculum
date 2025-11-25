<?php
session_start();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

session_destroy();

// Redirecionar para a página inicial
header('Location: ../index.php');
exit;
?>
