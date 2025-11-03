<?php
/**
 * Importador do catálogo JSON para a base de dados MySQL
 * - Lê data/catalogo_completo.json
 * - Insere categorias, marcas e produtos (evita duplicados)
 * - Uso: abrir no navegador ou executar via CLI
 */

require_once __DIR__ . '/../config.php';

$mysqli = db_connect();

$json_file = __DIR__ . '/../data/catalogo_completo.json';
if (!file_exists($json_file)) {
    die("Ficheiro de catálogo não encontrado: $json_file\n");
}

$data = json_decode(file_get_contents($json_file), true);
if (!$data) {
    die("Falha a ler/decodificar JSON: " . json_last_error_msg() . "\n");
}

// Helpers
if (!function_exists('slugify')) {
    function slugify($s) {
        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('/[^a-z0-9\\s-]/u', '', $s);
        $s = preg_replace('/[\\s-]+/', '-', trim($s));
        return $s;
    }
}

// Insert categorias (mantendo id quando fornecido)
if (!empty($data['categorias'])) {
    $stmt_insert_cat = $mysqli->prepare("INSERT INTO categories (id, name, slug, icon, parent_id, display_order, active) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)");
    foreach ($data['categorias'] as $c) {
        $id = isset($c['id']) ? (int)$c['id'] : null;
        $name = $c['nome'] ?? $c['name'] ?? '';
        $slug = $c['slug'] ?? slugify($name);
        $icon = $c['icone'] ?? '';
        $parent = isset($c['parent_id']) ? $c['parent_id'] : null;
        $order = 0;
        $active = 1;

        // Bind parameters - use null for id if not set
        if ($id) {
            $stmt_insert_cat->bind_param('issiiii', $id, $name, $slug, $icon, $parent, $order, $active);
        } else {
            $temp_id = 0;
            $stmt_insert_cat->bind_param('issiiii', $temp_id, $name, $slug, $icon, $parent, $order, $active);
        }
        @$stmt_insert_cat->execute();
    }
    $stmt_insert_cat->close();
}

// Insert marcas
if (!empty($data['marcas'])) {
    $stmt_insert_brand = $mysqli->prepare("INSERT INTO brands (id, name, slug, logo, display_order, active) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)");
    foreach ($data['marcas'] as $b) {
        $id = isset($b['id']) ? (int)$b['id'] : null;
        $name = $b['nome'] ?? $b['name'] ?? '';
        $slug = $b['slug'] ?? slugify($name);
        $logo = $b['logo_url'] ?? $b['logo'] ?? '';
        $order = 0;
        $active = 1;

        if ($id) {
            $stmt_insert_brand->bind_param('isssii', $id, $name, $slug, $logo, $order, $active);
        } else {
            $temp_id = 0;
            $stmt_insert_brand->bind_param('isssii', $temp_id, $name, $slug, $logo, $order, $active);
        }
        @$stmt_insert_brand->execute();
    }
    $stmt_insert_brand->close();
}

// Map slugs to IDs for categories and brands
$cats_map = [];
$res = $mysqli->query("SELECT id, slug FROM categories");
while ($r = $res->fetch_assoc()) {
    $cats_map[$r['slug']] = $r['id'];
}

$brands_map = [];
$res = $mysqli->query("SELECT id, slug, name FROM brands");
while ($r = $res->fetch_assoc()) {
    $brands_map[mb_strtolower($r['name'], 'UTF-8')] = $r['id'];
    $brands_map[$r['slug']] = $r['id'];
}

// Inserir produtos
$inserted = 0;
$skipped = 0;

// Detectar colunas existentes na tabela produtos para montar INSERT compatível
$existing_cols = [];
$res_cols = $mysqli->query("SHOW COLUMNS FROM produtos");
while ($r = $res_cols->fetch_assoc()) {
    $existing_cols[] = $r['Field'];
}

$desired = ['categoria','marca','modelo','nome','slug','preco','preco_original','loja','imagem','descricao','stock','destaque','novidade','category_id','brand_id'];
$insert_cols = array_values(array_intersect($desired, $existing_cols));

if (empty($insert_cols)) {
    die("Não foram encontradas colunas compatíveis na tabela produtos para inserir.\n");
}

$placeholders = implode(',', array_fill(0, count($insert_cols), '?'));
$insert_sql = "INSERT INTO produtos (" . implode(',', $insert_cols) . ") VALUES ($placeholders)";
$stmt_prod = $mysqli->prepare($insert_sql);

foreach ($data['produtos'] as $p) {
    $categoria = $p['categoria'] ?? '';
    $marca = $p['marca'] ?? '';
    $modelo = $p['modelo'] ?? ($p['nome'] ?? '');
    $nome = trim(($marca ? $marca . ' ' : '') . $modelo);

    // montar slug: category_slug/brand_slug/modelo
    $cat_slug = slugify($categoria);
    $brand_slug = $p['marca_id'] ? null : slugify($marca);
    // preferir mapas existentes
    $category_id = $cats_map[$cat_slug] ?? null;
    $brand_id = $brands_map[mb_strtolower($marca, 'UTF-8')] ?? $brands_map[$brand_slug] ?? null;

    $modelo_slug = slugify($modelo);
    $slug = trim($cat_slug . '/' . ($brand_slug ?: slugify($marca)) . '/' . $modelo_slug, '/');

    // Checar se já existe produto com o mesmo slug
    $exists = $mysqli->prepare("SELECT id FROM produtos WHERE slug = ? LIMIT 1");
    $exists->bind_param('s', $slug);
    $exists->execute();
    $res_e = $exists->get_result();
    if ($res_e && $res_e->num_rows > 0) {
        $skipped++;
        $exists->close();
        continue;
    }
    $exists->close();

    $preco = isset($p['preco']) ? (float)$p['preco'] : 0.00;
    $preco_original = isset($p['preco_original']) ? (float)$p['preco_original'] : null;
    $loja = $p['loja'] ?? '';
    $imagem = $p['imagem_url'] ?? $p['imagem'] ?? '';
    $descricao = $p['descricao'] ?? '';
    $stock = isset($p['stock']) ? (int)$p['stock'] : 100;
    $destaque = isset($p['destaque']) ? (int)$p['destaque'] : 0;
    $novidade = isset($p['novidade']) ? (int)$p['novidade'] : 0;

    // Map values according to $insert_cols order
    $values = [];
    foreach ($insert_cols as $col) {
        switch ($col) {
            case 'categoria': $values[] = $categoria; break;
            case 'marca': $values[] = $marca; break;
            case 'modelo': $values[] = $modelo; break;
            case 'nome': $values[] = $nome; break;
            case 'slug': $values[] = $slug; break;
            case 'preco': $values[] = $preco; break;
            case 'preco_original': $values[] = $preco_original; break;
            case 'loja': $values[] = $loja; break;
            case 'imagem': $values[] = $imagem; break;
            case 'descricao': $values[] = $descricao; break;
            case 'stock': $values[] = $stock; break;
            case 'destaque': $values[] = $destaque; break;
            case 'novidade': $values[] = $novidade; break;
            case 'category_id': $values[] = $category_id; break;
            case 'brand_id': $values[] = $brand_id; break;
            default: $values[] = null; break;
        }
    }

    // Build types string for bind_param
    $types = '';
    foreach ($values as $v) {
        if (is_int($v)) $types .= 'i';
        elseif (is_float($v) || is_double($v)) $types .= 'd';
        else $types .= 's';
    }

    // bind dynamically
    $tmp = [];
    $tmp[] = & $types;
    for ($i = 0; $i < count($values); $i++) {
        $tmp[] = & $values[$i];
    }

    call_user_func_array([$stmt_prod, 'bind_param'], $tmp);
    if ($stmt_prod->execute()) {
        $inserted++;
    } else {
        // could log error
    }
}

$stmt_prod->close();

echo "Importação concluída. Inseridos: $inserted; Ignorados (duplicados): $skipped\n";

$mysqli->close();

if (php_sapi_name() === 'cli') {
    exit(0);
}

?>
