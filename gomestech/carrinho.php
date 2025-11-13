<?php
session_start();
require_once __DIR__ . '/config.php';

$mysqli = db_connect();
$produtos = get_all_produtos($mysqli);
$mysqli->close();

$map = [];
foreach ($produtos as $p) $map[$p['id']] = $p;

$cart = &$_SESSION['cart'];
if (!is_array($cart)) $cart = [];

// Processar ações do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    
    if ($id && isset($map[$id])) {
        if ($action === 'add') {
            // Adicionar produto
            $qty = max(1, intval($_POST['qty'] ?? 1));
            if (!isset($cart[$id])) $cart[$id] = 0;
            $cart[$id] += $qty;
        } elseif ($action === 'increase') {
            // Aumentar quantidade
            if (isset($cart[$id])) {
                $cart[$id]++;
            }
        } elseif ($action === 'decrease') {
            // Diminuir quantidade
            if (isset($cart[$id])) {
                $cart[$id]--;
                if ($cart[$id] <= 0) {
                    unset($cart[$id]);
                }
            }
        } elseif ($action === 'remove') {
            // Remover produto completamente
            unset($cart[$id]);
        }
    }
    header('Location: carrinho.php');
    exit;
}

// Calcular totais (IVA já incluído nos preços)
$subtotal = 0;
foreach ($cart as $id => $qty) {
    if (isset($map[$id])) $subtotal += $map[$id]['preco'] * $qty;
}

// Total = Subtotal (IVA já incluído)
$total = $subtotal;
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GomesTech - Carrinho</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/gomestech.css">
</head>
<body>
  <header class="site-header">
    <div class="container">
      <div class="header-content">
        <div class="logo-section">
          <h1 class="logo">
            <a href="index.php">GomesTech</a>
          </h1>
        </div>
        
        <div class="header-actions">
          <a href="comparacao.php" class="header-icon" title="Comparar Produtos">
            <span class="icon">⚖️</span>
            <span class="label">Comparar</span>
          </a>
          <a href="favoritos.php" class="header-icon" title="Favoritos">
            <span class="icon">❤️</span>
            <span class="label">Favoritos</span>
          </a>
          <a href="carrinho.php" class="header-icon" title="Carrinho">
            <span class="icon">🛒</span>
            <span class="label">Carrinho</span>
          </a>
          <?php if(isset($_SESSION['user_id'])): ?>
            <a href="conta.php" class="header-icon btn-user">
              <span class="icon">👤</span>
              <span class="label">Conta</span>
            </a>
          <?php else: ?>
            <a href="login.php" class="header-icon btn-auth">
              <span class="icon">🔐</span>
              <span class="label">Login</span>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <main class="section">
    <div class="container">
      <h2 class="section-title">🛒 O Seu Carrinho</h2>
      <?php if (!count($cart)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">🛍️</div>
          <h3>O carrinho está vazio</h3>
          <a href="catalogo.php" class="btn cart-checkout-btn" style="max-width: 300px; margin: var(--spacing-xl) auto 0;">Ir às Compras</a>
        </div>
      <?php else: ?>
        <div class="cart-content">
          <table class="cart-table">
            <thead>
              <tr>
                <th>Produto</th>
                <th>Preço</th>
                <th>Quantidade</th>
                <th>Subtotal</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cart as $id => $qty): if(!isset($map[$id])) continue; $p = $map[$id]; ?>
                <tr>
                  <td>
                    <div class="cart-product-info">
                      <img class="cart-product-img" src="<?php echo htmlspecialchars($p['imagem'] ?? 'https://via.placeholder.com/80x80/FF6600/FFFFFF?text=Produto'); ?>" 
                           alt="<?php echo htmlspecialchars($p['modelo']); ?>"
                           onerror="this.src='https://via.placeholder.com/80x80/FF6600/FFFFFF?text=<?php echo urlencode($p['marca']); ?>'">
                      <div>
                        <div class="cart-product-name"><?php echo htmlspecialchars($p['modelo']); ?></div>
                        <div class="cart-product-brand"><?php echo htmlspecialchars($p['marca']); ?></div>
                      </div>
                    </div>
                  </td>
                  <td style="font-size: var(--font-size-lg); font-weight: 600; color: var(--text-primary);">€<?php echo number_format($p['preco'], 2, ',', '.'); ?></td>
                  <td>
                    <div class="cart-qty-control">
                      <form method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <input type="hidden" name="action" value="decrease">
                        <button type="submit" class="cart-qty-btn">−</button>
                      </form>
                      <span class="cart-qty-number"><?php echo $qty; ?></span>
                      <form method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <input type="hidden" name="action" value="increase">
                        <button type="submit" class="cart-qty-btn">+</button>
                      </form>
                    </div>
                  </td>
                  <td style="font-size: var(--font-size-xl); font-weight: 700; color: var(--color-primary);">€<?php echo number_format($p['preco'] * $qty, 2, ',', '.'); ?></td>
                  <td>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="id" value="<?php echo $id; ?>">
                      <input type="hidden" name="action" value="remove">
                      <button type="submit" class="cart-remove-btn">🗑️ Remover</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <div class="cart-summary">
            <h3>💳 Resumo da Encomenda</h3>
            <div class="cart-summary-line">
              <span>Subtotal:</span>
              <strong>€<?php echo number_format($subtotal, 2, ',', '.'); ?></strong>
            </div>
            <div class="cart-summary-line">
              <span>Portes:</span>
              <span style="color: #22c55e; font-weight: 600;">Grátis 🎉</span>
            </div>
            <div class="cart-summary-total">
              <span>Total:</span>
              <span>€<?php echo number_format($total, 2, ',', '.'); ?></span>
            </div>
            <p style="font-size: var(--font-size-sm); color: var(--text-tertiary); margin-top: var(--spacing-md); text-align: center;">
              ℹ️ IVA (23%) incluído nos preços
            </p>
            <?php if(isset($_SESSION['user_id'])): ?>
              <a href="checkout.php" class="cart-checkout-btn">✓ Finalizar Compra</a>
            <?php else: ?>
              <div style="background: rgba(255, 152, 0, 0.1); border: 1px solid rgba(255, 152, 0, 0.3); color: #ff9800; padding: var(--spacing-lg); border-radius: var(--radius-md); margin-bottom: var(--spacing-lg); text-align: center;">
                <p style="margin: 0 0 var(--spacing-md) 0; font-weight: 600;">⚠️ Inicie sessão para finalizar</p>
                <div style="display: flex; gap: var(--spacing-sm);">
                  <a href="login.php" class="cart-checkout-btn btn-auth" style="flex: 1; margin: 0; text-align:center;">Login e Registo</a>
                  <a href="registo.php" class="cart-checkout-btn" style="flex: 1; margin: 0; background: transparent; border: 2px solid var(--color-primary); color: var(--color-primary);">Criar Conta</a>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>&copy; 2025 GomesTech - Tecnologia, Inovação e Preço Justo</p>
    </div>
  </footer>

  <!-- Scroll to Top Button -->
  <button class="scroll-to-top" title="Voltar ao topo">↑</button>

  <script src="js/interactions.js"></script>
  <script src="js/animations.js"></script>
</body>
</html>

