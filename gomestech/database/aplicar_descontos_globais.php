<?php
require_once __DIR__ . '/../config.php';

$mysqli = db_connect();

echo "<h1>Atualização Rápida de Preços</h1>\n";
echo "<p>Reduzindo preços em 15-25% para competitividade...</p>\n<hr>\n";

// Atualizar todos os produtos reduzindo o preço atual em percentagem
$categorias_desconto = [
    'Smartphones' => 0.20,      // 20% desconto
    'Laptops' => 0.15,          // 15% desconto  
    'Tablets' => 0.18,          // 18% desconto
    'TVs' => 0.15,              // 15% desconto
    'Wearables' => 0.20,        // 20% desconto
    'Audio' => 0.20,            // 20% desconto
    'Consolas' => 0.12,         // 12% desconto
    'Ar Condicionado' => 0.10,  // 10% desconto (já tem desconto)
    'Aspiradores' => 0.20,      // 20% desconto
    'Frigorifico' => 0.15,      // 15% desconto
    'Maquinas Lavar' => 0.15,   // 15% desconto
    'Micro-ondas' => 0.18,      // 18% desconto
    'Maquinas Cafe' => 0.15,    // 15% desconto
];

$total = 0;

foreach ($categorias_desconto as $cat => $desconto_perc) {
    // Calcular novo preço: preço atual * (1 - desconto)
    $multiplicador = 1 - $desconto_perc;
    
    $stmt = $mysqli->prepare("UPDATE produtos 
                             SET preco = ROUND(preco * ?, 2) 
                             WHERE categoria = ? 
                             AND preco > 50");
    $stmt->bind_param("ds", $multiplicador, $cat);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        $desconto_display = round($desconto_perc * 100);
        echo "✓ $cat: -$desconto_display% → $affected produtos atualizados<br>\n";
        $total += $affected;
    }
    
    $stmt->close();
}

echo "<hr>\n<h2>Total: $total produtos atualizados</h2>\n";

// Mostrar amostra
echo "<hr>\n<h3>Amostra de novos preços:</h3>\n";
$result = $mysqli->query("SELECT categoria, marca, modelo, preco, preco_original 
                          FROM produtos 
                          WHERE preco < preco_original
                          ORDER BY RAND()
                          LIMIT 30");

if ($result) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>\n";
    echo "<tr style='background: #f0f0f0;'><th>Categoria</th><th>Marca</th><th>Modelo</th><th>Antes</th><th>Agora</th><th>Poupança</th></tr>\n";
    
    while ($row = $result->fetch_assoc()) {
        $economia = $row['preco_original'] - $row['preco'];
        $perc = round(($economia / $row['preco_original']) * 100);
        
        echo "<tr>";
        echo "<td>{$row['categoria']}</td>";
        echo "<td>{$row['marca']}</td>";
        echo "<td>{$row['modelo']}</td>";
        echo "<td style='text-decoration: line-through; color: #999;'>€" . number_format($row['preco_original'], 2) . "</td>";
        echo "<td style='color: #FF6A00; font-weight: bold; font-size: 16px;'>€" . number_format($row['preco'], 2) . "</td>";
        echo "<td style='color: green; font-weight: bold;'>-{$perc}% (€" . number_format($economia, 2) . ")</td>";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
}

$mysqli->close();

echo "<hr>\n<p><a href='../index.php' style='padding: 12px 24px; background: #FF6A00; color: white; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 20px;'>Ver Loja com Novos Preços</a></p>\n";
?>
