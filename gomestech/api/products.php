<?php
/**
 * API Endpoint: Produtos Diversificados
 * Retorna produtos com algoritmo anti-repetição de marcas
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/categories.php';

try {
    // Conectar à base de dados
    $mysqli = db_connect();
    // Parâmetros
    $category_slug = $_GET['category'] ?? null;
    $brand_slug = $_GET['brand'] ?? null;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = isset($_GET['per_page']) ? min(48, max(12, (int)$_GET['per_page'])) : 24;
    
    // Validar categoria se fornecida
    if ($category_slug) {
        $category = get_category_by_slug($mysqli, $category_slug);
        if (!$category) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Categoria não encontrada'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // Validar marca se fornecida
    if ($brand_slug) {
        $brand = get_brand_by_slug($mysqli, $brand_slug);
        if (!$brand) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Marca não encontrada'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // Obter produtos diversificados
    $products = get_diversified_products($mysqli, $category_slug, $brand_slug, $page, $per_page);
    
    // Contar total
    $total = count_products($mysqli, $category_slug, $brand_slug);
    $total_pages = ceil($total / $per_page);
    
    // Gerar ETag
    $etag = md5(serialize([
        'category' => $category_slug,
        'brand' => $brand_slug,
        'page' => $page,
        'products' => $products
    ]));
    header("ETag: \"{$etag}\"");
    
    // Verificar If-None-Match
    $client_etag = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    if ($client_etag === "\"{$etag}\"") {
        http_response_code(304); // Not Modified
        exit;
    }
    
    // Cache por 3 minutos (produtos mudam mais frequentemente)
    header('Cache-Control: public, max-age=180');
    
    echo json_encode([
        'success' => true,
        'data' => $products,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_items' => $total,
            'total_pages' => $total_pages,
            'has_next' => $page < $total_pages,
            'has_prev' => $page > 1
        ],
        'filters' => [
            'category' => $category_slug ? $category['name'] : null,
            'brand' => $brand_slug ? $brand['name'] : null
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao carregar produtos'
    ], JSON_UNESCAPED_UNICODE);
}
?>
