<!-- Hamburger Menu Toggle (para incluir no header) -->
<button class="hamburger-toggle" aria-label="Abrir menu de navegação" aria-expanded="false" aria-controls="hamburger-menu">
  <span class="hamburger-icon" aria-hidden="true">
    <span></span>
    <span></span>
    <span></span>
  </span>
</button>

<!-- Off-Canvas Menu -->
<aside 
  id="hamburger-menu" 
  class="hamburger-menu" 
  role="dialog" 
  aria-modal="true" 
  aria-label="Menu de categorias e marcas"
>
  <!-- Header -->
  <div class="hamburger-header">
    <h2 class="hamburger-title">Categorias</h2>
    <button class="hamburger-close" aria-label="Fechar menu" type="button">
      ×
    </button>
  </div>
  
  <!-- Content (categorias serão carregadas via JS) -->
  <nav class="hamburger-content">
    <p class="brand-loading">Carregando categorias...</p>
  </nav>
  
  <!-- Footer (opcional) -->
  <div class="hamburger-footer">
    <a href="/catalogo.php">📦 Ver todos os produtos</a>
    <a href="/comparacao.php">⚖️ Comparar produtos</a>
  </div>
</aside>

<!-- Backdrop -->
<div class="hamburger-backdrop" aria-hidden="true"></div>
