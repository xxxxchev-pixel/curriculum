// =====================================================
// DERMACARE - JAVASCRIPT PRINCIPAL
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar funcionalidades
    initSmoothScroll();
    initNavbarScroll();
    initFormValidation();
    initTooltips();
    initAnimations();
});

// ===== SMOOTH SCROLL =====
function initSmoothScroll() {
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
}

// ===== NAVBAR SCROLL EFFECT =====
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
            navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
        } else {
            navbar.classList.remove('scrolled');
            navbar.style.boxShadow = 'none';
        }
    });
}

// ===== VALIDAÇÃO DE FORMULÁRIOS =====
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// ===== TOOLTIPS =====
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// ===== ANIMAÇÕES AO SCROLL =====
function initAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
            }
        });
    }, {
        threshold: 0.1
    });
    
    document.querySelectorAll('.service-card, .testimonial-card, .feature-card').forEach(el => {
        observer.observe(el);
    });
}

// ===== UTILITÁRIOS =====

// Mostrar loading
function showLoading(element) {
    element.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div></div>';
}

// Mostrar erro
function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').prepend(alertDiv);
    
    setTimeout(() => alertDiv.remove(), 5000);
}

// Mostrar sucesso
function showSuccess(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').prepend(alertDiv);
    
    setTimeout(() => alertDiv.remove(), 5000);
}

// Formatar data para PT
function formatDatePT(date) {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

// Validar email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validar telefone PT
function validatePhone(phone) {
    const re = /^(\+351)?[0-9]{9}$/;
    return re.test(phone.replace(/\s/g, ''));
}

// Local Storage Helper
const Storage = {
    set: (key, value) => {
        localStorage.setItem(key, JSON.stringify(value));
    },
    get: (key) => {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : null;
    },
    remove: (key) => {
        localStorage.removeItem(key);
    },
    clear: () => {
        localStorage.clear();
    }
};

// Token de autenticação
const Auth = {
    setToken: (token) => {
        Storage.set('auth_token', token);
    },
    getToken: () => {
        return Storage.get('auth_token');
    },
    removeToken: () => {
        Storage.remove('auth_token');
    },
    setUser: (user) => {
        Storage.set('usuario', user);
        Storage.set('usuarioLogado', 'true');
    },
    getUser: () => {
        return Storage.get('usuario');
    },
    removeUser: () => {
        Storage.remove('usuario');
        Storage.remove('usuarioLogado');
    },
    isAuthenticated: () => {
        return !!Auth.getToken() && !!Auth.getUser();
    },
    logout: () => {
        Auth.removeToken();
        Auth.removeUser();
        Storage.clear();
    }
};

// API Helper
const API = {
    baseURL: 'http://localhost:3000/api',
    
    async request(endpoint, options = {}) {
        const token = Auth.getToken();
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };
        
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        
        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                ...options,
                headers
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Erro na requisição');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    get(endpoint) {
        return this.request(endpoint);
    },
    
    post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },
    
    delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }
};

// Exportar para uso global
window.DermaCare = {
    showLoading,
    showError,
    showSuccess,
    formatDatePT,
    validateEmail,
    validatePhone,
    Storage,
    Auth,
    API
};
