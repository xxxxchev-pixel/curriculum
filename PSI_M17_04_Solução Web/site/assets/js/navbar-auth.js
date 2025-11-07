/**
 * Navbar Authentication Manager
 * Gerencia o estado de autenticação no navbar de todas as páginas
 */

document.addEventListener('DOMContentLoaded', function() {
    atualizarNavbarAuth();
});

/**
 * Atualiza o navbar baseado no estado de autenticação
 */
function atualizarNavbarAuth() {
    const user = Auth.getUser();
    const navbarNav = document.querySelector('#navbarNav .navbar-nav');
    
    if (!navbarNav) {
        console.warn('Navbar não encontrado');
        return;
    }
    
    // Remover itens de login/registro se existirem
    const loginLink = navbarNav.querySelector('a[href="login.html"]')?.parentElement;
    if (loginLink) loginLink.remove();
    
    // Se usuário está logado
    if (user && user.nome) {
        const nomeCompleto = user.apelido 
            ? `${user.nome} ${user.apelido}` 
            : user.nome;
        
        // Verificar se já existe o dropdown
        let userDropdown = navbarNav.querySelector('#userDropdown');
        
        if (!userDropdown) {
            // Criar dropdown do usuário
            const dropdownHTML = `
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <span id="nomeUsuario">${nomeCompleto}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="perfil.html">
                            <i class="bi bi-person"></i> Meu Perfil</a></li>
                        <li><a class="dropdown-item" href="minhas-marcacoes.html">
                            <i class="bi bi-calendar-check"></i> Minhas Marcações</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="logoutGlobal(); return false;">
                            <i class="bi bi-box-arrow-right"></i> Sair</a></li>
                    </ul>
                </li>
            `;
            
            // Inserir antes do botão "Marcar Consulta"
            const marcarBtn = navbarNav.querySelector('a[href="marcacao.html"]')?.parentElement;
            if (marcarBtn) {
                marcarBtn.insertAdjacentHTML('beforebegin', dropdownHTML);
            } else {
                navbarNav.insertAdjacentHTML('beforeend', dropdownHTML);
            }
            
            console.log('✅ Dropdown de usuário criado:', nomeCompleto);
        } else {
            // Atualizar nome se dropdown já existe
            const nomeSpan = document.getElementById('nomeUsuario');
            if (nomeSpan) {
                nomeSpan.textContent = nomeCompleto;
            }
            console.log('✅ Nome de usuário atualizado:', nomeCompleto);
        }
    } else {
        // Usuário não está logado - mostrar link de login
        const userDropdownItem = navbarNav.querySelector('#userDropdown')?.parentElement;
        if (userDropdownItem) {
            userDropdownItem.remove();
        }
        
        // Adicionar link de login se não existir
        if (!navbarNav.querySelector('a[href="login.html"]')) {
            const loginHTML = `
                <li class="nav-item">
                    <a class="nav-link" href="login.html">
                        <i class="bi bi-person-circle"></i> Login
                    </a>
                </li>
            `;
            
            const marcarBtn = navbarNav.querySelector('a[href="marcacao.html"]')?.parentElement;
            if (marcarBtn) {
                marcarBtn.insertAdjacentHTML('beforebegin', loginHTML);
            } else {
                navbarNav.insertAdjacentHTML('beforeend', loginHTML);
            }
        }
        
        console.log('ℹ️ Usuário não autenticado - mostrando link de login');
    }
}

/**
 * Logout global (chamado de qualquer página)
 */
function logoutGlobal() {
    if (confirm('Tem certeza que deseja sair?')) {
        console.log('🚪 Fazendo logout...');
        
        // Limpar dados de autenticação
        Auth.logout();
        
        // Mostrar mensagem
        alert('✅ Logout realizado com sucesso!');
        
        // Redirecionar para página inicial
        window.location.href = 'index.html';
    }
}

/**
 * Verificar se usuário está autenticado (para páginas protegidas)
 */
function verificarAutenticacaoGlobal(redirecionarSeNao = false) {
    const user = Auth.getUser();
    const token = Auth.getToken();
    
    const isAuth = !!(user && token);
    
    if (!isAuth && redirecionarSeNao) {
        alert('⚠️ Você precisa estar logado para acessar esta página.');
        window.location.href = 'login.html';
        return false;
    }
    
    return isAuth;
}

// Exportar funções para uso global
window.atualizarNavbarAuth = atualizarNavbarAuth;
window.logoutGlobal = logoutGlobal;
window.verificarAutenticacaoGlobal = verificarAutenticacaoGlobal;
