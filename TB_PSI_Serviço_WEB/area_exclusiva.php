<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Área Exclusiva</title>
</head>
<body>
  <h1>Bem-vindo à Área Exclusiva!</h1>
  <p>Olá, <?php echo htmlspecialchars($_SESSION['user']); ?> 👋</p>
  <p>Este conteúdo só está disponível para utilizadores autenticados.</p>
  <p><a href="logout.php">Terminar sessão</a></p>
</body>
</html>
