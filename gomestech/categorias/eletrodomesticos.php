<?php
session_start();
require_once __DIR__ . '/../config.php';

// Conectar à base de dados e obter produtos da categoria
$mysqli = db_connect();
$products = get_produtos_by_categoria($mysqli, 'Eletrodomésticos');
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eletrodomésticos - GomesTech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/gomestech.css">
    <link rel="stylesheet" href="../css/catalog.css">
</head>
<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <!-- Top Bar -->
        <div class="header-top">
            <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
                <span>🚚 Envio Grátis em compras acima de 50€</span>
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <span>📞 Apoio ao Cliente: 800 123 456</span>
                    <a href="../ajuda.php" style="color: inherit; text-decoration: none;">Ajuda</a>
                    <a href="../encomendas.php" style="color: inherit; text-decoration: none;">Encomendas</a>
                    <?php if(isset($_SESSION['user'])): ?>
                        <a href="../conta.php" style="color: inherit; text-decoration: none;">Conta</a>
                    <?php else: ?>
                        <a href="../login.php" class="btn-auth" style="background: var(--accent); color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600;">Login e Registo</a>
                    <?php endif; ?>
                    <a href="../registo.php" style="color: inherit; text-decoration: none;">Criar Conta</a>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div class="header-main">
            <div class="header-container">
                <!-- Hamburger Toggle -->
                <button class="hamburger-toggle" aria-label="Abrir menu de navegação" aria-expanded="false" aria-controls="hamburger-menu" style="margin-right: 16px;">
                    <span class="hamburger-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                
                <!-- Logo -->
                <a href="../index.php" class="logo">
                    GomesTech
                </a>
                
                <!-- Search Bar -->
                <form action="catalogo.php" method="GET" class="search-bar">
                    <input 
                        type="search" 
                        name="q" 
                        placeholder="Escreve aqui o que procuras..." 
                        aria-label="Pesquisar produtos"
                    >
                    <button type="submit" aria-label="Pesquisar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                    </button>
                </form>
                
                <!-- Header Actions -->
                <div class="header-actions">
                    <!-- Theme Toggle -->
                    <button class="header-icon" onclick="toggleTheme()" title="Alternar tema">
                        <span id="theme-icon">☀️</span>
                    </button>
                    
                    <!-- Comparação -->
                    <a href="../comparacao.php" class="header-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 15v6M12 9v12M6 3v18"/>
                        </svg>
                        <span>Comparar</span>
                    </a>
                    
                    <!-- Favoritos -->
                    <a href="../favoritos.php" class="header-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 22l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
                        </svg>
                        <span>Favoritos</span>
                    </a>
                    
                    <!-- Carrinho -->
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
                </div>
            </div>
        </div>
    </header>

    <!-- Category Hero -->
    <section class="category-hero" style="background: linear-gradient(135deg, #FF6A00 0%, #FF8E53 100%); padding: 4rem 0; margin-bottom: 3rem;">
        <div class="container" style="text-align: center; color: white;">
            <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 800;">🏠 Eletrodomésticos</h1>
            <p style="font-size: 1.3rem; opacity: 0.95; max-width: 800px; margin: 0 auto;">Torne a sua casa mais inteligente e eficiente com os nossos eletrodomésticos de qualidade</p>
            
            <!-- Subcategories Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 3rem; max-width: 1000px; margin-left: auto; margin-right: auto;">
                <a href="ar-condicionado.php" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 2rem; border-radius: 16px; text-decoration: none; color: white; transition: all 0.3s ease; border: 2px solid rgba(255,255,255,0.3);" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">❄️</div>
                    <div style="font-weight: 700; font-size: 1.1rem;">Ar Condicionado</div>
                </a>
                
                <a href="aspiradores.php" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 2rem; border-radius: 16px; text-decoration: none; color: white; transition: all 0.3s ease; border: 2px solid rgba(255,255,255,0.3);" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🧹</div>
                    <div style="font-weight: 700; font-size: 1.1rem;">Aspiradores</div>
                </a>
                
                <a href="frigorifico.php" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 2rem; border-radius: 16px; text-decoration: none; color: white; transition: all 0.3s ease; border: 2px solid rgba(255,255,255,0.3);" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🧊</div>
                    <div style="font-weight: 700; font-size: 1.1rem;">Frigoríficos</div>
                </a>
                
                <a href="maquinas-cafe.php" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 2rem; border-radius: 16px; text-decoration: none; color: white; transition: all 0.3s ease; border: 2px solid rgba(255,255,255,0.3);" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">☕</div>
                    <div style="font-weight: 700; font-size: 1.1rem;">Máquinas de Café</div>
                </a>
                
                <a href="maquinas-lavar.php" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 2rem; border-radius: 16px; text-decoration: none; color: white; transition: all 0.3s ease; border: 2px solid rgba(255,255,255,0.3);" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">👕</div>
                    <div style="font-weight: 700; font-size: 1.1rem;">Máquinas de Lavar</div>
                </a>
                
                <a href="micro-ondas.php" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 2rem; border-radius: 16px; text-decoration: none; color: white; transition: all 0.3s ease; border: 2px solid rgba(255,255,255,0.3);" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🍽️</div>
                    <div style="font-weight: 700; font-size: 1.1rem;">Micro-ondas</div>
                </a>
            </div>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="products-section">
        <div class="container">
            <div class="section-header" style="margin-bottom: 2rem;">
                <h2 class="section-title">Todos os Eletrodomésticos</h2>
                <p style="color: #666; font-size: 1.1rem;"><?php echo count($products); ?> produtos disponíveis</p>
            </div>

            <?php if(count($products) > 0): ?>
            <div class="grid products">
                <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <div class="product-image">
                        <img loading="lazy" src="<?php echo htmlspecialchars($product['imagem'] ?? 'https://via.placeholder.com/300x300/FF6A00/FFFFFF?text=' . urlencode($product['marca'])); ?>" 
                             alt="<?php echo htmlspecialchars($product['modelo']); ?>">
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?php echo htmlspecialchars($product['categoria']); ?></span>
                        <h3 class="product-title"><?php echo htmlspecialchars($product['marca'] . ' ' . $product['modelo']); ?></h3>
                        <p class="product-brand"><?php echo htmlspecialchars($product['marca']); ?></p>
                        <div class="product-footer">
                            <span class="product-price">€<?php echo number_format($product['preco'], 2, ',', '.'); ?></span>
                            <div class="product-actions">
                                <form method="post" action="../carrinho.php" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" class="btn btn-primary" title="Adicionar ao Carrinho">🛒</button>
                                </form>
                                <a href="../produto.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary" style="padding:0.75rem 1rem;">Ver</a>
                                <button class="btn-icon compare-btn" data-id="<?php echo $product['id']; ?>" title="Comparar">⚖️</button>
                                <button class="btn-icon" onclick="addToFavorites(<?php echo $product['id']; ?>)" title="Favoritos">❤️</button>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📦</div>
                <h3>Nenhum produto disponível ainda</h3>
                <p>Em breve teremos eletrodomésticos incríveis para si!</p>
                <a href="../index.php" class="btn-primary" style="display: inline-block; margin-top: 1rem;">Voltar à Página Inicial</a>
            </div>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 3rem;">
                <a href="catalogo.php" class="btn-secondary" style="padding: 1rem 2rem; font-size: 1.1rem;">Ver Catálogo Completo</a>
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
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 GomesTech. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" title="Voltar ao topo">↑</button>

    <script src="../js/hamburger-menu.js"></script>
    <script src="../js/pricing.js"></script>
    <script src="../js/comparison.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/animations.js"></script>
    <script src="../js/interactions.js"></script>
    
    <script>
        // Theme Toggle
        function toggleTheme() {
            const icon = document.getElementById('theme-icon');
            const body = document.body;
            
            if (body.classList.contains('dark-mode')) {
                body.classList.remove('dark-mode');
                icon.textContent = '☀️';
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.add('dark-mode');
                icon.textContent = '🌙';
                localStorage.setItem('theme', 'dark');
            }
        }
        
        // Load saved theme
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
            document.getElementById('theme-icon').textContent = '🌙';
        }

        // Favoritos
        function addToFavorites(productId){
            let favorites = [];
            try { favorites = JSON.parse(localStorage.getItem('favorites')||'[]'); } catch(e){ favorites = []; }
            const id = parseInt(productId);
            if(!favorites.includes(id)){
                favorites.push(id);
                localStorage.setItem('favorites', JSON.stringify(favorites));
                notify('❤️ Adicionado aos favoritos!');
            } else {
                favorites = favorites.filter(x => x!==id);
                localStorage.setItem('favorites', JSON.stringify(favorites));
                notify('💔 Removido dos favoritos');
            }
        }

        function notify(message){
            const n = document.createElement('div');
            n.textContent = message;
            n.style.cssText = 'position:fixed;top:80px;right:20px;background:#111;color:#fff;padding:12px 16px;border-radius:10px;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.4)';
            document.body.appendChild(n);
            setTimeout(()=>{ n.remove(); }, 2200);
        }
    </script>
</body>
</html>
