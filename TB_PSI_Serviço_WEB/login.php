<?php
session_start();

// Dados de ligação à base de dados
$host = 'localhost';
$user = 'root';      // utilizador do MySQL
$pass = '';          // senha do MySQL (normalmente vazia no XAMPP)
$db   = 'site_login';

// Conectar à base de dados
$conn = new mysqli($host, $user, $pass, $db);

// Verificar erro de ligação
if ($conn->connect_error) {
    die("Erro de ligação: " . $conn->connect_error);
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['user'] ?? '';
    $password = $_POST['pass'] ?? '';

    // Prepared statement para segurança
    $stmt = $conn->prepare("SELECT password FROM utilizadores WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hash);
        $stmt->fetch();

        // Verificar se a senha está correta
        if (hash('sha256', $password) === $hash) {
            $_SESSION['login'] = true;
            $_SESSION['user'] = $username;
            header("Location: area_exclusiva.php");
            exit;
        } else {
            $erro = "Palavra-passe incorreta.";
        }
    } else {
        $erro = "Utilizador não encontrado.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Área Reservada</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <h1>Área Reservada</h1>

  <?php if ($erro): ?>
    <p style="color:red;"><?php echo htmlspecialchars($erro); ?></p>
  <?php endif; ?>

  <form method="post">
    <label>Utilizador:</label>
    <input type="text" name="user" required>

    <label>Palavra-passe:</label>
    <input type="password" name="pass" required>

    <button type="submit">Entrar</button>
  </form>

  <p><a href="index.html">Voltar</a></p>
</body>
</html>
