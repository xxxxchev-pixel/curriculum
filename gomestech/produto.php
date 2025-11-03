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
  header('Location: categorias/catalogo.php');
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/gomestech.css">
</head>
<body>
  <header class="site-header with-tagline">
    <div class="container" style="display: flex; align-items: center; justify-content: space-between; gap: var(--spacing-lg);">
      <div class="logo-wrapper">
        <h1><a href="index.php" style="color: var(--color-primary); text-decoration: none;">GomesTech</a></h1>
      </div>
      
      <nav style="display: flex; gap: var(--spacing-lg); align-items: center;">
        <a href="index.php" style="color: var(--text-primary); font-weight: 500; text-decoration: none;">🏠 Início</a>
        <a href="categorias/catalogo.php" style="color: var(--text-primary); font-weight: 500; text-decoration: none;">📦 Catálogo</a>
      </nav>
    </div>
  </header>

  <main class="section">
    <div class="container">
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

          <p class="description"><?php echo htmlspecialchars($p['descricao']); ?></p>

      <?php
      $cat = strtolower($p['categoria'] ?? '');
      if (strpos($cat, 'smartphone') !== false || strpos($cat, 'telemovel') !== false) {
        echo '<ul class="product-specs"><li>Ecrã: 6.1" a 6.7" Full HD+ ou AMOLED</li><li>Processador: Octa-core</li><li>RAM: 4GB a 12GB</li><li>Armazenamento: 64GB a 512GB</li><li>Câmaras: Principal 48MP+, frontal 12MP+</li><li>Bateria: 4000mAh a 5000mAh</li><li>Sistema: Android ou iOS</li></ul>';
      } elseif (strpos($cat, 'laptop') !== false || strpos($cat, 'notebook') !== false) {
        echo '<ul class="product-specs"><li>Ecrã: 14" a 16" Full HD+</li><li>Processador: Intel Core i5/i7 ou AMD Ryzen 5/7</li><li>RAM: 8GB a 32GB</li><li>SSD: 256GB a 1TB</li><li>Placa Gráfica: Integrada ou dedicada</li><li>Sistema: Windows/macOS/Linux</li></ul>';
      } elseif (strpos($cat, 'audio') !== false || strpos($cat, 'áudio') !== false) {
        echo '<ul class="product-specs"><li>Tipo: Bluetooth ou com fios</li><li>Autonomia: 5h a 30h</li><li>Cancelamento de ruído: Sim/Não</li><li>Microfone: Sim</li><li>Compatibilidade: Universal</li></ul>';
      } elseif (strpos($cat, 'frigorifico') !== false) {
        echo '<ul class="product-specs"><li>Capacidade: 200L a 600L</li><li>Tipo: Combinado, Side-by-Side, Uma porta</li><li>Eficiência energética: A++ a D</li><li>Funções: No Frost, congelador rápido, painel digital</li><li>Cor: Branco, inox, preto</li></ul>';
      } elseif (strpos($cat, 'maquina') !== false && strpos($cat, 'lavar') !== false) {
        echo '<ul class="product-specs"><li>Capacidade: 6kg a 12kg</li><li>Eficiência energética: A+++ a D</li><li>Programas: Rápido, Eco, Algodão, Sintéticos, Lã</li><li>Centrifugação: 1000 a 1600 rpm</li><li>Funções: Início diferido, vapor, painel digital</li><li>Cor: Branco, inox</li></ul>';
      } elseif (strpos($cat, 'micro-ondas') !== false) {
        echo '<ul class="product-specs"><li>Capacidade: 17L a 32L</li><li>Potência: 700W a 1200W</li><li>Funções: Grill, descongelação, programas automáticos</li><li>Painel: Analógico ou digital</li><li>Cor: Branco, inox, preto</li></ul>';
      } elseif (strpos($cat, 'wearable') !== false) {
        echo '<ul class="product-specs"><li>Ecrã: AMOLED ou LCD</li><li>Autonomia: 1 a 14 dias</li><li>Funções: Monitorização cardíaca, GPS, notificações, resistência à água</li><li>Compatibilidade: Android, iOS</li><li>Conectividade: Bluetooth, Wi-Fi</li></ul>';
      } elseif (strpos($cat, 'tablet') !== false) {
        echo '<ul class="product-specs"><li>Ecrã: 8" a 12.9" IPS ou AMOLED</li><li>Processador: Quad-core ou superior</li><li>RAM: 3GB a 8GB</li><li>Armazenamento: 32GB a 256GB</li><li>Sistema: Android, iOS, Windows</li><li>Bateria: 4000mAh a 10000mAh</li></ul>';
      } elseif (strpos($cat, 'tv') !== false) {
        echo '<ul class="product-specs"><li>Tamanho: 32" a 85"</li><li>Resolução: Full HD, 4K, 8K</li><li>Painel: LED, OLED, QLED</li><li>Smart TV: Sim</li><li>Conectividade: HDMI, USB, Wi-Fi, Bluetooth</li><li>Funções: HDR, comando por voz, apps integradas</li></ul>';
      } elseif (strpos($cat, 'consola') !== false) {
        echo '<ul class="product-specs"><li>Processador: Octa-core ou superior</li><li>Armazenamento: 500GB a 2TB</li><li>Resolução: Full HD, 4K, 8K</li><li>Conectividade: HDMI, USB, Wi-Fi, Bluetooth</li><li>Funções: Jogos online, apps, comando sem fios</li></ul>';
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

  <style>
    .product-detail{display:grid;grid-template-columns:1fr 1fr;gap:60px;margin-top:40px}
    .product-images{display:flex;justify-content:center;align-items:center;background:var(--card);border:1px solid var(--border);border-radius:16px;padding:40px}
    .main-image{max-width:100%;max-height:500px;object-fit:contain}
    .product-category{display:inline-block;background:var(--accent);color:#000;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:700;text-transform:uppercase;margin-bottom:12px}
    .product-info h1{font-size:36px;font-weight:900;margin-bottom:8px}
    .product-info .brand{color:var(--text-muted);font-size:18px;margin-bottom:24px}
    .price-section{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px;margin:24px 0}
    .price-label{font-size:14px;color:var(--text-muted);margin-bottom:8px}
    .price-value{font-size:42px;font-weight:900;color:var(--accent);margin-bottom:8px}
    .price-store{font-size:14px;color:var(--text-muted)}
    .description{line-height:1.8;color:var(--text-muted);margin:24px 0}
    .product-actions{margin:32px 0}
    .product-actions form{width:100%}
    .qty-selector{display:flex;align-items:center;gap:12px;margin-bottom:20px;justify-content:center}
    .qty-selector label{font-weight:600;color:var(--text)}
    .qty-selector input{width:80px;padding:12px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);text-align:center;font-size:16px;font-weight:600}
    .action-buttons{display:flex;gap:12px;align-items:stretch;width:100%}
    .action-buttons .btn,.action-buttons .btn-secondary{flex:1;font-size:1rem;padding:16px;white-space:nowrap;border:none;cursor:pointer}
    .btn-secondary{background:transparent;border:2px solid var(--accent)!important;color:var(--accent);border-radius:8px;font-weight:700;transition:all 0.3s}
    .btn-secondary:hover{background:var(--accent);color:#000;transform:translateY(-2px);box-shadow:0 8px 20px rgba(255,106,0,0.3)}
    @media(max-width:900px){.product-detail{grid-template-columns:1fr;gap:30px}}
  </style>

  <!-- Scroll to Top Button -->
  <button class="scroll-to-top" title="Voltar ao topo">↑</button>

  <script src="js/interactions.js"></script>
  <script src="js/main.js"></script>
  <script src="js/animations.js"></script>
</body>
</html>
