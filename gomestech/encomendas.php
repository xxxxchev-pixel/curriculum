<?php
session_start();

// Verificar se o utilizador está logado
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect=encomendas.php');
    exit;
}

// Carregar encomendas do ficheiro JSON
$orders_file = __DIR__ . '/data/orders.json';
$orders_data = [];

if (file_exists($orders_file)) {
    $orders_data = json_decode(file_get_contents($orders_file), true);
}

// Filtrar encomendas do utilizador
$user_orders = array_filter($orders_data, function($order) {
    return isset($order['user_id']) && $order['user_id'] === $_SESSION['user']['id'];
});

// Ordenar por data (mais recente primeiro)
usort($user_orders, function($a, $b) {
    return strtotime($b['data'] ?? '2024-01-01') - strtotime($a['data'] ?? '2024-01-01');
});
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Encomendas - GomesTech</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <h1><a href="index.php" style="color: var(--accent); text-decoration: none;">GomesTech</a></h1>
                </div>
                
                <div class="header-actions">
                    <a href="conta.php" class="header-action">
                        <span class="action-icon">👤</span>
                        <span class="action-text">Conta</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Orders Section -->
    <section style="padding: 4rem 0; min-height: 70vh;">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="font-size: 2.5rem; color: var(--accent);">Minhas Encomendas</h1>
                <p style="color: var(--text-muted);">Olá, <?php echo htmlspecialchars($_SESSION['user']['nome']); ?>!</p>
            </div>
            
            <?php if (empty($user_orders)): ?>
                <div style="text-align: center; padding: 4rem 2rem; background: var(--card-bg); border-radius: 12px;">
                    <p style="font-size: 3rem; margin-bottom: 1rem;">📦</p>
                    <h3 style="color: var(--text); margin-bottom: 1rem;">Ainda não tem encomendas</h3>
                    <p style="color: var(--text-muted); margin-bottom: 2rem;">Explore a nossa loja e faça a sua primeira compra!</p>
                    <a href="catalogo.php" class="btn-primary">Ver Produtos</a>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($user_orders as $order): ?>
                        <div style="background: var(--card-bg); padding: 2rem; border-radius: 12px; border-left: 4px solid var(--accent);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
                                <div>
                                    <h3 style="color: var(--accent); margin-bottom: 0.5rem;">Encomenda #<?php echo htmlspecialchars($order['id'] ?? 'N/A'); ?></h3>
                                    <p style="color: var(--text-muted); font-size: 0.9rem;">Data: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['data'] ?? 'now'))); ?></p>
                                </div>
                                <div>
                                    <?php 
                                    $status = $order['status'] ?? 'pendente';
                                    $status_colors = [
                                        'pendente' => '#FFBB33',
                                        'processando' => '#33B5E5',
                                        'enviado' => '#00C851',
                                        'entregue' => '#00C851',
                                        'cancelado' => '#FF4444'
                                    ];
                                    $color = $status_colors[$status] ?? '#888';
                                    ?>
                                    <span style="background: <?php echo $color; ?>20; color: <?php echo $color; ?>; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div style="border-top: 1px solid var(--border); padding-top: 1rem; margin-bottom: 1rem;">
                                <?php if (isset($order['items'])): ?>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                            <span style="color: var(--text);"><?php echo htmlspecialchars($item['nome'] ?? 'Produto'); ?> (x<?php echo $item['quantidade'] ?? 1; ?>)</span>
                                            <span style="color: var(--accent); font-weight: 600;"><?php echo number_format($item['preco'] ?? 0, 2, ',', '.'); ?>€</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 2px solid var(--border); padding-top: 1rem;">
                                <span style="font-size: 1.2rem; font-weight: 700; color: var(--text);">Total:</span>
                                <span style="font-size: 1.5rem; font-weight: 700; color: var(--accent);"><?php echo number_format($order['total'] ?? 0, 2, ',', '.'); ?>€</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 GomesTech. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
