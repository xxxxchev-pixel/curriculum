/**
 * GomesTech - Add to Cart com Micro-interação
 * Botão animado com feedback visual e toast
 */

import { toastSuccess } from './toast.js';

// Listener global para botões de adicionar ao carrinho
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-add, [data-add-to-cart]');
    if (!btn) return;
    
    e.preventDefault();
    
    // Prevenir cliques duplos
    if (btn.classList.contains('adding') || btn.classList.contains('added')) {
        return;
    }
    
    // Obter ID do produto
    const productId = btn.dataset.productId || btn.dataset.addToCart;
    if (!productId) {
        console.error('Product ID não encontrado');
        return;
    }
    
    // Estado: adding
    btn.classList.add('adding');
    btn.disabled = true;
    
    try {
        // Fazer pedido ao servidor
        const response = await fetch('/gomestech/carrinho.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                acao: 'add',
                produto_id: productId,
                csrf_token: getCSRFToken()
            })
        });
        
        if (!response.ok) {
            throw new Error('Erro ao adicionar ao carrinho');
        }
        
        // Sucesso - animação
        btn.classList.remove('adding');
        btn.classList.add('added');
        
        // Atualizar contador do carrinho (se existir)
        updateCartCount();
        
        // Mostrar toast
        toastSuccess('Adicionado ao carrinho!');
        
        // Voltar ao normal após animação
        setTimeout(() => {
            btn.classList.remove('added');
            btn.disabled = false;
        }, 1600);
        
    } catch (error) {
        console.error('Erro ao adicionar ao carrinho:', error);
        btn.classList.remove('adding');
        btn.disabled = false;
        
        // Mostrar erro
        if (typeof toastError !== 'undefined') {
            toastError('Erro ao adicionar ao carrinho');
        } else {
            alert('Erro ao adicionar ao carrinho. Tenta novamente.');
        }
    }
});

/**
 * Obter CSRF token do meta tag ou sessão
 */
function getCSRFToken() {
    // Tentar obter de meta tag
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    
    // Tentar obter de input hidden
    const inputToken = document.querySelector('input[name="csrf_token"]');
    if (inputToken) {
        return inputToken.value;
    }
    
    // Fallback: gerar um novo (não recomendado, apenas para demo)
    console.warn('CSRF token não encontrado');
    return '';
}

/**
 * Atualizar contador do carrinho no header
 */
async function updateCartCount() {
    try {
        const response = await fetch('/gomestech/api/cart-count.php');
        const data = await response.json();
        
        const cartBadge = document.querySelector('.cart-count, [data-cart-count]');
        if (cartBadge && data.count !== undefined) {
            cartBadge.textContent = data.count;
            
            // Animar mudança
            cartBadge.style.transform = 'scale(1.3)';
            setTimeout(() => {
                cartBadge.style.transform = 'scale(1)';
            }, 200);
        }
    } catch (error) {
        console.error('Erro ao atualizar contador do carrinho:', error);
    }
}

// Exportar se estiver a usar módulos
export { updateCartCount };
