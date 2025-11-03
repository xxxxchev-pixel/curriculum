<?php
// Exibir todos os erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Exibir erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/config.php';

// Conectar à base de dados
$mysqli = db_connect();
$products = get_all_produtos($mysqli);

// Se não houver produtos na base de dados (por exemplo em ambiente local sem importar o JSON),
// tentar um fallback para o ficheiro data/catalogo_completo.json para permitir visualização rápida.
if (empty($products)) {
    $json_file = __DIR__ . '/data/catalogo_completo.json';
    if (file_exists($json_file)) {
        $json = json_decode(file_get_contents($json_file), true);
        if (!empty($json['produtos']) && is_array($json['produtos'])) {
            $products = [];
            // Usar ids negativos para distinguir dos que estariam na BD
            $next_id = -1;
            foreach ($json['produtos'] as $p) {
                $products[] = [
                    'id' => $next_id--,
                    'categoria' => $p['categoria'] ?? ($p['categoria_nome'] ?? ''),
                    'marca' => $p['marca'] ?? ($p['marca_nome'] ?? ''),
                    'modelo' => $p['modelo'] ?? ($p['nome'] ?? ''),
                    'preco' => isset($p['preco']) ? (float)$p['preco'] : 0.00,
                    'preco_original' => isset($p['preco_original']) ? (float)$p['preco_original'] : null,
                    'imagem' => $p['imagem_url'] ?? $p['imagem'] ?? '',
                    'descricao' => $p['descricao'] ?? '',
                    'loja' => $p['loja'] ?? '',
                ];
            }
        }
    }
}

// Produtos em destaque (8 produtos)
$featured_products = array_slice($products, 0, 8);

// Ofertas especiais (produtos com desconto)
$special_offers = array_slice($products, 8, 4);

// Buscar produto do dia (se existir)
$produto_dia = null;
$check_column = $mysqli->query("SHOW COLUMNS FROM produtos LIKE 'produto_dia'");
if ($check_column && $check_column->num_rows > 0) {
    $result_dia = $mysqli->query("SELECT * FROM produtos WHERE produto_dia = TRUE LIMIT 1");
    if ($result_dia && $result_dia->num_rows > 0) {
        $produto_dia = $result_dia->fetch_assoc();
    }
}

// NÃO FECHAR mysqli aqui - precisamos para o popup
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GomesTech - Tecnologia ao Melhor Preço</title>
    <meta name="description" content="Loja online de tecnologia com os melhores preços em smartphones, laptops, TVs e muito mais.">
    
    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Unificado -->
    <link rel="stylesheet" href="css/gomestech.css">
    <link rel="stylesheet" href="css/hamburger-menu.css">
    
    <style>
        /* ===== POP-UP PRODUTO DO DIA ===== */
        .produto-dia-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        .produto-dia-modal {
            background: white;
            border-radius: 20px;
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.4s ease;
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(50px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .produto-dia-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #FF6A00;
            font-size: 24px;
            color: #FF6A00;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .produto-dia-close:hover {
            background: #FF6A00;
            color: white;
            transform: rotate(90deg);
        }
        
        .produto-dia-content {
            padding: 40px;
        }
        
        .produto-dia-badge {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #000;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .produto-dia-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }
        
        .produto-dia-image {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F5F5F7;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .produto-dia-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
        }
        
        .produto-dia-titulo {
            font-size: 28px;
            font-weight: 700;
            color: #1D1D1F;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        
        .produto-dia-descricao {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .produto-dia-preco-box {
            background: linear-gradient(135deg, #F5F5F7 0%, #E8E8EA 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .produto-dia-desconto {
            background: #FF3B30;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .produto-dia-preco-old {
            font-size: 20px;
            color: #999;
            text-decoration: line-through;
            margin-bottom: 5px;
        }
        
        .produto-dia-preco {
            font-size: 40px;
            font-weight: 800;
            color: #FF6A00;
        }
        
        .produto-dia-acoes {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .btn-produto-dia-comprar {
            flex: 1;
            background: linear-gradient(135deg, #FF6A00 0%, #FF8A3D 100%);
            color: white;
            padding: 16px 24px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 106, 0, 0.3);
        }
        
        .btn-produto-dia-comprar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 106, 0, 0.4);
        }
        
        .btn-produto-dia-ver {
            padding: 16px 24px;
            border: 2px solid #FF6A00;
            background: white;
            color: #FF6A00;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .btn-produto-dia-ver:hover {
            background: #FF6A00;
            color: white;
            transform: translateY(-2px);
        }
        
        .produto-dia-timer {
            background: #FFF3CD;
            border-left: 4px solid #FFC107;
            padding: 12px 16px;
            border-radius: 8px;
            color: #856404;
            font-weight: 600;
            text-align: center;
        }
        
        /* Responsivo */
        @media (max-width: 768px) {
            .produto-dia-modal {
                width: 95%;
                max-height: 95vh;
            }
            
            .produto-dia-content {
                padding: 30px 20px;
            }
            
            .produto-dia-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .produto-dia-titulo {
                font-size: 22px;
            }
            
            .produto-dia-preco {
                font-size: 32px;
            }
            
            .produto-dia-acoes {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    
  


    <!-- ===== HEADER ===== -->
    <header class="header">
        <!-- Top Bar -->
        <div class="header-top">
            <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
                <span>🚚 Envio Grátis em compras acima de 50€</span>
                <span>📞 Apoio ao Cliente: 800 123 456</span>
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
                <a href="index.php" class="logo">
                    GomesTech
                
                </a>
                
                <!-- Search Bar -->
                <form action="categorias/catalogo.php" method="GET" class="search-bar">
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
                    
                    <!-- Catálogo -->
                    <a href="categorias/catalogo.php" class="header-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                        </svg>
                        <span>Catálogo</span>
                    </a>
                    
                    <!-- Comparação -->
                    <a href="comparacao.php" class="header-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 15v6M12 9v12M6 3v18"/>
                        </svg>
                        <span>Comparar</span>
                    </a>
                    
                    <!-- Favoritos -->
                    <a href="favoritos.php" class="header-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 22l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
                        </svg>
                        <span>Favoritos</span>
                    </a>
                    
                    <!-- Carrinho -->
                    <a href="carrinho.php" class="header-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="badge"><?php echo count($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- User Account -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="conta.php" class="header-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span><?php echo htmlspecialchars(explode(' ', $_SESSION['user_nome'])[0]); ?></span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="header-icon btn-auth">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                <polyline points="10 17 15 12 10 7"/>
                                <line x1="15" y1="12" x2="3" y2="12"/>
                            </svg>
                            <span>Login e Registo</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        


    <!-- ===== POP-UP PRODUTO DO DIA ===== -->
    <?php if ($produto_dia): ?>
    <div class="produto-dia-overlay" id="produtoDiaOverlay">
        <div class="produto-dia-modal">
            <button class="produto-dia-close" onclick="fecharProdutoDia()">✕</button>
            
            <div class="produto-dia-content">
                <div class="produto-dia-badge">⭐ PRODUTO DO DIA</div>
                
                <div class="produto-dia-grid">
                    <div class="produto-dia-image">
                        <img src="<?php echo htmlspecialchars($produto_dia['imagem'] ?? 'https://via.placeholder.com/400/F5F5F7/1D1D1F?text=' . urlencode($produto_dia['modelo'])); ?>" 
                             alt="<?php echo htmlspecialchars($produto_dia['modelo']); ?>">
                    </div>
                    
                    <div class="produto-dia-info">
                        <h2 class="produto-dia-titulo">
                            <?php echo htmlspecialchars($produto_dia['marca'] . ' ' . $produto_dia['modelo']); ?>
                        </h2>
                        
                        <p class="produto-dia-descricao">
                            <?php echo htmlspecialchars($produto_dia['descricao'] ?? 'Oferta especial válida apenas hoje! Não perca esta oportunidade única.'); ?>
                        </p>
                        
                        <div class="produto-dia-preco-box">
                            <?php if(!empty($produto_dia['preco_original']) && $produto_dia['preco_original'] > $produto_dia['preco']): 
                                $desconto_percentual = round((($produto_dia['preco_original'] - $produto_dia['preco']) / $produto_dia['preco_original']) * 100);
                            ?>
                                <div class="produto-dia-desconto">-<?php echo $desconto_percentual; ?>%</div>
                                <div class="produto-dia-preco-old">€<?php echo number_format($produto_dia['preco_original'], 2, ',', '.'); ?></div>
                            <?php endif; ?>
                            <div class="produto-dia-preco">€<?php echo number_format($produto_dia['preco'], 2, ',', '.'); ?></div>
                        </div>
                        
                        <div class="produto-dia-acoes">
                            <form method="post" action="carrinho.php" style="flex: 1;">
                                <input type="hidden" name="id" value="<?php echo $produto_dia['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn-produto-dia-comprar">
                                    🛒 Adicionar ao Carrinho
                                </button>
                            </form>
                            <a href="produto.php?id=<?php echo $produto_dia['id']; ?>" class="btn-produto-dia-ver">
                                Ver Detalhes →
                            </a>
                        </div>
                        
                        <div class="produto-dia-timer">
                            ⏰ Oferta válida apenas hoje!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>


    <!-- ===== HERO BANNER ===== -->
    <section class="hero-slider">
        <!-- Slide 1: Black Friday -->
        <div class="hero-slide active" style="background: linear-gradient(135deg, #FF6A00 0%, #FF8A3D 100%);">
            <div class="hero-content">
                <h1 class="hero-title">Black Friday 2025</h1>
                <p class="hero-subtitle">Descontos até 50% em tecnologia premium. Ofertas exclusivas por tempo limitado!</p>
                <a href="categorias/catalogo.php" class="btn btn-primary" style="background: white; color: #FF6A00; display: inline-flex; font-size: 1.125rem;">
                    Ver Ofertas 🔥
                </a>
            </div>
            <div class="hero-image">
                <img src="https://via.placeholder.com/500x400/FF6A00/FFFFFF?text=Black+Friday" alt="Black Friday 2025" class="animate-float">
            </div>
        </div>
        
        <!-- Slide 2: Envio Grátis -->
        <div class="hero-slide" style="background: linear-gradient(135deg, #4CAF50 0%, #81C784 100%);">
            <div class="hero-content">
                <h1 class="hero-title">🚚 Envio Grátis</h1>
                <p class="hero-subtitle">Em todas as compras acima de 50€. Recebe em 24-48h!</p>
                <a href="categorias/catalogo.php" class="btn btn-primary" style="background: white; color: #4CAF50; display: inline-flex; font-size: 1.125rem;">
                    Comprar Agora
                </a>
            </div>
            <div class="hero-image">
                <img src="https://via.placeholder.com/500x400/4CAF50/FFFFFF?text=Envio+Gratis" alt="Envio Grátis" class="animate-float">
            </div>
        </div>
        
        <!-- Slide 3: Tecnologia Acessível -->
        <div class="hero-slide" style="background: linear-gradient(135deg, #2196F3 0%, #64B5F6 100%);">
            <div class="hero-content">
                <h1 class="hero-title">💰 Preços Acessíveis</h1>
                <p class="hero-subtitle">A melhor tecnologia ao alcance de todos. Preços reduzidos em toda a loja!</p>
                <a href="categorias/catalogo.php" class="btn btn-primary" style="background: white; color: #2196F3; display: inline-flex; font-size: 1.125rem;">
                    Explorar Catálogo
                </a>
            </div>
            <div class="hero-image">
                <img src="https://via.placeholder.com/500x400/2196F3/FFFFFF?text=Precos+Baixos" alt="Preços Acessíveis" class="animate-float">
            </div>
        </div>
        
        <!-- Slider Dots -->
        <div class="slider-dots">
            <button class="slider-dot active" aria-label="Slide 1"></button>
            <button class="slider-dot" aria-label="Slide 2"></button>
            <button class="slider-dot" aria-label="Slide 3"></button>
        </div>
    </section>

    <!-- ===== CATEGORIAS (Ícones Circulares) ===== -->
    <section class="categories-section">
        <div class="section-header">
            <h2 class="section-title">Compra por Categoria</h2>
        </div>
        
        <div class="categories-grid">
            <!-- Quick category links removed as requested -->
        </div>
    </section>

    <!-- ===== OFERTAS ESPECIAIS ===== -->
    <section class="products-section" style="background: linear-gradient(135deg, #FFB800 0%, #FF6600 100%); padding: 48px 24px; border-radius: 16px; margin-top: 48px;">
        <div class="section-header" style="color: white; max-width: 1400px; margin: 0 auto;">
            <h2 class="section-title" style="color: white;">⚡ Ofertas Relâmpago</h2>
            <span style="font-size: 1.5rem; font-weight: 700; color: white;">
                Termina em: <span id="countdown">23:59:42</span>
            </span>
        </div>
        
        <div class="products-grid" style="max-width: 1400px; margin: 24px auto 0; justify-content: center;">
            <?php foreach($special_offers as $product): ?>
            <div class="product-card">
                <a href="produto.php?id=<?php echo $product['id']; ?>" class="product-image">
                    <img 
                        src="<?php echo htmlspecialchars($product['imagem'] ?? 'https://via.placeholder.com/300/F5F5F7/1D1D1F?text=' . urlencode($product['modelo'])); ?>" 
                        alt="<?php echo htmlspecialchars($product['modelo']); ?>"
                        loading="lazy"
                    >
                </a>
                
                <div class="product-info">
                    <span class="product-category"><?php echo htmlspecialchars($product['categoria']); ?></span>
                    <h3 class="product-title">
                           <a href="produto.php?id=<?php echo $product['id']; ?>">
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
                            <form method="post" action="carrinho.php" style="width: 100%;">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-primary">
                                    🛒 Adicionar
                                </button>
                            </form>
                            
                            <div class="product-secondary-actions">
                                <button class="btn-icon favorite-btn" data-id="<?php echo $product['id']; ?>" title="Adicionar aos favoritos">
                                    ❤️ <span class="icon-text">Favorito</span>
                                </button>
                                <button class="btn-icon compare-btn" data-id="<?php echo $product['id']; ?>" title="Comparar">
                                    ⚖️ <span class="icon-text">Comparar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ===== PRODUTOS EM DESTAQUE ===== -->
    <section class="products-section">
        <div class="section-header">
            <h2 class="section-title">🔥 Produtos em Destaque</h2>
            <a href="categorias/catalogo.php" class="btn btn-secondary">Ver Todos →</a>
        </div>
        
        <div class="products-grid">
            <?php foreach($featured_products as $product): ?>
            <div class="product-card">
                <a href="produto.php?id=<?php echo $product['id']; ?>" class="product-image">
                    <img 
                        src="<?php echo htmlspecialchars($product['imagem'] ?? 'https://via.placeholder.com/300/F5F5F7/1D1D1F?text=' . urlencode($product['modelo'])); ?>" 
                        alt="<?php echo htmlspecialchars($product['modelo']); ?>"
                        loading="lazy"
                    >
                </a>
                
                <div class="product-info">
                    <span class="product-category"><?php echo htmlspecialchars($product['categoria']); ?></span>
                    <h3 class="product-title">
                           <a href="produto.php?id=<?php echo $product['id']; ?>">
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
                            <form method="post" action="carrinho.php" style="width: 100%;">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-primary">
                                    🛒 Adicionar
                                </button>
                            </form>
                            
                            <div class="product-secondary-actions">
                                <button class="btn-icon favorite-btn" data-id="<?php echo $product['id']; ?>" title="Adicionar aos favoritos">
                                    ❤️ <span class="icon-text">Favorito</span>
                                </button>
                                <button class="btn-icon compare-btn" data-id="<?php echo $product['id']; ?>" title="Comparar">
                                    ⚖️ <span class="icon-text">Comparar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3> GomesTech</h3>
                <p style="color: rgba(0, 0, 0, 0.7); margin-top: 16px;">
                    A tua loja online de tecnologia com os melhores preços e atendimento de qualidade.
                </p>
            </div>
            
            <div class="footer-section">
                <h3>Compras</h3>
                <ul>
                    <li><a href="categorias/catalogo.php">Catálogo</a></li>
                    <li><a href="categorias/smartphones.php">Smartphones</a></li>
                    <li><a href="categorias/laptops.php">Laptops</a></li>
                    <li><a href="comparacao.php">Comparar Produtos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Suporte</h3>
                <ul>
                    <li><a href="#">Centro de Ajuda</a></li>
                    <li><a href="#">Envios e Devoluções</a></li>
                    <li><a href="#">Garantia</a></li>
                    <li><a href="#">Contactos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Legal</h3>
                <ul>
                    <li><a href="#">Termos e Condições</a></li>
                    <li><a href="#">Política de Privacidade</a></li>
                    <li><a href="#">Livro de Reclamações</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> GomesTech. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="js/hamburger-menu.js"></script>
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
        
        // Countdown Timer
        function updateCountdown() {
            const now = new Date();
            const midnight = new Date();
            midnight.setHours(24, 0, 0, 0);
            
            const diff = midnight - now;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            const countdownEl = document.getElementById('countdown');
            if (countdownEl) {
                countdownEl.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
        }
        
        setInterval(updateCountdown, 1000);
        updateCountdown();
        
        // Add to Compare (padronizado para usar key 'compare_ids' como CSV para compatibilidade com main.js)
        function addToCompare(productId) {
            try {
                // Extrair apenas a parte numérica do productId (evita receber slugs ou valores mal formados)
                const rawId = productId;
                const match = String(rawId).match(/-?\d+/);
                const id = match ? match[0] : null;
                if (!id) {
                    showNotification('ID inválido para comparação', 'error');
                    return;
                }

                const raw = localStorage.getItem('compare_ids') || '';
                const ids = raw.split(',').filter(Boolean);

                if (ids.includes(id)) {
                    showNotification('ℹ️ Produto já está na comparação');
                    return;
                }

                if (ids.length >= 3) {
                    showNotification('❗ Só podes comparar até 3 produtos');
                    return;
                }

                ids.push(id);
                localStorage.setItem('compare_ids', ids.join(','));
                showNotification('✅ Produto adicionado à comparação!');

                // Tentar atualizar o botão flutuante sem reload; se não existir, recarregar para que main.js o atualize
                if (typeof window.updateCompareBtn === 'function') {
                    try { window.updateCompareBtn(); } catch (e) { window.location.reload(); }
                } else {
                    // Pequeno atraso para que o usuário veja a notificação antes do reload
                    setTimeout(() => window.location.reload(), 350);
                }
            } catch (e) {
                console.error('Erro ao adicionar à comparação', e);
                showNotification('Erro ao adicionar à comparação', 'error');
            }
        }
        
        // Add to Favorites
        function addToFavorites(productId) {
            let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            if (!favorites.includes(productId)) {
                favorites.push(productId);
                localStorage.setItem('favorites', JSON.stringify(favorites));
                showNotification('❤️ Adicionado aos favoritos!');
            } else {
                favorites = favorites.filter(id => id !== productId);
                localStorage.setItem('favorites', JSON.stringify(favorites));
                showNotification('💔 Removido dos favoritos');
            }
        }
        
        // Notification System
        function showNotification(message) {
            const isDark = document.body.classList.contains('dark-mode');
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: ${isDark ? '#242424' : '#FFFFFF'};
                color: ${isDark ? '#E8E8E8' : '#2C2C2C'};
                padding: 16px 24px;
                border-radius: 12px;
                box-shadow: ${isDark ? '0 8px 24px rgba(0,0,0,0.5)' : '0 8px 24px rgba(0,0,0,0.15)'};
                border: 1px solid ${isDark ? '#333333' : '#E0E0E0'};
                z-index: 10000;
                animation: slideIn 0.3s ease-out;
                font-weight: 600;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Slider Auto-rotate (2.5 segundos)
        let currentSlide = 0;
        const dots = document.querySelectorAll('.slider-dot');
        const slides = document.querySelectorAll('.hero-slide');
        
        function rotateSlider() {
            // Remover active de todos
            dots.forEach(dot => dot.classList.remove('active'));
            slides.forEach(slide => slide.classList.remove('active'));
            
            // Avançar para próximo slide
            currentSlide = (currentSlide + 1) % dots.length;
            
            // Adicionar active ao slide atual
            dots[currentSlide].classList.add('active');
            if (slides[currentSlide]) {
                slides[currentSlide].classList.add('active');
            }
        }
        
        // Iniciar rotação automática a cada 2.5 segundos
        setInterval(rotateSlider, 2500);
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                dots.forEach(d => d.classList.remove('active'));
                slides.forEach(s => s.classList.remove('active'));
                dot.classList.add('active');
                if (slides[index]) {
                    slides[index].classList.add('active');
                }
                currentSlide = index;
            });
        });
        
        // Pop-up Produto do Dia
        function fecharProdutoDia() {
            const overlay = document.getElementById('produtoDiaOverlay');
            if (overlay) {
                overlay.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    overlay.style.display = 'none';
                    // Salvar no sessionStorage para não mostrar novamente nesta sessão
                    sessionStorage.setItem('produtoDiaVisto', 'true');
                }, 300);
            }
        }
        
        // Mostrar pop-up se existir e não foi visto nesta sessão
        window.addEventListener('load', function() {
            const overlay = document.getElementById('produtoDiaOverlay');
            if (overlay && !sessionStorage.getItem('produtoDiaVisto')) {
                overlay.style.display = 'flex';
            } else if (overlay) {
                overlay.style.display = 'none';
            }
        });
        
        // Fechar ao clicar fora do modal
        document.addEventListener('click', function(e) {
            const overlay = document.getElementById('produtoDiaOverlay');
            if (e.target === overlay) {
                fecharProdutoDia();
            }
        });
        
        // Fechar com tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                fecharProdutoDia();
            }
        });
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
    <script src="js/main.js"></script>
    <script src="js/comparison.js"></script>
</body>
</html>
<?php
// Fechar conexão no final
$mysqli->close();
?>
