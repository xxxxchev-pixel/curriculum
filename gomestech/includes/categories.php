<?php
/**
 * GomesTech - Funções para Categorias e Marcas
 * Sistema de navegação hierárquica e diversificação de produtos
 */

/**
 * Obter todas as categorias principais (sem parent_id)
 */
function get_root_categories($mysqli) {
    $query = "
        SELECT id, name, slug, icon, display_order
        FROM categories
        WHERE parent_id IS NULL AND active = 1
        ORDER BY display_order ASC, name ASC
    ";
    
    $result = $mysqli->query($query);
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}

/**
 * Obter subcategorias de uma categoria
 */
function get_subcategories($mysqli, $parent_id) {
    $stmt = $mysqli->prepare("
        SELECT id, name, slug, display_order
        FROM categories
        WHERE parent_id = ? AND active = 1
        ORDER BY display_order ASC, name ASC
    ");
    
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subcategories = [];
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
    
    return $subcategories;
}

/**
 * Obter marcas ativas em uma categoria
 */
function get_brands_by_category($mysqli, $category_slug) {
    // Usar a tabela pivot category_brand (não depende da coluna 'active' em produtos)
    $query = "
        SELECT DISTINCT b.id, b.name, b.slug, b.display_order
        FROM brands b
        INNER JOIN category_brand cb ON cb.brand_id = b.id
        INNER JOIN categories c ON cb.category_id = c.id
        WHERE c.slug = ? AND b.active = 1
        ORDER BY b.display_order ASC, b.name ASC
    ";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $category_slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $brands = [];
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row;
    }
    
    return $brands;
}

/**
 * Obter árvore completa de categorias com subcategorias
 */
function get_categories_tree($mysqli) {
    // Buscar categorias principais
    $root_categories = get_root_categories($mysqli);
    
    // Para cada categoria, buscar subcategorias
    foreach ($root_categories as &$category) {
        $category['subcategories'] = get_subcategories($mysqli, $category['id']);
    }
    
    return $root_categories;
}

/**
 * Obter produtos com diversificação (anti-repetição)
 * Implementa algoritmo de round-robin para evitar produtos da mesma marca adjacentes
 */
function get_diversified_products($mysqli, $category_slug, $brand_slug = null, $page = 1, $per_page = 24) {
    $offset = ($page - 1) * $per_page;
    
    // Query base
    $query = "
        SELECT 
            p.id,
            p.modelo as name,
            p.marca as brand_name,
            p.brand_id,
            b.slug as brand_slug,
            p.categoria as category_name,
            p.category_id,
            c.slug as category_slug,
            p.preco,
            p.imagem
        FROM produtos p
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1
    ";
    
    $params = [];
    $types = "";
    
    // Filtrar por categoria
    if ($category_slug) {
        $query .= " AND c.slug = ?";
        $params[] = $category_slug;
        $types .= "s";
    }
    
    // Filtrar por marca
    if ($brand_slug) {
        $query .= " AND b.slug = ?";
        $params[] = $brand_slug;
        $types .= "s";
    }
    
    // Ordenar por marca para facilitar diversificação
        $query .= " ORDER BY p.brand_id, p.id";
    
    // Buscar mais produtos do que necessário para diversificar
    $fetch_limit = $per_page * 3;
    $query .= " LIMIT ?";
    $params[] = $fetch_limit;
    $types .= "i";
    
    $stmt = $mysqli->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    // Aplicar algoritmo de diversificação
    $diversified = diversify_products($products);
    
    // Paginar resultado diversificado
    return array_slice($diversified, $offset, $per_page);
}

/**
 * Algoritmo de diversificação de produtos
 * Evita que produtos da mesma marca fiquem adjacentes
 */
function diversify_products($products) {
    if (empty($products)) {
        return [];
    }
    
    // Agrupar por brand_id
    $groups = [];
    foreach ($products as $product) {
        $brand_id = $product['brand_id'] ?? 'unknown';
        if (!isset($groups[$brand_id])) {
            $groups[$brand_id] = [];
        }
        $groups[$brand_id][] = $product;
    }
    
    // Se só há uma marca, retornar como está
    if (count($groups) === 1) {
        return $products;
    }
    
    // Criar filas de cada grupo
    $queues = array_values($groups);
    $result = [];
    $last_brand_id = null;
    
    // Round-robin com anti-repetição
    while (count(array_filter($queues, fn($q) => !empty($q))) > 0) {
        // Encontrar próximo grupo que não repete a última marca
        $found = false;
        
        for ($i = 0; $i < count($queues); $i++) {
            if (empty($queues[$i])) {
                continue;
            }
            
            $candidate = $queues[$i][0];
            $candidate_brand = $candidate['brand_id'] ?? 'unknown';
            
            // Se não repete a marca anterior, usar este
            if ($candidate_brand !== $last_brand_id) {
                $item = array_shift($queues[$i]);
                $result[] = $item;
                $last_brand_id = $item['brand_id'] ?? 'unknown';
                $found = true;
                
                // Rodar filas para distribuir melhor
                $queue = array_shift($queues);
                $queues[] = $queue;
                break;
            }
        }
        
        // Se não encontrou nenhum diferente, pegar qualquer disponível
        if (!$found) {
            for ($i = 0; $i < count($queues); $i++) {
                if (!empty($queues[$i])) {
                    $item = array_shift($queues[$i]);
                    $result[] = $item;
                    $last_brand_id = $item['brand_id'] ?? 'unknown';
                    
                    // Rodar filas
                    $queue = array_shift($queues);
                    $queues[] = $queue;
                    break;
                }
            }
        }
    }
    
    return $result;
}

/**
 * Contar total de produtos em uma categoria/marca
 */
function count_products($mysqli, $category_slug, $brand_slug = null) {
    $query = "
        SELECT COUNT(*) as total
        FROM produtos p
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1
    ";
    
    $params = [];
    $types = "";
    
    if ($category_slug) {
        $query .= " AND c.slug = ?";
        $params[] = $category_slug;
        $types .= "s";
    }
    
    if ($brand_slug) {
        $query .= " AND b.slug = ?";
        $params[] = $brand_slug;
        $types .= "s";
    }
    
    $stmt = $mysqli->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return (int) $row['total'];
}

/**
 * Obter categoria por slug
 */
function get_category_by_slug($mysqli, $slug) {
    $stmt = $mysqli->prepare("
        SELECT id, name, slug, parent_id, icon
        FROM categories
        WHERE slug = ? AND active = 1
        LIMIT 1
    ");
    
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Obter marca por slug
 */
function get_brand_by_slug($mysqli, $slug) {
    $stmt = $mysqli->prepare("
        SELECT id, name, slug
        FROM brands
        WHERE slug = ? AND active = 1
        LIMIT 1
    ");
    
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Gerar breadcrumbs para navegação
 */
function get_breadcrumbs($mysqli, $category_slug, $brand_slug = null) {
    $breadcrumbs = [
        ['name' => 'Início', 'url' => '/']
    ];
    
    if ($category_slug) {
        $category = get_category_by_slug($mysqli, $category_slug);
        if ($category) {
            $breadcrumbs[] = [
                'name' => $category['name'],
                'url' => '/c/' . $category_slug
            ];
            
            if ($brand_slug) {
                $brand = get_brand_by_slug($mysqli, $brand_slug);
                if ($brand) {
                    $breadcrumbs[] = [
                        'name' => $brand['name'],
                        'url' => '/c/' . $category_slug . '/' . $brand_slug
                    ];
                }
            }
        }
    }
    
    return $breadcrumbs;
}
?>
