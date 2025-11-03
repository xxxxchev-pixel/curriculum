<?php
/**
 * GomesTech - Limpeza de Ficheiros Não Utilizados
 * Remove ficheiros antigos e desnecessários do projeto
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='pt'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Limpeza de Ficheiros - GomesTech</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
            max-width: 1000px; 
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
        h1 { color: #2C2C2C; border-bottom: 4px solid #FF6A00; padding-bottom: 15px; }
        .warning { 
            background: #fff3cd; 
            border-left: 5px solid #ffc107; 
            color: #856404; 
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .success { 
            background: #d4edda; 
            border-left: 5px solid #28a745; 
            color: #155724; 
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .danger { 
            background: #f8d7da; 
            border-left: 5px solid #dc3545; 
            color: #721c24; 
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .file-item {
            background: #f8f9fa;
            padding: 12px;
            margin: 8px 0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-item.deleted {
            background: #d4edda;
            border-left: 3px solid #28a745;
        }
        .file-item.skipped {
            background: #fff3cd;
            border-left: 3px solid #ffc107;
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
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🧹 Limpeza de Ficheiros Não Utilizados</h1>";

// Ficheiros e diretórios para remover (deprecated)
$files_to_remove = [
    // Backups antigos
    'config-refatorado.php',
    '.htaccess-refatorado',
    'produto-refatorado.php',
    
    // Deprecated (se existir)
    'deprecated/0_instalacao_completa.sql',
    'deprecated/checkout_functions_v2.php',
    'deprecated/database_functions_v2.php',
    'deprecated/gomestech_completo.sql',
    'deprecated/research_data.json',
    'deprecated/teste_registo.php',
    
    // Ficheiros de teste antigos
    'database/verificar_importacao.php',
    'database/verificar_precos.php',
    'database/corrigir_centimos.php',
    
    // Outros
    'adicionar_slugs.php'
];

// Ficheiros a manter (referência)
$files_to_keep = [
    'config.php',
    '.htaccess',
    'index.php',
    'produto.php',
    'carrinho.php',
    'checkout.php',
    'login.php',
    'registo.php',
    'conta.php',
    'favoritos.php',
    'comparacao.php',
    'encomendas.php',
    'ajuda.php',
    'diagnostico.php',
    '404.php',
    '500.php',
    'database/GOMESTECH_COMPLETO_V2.sql',
    'database/gerar_slugs.php',
    'database/importar_catalogo_json.php',
    'database/atualizar_todos_precos.php',
    'database/diagnostico_rapido.php'
];

$executar = $_GET['executar'] ?? 'nao';

if ($executar !== 'sim') {
    echo "<div class='warning'>";
    echo "<h2>⚠️ ATENÇÃO: Operação Irreversível!</h2>";
    echo "<p>Esta ação irá <strong>REMOVER PERMANENTEMENTE</strong> os seguintes ficheiros:</p>";
    echo "<ul>";
    
    $found_files = [];
    foreach ($files_to_remove as $file) {
        $full_path = __DIR__ . '/../' . $file;
        if (file_exists($full_path)) {
            $found_files[] = $file;
            $size = filesize($full_path);
            $size_kb = round($size / 1024, 2);
            echo "<li><code>{$file}</code> ({$size_kb} KB)</li>";
        }
    }
    echo "</ul>";
    
    if (empty($found_files)) {
        echo "</div>";
        echo "<div class='success'>";
        echo "<h3>✅ Nenhum ficheiro para remover</h3>";
        echo "<p>Todos os ficheiros deprecados já foram removidos.</p>";
        echo "</div>";
    } else {
        echo "<p><strong>Total de ficheiros:</strong> " . count($found_files) . "</p>";
        echo "</div>";
        
        echo "<div class='danger'>";
        echo "<h3>📋 Antes de continuar:</h3>";
        echo "<ol>";
        echo "<li>Certifica-te que tens um backup completo</li>";
        echo "<li>Verifica que o site está a funcionar com os novos ficheiros</li>";
        echo "<li>Esta ação NÃO pode ser desfeita</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<div style='text-align: center; margin-top: 30px;'>";
        echo "<a href='?executar=sim' class='btn btn-danger' style='font-size: 1.2em;'>🗑️ REMOVER FICHEIROS AGORA</a><br>";
        echo "<a href='../index.php' class='btn btn-secondary'>❌ Cancelar</a>";
        echo "</div>";
    }
} else {
    // EXECUTAR REMOÇÃO
    echo "<h2>🔄 Processando Remoção...</h2>";
    
    $removed = 0;
    $skipped = 0;
    $errors = 0;
    
    foreach ($files_to_remove as $file) {
        $full_path = __DIR__ . '/../' . $file;
        
        if (file_exists($full_path)) {
            if (is_file($full_path)) {
                if (@unlink($full_path)) {
                    echo "<div class='file-item deleted'>";
                    echo "<span>✅ <strong>{$file}</strong></span>";
                    echo "<span style='color: #28a745;'>REMOVIDO</span>";
                    echo "</div>";
                    $removed++;
                } else {
                    echo "<div class='file-item' style='background: #f8d7da; border-left: 3px solid #dc3545;'>";
                    echo "<span>❌ <strong>{$file}</strong></span>";
                    echo "<span style='color: #dc3545;'>ERRO</span>";
                    echo "</div>";
                    $errors++;
                }
            } elseif (is_dir($full_path)) {
                // Tentar remover diretório vazio
                if (@rmdir($full_path)) {
                    echo "<div class='file-item deleted'>";
                    echo "<span>✅ <strong>{$file}/</strong> (diretório vazio)</span>";
                    echo "<span style='color: #28a745;'>REMOVIDO</span>";
                    echo "</div>";
                    $removed++;
                } else {
                    echo "<div class='file-item skipped'>";
                    echo "<span>⚠️ <strong>{$file}/</strong> (diretório não vazio)</span>";
                    echo "<span style='color: #ffc107;'>MANTIDO</span>";
                    echo "</div>";
                    $skipped++;
                }
            }
        } else {
            echo "<div class='file-item skipped'>";
            echo "<span>ℹ️ <strong>{$file}</strong></span>";
            echo "<span style='color: #6c757d;'>JÁ REMOVIDO</span>";
            echo "</div>";
            $skipped++;
        }
    }
    
    echo "<div class='success' style='margin-top: 30px;'>";
    echo "<h2>📊 Resumo da Limpeza</h2>";
    echo "<ul style='font-size: 1.1em;'>";
    echo "<li>✅ <strong>{$removed} ficheiros removidos</strong></li>";
    echo "<li>⚠️ <strong>{$skipped} ficheiros ignorados</strong> (já não existiam)</li>";
    if ($errors > 0) {
        echo "<li>❌ <strong>{$errors} erros</strong></li>";
    }
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='success'>";
    echo "<h3>🎉 Limpeza Concluída!</h3>";
    echo "<p>O projeto GomesTech está agora mais limpo e organizado.</p>";
    echo "<h4>✅ Ficheiros Ativos Mantidos:</h4>";
    echo "<ul style='column-count: 2; column-gap: 20px;'>";
    foreach ($files_to_keep as $file) {
        echo "<li><code>{$file}</code></li>";
    }
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='diagnostico_rapido.php' class='btn btn-secondary'>🔍 Ver Diagnóstico</a> ";
    echo "<a href='../index.php' class='btn btn-secondary'>🏠 Ir para o Site</a>";
    echo "</div>";
}

echo "</div></body></html>";
