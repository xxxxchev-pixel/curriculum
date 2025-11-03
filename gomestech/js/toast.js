/**
 * GomesTech - Toast Notification System
 * Sistema de notificações toast reutilizável e acessível
 */

// Criar container de toasts se não existir
const toastWrap = (() => {
    let wrap = document.querySelector('.toast-wrap');
    if (!wrap) {
        wrap = document.createElement('div');
        wrap.className = 'toast-wrap';
        wrap.setAttribute('aria-live', 'polite');
        wrap.setAttribute('aria-atomic', 'true');
        document.body.appendChild(wrap);
    }
    return wrap;
})();

/**
 * Mostrar toast notification
 * @param {string} message - Mensagem a mostrar
 * @param {number} duration - Duração em ms (default: 2200)
 * @param {string} type - Tipo: 'success', 'error', 'info', 'warning'
 */
export function toast(message, duration = 2200, type = 'info') {
    const toastEl = document.createElement('div');
    toastEl.className = `toast toast-${type}`;
    toastEl.setAttribute('role', 'status');
    
    // Ícone baseado no tipo
    const icons = {
        success: '✓',
        error: '✕',
        info: 'ℹ',
        warning: '⚠'
    };
    
    toastEl.innerHTML = `
        <span class="toast-icon">${icons[type] || icons.info}</span>
        <span class="toast-message">${escapeHtml(message)}</span>
    `;
    
    toastWrap.appendChild(toastEl);
    
    // Animar entrada
    requestAnimationFrame(() => {
        toastEl.classList.add('show');
    });
    
    // Remover após duração
    setTimeout(() => {
        toastEl.classList.remove('show');
        setTimeout(() => {
            toastEl.remove();
        }, 250);
    }, duration);
}

/**
 * Helpers de atalho
 */
export const toastSuccess = (msg, dur) => toast(msg, dur, 'success');
export const toastError = (msg, dur) => toast(msg, dur, 'error');
export const toastInfo = (msg, dur) => toast(msg, dur, 'info');
export const toastWarning = (msg, dur) => toast(msg, dur, 'warning');

/**
 * Escape HTML para prevenir XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Se não estiver a usar modules, expor globalmente
if (typeof window !== 'undefined' && !window.toast) {
    window.toast = toast;
    window.toastSuccess = toastSuccess;
    window.toastError = toastError;
    window.toastInfo = toastInfo;
    window.toastWarning = toastWarning;
}
