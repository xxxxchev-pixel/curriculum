<?php
// Ocultar avisos/notices em produção
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR | E_PARSE);

session_start();
require_once __DIR__ . '/config.php';

// Conectar à base de dados
$mysqli = db_connect();

// Obter filtros da URL
$categoria_filtro = isset($_GET['cat']) ? $_GET['cat'] : '';
$marca_filtro = isset($_GET['marca']) ? $_GET['marca'] : '';
$busca = isset($_GET['q']) ? $_GET['q'] : '';
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$produtos_por_pagina = 24; // 24 produtos por página (grid 4x6)

// Buscar produtos
$products = [];
if ($busca) {
    $products = get_all_produtos($mysqli);
    if (!empty($products) && is_array($products)) {
        $products = array_filter($products, function($p) use ($busca) {
            $search = strtolower($busca);
            return strpos(strtolower($p['marca'] ?? ''), $search) !== false ||
                   strpos(strtolower($p['modelo'] ?? ''), $search) !== false ||
                   strpos(strtolower($p['categoria'] ?? ''), $search) !== false;
        });
    }
} elseif ($categoria_filtro && $marca_filtro) {
    $products = filter_produtos($mysqli, ['categoria' => $categoria_filtro, 'marca' => $marca_filtro]);
} elseif ($categoria_filtro) {
    $products = get_produtos_by_categoria($mysqli, $categoria_filtro);
} else {
    $products = get_all_produtos($mysqli);
}

// Garantir que seja array
if (!is_array($products)) {
    $products = [];
}

// PAGINAÇÃO
$total_produtos = count($products);
$total_paginas = ceil($total_produtos / $produtos_por_pagina);
$pagina_atual = max(1, min($pagina_atual, $total_paginas)); // Garantir que está entre 1 e total_paginas
$offset = ($pagina_atual - 1) * $produtos_por_pagina;

// Pegar apenas os produtos da página atual
$products_pagina = array_slice($products, $offset, $produtos_por_pagina);

// Buscar categorias e marcas
$categorias = get_categorias($mysqli);
if (!is_array($categorias)) {
    $categorias = [];
}

$marcas = [];
if ($categoria_filtro) {
    $marcas = get_marcas_by_categoria($mysqli, $categoria_filtro);
    if (!is_array($marcas)) {
        $marcas = [];
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $categoria_filtro ? htmlspecialchars($categoria_filtro) . ' - ' : ''; ?>Catálogo - GomesTech</title>
    
    <!-- Preconnect para melhor performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/gomestech.css">
    <link rel="stylesheet" href="css/hamburger-menu.css">
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
                <form action="catalogo.php" method="GET" class="search-bar">
                    <input 
                        type="search" 
                        name="q" 
                        placeholder="Escreve aqui o que procuras..." 
                        value="<?php echo htmlspecialchars($busca ?? ''); ?>"
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
                    <a href="catalogo.php" class="header-icon">
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
    </header>

    <!-- MENU LATERAL DE CATEGORIAS -->
    <nav id="sideMenu" class="side-menu" style="position:fixed;left:-280px;top:80px;width:260px;z-index:200;background:#fff;border-right:1px solid #eee;height:calc(100vh - 80px);padding:24px 0;transition:left 0.3s ease;overflow-y:auto;box-shadow:2px 0 8px rgba(0,0,0,0.1);">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0 24px 16px;">
            <h3 style="font-size:1.1rem;color:#FF6A00;margin:0;">Menu de Categorias</h3>
            <button id="sideMenuClose" style="background:none;border:none;font-size:24px;cursor:pointer;color:#666;">×</button>
        </div>
        <ul style="list-style:none;padding:0;margin:0;">
        <?php
        foreach($categorias as $cat):
            $cat_slug = strtolower(str_replace(' ', '-', $cat));
            $is_active = ($categoria_filtro === $cat);
        ?>
            <li style="margin-bottom:8px;">
                <a href="catalogo.php?cat=<?php echo urlencode($cat); ?>" 
                   style="display:flex;align-items:center;padding:12px 24px;color:<?php echo $is_active ? '#FF6A00' : '#222'; ?>;font-weight:600;text-decoration:none;transition:background 0.2s;<?php echo $is_active ? 'background:#f5f5f5;' : ''; ?>" 
                   onmouseover="this.style.background='#f5f5f5'" 
                   onmouseout="this.style.background='<?php echo $is_active ? '#f5f5f5' : 'transparent'; ?>'">
                    <img src="img/<?php echo $cat_slug; ?>.svg" alt="<?php echo htmlspecialchars($cat); ?>" style="width:28px;height:28px;margin-right:12px;" onerror="this.style.display='none'">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
            </li>
        <?php endforeach; ?>
        </ul>
    </nav>

    <!-- BREADCRUMB -->
    <div style="max-width:1400px;margin:120px auto 32px;padding:0 24px;">
        <div style="font-size:0.875rem;color:#666;margin-bottom:24px;">
            <a href="index.php" style="color:#666;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#FF6A00'" onmouseout="this.style.color='#666'">Início</a>
            <span style="margin:0 8px;">/</span>
            <span style="color:#FF6A00;font-weight:600;">Catálogo</span>
            <?php if($categoria_filtro): ?>
                <span style="margin:0 8px;">/</span>
                <span style="color:#FF6A00;font-weight:600;"><?php echo htmlspecialchars($categoria_filtro); ?></span>
            <?php endif; ?>
        </div>

        <!-- TÍTULO E FILTROS -->
        <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;margin-bottom:32px;">
            <div style="flex:1;min-width:300px;">
                <h1 style="font-size:2.5rem;margin:0 0 12px;color:#1D1D1F;font-weight:700;">
                    <?php if($busca): ?>
                        Resultados para "<?php echo htmlspecialchars($busca); ?>"
                    <?php elseif($categoria_filtro): ?>
                        <?php echo htmlspecialchars($categoria_filtro); ?>
                    <?php else: ?>
                        Catálogo Completo
                    <?php endif; ?>
                </h1>
                <p style="margin:0;color:#666;font-size:1rem;">
                    <strong><?php echo $total_produtos; ?></strong> produtos encontrados
                    <?php if($total_paginas > 1): ?>
                        | Página <strong><?php echo $pagina_atual; ?></strong> de <strong><?php echo $total_paginas; ?></strong>
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- FILTROS -->
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                <!-- Filtro de Categoria -->
                <select onchange="filtrarCategoria(this.value)" style="padding:12px 16px;border:2px solid #E5E5E7;border-radius:8px;background:#fff;font-size:0.95rem;font-weight:600;min-width:200px;cursor:pointer;transition:border-color 0.3s;" onfocus="this.style.borderColor='#FF6A00'" onblur="this.style.borderColor='#E5E5E7'">
                    <option value="">📁 Todas as Categorias</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php if($categoria_filtro === $cat) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <!-- Filtro de Marca (só aparece se categoria selecionada) -->
                <?php if($categoria_filtro && !empty($marcas)): ?>
                <select onchange="filtrarMarca(this.value)" style="padding:12px 16px;border:2px solid #E5E5E7;border-radius:8px;background:#fff;font-size:0.95rem;font-weight:600;min-width:180px;cursor:pointer;transition:border-color 0.3s;" onfocus="this.style.borderColor='#FF6A00'" onblur="this.style.borderColor='#E5E5E7'">
                    <option value="">🏷️ Todas as Marcas</option>
                    <?php foreach($marcas as $marca): ?>
                        <option value="<?php echo htmlspecialchars($marca); ?>" <?php if($marca_filtro === $marca) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($marca); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                
                <!-- Botão Limpar Filtros -->
                <?php if($categoria_filtro || $marca_filtro || $busca): ?>
                <a href="catalogo.php" class="btn btn-secondary" style="padding:12px 24px;background:#F5F5F7;color:#1D1D1F;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.95rem;transition:all 0.3s;display:inline-flex;align-items:center;gap:8px;" onmouseover="this.style.background='#E5E5E7'" onmouseout="this.style.background='#F5F5F7'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Limpar Filtros
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PRODUTOS -->
    <section class="products-section" style="max-width:1400px;margin:0 auto 64px;padding:0 24px;">
        <div class="products-grid">
            <?php if(empty($products_pagina)): ?>
                <div style="grid-column:1/-1;text-align:center;padding:80px 24px;color:#666;">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 32px;opacity:0.2;">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <h2 style="font-size:2rem;margin:0 0 16px;color:#1D1D1F;font-weight:700;">Nenhum produto encontrado</h2>
                    <p style="font-size:1.125rem;margin:0 0 32px;color:#86868B;">Tenta ajustar os filtros ou procura por outros termos.</p>
                    <a href="catalogo.php" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:#FF6A00;color:#fff;border-radius:12px;text-decoration:none;font-weight:700;font-size:1rem;transition:all 0.3s;box-shadow:0 4px 12px rgba(255,106,0,0.3);" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 16px rgba(255,106,0,0.4)'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 12px rgba(255,106,0,0.3)'">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                        </svg>
                        Ver Todos os Produtos
                    </a>
                </div>
            <?php else: ?>
                <?php foreach($products_pagina as $product): ?>
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
        
        <!-- PAGINAÇÃO -->
        <?php if($total_paginas > 1): ?>
        <div style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:48px;flex-wrap:wrap;">
            <?php if($pagina_atual > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_atual - 1])); ?>" 
                   class="btn-pagination" 
                   style="padding:12px 16px;border:2px solid #E5E5E7;border-radius:8px;background:#fff;color:#1D1D1F;text-decoration:none;font-weight:600;transition:all 0.3s;display:inline-flex;align-items:center;gap:6px;"
                   onmouseover="this.style.borderColor='#FF6A00';this.style.color='#FF6A00'" 
                   onmouseout="this.style.borderColor='#E5E5E7';this.style.color='#1D1D1F'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    Anterior
                </a>
            <?php endif; ?>
            
            <?php
            // Mostrar até 7 números de página
            $inicio = max(1, $pagina_atual - 3);
            $fim = min($total_paginas, $pagina_atual + 3);
            
            // Se estiver no início, mostrar mais à frente
            if($pagina_atual <= 3) {
                $fim = min($total_paginas, 7);
            }
            // Se estiver no fim, mostrar mais atrás
            if($pagina_atual >= $total_paginas - 2) {
                $inicio = max(1, $total_paginas - 6);
            }
            
            // Primeira página
            if($inicio > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>" 
                   class="btn-pagination" 
                   style="padding:12px 16px;border:2px solid #E5E5E7;border-radius:8px;background:#fff;color:#1D1D1F;text-decoration:none;font-weight:600;transition:all 0.3s;min-width:48px;text-align:center;"
                   onmouseover="this.style.borderColor='#FF6A00';this.style.color='#FF6A00'" 
                   onmouseout="this.style.borderColor='#E5E5E7';this.style.color='#1D1D1F'">
                    1
                </a>
                <?php if($inicio > 2): ?>
                    <span style="color:#86868B;font-weight:600;padding:0 8px;">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php for($i = $inicio; $i <= $fim; $i++): ?>
                <?php if($i == $pagina_atual): ?>
                    <span class="btn-pagination active" 
                          style="padding:12px 16px;border:2px solid #FF6A00;border-radius:8px;background:#FF6A00;color:#fff;font-weight:600;min-width:48px;text-align:center;">
                        <?php echo $i; ?>
                    </span>
                <?php else: ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>" 
                       class="btn-pagination" 
                       style="padding:12px 16px;border:2px solid #E5E5E7;border-radius:8px;background:#fff;color:#1D1D1F;text-decoration:none;font-weight:600;transition:all 0.3s;min-width:48px;text-align:center;"
                       onmouseover="this.style.borderColor='#FF6A00';this.style.color='#FF6A00'" 
                       onmouseout="this.style.borderColor='#E5E5E7';this.style.color='#1D1D1F'">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <!-- Última página -->
            <?php if($fim < $total_paginas): ?>
                <?php if($fim < $total_paginas - 1): ?>
                    <span style="color:#86868B;font-weight:600;padding:0 8px;">...</span>
                <?php endif; ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $total_paginas])); ?>" 
                   class="btn-pagination" 
                   style="padding:12px 16px;border:2px solid #E5E5E7;border-radius:8px;background:#fff;color:#1D1D1F;text-decoration:none;font-weight:600;transition:all 0.3s;min-width:48px;text-align:center;"
                   onmouseover="this.style.borderColor='#FF6A00';this.style.color='#FF6A00'" 
                   onmouseout="this.style.borderColor='#E5E5E7';this.style.color='#1D1D1F'">
                    <?php echo $total_paginas; ?>
                </a>
            <?php endif; ?>
            
            <?php if($pagina_atual < $total_paginas): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_atual + 1])); ?>" 
                   class="btn-pagination" 
                   style="padding:12px 16px;border:2px solid #E5E5E7;border-radius:8px;background:#fff;color:#1D1D1F;text-decoration:none;font-weight:600;transition:all 0.3s;display:inline-flex;align-items:center;gap:6px;"
                   onmouseover="this.style.borderColor='#FF6A00';this.style.color='#FF6A00'" 
                   onmouseout="this.style.borderColor='#E5E5E7';this.style.color='#1D1D1F'">
                    Próxima
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>GomesTech</h3>
                <p style="color: rgba(0, 0, 0, 0.7); margin-top: 16px;">
                    A tua loja online de tecnologia com os melhores preços e atendimento de qualidade.
                </p>
            </div>
            
            <div class="footer-section">
                <h3>Compras</h3>
                <ul>
                    <li><a href="catalogo.php">Catálogo</a></li>
                    <li><a href="catalogo.php?cat=Smartphones">Smartphones</a></li>
                    <li><a href="catalogo.php?cat=Laptops">Laptops</a></li>
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

        // Filtros
        function filtrarCategoria(categoria) {
            const url = new URL(window.location);
            if (categoria) {
                url.searchParams.set('cat', categoria);
            } else {
                url.searchParams.delete('cat');
            }
            url.searchParams.delete('marca'); // Reset marca ao mudar categoria
            window.location = url;
        }

        function filtrarMarca(marca) {
            const url = new URL(window.location);
            if (marca) {
                url.searchParams.set('marca', marca);
            } else {
                url.searchParams.delete('marca');
            }
            window.location = url;
        }
    </script>

    <!-- Scripts principais -->
    <script src="js/main.js"></script>
    <script src="js/wishlist.js"></script>
    <script src="js/toast.js"></script>

</body>
</html>
