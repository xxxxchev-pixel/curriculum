/**
 * GomesTech - Microinterações & Efeitos Premium
 * JavaScript para interações subtis e acessíveis
 */

(function() {
  'use strict';

  // Deteta preferência de movimento reduzido
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ===== HEADER STICKY COM CONTRAÇÃO =====
  const header = document.querySelector('.header');
  let lastScroll = 0;

  function handleScroll() {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 64) {
      header?.classList.add('scrolled');
    } else {
      header?.classList.remove('scrolled');
    }
    
    lastScroll = currentScroll;
  }

  window.addEventListener('scroll', handleScroll, { passive: true });

  // ===== SWEEP DE GRADIENTE NOS CARDS (UMA VEZ POR HOVER) =====
  const cards = document.querySelectorAll('.card, .product-card');
  
  cards.forEach(card => {
    let sweepTimeout;
    let canSweep = true;
    
    card.addEventListener('mouseenter', () => {
      if (!prefersReducedMotion && canSweep) {
        card.dataset.sweep = '1';
        canSweep = false;
        
        sweepTimeout = setTimeout(() => {
          card.dataset.sweep = '0';
        }, 1200);
        
        // Permite novo sweep após 3s
        setTimeout(() => {
          canSweep = true;
        }, 3000);
      }
    });
    
    card.addEventListener('mouseleave', () => {
      clearTimeout(sweepTimeout);
    });
  });

  // ===== SWEEP NOS BOTÕES (PRIMEIRA ENTRADA) =====
  const buttons = document.querySelectorAll('.btn');
  
  buttons.forEach(btn => {
    let sweepTimeout;
    let hasHovered = false;
    
    btn.addEventListener('mouseenter', () => {
      if (!prefersReducedMotion && !hasHovered) {
        btn.dataset.hoverSweep = '1';
        hasHovered = true;
        
        sweepTimeout = setTimeout(() => {
          btn.dataset.hoverSweep = '0';
        }, 180);
      }
    });
    
    btn.addEventListener('mouseleave', () => {
      clearTimeout(sweepTimeout);
      // Reset após 600ms fora do botão
      setTimeout(() => {
        hasHovered = false;
      }, 600);
    });
  });

  // ===== ADICIONAR AO CARRINHO COM ANIMAÇÃO =====
  const addToCartButtons = document.querySelectorAll('[data-action="add-to-cart"]');
  const cartBadge = document.querySelector('.cart-badge');
  
  addToCartButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      if (!this.form) return; // Deixa o form submit normal acontecer
      
      // Visual feedback no botão
      const originalHTML = this.innerHTML;
      
      if (!prefersReducedMotion) {
        this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        this.style.pointerEvents = 'none';
        
        // Anima o badge do carrinho
        if (cartBadge) {
          cartBadge.dataset.pulse = '1';
          setTimeout(() => {
            cartBadge.dataset.pulse = '0';
          }, 140);
        }
        
        // Restaura botão após 700ms
        setTimeout(() => {
          this.innerHTML = originalHTML;
          this.style.pointerEvents = '';
        }, 700);
      }
      
      // Anuncia para screen readers
      announceToScreenReader('Produto adicionado ao carrinho');
    });
  });

  // ===== INK EFFECT NOS BADGES =====
  const badges = document.querySelectorAll('.badge');
  
  badges.forEach(badge => {
    const parentCard = badge.closest('.card, .product-card');
    
    if (parentCard) {
      parentCard.addEventListener('mouseenter', () => {
        if (!prefersReducedMotion) {
          badge.dataset.ink = '1';
          setTimeout(() => {
            badge.dataset.ink = '0';
          }, 90);
        }
      });
    }
  });

  // ===== HAMBURGER MENU =====
  const hamburger = document.querySelector('.hamburger-menu');
  const nav = document.querySelector('.main-nav');
  const navOverlay = document.querySelector('.nav-overlay');
  
  function toggleMenu() {
    hamburger?.classList.toggle('active');
    nav?.classList.toggle('active');
    navOverlay?.classList.toggle('active');
    
    // Gerencia foco
    if (nav?.classList.contains('active')) {
      document.body.style.overflow = 'hidden';
      const firstLink = nav.querySelector('a');
      firstLink?.focus();
    } else {
      document.body.style.overflow = '';
    }
  }
  
  hamburger?.addEventListener('click', toggleMenu);
  navOverlay?.addEventListener('click', toggleMenu);
  
  // Fecha com ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && nav?.classList.contains('active')) {
      toggleMenu();
    }
  });

  // ===== AUTO-SUGGEST SEARCH =====
  const searchInput = document.querySelector('.search-input');
  const searchSuggestions = document.querySelector('.search-suggestions');
  let selectedIndex = -1;
  
  if (searchInput && searchSuggestions) {
    searchInput.addEventListener('focus', () => {
      searchSuggestions.classList.add('active');
    });
    
    searchInput.addEventListener('blur', (e) => {
      // Delay para permitir clique nas sugestões
      setTimeout(() => {
        if (!searchSuggestions.contains(document.activeElement)) {
          searchSuggestions.classList.remove('active');
        }
      }, 200);
    });
    
    // Navegação com setas
    searchInput.addEventListener('keydown', (e) => {
      const items = searchSuggestions.querySelectorAll('.search-suggestion-item');
      
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
        updateSelectedItem(items, selectedIndex);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex = Math.max(selectedIndex - 1, -1);
        updateSelectedItem(items, selectedIndex);
      } else if (e.key === 'Enter' && selectedIndex >= 0) {
        e.preventDefault();
        items[selectedIndex]?.click();
      } else if (e.key === 'Escape') {
        searchSuggestions.classList.remove('active');
        selectedIndex = -1;
      }
    });
  }
  
  function updateSelectedItem(items, index) {
    items.forEach((item, i) => {
      if (i === index) {
        item.classList.add('selected');
        item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      } else {
        item.classList.remove('selected');
      }
    });
  }

  // ===== GALERIA COM LENS ZOOM =====
  const productImages = document.querySelectorAll('.product-gallery-main');
  
  productImages.forEach(img => {
    const lens = document.createElement('div');
    lens.className = 'zoom-lens';
    lens.style.cssText = `
      position: absolute;
      width: 120px;
      height: 120px;
      border: 2px solid var(--color-accent-start);
      border-radius: 50%;
      pointer-events: none;
      opacity: 0;
      transition: opacity var(--duration-fast) var(--ease-out);
      overflow: hidden;
      z-index: 10;
    `;
    
    img.parentElement.style.position = 'relative';
    img.parentElement.appendChild(lens);
    
    img.addEventListener('mouseenter', () => {
      if (!prefersReducedMotion) {
        lens.style.opacity = '1';
      }
    });
    
    img.addEventListener('mouseleave', () => {
      lens.style.opacity = '0';
    });
    
    img.addEventListener('mousemove', (e) => {
      if (prefersReducedMotion) return;
      
      const rect = img.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      lens.style.left = `${x - 60}px`;
      lens.style.top = `${y - 60}px`;
      
      // Cria efeito de zoom dentro do lens
      const bgX = -((x / rect.width) * 100);
      const bgY = -((y / rect.height) * 100);
      lens.style.backgroundImage = `url(${img.src})`;
      lens.style.backgroundSize = `${rect.width * 1.5}px ${rect.height * 1.5}px`;
      lens.style.backgroundPosition = `${bgX}% ${bgY}%`;
    });
  });

  // ===== LAZY LOADING COM INTERSECTION OBSERVER =====
  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          const src = img.dataset.src;
          
          if (src) {
            img.src = src;
            img.removeAttribute('data-src');
            observer.unobserve(img);
          }
        }
      });
    }, {
      rootMargin: '50px'
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
      imageObserver.observe(img);
    });
  }

  // ===== SKELETON SHIMMER (APENAS 300MS) =====
  const skeletons = document.querySelectorAll('.skeleton');
  
  skeletons.forEach(skeleton => {
    setTimeout(() => {
      skeleton.classList.add('loaded');
    }, 300);
  });

  // ===== TOAST NOTIFICATIONS =====
  const toastContainer = document.createElement('div');
  toastContainer.className = 'toast-container';
  toastContainer.style.cssText = `
    position: fixed;
    bottom: var(--space-6);
    right: var(--space-6);
    z-index: var(--z-toast);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    max-width: 400px;
  `;
  document.body.appendChild(toastContainer);
  
  window.showToast = function(message, type = 'info', duration = 3500) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
      padding: var(--space-4);
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      color: var(--color-text-primary);
      font-size: var(--font-size-body);
      opacity: 0;
      transform: translateX(100%);
      transition: opacity var(--duration-transition) var(--ease-out),
                  transform var(--duration-transition) var(--ease-spring);
    `;
    
    toastContainer.appendChild(toast);
    
    // Limite de 2 toasts simultâneos
    if (toastContainer.children.length > 2) {
      toastContainer.removeChild(toastContainer.firstChild);
    }
    
    // Anima entrada
    requestAnimationFrame(() => {
      toast.style.opacity = '1';
      toast.style.transform = 'translateX(0)';
    });
    
    // Auto-dismiss
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      
      setTimeout(() => {
        toast.remove();
      }, 500);
    }, duration);
    
    // Anuncia para screen readers
    announceToScreenReader(message);
  };

  // ===== KONAMI CODE EASTER EGG =====
  const konamiCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
  let konamiIndex = 0;
  
  document.addEventListener('keydown', (e) => {
    if (e.key === konamiCode[konamiIndex]) {
      konamiIndex++;
      
      if (konamiIndex === konamiCode.length) {
        activateRetroMode();
        konamiIndex = 0;
      }
    } else {
      konamiIndex = 0;
    }
  });
  
  function activateRetroMode() {
    if (prefersReducedMotion) {
      // Modo estático
      document.body.style.filter = 'sepia(0.3) contrast(1.1)';
      showToast('🕹️ Modo Retro ativado!', 'info', 5000);
    } else {
      // Modo animado
      const retroStyle = document.createElement('style');
      retroStyle.textContent = `
        @keyframes scanline {
          0% { transform: translateY(-100%); }
          100% { transform: translateY(100vh); }
        }
        .retro-scanline {
          position: fixed;
          inset: 0;
          background: linear-gradient(transparent 50%, rgba(0,255,0,0.03) 50%);
          background-size: 100% 4px;
          pointer-events: none;
          z-index: 9999;
          animation: scanline 8s linear infinite;
        }
      `;
      document.head.appendChild(retroStyle);
      
      const scanline = document.createElement('div');
      scanline.className = 'retro-scanline';
      document.body.appendChild(scanline);
      
      document.body.style.textShadow = '0 0 5px rgba(0,255,0,0.5)';
      showToast('🕹️ Modo CRT ativado!', 'info', 5000);
      
      setTimeout(() => {
        scanline.remove();
        retroStyle.remove();
        document.body.style.textShadow = '';
      }, 5000);
    }
  }

  // ===== ARIA LIVE ANNOUNCER =====
  const announcer = document.createElement('div');
  announcer.setAttribute('aria-live', 'polite');
  announcer.setAttribute('aria-atomic', 'true');
  announcer.className = 'sr-only';
  document.body.appendChild(announcer);
  
  function announceToScreenReader(message) {
    announcer.textContent = '';
    setTimeout(() => {
      announcer.textContent = message;
    }, 100);
  }

  // ===== SCROLL TO TOP =====
  const scrollToTopBtn = document.querySelector('.scroll-to-top');
  
  if (scrollToTopBtn) {
    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 300) {
        scrollToTopBtn.style.opacity = '1';
        scrollToTopBtn.style.pointerEvents = 'auto';
      } else {
        scrollToTopBtn.style.opacity = '0';
        scrollToTopBtn.style.pointerEvents = 'none';
      }
    }, { passive: true });
    
    scrollToTopBtn.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: prefersReducedMotion ? 'auto' : 'smooth'
      });
    });
  }

  // ===== PERFORMANCE: INP OPTIMIZATION =====
  // Debounce para inputs
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
  
  // Aplica debounce em campos de busca
  document.querySelectorAll('input[type="search"], input[type="text"]').forEach(input => {
    const originalHandler = input.oninput;
    if (originalHandler) {
      input.oninput = debounce(originalHandler, 150);
    }
  });

})();
