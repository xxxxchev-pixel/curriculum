<section class="product-description">
    <h2>Descrição</h2>
    <p>As TVs modernas oferecem imagens de alta definição, conectividade inteligente e múltiplas opções de tamanho para entretenimento em casa.</p>
    <h3>Especificações comuns</h3>
    <ul>
        <li>Tamanho: 32" a 85"</li>
        <li>Resolução: Full HD, 4K, 8K</li>
        <li>Tipo de painel: LED, OLED, QLED</li>
        <li>Smart TV: Sim</li>
        <li>Conectividade: HDMI, USB, Wi-Fi, Bluetooth</li>
        <li>Funções: HDR, comando por voz, apps integradas</li>
    </ul>
</section>
<?php
session_start();
require_once __DIR__ . '/../config.php';

// Verificar se foi selecionada uma marca
$marca_selecionada = $_GET['marca'] ?? null;

// Conectar à base de dados e obter produtos da categoria
$mysqli = db_connect();
$products = get_produtos_by_categoria($mysqli, 'TVs');
$mysqli->close();

// Organizar produtos por marca
$produtos_por_marca = [];
foreach ($products as $product) {
    $marca = $product['marca'];
    if (!isset($produtos_por_marca[$marca])) {
        $produtos_por_marca[$marca] = [];
    }
    $produtos_por_marca[$marca][] = $product;
}
ksort($produtos_por_marca);

// Definir ícones e nomes por marca
$brand_info = [
    'Samsung' => ['icon' => '📺', 'nome' => 'Samsung QLED'],
    'Sony' => ['icon' => '🎬', 'nome' => 'Sony BRAVIA'],
    'LG' => ['icon' => '📱', 'nome' => 'LG OLED'],
    'Philips' => ['icon' => '💡', 'nome' => 'Philips Ambilight']
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart TVs - GomesTech</title>
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
          
            </nav>
        </div>
    </header>

    <!-- Category Hero -->
    <section class="category-hero" style="background: linear-gradient(135deg, #1A1A1D 0%, #FF6A00 100%); padding: 3rem 0; margin-bottom: 3rem; text-align: center;">
        <div class="container">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">📺 Smart TVs</h1>
            <p style="font-size: 1.2rem; color: var(--text-muted);">Escolha a sua marca favorita</p>
            <?php if($marca_selecionada): ?>
                <a href="tvs.php" class="btn-secondary" style="display: inline-block; margin-top: 1rem;">← Voltar às Marcas</a>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!$marca_selecionada): ?>
    <!-- Brand Selection Grid -->
    <section class="brand-selection-section" style="padding: 3rem 0;">
        <div class="container">
            <div class="brands-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; max-width: 1200px; margin: 0 auto;">
                <?php foreach ($produtos_por_marca as $marca => $produtos_marca): ?>
                <a href="?marca=<?php echo urlencode($marca); ?>" class="brand-card" style="background: var(--surface); border-radius: 15px; padding: 3rem 2rem; text-align: center; transition: all 0.3s ease; text-decoration: none; border: 2px solid transparent; display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                    <div class="brand-icon" style="font-size: 4rem; margin-bottom: 1rem;">
                        <?php echo $brand_info[$marca]['icon'] ?? '📺'; ?>
                    </div>
                    <h3 style="font-size: 1.5rem; color: var(--text); margin: 0;">
                        <?php echo htmlspecialchars($brand_info[$marca]['nome'] ?? $marca); ?>
                    </h3>
                    <p style="color: var(--text-muted); margin: 0.5rem 0;">
                        <?php echo count($produtos_marca); ?> modelo<?php echo count($produtos_marca) > 1 ? 's' : ''; ?> disponível<?php echo count($produtos_marca) > 1 ? 'is' : ''; ?>
                    </p>
                    <span class="btn-primary" style="margin-top: auto; display: inline-block;">
                        Ver Produtos
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <?php else: ?>
    <!-- Products of Selected Brand -->
    <section class="products-section">
        <div class="container">
            <?php if (isset($produtos_por_marca[$marca_selecionada])): ?>
            <div class="brand-section">
                <div class="brand-header" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 3rem; padding-bottom: 1rem; border-bottom: 3px solid var(--accent); justify-content: center;">
                    <h2 style="font-size: 2.5rem; color: var(--accent); margin: 0;">
                        <?php 
                        echo $brand_info[$marca_selecionada]['icon'] ?? '📺';
                        echo ' ' . htmlspecialchars($brand_info[$marca_selecionada]['nome'] ?? $marca_selecionada); 
                        ?>
                    </h2>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($produtos_por_marca[$marca_selecionada] as $product): ?>
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
                                    <form method="post" action="../carrinho.php" style="display:inline-block; width: 100%;">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" class="btn-primary" title="Adicionar ao Carrinho">🛒 Adicionar</button>
                                    </form>
                                    <div class="product-secondary-actions">
                                        <a href="../produto.php?id=<?php echo $product['id']; ?>" class="btn-icon" title="Ver Detalhes">
                                            <span class="icon-text">👁️ Favorito</span>
                                        </a>
                                        <button onclick="addToCompare(<?php echo $product['id']; ?>)" class="btn-icon" title="Comparar">
                                            <span class="icon-text">⚖️ Comparar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
                <p style="text-align: center; font-size: 1.2rem; color: var(--text-muted);">Marca não encontrada.</p>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 3rem;">
                <a href="tvs.php" class="btn-secondary">← Voltar às Marcas</a>
                <a href="catalogo.php" class="btn-secondary" style="margin-left: 1rem;">Ver Catálogo Completo</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>GomesTech</h4>
                    <p>A sua loja de tecnologia online com os melhores preços e produtos de qualidade.</p>
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
