<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$mysqli = db_connect();
$success = '';
$error = '';
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $query = "SELECT * FROM produtos WHERE id IN ($placeholders)";
    $stmt = $mysqli->prepare($query);
    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($produto = $result->fetch_assoc()) {
        $quantidade = $_SESSION['cart'][$produto['id']];
        $subtotal = $produto['preco'] * $quantidade;
        $total += $subtotal;
        $cart_items[] = [
            'produto' => $produto,
            'quantidade' => $quantidade,
            'subtotal' => $subtotal
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $morada = trim($_POST['morada'] ?? '');
    $metodo_pagamento = $_POST['metodo_pagamento'] ?? '';
    
    if (empty($nome) || empty($email) || empty($telefone) || empty($morada) || empty($metodo_pagamento)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif (empty($cart_items)) {
        $error = 'O seu carrinho está vazio.';
    } else {
        // Preferir tabela `encomendas` / `encomenda_itens` quando disponível
        $use_db_orders = false;
        try {
            $chk = $mysqli->query("SHOW TABLES LIKE 'encomendas'");
            if ($chk && $chk->num_rows > 0) $use_db_orders = true;
        } catch (Throwable $e) { $use_db_orders = false; }

        if ($use_db_orders) {
            // Inserir encomenda e itens em transação
            $mysqli->begin_transaction();
            try {
                $user_id = intval($_SESSION['user_id']);
                $ins = $mysqli->prepare('INSERT INTO encomendas (user_id, total, morada, telefone, status) VALUES (?, ?, ?, ?, ? )');
                $status = 'pendente';
                $ins->bind_param('idiss', $user_id, $total, $morada, $telefone, $status);
                $ins->execute();
                $encomenda_id = $ins->insert_id;
                $ins->close();

                $it_stmt = $mysqli->prepare('INSERT INTO encomenda_itens (encomenda_id, produto_id, qty, preco) VALUES (?, ?, ?, ?)');
                foreach ($cart_items as $item) {
                    $pid = intval($item['produto']['id']);
                    $qty = intval($item['quantidade']);
                    $preco_item = floatval($item['produto']['preco']);
                    $it_stmt->bind_param('iiid', $encomenda_id, $pid, $qty, $preco_item);
                    $it_stmt->execute();
                }
                $it_stmt->close();

                $mysqli->commit();
                unset($_SESSION['cart']);
                $success = 'Encomenda realizada com sucesso! Receberá um email de confirmação.';
            } catch (Throwable $e) {
                $mysqli->rollback();
                $error = 'Erro ao processar encomenda: ' . $e->getMessage();
            }
        } else {
            // Fallback: tentar tabela orders (legacy)
            $stmt = $mysqli->prepare("INSERT INTO orders (user_id, total, status, metodo_pagamento, morada_entrega) VALUES (?, ?, 'pending', ?, ?)");
            $user_id = $_SESSION['user_id'];
            $stmt->bind_param("idss", $user_id, $total, $metodo_pagamento, $morada);
            if ($stmt->execute()) {
                unset($_SESSION['cart']);
                $success = 'Encomenda realizada com sucesso! Receberá um email de confirmação.';
            } else {
                $error = 'Erro ao processar encomenda.';
            }
        }
    }
}

$user_stmt = $mysqli->prepare("SELECT nome, email, telefone, morada FROM users WHERE id = ?");
$user_id = $_SESSION['user_id'];
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - GomesTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/gomestech.css">
    <style>
        .checkout-container{max-width:1200px;margin:40px auto;padding:0 20px}
        .checkout-grid{display:grid;grid-template-columns:1fr 400px;gap:30px}
        .checkout-form{background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;padding:40px}
        .checkout-summary{background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;padding:30px;align-self:start;position:sticky;top:20px}
        h1{font-size:32px;margin-bottom:10px;color:var(--text-primary)}
        .section-title{font-size:20px;font-weight:600;margin-top:30px;margin-bottom:20px;color:var(--text-primary);border-bottom:2px solid var(--border-color);padding-bottom:10px}
        .section-title:first-child{margin-top:0}
        .form-group{margin-bottom:24px}
        .form-group label{display:block;font-weight:600;margin-bottom:10px;color:var(--text-primary);font-size:16px}
        .form-group input,.form-group textarea{width:100%;padding:18px 20px;border:1px solid var(--border-color);border-radius:10px;font-size:18px;background:var(--bg-secondary);color:var(--text-primary);transition:all .2s;min-height:60px;box-sizing:border-box}
        .form-group textarea{min-height:120px;resize:vertical;font-family:inherit;line-height:1.5}
        .form-group input:focus,.form-group textarea:focus{outline:none;border-color:var(--color-primary);box-shadow:0 0 0 4px rgba(255,106,0,0.1)}
        .payment-methods{display:grid;gap:16px}
        .payment-card{position:relative;border:2px solid var(--border-color);border-radius:12px;padding:20px 24px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:16px;background:var(--bg-secondary)}
        .payment-card:hover{border-color:var(--color-primary);transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        .payment-card input[type="radio"]{width:24px;height:24px;cursor:pointer;accent-color:var(--color-primary)}
        .payment-card label{cursor:pointer;font-size:18px;font-weight:600;flex:1;display:flex;align-items:center;gap:12px}
        .payment-card.selected{border-color:var(--color-primary);background:rgba(255,106,0,0.05)}
        .summary-item{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border-color)}
        .summary-item:last-child{border:none}
        .summary-total{font-size:24px;font-weight:700;color:var(--color-primary);margin-top:20px;padding-top:20px;border-top:2px solid var(--border-color);display:flex;justify-content:space-between}
        .btn-submit{width:100%;padding:20px;background:var(--color-primary);color:white;border:none;border-radius:12px;font-size:20px;font-weight:700;cursor:pointer;transition:all .2s;margin-top:30px}
        .btn-submit:hover{background:#e55f00;transform:translateY(-2px);box-shadow:0 6px 16px rgba(255,106,0,0.4)}
        .alert{padding:20px 24px;border-radius:12px;margin-bottom:30px;font-weight:600;font-size:16px}
        .alert-success{background:#d4edda;color:#155724;border:2px solid #c3e6cb}
        .alert-error{background:#f8d7da;color:#721c24;border:2px solid #f5c6cb}
        .empty-cart{text-align:center;padding:60px 20px}
        .empty-cart h2{font-size:24px;margin-bottom:16px}
        .cart-product{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--border-color);font-size:14px}
        .cart-product img{width:60px;height:60px;object-fit:cover;border-radius:8px}
        @media(max-width:768px){
            .checkout-grid{grid-template-columns:1fr}
            .checkout-summary{position:static}
        }
    </style>
</head>
<body>
    <header class="site-header with-tagline">
        <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:var(--spacing-lg)">
            <div class="logo-wrapper">
                <h1><a href="index.php" style="color:var(--color-primary);text-decoration:none">GomesTech</a></h1>
            </div>
            <nav style="display:flex;gap:var(--spacing-lg);align-items:center">
                <?php if(isset($_SESSION['user_id'])):?>
                    <a href="conta.php">👤 <?php echo htmlspecialchars(explode(' ',$_SESSION['user_nome'])[0]);?></a>
                    <a href="logout.php" style="padding:var(--spacing-sm) var(--spacing-lg);background:#dc3545;color:white;border-radius:var(--radius-md);text-decoration:none;font-weight:600">Sair</a>
                <?php else:?>
                    <a href="login.php" class="btn-auth" style="width:auto;padding:10px 16px;display:inline-flex;align-items:center;gap:8px;">Login e Registo</a>
                <?php endif;?>
            </nav>
        </div>
    </header>
    <main class="checkout-container">
        <?php if($success):?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($success);?></div>
            <div style="text-align:center;padding:40px">
                <a href="index.php" class="btn-submit" style="display:inline-block;width:auto;padding:16px 40px;text-decoration:none">🏠 Voltar ao Início</a>
            </div>
        <?php elseif(empty($cart_items)):?>
            <div class="empty-cart">
                <h2>🛒 O seu carrinho está vazio</h2>
                <p>Adicione produtos para finalizar a compra</p>
                <a href="categorias/catalogo.php" style="display:inline-block;margin-top:20px;padding:14px 32px;background:var(--color-primary);color:white;border-radius:8px;text-decoration:none;font-weight:600">Ver Catálogo</a>
            </div>
        <?php else:?>
            <h1>🛒 Finalizar Compra</h1>
            <?php if($error):?><div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error);?></div><?php endif;?>
            <div class="checkout-grid">
                <div class="checkout-form">
                    <form method="POST">
                        <div class="section-title">📋 Dados de Contacto</div>
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($user['nome']??'');?>" placeholder="João Silva">
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']??'');?>" placeholder="joao@email.com">
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone *</label>
                            <input type="tel" id="telefone" name="telefone" required value="<?php echo htmlspecialchars($user['telefone']??'');?>" placeholder="912 345 678">
                        </div>
                        <div class="section-title">📍 Morada de Entrega</div>
                        <div class="form-group">
                            <label for="morada">Morada Completa *</label>
                            <textarea id="morada" name="morada" required placeholder="Rua, número, andar, apartamento&#10;Código postal&#10;Cidade, País"><?php echo htmlspecialchars($user['morada']??'');?></textarea>
                        </div>
                        <div class="section-title">💳 Método de Pagamento</div>
                        <div class="payment-methods">
                            <div class="payment-card" onclick="selectPayment('multibanco')">
                                <input type="radio" name="metodo_pagamento" value="multibanco" id="pay_multibanco" required>
                                <label for="pay_multibanco">💳 Multibanco (Referência MB)</label>
                            </div>
                            <div class="payment-card" onclick="selectPayment('mbway')">
                                <input type="radio" name="metodo_pagamento" value="mbway" id="pay_mbway">
                                <label for="pay_mbway">📱 MB WAY</label>
                            </div>
                            <div class="payment-card" onclick="selectPayment('cartao')">
                                <input type="radio" name="metodo_pagamento" value="cartao" id="pay_cartao">
                                <label for="pay_cartao">💳 Cartão de Crédito/Débito</label>
                            </div>
                            <div class="payment-card" onclick="selectPayment('paypal')">
                                <input type="radio" name="metodo_pagamento" value="paypal" id="pay_paypal">
                                <label for="pay_paypal">🅿️ PayPal</label>
                            </div>
                        </div>
                        <button type="submit" class="btn-submit">✅ Confirmar Encomenda</button>
                    </form>
                </div>
                <div class="checkout-summary">
                    <h3 style="font-size:22px;margin-bottom:20px;color:var(--text-primary)">📦 Resumo da Encomenda</h3>
                    <div style="max-height:300px;overflow-y:auto;margin-bottom:20px">
                        <?php foreach($cart_items as $item):?>
                            <div class="cart-product">
                                <img src="<?php echo htmlspecialchars($item['produto']['imagem']);?>" alt="">
                                <div style="flex:1">
                                    <div style="font-weight:600;margin-bottom:4px"><?php echo htmlspecialchars($item['produto']['nome']);?></div>
                                    <div style="color:var(--text-muted)"><?php echo $item['quantidade'];?>x €<?php echo number_format($item['produto']['preco'],2);?></div>
                                </div>
                                <div style="font-weight:600;color:var(--color-primary)">€<?php echo number_format($item['subtotal'],2);?></div>
                            </div>
                        <?php endforeach;?>
                    </div>
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>€<?php echo number_format($total,2);?></span>
                    </div>
                    <div class="summary-item">
                        <span>Envio</span>
                        <span style="color:green;font-weight:600">GRÁTIS</span>
                    </div>
                    <div class="summary-total">
                        <span>TOTAL</span>
                        <span>€<?php echo number_format($total,2);?></span>
                    </div>
                </div>
            </div>
        <?php endif;?>
    </main>
    <footer class="footer">
        <div class="footer-bottom"><p>&copy; <?php echo date('Y');?> GomesTech. Todos os direitos reservados.</p></div>
    </footer>
    <script>
        function selectPayment(method){
            document.querySelectorAll('.payment-card').forEach(c=>c.classList.remove('selected'));
            const card=event.currentTarget;
            card.classList.add('selected');
            card.querySelector('input').checked=true;
        }
    </script>
</body>
</html>
