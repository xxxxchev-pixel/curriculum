// Registo Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const registoForm = document.getElementById('registoForm');
    
    if (registoForm) {
        registoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Obter dados do formulário
            const formData = {
                nome: document.getElementById('nome').value,
                apelido: document.getElementById('apelido').value,
                dataNascimento: document.getElementById('dataNascimento').value,
                genero: document.getElementById('genero').value,
                nif: document.getElementById('nif').value,
                email: document.getElementById('email').value,
                telefone: document.getElementById('telefone').value,
                telemovel: document.getElementById('telemovel').value,
                endereco: document.getElementById('endereco').value,
                codigoPostal: document.getElementById('codigoPostal').value,
                cidade: document.getElementById('cidade').value,
                senha: document.getElementById('senha').value,
                confirmarSenha: document.getElementById('confirmarSenha').value,
                seguro: document.getElementById('seguro').value,
                numeroSeguro: document.getElementById('numeroSeguro').value,
                newsletter: document.getElementById('newsletter').checked,
                termos: document.getElementById('termos').checked
            };
            
            // Validações
            if (!validarRegisto(formData)) {
                return;
            }
            
            // Mostrar loading
            const btnSubmit = document.querySelector('button[type="submit"]');
            const btnTextoOriginal = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Criando conta...';
            
            // Enviar para API
            fetch('/PSI_M17_04_Solução Web/api/registrar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    // Criar token de autenticação
                    const authToken = 'token_' + data.dados.id + '_' + Date.now();
                    
                    // Salvar dados do usuário usando Auth
                    Auth.setUser(data.dados);
                    Auth.setToken(authToken);
                    
                    console.log('✅ Usuário registrado:', data.dados);
                    console.log('✅ Token criado:', authToken);
                    
                    // Mensagem de sucesso
                    alert('✅ ' + data.mensagem + '\n\nRedirecionando para o seu perfil...');
                    
                    // Redirecionar para perfil
                    setTimeout(() => {
                        window.location.href = 'perfil.html';
                    }, 1500);
                } else {
                    // Erro retornado pela API
                    alert('❌ Erro: ' + data.erro);
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = btnTextoOriginal;
                }
            })
            .catch(error => {
                console.error('Erro no registo:', error);
                alert('❌ Erro ao criar conta. Verifique sua conexão e tente novamente.\n\nDetalhes: ' + error.message);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnTextoOriginal;
            });
        });
    }
});

// Validar dados de registo
function validarRegisto(data) {
    // Validar email
    if (!validateEmail(data.email)) {
        alert('Email inválido!');
        return false;
    }
    
    // Validar telefone
    if (!validatePhone(data.telefone)) {
        alert('Telefone inválido!');
        return false;
    }
    
    // Validar NIF (9 dígitos)
    if (!/^\d{9}$/.test(data.nif)) {
        alert('NIF inválido! Deve ter 9 dígitos.');
        return false;
    }
    
    // Validar idade (mínimo 18 anos)
    const hoje = new Date();
    const nascimento = new Date(data.dataNascimento);
    const idade = hoje.getFullYear() - nascimento.getFullYear();
    
    if (idade < 18) {
        alert('Deve ter pelo menos 18 anos para se registar.');
        return false;
    }
    
    // Validar senha
    if (data.senha.length < 8) {
        alert('A senha deve ter no mínimo 8 caracteres.');
        return false;
    }
    
    if (!/[A-Za-z]/.test(data.senha) || !/[0-9]/.test(data.senha)) {
        alert('A senha deve conter letras e números.');
        return false;
    }
    
    // Validar confirmação de senha
    if (data.senha !== data.confirmarSenha) {
        document.getElementById('senhaErro').style.display = 'block';
        setTimeout(() => {
            document.getElementById('senhaErro').style.display = 'none';
        }, 5000);
        return false;
    }
    
    // Validar termos
    if (!data.termos) {
        alert('Deve aceitar os termos e condições.');
        return false;
    }
    
    return true;
}

// Validação em tempo real da senha
document.addEventListener('DOMContentLoaded', function() {
    const senhaInput = document.getElementById('senha');
    const confirmarInput = document.getElementById('confirmarSenha');
    
    if (confirmarInput) {
        confirmarInput.addEventListener('blur', function() {
            if (senhaInput.value !== confirmarInput.value) {
                document.getElementById('senhaErro').style.display = 'block';
            } else {
                document.getElementById('senhaErro').style.display = 'none';
            }
        });
    }
});
