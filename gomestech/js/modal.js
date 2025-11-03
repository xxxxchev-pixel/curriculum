/**
 * GomesTech - Modal Acessível
 * Modal com trap de foco, Esc para fechar, restauro de foco
 * WCAG 2.2 AA compliant
 */

class ModalManager {
    constructor(modalId) {
        this.modal = document.getElementById(modalId);
        if (!this.modal) {
            console.warn(`Modal ${modalId} não encontrado`);
            return;
        }
        
        this.content = this.modal.querySelector('.modal-content');
        this.closeBtn = this.modal.querySelector('.close, [data-close-modal]');
        this.lastFocusedElement = null;
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        // Event listeners
        this.closeBtn?.addEventListener('click', () => this.close());
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
        
        // Botões que abrem o modal
        document.querySelectorAll(`[data-open-modal="${this.modal.id}"]`).forEach(btn => {
            btn.addEventListener('click', () => this.open());
        });
    }
    
    open() {
        if (this.isOpen) return;
        
        this.lastFocusedElement = document.activeElement;
        this.modal.hidden = false;
        
        // Animar entrada
        requestAnimationFrame(() => {
            this.modal.classList.add('show');
        });
        
        // Bloquear scroll do body
        document.body.style.overflow = 'hidden';
        
        // Focar primeiro elemento focável ou o conteúdo
        const firstFocusable = this.getFocusableElements()[0];
        if (firstFocusable) {
            firstFocusable.focus();
        } else {
            this.content?.focus();
        }
        
        // Event listeners
        document.addEventListener('keydown', this.handleKeydown);
        
        this.isOpen = true;
        
        // Dispatch evento personalizado
        this.modal.dispatchEvent(new CustomEvent('modal:opened'));
    }
    
    close() {
        if (!this.isOpen) return;
        
        this.modal.classList.remove('show');
        
        setTimeout(() => {
            this.modal.hidden = true;
            document.body.style.overflow = '';
            
            // Restaurar foco
            this.lastFocusedElement?.focus();
            
            this.isOpen = false;
            
            // Dispatch evento personalizado
            this.modal.dispatchEvent(new CustomEvent('modal:closed'));
        }, 200);
        
        // Remover event listeners
        document.removeEventListener('keydown', this.handleKeydown);
    }
    
    handleKeydown = (e) => {
        if (e.key === 'Escape') {
            e.preventDefault();
            this.close();
            return;
        }
        
        if (e.key === 'Tab') {
            this.trapFocus(e);
        }
    }
    
    trapFocus(e) {
        const focusables = this.getFocusableElements();
        if (focusables.length === 0) return;
        
        const first = focusables[0];
        const last = focusables[focusables.length - 1];
        
        if (e.shiftKey) {
            // Shift + Tab
            if (document.activeElement === first) {
                e.preventDefault();
                last.focus();
            }
        } else {
            // Tab
            if (document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }
    
    getFocusableElements() {
        const selector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
        return Array.from(this.content?.querySelectorAll(selector) || []);
    }
}

// Inicializar modal "Produto do Dia" se existir
document.addEventListener('DOMContentLoaded', () => {
    const produtoDoDiaModal = new ModalManager('produto-do-dia-modal');
    
    // Expor função global para abrir
    if (produtoDoDiaModal.modal) {
        window.showProdutoDoDia = () => produtoDoDiaModal.open();
        window.hideProdutoDoDia = () => produtoDoDiaModal.close();
    }
    
    // Auto-abrir após 2 segundos (opcional, comentado por padrão)
    // setTimeout(() => {
    //     if (window.showProdutoDoDia && !sessionStorage.getItem('pdd_shown')) {
    //         window.showProdutoDoDia();
    //         sessionStorage.setItem('pdd_shown', '1');
    //     }
    // }, 2000);
});

// Exportar para uso como módulo
export { ModalManager };
