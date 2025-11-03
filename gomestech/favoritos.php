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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/gomestech.css">
  <link rel="stylesheet" href="css/favorites.css">
</head>
<body>
  <header class="site-header with-tagline">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:var(--spacing-lg)">
      <div class="logo-wrapper">
        <h1><a href="index.php" style="color:var(--color-primary);text-decoration:none">GomesTech</a></h1>
      </div>
      <nav style="display:flex;gap:var(--spacing-lg);align-items:center">
  <!-- Removido o link Favoritos apenas nesta página -->
        <!-- Removido o botão Login e Registo apenas nesta página -->
      </nav>
    </div>
  </header>

  <main class="section favorites-section">
    <div class="container">
      <div class="favorites-header">
        <h2 class="section-title">❤️ Os meus Favoritos</h2>
        <div class="favorites-actions">
          <button class="btn-secondary btn-clear" onclick="clearFavorites()">Limpar Favoritos</button>
          <a class="btn-primary link-add" href="categorias/catalogo.php" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 14px 24px; font-size: 16px; font-weight: 600;">+ Adicionar mais</a>
        </div>
      </div>

      <?php if(empty($favoritos)): ?>
        <div class="favorites-empty">
          <h3>Sem favoritos ainda</h3>
          <p>Guarda os teus produtos preferidos para veres mais tarde.</p>
          <a href="categorias/catalogo.php" class="btn-primary" style="display:inline-block; text-decoration:none; padding: 14px 28px; font-size: 16px; font-weight: 600;">Ver Catálogo</a>
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

                <div class="favorite-actions product-actions">
                  <form method="post" action="carrinho.php" style="display:inline-block">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn btn-primary">🛒 Adicionar</button>
                  </form>
                  <button class="btn btn-secondary btn-remove" onclick="removeFavorite(<?php echo $p['id']; ?>)">Remover</button>
                  <a href="produto.php?id=<?php echo urlencode($p['id']); ?>" class="btn btn-secondary" style="text-decoration:none">Ver</a>
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
</body>
</html>
