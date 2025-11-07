// Contacto Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Obter dados do formulário
            const formData = {
                nome: document.getElementById('nome').value,
                email: document.getElementById('email').value,
                telefone: document.getElementById('telefone').value,
                assunto: document.getElementById('assunto').value,
                mensagem: document.getElementById('mensagem').value,
                timestamp: new Date().toISOString()
            };
            
            // Validar dados
            if (!validateEmail(formData.email)) {
                alert('Email inválido!');
                return;
            }
            
            if (!validatePhone(formData.telefone)) {
                alert('Telefone inválido!');
                return;
            }
            
            // Simular envio (em produção, fazer chamada API)
            console.log('Dados de contacto:', formData);
            
            // Mostrar mensagem de sucesso
            document.getElementById('mensagemSucesso').style.display = 'block';
            
            // Limpar formulário
            contactForm.reset();
            
            // Esconder mensagem após 5 segundos
            setTimeout(() => {
                document.getElementById('mensagemSucesso').style.display = 'none';
            }, 5000);
            
            // EM PRODUÇÃO: Enviar para API
            /*
            API.post('/api/contacto', formData)
                .then(response => {
                    document.getElementById('mensagemSucesso').style.display = 'block';
                    contactForm.reset();
                })
                .catch(error => {
                    console.error('Erro ao enviar mensagem:', error);
                    alert('Erro ao enviar mensagem. Tente novamente.');
                });
            */
        });
    }
});
