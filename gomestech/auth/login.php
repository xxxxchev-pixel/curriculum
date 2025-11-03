<?php
session_start();
require_once __DIR__ . '/../config.php';

// Se já está autenticado, redirecionar
if (is_authenticated()) {
    $next = $_GET['next'] ?? '/conta.php';
    header('Location: ' . $next);
    exit;
}

$mysqli = db_connect();
$error = '';
$email = $_GET['email'] ?? '';
$msg = $_GET['msg'] ?? '';

// Mensagem se vem do registo com email duplicado
if ($msg === 'email_exists') {
    $error = 'Este email já está registado. Por favor, faz login.';
}

// Processar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    
    // Verificar CSRF
    if (!verify_csrf_token($csrf)) {
        $error = 'Sessão expirada. Por favor, tenta novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Autenticar
        $result = authenticate_user($mysqli, $email, $password);
        
        if ($result['success']) {
            // Criar sessão
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_nome'] = $result['name'];
            
            // Regenerar sessão por segurança
            session_regenerate_id(true);
            
            // Redirecionar
            $next = $_GET['next'] ?? '/conta.php';
            header('Location: ' . $next);
            exit;
            
        } else {
            $error = $result['error'];
        }
    }
}

$csrf_token = generate_csrf_token();
$mysqli->close();
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
    <link rel="stylesheet" href="../css/gomestech.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-6);
            background: var(--color-bg-primary);
        }
        
        .auth-card {
            width: 100%;
            max-width: 480px;
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: var(--space-8);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: var(--space-7);
        }
        
        .auth-header h1 {
            font-size: var(--font-size-5);
            font-weight: 700;
            margin-bottom: var(--space-2);
            background: linear-gradient(135deg, var(--color-accent-start), var(--color-accent-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .auth-header p {
            color: var(--color-text-secondary);
        }
        
        .form-group {
            margin-bottom: var(--space-5);
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: var(--space-2);
            color: var(--color-text-primary);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border);
            border-radius: 8px;
            color: var(--color-text-primary);
            font-size: var(--font-size-2);
            transition: border-color var(--duration-2) var(--ease-out);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--color-accent-start);
        }
        
        .error-message {
            background: rgba(244, 67, 54, 0.1);
            border: 1px solid rgba(244, 67, 54, 0.3);
            color: #f44336;
            padding: var(--space-4);
            border-radius: 8px;
            margin-bottom: var(--space-5);
            font-size: var(--font-size-1);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: var(--space-6);
            padding-top: var(--space-6);
            border-top: 1px solid var(--color-border);
        }
        
        .auth-footer a {
            color: var(--color-accent-start);
            text-decoration: none;
            font-weight: 600;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .btn-auth {
            width: 100%;
            padding: 14px;
            font-size: var(--font-size-2);
            font-weight: 600;
            margin-top: var(--space-4);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            color: var(--color-text-secondary);
            text-decoration: none;
            margin-bottom: var(--space-6);
            font-size: var(--font-size-1);
        }
        
        .back-link:hover {
            color: var(--color-text-primary);
        }
        
        @media (max-width: 768px) {
            .auth-container {
                padding: var(--space-4);
            }
            
            .auth-card {
                padding: var(--space-6);
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <a href="/" class="back-link">
                ← Voltar à página inicial
            </a>
            
            <div class="auth-header">
                <h1>Entrar</h1>
                <p>Acede à tua conta GomesTech</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        value="<?php echo htmlspecialchars($email); ?>"
                        autocomplete="email"
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Palavra-passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <button type="submit" class="btn-primary btn-auth">
                    Entrar
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Não tens conta? <a href="/auth/register.php<?php echo isset($_GET['next']) ? '?next=' . urlencode($_GET['next']) : ''; ?>">Cria uma agora</a></p>
            </div>
        </div>
    </div>
    
    <script src="../js/interactions.js"></script>
</body>
</html>
