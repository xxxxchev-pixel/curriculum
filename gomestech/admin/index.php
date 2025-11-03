<?php
// Temporarily enable error display to help debug Internal Server Error (development only)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config.php';

// Register shutdown handler to capture fatal errors and write them to admin/error_debug.log
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err) {
        $msg = sprintf("[%s] %s in %s on line %d\n", date('Y-m-d H:i:s'), $err['message'], $err['file'] ?? 'n/a', $err['line'] ?? 0);
        @file_put_contents(__DIR__ . '/error_debug.log', $msg, FILE_APPEND);
        if (!headers_sent()) {
            echo "<pre style=\"white-space:pre-wrap;color:#900;background:#fee;padding:12px;border-radius:8px;\">" . htmlspecialchars($msg) . "</pre>";
        }
    }
});

// Autenticação
$admin_password = 'admin123@#'; // ALTERE ESTA PASSWORD!

if (isset($_POST['logout'])) {
    unset($_SESSION['admin_logged']);
    unset($_SESSION['is_admin']);
    // regenerate session id after logout
    if (function_exists('regenerate_session')) regenerate_session();
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['admin_logged'])) {
    // Se foi submetido um email, tentamos autenticar contra a tabela users
    if (isset($_POST['password'])) {
        $input_password = $_POST['password'];
        $input_email = trim($_POST['email'] ?? '');

        $authed = false;

        // Primeiro tente autenticação via base de dados se email fornecido
        if ($input_email !== '') {
            if (function_exists('authenticate_user')) {
                $db_user = authenticate_user($mysqli, $input_email, $input_password);
                if ($db_user && (!empty($db_user['is_admin']) || (isset($db_user['is_admin']) && $db_user['is_admin'] == 1))) {
                    // Login via DB: sinalizar session com user info
                    $_SESSION['user_id'] = intval($db_user['id']);
                    $_SESSION['user_nome'] = $db_user['nome'] ?? ($db_user['email'] ?? 'admin');
                    $_SESSION['admin_logged'] = true;
                    $_SESSION['is_admin'] = true;
                    $authed = true;
                }
            }
        }

        // Se não autenticou via DB, fallback para password global (compatibilidade)
        if (!$authed) {
            if ($input_password === $admin_password) {
                $_SESSION['admin_logged'] = true;
                $_SESSION['is_admin'] = true;
                $authed = true;
            }
        }

        if ($authed) {
            if (function_exists('regenerate_session')) regenerate_session();
            header('Location: index.php');
            exit;
        }
    }

    // Se chegou aqui, mostrar o formulário de login
    include 'login_admin.php';
    exit;
}

// Conectar à base de dados
$mysqli = db_connect();

// Carregar dados da BD
$produtos = [];
$produtos_result = $mysqli->query("SELECT * FROM produtos ORDER BY id DESC");
if ($produtos_result && $produtos_result !== false) {
    while ($row = $produtos_result->fetch_assoc()) {
        $produtos[] = $row;
    }
}

$users = [];
$users_result = $mysqli->query("SELECT * FROM users ORDER BY created_at DESC");
if ($users_result && $users_result !== false) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}

$orders = [];
$orders_result = $mysqli->query("SELECT * FROM orders ORDER BY created_at DESC");
if ($orders_result && $orders_result !== false) {
    while ($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Estatísticas
$total_produtos = count($produtos);
$total_usuarios = count($users);
$total_pedidos = count($orders);
$receita_total = 0;
foreach($orders as $order) {
    $receita_total += $order['total'] ?? 0;
}

$produtos_destaque = count(array_filter($produtos, fn($p) => $p['destaque'] ?? false));
$produtos_novidade = count(array_filter($produtos, fn($p) => $p['novidade'] ?? false));

// Produtos com stock baixo
$produtos_baixo_stock = array_filter($produtos, fn($p) => ($p['stock'] ?? 0) < 10);

// Categorias únicas
$categorias = array_unique(array_column($produtos, 'categoria'));
sort($categorias);

$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - GomesTech</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root { --sidebar-width: 250px; --header-height: 70px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--primary-bg); color: var(--text); font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: var(--sidebar-width); background: var(--card-bg); border-right: 1px solid var(--border); position: fixed; height: 100vh; overflow-y: auto; z-index: 100; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid var(--border); background: linear-gradient(135deg, var(--accent), var(--accent-hover)); }
        .sidebar-header h2 { color: white; font-size: 1.5rem; }
        .sidebar-nav { padding: 20px 0; }
        .nav-item { display: block; padding: 15px 20px; color: var(--text); text-decoration: none; transition: all 0.3s; border-left: 3px solid transparent; }
        .nav-item:hover, .nav-item.active { background: var(--secondary-bg); border-left-color: var(--accent); color: var(--accent); }
        .nav-item i, .nav-item .i { margin-right: 10px; width: 20px; display: inline-block; }
        .admin-main { margin-left: var(--sidebar-width); flex: 1; min-height: 100vh; }
        .admin-header { height: var(--header-height); background: var(--card-bg); border-bottom: 1px solid var(--border); padding: 0 30px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .admin-content { padding: 30px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 25px; position: relative; overflow: hidden; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 5px; height: 100%; background: var(--accent); }
        .stat-card h3 { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card .number { font-size: 2.5rem; font-weight: 700; color: var(--accent); margin-bottom: 10px; }
        .stat-card .trend { font-size: 0.85rem; color: var(--success); }
        .data-table { width: 100%; background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
        .data-table thead { background: var(--secondary-bg); }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); }
        .data-table th { font-weight: 600; color: var(--accent); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        .data-table tr:hover { background: var(--secondary-bg); }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: var(--accent-hover); transform: translateY(-2px); }
        .btn-danger { background: var(--error); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background: var(--success); color: white; }
        .alert-warning { background: var(--warning); color: #000; }
        .alert-error { background: var(--error); color: white; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: var(--success); color: white; }
        .badge-warning { background: var(--warning); color: #000; }
        .badge-error { background: var(--error); color: white; }
        .section-title { font-size: 1.8rem; margin-bottom: 25px; color: var(--text); border-bottom: 2px solid var(--accent); padding-bottom: 10px; }
        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .quick-action-btn { background: var(--card-bg); border: 2px solid var(--border); padding: 20px; border-radius: 12px; text-align: center; cursor: pointer; transition: all 0.3s; text-decoration: none; color: var(--text); }
        .quick-action-btn:hover { border-color: var(--accent); transform: translateY(-3px); }
        .quick-action-btn i, .quick-action-btn .i { font-size: 2rem; color: var(--accent); display: block; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>🛠️ Admin Panel</h2>
                <p style="font-size: 0.85rem; color: rgba(255,255,255,0.8); margin-top: 5px;">GomesTech</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="?page=dashboard" class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>">
                    <span class="i">📊</span> Dashboard
                </a>
                <a href="?page=produtos" class="nav-item <?= $page === 'produtos' ? 'active' : '' ?>">
                    <span class="i">📦</span> Produtos
                </a>
                <a href="?page=pedidos" class="nav-item <?= $page === 'pedidos' ? 'active' : '' ?>">
                    <span class="i">🛒</span> Pedidos
                </a>
                <a href="?page=usuarios" class="nav-item <?= $page === 'usuarios' ? 'active' : '' ?>">
                    <span class="i">👥</span> Utilizadores
                </a>
                <a href="imagens.php" class="nav-item">
                    <span class="i">📸</span> Imagens
                </a>
                <a href="../index.php" class="nav-item" target="_blank">
                    <span class="i">🌐</span> Ver Site
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="admin-main">
            <header class="admin-header">
                <h1 style="font-size: 1.5rem;">
                    <?php
                    $titles = [
                        'dashboard' => '📊 Dashboard',
                        'produtos' => '📦 Gestão de Produtos',
                        'pedidos' => '🛒 Gestão de Pedidos',
                        'usuarios' => '👥 Gestão de Utilizadores'
                    ];
                    echo $titles[$page] ?? 'Admin';
                    ?>
                </h1>
                <form method="POST" style="margin: 0;">
                    <button type="submit" name="logout" class="btn btn-danger">🚪 Sair</button>
                </form>
            </header>
            
            <div class="admin-content">
                <?php
                // Incluir página solicitada
                switch($page) {
                    case 'produtos':
                        include 'produtos.php';
                        break;
                    case 'pedidos':
                        include 'pedidos.php';
                        break;
                    case 'usuarios':
                        include 'usuarios.php';
                        break;
                    default:
                        // Dashboard
                        ?>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h3>Total de Produtos</h3>
                                <div class="number"><?= $total_produtos ?></div>
                                <div class="trend">✨ <?= $produtos_novidade ?> novidades</div>
                            </div>
                            
                            <div class="stat-card">
                                <h3>Total de Pedidos</h3>
                                <div class="number"><?= $total_pedidos ?></div>
                                <div class="trend">📈 Crescimento constante</div>
                            </div>
                            
                            <div class="stat-card">
                                <h3>Receita Total</h3>
                                <div class="number">€<?= number_format($receita_total, 2, ',', '.') ?></div>
                                <div class="trend">💰 Valor acumulado</div>
                            </div>
                            
                            <div class="stat-card">
                                <h3>Utilizadores</h3>
                                <div class="number"><?= $total_usuarios ?></div>
                                <div class="trend">👥 Clientes registados</div>
                            </div>
                        </div>
                        
                        <h2 class="section-title">⚡ Ações Rápidas</h2>
                        <div class="quick-actions">
                            <a href="?page=produtos&action=add" class="quick-action-btn">
                                <span class="i">➕</span>
                                <strong>Adicionar Produto</strong>
                            </a>
                            <a href="?page=pedidos" class="quick-action-btn">
                                <span class="i">📦</span>
                                <strong>Ver Pedidos</strong>
                            </a>
                            <a href="imagens.php" class="quick-action-btn">
                                <span class="i">📸</span>
                                <strong>Gerir Imagens</strong>
                            </a>
                            <a href="?page=produtos" class="quick-action-btn">
                                <span class="i">💰</span>
                                <strong>Alterar Preços</strong>
                            </a>
                        </div>
                        
                        <?php if(count($produtos_baixo_stock) > 0): ?>
                        <div class="alert alert-warning">
                            ⚠️ <strong>Atenção!</strong> <?= count($produtos_baixo_stock) ?> produtos com stock baixo (menos de 10 unidades)
                        </div>
                        <?php endif; ?>
                        
                        <h2 class="section-title">📦 Produtos em Destaque</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th>Preço</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $destaques = array_filter($produtos, fn($p) => $p['destaque'] ?? false);
                                $destaques = array_slice($destaques, 0, 10);
                                foreach($destaques as $produto):
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($produto['marca'] . ' ' . $produto['modelo']) ?></strong></td>
                                    <td><?= htmlspecialchars($produto['categoria']) ?></td>
                                    <td>€<?= number_format($produto['preco'], 2, ',', '.') ?></td>
                                    <td>
                                        <?php if(($produto['stock'] ?? 0) < 10): ?>
                                            <span class="badge badge-warning"><?= $produto['stock'] ?? 0 ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-success"><?= $produto['stock'] ?? 0 ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($produto['destaque'] ?? false): ?>
                                            <span class="badge badge-success">⭐ Destaque</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$mysqli->close();
?>
