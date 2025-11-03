/**
 * GomesTech - Tilt 3D Effect (Progressive Enhancement)
 * Efeito subtil de tilt 3D nos product cards
 * Apenas em desktop e respeitando prefers-reduced-motion
 */

// Verificar se utilizador prefere movimento reduzido
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Não aplicar efeito se o utilizador preferir movimento reduzido
if (!prefersReducedMotion) {
    initTiltEffect();
}

function initTiltEffect() {
    const cards = document.querySelectorAll('.product-card, [data-tilt]');
    
    // Apenas em dispositivos com hover (desktop)
    const hasHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    if (!hasHover) return;
    
    cards.forEach(card => {
        const maxTilt = parseFloat(card.dataset.tiltMax) || 6; // graus máximos de rotação
        const perspective = parseFloat(card.dataset.tiltPerspective) || 900;
        
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Calcular rotação baseada na posição do mouse
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = ((y - centerY) / centerY) * -maxTilt;
            const rotateY = ((x - centerX) / centerX) * maxTilt;
            
            // Aplicar transformação
            card.style.transform = `
                perspective(${perspective}px) 
                rotateX(${rotateX}deg) 
                rotateY(${rotateY}deg) 
                translateY(-2px)
                scale3d(1.02, 1.02, 1.02)
            `;
            
            // Efeito de brilho (opcional)
            const glare = card.querySelector('.tilt-glare');
            if (glare) {
                const glareOpacity = Math.abs((x - centerX) / centerX) * 0.3;
                glare.style.opacity = glareOpacity;
                glare.style.left = `${x}px`;
                glare.style.top = `${y}px`;
            }
        });
        
        card.addEventListener('mouseleave', () => {
            // Voltar à posição original suavemente
            card.style.transform = '';
            
            const glare = card.querySelector('.tilt-glare');
            if (glare) {
                glare.style.opacity = '0';
            }
        });
        
        // Adicionar will-change para performance
        card.style.willChange = 'transform';
        card.style.transition = 'transform 0.1s ease-out';
    });
}

// Reinicializar se cards forem adicionados dinamicamente
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1 && !prefersReducedMotion) {
                if (node.matches('.product-card, [data-tilt]')) {
                    initTiltEffect();
                }
                
                // Verificar descendentes
                const cards = node.querySelectorAll('.product-card, [data-tilt]');
                if (cards.length > 0) {
                    initTiltEffect();
                }
            }
        });
    });
});

// Observar adições ao DOM
observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Exportar
export { initTiltEffect };
