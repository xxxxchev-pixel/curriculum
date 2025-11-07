// Perfil Page Handler
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se está autenticado
    verificarAutenticacao();
    
    // Carregar dados do usuário
    carregarDadosUsuario();
    
    // Event Listeners
    const formDados = document.getElementById('formDadosPessoais');
    if (formDados) {
        formDados.addEventListener('submit', atualizarDados);
    }
    
    const formSenha = document.getElementById('formAlterarSenha');
    if (formSenha) {
        formSenha.addEventListener('submit', alterarSenha);
    }
});

// Verificar autenticação
function verificarAutenticacao() {
    const token = localStorage.getItem('auth_token');
    const user = Auth.getUser();
    
    if (!token || !user) {
        alert('Sessão expirada. Por favor, faça login novamente.');
        window.location.href = 'login.html';
        return false;
    }
    
    return true;
}

// Carregar dados do usuário
function carregarDadosUsuario() {
    const user = Auth.getUser();
    
    if (user) {
        // Nome completo do usuário real
        const nomeCompleto = user.nome && user.apelido 
            ? `${user.nome} ${user.apelido}` 
            : user.nome || 'Usuário';
        
        // Atualizar nome no dropdown do navbar
        const nomeElem = document.getElementById('nomeUsuario');
        if (nomeElem) {
            nomeElem.textContent = nomeCompleto;
        }
        
        // Atualizar perfil sidebar
        const perfilNome = document.getElementById('perfilNome');
        const perfilEmail = document.getElementById('perfilEmail');
        const perfilAvatar = document.getElementById('perfilAvatar');
        
        if (perfilNome) perfilNome.textContent = nomeCompleto;
        if (perfilEmail) perfilEmail.textContent = user.email || '';
        
        // Atualizar avatar com nome do usuário
        if (perfilAvatar && user.nome) {
            const avatarName = user.apelido 
                ? `${user.nome}+${user.apelido}` 
                : user.nome;
            perfilAvatar.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(avatarName)}&size=150&background=0d6efd&color=fff`;
        }
        
        // Preencher formulário de dados pessoais com dados reais
        preencherFormularioDados(user);
        
        // Mostrar informações adicionais no dashboard
        atualizarDashboard(user);
    }
}

// Preencher formulário com dados do usuário
function preencherFormularioDados(user) {
    // Preencher campos se existirem
    const campos = {
        'nome': user.nome,
        'apelido': user.apelido,
        'email': user.email,
        'telefone': user.telefone,
        'telemovel': user.telemovel,
        'nif': user.nif,
        'dataNascimento': user.data_nascimento,
        'genero': user.genero,
        'endereco': user.endereco,
        'codigoPostal': user.codigo_postal,
        'cidade': user.cidade,
        'seguro': user.seguro,
        'numeroSeguro': user.numero_seguro
    };
    
    for (const [campo, valor] of Object.entries(campos)) {
        const elemento = document.getElementById(campo);
        if (elemento && valor) {
            elemento.value = valor;
        }
    }
    
    // Checkbox de newsletter
    const newsletterElem = document.getElementById('newsletter');
    if (newsletterElem) {
        newsletterElem.checked = user.newsletter || false;
    }
}

// Atualizar dashboard com informações do usuário
function atualizarDashboard(user) {
    // Atualizar mensagem de boas-vindas
    const bemVindoElem = document.querySelector('.card-body h4');
    if (bemVindoElem && bemVindoElem.textContent.includes('Bem-vindo')) {
        const primeiroNome = user.nome || 'Usuário';
        bemVindoElem.textContent = `Bem-vindo de volta, ${primeiroNome}!`;
    }
    
    // Mostrar dados da conta
    const infoContaElem = document.getElementById('infoConta');
    if (infoContaElem) {
        infoContaElem.innerHTML = `
            <div class="alert alert-info">
                <strong><i class="bi bi-person-check"></i> Conta Verificada</strong><br>
                <small>Email: ${user.email}</small><br>
                <small>NIF: ${user.nif || 'Não informado'}</small><br>
                <small>Telefone: ${user.telefone || user.telemovel || 'Não informado'}</small>
            </div>
        `;
    }
}

// Mostrar seção específica
function mostrarSecao(secao) {
    // Esconder todas as seções
    const secoes = document.querySelectorAll('.secao-perfil');
    secoes.forEach(s => s.style.display = 'none');
    
    // Mostrar seção selecionada
    const secaoElem = document.getElementById('secao' + capitalize(secao));
    if (secaoElem) {
        secaoElem.style.display = 'block';
    }
    
    // Atualizar menu ativo
    const menuItems = document.querySelectorAll('.list-group-item');
    menuItems.forEach(item => item.classList.remove('active'));
    event.target.classList.add('active');
}

// Capitalizar primeira letra
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Atualizar dados pessoais
function atualizarDados(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const dadosAtualizados = Object.fromEntries(formData);
    
    // Adicionar ID do usuário
    const user = Auth.getUser();
    if (!user || !user.id) {
        alert('❌ Erro: Usuário não identificado. Faça login novamente.');
        return;
    }
    
    dadosAtualizados.id = user.id;
    
    // Mostrar loading
    const btnSubmit = e.target.querySelector('button[type="submit"]');
    const btnTextoOriginal = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';
    
    // Enviar para API (a ser implementado)
    console.log('Dados a atualizar:', dadosAtualizados);
    
    // Por enquanto, atualizar no localStorage
    const dadosAtualizadosCompletos = {
        ...user,
        ...dadosAtualizados
    };
    
    Auth.setUser(dadosAtualizadosCompletos);
    
    alert('✅ Dados atualizados com sucesso!');
    btnSubmit.disabled = false;
    btnSubmit.innerHTML = btnTextoOriginal;
    
    // Recarregar dados na tela
    carregarDadosUsuario();
    
    // TODO: Enviar para API
    /*
    fetch('/PSI_M17_04_Solução Web/api/atualizar-usuario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(dadosAtualizados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            // Atualizar dados no localStorage
            Auth.setUser(data.dados);
            alert('✅ Dados atualizados com sucesso!');
            carregarDadosUsuario();
        } else {
            alert('❌ Erro: ' + data.erro);
        }
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = btnTextoOriginal;
    })
    .catch(error => {
        alert('❌ Erro ao atualizar dados: ' + error.message);
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = btnTextoOriginal;
    });
    */
}

// Alterar senha
function alterarSenha(e) {
    e.preventDefault();
    
    const form = e.target;
    const senhaAtual = form.elements[0].value;
    const novaSenha = form.elements[1].value;
    const confirmarSenha = form.elements[2].value;
    
    // Validar
    if (novaSenha !== confirmarSenha) {
        alert('As senhas não coincidem!');
        return;
    }
    
    if (novaSenha.length < 8) {
        alert('A senha deve ter no mínimo 8 caracteres!');
        return;
    }
    
    // Simular alteração
    alert('Senha alterada com sucesso!');
    form.reset();
    
    // EM PRODUÇÃO: Enviar para API
    /*
    API.post('/api/auth/alterar-senha', {
        senhaAtual,
        novaSenha
    })
        .then(response => {
            alert('Senha alterada com sucesso!');
            form.reset();
        })
        .catch(error => {
            alert('Erro ao alterar senha.');
        });
    */
}

// Cancelar consulta
function cancelarConsulta(id) {
    if (confirm('Tem certeza que deseja cancelar esta consulta?')) {
        alert('Consulta cancelada com sucesso!');
        
        // EM PRODUÇÃO: Enviar para API
        /*
        API.delete(`/api/consultas/${id}`)
            .then(response => {
                alert('Consulta cancelada com sucesso!');
                // Recarregar lista de consultas
                location.reload();
            })
            .catch(error => {
                alert('Erro ao cancelar consulta.');
            });
        */
    }
}

// Logout
function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
        // Limpar todos os dados de autenticação
        Auth.logout();
        
        // Redirecionar para página inicial
        window.location.href = 'index.html';
    }
}
