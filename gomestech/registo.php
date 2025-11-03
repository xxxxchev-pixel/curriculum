<?php
session_start();
require_once __DIR__ . '/config.php';

// Se já está logado, redirecionar para index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $telefone = trim($_POST['telefone'] ?? '');
    $morada = trim($_POST['morada'] ?? '');
    $nif = trim($_POST['nif'] ?? '');
    $codigo_postal = trim($_POST['codigo_postal'] ?? '');
    
    // Validações
    if (empty($nome) || empty($email) || empty($password) || empty($password_confirm) || empty($nif) || empty($codigo_postal)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, insira um email válido.';
    } elseif (strlen($password) < 6) {
        $error = 'A password deve ter pelo menos 6 caracteres.';
    } elseif ($password !== $password_confirm) {
        $error = 'As passwords não coincidem.';
    } elseif (!preg_match('/^\d{9}$/', $nif)) {
        $error = 'O NIF deve ter 9 dígitos.';
    } elseif (!preg_match('/^\d{4}-\d{3}$/', $codigo_postal)) {
        $error = 'O Código Postal deve ter o formato XXXX-XXX (ex: 1000-001).';
    } else {
        $mysqli = db_connect();
        
        // Verificar se o email já existe
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Este email já está registado.';
        } else {
            // Registar utilizador
            $data = [
                'nome' => $nome,
                'email' => $email,
                'password' => $password,
                'telefone' => $telefone,
                'morada' => $morada,
                'nif' => $nif,
                'codigo_postal' => $codigo_postal
            ];
            
            if (register_user($mysqli, $data)) {
                // Buscar o utilizador registado para fazer login automático
                $stmt = $mysqli->prepare("SELECT id, nome, email FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                // Fazer login automático
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['user_email'] = $user['email'];
                
                $mysqli->close();
                
                // Redirecionar para a página guardada ou index
                $redirect = $_SESSION['redirect_after_login'] ?? 'index.php?registo=sucesso';
                unset($_SESSION['redirect_after_login']); // Limpar redirecionamento
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Erro ao registar. Por favor, tente novamente.';
            }
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
    <title>Criar Conta - GomesTech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/gomestech.css">
</head>
<body>
    <header class="site-header">
         <header class="header">
        <div class="header-main">
            <div class="header-container">
                <a href="index.php" class="logo">
                    GomesTech                   
                </a>
                
            </div>
        </div>
    </header>

    </header>

    <div class="auth-container">
        <div class="auth-box">
            <h2>Criar Conta ✨</h2>
            <p class="subtitle">Registe-se para uma experiência personalizada</p>
            
            <?php if ($error): ?>
                <div class="error-message">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    ✓ Conta criada com sucesso! <a href="login.php" style="color: #22c55e; text-decoration: underline; font-weight: 600;">Faça login aqui</a>
                </div>
            <?php else: ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" required 
                           value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"
                           placeholder="João Silva">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="seu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Mínimo 6 caracteres">
                    <span class="hint">Mínimo 6 caracteres</span>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirmar Password *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required
                           placeholder="Repita a password">
                </div>
                
                <div class="form-group">
                    <label for="telefone">Telefone <span style="color: var(--text-tertiary); font-weight: 400;">(opcional)</span></label>
                    <input type="tel" id="telefone" name="telefone"
                           value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>"
                           placeholder="912345678">
                </div>
                
                <div class="form-group">
                    <label for="nif">NIF *</label>
                    <input type="text" id="nif" name="nif" required
                           value="<?php echo htmlspecialchars($_POST['nif'] ?? ''); ?>"
                           placeholder="123456789"
                           maxlength="9"
                           pattern="\d{9}">
                    <span class="hint">9 dígitos</span>
                </div>
                
                <div class="form-group">
                    <label for="codigo_postal">Código Postal *</label>
                    <input type="text" id="codigo_postal" name="codigo_postal" required
                           value="<?php echo htmlspecialchars($_POST['codigo_postal'] ?? ''); ?>"
                           placeholder="1000-001"
                           pattern="\d{4}-\d{3}">
                    <span class="hint">Formato: XXXX-XXX</span>
                </div>
                
                <div class="form-group">
                    <label for="morada">Morada <span style="color: var(--text-tertiary); font-weight: 400;">(opcional)</span></label>
                    <textarea id="morada" name="morada" 
                              placeholder="Rua, número, código postal, cidade"><?php echo htmlspecialchars($_POST['morada'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn-auth">Criar Conta</button>
            </form>
            
            <?php endif; ?>
            
            <div class="auth-link">
                Já tem conta? <a href="login.php">Faça login aqui</a>
            </div>
            
            <div class="back-link">
                <a href="index.php">← Voltar à loja</a>
            </div>
        </div>
    </div>

    <script src="js/animations.js"></script>
</body>
</html>
