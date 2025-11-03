document.addEventListener('DOMContentLoaded', ()=>{
  // Popup Produto do Dia
  const popup = document.getElementById('produto-dia');
  const close = document.getElementById('close-popup');
  if(popup){ 
    setTimeout(()=> popup.classList.remove('hidden'), 800); 
    if(close) close.addEventListener('click', ()=> popup.classList.add('hidden')); 
    popup.addEventListener('click', (e)=>{ if(e.target===popup) popup.classList.add('hidden'); });
  }

  // Hero Slider com navegação manual
  const slides = Array.from(document.querySelectorAll('.slide'));
  const prevBtn = document.querySelector('.slider-btn.prev');
  const nextBtn = document.querySelector('.slider-btn.next');
  let idx = 0;
  let autoSlide;

  function show(i){ 
    slides.forEach((s,j)=> s.classList.toggle('active', j===i)); 
  }
  
  function nextSlide(){
    idx = (idx+1) % slides.length;
    show(idx);
  }

  function prevSlide(){
    idx = (idx-1 + slides.length) % slides.length;
    show(idx);
  }

  function startAuto(){
    autoSlide = setInterval(nextSlide, 5000);
  }

  function stopAuto(){
    clearInterval(autoSlide);
  }

  if(slides.length){ 
    show(0); 
    startAuto();
    if(nextBtn) nextBtn.addEventListener('click', ()=>{ stopAuto(); nextSlide(); startAuto(); });
    if(prevBtn) prevBtn.addEventListener('click', ()=>{ stopAuto(); prevSlide(); startAuto(); });
  }

  // Compare buttons
  const compareBtns = Array.from(document.querySelectorAll('.compare-btn'));
  compareBtns.forEach(b=> b.addEventListener('click', ()=>{
    // Sanitizar id (aceitar apenas inteiros, evitar valores errados como slugs ou vazios)
    const rawId = b.dataset.id || '';
    const match = String(rawId).match(/-?\d+/);
    const id = match ? match[0] : null;
    const ids = (localStorage.getItem('compare_ids')||'').split(',').filter(Boolean);
    if (!id) {
      showNotification('ID inválido para comparação', 'error');
      return;
    }
    if(ids.includes(id)){
      showNotification('Produto já adicionado à comparação', 'warning');
      return;
    }
    if(ids.length>=3){ 
      showNotification('Só podes comparar até 3 produtos', 'error'); 
      return; 
    }
    ids.push(id);
    localStorage.setItem('compare_ids', ids.join(','));
    b.classList.add('active');
    b.innerHTML = '✓ <span class="icon-text">Adicionado</span>';
    showNotification('Produto adicionado à comparação!', 'success');
    updateCompareBtn();
  }));

  // Favorite buttons (padronizado para CSV em 'favorites')
  const favBtns = Array.from(document.querySelectorAll('.favorite-btn'));
  favBtns.forEach(b=> b.addEventListener('click', ()=>{
    const rawId = b.dataset.id || '';
    const match = String(rawId).match(/-?\d+/);
    const id = match ? match[0] : null;
    if (!id) {
      showNotification('ID inválido para favoritos', 'error');
      return;
    }
    let favs = (localStorage.getItem('favorites')||'').split(',').filter(Boolean);
    if(favs.includes(id)){
      favs = favs.filter(f => f !== id);
      localStorage.setItem('favorites', favs.join(','));
      b.classList.remove('active');
      b.innerHTML = '❤️ <span class="icon-text">Favorito</span>';
      showNotification('Removido dos favoritos', 'info');
    } else {
      favs.push(id);
      localStorage.setItem('favorites', favs.join(','));
      b.classList.add('active');
      b.innerHTML = '❤️ <span class="icon-text">Favorito</span>';
      showNotification('Adicionado aos favoritos!', 'success');
    }
    updateFavoriteBtn();
  }));

  // Atualizar estado dos botões de favoritos
  function loadFavoriteStates(){
    const favs = (localStorage.getItem('favorites')||'').split(',').filter(Boolean);
    favBtns.forEach(b => {
      if(favs.includes(b.dataset.id)){
        b.classList.add('active');
        b.innerHTML = '❤️ <span class="icon-text">Favorito</span>';
      }
    });
  }
  loadFavoriteStates();

  // Atualizar badge/contador de favoritos (opcional)
  function updateFavoriteBtn(){
    const favs = (localStorage.getItem('favorites')||'').split(',').filter(Boolean);
    let badge = document.querySelector('.favorites-count, [data-favorites-count]');
    if(badge) badge.textContent = favs.length;
  }
  try { window.updateFavoriteBtn = updateFavoriteBtn; } catch(e) {}
  updateFavoriteBtn();

  // Create floating compare button
  function updateCompareBtn(){
    const ids = (localStorage.getItem('compare_ids')||'').split(',').filter(Boolean);
    let btn = document.getElementById('floatingCompareBtn');
    // Detectar caminho base (se estamos em subpasta)
    const basePath = window.location.pathname.includes('/categorias/') ? '../' : '';
    if(ids.length > 0){
      if(!btn){
        // Criar botão principal
        btn = document.createElement('div');
        btn.id = 'floatingCompareBtn';
        btn.style.cssText = 'position:fixed;bottom:30px;right:30px;background:linear-gradient(135deg, #FF6A00, #FF8A3D);color:white;padding:18px 28px;border-radius:50px;box-shadow:0 8px 24px rgba(255,106,0,0.5);z-index:9999;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:10px;transition:all .3s;font-size:17px;border:2px solid rgba(255,255,255,0.3);cursor:pointer;min-width:120px;';
        btn.innerHTML = `
          <a href="${basePath}comparacao.php?ids=${ids.join(',')}" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;flex:1;">
            <span style="font-size:20px">⚖️</span>
            <span class="count" style="background:white;color:#FF6A00;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-weight:bold">${ids.length}</span>
            <span>Comparar</span>
          </a>
          <button id="clearCompareBtn" title="Limpar comparação" style="background:transparent;border:none;color:white;font-size:22px;margin-left:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">✕</button>
        `;
        document.body.appendChild(btn);
        btn.addEventListener('mouseenter', ()=> {
          btn.style.transform = 'translateY(-4px) scale(1.05)';
          btn.style.boxShadow = '0 12px 32px rgba(255,106,0,0.6)';
        });
        btn.addEventListener('mouseleave', ()=> {
          btn.style.transform = 'translateY(0) scale(1)';
          btn.style.boxShadow = '0 8px 24px rgba(255,106,0,0.5)';
        });
        // Limpar comparação ao clicar no X
        btn.querySelector('#clearCompareBtn').addEventListener('click', (e)=>{
          e.preventDefault();
          localStorage.removeItem('compare_ids');
          updateCompareBtn();
        });
      } else {
        // Atualizar link e contador
        btn.querySelector('a').href = basePath + 'comparacao.php?ids=' + ids.join(',');
        btn.querySelector('.count').textContent = ids.length;
      }
    } else {
      if(btn) btn.remove();
    }
  }
  
  // Tornar disponível globalmente para outros scripts chamarem após alteração em localStorage
  try { window.updateCompareBtn = updateCompareBtn; } catch(e) {}
  updateCompareBtn();

  // Notification system
  function showNotification(msg, type='info'){
    const notif = document.createElement('div');
    notif.className = 'notification ' + type;
    notif.textContent = msg;
    notif.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#111;color:#fff;padding:16px 24px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.5);z-index:9999;animation:slideInUp 0.3s;border-left:4px solid ' + (type==='success'?'#00ff88':type==='error'?'#ff4444':'#ff6a00');
    document.body.appendChild(notif);
    setTimeout(()=>{ notif.remove(); }, 3000);
  }

  // Promo flash popup after 30s
  setTimeout(()=>{
    if(!sessionStorage.getItem('promo_shown')){
      sessionStorage.setItem('promo_shown','1');
      showNotification('💥 Promoção Flash: 10% até às 23h!', 'info');
    }
  }, 30000);
});
