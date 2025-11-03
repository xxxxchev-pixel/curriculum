<?php
/**
 * API Endpoint: Marcas por Categoria
 * Retorna marcas ativas em uma categoria específica
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/categories.php';

try {
    // Conectar à base de dados
    $mysqli = db_connect();
    // Validar parâmetro category
    $category_slug = $_GET['category'] ?? '';
    
    if (empty($category_slug)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Parâmetro "category" é obrigatório'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar se categoria existe
    $category = get_category_by_slug($mysqli, $category_slug);
    if (!$category) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Categoria não encontrada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Obter marcas
    $brands = get_brands_by_category($mysqli, $category_slug);
    
    // Gerar ETag para cache
    $etag = md5($category_slug . json_encode($brands));
    header("ETag: \"{$etag}\"");
    
    // Verificar If-None-Match
    $client_etag = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    if ($client_etag === "\"{$etag}\"") {
        http_response_code(304); // Not Modified
        exit;
    }
    
    // Cache por 10 minutos
    header('Cache-Control: public, max-age=600');
    
    echo json_encode([
        'success' => true,
        'category' => $category['name'],
        'data' => $brands
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao carregar marcas'
    ], JSON_UNESCAPED_UNICODE);
}
?>
