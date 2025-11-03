<?php
session_start();
require_once __DIR__ . '/../config.php';

// Verificar autenticação
if (!isset($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

$mysqli = db_connect();

$message = '';
$message_type = '';
$produtos_afetados = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aplicar_desconto_aleatorio'])) {
    $confirmar = $_POST['confirmar'] ?? 'nao';
    
    if ($confirmar === 'sim') {
        // Garantir que preco_original existe e está preenchido
        $mysqli->query("UPDATE produtos SET preco_original = preco WHERE preco_original IS NULL");
        
        // Buscar todos os produtos
        $result = $mysqli->query("SELECT id, marca, modelo, preco_original, categoria FROM produtos ORDER BY categoria, marca");
        
        while ($produto = $result->fetch_assoc()) {
            // Gerar desconto aleatório entre 35% e 40%
            $desconto = rand(35, 40);
            $preco_original = $produto['preco_original'];
            $preco_promocional = round($preco_original * (1 - $desconto / 100), 2);
            
            // Atualizar produto
            $update = "UPDATE produtos 
                       SET preco = ?, 
                           desconto_promocao = ? 
                       WHERE id = ?";
            $stmt = $mysqli->prepare($update);
            $stmt->bind_param("dii", $preco_promocional, $desconto, $produto['id']);
            $stmt->execute();
            
            $produtos_afetados[] = [
                'marca' => $produto['marca'],
                'modelo' => $produto['modelo'],
                'categoria' => $produto['categoria'],
                'preco_original' => $preco_original,
                'preco_novo' => $preco_promocional,
                'desconto' => $desconto
            ];
        }
        
        $message = "Descontos aleatórios aplicados com sucesso em " . count($produtos_afetados) . " produtos!";
        $message_type = "success";
    }
}

// Buscar estatísticas atuais
$stats = $mysqli->query("
    SELECT 
        COUNT(*) as total_produtos,
        COUNT(CASE WHEN desconto_promocao > 0 THEN 1 END) as com_desconto,
        AVG(desconto_promocao) as desconto_medio,
        SUM(CASE WHEN desconto_promocao > 0 THEN (preco_original - preco) ELSE 0 END) as economia_total
    FROM produtos
    WHERE preco_original IS NOT NULL
")->fetch_assoc();

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoções Aleatórias - Admin GomesTech</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        h1 {
            font-size: 2.5em;
            color: #2C2C2C;
            margin-bottom: 10px;
            border-bottom: 4px solid #FF6A00;
            padding-bottom: 15px;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.1em;
            margin-bottom: 30px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .stat-value {
            font-size: 3em;
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 0.95em;
            opacity: 0.95;
        }
        
        .alert {
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            border-left: 5px solid;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .alert-info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        .alert h3 {
            margin-bottom: 10px;
            font-size: 1.3em;
        }
        
        .btn {
            padding: 15px 35px;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #FF6A00;
            color: white;
        }
        
        .btn-primary:hover {
            background: #E55A00;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255,106,0,0.4);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(220,53,69,0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .produtos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 15px;
            max-height: 600px;
            overflow-y: auto;
            margin: 20px 0;
            padding: 10px;
        }
        
        .produto-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #28a745;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .produto-card:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .produto-info h4 {
            color: #2C2C2C;
            margin-bottom: 8px;
            font-size: 1.1em;
        }
        
        .produto-info small {
            color: #6c757d;
            display: block;
            margin-bottom: 10px;
        }
        
        .preco-info {
            text-align: right;
        }
        
        .preco-original {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9em;
        }
        
        .preco-novo {
            color: #28a745;
            font-size: 1.4em;
            font-weight: bold;
        }
        
        .badge-desconto {
            background: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: bold;
            margin-top: 5px;
            display: inline-block;
        }
        
        .actions-container {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎲 Promoções Aleatórias</h1>
        <p class="subtitle">Aplique descontos de 35-40% aleatoriamente em todos os produtos</p>
        
        <!-- Estatísticas Atuais -->
        <div class="stats">
            <div class="stat-card">
                <span class="stat-value"><?php echo $stats['total_produtos']; ?></span>
                <span class="stat-label">Total de Produtos</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?php echo $stats['com_desconto']; ?></span>
                <span class="stat-label">Com Descontos Ativos</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?php echo $stats['desconto_medio'] > 0 ? round($stats['desconto_medio'], 1) . '%' : '0%'; ?></span>
                <span class="stat-label">Desconto Médio</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?php echo number_format($stats['economia_total'], 2); ?>€</span>
                <span class="stat-label">Economia Total</span>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <h3><?php echo $message_type === 'success' ? '✅ Sucesso!' : '⚠️ Aviso'; ?></h3>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($produtos_afetados)): ?>
            <!-- Mostrar produtos afetados -->
            <div class="alert alert-success">
                <h3>📦 Produtos Atualizados (<?php echo count($produtos_afetados); ?>)</h3>
            </div>
            
            <div class="produtos-grid">
                <?php foreach ($produtos_afetados as $prod): ?>
                    <div class="produto-card">
                        <div class="produto-info">
                            <h4><?php echo htmlspecialchars($prod['marca'] . ' ' . $prod['modelo']); ?></h4>
                            <small><?php echo htmlspecialchars($prod['categoria']); ?></small>
                            <span class="badge-desconto">-<?php echo $prod['desconto']; ?>%</span>
                        </div>
                        <div class="preco-info">
                            <div class="preco-original"><?php echo number_format($prod['preco_original'], 2); ?>€</div>
                            <div class="preco-novo"><?php echo number_format($prod['preco_novo'], 2); ?>€</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="actions-container">
                <a href="promocoes.php" class="btn btn-primary">Voltar às Promoções</a>
                <a href="../index.php" class="btn btn-success">Ver Site</a>
                <a href="../database/restaurar_precos_v2.php" class="btn btn-secondary">Restaurar Preços</a>
            </div>
            
        <?php else: ?>
            <!-- Formulário de confirmação -->
            <div class="alert alert-warning">
                <h3>⚠️ ATENÇÃO - Ação Irreversível</h3>
                <p><strong>Esta ação irá:</strong></p>
                <ul style="margin: 15px 0 15px 30px;">
                    <li>Aplicar descontos <strong>ALEATÓRIOS</strong> entre 35% e 40% em TODOS os produtos</li>
                    <li>Salvar os preços originais (se ainda não salvos)</li>
                    <li>Atualizar a coluna <code>desconto_promocao</code> com o percentual aplicado</li>
                    <li>Permitir restauração posterior dos preços originais</li>
                </ul>
                <p style="margin-top: 15px;"><strong>Cada produto receberá um desconto diferente!</strong></p>
            </div>
            
            <div class="alert alert-info">
                <h3>💡 Como Funciona</h3>
                <ul style="margin: 15px 0 15px 30px;">
                    <li>Sistema gera número aleatório entre 35 e 40 para cada produto</li>
                    <li>Exemplo: Produto A = -37%, Produto B = -35%, Produto C = -40%</li>
                    <li>Preços originais são preservados para restauração</li>
                    <li>Você pode reverter a qualquer momento usando "Restaurar Preços"</li>
                </ul>
            </div>
            
            <div class="actions-container">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="aplicar_desconto_aleatorio" value="1">
                    <input type="hidden" name="confirmar" value="sim">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('⚠️ TEM CERTEZA?\n\nEsta ação irá alterar os preços de TODOS os produtos!\n\nClique OK para confirmar.');">
                        🎲 APLICAR DESCONTOS ALEATÓRIOS
                    </button>
                </form>
                <a href="promocoes.php" class="btn btn-secondary">❌ Cancelar</a>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-info" style="margin-top: 30px;">
            <h3>📚 Dicas de Uso</h3>
            <ul style="margin: 15px 0 15px 30px;">
                <li><strong>Primeiro uso:</strong> Execute o SQL <code>database/adicionar_promocoes.sql</code> no phpMyAdmin</li>
                <li><strong>Reverter preços:</strong> Use <code>database/restaurar_precos_v2.php</code></li>
                <li><strong>Black Friday:</strong> Aplique descontos aleatórios para variedade visual</li>
                <li><strong>Promoções manuais:</strong> Use o painel de promoções para produtos específicos</li>
            </ul>
        </div>
    </div>
</body>
</html>
