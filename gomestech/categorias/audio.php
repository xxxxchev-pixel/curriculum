<?php
session_start();
require_once __DIR__ . '/../config.php';

// Verificar se foi selecionada uma marca
$marca_selecionada = $_GET['marca'] ?? null;

// Conectar à base de dados e obter produtos da categoria
$mysqli = db_connect();
$products = get_produtos_by_categoria($mysqli, 'Audio');
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

// Ordenar marcas alfabeticamente
ksort($produtos_por_marca);

// Definir ícones e nomes por marca
$brand_info = [
    'Apple' => ['icon' => '🍎', 'nome' => 'Apple'],
    'Sony' => ['icon' => '🎧', 'nome' => 'Sony'],
    'Bose' => ['icon' => '🔊', 'nome' => 'Bose'],
    'JBL' => ['icon' => '🎵', 'nome' => 'JBL'],
    'Sennheiser' => ['icon' => '🎼', 'nome' => 'Sennheiser'],
    'Beats' => ['icon' => '🎶', 'nome' => 'Beats']
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Áudio - GomesTech</title>
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
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">🎧 Áudio</h1>
<section class="product-description">
    <h2>Descrição</h2>
    <p>Produtos de áudio proporcionam uma experiência sonora imersiva, seja para música, chamadas ou entretenimento.</p>
    <h3>Especificações comuns</h3>
    <ul>
        <li>Tipo: Bluetooth ou com fios</li>
        <li>Autonomia: 5h a 30h (auriculares)</li>
        <li>Cancelamento de ruído: Sim/Não</li>
        <li>Microfone integrado: Sim</li>
        <li>Compatibilidade: Universal</li>
    </ul>
</section>
            <p style="font-size: 1.2rem; color: var(--text-muted);">Escolha a sua marca favorita</p>
            <?php if($marca_selecionada): ?>
                <a href="audio.php" class="btn-secondary" style="display: inline-block; margin-top: 1rem;">← Voltar às Marcas</a>
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
                        <?php echo $brand_info[$marca]['icon'] ?? '🎧'; ?>
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
    <!-- Products Grid -->
    <section class="products-section">
        <div class="container">
            <div class="products-grid">
                <?php 
                $produtos_filtrados = $produtos_por_marca[$marca_selecionada] ?? [];
                foreach ($produtos_filtrados as $product): ?>
                <div class="product-card">
                    <a href="../produto.php?id=<?php echo $product['id']; ?>" class="product-image">
                        <img src="<?php echo htmlspecialchars($product['imagem'] ?? 'https://via.placeholder.com/300/F5F5F7/1D1D1F?text=' . urlencode($product['modelo'])); ?>" 
                             alt="<?php echo htmlspecialchars($product['modelo']); ?>"
                             loading="lazy">
                    </a>
                    
                    <div class="product-info">
                        <span class="product-category"><?php echo htmlspecialchars($product['categoria']); ?></span>
                        <h3 class="product-title">
                            <a href="../produto.php?id=<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['marca'] . ' ' . $product['modelo']); ?>
                            </a>
                        </h3>
                        
                        <div class="product-footer">
                            <span class="product-price">€<?php echo number_format($product['preco'], 2, ',', '.'); ?></span>
                            
                            <div class="product-actions">
                                <form method="post" action="../carrinho.php" style="flex: 1;">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        🛒 Adicionar
                                    </button>
                                </form>
                                <button class="btn-icon compare-btn" data-id="<?php echo $product['id']; ?>" title="Comparar">⚖️</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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
                <div class="footer-col">
                    <h4>Categorias</h4>
                    <ul>
                        <!-- Quick category links removed as requested -->
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
