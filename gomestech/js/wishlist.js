/**
 * GomesTech - Wishlist (Favoritos)
 * Coração animado com toggle e persistência em localStorage
 */

// Listener global para botões de wishlist
document.addEventListener('click', (e) => {
    const heartBtn = e.target.closest('.heart, [data-wishlist-toggle]');
    if (!heartBtn) return;
    
    e.preventDefault();
    
    // Obter ID do produto
    const productId = heartBtn.dataset.productId || heartBtn.dataset.wishlistToggle;
    if (!productId) {
        console.error('Product ID não encontrado');
        return;
    }
    
    // Toggle estado
    const isActive = heartBtn.classList.toggle('active');
    
    // Animar
    const icon = heartBtn.querySelector('svg, .icon');
    if (icon) {
        icon.animate([
            { transform: 'scale(0.8)' },
            { transform: 'scale(1.2)' },
            { transform: 'scale(1)' }
        ], {
            duration: 300,
            easing: 'ease-out'
        });
    }
    
    // Atualizar localStorage
    if (isActive) {
        addToWishlist(productId);
        
        // Feedback
        if (typeof toastSuccess !== 'undefined') {
            toastSuccess('Adicionado aos favoritos!');
        }
    } else {
        removeFromWishlist(productId);
        
        // Feedback
        if (typeof toastInfo !== 'undefined') {
            toastInfo('Removido dos favoritos');
        }
    }
    
    // Atualizar contador (se existir)
    updateWishlistCount();
});

/**
 * Adicionar produto à wishlist
 */
function addToWishlist(productId) {
    const wishlist = getWishlist();
    if (!wishlist.includes(productId)) {
        wishlist.push(productId);
        saveWishlist(wishlist);
    }
    // Atualizar também 'favorites' CSV
    localStorage.setItem('favorites', wishlist.join(','));
}

/**
 * Remover produto da wishlist
 */
function removeFromWishlist(productId) {
    let wishlist = getWishlist();
    wishlist = wishlist.filter(id => id !== productId);
    saveWishlist(wishlist);
    // Atualizar também 'favorites' CSV
    localStorage.setItem('favorites', wishlist.join(','));
}

/**
 * Obter wishlist do localStorage
 */
function getWishlist() {
    try {
        const stored = localStorage.getItem('favorites') || localStorage.getItem('wishlist');
        
        if (!stored) return [];
        
        // Suportar formato antigo (string "1,2,3") e novo (JSON array)
        if (stored.startsWith('[')) {
            const arr = JSON.parse(stored);
            // Atualizar para CSV
            localStorage.setItem('favorites', arr.join(','));
            return arr;
        } else {
            return stored.split(',').filter(id => id).map(id => id.trim());
        }
    } catch (error) {
        console.error('Erro ao ler wishlist:', error);
        return [];
    }
}

/**
 * Guardar wishlist no localStorage
 */
function saveWishlist(wishlist) {
    try {
        // Guardar como JSON array (formato moderno)
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        
        // Manter compatibilidade com formato antigo
        localStorage.setItem('favorites', wishlist.join(','));
    } catch (error) {
        console.error('Erro ao guardar wishlist:', error);
    }
}

/**
 * Verificar se produto está na wishlist
 */
function isInWishlist(productId) {
    const wishlist = getWishlist();
    return wishlist.includes(productId.toString()) || wishlist.includes(parseInt(productId));
}

/**
 * Atualizar contador de wishlist no header
 */
function updateWishlistCount() {
    const wishlist = getWishlist();
    const count = wishlist.length;
    
    const badge = document.querySelector('.wishlist-count, [data-wishlist-count]');
    if (badge) {
        badge.textContent = count;
        
        // Animar
        badge.style.transform = 'scale(1.3)';
        setTimeout(() => {
            badge.style.transform = 'scale(1)';
        }, 200);
    }
}

/**
 * Sincronizar estado dos botões de wishlist ao carregar página
 */
function syncWishlistButtons() {
    document.querySelectorAll('.heart, [data-wishlist-toggle]').forEach(btn => {
        const productId = btn.dataset.productId || btn.dataset.wishlistToggle;
        if (productId && isInWishlist(productId)) {
            btn.classList.add('active');
        }
    });
    
    updateWishlistCount();
}

// Inicializar ao carregar página
document.addEventListener('DOMContentLoaded', syncWishlistButtons);

// Exportar funções úteis
if (typeof window !== 'undefined') {
    window.wishlist = {
        add: addToWishlist,
        remove: removeFromWishlist,
        get: getWishlist,
        has: isInWishlist,
        updateCount: updateWishlistCount
    };
}

export { addToWishlist, removeFromWishlist, getWishlist, isInWishlist, updateWishlistCount };
