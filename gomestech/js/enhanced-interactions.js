// Enhanced interactions for GomesTech
document.addEventListener('DOMContentLoaded', function() {
    
    // Header scroll effect with auto-hide
    const headerMain = document.querySelector('.header-main');
    
    if (headerMain) {
        let lastScroll = 0;
        let scrollTimeout;
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            // Clear timeout to debounce
            clearTimeout(scrollTimeout);
            
            scrollTimeout = setTimeout(() => {
                // Add scrolled class for styling after 100px
                if (currentScroll > 100) {
                    headerMain.classList.add('scrolled');
                } else {
                    headerMain.classList.remove('scrolled');
                }
                
                // Hide header when scrolling down, show when scrolling up
                if (currentScroll > lastScroll && currentScroll > 200) {
                    // Scrolling down and past 200px - hide header
                    headerMain.classList.add('header-hidden');
                } else if (currentScroll < lastScroll) {
                    // Scrolling up - show header
                    headerMain.classList.remove('header-hidden');
                }
                
                lastScroll = currentScroll;
            }, 10);
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add fade-in animation to elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-visible');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.product-card, .stat-card, .benefit-badge').forEach(el => {
        el.classList.add('fade-in-element');
        observer.observe(el);
    });
    
    // Image lazy loading enhancement
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
                img.classList.add('loaded');
                imageObserver.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
    
    // Enhanced button click feedback
    document.querySelectorAll('.btn, .btn-primary, .btn-secondary').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
});

// Add CSS for new animations
const style = document.createElement('style');
style.textContent = `
    .site-header.scrolled {
        padding: 12px 0 !important;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1) !important;
    }
    
    .fade-in-element {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    
    .fade-in-visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    img {
        transition: opacity 0.3s ease;
    }
    
    img.loaded {
        opacity: 1;
    }
    
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    button, .btn {
        position: relative;
        overflow: hidden;
    }
`;
document.head.appendChild(style);

// Theme toggle function
window.toggleTheme = function() {
    const body = document.body;
    const themeIcon = document.getElementById('theme-icon');
    
    body.classList.toggle('dark-mode');
    
    // Update icon
    if (body.classList.contains('dark-mode')) {
        if (themeIcon) themeIcon.textContent = '🌙';
        localStorage.setItem('theme', 'dark');
    } else {
        if (themeIcon) themeIcon.textContent = '☀️';
        localStorage.setItem('theme', 'light');
    }
};

// Load saved theme on page load
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) themeIcon.textContent = '🌙';
}

