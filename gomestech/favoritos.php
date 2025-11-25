<?php
session_start();
require_once __DIR__ . '/config.php';

$mysqli = db_connect();
$all = get_all_produtos($mysqli);
$mysqli->close();

// Mapear produtos por id
$map = [];
foreach ($all as $p) { $map[$p['id']] = $p; }

// Obter favoritos a partir do parâmetro ids (ex.: ?ids=1,2,3)

// Se não houver ?ids=, tentar sincronizar com localStorage via JS
$ids_param = $_GET['ids'] ?? '';
if (empty($ids_param)) {
  echo "<script>if (localStorage.getItem('favorites')) { window.location.search = '?ids=' + encodeURIComponent(localStorage.getItem('favorites')); }</script>";
  $ids_param = '';
}
$ids = array_values(array_filter(array_map('intval', explode(',', $ids_param))));

$favoritos = [];
foreach ($ids as $id) {
  if (isset($map[$id])) $favoritos[] = $map[$id];
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Favoritos - GomesTech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/gomestech.css">
  <link rel="stylesheet" href="css/hamburger-menu.css">
  <link rel="stylesheet" href="css/favorites.css">
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main class="section favorites-section" style="padding-top: 180px;">
    <div class="container">
      <div class="favorites-header">
        <h2 class="section-title">❤️ Os meus Favoritos</h2>
      </div>

      <?php if(empty($favoritos)): ?>
        <div class="favorites-empty">
          <h3>Sem favoritos ainda</h3>
          <p>Guarda os teus produtos preferidos para veres mais tarde.</p>
          <a href="catalogo.php" class="btn-primary" style="display:inline-block; text-decoration:none; padding: 16px 32px; font-size: 16px; font-weight: 700; background: linear-gradient(135deg, var(--color-primary) 0%, #FF8534 100%); color: white; border-radius: 12px; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(255, 106, 0, 0.3);">Ver Catálogo</a>
        </div>
      <?php else: ?>
        <div class="favorites-grid">
          <?php foreach($favoritos as $p): ?>
            <article class="favorite-card">
              <div class="favorite-image">
                <img src="<?php echo htmlspecialchars($p['imagem'] ?? 'https://via.placeholder.com/600x400/FF6A00/FFFFFF?text=' . urlencode($p['marca'])); ?>" alt="<?php echo htmlspecialchars($p['modelo']); ?>">
              </div>
              <div class="favorite-info">
                <span class="product-category"><?php echo htmlspecialchars($p['categoria']); ?></span>
                <h3 class="product-title" style="margin:0"><?php echo htmlspecialchars($p['marca'] . ' ' . $p['modelo']); ?></h3>
                <div class="product-price">€<?php echo number_format($p['preco'],2,',','.'); ?></div>

                <div class="product-actions" style="display: flex; flex-direction: column; align-items: center; gap: 10px; width: 100%; margin-top: 16px;">
                  <form method="post" action="carrinho.php" style="width: 100%; display: flex; justify-content: center;">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn btn-primary btn-cart-main" style="width: 100%; padding: 14px; font-size: 16px; font-weight: 700; background: linear-gradient(135deg, #FF6A00, #FF8534); color: white; border: none; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(255, 106, 0, 0.3);">
                      🛒 Adicionar ao Carrinho
                    </button>
                  </form>
                  <div class="product-secondary-actions" style="display: flex; justify-content: center; gap: 12px; width: 100%;">
                    <button class="btn-icon btn-secondary-action btn-remove-fav" onclick="removeFavorite(<?php echo $p['id']; ?>)" style="flex: 1; padding: 12px; background: white; border: 2px solid #E5E5E7; border-radius: 10px; color: #DC3545; font-weight: 600; cursor: pointer; transition: all 0.3s ease;" title="Remover dos favoritos">
                      ❌ Remover
                    </button>
                    <a href="produto.php?id=<?php echo urlencode($p['id']); ?>" class="btn-icon btn-secondary-action btn-view-product" style="flex: 1; padding: 12px; background: white; border: 2px solid #E5E5E7; border-radius: 10px; color: #666; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease;" title="Ver produto">
                      👁️ Ver
                    </a>
                  </div>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script>
    // Sincronização robusta com localStorage evitando loops
    (function syncFromLocal(){
      try {
        const url = new URL(window.location.href);
        const urlIds = (url.searchParams.get('ids')||'')
          .split(',')
          .map(x=>parseInt(x,10))
          .filter(n=>!isNaN(n));
        
        // Ler favoritos do localStorage (formato: "1,2,3")
        const favsString = localStorage.getItem('favorites') || '';
        const favs = favsString
          .split(',')
          .map(x=>parseInt(x,10))
          .filter(n=>!isNaN(n));

        // Ordenar e remover duplicados para comparação estável
        const normalize = arr => Array.from(new Set(arr)).sort((a,b)=>a-b);
        const A = normalize(urlIds);
        const B = normalize(favs);

        const same = A.length===B.length && A.every((v,i)=>v===B[i]);

        // Caso 1: sem ids na URL e sem favoritos → não redireciona
        if(A.length===0 && B.length===0){
          return; // evita loop de reload em página vazia
        }

        // Se diferem, atualizar URL para refletir localStorage
        if(!same){
          if(B.length>0){
            url.searchParams.set('ids', B.join(','));
          } else {
            url.searchParams.delete('ids');
          }
          const next = url.toString();
          if(next !== window.location.href){
            window.location.replace(next);
          }
        }
      } catch(e){ /* silencioso */ }
    })();

    function removeFavorite(id){
      // Ler favoritos do localStorage (formato string "1,2,3")
      const favsString = localStorage.getItem('favorites') || '';
      let favs = favsString.split(',').map(x => parseInt(x, 10)).filter(n => !isNaN(n));
      
      // Remover o ID
      favs = favs.filter(x => x !== parseInt(id));
      
      // Salvar de volta no formato string
      localStorage.setItem('favorites', favs.join(','));
      
      // Atualizar URL
      const url = new URL(window.location.href);
      const ids = favs.join(',');
      if(ids) { url.searchParams.set('ids', ids); } else { url.searchParams.delete('ids'); }
      window.location.replace(url.toString());
    }
    function clearFavorites(){
      localStorage.removeItem('favorites');
      const url = new URL(window.location.href);
      url.searchParams.delete('ids');
      window.location.replace(url.toString());
    }
  </script>
  <script src="js/enhanced-interactions.js"></script>
</body>
</html>
