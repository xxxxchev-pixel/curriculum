<?php
session_start();
require_once __DIR__ . '/../config.php';

// Conectar à base de dados
$mysqli = db_connect();

// Configuração de paginação
$produtos_por_pagina = 16;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $produtos_por_pagina;

// Preparar filtros
$filters = [];
$categoria = $_GET['categoria'] ?? '';
$marca = $_GET['marca'] ?? '';
$min = $_GET['min'] ?? '';
$max = $_GET['max'] ?? '';
$sort = $_GET['sort'] ?? 'modelo_asc';

if (!empty($categoria)) {
    $filters['categoria'] = $categoria;
}
if (!empty($marca)) {
    $filters['marca'] = $marca;
}
if (!empty($min)) {
    $filters['min_preco'] = floatval($min);
}
if (!empty($max)) {
    $filters['max_preco'] = floatval($max);
}
if (!empty($_GET['q'])) {
    $filters['search'] = trim($_GET['q']);
}
$filters['sort'] = $sort;

// Aplicar filtros (obter todos os produtos filtrados primeiro)
$all_filtered = filter_produtos($mysqli, $filters);

// Se a base de dados não retornar produtos (ex.: ambiente local sem importação),
// tentar carregar do ficheiro JSON local e aplicar filtros em memória.
if (empty($all_filtered)) {
    $json_file = __DIR__ . '/../data/catalogo_completo.json';
    if (file_exists($json_file)) {
        $json = json_decode(file_get_contents($json_file), true);
        if (!empty($json['produtos']) && is_array($json['produtos'])) {
            $all_filtered = [];
            foreach ($json['produtos'] as $jp) {
                $item = [
                    'id' => $jp['id'] ?? null,
                    'categoria' => $jp['categoria'] ?? ($jp['categoria_nome'] ?? ''),
                    'marca' => $jp['marca'] ?? ($jp['marca_nome'] ?? ''),
                    'modelo' => $jp['modelo'] ?? ($jp['nome'] ?? ''),
                    'preco' => isset($jp['preco']) ? (float)$jp['preco'] : 0.0,
                    'preco_original' => isset($jp['preco_original']) ? (float)$jp['preco_original'] : null,
                    'imagem' => $jp['imagem_url'] ?? $jp['imagem'] ?? '',
                    'descricao' => $jp['descricao'] ?? '',
                ];

                // Aplicar filtros manuais do formulário
                $match = true;
                if (!empty($categoria)) {
                    $cat_slug = function_exists('slugify') ? slugify($item['categoria']) : strtolower(preg_replace('/[^a-z0-9]+/i','-', $item['categoria']));
                    if ($categoria !== $item['categoria'] && $categoria !== $cat_slug) $match = false;
                }
                if (!empty($marca) && strcasecmp(trim($marca), trim($item['marca'])) !== 0) {
                    $match = false;
                }
                if ($min !== '' && is_numeric($min) && $item['preco'] < floatval($min)) $match = false;
                if ($max !== '' && is_numeric($max) && $item['preco'] > floatval($max)) $match = false;
                if (!empty($search)) {
                    $s = mb_strtolower($search, 'UTF-8');
                    $hay = mb_strtolower($item['marca'] . ' ' . $item['modelo'] . ' ' . $item['descricao'], 'UTF-8');
                    if (mb_strpos($hay, $s) === false) $match = false;
                }

                if ($match) $all_filtered[] = $item;
            }
        }
    }
}
$total_produtos = count($all_filtered);
$total_paginas = ceil($total_produtos / $produtos_por_pagina);

// Pegar apenas os produtos da página atual
$filtered = array_slice($all_filtered, $offset, $produtos_por_pagina);

// Obter categorias e marcas
$all_categories = get_all_categories($mysqli);
$all_brands = get_brands_by_category($mysqli, $categoria);
// Normalizar marca recebida (usar slug para comparações)
$marca_slug = '';
if (!empty($marca)) {
    if (function_exists('slugify')) {
        $marca_slug = slugify($marca);
    } else {
        $marca_slug = strtolower(preg_replace('/[^a-z0-9]+/i','-', $marca));
    }
}
// Ordenar marcas alfabeticamente
usort($all_brands, function($a,$b){ return strcasecmp($a['name']??'', $b['name']??''); });

// Organizar categorias em principais e subcategorias
$main_categories = [];
$sub_categories = [];
foreach ($all_categories as $cat) {
    if (empty($cat['parent_id'])) {
        $main_categories[] = $cat;
    } else {
        if (!isset($sub_categories[$cat['parent_id']])) {
            $sub_categories[$cat['parent_id']] = [];
        }
        $sub_categories[$cat['parent_id']][] = $cat;
    }
}

$search = $filters['search'] ?? '';

// Utilitário simples para slug
if (!function_exists('slugify')) {
    function slugify($text){
        $text = iconv('UTF-8','ASCII//TRANSLIT',$text);
        $text = preg_replace('~[^\pL\d]+~u','-', $text);
        $text = trim($text,'-');
        $text = strtolower($text);
        $text = preg_replace('~[^-a-z0-9]+~','', $text);
        return $text ?: '';
    }
}

// Contagem de produtos por marca (ignorando filtro de marca atual)
$filters_for_counts = $filters;
unset($filters_for_counts['marca']);
$all_for_counts = filter_produtos($mysqli, $filters_for_counts);
$brand_counts = [];
foreach($all_for_counts as $prod){
    $bslug = slugify($prod['marca'] ?? '');
    if(!$bslug) continue;
    $brand_counts[$bslug] = ($brand_counts[$bslug] ?? 0) + 1;
}

// Título da página
$titulo = 'Catálogo';
if ($categoria || $marca || $search) {
    $parts = [];
    if ($categoria) $parts[] = $categoria;
    if ($marca) $parts[] = $marca;
    if ($search) $parts[] = 'Pesquisa: ' . $search;
    $titulo = implode(' · ', $parts);
}

// Função para gerar URL de paginação mantendo filtros
function get_pagination_url($page, $get_params) {
    $params = $get_params;
    $params['pagina'] = $page;
    return 'catalogo.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> - GomesTech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/gomestech.css">
    <link rel="stylesheet" href="../css/hamburger-menu.css">
    <link rel="stylesheet" href="../css/catalog.css">
</head>
<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <!-- Orange Top Bar -->
        <div class="orange-bar"></div>
        
        <!-- Top Bar -->
        <div class="header-top">
            <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
                <span>📦 Envio Grátis em compras acima de 50€</span>
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <span>📞 Apoio ao Cliente: 800 123 456</span>
                    <a href="../ajuda.php" style="color: inherit; text-decoration: none; font-weight: 500;">Ajuda</a>   
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
                        value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
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
                    <a href="catalogo.php" class="header-icon" style="background: #f8f9fa;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                        </svg>
                        <span>Catálogo</span>
                    </a>
                    
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
                    
                    <!-- Login e Registo -->
                    <a href="../login.php" class="header-icon btn-auth">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        <span>Login e Registo</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="container" style="padding: 1rem 0;">
        <div style="color: var(--text-muted); font-size: 0.9rem;">
            <?php if($categoria): ?>
                <span> / </span>
                <a href="?categoria=<?php echo urlencode($categoria); ?>" style="color: var(--accent); text-decoration: none;">
                    <?php echo htmlspecialchars($categoria); ?>
                </a>
            <?php endif; ?>
            <?php if($marca): ?>
                <span> / </span>
                <strong><?php echo htmlspecialchars($marca); ?></strong>
            <?php endif; ?>
        </div>
    </div>

    <!-- Conteúdo principal -->
    <section class="products-section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-title"><?php echo htmlspecialchars($titulo); ?></h2>
                    <p style="color: #666; font-size: 1.1rem; margin: 0.5rem 0 0 0;">
                        <?php echo $total_produtos; ?> produto<?php echo $total_produtos != 1 ? 's' : ''; ?> encontrado<?php echo $total_produtos != 1 ? 's' : ''; ?>
                    </p>
                </div>
            </div>

                        <!-- Filtros principais -->
                        <div class="filters">
                            <form method="get" class="filters-grid" autocomplete="off">
                                <select name="categoria" class="filter-control" onchange="this.form.submit()">
                                    <option value="" <?php if (empty($categoria)) echo 'selected'; ?>>Todas as Categorias</option>
                                    <?php foreach($main_categories as $cat): ?>
                                        <?php $label = $cat['name'] ?? ''; ?>
                                        <optgroup label="<?php echo htmlspecialchars($label); ?>">
                                            <option value="<?php echo htmlspecialchars($cat['slug']); ?>" <?php if($categoria === $cat['slug'] || $categoria === $cat['name']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                            <?php if(isset($sub_categories[$cat['id']])): ?>
                                                <?php foreach($sub_categories[$cat['id']] as $subcat): ?>
                                                    <option value="<?php echo htmlspecialchars($subcat['slug']); ?>" <?php if($categoria === $subcat['slug'] || $categoria === $subcat['name']) echo 'selected'; ?>>
                                                        &nbsp;&nbsp;→ <?php echo htmlspecialchars($subcat['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>

                                <select name="marca" class="filter-control" onchange="this.form.submit()">
                                    <option value="" <?php if (!isset($_GET['marca']) || $_GET['marca'] === "") echo 'selected'; ?>>Todas as Marcas</option>
                                    <?php foreach($all_brands as $brand): ?>
                                        <?php $bname = $brand['name'] ?? ''; $bslug = $brand['slug'] ?? (function_exists('slugify')?slugify($bname):strtolower(preg_replace('/[^a-z0-9]+/i','-', $bname))); ?>
                                        <option value="<?php echo htmlspecialchars($bname); ?>" <?php if(isset($_GET['marca']) && ($_GET['marca'] === $bname || $marca_slug === $bslug)) echo 'selected'; ?> >
                                            <?php echo htmlspecialchars($bname); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <input type="number" name="min" step="0.01" placeholder="Preço mínimo (€)" value="<?php echo htmlspecialchars($min); ?>" class="filter-control" />
                                <input type="number" name="max" step="0.01" placeholder="Preço máximo (€)" value="<?php echo htmlspecialchars($max); ?>" class="filter-control" />

                                <select name="sort" class="filter-control" onchange="this.form.submit()">
                                    <option value="modelo_asc" <?php if($sort === 'modelo_asc') echo 'selected'; ?>>Nome A-Z</option>
                                    <option value="modelo_desc" <?php if($sort === 'modelo_desc') echo 'selected'; ?>>Nome Z-A</option>
                                    <option value="preco_asc" <?php if($sort === 'preco_asc') echo 'selected'; ?>>Preço Crescente</option>
                                    <option value="preco_desc" <?php if($sort === 'preco_desc') echo 'selected'; ?>>Preço Decrescente</option>
                                    <option value="destaque" <?php if($sort === 'destaque') echo 'selected'; ?>>Destaques</option>
                                </select>
                            </form>
                        </div>

                                <?php if(!empty($categoria) && !empty($all_brands)): ?>
                        <div class="brand-filter-bar">
                            <div class="brand-filter-scroll">
                                <a href="?categoria=<?php echo urlencode($categoria);?>#produtos" class="brand-btn <?php echo empty($marca)?'active':''; ?>" role="button" aria-pressed="<?php echo empty($marca)?'true':'false'; ?>">🌐 Todas as marcas</a>
                                <?php foreach($all_brands as $brand): ?>
                                            <?php $bname = $brand['name'] ?? ''; $bslug = $brand['slug'] ?? (function_exists('slugify')?slugify($bname):strtolower(preg_replace('/[^a-z0-9]+/i','-', $bname))); $count = $brand_counts[$bslug] ?? null; ?>
                                            <a href="?categoria=<?php echo urlencode($categoria);?>&marca=<?php echo urlencode($bname);?>#produtos" class="brand-btn <?php echo ($marca_slug===$bslug||$marca===$bname)?'active':''; ?>" role="button" aria-pressed="<?php echo ($marca_slug===$bslug||$marca===$bname)?'true':'false'; ?>">
                                                <?php echo htmlspecialchars($bname);?><?php if($count!==null): ?> (<?php echo $count; ?>)<?php endif; ?>
                                            </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

            <!-- Grid de produtos -->
            <?php if (count($filtered) > 0): ?>
                        <section id="produtos" class="grid products">
                            <?php if(empty($filtered)): ?>
                                <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                                    <h2 style="color: var(--text-muted); font-size: 24px; margin-bottom: 12px;">😔 Nenhum produto encontrado</h2>
                                    <p style="color: var(--text-muted); margin-bottom: 24px;">Tente ajustar os filtros ou <a href="index.php" style="color: var(--color-primary);">ver todos os produtos</a></p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($filtered as $p): ?>
                                    <article class="product-card">
                                        <a href="../produto.php?id=<?php echo $p['id']; ?>" class="product-image">
                                            <img loading="lazy" 
                                                 src="<?php echo htmlspecialchars($p['imagem'] ?? 'https://via.placeholder.com/300/F5F5F7/1D1D1F?text=' . urlencode($p['modelo'])); ?>" 
                                                 alt="<?php echo htmlspecialchars($p['modelo']); ?>"
                                                 onerror="this.src='https://via.placeholder.com/300/F5F5F7/1D1D1F?text=<?php echo urlencode($p['marca']); ?>'">
                                        </a>
                                        <div class="product-info">
                                            <span class="product-category"><?php echo htmlspecialchars($p['categoria']); ?></span>
                                            <h3 class="product-title">
                                                <a href="../produto.php?id=<?php echo $p['id']; ?>">
                                                    <?php echo htmlspecialchars($p['marca'] . ' ' . $p['modelo']); ?>
                                                </a>
                                            </h3>
                                            <div class="product-footer">
                                                <div class="product-price-wrapper">
                                                    <?php if(!empty($p['preco_original']) && $p['preco_original'] > $p['preco']): ?>
                                                        <span class="product-price-old">€<?php echo number_format($p['preco_original'],2,',','.'); ?></span>
                                                    <?php endif; ?>
                                                    <span class="product-price">€<?php echo number_format($p['preco'],2,',','.'); ?></span>
                                                </div>
                                                <div class="product-actions">
                                                    <form method="post" action="../carrinho.php" style="width: 100%;">
                                                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                        <input type="hidden" name="action" value="add">
                                                        <input type="hidden" name="qty" value="1">
                                                        <button type="submit" class="btn btn-primary">
                                                            🛒 Adicionar
                                                        </button>
                                                    </form>
                                                    <div class="product-secondary-actions">
                                                        <button class="btn-icon favorite-btn" data-id="<?php echo $p['id']; ?>" title="Adicionar aos favoritos">
                                                            ❤️ <span class="icon-text">Favorito</span>
                                                        </button>
                                                        <button class="btn-icon compare-btn" data-id="<?php echo $p['id']; ?>" title="Comparar">
                                                            ⚖️ <span class="icon-text">Comparar</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </section>
            
            <!-- Paginação -->
                        <?php if($total_paginas > 1): ?>
                        <div class="pagination-wrapper">
                            <nav class="pagination">
                                <!-- Botão Anterior -->
                                <?php if($pagina_atual > 1): ?>
                                    <a href="<?php echo get_pagination_url($pagina_atual - 1, $_GET); ?>" class="pagination-btn">
                                        ← Anterior
                                    </a>
                                <?php else: ?>
                                    <span class="pagination-btn disabled">
                                        ← Anterior
                                    </span>
                                <?php endif; ?>

                                <!-- Números de página -->
                                <div class="pagination-numbers">
                                    <?php
                                    // Mostrar no máximo 7 páginas
                                    $start = max(1, $pagina_atual - 3);
                                    $end = min($total_paginas, $pagina_atual + 3);
                  
                                    // Sempre mostrar primeira página
                                    if($start > 1): ?>
                                        <a href="<?php echo get_pagination_url(1, $_GET); ?>" class="pagination-number">1</a>
                                        <?php if($start > 2): ?>
                                            <span class="pagination-ellipsis">...</span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php for($i = $start; $i <= $end; $i++): ?>
                                        <?php if($i == $pagina_atual): ?>
                                            <span class="pagination-number active"><?php echo $i; ?></span>
                                        <?php else: ?>
                                            <a href="<?php echo get_pagination_url($i, $_GET); ?>" class="pagination-number"><?php echo $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <?php if($end < $total_paginas): ?>
                                        <?php if($end < $total_paginas - 1): ?>
                                            <span class="pagination-ellipsis">...</span>
                                        <?php endif; ?>
                                        <a href="<?php echo get_pagination_url($total_paginas, $_GET); ?>" class="pagination-number"><?php echo $total_paginas; ?></a>
                                    <?php endif; ?>
                                </div>

                                <!-- Botão Próximo -->
                                <?php if($pagina_atual < $total_paginas): ?>
                                    <a href="<?php echo get_pagination_url($pagina_atual + 1, $_GET); ?>" class="pagination-btn">
                                        Próximo →
                                    </a>
                                <?php else: ?>
                                    <span class="pagination-btn disabled">
                                        Próximo →
                                    </span>
                                <?php endif; ?>
                            </nav>

                            <!-- Informação de produtos -->
                            <p class="pagination-info">
                                Mostrando <?php echo $offset + 1; ?> a <?php echo min($offset + $produtos_por_pagina, $total_produtos); ?> de <?php echo $total_produtos; ?> produtos
                            </p>
                        </div>
                        <?php endif; ?>
            
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">😕</div>
                    <h3 style="color: #666; font-size: 2rem; margin-bottom: 1rem;">Nenhum produto encontrado</h3>
                    <p style="color: #999; margin-bottom: 2rem;">Tente ajustar os filtros ou explorar outras categorias.</p>
                    <a href="../index.php#produtos" class="btn-primary" style="display: inline-block; padding: 1rem 2rem; background: linear-gradient(135deg, var(--color-primary), #ff8c42); color: white; text-decoration: none; border-radius: 12px; font-weight: 700; transition: all 0.3s ease;">Ver Todos os Produtos</a>
                </div>
            <?php endif; ?>
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
                <p>&copy; <?php echo date('Y'); ?> GomesTech. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <button class="scroll-to-top" title="Voltar ao topo">↑</button>

    <script src="../js/hamburger-menu.js"></script>
    <script src="../js/interactions.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/comparison.js"></script>
    <script src="../js/animations.js"></script>
    
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
    </script>
    
    <script>
        // Quando a categoria mudar, resetar a marca e submeter o formulário
        document.addEventListener('DOMContentLoaded', function() {
            const categoriaSelect = document.querySelector('select[name="categoria"]');
            const marcaSelect = document.querySelector('select[name="marca"]');
            
            // Sticky effect para filtros
            const filters = document.querySelector('.filters');
            const brandBar = document.querySelector('.brand-filter-bar');
            
            window.addEventListener('scroll', function() {
                if (filters) {
                    const rect = filters.getBoundingClientRect();
                    if (rect.top <= 120) {
                        filters.classList.add('stuck');
                    } else {
                        filters.classList.remove('stuck');
                    }
                }
                
                if (brandBar) {
                    const rect = brandBar.getBoundingClientRect();
                    if (rect.top <= 280) {
                        brandBar.classList.add('stuck');
                    } else {
                        brandBar.classList.remove('stuck');
                    }
                }
            });
            
            // Analytics - visualização de categoria
            try {
                const params = new URLSearchParams(location.search);
                const cat = params.get('categoria') || '<?php echo addslashes($categoria); ?>';
                const total = <?php echo (int)$total_produtos; ?>;
                window.dispatchEvent(new CustomEvent('analytics', { detail: { event: 'category_view', category: cat, total } }));
                console.log('[analytics] category_view', {category: cat, total});
            } catch(e) {}
      
            if (categoriaSelect && marcaSelect) {
                categoriaSelect.addEventListener('change', function() {
                    marcaSelect.value = '';
                    this.form.submit();
                });
            }

            // Marcas: evento + foco + scroll
            document.querySelectorAll('.brand-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    try {
                        const pressed = btn.getAttribute('aria-pressed') === 'true';
                        const brandParam = new URL(btn.href).searchParams.get('marca') || '';
                        window.dispatchEvent(new CustomEvent('analytics', { detail: { event: 'brand_filter_applied', brand: brandParam } }));
                        console.log('[analytics] brand_filter_applied', {brand: brandParam});
                    } catch(e) {}
                });
            });

            // Scroll para produtos se houver marca na URL
            const hasBrand = new URLSearchParams(location.search).get('marca');
            if (hasBrand) {
                const target = document.getElementById('produtos');
                if (target) target.scrollIntoView({behavior:'smooth', block:'start'});
            }

            // Skeletons: remover ao carregar imagem + impressões
            const productImages = document.querySelectorAll('.product-image img');
            const impressionIds = [];
            productImages.forEach(img => {
                const wrap = img.closest('.product-image');
                if (wrap) wrap.classList.add('skeleton');
                const handler = () => { if (wrap) wrap.classList.remove('skeleton'); };
                if (img.complete) handler(); else img.addEventListener('load', handler, { once:true });
                const id = parseInt(img.dataset.id||'');
                if(!isNaN(id)) impressionIds.push(id);
            });
            if (impressionIds.length){
                try {
                    window.dispatchEvent(new CustomEvent('analytics', { detail: { event: 'product_impression', ids: impressionIds } }));
                    console.log('[analytics] product_impression', {ids: impressionIds});
                } catch(e) {}
            }
        });

        // Favoritos (localStorage)
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
