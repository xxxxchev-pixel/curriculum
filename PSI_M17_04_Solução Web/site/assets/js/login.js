// Login Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const lembrar = document.getElementById('lembrar').checked;
            
            // Validar email
            if (!validateEmail(email)) {
                mostrarErro('Email inválido!');
                return;
            }
            
            // Mostrar loading
            const btnSubmit = document.querySelector('#loginForm button[type="submit"]');
            const btnTextoOriginal = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Entrando...';
            
            // Fazer chamada à API
            fetch('/PSI_M17_04_Solução Web/api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, senha })
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    // Criar token de autenticação
                    const authToken = 'token_' + data.dados.id + '_' + Date.now();
                    
                    // Guardar dados do usuário usando Auth
                    Auth.setUser(data.dados);
                    Auth.setToken(authToken);
                    
                    console.log('✅ Login bem-sucedido:', data.dados);
                    console.log('✅ Token criado:', authToken);
                    
                    if (lembrar) {
                        localStorage.setItem('rememberMe', 'true');
                        localStorage.setItem('rememberedEmail', email);
                    }
                    
                    // Redirecionar
                    alert('✅ ' + data.mensagem);
                    window.location.href = 'perfil.html';
                } else {
                    // Erro retornado pela API
                    mostrarErro(data.erro);
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = btnTextoOriginal;
                }
            })
            .catch(error => {
                console.error('Erro no login:', error);
                mostrarErro('Erro ao conectar. Verifique sua conexão e tente novamente.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnTextoOriginal;
            });
        });
    }
});

// Mostrar erro de login
function mostrarErro(mensagem) {
    const divErro = document.getElementById('mensagemErro');
    divErro.textContent = mensagem;
    divErro.style.display = 'block';
    
    setTimeout(() => {
        divErro.style.display = 'none';
    }, 5000);
}

// Toggle visibilidade da senha
function togglePassword() {
    const senhaInput = document.getElementById('senha');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        senhaInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}

// Recuperar senha
function recuperarSenha() {
    const email = document.getElementById('emailRecuperar').value;
    
    if (!validateEmail(email)) {
        alert('Email inválido!');
        return;
    }
    
    // Simular envio de email (em produção, fazer chamada API)
    console.log('Recuperar senha para:', email);
    
    // Mostrar mensagem de sucesso
    document.getElementById('mensagemRecuperar').style.display = 'block';
    
    // EM PRODUÇÃO:
    /*
    API.post('/api/auth/recuperar-senha', { email })
        .then(response => {
            document.getElementById('mensagemRecuperar').style.display = 'block';
        })
        .catch(error => {
            alert('Erro ao enviar email. Tente novamente.');
        });
    */
}

// ============================================
// GOOGLE OAUTH2 LOGIN
// ============================================

// Configuração Google OAuth2
// IMPORTANTE: Substitua pelo seu Client ID real do Google Cloud Console
// Tutorial completo em: CONFIGURACAO_OAUTH_EMAILS.md
const GOOGLE_CLIENT_ID = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';
const GOOGLE_REDIRECT_URI = window.location.origin + '/PSI_M17_04_Solução Web/site/login.html';

// Verificar se Client ID está configurado
const GOOGLE_CONFIGURADO = GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';

// Inicializar Google Sign-In
function initGoogleSignIn() {
    // Só inicializar se o Client ID estiver configurado
    if (!GOOGLE_CONFIGURADO) {
        console.warn('Google Client ID não configurado. OAuth real não disponível.');
        return;
    }
    
    // Carregar biblioteca do Google
    if (typeof google !== 'undefined' && google.accounts) {
        google.accounts.id.initialize({
            client_id: GOOGLE_CLIENT_ID,
            callback: handleGoogleCallback,
            auto_select: false,
            cancel_on_tap_outside: true
        });
    }
}

// Login com Google
function loginGoogle() {
    // Verificar se Client ID está configurado
    if (!GOOGLE_CONFIGURADO) {
        mostrarAvisoConfiguracao('Google');
        return;
    }
    
    // Verificar se Google Sign-In está carregado
    if (typeof google !== 'undefined' && google.accounts) {
        // Usar Google One Tap
        google.accounts.id.prompt((notification) => {
            if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
                // Se One Tap não funcionar, usar popup
                mostrarGoogleSignInButton();
            }
        });
    } else {
        // Se SDK não estiver carregado, mostrar botão manual
        mostrarGoogleSignInButton();
    }
}

// Mostrar aviso de configuração necessária
function mostrarAvisoConfiguracao(provider) {
    const modalHtml = `
        <div class="modal fade" id="avisoConfigModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="bi bi-exclamation-triangle"></i> Configuração Necessária
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <strong>Login com ${provider} não configurado</strong>
                        </div>
                        
                        <p>Para usar o login com ${provider}, é necessário configurar as credenciais OAuth.</p>
                        
                        <h6 class="mt-4 mb-3">
                            <i class="bi bi-list-check"></i> Como configurar:
                        </h6>
                        <ol class="mb-3">
                            <li class="mb-2">Siga o guia de configuração: 
                                <br><code class="text-primary">CONFIGURACAO_OAUTH_EMAILS.md</code>
                            </li>
                            <li class="mb-2">${provider === 'Google' ? 'Crie credenciais no <strong>Google Cloud Console</strong>' : 'Crie uma aplicação no <strong>Facebook Developers</strong>'}
                            </li>
                            <li class="mb-2">Copie o ${provider === 'Google' ? '<strong>Client ID</strong>' : '<strong>App ID</strong>'}
                            </li>
                            <li class="mb-2">Cole no arquivo: 
                                <br><code class="text-primary">site/assets/js/login.js</code>
                                <br><small class="text-muted">(linha ${provider === 'Google' ? '11' : '280'})</small>
                            </li>
                        </ol>
                        
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-info-circle"></i>
                            <strong>Alternativa imediata:</strong> 
                            Use o login tradicional com email e senha logo abaixo.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Fechar
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="focusLoginForm()">
                            <i class="bi bi-box-arrow-in-right"></i> Usar Login Tradicional
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal anterior se existir
    const oldModal = document.getElementById('avisoConfigModal');
    if (oldModal) oldModal.remove();
    
    // Adicionar modal ao body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('avisoConfigModal'));
    modal.show();
}

// Focar no formulário de login tradicional
function focusLoginForm() {
    const emailInput = document.getElementById('email');
    if (emailInput) {
        // Aguardar um pouco para o modal fechar
        setTimeout(() => {
            emailInput.focus();
            emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Adicionar destaque visual temporário
            const loginCard = emailInput.closest('.card, .login-form, form')?.parentElement;
            if (loginCard) {
                loginCard.style.transition = 'all 0.3s ease';
                loginCard.style.boxShadow = '0 0 20px rgba(0, 123, 255, 0.5)';
                loginCard.style.transform = 'scale(1.02)';
                
                setTimeout(() => {
                    loginCard.style.boxShadow = '';
                    loginCard.style.transform = '';
                }, 2000);
            }
        }, 300);
    }
}

// Mostrar botão de login do Google
function mostrarGoogleSignInButton() {
    // Renderizar botão do Google
    const googleBtnContainer = document.createElement('div');
    googleBtnContainer.id = 'googleSignInBtn';
    
    if (typeof google !== 'undefined' && google.accounts) {
        google.accounts.id.renderButton(
            googleBtnContainer,
            { 
                theme: "outline", 
                size: "large",
                text: "continue_with",
                width: 300
            }
        );
        
        // Mostrar em modal
        const modalHtml = `
            <div class="modal fade" id="googleLoginModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Login com Google</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <p>Clique no botão abaixo para entrar:</p>
                            <div id="googleBtnContainer"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        document.getElementById('googleBtnContainer').appendChild(googleBtnContainer);
        new bootstrap.Modal(document.getElementById('googleLoginModal')).show();
    } else {
        alert('Por favor, aguarde o carregamento do Google Sign-In ou verifique sua conexão com a internet.');
    }
}

// ============================================
// FACEBOOK LOGIN
// ============================================

// OAuth2 Flow tradicional do Google
function loginGoogleOAuth2() {
    const authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    const params = new URLSearchParams({
        client_id: GOOGLE_CLIENT_ID,
        redirect_uri: GOOGLE_REDIRECT_URI,
        response_type: 'token',
        scope: 'email profile',
        state: generateRandomState()
    });
    
    // Guardar state para verificação
    sessionStorage.setItem('oauth_state', params.get('state'));
    
    // Redirecionar para Google
    window.location.href = `${authUrl}?${params.toString()}`;
}

// Callback do Google Sign-In
function handleGoogleCallback(response) {
    try {
        // Decodificar JWT token
        const userInfo = parseJwt(response.credential);
        
        // Criar objeto de usuário
        const userData = {
            id: userInfo.sub,
            nome: userInfo.name,
            email: userInfo.email,
            foto: userInfo.picture,
            tipo: 'paciente',
            provider: 'google'
        };
        
        // Guardar dados do usuário
        Auth.setUser(userData);
        localStorage.setItem('auth_token', response.credential);
        localStorage.setItem('auth_provider', 'google');
        
        // Criar/atualizar usuário no backend
        criarUsuarioOAuth(userData);
        
        // Redirecionar para perfil
        window.location.href = 'perfil.html';
        
    } catch (error) {
        console.error('Erro no login Google:', error);
        alert('Erro ao fazer login com Google. Tente novamente.');
    }
}

// ============================================
// FACEBOOK LOGIN
// ============================================

// Configuração Facebook
// IMPORTANTE: Substitua pelo seu App ID real do Facebook Developers
// Tutorial completo em: CONFIGURACAO_OAUTH_EMAILS.md
const FACEBOOK_APP_ID = 'YOUR_FACEBOOK_APP_ID';

// Verificar se App ID está configurado
const FACEBOOK_CONFIGURADO = FACEBOOK_APP_ID !== 'YOUR_FACEBOOK_APP_ID';

// Inicializar Facebook SDK
function initFacebookSDK() {
    // Só inicializar se o App ID estiver configurado
    if (!FACEBOOK_CONFIGURADO) {
        console.warn('Facebook App ID não configurado. OAuth real não disponível.');
        return;
    }
    
    window.fbAsyncInit = function() {
        FB.init({
            appId: FACEBOOK_APP_ID,
            cookie: true,
            xfbml: true,
            version: 'v18.0'
        });
        
        // Verificar se já está logado
        FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
                console.log('Usuário já conectado ao Facebook');
            }
        });
    };
    
    // Carregar SDK do Facebook
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "https://connect.facebook.net/pt_PT/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
}

// Login com Facebook
function loginFacebook() {
    // Verificar se App ID está configurado
    if (!FACEBOOK_CONFIGURADO) {
        mostrarAvisoConfiguracao('Facebook');
        return;
    }
    
    if (typeof FB === 'undefined') {
        alert('Aguarde o carregamento do Facebook SDK...');
        setTimeout(() => {
            if (typeof FB !== 'undefined') {
                iniciarLoginFacebook();
            } else {
                alert('Erro ao carregar Facebook SDK. Verifique sua conexão.');
            }
        }, 2000);
        return;
    }
    
    iniciarLoginFacebook();
}

function iniciarLoginFacebook() {
    FB.login(function(response) {
        if (response.status === 'connected') {
            // Login bem-sucedido
            handleFacebookCallback(response);
        } else {
            console.log('Utilizador cancelou o login ou não autorizou.');
        }
    }, {
        scope: 'public_profile,email',
        return_scopes: true
    });
}

// Callback do Facebook Login
function handleFacebookCallback(response) {
    const accessToken = response.authResponse.accessToken;
    
    // Obter dados do usuário
    FB.api('/me', {
        fields: 'id,name,email,picture.type(large)'
    }, function(userInfo) {
        try {
            // Criar objeto de usuário
            const userData = {
                id: userInfo.id,
                nome: userInfo.name,
                email: userInfo.email || '',
                foto: userInfo.picture.data.url,
                tipo: 'paciente',
                provider: 'facebook'
            };
            
            // Guardar dados do usuário
            Auth.setUser(userData);
            localStorage.setItem('auth_token', accessToken);
            localStorage.setItem('auth_provider', 'facebook');
            
            // Criar/atualizar usuário no backend
            criarUsuarioOAuth(userData);
            
            // Redirecionar para perfil
            window.location.href = 'perfil.html';
            
        } catch (error) {
            console.error('Erro no login Facebook:', error);
            alert('Erro ao fazer login com Facebook. Tente novamente.');
        }
    });
}

// ============================================
// FUNÇÕES AUXILIARES OAUTH
// ============================================

// Criar/Atualizar usuário OAuth no backend
function criarUsuarioOAuth(userData) {
    // EM PRODUÇÃO: Enviar para API
    /*
    API.post('/api/auth/oauth', userData)
        .then(response => {
            console.log('Usuário OAuth criado/atualizado:', response);
        })
        .catch(error => {
            console.error('Erro ao criar usuário OAuth:', error);
        });
    */
    
    // Simulação: Guardar no localStorage
    const usuarios = JSON.parse(localStorage.getItem('usuarios_oauth') || '[]');
    const index = usuarios.findIndex(u => u.email === userData.email);
    
    if (index >= 0) {
        usuarios[index] = { ...usuarios[index], ...userData };
    } else {
        usuarios.push(userData);
    }
    
    localStorage.setItem('usuarios_oauth', JSON.stringify(usuarios));
}

// Gerar estado aleatório para OAuth
function generateRandomState() {
    return Math.random().toString(36).substring(2, 15) + 
           Math.random().toString(36).substring(2, 15);
}

// Decodificar JWT token
function parseJwt(token) {
    try {
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
        return JSON.parse(jsonPayload);
    } catch (error) {
        console.error('Erro ao decodificar JWT:', error);
        return null;
    }
}

// Processar callback do OAuth2 (se houver hash na URL)
function processOAuthCallback() {
    const hash = window.location.hash.substring(1);
    if (!hash) return;
    
    const params = new URLSearchParams(hash);
    const accessToken = params.get('access_token');
    const state = params.get('state');
    
    if (accessToken && state) {
        // Verificar estado
        const savedState = sessionStorage.getItem('oauth_state');
        if (state !== savedState) {
            alert('Erro de segurança: Estado OAuth inválido');
            return;
        }
        
        // Obter informações do usuário do Google
        fetch('https://www.googleapis.com/oauth2/v2/userinfo', {
            headers: { Authorization: `Bearer ${accessToken}` }
        })
        .then(res => res.json())
        .then(userInfo => {
            const userData = {
                id: userInfo.id,
                nome: userInfo.name,
                email: userInfo.email,
                foto: userInfo.picture,
                tipo: 'paciente',
                provider: 'google'
            };
            
            Auth.setUser(userData);
            localStorage.setItem('auth_token', accessToken);
            localStorage.setItem('auth_provider', 'google');
            
            criarUsuarioOAuth(userData);
            
            // Limpar hash e redirecionar
            window.location.hash = '';
            window.location.href = 'perfil.html';
        })
        .catch(error => {
            console.error('Erro ao obter dados do usuário:', error);
            alert('Erro no login. Tente novamente.');
        });
    }
}

// Inicializar SDKs ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Google Sign-In
    const googleScript = document.createElement('script');
    googleScript.src = 'https://accounts.google.com/gsi/client';
    googleScript.async = true;
    googleScript.defer = true;
    googleScript.onload = initGoogleSignIn;
    document.head.appendChild(googleScript);
    
    // Inicializar Facebook SDK
    initFacebookSDK();
    
    // Processar callback OAuth2 se houver
    processOAuthCallback();
});

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

// Mostrar toast de notificação
function showToast(message, type = 'info') {
    // Criar toast dinamicamente
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Adicionar ao body
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    // Remover após fechar
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}
