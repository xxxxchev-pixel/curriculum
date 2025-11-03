<?php
session_start();
require_once __DIR__ . '/../config.php';

// Verificar autenticação
if (!isset($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

$mysqli = db_connect();

// Processar ações
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_promotion':
                $produto_id = intval($_POST['produto_id']);
                $desconto = floatval($_POST['desconto']);
                $data_inicio = $_POST['data_inicio'];
                $data_fim = $_POST['data_fim'];
                
                $query = "INSERT INTO promocoes (produto_id, desconto, data_inicio, data_fim, ativo) 
                         VALUES (?, ?, ?, ?, 1)";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("idss", $produto_id, $desconto, $data_inicio, $data_fim);
                
                if ($stmt->execute()) {
                    $message = "Promoção adicionada com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao adicionar promoção: " . $mysqli->error;
                    $message_type = "error";
                }
                break;
                
            case 'toggle_promotion':
                $promo_id = intval($_POST['promo_id']);
                $query = "UPDATE promocoes SET ativo = NOT ativo WHERE id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("i", $promo_id);
                $stmt->execute();
                $message = "Promoção atualizada!";
                $message_type = "success";
                break;
                
            case 'delete_promotion':
                $promo_id = intval($_POST['promo_id']);
                $query = "DELETE FROM promocoes WHERE id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("i", $promo_id);
                $stmt->execute();
                $message = "Promoção removida!";
                $message_type = "success";
                break;
                
            case 'set_produto_dia':
                $produto_id = intval($_POST['produto_id']);
                // Remover produto do dia anterior
                $mysqli->query("UPDATE produtos SET produto_dia = 0");
                // Definir novo produto do dia
                $query = "UPDATE produtos SET produto_dia = 1 WHERE id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("i", $produto_id);
                $stmt->execute();
                $message = "Produto do dia definido!";
                $message_type = "success";
                break;
        }
    }
}

// Buscar promoções ativas
$promocoes = [];
$query = "SELECT p.*, pr.marca, pr.modelo, pr.preco 
          FROM promocoes p 
          JOIN produtos pr ON p.produto_id = pr.id 
          ORDER BY p.data_inicio DESC";
$result = $mysqli->query($query);
while ($row = $result->fetch_assoc()) {
    $promocoes[] = $row;
}

// Buscar todos os produtos
$produtos = [];
$query = "SELECT id, marca, modelo, preco, produto_dia FROM produtos ORDER BY marca, modelo";
$result = $mysqli->query($query);
while ($row = $result->fetch_assoc()) {
    $produtos[] = $row;
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoções - Admin GomesTech</title>
    <link rel="stylesheet" href="../css/gomestech.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            font-size: 28px;
            color: #2C2C2C;
        }
        
        .btn-back {
            background: #FF6A00;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #E55D00;
            transform: translateY(-2px);
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #2C2C2C;
            border-bottom: 3px solid #FF6A00;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2C2C2C;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #E0E0E0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #FF6A00;
        }
        
        .btn-submit {
            width: 100%;
            background: #FF6A00;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: #E55D00;
            transform: translateY(-2px);
        }
        
        .promo-list {
            list-style: none;
        }
        
        .promo-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #FF6A00;
        }
        
        .promo-item.inactive {
            opacity: 0.6;
            border-left-color: #999;
        }
        
        .promo-info {
            flex: 1;
        }
        
        .promo-info strong {
            display: block;
            font-size: 16px;
            color: #2C2C2C;
            margin-bottom: 5px;
        }
        
        .promo-info span {
            font-size: 14px;
            color: #666;
        }
        
        .promo-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-toggle {
            background: #4CAF50;
            color: white;
        }
        
        .btn-toggle.inactive {
            background: #999;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
        }
        
        .btn-small:hover {
            transform: translateY(-2px);
        }
        
        .produto-dia-badge {
            background: #FFD700;
            color: #000;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>🎁 Gestão de Promoções</h1>
            <a href="dashboard.php" class="btn-back">← Voltar ao Dashboard</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid-2">
            <!-- Adicionar Promoção -->
            <div class="card">
                <h2>➕ Nova Promoção</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_promotion">
                    
                    <div class="form-group">
                        <label>Produto:</label>
                        <select name="produto_id" required>
                            <option value="">Selecionar produto...</option>
                            <?php foreach ($produtos as $prod): ?>
                                <option value="<?php echo $prod['id']; ?>">
                                    <?php echo htmlspecialchars($prod['marca'] . ' ' . $prod['modelo']); ?> 
                                    (<?php echo number_format($prod['preco'], 2); ?>€)
                                    <?php if ($prod['produto_dia']): ?>
                                        <span class="produto-dia-badge">⭐ PRODUTO DO DIA</span>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Desconto (%):</label>
                        <input type="number" name="desconto" min="1" max="90" step="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Data Início:</label>
                        <input type="date" name="data_inicio" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Data Fim:</label>
                        <input type="date" name="data_fim" required>
                    </div>
                    
                    <button type="submit" class="btn-submit">Criar Promoção</button>
                </form>
            </div>
            
            <!-- Definir Produto do Dia -->
            <div class="card">
                <h2>⭐ Produto do Dia</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="set_produto_dia">
                    
                    <div class="form-group">
                        <label>Escolher Produto:</label>
                        <select name="produto_id" required>
                            <option value="">Selecionar produto...</option>
                            <?php foreach ($produtos as $prod): ?>
                                <option value="<?php echo $prod['id']; ?>" <?php echo $prod['produto_dia'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prod['marca'] . ' ' . $prod['modelo']); ?>
                                    <?php if ($prod['produto_dia']): ?>
                                        ⭐ (ATUAL)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-submit">Definir como Produto do Dia</button>
                </form>
                
                <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #FFD700;">
                    <strong>💡 Dica:</strong> O produto do dia aparecerá no pop-up ao entrar no site e em destaque no slider principal.
                </div>
            </div>
        </div>
        
        <!-- Lista de Promoções -->
        <div class="card">
            <h2>📋 Promoções Ativas</h2>
            
            <?php if (empty($promocoes)): ?>
                <p style="color: #666; text-align: center; padding: 40px;">
                    Nenhuma promoção criada ainda. Adicione a primeira promoção acima! 🎉
                </p>
            <?php else: ?>
                <ul class="promo-list">
                    <?php foreach ($promocoes as $promo): ?>
                        <li class="promo-item <?php echo $promo['ativo'] ? '' : 'inactive'; ?>">
                            <div class="promo-info">
                                <strong>
                                    <?php echo htmlspecialchars($promo['marca'] . ' ' . $promo['modelo']); ?>
                                </strong>
                                <span>
                                    -<?php echo $promo['desconto']; ?>% | 
                                    Preço original: <?php echo number_format($promo['preco'], 2); ?>€ | 
                                    Preço promocional: <?php echo number_format($promo['preco'] * (1 - $promo['desconto']/100), 2); ?>€ |
                                    <?php echo date('d/m/Y', strtotime($promo['data_inicio'])); ?> → 
                                    <?php echo date('d/m/Y', strtotime($promo['data_fim'])); ?>
                                    <?php echo $promo['ativo'] ? '✅ ATIVA' : '⏸️ PAUSADA'; ?>
                                </span>
                            </div>
                            <div class="promo-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_promotion">
                                    <input type="hidden" name="promo_id" value="<?php echo $promo['id']; ?>">
                                    <button type="submit" class="btn-small btn-toggle <?php echo $promo['ativo'] ? '' : 'inactive'; ?>">
                                        <?php echo $promo['ativo'] ? 'Pausar' : 'Ativar'; ?>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Remover esta promoção?');">
                                    <input type="hidden" name="action" value="delete_promotion">
                                    <input type="hidden" name="promo_id" value="<?php echo $promo['id']; ?>">
                                    <button type="submit" class="btn-small btn-delete">Remover</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
