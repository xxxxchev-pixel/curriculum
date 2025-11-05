<?php

// Ocultar avisos/notices em produção
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR | E_PARSE);

// Corrigir warning de variável não definida
if (!isset($produto_dia)) {
    $produto_dia = null;
}

session_start();
require_once __DIR__ . '/config.php';

// Conectar à base de dados
$mysqli = db_connect();

$products = get_all_produtos($mysqli);

// Inicializar arrays de ofertas e destaque
$special_offers = [];
$featured_products = [];

if (!empty($products) && is_array($products)) {
    foreach ($products as $product) {
        // Ofertas relâmpago: produtos com desconto (preco_original > preco)
        if (!empty($product['preco_original']) && $product['preco_original'] > $product['preco']) {
            $special_offers[] = $product;
        }
        // Produtos em destaque: campo 'destaque' ou 'featured' ou todos se não existir
        if ((isset($product['destaque']) && $product['destaque']) || (isset($product['featured']) && $product['featured'])) {
            $featured_products[] = $product;
        }
    }
    // Se não houver nenhum destaque, mostrar os primeiros 8 produtos
    if (empty($featured_products)) {
        $featured_products = array_slice($products, 0, 8);
    }
    // Se não houver ofertas, mostrar os primeiros 8 produtos
    if (empty($special_offers)) {
        $special_offers = array_slice($products, 0, 8);
    }
}

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
                $p['id'] = $next_id--;
                $products[] = $p;
            }
        }
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
                <!-- Menu Lateral Toggle (Botão Laranja) -->
                <button id="sideMenuToggle" aria-label="Abrir menu de categorias" style="background:#FF6A00;color:#fff;border:none;border-radius:8px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 8px rgba(255,106,0,0.3);transition:all 0.3s ease;margin-right:16px;flex-shrink:0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
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

    <?php
    // Categorias principais para exibir na grid
    $categorias_grid = [
        'Smartphones' => 'smartphones',
        'Laptops' => 'laptops',
        'Televisores' => 'tvs',
        'Wearables' => 'wearables',
        'Tablets' => 'tablets',
        'Audio' => 'audio',
        'Auriculares' => 'auriculares',
        'Headphones' => 'headphones',
    ];
    $categoria_selecionada = isset($_GET['categoria']) ? $_GET['categoria'] : null;
    $marca_selecionada = isset($_GET['marca']) ? $_GET['marca'] : null;
    ?>
    <div class="section-header">
        <h2 class="section-title">Compra por Categoria</h2>
    </div>
    <div class="categories-grid">
        <?php foreach($categorias_grid as $cat_nome => $cat_slug): ?>
            <a href="categorias/categoria.php?cat=<?php echo urlencode($cat_nome); ?>" class="category-card">
                <img src="img/<?php echo $cat_slug; ?>.svg" alt="<?php echo htmlspecialchars($cat_nome); ?>" style="width:48px;height:48px;">
                <span><?php echo htmlspecialchars($cat_nome); ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if($categoria_selecionada): ?>
        <?php $marcas = get_marcas_by_categoria($mysqli, $categoria_selecionada); ?>
        <div class="brands-section" style="margin-top:32px;">
            <div class="section-header">
                <h2 class="section-title">Marcas em <?php echo htmlspecialchars($categoria_selecionada); ?></h2>
            </div>
            <div class="brands-grid">
                <?php foreach($marcas as $marca): ?>
                    <a href="categorias/categoria.php?cat=<?php echo urlencode($categoria_selecionada); ?>&marca=<?php echo urlencode($marca); ?>" class="brand-card<?php if($marca === $marca_selecionada) echo ' active'; ?>">
                        <img src="img/<?php echo strtolower(str_replace(' ', '-', $marca)); ?>-logo.svg" alt="<?php echo htmlspecialchars($marca); ?>" style="height:40px;">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    </section>

    <!-- MENU LATERAL DE CATEGORIAS -->
    <nav id="sideMenu" class="side-menu" style="position:fixed;left:-280px;top:80px;width:260px;z-index:200;background:#fff;border-right:1px solid #eee;height:calc(100vh - 80px);padding:24px 0;transition:left 0.3s ease;overflow-y:auto;box-shadow:2px 0 8px rgba(0,0,0,0.1);">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0 24px 16px;">
            <h3 style="font-size:1.1rem;color:#FF6A00;margin:0;">Menu de Categorias</h3>
            <button id="sideMenuClose" style="background:none;border:none;font-size:24px;cursor:pointer;color:#666;">×</button>
        </div>
        <ul style="list-style:none;padding:0;margin:0;">
        <?php
        // Buscar todas as categorias
        $todas_categorias = get_categorias($mysqli);
        foreach($todas_categorias as $cat):
            $cat_slug = strtolower(str_replace(' ', '-', $cat));
        ?>
            <li style="margin-bottom:8px;">
                <a href="categorias/categoria.php?cat=<?php echo urlencode($cat); ?>" style="display:flex;align-items:center;padding:12px 24px;color:#222;font-weight:600;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='transparent'">
                    <img src="img/<?php echo $cat_slug; ?>.svg" alt="<?php echo htmlspecialchars($cat); ?>" style="width:28px;height:28px;margin-right:12px;" onerror="this.style.display='none'">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
            </li>
        <?php endforeach; ?>
        </ul>
    </nav>

    <!-- ===== OFERTAS ESPECIAIS ===== -->
    <section class="products-section" style="background: linear-gradient(135deg, #FFB800 0%, #FF6600 100%); padding: 48px 24px; border-radius: 16px; margin-top: 48px;">
        <div class="section-header" style="color: white; max-width: 1400px; margin: 0 auto;">
            <h2 class="section-title" style="color: white;">⚡ Ofertas Relâmpago</h2>
            <span style="font-size: 1.5rem; font-weight: 700; color: white;">
                Termina em: <span id="countdown">23:59:42</span>
            </span>
        </div>
        
        <div class="products-grid" style="max-width: 1400px; margin: 24px auto 0; justify-content: center;">
            <?php
            // Filtragem de produtos por categoria e marca
            $produtos_filtrados = $special_offers;
            if ($categoria_selecionada) {
                $produtos_filtrados = array_filter($produtos_filtrados, function($p) use ($categoria_selecionada) {
                    return $p['categoria'] === $categoria_selecionada;
                });
            }
            if ($marca_selecionada) {
                $produtos_filtrados = array_filter($produtos_filtrados, function($p) use ($marca_selecionada) {
                    return $p['marca'] === $marca_selecionada;
                });
            }
            
            // Mostrar mensagem se não houver produtos
            if(empty($produtos_filtrados)):
            ?>
                <div style="grid-column:1/-1;text-align:center;padding:48px;color:#666;">
                    <p style="font-size:1.2rem;">Nenhum produto encontrado para os filtros selecionados.</p>
                    <a href="index.php" style="display:inline-block;margin-top:16px;padding:12px 24px;background:#FF6A00;color:#fff;border-radius:8px;text-decoration:none;">Limpar Filtros</a>
                </div>
            <?php else: ?>
            
            <?php foreach($produtos_filtrados as $product): ?>
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
                        <div class="product-actions" style="display: flex; flex-direction: column; align-items: center; gap: 10px; width: 100%; margin-top: 10px;">
                            <form method="post" action="carrinho.php" style="width: 100%; display: flex; justify-content: center;">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-primary btn-cart-main">
                                    🛒 Adicionar ao Carrinho
                                </button>
                            </form>
                            <div class="product-secondary-actions" style="display: flex; justify-content: center; gap: 12px; width: 100%;">
                                <button class="btn-icon btn-secondary-action favorite-btn" data-id="<?php echo $product['id']; ?>" title="Adicionar aos favoritos">
                                    ❤️ <span class="icon-text">Favorito</span>
                                </button>
                                <button class="btn-icon btn-secondary-action compare-btn" data-id="<?php echo $product['id']; ?>" title="Comparar">
                                    ⚖️ <span class="icon-text">Comparar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
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
                        <div class="product-actions" style="display: flex; flex-direction: column; align-items: center; gap: 10px; width: 100%; margin-top: 10px;">
                            <form method="post" action="carrinho.php" style="width: 100%; display: flex; justify-content: center;">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-primary btn-cart-main">
                                    🛒 Adicionar ao Carrinho
                                </button>
                            </form>
                            <div class="product-secondary-actions" style="display: flex; justify-content: center; gap: 12px; width: 100%;">
                                <button class="btn-icon btn-secondary-action favorite-btn" data-id="<?php echo $product['id']; ?>" title="Adicionar aos favoritos">
                                    ❤️ <span class="icon-text">Favorito</span>
                                </button>
                                <button class="btn-icon btn-secondary-action compare-btn" data-id="<?php echo $product['id']; ?>" title="Comparar">
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
    <script>
        // ===== MENU LATERAL TOGGLE =====
        const sideMenu = document.getElementById('sideMenu');
        const sideMenuToggle = document.getElementById('sideMenuToggle');
        const sideMenuClose = document.getElementById('sideMenuClose');
        let sideMenuOpen = false;

        function toggleSideMenu() {
            sideMenuOpen = !sideMenuOpen;
            if (sideMenuOpen) {
                sideMenu.style.left = '0';
                sideMenuToggle.style.transform = 'rotate(90deg)';
            } else {
                sideMenu.style.left = '-280px';
                sideMenuToggle.style.transform = 'rotate(0deg)';
            }
        }

        sideMenuToggle.addEventListener('click', toggleSideMenu);
        sideMenuClose.addEventListener('click', toggleSideMenu);

        // Fechar menu ao clicar fora dele
        document.addEventListener('click', function(e) {
            if (sideMenuOpen && !sideMenu.contains(e.target) && !sideMenuToggle.contains(e.target)) {
                toggleSideMenu();
            }
        });

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
