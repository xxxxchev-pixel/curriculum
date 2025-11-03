<?php
/**
 * GomesTech - Página de Produto Refatorada
 * 
 * Features:
 * - Busca por slug ou ID
 * - JSON-LD (Product schema)
 * - Imagens responsivas (srcset, loading="lazy", decoding="async")
 * - Meta tags OG/Twitter
 * - Breadcrumbs com JSON-LD
 * - CSRF protection
 * - Acessibilidade WCAG 2.2 AA
 */

require_once __DIR__ . '/config.php';

// Obter produto por slug (prioridade) ou ID (fallback)
$produto = null;

if (isset($_GET['slug'])) {
    $produto = get_produto_by_slug($mysqli, $_GET['slug']);
} elseif (isset($_GET['id'])) {
    $produto = get_produto_by_id($mysqli, (int)$_GET['id']);
}

// 404 se produto não encontrado
if (!$produto) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit();
}

// Dados do produto
$produto_id = (int)$produto['id'];
$nome = $produto['marca'] . ' ' . $produto['modelo'];
$preco = (float)$produto['preco'];
$imagem = $produto['imagem'] ?? '/img/placeholder.jpg';
$categoria = $produto['categoria'] ?? 'Produtos';
$descricao = $produto['descricao'] ?? '';
$marca = $produto['marca'] ?? '';
$modelo = $produto['modelo'] ?? '';
$slug = $produto['slug'] ?? null;
$sku = $produto['sku'] ?? 'SKU-' . $produto_id;
$stock = (int)($produto['stock'] ?? 0);

// URL canônica
$url_canonical = $slug 
    ? BASE_URL . '/produto/' . $slug 
    : BASE_URL . '/produto.php?id=' . $produto_id;

// Verificar se há estoque
$in_stock = $stock > 0;
$availability = $in_stock ? 'InStock' : 'OutOfStock';

// Meta description (primeiros 155 caracteres da descrição)
$meta_description = mb_substr(strip_tags($descricao), 0, 155);
if (mb_strlen(strip_tags($descricao)) > 155) {
    $meta_description .= '...';
}

// Breadcrumbs
$breadcrumbs = [
    ['name' => 'Início', 'url' => BASE_URL],
    ['name' => $categoria, 'url' => BASE_URL . '/categoria/' . slugify($categoria)],
    ['name' => $nome, 'url' => $url_canonical]
];

// Flash message (se houver)
$flash = get_flash();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= h($meta_description) ?>">
    <meta name="csrf-token" content="<?= h(generate_csrf_token()) ?>">
    
    <title><?= h($nome) ?> - GomesTech | Tecnologia de Qualidade</title>
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= h($url_canonical) ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="<?= h($url_canonical) ?>">
    <meta property="og:title" content="<?= h($nome) ?> - GomesTech">
    <meta property="og:description" content="<?= h($meta_description) ?>">
    <meta property="og:image" content="<?= h(BASE_URL . $imagem) ?>">
    <meta property="product:price:amount" content="<?= h(number_format($preco, 2, '.', '')) ?>">
    <meta property="product:price:currency" content="EUR">
    <?php if ($in_stock): ?>
    <meta property="product:availability" content="in stock">
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= h($url_canonical) ?>">
    <meta name="twitter:title" content="<?= h($nome) ?> - GomesTech">
    <meta name="twitter:description" content="<?= h($meta_description) ?>">
    <meta name="twitter:image" content="<?= h(BASE_URL . $imagem) ?>">
    
    <!-- JSON-LD: Product Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": <?= json_encode($nome) ?>,
        "image": [<?= json_encode(BASE_URL . $imagem) ?>],
        "description": <?= json_encode($meta_description) ?>,
        "sku": <?= json_encode($sku) ?>,
        "brand": {
            "@type": "Brand",
            "name": <?= json_encode($marca) ?>
        },
        "offers": {
            "@type": "Offer",
            "url": <?= json_encode($url_canonical) ?>,
            "priceCurrency": "EUR",
            "price": "<?= number_format($preco, 2, '.', '') ?>",
            "availability": "https://schema.org/<?= $availability ?>",
            "seller": {
                "@type": "Organization",
                "name": "GomesTech"
            }
        }
    }
    </script>
    
    <!-- JSON-LD: BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
            {
                "@type": "ListItem",
                "position": <?= $index + 1 ?>,
                "name": <?= json_encode($crumb['name']) ?>,
                "item": <?= json_encode($crumb['url']) ?>
            }<?= $index < count($breadcrumbs) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ]
    }
    </script>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/gomestech.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/animations.css">
    
    <!-- Preconnect para performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
</head>
<body>
    
    <?php include __DIR__ . '/includes/hamburger-menu.php'; ?>
    
    <main id="main-content" tabindex="-1">
        
        <!-- Breadcrumbs -->
        <nav aria-label="Breadcrumb" class="breadcrumbs">
            <ol>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                <li>
                    <?php if ($index < count($breadcrumbs) - 1): ?>
                        <a href="<?= h($crumb['url']) ?>"><?= h($crumb['name']) ?></a>
                    <?php else: ?>
                        <span aria-current="page"><?= h($crumb['name']) ?></span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        
        <!-- Flash Message -->
        <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>" role="alert">
            <?= h($flash['message']) ?>
        </div>
        <?php endif; ?>
        
        <!-- Produto -->
        <article class="produto-detail">
            
            <!-- Galeria de Imagens -->
            <div class="produto-gallery">
                <img 
                    src="<?= h($imagem) ?>" 
                    alt="<?= h($nome) ?>"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                    srcset="<?= h($imagem) ?> 1x, <?= h(preg_replace('/(\.\w+)$/', '@2x$1', $imagem)) ?> 2x"
                    sizes="(max-width: 768px) 100vw, 50vw"
                    width="600"
                    height="600">
                
                <!-- Botão Wishlist -->
                <button 
                    class="heart" 
                    data-wishlist-toggle
                    data-product-id="<?= $produto_id ?>"
                    aria-label="Adicionar aos favoritos"
                    type="button">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>
            </div>
            
            <!-- Informação do Produto -->
            <div class="produto-info">
                
                <header>
                    <p class="categoria">
                        <a href="<?= h(BASE_URL . '/categoria/' . slugify($categoria)) ?>">
                            <?= h($categoria) ?>
                        </a>
                    </p>
                    
                    <h1><?= h($nome) ?></h1>
                    
                    <!-- Rating (placeholder) -->
                    <div class="rating" role="img" aria-label="4 de 5 estrelas">
                        <div class="stars">
                            <span class="star filled" aria-hidden="true"></span>
                            <span class="star filled" aria-hidden="true"></span>
                            <span class="star filled" aria-hidden="true"></span>
                            <span class="star filled" aria-hidden="true"></span>
                            <span class="star" aria-hidden="true"></span>
                        </div>
                        <span class="rating-count">(124 avaliações)</span>
                    </div>
                </header>
                
                <!-- Preço -->
                <div class="preco-box">
                    <p class="preco"><?= format_price($preco) ?></p>
                    <p class="iva-info">IVA incluído</p>
                </div>
                
                <!-- Disponibilidade -->
                <div class="disponibilidade">
                    <?php if ($in_stock): ?>
                        <span class="badge badge-success">✓ Em stock</span>
                    <?php else: ?>
                        <span class="badge badge-danger">✕ Esgotado</span>
                    <?php endif; ?>
                </div>
                
                <!-- Descrição -->
                <div class="descricao">
                    <h2>Descrição</h2>
                    <?= nl2br(h($descricao)) ?>
                </div>
                
                <!-- Especificações (placeholder) -->
                <div class="specs">
                    <h2>Especificações</h2>
                    <table>
                        <tbody>
                            <tr>
                                <th>Marca</th>
                                <td><?= h($marca) ?></td>
                            </tr>
                            <tr>
                                <th>Modelo</th>
                                <td><?= h($modelo) ?></td>
                            </tr>
                            <tr>
                                <th>Categoria</th>
                                <td><?= h($categoria) ?></td>
                            </tr>
                            <tr>
                                <th>SKU</th>
                                <td><?= h($sku) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- CTAs -->
                <div class="ctas">
                    <?php if ($in_stock): ?>
                        <button 
                            class="btn btn-primary btn-add" 
                            data-add-to-cart
                            data-product-id="<?= $produto_id ?>"
                            type="button">
                            <span class="icon">🛒</span>
                            Adicionar ao Carrinho
                        </button>
                        
                        <button 
                            class="btn btn-secondary"
                            onclick="window.location.href='<?= h(BASE_URL . '/checkout.php?produto=' . $produto_id) ?>'"
                            type="button">
                            Comprar Agora
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>
                            Produto Esgotado
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Políticas -->
                <div class="policies">
                    <p>✓ Envio grátis acima de 50€</p>
                    <p>✓ Garantia de 2 anos</p>
                    <p>✓ Devolução em 14 dias</p>
                </div>
                
            </div>
            
        </article>
        
        <!-- Produtos Relacionados (placeholder) -->
        <section class="produtos-relacionados">
            <h2>Produtos Relacionados</h2>
            <div class="product-grid">
                <!-- TODO: Listar produtos relacionados -->
            </div>
        </section>
        
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Scripts (defer para não bloquear) -->
    <script src="<?= BASE_URL ?>/js/toast.js" defer></script>
    <script src="<?= BASE_URL ?>/js/wishlist.js" defer></script>
    <script src="<?= BASE_URL ?>/js/add-to-cart.js" type="module" defer></script>
    <script src="<?= BASE_URL ?>/js/tilt.js" defer></script>
    
</body>
</html>
