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
            $next = $_GET['next'] ?? '/conta.php';
            header('Location: ' . $next);
            exit;
            
        } else {
            if ($result['error'] === 'email_exists') {
                // Email já existe - redirecionar para login
                $email_encoded = urlencode($form_data['email']);
                $next = urlencode($_GET['next'] ?? '');
                header('Location: /auth/login.php?email=' . $email_encoded . '&next=' . $next . '&msg=email_exists');
                exit;
            } else {
                $error = $result['message'];
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
            max-width: 520px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-4);
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
        
        .success-message {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: #4CAF50;
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
        
        .password-hint {
            font-size: var(--font-size-0);
            color: var(--color-text-tertiary);
            margin-top: var(--space-2);
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
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
                
                <button type="submit" class="btn-primary btn-auth">
                    Criar Conta
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Já tens conta? <a href="/auth/login.php<?php echo isset($_GET['next']) ? '?next=' . urlencode($_GET['next']) : ''; ?>">Faz login</a></p>
            </div>
        </div>
    </div>
    
    <script src="../js/interactions.js"></script>
</body>
</html>
