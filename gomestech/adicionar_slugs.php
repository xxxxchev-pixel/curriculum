<?php
/**
 * Script para adicionar slugs aos produtos existentes
 * Execute este script uma vez após importar a base de dados
 */

require_once __DIR__ . '/config.php';

// Conectar à base de dados
$mysqli = db_connect();

// Função para gerar slug
function generateSlug($categoria, $marca, $modelo) {
    // Mapeamento de categorias para URLs
    $catMap = [
        'Smartphones' => 'smartphones',
        'Laptops' => 'laptops',
        'Tablets' => 'tablets',
        'Wearables' => 'wearables',
        'TVs' => 'tvs',
        'Audio' => 'audio',
        'Consolas' => 'consolas',
        'Frigoríficos' => 'frigorificos',
        'Máquinas de Lavar' => 'maquinas-de-lavar',
        'Micro-ondas' => 'micro-ondas',
        'Aspiradores' => 'aspiradores',
        'Ar Condicionado' => 'ar-condicionado',
        'Máquinas de Café' => 'maquinas-de-cafe'
    ];
    
    // Converter categoria
    $cat = $catMap[$categoria] ?? strtolower($categoria);
    
    // Converter marca para slug
    $marcaSlug = strtolower($marca);
    $marcaSlug = str_replace([' ', '\'', '&'], ['-', '', ''], $marcaSlug);
    $marcaSlug = iconv('UTF-8', 'ASCII//TRANSLIT', $marcaSlug);
    $marcaSlug = preg_replace('/[^a-z0-9-]/', '', $marcaSlug);
    
    // Converter modelo para slug
    $modeloSlug = strtolower($modelo);
    $modeloSlug = str_replace([' ', '"', '\'', '+', '&', '.'], ['-', '', '', '', '', ''], $modeloSlug);
    $modeloSlug = iconv('UTF-8', 'ASCII//TRANSLIT', $modeloSlug);
    $modeloSlug = preg_replace('/[^a-z0-9-]/', '', $modeloSlug);
    $modeloSlug = preg_replace('/-+/', '-', $modeloSlug);
    $modeloSlug = trim($modeloSlug, '-');
    
    return $cat . '/' . $marcaSlug . '/' . $modeloSlug;
}

// Buscar todos os produtos sem slug
$result = $mysqli->query("SELECT id, categoria, marca, modelo FROM produtos WHERE slug IS NULL OR slug = ''");

if ($result) {
    $updated = 0;
    $errors = 0;
    
    while ($produto = $result->fetch_assoc()) {
        $slug = generateSlug($produto['categoria'], $produto['marca'], $produto['modelo']);
        
        // Atualizar o produto com o slug
        $stmt = $mysqli->prepare("UPDATE produtos SET slug = ? WHERE id = ?");
        $stmt->bind_param("si", $slug, $produto['id']);
        
        if ($stmt->execute()) {
            $updated++;
            echo "✓ Produto #{$produto['id']}: {$slug}<br>\n";
        } else {
            $errors++;
            echo "✗ Erro no produto #{$produto['id']}: " . $stmt->error . "<br>\n";
        }
        
        $stmt->close();
    }
    
    echo "<hr>";
    echo "<strong>Resumo:</strong><br>\n";
    echo "Produtos atualizados: $updated<br>\n";
    echo "Erros: $errors<br>\n";
} else {
    echo "Erro na consulta: " . $mysqli->error;
}

$mysqli->close();
?>
