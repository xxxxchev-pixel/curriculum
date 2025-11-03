<?php
/**
 * API Endpoint: Categorias
 * Retorna árvore de categorias com subcategorias
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/categories.php';

try {
    // Conectar à base de dados
    $mysqli = db_connect();
    // Obter árvore de categorias
    $categories = get_categories_tree($mysqli);
    
    // Gerar ETag para cache
    $etag = md5(json_encode($categories));
    header("ETag: \"{$etag}\"");
    
    // Verificar If-None-Match
    $client_etag = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    if ($client_etag === "\"{$etag}\"") {
        http_response_code(304); // Not Modified
        exit;
    }
    
    // Cache por 5 minutos
    header('Cache-Control: public, max-age=300');
    
    echo json_encode([
        'success' => true,
        'data' => $categories
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao carregar categorias'
    ], JSON_UNESCAPED_UNICODE);
}
?>
