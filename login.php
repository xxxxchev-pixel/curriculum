<?php
session_start();
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';

$erro = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utilizador = isset($_POST["utilizador"]) ? trim($_POST["utilizador"]) : '';
    $password = isset($_POST["password"]) ? $_POST["password"] : '';

    $authenticated = false;

    // Primeiro tenta autenticar usando a base de dados (se configurada)
    if (defined('USE_DB') && USE_DB && isset($pdo) && $pdo) {
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$utilizador]);
        $row = $stmt->fetch();
        if ($row && isset($row['password_hash']) && password_verify($password, $row['password_hash'])) {
            $authenticated = true;
        }
    } else {
        // Fallback — credenciais definidas no código (exigência da tarefa original)
        if ($utilizador === 'aluno' && $password === '12345') {
            $authenticated = true;
        }
    }

    if ($authenticated) {
        $_SESSION["autenticado"] = true;
        header("Location: area_reservada.php");
        exit();
    } else {
        $erro = "Utilizador ou palavra-passe incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Área Reservada - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>O Mundo da Fotografia</h1>
        <nav>
            <a href="index.html">Início</a>
            <a href="conteudos.html">Conteúdos</a>
            <a href="contacto.html">Contacto</a>
            <a href="login.php">Área Reservada</a>
        </nav>
    </header>
    <main>
        <h2>Login</h2>
        <?php if ($erro) echo "<p style='color:red;'>" . htmlspecialchars($erro) . "</p>"; ?>
        <form method="post">
            <label>Utilizador:<br><input type="text" name="utilizador"></label><br>
            <label>Palavra-passe:<br><input type="password" name="password"></label><br>
            <button type="submit">Entrar</button>
        </form>
    </main>
    <footer>
        &copy; 2024 O Mundo da Fotografia
    </footer>
</body>
</html>