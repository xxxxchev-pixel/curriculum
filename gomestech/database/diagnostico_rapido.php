<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html>
<html lang='pt'>
<head>
    <meta charset='UTF-8'>
    <title>Diagnóstico Rápido - GomesTech</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #2C2C2C; }
        .success { background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px; color: #155724; }
        .error { background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px; color: #721c24; }
        .info { background: #d1ecf1; padding: 15px; margin: 10px 0; border-radius: 5px; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #2C2C2C; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #FF6A00; color: white; 
               text-decoration: none; border-radius: 8px; margin: 10px 5px; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Diagnóstico Rápido da Base de Dados</h1>";

try {
    $mysqli = db_connect();
    echo "<div class='success'>✅ Conexão à base de dados: OK</div>";
    
    // Verificar se tabela existe
    $result = $mysqli->query("SHOW TABLES LIKE 'produtos'");
    if($result->num_rows > 0) {
        echo "<div class='success'>✅ Tabela 'produtos' existe</div>";
    } else {
        echo "<div class='error'>❌ Tabela 'produtos' não existe!</div>";
        exit;
    }
    
    // Contar produtos
    $count_result = $mysqli->query("SELECT COUNT(*) as total FROM produtos");
    $count = $count_result->fetch_assoc()['total'];
    echo "<div class='info'>📦 Total de produtos: <strong>$count</strong></div>";
    
    // Mostrar colunas
    echo "<h2>📋 Estrutura da Tabela</h2>";
    echo "<table>";
    echo "<tr><th>Coluna</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    $columns = $mysqli->query("SHOW COLUMNS FROM produtos");
    while($col = $columns->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar alguns produtos
    echo "<h2>📦 Primeiros 10 Produtos</h2>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Marca</th><th>Modelo</th><th>Preço</th><th>Categoria</th></tr>";
    
    $produtos = $mysqli->query("SELECT id, marca, modelo, preco, categoria FROM produtos LIMIT 10");
    if($produtos && $produtos->num_rows > 0) {
        while($p = $produtos->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$p['id']}</td>";
            echo "<td>{$p['marca']}</td>";
            echo "<td>{$p['modelo']}</td>";
            echo "<td><strong>" . number_format($p['preco'], 2, ',', '.') . "€</strong></td>";
            echo "<td>{$p['categoria']}</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Nenhum produto encontrado</td></tr>";
    }
    echo "</table>";
    
    // Verificar preços suspeitos
    $baixos = $mysqli->query("SELECT COUNT(*) as total FROM produtos WHERE preco < 10");
    $baixos_count = $baixos->fetch_assoc()['total'];
    
    $altos = $mysqli->query("SELECT COUNT(*) as total FROM produtos WHERE preco > 5000");
    $altos_count = $altos->fetch_assoc()['total'];
    
    echo "<h2>⚠️ Análise de Preços</h2>";
    
    if($baixos_count > 0) {
        echo "<div class='error'>❌ <strong>$baixos_count produtos</strong> com preço abaixo de 10€ (possível erro)</div>";
    } else {
        echo "<div class='success'>✅ Nenhum produto com preço suspeito (< 10€)</div>";
    }
    
    if($altos_count > 0) {
        echo "<div class='info'>ℹ️ <strong>$altos_count produtos</strong> com preço acima de 5000€</div>";
    }
    
    // Estatísticas por categoria
    echo "<h2>📊 Produtos por Categoria</h2>";
    echo "<table>";
    echo "<tr><th>Categoria</th><th>Quantidade</th><th>Preço Médio</th></tr>";
    
    $stats = $mysqli->query("
        SELECT categoria, 
               COUNT(*) as quantidade, 
               AVG(preco) as preco_medio 
        FROM produtos 
        GROUP BY categoria 
        ORDER BY quantidade DESC
    ");
    
    while($stat = $stats->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>{$stat['categoria']}</strong></td>";
        echo "<td>{$stat['quantidade']}</td>";
        echo "<td>" . number_format($stat['preco_medio'], 2, ',', '.') . "€</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $mysqli->close();
    
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='atualizar_precos_mercado.php' class='btn'>💰 Atualizar Preços</a>";
    echo "<a href='verificar_precos.php' class='btn' style='background: #6c757d;'>🔍 Verificar Preços</a>";
    echo "<a href='../index.php' class='btn' style='background: #28a745;'>🏠 Ver Site</a>";
    echo "</div>";
    
} catch(Exception $e) {
    echo "<div class='error'>❌ ERRO: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
