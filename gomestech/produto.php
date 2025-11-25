<?php
session_start();
require_once __DIR__ . '/config.php';

$mysqli = db_connect();

// Suportar tanto ID quanto slug
$id = $_GET['id'] ?? '';
$url = $_GET['url'] ?? '';

$p = null;

if ($url) {
  // URL amigável (slug)
  $stmt = $mysqli->prepare("SELECT * FROM produtos WHERE slug = ?");
  $stmt->bind_param("s", $url);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $p = $result->fetch_assoc();
  }
  $stmt->close();
} elseif ($id) {
  // ID tradicional
  $stmt = $mysqli->prepare("SELECT * FROM produtos WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $p = $result->fetch_assoc();
  }
  $stmt->close();
}

if (!$p) {
  $mysqli->close();
  header('Location: catalogo.php');
  exit;
}

$mysqli->close();
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo htmlspecialchars($p['modelo']); ?> - GomesTech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/gomestech.css">
  <link rel="stylesheet" href="css/hamburger-menu.css">
  <link rel="stylesheet" href="css/product.css">
  <link rel="stylesheet" href="css/layout-improvements.css">
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main class="section">
    <div class="container">
      <!-- Breadcrumb -->
      <nav class="breadcrumbs">
        <a href="index.php">Início</a>
        <span>›</span>
        <a href="catalogo.php">Catálogo</a>
        <span>›</span>
        <span><?php echo htmlspecialchars($p['categoria']); ?></span>
      </nav>
      <div class="product-detail">
        <div class="product-images">
          <img src="<?php echo htmlspecialchars($p['imagem'] ?? 'https://via.placeholder.com/600x400/FF6A00/FFFFFF?text=' . urlencode($p['marca'])); ?>" 
               alt="<?php echo htmlspecialchars($p['modelo']); ?>" 
               class="main-image"
               onerror="this.src='https://via.placeholder.com/600x400/FF6A00/FFFFFF?text=<?php echo urlencode($p['marca']); ?>'">
        </div>
        
        <div class="product-info">
          <span class="product-category"><?php echo htmlspecialchars($p['categoria']); ?></span>
          <h1><?php echo htmlspecialchars($p['modelo']); ?></h1>
          <p class="brand">Por <?php echo htmlspecialchars($p['marca']); ?></p>
          
          <div class="price-section">
            <p class="price-label">Preço</p>
            <p class="price-value">€<?php echo number_format($p['preco'], 2, ',', '.'); ?></p>
            <p class="price-store">Disponível em <?php echo htmlspecialchars($p['loja']); ?></p>
          </div>

          <!-- Benefícios -->
          <div class="product-benefits">
            <div class="benefit-badge">
              <span class="icon">🚚</span>
              <span>Envio Grátis</span>
            </div>
            <div class="benefit-badge">
              <span class="icon">🔄</span>
              <span>Devolução 30 dias</span>
            </div>
            <div class="benefit-badge">
              <span class="icon">🛡️</span>
              <span>Garantia 2 anos</span>
            </div>
            <div class="benefit-badge">
              <span class="icon">💳</span>
              <span>Pagamento seguro</span>
            </div>
          </div>

          <p class="description"><?php echo htmlspecialchars($p['descricao']); ?></p>

          <?php
          // Exibir especificações técnicas se existirem - DENTRO DA ÁREA DO PRODUTO
          if (!empty($p['especificacoes'])) {
              $specs = json_decode($p['especificacoes'], true);
              if ($specs && is_array($specs) && count($specs) > 0) {
                  echo '<div class="product-specs-section">';
                  echo '<button class="specs-toggle-btn" onclick="toggleSpecs(this)"><span class="icon">📋</span> <span class="specs-text">Especificações Técnicas</span> <span class="arrow">▼</span></button>';
                  echo '<div class="product-specs-content" style="display:none;">';
                  echo '<ul class="product-specs">';
                  foreach ($specs as $key => $value) {
                      if (!empty($value)) {
                          // Formatar o label (primeira letra maiúscula)
                          $label = ucfirst(str_replace('_', ' ', $key));
                          echo '<li><span class="spec-icon">✓</span><strong>' . htmlspecialchars($label) . ':</strong> ' . htmlspecialchars($value) . '</li>';
                      }
                  }
                  echo '</ul>';
                  echo '</div>';
                  echo '</div>';
              }
          }
          ?>

          <div class="product-actions">
            <form method="post" action="carrinho.php" id="add-to-cart-form">
              <input type="hidden" name="id" value="<?php echo htmlspecialchars($p['id']); ?>">
              <input type="hidden" name="action" value="add">
              
              <div class="qty-selector">
                <label>Quantidade:</label>
                <input type="number" name="qty" value="1" min="1" max="10">
              </div>
              
              <div class="action-buttons">
                <button type="submit" class="btn btn-hero">🛒 Adicionar ao Carrinho</button>
                <button type="button" class="btn-secondary compare-btn" data-id="<?php echo htmlspecialchars($p['id']); ?>">⚖️ Adicionar à Comparação</button>
              </div>
            </form>
          </div>
        </div>
      </div>
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
  <script src="js/main.js"></script>
  <script src="js/animations.js"></script>
  <script src="js/enhanced-interactions.js"></script>
  <script>
  function toggleSpecs(btn) {
    const content = btn.nextElementSibling;
    const arrow = btn.querySelector('.arrow');
    if (content.style.display === 'none') {
      content.style.display = 'block';
      arrow.textContent = '▲';
      btn.classList.add('active');
    } else {
      content.style.display = 'none';
      arrow.textContent = '▼';
      btn.classList.remove('active');
    }
  }
  </script>
</body>
</html>
