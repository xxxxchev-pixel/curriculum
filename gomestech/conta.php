<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$mysqli = db_connect();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $morada = trim($_POST['morada'] ?? '');
    $nif = trim($_POST['nif'] ?? '');
    $codigo_postal = trim($_POST['codigo_postal'] ?? '');
    
    if (empty($nome) || empty($email) || empty($nif) || empty($codigo_postal)) {
        $error = 'Nome, email, NIF e Código Postal são obrigatórios.';
    } elseif (!preg_match('/^\d{9}$/', $nif)) {
        $error = 'O NIF deve ter 9 dígitos.';
    } elseif (!preg_match('/^\d{4}-\d{3}$/', $codigo_postal)) {
        $error = 'O Código Postal deve ter o formato XXXX-XXX.';
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $user_id = $_SESSION['user_id'];
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Este email já está a ser usado.';
        } else {
            $stmt = $mysqli->prepare("UPDATE users SET nome = ?, email = ?, telefone = ?, morada = ?, nif = ?, codigo_postal = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $nome, $email, $telefone, $morada, $nif, $codigo_postal, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['user_nome'] = $nome;
                $_SESSION['user_email'] = $email;
                $success = 'Dados atualizados com sucesso!';
            } else {
                $error = 'Erro ao atualizar dados.';
            }
        }
    }
}

$stmt = $mysqli->prepare("SELECT nome, email, telefone, morada, nif, codigo_postal FROM users WHERE id = ?");
$user_id = $_SESSION['user_id'];
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - GomesTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/gomestech.css">
    <style>
        .account-container{max-width:900px;margin:40px auto;padding:0 20px}
        .account-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;padding:40px;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        .account-header{margin-bottom:30px;border-bottom:2px solid var(--border-color);padding-bottom:20px}
        .account-header h1{font-size:28px;margin-bottom:8px;color:var(--text-primary)}
        .form-grid{display:grid;gap:24px;grid-template-columns:1fr 1fr}
        .form-group{display:flex;flex-direction:column}
        .form-group.full-width{grid-column:1/-1}
        .form-group label{font-weight:600;margin-bottom:8px;color:var(--text-primary);font-size:14px}
        .form-group input,.form-group textarea{padding:14px 16px;border:1px solid var(--border-color);border-radius:8px;font-size:16px;background:var(--bg-secondary);color:var(--text-primary);transition:all .2s}
        .form-group input:focus,.form-group textarea:focus{outline:none;border-color:var(--color-primary);box-shadow:0 0 0 3px rgba(255,106,0,0.1)}
        .form-group textarea{resize:vertical;min-height:100px;font-family:inherit}
        .alert{padding:16px 20px;border-radius:8px;margin-bottom:24px;font-weight:500}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
        .btn-save{padding:14px 32px;background:var(--color-primary);color:white;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;transition:all .2s;grid-column:1/-1}
        .btn-save:hover{background:#e55f00;transform:translateY(-2px);box-shadow:0 4px 12px rgba(255,106,0,0.3)}
        @media(max-width:768px){
            .form-grid{grid-template-columns:1fr}
            .btn-save{grid-column:1}
        }
        .header-custom{background:var(--bg-card);padding:20px 0;border-bottom:1px solid var(--border-color);box-shadow:0 2px 8px rgba(0,0,0,0.05)}
        .header-custom .container{max-width:1400px;margin:0 auto;padding:0 24px;display:flex !important;flex-direction:row !important;align-items:center !important;justify-content:space-between !important}
        .header-custom .logo{font-size:28px;font-weight:900;color:var(--color-primary);text-decoration:none;flex-shrink:0}
        .header-icons{display:flex !important;flex-direction:row !important;gap:32px;align-items:center !important;flex-wrap:nowrap !important}
        .header-icon-item{display:flex !important;flex-direction:row !important;align-items:center;gap:8px;color:var(--text-primary);text-decoration:none;font-weight:600;font-size:15px;transition:color 0.3s ease;white-space:nowrap}
        .header-icon-item:hover{color:var(--color-primary)}
        .header-icon-item svg{flex-shrink:0}
        .btn-login-register{display:inline-flex !important;flex-direction:row !important;align-items:center;gap:10px;padding:12px 24px;background:linear-gradient(135deg,#FF6A00 0%,#FF8534 100%);color:white !important;text-decoration:none;font-weight:700;font-size:15px;border-radius:10px;box-shadow:0 4px 12px rgba(255,106,0,0.25);transition:all 0.3s ease;white-space:nowrap}
        .btn-login-register:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(255,106,0,0.35)}
    </style>
</head>
<body>
    <header class="header-custom">
        <div class="container">
            <a href="index.php" class="logo">🔶 GomesTech</a>
            <div class="header-icons">
                <a href="catalogo.php" class="header-icon-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                    </svg>
                    <span>Catálogo</span>
                </a>
                <a href="comparacao.php" class="header-icon-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 15v6M12 9v12M6 3v18"/>
                    </svg>
                    <span>Comparar</span>
                </a>
                <a href="favoritos.php" class="header-icon-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 22l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
                    </svg>
                    <span>Favoritos</span>
                </a>
                <a href="carrinho.php" class="header-icon-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="badge"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="conta.php" class="btn-login-register">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <?php echo htmlspecialchars(explode(' ',$_SESSION['user_nome'])[0]); ?>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-login-register">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <path d="M10 17l5-5-5-5"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        Login e Registo
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="account-container">
        <div class="account-card">
            <div class="account-header">
                <h1>👤 Minha Conta</h1>
                <p>Atualize os seus dados pessoais</p>
            </div>
            <?php if($success):?><div class="alert alert-success">✅ <?php echo htmlspecialchars($success);?></div><?php endif;?>
            <?php if($error):?><div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error);?></div><?php endif;?>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($user['nome']??'');?>" placeholder="João Silva">
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']??'');?>" placeholder="joao@email.com">
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($user['telefone']??'');?>" placeholder="912 345 678">
                    </div>
                    <div class="form-group">
                        <label for="nif">NIF *</label>
                        <input type="text" id="nif" name="nif" required value="<?php echo htmlspecialchars($user['nif']??'');?>" placeholder="123456789" maxlength="9" pattern="\d{9}">
                    </div>
                    <div class="form-group">
                        <label for="codigo_postal">Código Postal *</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" required value="<?php echo htmlspecialchars($user['codigo_postal']??'');?>" placeholder="1000-001" pattern="\d{4}-\d{3}">
                    </div>
                    <div class="form-group full-width">
                        <label for="morada">Morada Completa</label>
                        <textarea id="morada" name="morada" placeholder="Rua, número, andar, apartamento, cidade"><?php echo htmlspecialchars($user['morada']??'');?></textarea>
                    </div>
                    <button type="submit" class="btn-save">💾 Guardar Alterações</button>
                </div>
            </form>
        </div>
    </main>
    <footer class="footer">
        <div class="footer-bottom"><p>&copy; <?php echo date('Y');?> GomesTech. Todos os direitos reservados.</p></div>
    </footer>
</body>
</html>
