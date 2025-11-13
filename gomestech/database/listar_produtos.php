<?php
require_once __DIR__ . '/../config.php';

$mysqli = db_connect();

// Buscar produtos por categoria para verificar nomes exatos
$categorias = ['Smartphones', 'Laptops', 'Tablets', 'Wearables', 'Audio', 'Consolas'];

foreach ($categorias as $cat) {
    echo "<h3>$cat</h3>";
    $result = $mysqli->query("SELECT id, marca, modelo, preco, preco_original 
                              FROM produtos 
                              WHERE categoria = '$cat' 
                              ORDER BY marca, modelo 
                              LIMIT 30");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Marca</th><th>Modelo</th><th>Preço Atual</th><th>Preço Original</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['marca']}</td>";
            echo "<td>{$row['modelo']}</td>";
            echo "<td>€" . number_format($row['preco'], 2) . "</td>";
            echo "<td>€" . number_format($row['preco_original'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
}

$mysqli->close();
?>
