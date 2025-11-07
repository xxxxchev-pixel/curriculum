// ============================================
// GESTÃO DE MARCAÇÕES DO UTILIZADOR
// ============================================

let currentFilter = 'todas';
let currentCancelId = null;

// Dados dos médicos (sincronizado com medicos.html)
const medicos = {
    'miguel-santos': {
        nome: 'Dr. Miguel Santos',
        especialidade: 'Dermatologia Clínica',
        foto: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400&fit=crop&q=80',
        crm: 'CRM 12345'
    },
    'ana-rodrigues': {
        nome: 'Dra. Ana Rodrigues',
        especialidade: 'Dermatologia Estética',
        foto: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&fit=crop&q=80',
        crm: 'CRM 23456'
    },
    'carlos-mendes': {
        nome: 'Dr. Carlos Mendes',
        especialidade: 'Tricologia',
        foto: 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400&fit=crop&q=80',
        crm: 'CRM 34567'
    }
};

// Dados dos serviços
const servicos = {
    'acne': 'Tratamento de Acne',
    'eczema': 'Eczema e Dermatites',
    'psoriase': 'Tratamento de Psoríase',
    'queda-cabelo': 'Tratamento de Queda de Cabelo',
    'botox': 'Botox',
    'preenchimento': 'Preenchimento Facial',
    'peeling': 'Peeling Químico',
    'microagulhamento': 'Microagulhamento',
    'rejuvenescimento': 'Rejuvenescimento a Laser',
    'depilacao': 'Depilação a Laser',
    'mapeamento': 'Mapeamento de Pintas',
    'checkup': 'Checkup Dermatológico',
    'criolipólise': 'Criolipólise',
    'celulite': 'Tratamento de Celulite',
    'consulta-geral': 'Consulta Geral',
    'avaliacao': 'Avaliação Dermatológica'
};

// ============================================
// CARREGAR MARCAÇÕES
// ============================================

function carregarMarcacoes() {
    // Verificar se o usuário está autenticado
    const user = Auth.getUser();
    if (!user) {
        window.location.href = 'login.html';
        return;
    }

    // EM PRODUÇÃO: Buscar do backend
    /*
    API.get(`/api/consultas/usuario/${user.id}`)
        .then(response => {
            renderizarMarcacoes(response.data);
        })
        .catch(error => {
            console.error('Erro ao carregar marcações:', error);
            showToast('Erro ao carregar suas marcações', 'danger');
        });
    */

    // Simulação: Buscar do localStorage ou criar marcações de exemplo
    let marcacoes = JSON.parse(localStorage.getItem('marcacoes') || '[]');
    
    // Se não houver marcações, criar alguns exemplos para demonstração
    if (marcacoes.length === 0) {
        marcacoes = criarMarcacoesExemplo(user.id);
        localStorage.setItem('marcacoes', JSON.stringify(marcacoes));
    }
    
    // Filtrar marcações do usuário atual
    const minhasMarcacoes = marcacoes.filter(m => m.paciente_id === user.id || m.paciente_email === user.email);
    
    renderizarMarcacoes(minhasMarcacoes);
}

// Criar marcações de exemplo para demonstração
function criarMarcacoesExemplo(userId) {
    const hoje = new Date();
    
    return [
        {
            id: 1,
            paciente_id: userId,
            paciente_email: Auth.getUser()?.email,
            paciente_nome: Auth.getUser()?.nome,
            medico_id: 'miguel-santos',
            servico_id: 'checkup',
            data: formatarData(addDays(hoje, 3)),
            hora: '10:00',
            status: 'confirmada',
            observacoes: 'Consulta de rotina para checkup dermatológico',
            criado_em: new Date().toISOString()
        },
        {
            id: 2,
            paciente_id: userId,
            paciente_email: Auth.getUser()?.email,
            paciente_nome: Auth.getUser()?.nome,
            medico_id: 'ana-rodrigues',
            servico_id: 'botox',
            data: formatarData(addDays(hoje, 10)),
            hora: '14:30',
            status: 'pendente',
            observacoes: 'Primeira sessão de botox - área da testa',
            criado_em: new Date().toISOString()
        },
        {
            id: 3,
            paciente_id: userId,
            paciente_email: Auth.getUser()?.email,
            paciente_nome: Auth.getUser()?.nome,
            medico_id: 'carlos-mendes',
            servico_id: 'queda-cabelo',
            data: formatarData(addDays(hoje, -7)),
            hora: '09:00',
            status: 'concluida',
            observacoes: 'Avaliação de queda de cabelo - retorno em 30 dias',
            criado_em: new Date().toISOString()
        },
        {
            id: 4,
            paciente_id: userId,
            paciente_email: Auth.getUser()?.email,
            paciente_nome: Auth.getUser()?.nome,
            medico_id: 'miguel-santos',
            servico_id: 'acne',
            data: formatarData(addDays(hoje, -30)),
            hora: '15:00',
            status: 'concluida',
            observacoes: 'Tratamento de acne - aplicação de medicação tópica',
            criado_em: new Date().toISOString()
        },
        {
            id: 5,
            paciente_id: userId,
            paciente_email: Auth.getUser()?.email,
            paciente_nome: Auth.getUser()?.nome,
            medico_id: 'ana-rodrigues',
            servico_id: 'peeling',
            data: formatarData(addDays(hoje, 15)),
            hora: '11:00',
            status: 'confirmada',
            observacoes: 'Peeling químico superficial',
            criado_em: new Date().toISOString()
        }
    ];
}

// ============================================
// RENDERIZAR MARCAÇÕES
// ============================================

function renderizarMarcacoes(marcacoes) {
    const container = document.getElementById('appointmentsList');
    const emptyState = document.getElementById('emptyState');
    
    // Filtrar marcações baseado no filtro ativo
    const marcacoesFiltradas = filtrarMarcacoes(marcacoes, currentFilter);
    
    // Ordenar por data (mais recentes primeiro)
    marcacoesFiltradas.sort((a, b) => {
        const dateA = new Date(a.data + ' ' + a.hora);
        const dateB = new Date(b.data + ' ' + b.hora);
        return dateB - dateA;
    });
    
    if (marcacoesFiltradas.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('d-none');
        return;
    }
    
    emptyState.classList.add('d-none');
    
    let html = '';
    
    marcacoesFiltradas.forEach(marcacao => {
        const medico = medicos[marcacao.medico_id] || {
            nome: 'Médico não identificado',
            especialidade: '',
            foto: 'https://via.placeholder.com/60',
            crm: ''
        };
        
        const servico = servicos[marcacao.servico_id] || 'Serviço não identificado';
        const dataFormatada = formatarDataExtenso(marcacao.data);
        const isPast = isDataPassada(marcacao.data, marcacao.hora);
        
        html += `
            <div class="col-md-6 mb-4" data-appointment-id="${marcacao.id}">
                <div class="card appointment-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">
                                    <i class="bi bi-bandaid text-primary"></i>
                                    ${servico}
                                </h5>
                                <span class="appointment-status status-${marcacao.status}">
                                    ${getStatusText(marcacao.status)}
                                </span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="verDetalhes(${marcacao.id}, event)">
                                            <i class="bi bi-eye"></i> Ver Detalhes
                                        </a>
                                    </li>
                                    ${!isPast && marcacao.status !== 'cancelada' ? `
                                        <li>
                                            <a class="dropdown-item" href="marcacao.html?editar=${marcacao.id}">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="cancelarMarcacao(${marcacao.id}, event)">
                                                <i class="bi bi-x-circle"></i> Cancelar
                                            </a>
                                        </li>
                                    ` : ''}
                                </ul>
                            </div>
                        </div>
                        
                        <div class="appointment-date">
                            <i class="bi bi-calendar-event"></i>
                            ${dataFormatada}
                        </div>
                        <div class="appointment-time">
                            <i class="bi bi-clock"></i>
                            ${marcacao.hora}
                        </div>
                        
                        <div class="doctor-info">
                            <img src="${medico.foto}" alt="${medico.nome}" class="doctor-photo">
                            <div>
                                <strong>${medico.nome}</strong><br>
                                <small class="text-muted">${medico.especialidade}</small>
                            </div>
                        </div>
                        
                        ${marcacao.observacoes ? `
                            <div class="mt-3 pt-3 border-top">
                                <small class="text-muted">
                                    <i class="bi bi-chat-left-text"></i>
                                    ${marcacao.observacoes}
                                </small>
                            </div>
                        ` : ''}
                        
                        <div class="action-buttons mt-3">
                            ${!isPast && marcacao.status === 'confirmada' ? `
                                <button class="btn btn-sm btn-outline-primary flex-fill" onclick="adicionarCalendario(${marcacao.id})">
                                    <i class="bi bi-calendar-plus"></i> Adicionar ao Calendário
                                </button>
                            ` : ''}
                            ${isPast && marcacao.status === 'concluida' ? `
                                <button class="btn btn-sm btn-outline-success flex-fill" onclick="verDetalhes(${marcacao.id}, event)">
                                    <i class="bi bi-file-text"></i> Ver Relatório
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ============================================
// FILTRAR MARCAÇÕES
// ============================================

function filtrarMarcacoes(marcacoes, filtro) {
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);
    
    switch(filtro) {
        case 'proximas':
            return marcacoes.filter(m => {
                const dataMarcacao = new Date(m.data);
                return dataMarcacao >= hoje && m.status !== 'cancelada' && m.status !== 'concluida';
            });
            
        case 'pendentes':
            return marcacoes.filter(m => m.status === 'pendente');
            
        case 'confirmadas':
            return marcacoes.filter(m => m.status === 'confirmada');
            
        case 'historico':
            return marcacoes.filter(m => {
                const dataMarcacao = new Date(m.data);
                return dataMarcacao < hoje || m.status === 'concluida' || m.status === 'cancelada';
            });
            
        case 'todas':
        default:
            return marcacoes;
    }
}

function filterAppointments(filtro, event) {
    if (event) {
        event.preventDefault();
    }
    
    // Atualizar tab ativo
    document.querySelectorAll('.filter-tabs .nav-link').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.closest('.nav-link').classList.add('active');
    
    // Atualizar filtro e recarregar
    currentFilter = filtro;
    carregarMarcacoes();
}

// ============================================
// DETALHES DA MARCAÇÃO
// ============================================

function verDetalhes(marcacaoId, event) {
    if (event) {
        event.preventDefault();
    }
    
    const marcacoes = JSON.parse(localStorage.getItem('marcacoes') || '[]');
    const marcacao = marcacoes.find(m => m.id === marcacaoId);
    
    if (!marcacao) {
        alert('Marcação não encontrada!');
        return;
    }
    
    const medico = medicos[marcacao.medico_id] || {};
    const servico = servicos[marcacao.servico_id] || 'Serviço não identificado';
    const dataFormatada = formatarDataExtenso(marcacao.data);
    
    const modalContent = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <strong><i class="bi bi-bandaid text-primary"></i> Serviço:</strong><br>
                ${servico}
            </div>
            <div class="col-md-6 mb-3">
                <strong><i class="bi bi-calendar-event text-primary"></i> Data e Hora:</strong><br>
                ${dataFormatada} às ${marcacao.hora}
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-12">
                <strong><i class="bi bi-person-badge text-primary"></i> Médico Responsável:</strong><br>
                <div class="d-flex align-items-center gap-3 mt-2">
                    <img src="${medico.foto}" alt="${medico.nome}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <h6 class="mb-0">${medico.nome}</h6>
                        <small class="text-muted">${medico.especialidade}</small><br>
                        <small class="text-muted">${medico.crm}</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <strong><i class="bi bi-info-circle text-primary"></i> Status:</strong><br>
                <span class="appointment-status status-${marcacao.status} mt-2 d-inline-block">
                    ${getStatusText(marcacao.status)}
                </span>
            </div>
            <div class="col-md-6">
                <strong><i class="bi bi-clock-history text-primary"></i> Marcado em:</strong><br>
                ${new Date(marcacao.criado_em).toLocaleDateString('pt-PT')}
            </div>
        </div>
        
        ${marcacao.observacoes ? `
            <div class="row mb-3">
                <div class="col-12">
                    <strong><i class="bi bi-chat-left-text text-primary"></i> Observações:</strong><br>
                    <p class="mb-0 mt-2">${marcacao.observacoes}</p>
                </div>
            </div>
        ` : ''}
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Informações Importantes:</strong>
            <ul class="mb-0 mt-2">
                <li>Chegue com 15 minutos de antecedência</li>
                <li>Traga documentos de identificação e cartão de saúde</li>
                <li>Para cancelamentos, contacte-nos com 24h de antecedência</li>
            </ul>
        </div>
    `;
    
    document.getElementById('modalDetailsContent').innerHTML = modalContent;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

// ============================================
// CANCELAR MARCAÇÃO
// ============================================

function cancelarMarcacao(marcacaoId, event) {
    if (event) {
        event.preventDefault();
    }
    
    currentCancelId = marcacaoId;
    document.getElementById('cancelReason').value = '';
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

async function confirmCancellation() {
    if (!currentCancelId) return;
    
    const motivo = document.getElementById('cancelReason').value;
    const marcacoes = JSON.parse(localStorage.getItem('marcacoes') || '[]');
    const marcacao = marcacoes.find(m => m.id === currentCancelId);
    
    if (!marcacao) {
        alert('Marcação não encontrada!');
        return;
    }
    
    // EM PRODUÇÃO: Enviar para API
    /*
    API.put(`/api/consultas/${currentCancelId}/cancelar`, { motivo })
        .then(response => {
            showToast('Consulta cancelada com sucesso', 'success');
            carregarMarcacoes();
        })
        .catch(error => {
            console.error('Erro ao cancelar:', error);
            showToast('Erro ao cancelar consulta', 'danger');
        });
    */
    
    // Atualizar no localStorage
    const index = marcacoes.findIndex(m => m.id === currentCancelId);
    
    if (index !== -1) {
        marcacoes[index].status = 'cancelada';
        marcacoes[index].motivo_cancelamento = motivo;
        marcacoes[index].cancelado_em = new Date().toISOString();
        
        localStorage.setItem('marcacoes', JSON.stringify(marcacoes));
        
        // Enviar email de cancelamento
        await enviarEmailCancelamento(marcacoes[index]);
        
        // Fechar modal
        bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
        
        // Mostrar mensagem de sucesso
        showToast('Consulta cancelada com sucesso! Email de confirmação enviado.', 'success');
        
        // Recarregar marcações
        setTimeout(() => {
            carregarMarcacoes();
        }, 500);
    }
    
    currentCancelId = null;
}

// Enviar email de cancelamento
async function enviarEmailCancelamento(marcacao) {
    try {
        const medico = medicos[marcacao.medico_id] || {};
        const servico = servicos[marcacao.servico_id] || 'Consulta';
        
        const emailData = {
            tipo: 'cancelamento',
            nome: marcacao.paciente_nome || Auth.getUser()?.nome || 'Cliente',
            email: marcacao.paciente_email || Auth.getUser()?.email,
            servico: servico,
            data: formatarDataPorExtenso(marcacao.data),
            hora: marcacao.hora,
            motivo: marcacao.motivo_cancelamento || 'Não especificado'
        };
        
        // EM PRODUÇÃO: Enviar para API PHP
        const response = await fetch('/PSI_M17_04_Solução Web/api/enviar-email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(emailData)
        });
        
        if (response.ok) {
            console.log('Email de cancelamento enviado com sucesso!');
        } else {
            console.warn('Falha ao enviar email de cancelamento');
        }
        
    } catch (error) {
        console.warn('Erro ao enviar email de cancelamento:', error);
    }
}

// Formatar data por extenso
function formatarDataPorExtenso(dataStr) {
    const [ano, mes, dia] = dataStr.split('-');
    const data = new Date(ano, mes - 1, dia);
    
    const diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
    const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    
    return `${diasSemana[data.getDay()]}, ${dia} de ${meses[data.getMonth()]} de ${ano}`;
}

// ============================================
// ADICIONAR AO CALENDÁRIO
// ============================================

function adicionarCalendario(marcacaoId) {
    const marcacoes = JSON.parse(localStorage.getItem('marcacoes') || '[]');
    const marcacao = marcacoes.find(m => m.id === marcacaoId);
    
    if (!marcacao) return;
    
    const medico = medicos[marcacao.medico_id] || {};
    const servico = servicos[marcacao.servico_id] || 'Consulta';
    
    // Criar evento em formato iCal
    const evento = `BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTART:${marcacao.data.replace(/-/g, '')}T${marcacao.hora.replace(':', '')}00
DTEND:${marcacao.data.replace(/-/g, '')}T${addMinutes(marcacao.hora, 60).replace(':', '')}00
SUMMARY:${servico} - DermaCare
DESCRIPTION:Consulta com ${medico.nome}\\n${marcacao.observacoes || ''}
LOCATION:DermaCare - Clínica Dermatológica
END:VEVENT
END:VCALENDAR`;
    
    // Criar arquivo para download
    const blob = new Blob([evento], { type: 'text/calendar' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `consulta-dermacare-${marcacao.id}.ics`;
    link.click();
    
    showToast('Evento adicionado ao calendário!', 'success');
}

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

function getStatusText(status) {
    const statusMap = {
        'pendente': 'Pendente',
        'confirmada': 'Confirmada',
        'concluida': 'Concluída',
        'cancelada': 'Cancelada'
    };
    return statusMap[status] || status;
}

function formatarData(date) {
    if (typeof date === 'string') {
        return date;
    }
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatarDataExtenso(dataStr) {
    const [year, month, day] = dataStr.split('-');
    const date = new Date(year, month - 1, day);
    
    const diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                   'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    
    return `${diasSemana[date.getDay()]}, ${day} de ${meses[date.getMonth()]} de ${year}`;
}

function addDays(date, days) {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

function addMinutes(timeStr, minutes) {
    const [hours, mins] = timeStr.split(':').map(Number);
    const totalMinutes = hours * 60 + mins + minutes;
    const newHours = Math.floor(totalMinutes / 60);
    const newMins = totalMinutes % 60;
    return `${String(newHours).padStart(2, '0')}:${String(newMins).padStart(2, '0')}`;
}

function isDataPassada(dataStr, horaStr) {
    const agora = new Date();
    const dataMarcacao = new Date(dataStr + 'T' + horaStr);
    return dataMarcacao < agora;
}

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
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remover após fechar
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// ============================================
// INICIALIZAÇÃO
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    carregarMarcacoes();
});
