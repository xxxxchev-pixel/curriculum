<?php
/**
 * ATUALIZAÇÃO DE PREÇOS COMPETITIVOS - DEZEMBRO 2024
 * Baseado em pesquisa de mercado (Worten, FNAC, Media Markt, Amazon)
 * Preços GomesTech: 5-10% abaixo do mercado
 */

require_once __DIR__ . '/../config.php';

$mysqli = db_connect();

echo "<h1>Atualização de Preços - GomesTech</h1>\n";
echo "<p>Aplicando preços competitivos baseados em pesquisa de mercado...</p>\n";
echo "<hr>\n";

// Array de atualizações de preços
$updates = [
    // SMARTPHONES
    ['modelo' => 'iPhone 13 128GB', 'marca' => 'Apple', 'preco' => 489.00],
    ['modelo' => 'iPhone 14 128GB', 'marca' => 'Apple', 'preco' => 639.00],
    ['modelo' => 'iPhone 15 128GB', 'marca' => 'Apple', 'preco' => 829.00],
    ['modelo' => 'iPhone 15 Pro 256GB', 'marca' => 'Apple', 'preco' => 1179.00],
    ['modelo' => 'Samsung Galaxy S23', 'marca' => 'Samsung', 'preco' => 599.00],
    ['modelo' => 'Samsung Galaxy S24', 'marca' => 'Samsung', 'preco' => 799.00],
    ['modelo' => 'Samsung Galaxy A54', 'marca' => 'Samsung', 'preco' => 369.00],
    ['modelo' => 'Samsung Galaxy A34', 'marca' => 'Samsung', 'preco' => 299.00],
    ['modelo' => 'Xiaomi 13 Pro', 'marca' => 'Xiaomi', 'preco' => 849.00],
    ['modelo' => 'Xiaomi Redmi Note 13 Pro', 'marca' => 'Xiaomi', 'preco' => 299.00],
    ['modelo' => 'OnePlus 11', 'marca' => 'OnePlus', 'preco' => 679.00],
    ['modelo' => 'Google Pixel 8', 'marca' => 'Google', 'preco' => 699.00],
    
    // LAPTOPS
    ['modelo' => 'MacBook Air M2', 'marca' => 'Apple', 'preco' => 1129.00],
    ['modelo' => 'Dell XPS 13', 'marca' => 'Dell', 'preco' => 1029.00],
    ['modelo' => 'HP Pavilion 15', 'marca' => 'HP', 'preco' => 649.00],
    ['modelo' => 'Lenovo ThinkPad X1 Carbon', 'marca' => 'Lenovo', 'preco' => 1299.00],
    ['modelo' => 'ASUS ZenBook 14', 'marca' => 'ASUS', 'preco' => 839.00],
    ['modelo' => 'MSI GF63 Thin', 'marca' => 'MSI', 'preco' => 749.00],
    
    // TABLETS
    ['modelo' => 'iPad Air M2', 'marca' => 'Apple', 'preco' => 649.00],
    ['modelo' => 'Samsung Galaxy Tab S9', 'marca' => 'Samsung', 'preco' => 749.00],
    ['modelo' => 'Samsung Galaxy Tab A9', 'marca' => 'Samsung', 'preco' => 179.00],
    ['modelo' => 'Lenovo Tab P11', 'marca' => 'Lenovo', 'preco' => 229.00],
    
    // WEARABLES
    ['modelo' => 'Apple Watch Series 9', 'marca' => 'Apple', 'preco' => 419.00],
    ['modelo' => 'Apple Watch SE', 'marca' => 'Apple', 'preco' => 279.00],
    ['modelo' => 'Samsung Galaxy Watch 6', 'marca' => 'Samsung', 'preco' => 319.00],
    ['modelo' => 'Garmin Forerunner 265', 'marca' => 'Garmin', 'preco' => 419.00],
    ['modelo' => 'Fitbit Charge 6', 'marca' => 'Fitbit', 'preco' => 149.00],
    
    // ÁUDIO
    ['modelo' => 'Sony WH-1000XM5', 'marca' => 'Sony', 'preco' => 349.00],
    ['modelo' => 'AirPods Pro 2', 'marca' => 'Apple', 'preco' => 259.00],
    ['modelo' => 'Bose QuietComfort 45', 'marca' => 'Bose', 'preco' => 299.00],
    ['modelo' => 'JBL Flip 6', 'marca' => 'JBL', 'preco' => 109.00],
    ['modelo' => 'Marshall Emberton II', 'marca' => 'Marshall', 'preco' => 139.00],
    
    // CONSOLAS
    ['modelo' => 'PlayStation 5', 'marca' => 'Sony', 'preco' => 519.00],
    ['modelo' => 'Xbox Series X', 'marca' => 'Microsoft', 'preco' => 479.00],
    ['modelo' => 'Nintendo Switch OLED', 'marca' => 'Nintendo', 'preco' => 329.00],
    ['modelo' => 'Steam Deck', 'marca' => 'Valve', 'preco' => 399.00],
    
    // ELETRODOMÉSTICOS
    ['modelo' => 'Dyson V15 Detect', 'marca' => 'Dyson', 'preco' => 599.00],
    ['modelo' => 'iRobot Roomba j7+', 'marca' => 'iRobot', 'preco' => 749.00],
    ['modelo' => 'Samsung Smart Oven', 'marca' => 'Samsung', 'preco' => 279.00],
    ['modelo' => 'LG NeoChef', 'marca' => 'LG', 'preco' => 179.00],
    ['modelo' => 'Nespresso Vertuo', 'marca' => 'Nespresso', 'preco' => 179.00],
];

$total_updated = 0;
$total_errors = 0;

foreach ($updates as $item) {
    $stmt = $mysqli->prepare("UPDATE produtos SET preco = ? WHERE modelo = ? AND marca = ?");
    $stmt->bind_param("dss", $item['preco'], $item['modelo'], $item['marca']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "✓ {$item['marca']} {$item['modelo']}: €{$item['preco']}<br>\n";
            $total_updated++;
        } else {
            echo "⚠ Produto não encontrado: {$item['marca']} {$item['modelo']}<br>\n";
        }
    } else {
        echo "✗ Erro ao atualizar: {$item['marca']} {$item['modelo']}<br>\n";
        $total_errors++;
    }
    
    $stmt->close();
}

// Atualizar produtos por padrão LIKE
$like_updates = [
    // TVs
    ['pattern' => 'Samsung QLED 55%', 'marca' => 'Samsung', 'preco' => 839.00],
    ['pattern' => 'LG OLED 55%', 'marca' => 'LG', 'preco' => 1199.00],
    ['pattern' => 'Sony Bravia 65%', 'marca' => 'Sony', 'preco' => 1399.00],
    ['pattern' => 'Philips%43%4K%', 'marca' => 'Philips', 'preco' => 369.00],
    
    // Ar Condicionado
    ['pattern' => 'Daikin Comfora%12000%', 'marca' => 'Daikin', 'preco' => 649.00],
    ['pattern' => 'Mitsubishi MSZ-HR%', 'marca' => 'Mitsubishi', 'preco' => 749.00],
    ['pattern' => 'LG Dual Cool%', 'marca' => 'LG', 'preco' => 559.00],
    ['pattern' => 'Samsung WindFree%', 'marca' => 'Samsung', 'preco' => 849.00],
    
    // Frigoríficos
    ['pattern' => 'Samsung Family Hub%', 'marca' => 'Samsung', 'preco' => 2299.00],
    ['pattern' => 'LG InstaView%', 'marca' => 'LG', 'preco' => 1849.00],
    ['pattern' => 'Bosch Serie 6%', 'marca' => 'Bosch', 'preco' => 1199.00],
    
    // Máquinas de Lavar
    ['pattern' => 'Bosch Serie 8%Roupa%', 'marca' => 'Bosch', 'preco' => 839.00],
    ['pattern' => 'LG AI DD%', 'marca' => 'LG', 'preco' => 699.00],
    ['pattern' => 'Samsung EcoBubble%', 'marca' => 'Samsung', 'preco' => 559.00],
];

echo "<hr>\n<h3>Atualizando produtos por padrão...</h3>\n";

foreach ($like_updates as $item) {
    $stmt = $mysqli->prepare("UPDATE produtos SET preco = ? WHERE modelo LIKE ? AND marca = ?");
    $stmt->bind_param("dss", $item['preco'], $item['pattern'], $item['marca']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "✓ {$item['marca']} ({$item['pattern']}): €{$item['preco']} - {$stmt->affected_rows} produto(s) atualizado(s)<br>\n";
            $total_updated += $stmt->affected_rows;
        }
    }
    
    $stmt->close();
}

echo "<hr>\n";
echo "<h2>Resumo:</h2>\n";
echo "<p><strong>Total de produtos atualizados:</strong> $total_updated</p>\n";
echo "<p><strong>Erros:</strong> $total_errors</p>\n";

// Mostrar alguns produtos atualizados
echo "<hr>\n<h3>Amostra de produtos com novos preços:</h3>\n";
$result = $mysqli->query("SELECT marca, modelo, preco, preco_original, categoria 
                          FROM produtos 
                          WHERE preco < preco_original 
                          ORDER BY categoria, preco DESC 
                          LIMIT 20");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Categoria</th><th>Marca</th><th>Modelo</th><th>Preço Original</th><th>Novo Preço</th><th>Desconto</th></tr>\n";
    
    while ($row = $result->fetch_assoc()) {
        $desconto = round((($row['preco_original'] - $row['preco']) / $row['preco_original']) * 100);
        echo "<tr>";
        echo "<td>{$row['categoria']}</td>";
        echo "<td>{$row['marca']}</td>";
        echo "<td>{$row['modelo']}</td>";
        echo "<td style='text-decoration: line-through; color: #999;'>€" . number_format($row['preco_original'], 2) . "</td>";
        echo "<td style='color: #FF6A00; font-weight: bold;'>€" . number_format($row['preco'], 2) . "</td>";
        echo "<td style='color: green;'>-{$desconto}%</td>";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
}

$mysqli->close();

echo "<hr>\n";
echo "<p><a href='../index.php'>← Voltar à Loja</a></p>\n";
?>
