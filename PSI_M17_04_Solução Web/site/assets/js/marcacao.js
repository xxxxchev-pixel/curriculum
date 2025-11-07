// =====================================================
// SISTEMA DE MARCAÇÃO DE CONSULTAS
// =====================================================

let currentStep = 1;
const totalSteps = 5;

const marcacaoData = {
    medicoId: null,
    medicoNome: null,
    servicoId: null,
    servicoNome: null,
    data: null,
    horario: null,
    dados: {}
};

// ===== VERIFICAR AUTENTICAÇÃO =====
function verificarAutenticacao() {
    const user = Auth.getUser();
    const token = Auth.getToken();
    
    if (!user || !token) {
        alert('⚠️ Você precisa estar logado para marcar uma consulta.\n\nRedirecionando para a página de login...');
        setTimeout(() => {
            window.location.href = 'login.html?redirect=marcacao.html';
        }, 1500);
        return false;
    }
    
    console.log('✅ Usuário autenticado:', user.nome);
    return true;
}

// ===== PRÉ-PREENCHER DADOS DO USUÁRIO =====
function preencherDadosUsuario() {
    const user = Auth.getUser();
    
    if (!user) return;
    
    // Aguardar até que os campos estejam disponíveis
    setTimeout(() => {
        // Usar IDs específicos dos campos
        const nomeInput = document.getElementById('input-nome');
        const emailInput = document.getElementById('input-email');
        const telefoneInput = document.getElementById('input-telefone');
        const nifInput = document.getElementById('input-nif');
        
        if (nomeInput && user.nome) {
            const nomeCompleto = user.apelido ? `${user.nome} ${user.apelido}` : user.nome;
            nomeInput.value = nomeCompleto;
            nomeInput.setAttribute('readonly', 'true');
            nomeInput.classList.add('bg-light');
        }
        
        if (emailInput && user.email) {
            emailInput.value = user.email;
            emailInput.setAttribute('readonly', 'true');
            emailInput.classList.add('bg-light');
        }
        
        if (telefoneInput && (user.telefone || user.telemovel)) {
            telefoneInput.value = user.telefone || user.telemovel;
        }
        
        if (nifInput && user.nif) {
            nifInput.value = user.nif;
            nifInput.setAttribute('readonly', 'true');
            nifInput.classList.add('bg-light');
        }
        
        console.log('✅ Dados do usuário pré-preenchidos:', {
            nome: nomeInput?.value,
            email: emailInput?.value,
            telefone: telefoneInput?.value,
            nif: nifInput?.value
        });
    }, 500);
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    // Verificar autenticação
    verificarAutenticacao();
    
    // Pré-preencher dados do usuário
    preencherDadosUsuario();
    
    inicializarCalendario();
    atualizarNavegacao();
});

// ===== NAVEGAÇÃO ENTRE STEPS =====
function proximoStep() {
    if (validarStep(currentStep)) {
        currentStep++;
        mostrarStep(currentStep);
        atualizarNavegacao();
    }
}

function voltarStep() {
    if (currentStep > 1) {
        currentStep--;
        mostrarStep(currentStep);
        atualizarNavegacao();
    }
}

function mostrarStep(step) {
    // Esconder todos os steps
    document.querySelectorAll('.step-content').forEach(el => {
        el.classList.add('d-none');
    });
    
    // Mostrar step atual
    document.getElementById(`step-${step}`).classList.remove('d-none');
    
    // Atualizar indicadores
    document.querySelectorAll('.step').forEach((el, index) => {
        el.classList.remove('active', 'completed');
        if (index + 1 < step) {
            el.classList.add('completed');
        } else if (index + 1 === step) {
            el.classList.add('active');
        }
    });
    
    // Scroll para topo
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function atualizarNavegacao() {
    const btnVoltar = document.getElementById('btn-voltar');
    const btnProximo = document.getElementById('btn-proximo');
    
    btnVoltar.disabled = currentStep === 1;
    
    if (currentStep === totalSteps) {
        btnProximo.style.display = 'none';
    } else {
        btnProximo.style.display = 'block';
    }
}

// ===== VALIDAÇÃO =====
function validarStep(step) {
    switch(step) {
        case 1:
            if (!marcacaoData.medicoId) {
                alert('Por favor, selecione um médico');
                return false;
            }
            return true;
        case 2:
            if (!marcacaoData.servicoId) {
                alert('Por favor, selecione um serviço');
                return false;
            }
            return true;
        case 3:
            if (!marcacaoData.data || !marcacaoData.horario) {
                alert('Por favor, selecione data e horário');
                return false;
            }
            return true;
        case 4:
            const form = document.getElementById('form-dados');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            coletarDados();
            atualizarResumo();
            return true;
        default:
            return true;
    }
}

// ===== SELEÇÕES =====
function selecionarMedico(id) {
    // Remover seleção anterior
    document.querySelectorAll('.medico-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Adicionar seleção atual
    const card = document.querySelector(`[data-medico-id="${id}"]`);
    card.classList.add('selected');
    
    // Salvar dados
    marcacaoData.medicoId = id;
    marcacaoData.medicoNome = card.querySelector('h5').textContent;
    
    // Avançar automaticamente após 500ms
    setTimeout(() => {
        proximoStep();
    }, 500);
}

function selecionarServico(id) {
    // Remover seleção anterior
    document.querySelectorAll('#servicos-container .card').forEach(card => {
        card.classList.remove('border-primary', 'bg-light');
    });
    
    // Adicionar seleção atual
    const card = event.currentTarget;
    card.classList.add('border-primary', 'bg-light');
    
    // Salvar dados
    marcacaoData.servicoId = id;
    marcacaoData.servicoNome = card.querySelector('h5').textContent.replace(/.*?\s/, '');
    
    // Avançar automaticamente
    setTimeout(() => {
        proximoStep();
    }, 500);
}

function selecionarHorario(horario) {
    // Remover seleção anterior
    document.querySelectorAll('.btn-horario').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    // Adicionar seleção atual
    event.target.classList.add('selected');
    
    // Salvar horário
    marcacaoData.horario = horario;
}

// ===== CALENDÁRIO =====
function inicializarCalendario() {
    flatpickr("#calendario", {
        locale: "pt",
        minDate: "today",
        maxDate: new Date().fp_incr(90),
        disable: [
            function(date) {
                // Desabilitar domingos
                return (date.getDay() === 0);
            }
        ],
        onChange: function(selectedDates, dateStr) {
            marcacaoData.data = dateStr;
            carregarHorariosDisponiveis(dateStr);
        }
    });
}

async function carregarHorariosDisponiveis(data) {
    const container = document.getElementById('horarios-container');
    container.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary"></div></div>';
    
    // Simulação - em produção, fazer requisição à API
    setTimeout(() => {
        const horarios = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', 
                          '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'];
        
        container.innerHTML = horarios.map(h => `
            <div class="col-4 mb-2">
                <button class="btn-horario w-100" onclick="selecionarHorario('${h}')">${h}</button>
            </div>
        `).join('');
    }, 500);
}

// ===== DADOS DO FORMULÁRIO =====
function coletarDados() {
    const user = Auth.getUser();
    
    marcacaoData.dados = {
        usuario_id: user?.id,
        nome: document.getElementById('input-nome')?.value || '',
        email: document.getElementById('input-email')?.value || '',
        telefone: document.getElementById('input-telefone')?.value || '',
        nif: document.getElementById('input-nif')?.value || '',
        observacoes: document.getElementById('input-observacoes')?.value || ''
    };
    
    console.log('✅ Dados coletados:', marcacaoData.dados);
}

// ===== RESUMO =====
function atualizarResumo() {
    document.getElementById('resumo-medico').textContent = marcacaoData.medicoNome || '-';
    document.getElementById('resumo-servico').textContent = marcacaoData.servicoNome || '-';
    document.getElementById('resumo-data').textContent = marcacaoData.data || '-';
    document.getElementById('resumo-hora').textContent = marcacaoData.horario || '-';
}

// ===== CONFIRMAÇÃO =====
async function confirmarMarcacao() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';
    
    try {
        // Obter dados do usuário logado
        const user = Auth.getUser();
        
        // Preparar dados completos da marcação
        const dadosMarcacao = {
            paciente_id: user?.id || 'temp_' + Date.now(),
            paciente_nome: marcacaoData.dados.nome,
            paciente_email: marcacaoData.dados.email,
            paciente_telefone: marcacaoData.dados.telefone,
            medico_id: marcacaoData.medicoId,
            medico_nome: marcacaoData.medicoNome,
            servico_id: marcacaoData.servicoId,
            servico_nome: marcacaoData.servicoNome,
            data: marcacaoData.data,
            hora: marcacaoData.horario,
            observacoes: marcacaoData.dados.observacoes || '',
            status: 'pendente',
            criado_em: new Date().toISOString()
        };
        
        // Guardar marcação no localStorage (simulação do backend)
        const marcacoes = JSON.parse(localStorage.getItem('marcacoes') || '[]');
        dadosMarcacao.id = marcacoes.length + 1;
        marcacoes.push(dadosMarcacao);
        localStorage.setItem('marcacoes', JSON.stringify(marcacoes));
        
        // EM PRODUÇÃO: Enviar para API
        /*
        const response = await fetch('/api/consultas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dadosMarcacao)
        });
        
        if (!response.ok) throw new Error('Erro ao criar consulta');
        const consulta = await response.json();
        */
        
        // Enviar email de confirmação
        await enviarEmailConfirmacao(dadosMarcacao);
        
        // Aguardar um pouco para simular processamento
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Mostrar sucesso
        mostrarSucesso();
        
    } catch (error) {
        console.error('Erro ao marcar consulta:', error);
        alert('Erro ao marcar consulta. Por favor, tente novamente.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Confirmar Marcação';
    }
}

// Enviar email de confirmação
async function enviarEmailConfirmacao(marcacao) {
    try {
        // Dados para o email de confirmação
        const emailData = {
            email: marcacao.paciente_email,
            nome: marcacao.paciente_nome,
            medico_nome: marcacao.medico_nome,
            servico_nome: marcacao.servico_nome,
            data: marcacao.data,
            hora: marcacao.hora,
            observacoes: marcacao.observacoes || 'Nenhuma observação adicionada'
        };
        
        console.log('📧 Enviando email de confirmação...', emailData);
        
        // Enviar para API PHP de confirmação
        const response = await fetch('/PSI_M17_04_Solução Web/api/enviar-confirmacao-marcacao.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(emailData)
        });
        
        const resultado = await response.json();
        
        if (resultado.sucesso) {
            console.log('✅ Email de confirmação enviado com sucesso!');
        } else {
            console.warn('⚠️ Falha ao enviar email:', resultado.mensagem);
            console.log('Marcação foi criada, mas email não foi enviado');
        }
        
    } catch (error) {
        // Não bloquear a marcação se o email falhar
        console.warn('❌ Erro ao enviar email:', error);
        console.log('Marcação criada com sucesso, mas email de confirmação falhou');
    }
}

// Formatar data por extenso para email
function formatarDataPorExtenso(dataStr) {
    const [ano, mes, dia] = dataStr.split('-');
    const data = new Date(ano, mes - 1, dia);
    
    const diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
    const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    
    return `${diasSemana[data.getDay()]}, ${dia} de ${meses[data.getMonth()]} de ${ano}`;
}

function mostrarSucesso() {
    const container = document.querySelector('.container');
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
            </div>
            <h2 class="mb-3">Consulta Marcada com Sucesso!</h2>
            <p class="lead mb-4">
                Enviámos um email de confirmação para ${marcacaoData.dados.email}
            </p>
            <div class="card mx-auto" style="max-width: 500px;">
                <div class="card-body">
                    <h5>Detalhes da Consulta</h5>
                    <hr>
                    <p><strong>Médico:</strong> ${marcacaoData.medicoNome}</p>
                    <p><strong>Serviço:</strong> ${marcacaoData.servicoNome}</p>
                    <p><strong>Data:</strong> ${marcacaoData.data}</p>
                    <p><strong>Hora:</strong> ${marcacaoData.horario}</p>
                    <hr>
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle"></i> 
                        Receberá um lembrete 24h antes da consulta.
                    </p>
                </div>
            </div>
            <div class="mt-4">
                <a href="index.html" class="btn btn-primary">Voltar ao Início</a>
                <a href="login.html" class="btn btn-outline-primary">Aceder à Minha Área</a>
            </div>
        </div>
    `;
}
