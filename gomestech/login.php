<?php
session_start();
require_once __DIR__ . '/config.php';

// Se já está logado, redirecionar para index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $mysqli = db_connect();
        // Autenticar utilizador usando a função segura
        $user = authenticate_user($mysqli, $email, $password);

        if ($user) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_nome'] = $user['nome'] ?? '';
            $_SESSION['user_email'] = $user['email'] ?? '';
            // sinalizar admin se aplicável
            if (isset($user['is_admin']) && ($user['is_admin'] == 1 || $user['is_admin'] === '1')) {
                $_SESSION['is_admin'] = true;
            }

            // Redirecionar para a página guardada ou index
            $redirect = $_SESSION['redirect_after_login'] ?? $_GET['redirect'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']); // Limpar redirecionamento
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Email ou password incorretos.';
        }

        $mysqli->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GomesTech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/gomestech.css">
</head>
<body>
    <header class="header">
        <div class="header-main">
            <div class="header-container">
                <a href="index.php" class="logo">
                    GomesTech                   
                </a>
                
            </div>
        </div>
    </header>

    <div class="auth-container">
        <div class="auth-box">
            <h2>Bem-vindo! 👋</h2>
            <p class="subtitle">Inicie sessão para continuar</p>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'precisa_login'): ?>
                <div class="error-message" style="background: rgba(255, 152, 0, 0.1); border-color: rgba(255, 152, 0, 0.3); color: #ff9800;">
                    ⚠️ Para finalizar a compra, precisa de iniciar sessão ou criar uma conta.
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn-auth">Entrar</button>
            </form>
            
            <div class="auth-divider">OU</div>
            
            <div class="auth-link">
                <a href="registo.php"><strong>Criar Nova Conta</strong></a>
            </div>
            
            <div class="back-link">
                <a href="index.php">← Voltar à loja</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> GomesTech. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="js/animations.js"></script>
</body>
</html>
