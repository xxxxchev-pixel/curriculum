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
$success = '';
$form_data = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address_line1' => '',
    'city' => '',
    'postal_code' => ''
];

// Processar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    
    // Verificar CSRF
    if (!verify_csrf_token($csrf)) {
        $error = 'Sessão expirada. Por favor, tenta novamente.';
    } else {
        // Guardar dados do formulário
        $form_data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'address_line1' => trim($_POST['address_line1'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? '')
        ];
        
        // Criar utilizador
        $result = create_user($mysqli, $form_data);
        
        if ($result['success']) {
            // Autenticar automaticamente
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_nome'] = $form_data['name'];
            
            // Regenerar sessão por segurança
            session_regenerate_id(true);
            
            // Redirecionar
            $next = $_GET['next'] ?? '../conta.php';
            header('Location: ' . $next);
            exit;
            
        } else {
            if (isset($result['error']) && $result['error'] === 'email_exists') {
                // Email já existe - redirecionar para login
                $email_encoded = urlencode($form_data['email']);
                $next = urlencode($_GET['next'] ?? '');
                header('Location: login.php?email=' . $email_encoded . '&next=' . $next . '&msg=email_exists');
                exit;
            } else {
                $error = $result['error'] ?? 'Erro ao criar conta. Por favor, tenta novamente.';
            }
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
    <title>Criar Conta - GomesTech</title>
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
            max-width: 560px;
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
            margin-bottom: 36px;
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
            margin-bottom: 20px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
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
        
        .success-message {
            background: rgba(40, 167, 69, 0.1);
            border: 2px solid rgba(40, 167, 69, 0.3);
            color: #28A745;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-message::before {
            content: "✓";
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
        
        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: 6px;
            line-height: 1.4;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
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
            <div class="auth-header">
                <h1>Criar Conta</h1>
                <p>Preenche os dados para começar</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="name">Nome Completo *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required 
                        minlength="2"
                        maxlength="120"
                        value="<?php echo htmlspecialchars($form_data['name']); ?>"
                        autocomplete="name"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        value="<?php echo htmlspecialchars($form_data['email']); ?>"
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Palavra-passe *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                    <div class="password-hint">
                        Mínimo 8 caracteres, com pelo menos 1 letra e 1 número
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Telefone</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone"
                        value="<?php echo htmlspecialchars($form_data['phone']); ?>"
                        autocomplete="tel"
                        placeholder="912 345 678"
                    >
                </div>
                
                <div class="form-group">
                    <label for="address_line1">Morada</label>
                    <input 
                        type="text" 
                        id="address_line1" 
                        name="address_line1"
                        value="<?php echo htmlspecialchars($form_data['address_line1']); ?>"
                        autocomplete="address-line1"
                        placeholder="Rua, número, andar"
                    >
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">Cidade</label>
                        <input 
                            type="text" 
                            id="city" 
                            name="city"
                            value="<?php echo htmlspecialchars($form_data['city']); ?>"
                            autocomplete="address-level2"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="postal_code">Código Postal</label>
                        <input 
                            type="text" 
                            id="postal_code" 
                            name="postal_code"
                            value="<?php echo htmlspecialchars($form_data['postal_code']); ?>"
                            autocomplete="postal-code"
                            placeholder="0000-000"
                            pattern="\d{4}-?\d{3}"
                        >
                    </div>
                </div>
                
                <button type="submit" class="btn-auth">
                    Criar Conta
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Já tens conta? <a href="login.php<?php echo isset($_GET['next']) ? '?next=' . urlencode($_GET['next']) : ''; ?>">Faz login</a></p>
            </div>
        </div>
    </div>
    
    <script src="../js/interactions.js"></script>
</body>
</html>
