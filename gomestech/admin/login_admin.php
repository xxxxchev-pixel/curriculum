<?php
// Página de login simples para a área admin
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Login</title>
    <link rel="stylesheet" href="../css/gomestech.css">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg-primary); }
        .login-box { max-width: 400px; width: 100%; background: var(--bg-card); padding: 40px; border-radius: 12px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); }
        .login-box h2 { text-align: center; color: var(--color-primary); margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); }
        .form-group input { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-secondary); color: var(--text-primary); font-size: 16px; }
        .form-group input:focus { outline: none; border-color: var(--color-primary); }
        .btn-primary { width: 100%; padding: 14px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-primary:hover { background: var(--color-primary-hover); transform: translateY(-2px); }
        .login-hint { color: var(--text-secondary); font-size: 0.9rem; margin-top: 15px; text-align: center; }
        .error-msg { color: #ff4444; text-align: center; margin-bottom: 20px; padding: 12px; background: rgba(255, 68, 68, 0.1); border-radius: 8px; border: 1px solid rgba(255, 68, 68, 0.3); }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 Área Administrativa</h2>
        <?php if (isset($_POST['password']) && !isset($show_db_notice)): ?>
            <p class="error-msg">❌ Password incorreta</p>
        <?php endif; ?>
        <form method="POST" action="index.php">
            <div class="form-group">
                <label for="email">Email (opcional)</label>
                <input type="email" id="email" name="email" placeholder="admin@exemplo.local (opcional)">
                <small style="display:block;margin-top:8px;color:var(--text-secondary)">Se preencher o email autenticaremos contra a tabela <code>users</code>. Se deixar vazio, será usada a password global.</small>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required autofocus>
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>
        <p class="login-hint">💡 Password padrão: admin123@#</p>
    </div>
</body>
</html>
