<?php
session_start();
require_once __DIR__ . '/../config.php';

// Se já está autenticado, redirecionar
if (is_authenticated()) {
    $next = $_GET['next'] ?? '../conta.php';
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
            $next = $_GET['next'] ?? '../conta.php';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/gomestech.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            font-family: 'Inter', sans-serif;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .auth-card {
            width: 100%;
            max-width: 480px;
            background: white;
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .auth-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin: 0 0 12px 0;
            background: linear-gradient(135deg, #FF6A00, #FF8534);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }
        
        .auth-header p {
            color: #666;
            margin: 0;
            font-size: 15px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1D1D1F;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            background: #F8F9FA;
            border: 2px solid #E5E5E7;
            border-radius: 12px;
            color: #1D1D1F;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #FF6A00;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 106, 0, 0.1);
            transform: translateY(-1px);
        }

        .form-group input::placeholder {
            color: #999;
        }
        
        .error-message {
            background: rgba(220, 53, 69, 0.1);
            border: 2px solid rgba(220, 53, 69, 0.3);
            color: #DC3545;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message::before {
            content: "⚠️";
            font-size: 18px;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 28px;
            border-top: 1px solid #E5E5E7;
        }

        .auth-footer p {
            margin: 0 0 8px 0;
            color: #666;
            font-size: 14px;
        }
        
        .auth-footer a {
            color: #FF6A00;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .auth-footer a:hover {
            color: #E55D00;
            text-decoration: underline;
        }
        
        .btn-auth {
            width: 100%;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            background: linear-gradient(135deg, #FF6A00, #FF8534);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            box-shadow: 0 6px 20px rgba(255, 106, 0, 0.3);
            letter-spacing: 0.3px;
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(255, 106, 0, 0.4);
            background: linear-gradient(135deg, #E55D00, #FF6A00);
        }

        .btn-auth:active {
            transform: translateY(0);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #666;
            text-decoration: none;
            margin-bottom: 32px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 8px;
        }
        
        .back-link:hover {
            color: #FF6A00;
            background: rgba(255, 106, 0, 0.05);
            transform: translateX(-2px);
        }
        
        @media (max-width: 768px) {
            .auth-container {
                padding: 24px 16px;
            }
            
            .auth-card {
                padding: 32px 24px;
                border-radius: 20px;
            }

            .auth-header h1 {
                font-size: 28px;
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
                
                <button type="submit" class="btn-auth">
                    Entrar
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Não tens conta? <a href="register.php<?php echo isset($_GET['next']) ? '?next=' . urlencode($_GET['next']) : ''; ?>">Cria uma agora</a></p>
            </div>
        </div>
    </div>
    
    <script src="../js/interactions.js"></script>
</body>
</html>
