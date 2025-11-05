<?php
session_start();
require_once __DIR__ . '/../config.php';

// Conectar à base de dados
$mysqli = db_connect();

// Obter categoria da URL
$categoria = isset($_GET['cat']) ? $_GET['cat'] : '';
$marca_filtro = isset($_GET['marca']) ? $_GET['marca'] : '';

if (empty($categoria)) {
    header('Location: ../index.php');
    exit;
}

// Buscar produtos da categoria
if ($marca_filtro) {
    $produtos = filter_produtos($mysqli, ['categoria' => $categoria, 'marca' => $marca_filtro]);
} else {
    $produtos = get_produtos_by_categoria($mysqli, $categoria);
}

// Buscar marcas disponíveis nesta categoria
$marcas = get_marcas_by_categoria($mysqli, $categoria);

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($categoria); ?> - GomesTech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/gomestech.css">
    <link rel="stylesheet" href="../css/hamburger-menu.css">
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="header-top">
            <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
                <span>🚚 Envio Grátis em compras acima de 50€</span>
                <span>📞 Apoio ao Cliente: 800 123 456</span>
            </div>
        </div>

        <div class="header-main">
            <div class="header-container">
                <button id="sideMenuToggle" aria-label="Abrir menu de categorias" style="background:#FF6A00;color:#fff;border:none;border-radius:8px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 8px rgba(255,106,0,0.3);transition:all 0.3s ease;margin-right:16px;flex-shrink:0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                
                <a href="../index.php" class="logo">GomesTech</a>
                
                <form action="catalogo.php" method="GET" class="search-bar">
                    <input type="search" name="q" placeholder="Escreve aqui o que procuras..." aria-label="Pesquisar produtos">
                    <button type="submit" aria-label="Pesquisar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                    </button>
                </form>
                
                <div class="header-actions">
                    <a href="../carrinho.php" class="header-icon">
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
                        <a href="../conta.php" class="header-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span><?php echo htmlspecialchars(explode(' ', $_SESSION['user_nome'])[0]); ?></span>
                        </a>
                    <?php else: ?>
                        <a href="../login.php" class="header-icon btn-auth">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                <polyline points="10 17 15 12 10 7"/>
                                <line x1="15" y1="12" x2="3" y2="12"/>
                            </svg>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- MENU LATERAL -->
    <nav id="sideMenu" class="side-menu" style="position:fixed;left:-280px;top:80px;width:260px;z-index:200;background:#fff;border-right:1px solid #eee;height:calc(100vh - 80px);padding:24px 0;transition:left 0.3s ease;overflow-y:auto;box-shadow:2px 0 8px rgba(0,0,0,0.1);">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0 24px 16px;">
            <h3 style="font-size:1.1rem;color:#FF6A00;margin:0;">Menu de Categorias</h3>
            <button id="sideMenuClose" style="background:none;border:none;font-size:24px;cursor:pointer;color:#666;">×</button>
        </div>
        <ul style="list-style:none;padding:0;margin:0;">
        <?php
        $todas_categorias = get_categorias($mysqli);
        foreach($todas_categorias as $cat):
            $cat_slug = strtolower(str_replace(' ', '-', $cat));
            $is_active = ($cat === $categoria);
        ?>
            <li style="margin-bottom:8px;">
                <a href="categoria.php?cat=<?php echo urlencode($cat); ?>" style="display:flex;align-items:center;padding:12px 24px;color:<?php echo $is_active ? '#FF6A00' : '#222'; ?>;font-weight:600;text-decoration:none;background:<?php echo $is_active ? '#fff3e6' : 'transparent'; ?>;transition:background 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='<?php echo $is_active ? '#fff3e6' : 'transparent'; ?>'">
                    <img src="../img/<?php echo $cat_slug; ?>.svg" alt="<?php echo htmlspecialchars($cat); ?>" style="width:28px;height:28px;margin-right:12px;" onerror="this.style.display='none'">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
            </li>
        <?php endforeach; ?>
        </ul>
    </nav>

    <!-- CONTEÚDO PRINCIPAL -->
    <main style="margin-top:32px;padding:0 24px;max-width:1400px;margin-left:auto;margin-right:auto;">
        <!-- Breadcrumb -->
        <nav style="margin-bottom:24px;">
            <a href="../index.php" style="color:#666;text-decoration:none;">Início</a>
            <span style="margin:0 8px;color:#999;">/</span>
            <span style="color:#FF6A00;font-weight:600;"><?php echo htmlspecialchars($categoria); ?></span>
        </nav>

        <!-- Título e Filtros -->
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:32px;">
            <h1 style="font-size:2rem;font-weight:700;margin:0;"><?php echo htmlspecialchars($categoria); ?></h1>
            
            <?php if(!empty($marcas)): ?>
            <div style="display:flex;align-items:center;gap:12px;">
                <label for="marcaFilter" style="font-weight:600;color:#666;">Marca:</label>
                <select id="marcaFilter" onchange="filtrarPorMarca(this.value)" style="padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:1rem;cursor:pointer;min-width:200px;">
                    <option value="">Todas as marcas</option>
                    <?php foreach($marcas as $marca): ?>
                        <option value="<?php echo htmlspecialchars($marca); ?>" <?php echo ($marca === $marca_filtro) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($marca); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <!-- Grid de Produtos -->
        <div class="products-grid" style="margin-bottom:48px;">
            <?php if(empty($produtos)): ?>
                <div style="grid-column:1/-1;text-align:center;padding:64px 24px;">
                    <p style="font-size:1.2rem;color:#666;margin-bottom:16px;">Nenhum produto encontrado nesta categoria.</p>
                    <a href="../index.php" class="btn btn-primary">Voltar à Página Inicial</a>
                </div>
            <?php else: ?>
                <?php foreach($produtos as $product): ?>
                <div class="product-card">
                    <a href="../produto.php?id=<?php echo $product['id']; ?>" class="product-image">
                        <img src="<?php echo htmlspecialchars($product['imagem'] ?? 'https://via.placeholder.com/300/F5F5F7/1D1D1F?text=' . urlencode($product['modelo'])); ?>" 
                             alt="<?php echo htmlspecialchars($product['modelo']); ?>" loading="lazy">
                    </a>
                    <div class="product-info">
                        <span class="product-category"><?php echo htmlspecialchars($product['categoria']); ?></span>
                        <h3 class="product-title">
                            <a href="../produto.php?id=<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['marca'] . ' ' . $product['modelo']); ?>
                            </a>
                        </h3>
                        <div class="product-footer">
                            <div class="product-price-wrapper">
                                <?php if(!empty($product['preco_original']) && $product['preco_original'] > $product['preco']): ?>
                                    <span class="product-price-old">€<?php echo number_format($product['preco_original'], 2, ',', '.'); ?></span>
                                <?php endif; ?>
                                <span class="product-price">€<?php echo number_format($product['preco'], 2, ',', '.'); ?></span>
                            </div>
                            <div class="product-actions">
                                <form method="post" action="../carrinho.php">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" class="btn btn-primary btn-cart-main">🛒 Adicionar ao Carrinho</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>GomesTech</h3>
                <p style="color: rgba(0, 0, 0, 0.7); margin-top: 16px;">
                    A tua loja online de tecnologia com os melhores preços.
                </p>
            </div>
            <div class="footer-section">
                <h3>Suporte</h3>
                <ul>
                    <li><a href="#">Centro de Ajuda</a></li>
                    <li><a href="#">Contactos</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> GomesTech. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        // Menu Lateral
        const sideMenu = document.getElementById('sideMenu');
        const sideMenuToggle = document.getElementById('sideMenuToggle');
        const sideMenuClose = document.getElementById('sideMenuClose');
        let sideMenuOpen = false;

        function toggleSideMenu() {
            sideMenuOpen = !sideMenuOpen;
            sideMenu.style.left = sideMenuOpen ? '0' : '-280px';
            sideMenuToggle.style.transform = sideMenuOpen ? 'rotate(90deg)' : 'rotate(0deg)';
        }

        sideMenuToggle.addEventListener('click', toggleSideMenu);
        sideMenuClose.addEventListener('click', toggleSideMenu);

        document.addEventListener('click', function(e) {
            if (sideMenuOpen && !sideMenu.contains(e.target) && !sideMenuToggle.contains(e.target)) {
                toggleSideMenu();
            }
        });

        // Filtro de Marca
        function filtrarPorMarca(marca) {
            const urlParams = new URLSearchParams(window.location.search);
            const categoria = urlParams.get('cat');
            
            if (marca) {
                window.location.href = `categoria.php?cat=${encodeURIComponent(categoria)}&marca=${encodeURIComponent(marca)}`;
            } else {
                window.location.href = `categoria.php?cat=${encodeURIComponent(categoria)}`;
            }
        }
    </script>
</body>
</html>
<?php $mysqli->close(); ?>
