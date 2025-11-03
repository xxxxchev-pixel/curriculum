<?php
session_start();
require_once __DIR__ . '/../config.php';

// Conectar à base de dados e obter produtos da categoria
$mysqli = db_connect();
$products = get_produtos_by_categoria($mysqli, 'Ar Condicionado');
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ar Condicionado - GomesTech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/gomestech.css">
    <link rel="stylesheet" href="../css/catalog.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1><a href="../index.php" style="color: var(--accent); text-decoration: none;">GomesTech</a></h1>
            </div>
        </div>
    </header>

    <!-- Category Hero -->
    <section class="category-hero" style="background: linear-gradient(135deg, #1A1A1D 0%, #FF6A00 100%); padding: 3rem 0; margin-bottom: 3rem; text-align: center;">
        <div class="container">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">❄️ Ar Condicionado</h1>
            <p style="font-size: 1.2rem; color: var(--text-muted);">Daikin, Mitsubishi, LG - Split Inverter e eficiência energética</p>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="products-section">
        <div class="container">
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="../<?php echo htmlspecialchars($product['imagem'] ?? 'img/placeholder.png'); ?>" 
                             alt="<?php echo htmlspecialchars($product['marca'] . ' ' . $product['modelo']); ?>">
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?php echo htmlspecialchars($product['categoria']); ?></span>
                        <h3 class="product-title"><?php echo htmlspecialchars($product['marca'] . ' ' . $product['modelo']); ?></h3>
                        <div class="product-footer">
                            <span class="product-price"><?php echo number_format($product['preco'], 2, ',', '.'); ?>€</span>
                            <div class="product-actions">
                                <a href="../produto.php?id=<?php echo $product['id']; ?>" class="btn-primary">Ver Produto</a>
                                <button onclick="addToCompare(<?php echo $product['id']; ?>)" class="btn-icon" title="Comparar">⚖️</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="catalogo.php" class="btn-secondary">Ver Todos os Produtos</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>GomesTech</h4>
                    <p>A sua loja de tecnologia online com os melhores preços e produtos de qualidade.</p>
                </div>
                <div class="footer-col">
                    <h4>Categorias</h4>
                    <ul>
                        <!-- Quick category links removed as requested -->
                        <li><a href="aspiradores.php">Aspiradores</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 GomesTech. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" title="Voltar ao topo">↑</button>

    <script src="../js/pricing.js"></script>
    <script src="../js/comparison.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/animations.js"></script>
    <script src="../js/interactions.js"></script>
</body>
</html>
