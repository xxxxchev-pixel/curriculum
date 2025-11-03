<?php
/**
 * Gerar Slugs Únicos para Todos os Produtos
 * Execute DEPOIS de executar migracao_slugs.sql
 */

require_once __DIR__ . '/../config.php';

$mysqli = db_connect();

echo "<!DOCTYPE html>
<html lang='pt'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Gerar Slugs - GomesTech</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
            max-width: 900px; 
            margin: 40px auto; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { 
            color: #2C2C2C; 
            border-bottom: 4px solid #FF6A00; 
            padding-bottom: 15px; 
            margin-bottom: 30px;
        }
        .success { 
            background: #d4edda; 
            border-left: 5px solid #28a745; 
            color: #155724; 
            padding: 15px 20px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .info { 
            background: #d1ecf1; 
            border-left: 5px solid #17a2b8; 
            color: #0c5460; 
            padding: 15px 20px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .produto-item {
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #28a745;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9em;
        }
        .slug { 
            color: #6c757d; 
            font-family: 'Courier New', monospace; 
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin: 10px 0;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
            transition: all .2s ease;
        }
        .btn-primary { 
            background: #FF6A00; 
            color: white; 
        }
        .btn-primary:hover { 
            background: #ff8c33; 
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(255,106,0,0.3);
        }
        .scrollable {
            max-height: 400px;
            overflow-y: auto;
            margin: 20px 0;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
        }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔗 Gerar Slugs para URLs Limpas</h1>";

// Função para gerar slug único
function gerar_slug_unico($mysqli, $base_text, $produto_id) {
    // Converter para slug base
    $slug = slugify($base_text);
    
    // Se vazio, usar ID
    if (empty($slug)) {
        $slug = 'produto-' . $produto_id;
    }
    
    // Verificar unicidade
    $original_slug = $slug;
    $counter = 1;
    
    $stmt = $mysqli->prepare("SELECT id FROM produtos WHERE slug = ? AND id != ? LIMIT 1");
    
    while (true) {
        $stmt->bind_param('si', $slug, $produto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Slug é único
            break;
        }
        
        // Slug já existe, tentar com sufixo
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    $stmt->close();
    return $slug;
}

$executar = $_GET['executar'] ?? 'nao';

if ($executar !== 'sim') {
    // Verificar quantos produtos não têm slug
    $result = $mysqli->query("SELECT COUNT(*) as total FROM produtos WHERE slug IS NULL OR slug = ''");
    $row = $result->fetch_assoc();
    $sem_slug = $row['total'];
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM produtos");
    $row = $result->fetch_assoc();
    $total = $row['total'];
    
    echo "<div class='stats'>";
    echo "<div class='stat-box'>";
    echo "<div>Total de Produtos</div>";
    echo "<div class='stat-number'>{$total}</div>";
    echo "</div>";
    echo "<div class='stat-box'>";
    echo "<div>Sem Slug</div>";
    echo "<div class='stat-number'>{$sem_slug}</div>";
    echo "</div>";
    echo "</div>";
    
    if ($sem_slug > 0) {
        echo "<div class='info'>";
        echo "<h3>📋 Preview dos Slugs</h3>";
        echo "<p>Exemplos de como ficarão as URLs:</p>";
        
        $preview = $mysqli->query("SELECT id, marca, modelo FROM produtos WHERE slug IS NULL OR slug = '' LIMIT 5");
        
        echo "<div style='margin: 15px 0;'>";
        while ($p = $preview->fetch_assoc()) {
            $slug_preview = slugify($p['marca'] . ' ' . $p['modelo']);
            echo "<div class='produto-item'>";
            echo "<span><strong>{$p['marca']} {$p['modelo']}</strong></span>";
            echo "<span class='slug'>/produto/{$slug_preview}</span>";
            echo "</div>";
        }
        echo "</div>";
        
        echo "<p><strong>✅ Benefícios:</strong></p>";
        echo "<ul>";
        echo "<li>URLs amigáveis para SEO</li>";
        echo "<li>Melhor experiência de utilizador</li>";
        echo "<li>Links mais fáceis de partilhar</li>";
        echo "<li>Melhor indexação no Google</li>";
        echo "</ul>";
        
        echo "</div>";
        
        echo "<div style='text-align: center; margin-top: 30px;'>";
        echo "<a href='?executar=sim' class='btn btn-primary' style='font-size: 1.2em;'>🚀 GERAR SLUGS AGORA</a>";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "<h3>✅ Todos os produtos já têm slugs!</h3>";
        echo "<p>Não é necessário executar este script novamente.</p>";
        echo "</div>";
        
        echo "<div style='text-align: center; margin-top: 20px;'>";
        echo "<a href='../index.php' class='btn btn-primary'>🏠 Ir para o Site</a>";
        echo "</div>";
    }
    
} else {
    // EXECUTAR: Gerar slugs
    echo "<div class='info'>";
    echo "<h3>🔄 Processando...</h3>";
    echo "</div>";
    
    $result = $mysqli->query("SELECT id, marca, modelo FROM produtos WHERE slug IS NULL OR slug = ''");
    
    $atualizados = 0;
    $produtos_processados = [];
    
    echo "<div class='scrollable'>";
    
    while ($produto = $result->fetch_assoc()) {
        $base_text = trim($produto['marca'] . ' ' . $produto['modelo']);
        $slug = gerar_slug_unico($mysqli, $base_text, $produto['id']);
        
        // Atualizar no banco
        $stmt = $mysqli->prepare("UPDATE produtos SET slug = ? WHERE id = ?");
        $stmt->bind_param('si', $slug, $produto['id']);
        
        if ($stmt->execute()) {
            $atualizados++;
            
            if ($atualizados <= 50) {
                echo "<div class='produto-item'>";
                echo "<span><strong>{$produto['marca']} {$produto['modelo']}</strong></span>";
                echo "<span class='slug'>{$slug}</span>";
                echo "</div>";
            }
            
            $produtos_processados[] = [
                'id' => $produto['id'],
                'nome' => $base_text,
                'slug' => $slug
            ];
        }
        
        $stmt->close();
    }
    
    if ($atualizados > 50) {
        echo "<p style='text-align: center; color: #6c757d; margin-top: 10px; font-weight: bold;'>";
        echo "... e mais " . ($atualizados - 50) . " produtos processados";
        echo "</p>";
    }
    
    echo "</div>";
    
    echo "<div class='success' style='margin-top: 30px;'>";
    echo "<h2>🎉 Slugs Gerados com Sucesso!</h2>";
    echo "<ul style='font-size: 1.1em;'>";
    echo "<li>✅ <strong>{$atualizados} produtos</strong> atualizados</li>";
    echo "<li>✅ Slugs únicos garantidos</li>";
    echo "<li>✅ URLs amigáveis para SEO</li>";
    echo "<li>✅ Compatível com .htaccess</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>🚀 Próximos Passos:</h3>";
    echo "<ol>";
    echo "<li>Verificar se o ficheiro <code>.htaccess</code> tem as regras de rewrite</li>";
    echo "<li>Testar uma URL limpa: <code>/produto/" . ($produtos_processados[0]['slug'] ?? 'exemplo') . "</code></li>";
    echo "<li>Verificar no Google Search Console (após deployment)</li>";
    echo "<li>Criar redirects 301 das URLs antigas (se necessário)</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='diagnostico_rapido.php' class='btn btn-primary'>🔍 Ver Diagnóstico</a> ";
    echo "<a href='../index.php' class='btn btn-primary'>🏠 Ver Site</a>";
    echo "</div>";
}

echo "</div></body></html>";

$mysqli->close();
