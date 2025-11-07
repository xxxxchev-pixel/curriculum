-- ============================================
-- DermaCare - Base de Dados Final Consolidada
-- Sistema Completo de Gestão de Clínica Dermatológica
-- Data: 07/11/2025
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================
-- TABELA DE USUÁRIOS (Unificada)
-- ============================================

DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    apelido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    
    -- Dados de Contato
    telefone VARCHAR(20),
    telemovel VARCHAR(20),
    
    -- Documentação
    nif VARCHAR(9) UNIQUE,
    
    -- Dados Pessoais
    data_nascimento DATE,
    genero ENUM('masculino', 'feminino', 'outro', 'prefiro_nao_dizer'),
    
    -- Endereço
    endereco TEXT,
    codigo_postal VARCHAR(10),
    cidade VARCHAR(100),
    pais VARCHAR(100) DEFAULT 'Portugal',
    
    -- Seguro de Saúde
    seguro VARCHAR(100),
    numero_seguro VARCHAR(50),
    
    -- Preferências
    newsletter BOOLEAN DEFAULT FALSE,
    
    -- Imagem
    foto_perfil VARCHAR(255),
    
    -- Segurança
    email_verificado BOOLEAN DEFAULT FALSE,
    token_verificacao VARCHAR(64),
    token_reset_senha VARCHAR(64),
    
    -- Controle
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    
    -- Índices
    INDEX idx_email (email),
    INDEX idx_nif (nif),
    INDEX idx_nome (nome, apelido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE MÉDICOS
-- ============================================

DROP TABLE IF EXISTS medicos;
CREATE TABLE medicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    especialidade VARCHAR(100) NOT NULL,
    crm VARCHAR(20) NOT NULL UNIQUE COMMENT 'CRM ou Número da Ordem',
    
    -- Contato
    email VARCHAR(150),
    telefone VARCHAR(20),
    
    -- Informações Profissionais
    bio TEXT,
    foto VARCHAR(255),
    anos_experiencia INT,
    formacao TEXT,
    
    -- Status
    disponivel BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_especialidade (especialidade),
    INDEX idx_disponivel (disponivel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE HORÁRIOS DOS MÉDICOS
-- ============================================

DROP TABLE IF EXISTS horarios_disponiveis;
CREATE TABLE horarios_disponiveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medico_id INT NOT NULL,
    dia_semana ENUM('segunda','terca','quarta','quinta','sexta','sabado','domingo'),
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    disponivel BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
    INDEX idx_medico_dia (medico_id, dia_semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE MARCAÇÕES/CONSULTAS
-- ============================================

DROP TABLE IF EXISTS marcacoes;
CREATE TABLE marcacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    medico_id INT NOT NULL,
    
    -- Data e Hora
    data_marcacao DATE NOT NULL,
    hora_marcacao TIME NOT NULL,
    duracao_minutos INT DEFAULT 30,
    
    -- Informações da Consulta
    tipo_consulta VARCHAR(100),
    motivo TEXT,
    observacoes TEXT,
    
    -- Status e Controle
    status ENUM('pendente', 'confirmada', 'cancelada', 'concluida', 'falta') DEFAULT 'pendente',
    motivo_cancelamento TEXT,
    
    -- Pagamento
    valor DECIMAL(10, 2),
    forma_pagamento ENUM('dinheiro', 'cartao', 'mbway', 'transferencia', 'seguro'),
    pago BOOLEAN DEFAULT FALSE,
    
    -- Notificações
    email_enviado BOOLEAN DEFAULT FALSE,
    lembrete_enviado BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    confirmada_em TIMESTAMP NULL,
    cancelada_em TIMESTAMP NULL,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
    
    INDEX idx_usuario (usuario_id),
    INDEX idx_medico (medico_id),
    INDEX idx_data (data_marcacao, hora_marcacao),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE DETALHES DA CONSULTA (Prontuário)
-- ============================================

DROP TABLE IF EXISTS consultas_detalhes;
CREATE TABLE consultas_detalhes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marcacao_id INT NOT NULL UNIQUE,
    
    -- Anamnese
    queixa_principal TEXT,
    historia_doenca TEXT,
    
    -- Exame
    exame_fisico TEXT,
    
    -- Diagnóstico e Tratamento
    diagnostico TEXT,
    tratamento_prescrito TEXT,
    observacoes_medicas TEXT,
    
    -- Acompanhamento
    proxima_consulta DATE,
    
    -- Timestamps
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (marcacao_id) REFERENCES marcacoes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE CATEGORIAS DE SERVIÇOS
-- ============================================

DROP TABLE IF EXISTS categorias_servicos;
CREATE TABLE categorias_servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    icone VARCHAR(50),
    cor VARCHAR(20),
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    
    INDEX idx_ordem (ordem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE SERVIÇOS
-- ============================================

DROP TABLE IF EXISTS servicos;
CREATE TABLE servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    descricao_detalhada TEXT,
    duracao_minutos INT DEFAULT 30,
    preco DECIMAL(10, 2),
    preco_minimo DECIMAL(10, 2),
    imagem VARCHAR(255),
    ativo BOOLEAN DEFAULT TRUE,
    destaque BOOLEAN DEFAULT FALSE,
    
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (categoria_id) REFERENCES categorias_servicos(id) ON DELETE SET NULL,
    INDEX idx_categoria (categoria_id),
    INDEX idx_ativo (ativo),
    INDEX idx_destaque (destaque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE DOCUMENTOS
-- ============================================

DROP TABLE IF EXISTS documentos;
CREATE TABLE documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    marcacao_id INT,
    
    tipo_documento ENUM('receita', 'exame', 'relatorio', 'atestado', 'outro') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    ficheiro VARCHAR(255) NOT NULL,
    tamanho_kb INT,
    mime_type VARCHAR(100),
    
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (marcacao_id) REFERENCES marcacoes(id) ON DELETE SET NULL,
    
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE NOTIFICAÇÕES
-- ============================================

DROP TABLE IF EXISTS notificacoes;
CREATE TABLE notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('email', 'sms', 'push', 'sistema') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    lida BOOLEAN DEFAULT FALSE,
    link VARCHAR(255),
    
    enviada BOOLEAN DEFAULT FALSE,
    enviada_em TIMESTAMP NULL,
    
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_lida (usuario_id, lida),
    INDEX idx_criado (data_criacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE MENSAGENS DE CONTACTO
-- ============================================

DROP TABLE IF EXISTS mensagens_contacto;
CREATE TABLE mensagens_contacto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    assunto VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    
    respondida BOOLEAN DEFAULT FALSE,
    resposta TEXT,
    respondida_em TIMESTAMP NULL,
    
    ip_address VARCHAR(45),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_respondida (respondida),
    INDEX idx_criado (data_criacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE CONFIGURAÇÕES DO SISTEMA
-- ============================================

DROP TABLE IF EXISTS configuracoes;
CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    descricao TEXT,
    
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DADOS INICIAIS - MÉDICOS
-- ============================================

INSERT INTO medicos (nome, especialidade, crm, email, telefone, bio, foto, anos_experiencia, disponivel) VALUES
('Dra. Ana Silva', 'Dermatologia Clínica', 'CRM12345', 'ana.silva@dermacare.pt', '912345678', 
 'Especialista em dermatologia clínica com foco em tratamentos estéticos avançados e cuidados preventivos. Experiência no diagnóstico e tratamento de acne, rosácea e envelhecimento cutâneo.', 
 'assets/images/medicos/dra-ana.jpg', 15, TRUE),

('Dr. Carlos Santos', 'Dermatologia Estética', 'CRM23456', 'carlos.santos@dermacare.pt', '913456789', 
 'Expert em procedimentos estéticos minimamente invasivos e rejuvenescimento facial. Especializado em aplicação de toxina botulínica, preenchimentos e bioestimuladores de colágeno.', 
 'assets/images/medicos/dr-carlos.jpg', 12, TRUE),

('Dra. Maria Costa', 'Tricologia', 'CRM34567', 'maria.costa@dermacare.pt', '914567890', 
 'Especialista em saúde capilar e tratamentos para queda de cabelo. Dedicada ao diagnóstico e tratamento de alopecias e doenças do couro cabeludo.', 
 'assets/images/medicos/dra-maria.jpg', 10, TRUE),

('Dr. Pedro Oliveira', 'Dermatologia Pediátrica', 'CRM45678', 'pedro.oliveira@dermacare.pt', '915678901', 
 'Dermatologista pediátrico com vasta experiência em tratamento de doenças de pele em crianças e adolescentes. Especializado em dermatite atópica e hemangiomas.', 
 'assets/images/medicos/dr-pedro.jpg', 8, TRUE);

-- ============================================
-- DADOS INICIAIS - HORÁRIOS DOS MÉDICOS
-- ============================================

-- Dra. Ana Silva (Segunda a Sexta: 9h-17h)
INSERT INTO horarios_disponiveis (medico_id, dia_semana, hora_inicio, hora_fim, disponivel) VALUES
(1, 'segunda', '09:00:00', '17:00:00', TRUE),
(1, 'terca', '09:00:00', '17:00:00', TRUE),
(1, 'quarta', '09:00:00', '17:00:00', TRUE),
(1, 'quinta', '09:00:00', '17:00:00', TRUE),
(1, 'sexta', '09:00:00', '17:00:00', TRUE);

-- Dr. Carlos Santos (Segunda a Sexta: 10h-18h)
INSERT INTO horarios_disponiveis (medico_id, dia_semana, hora_inicio, hora_fim, disponivel) VALUES
(2, 'segunda', '10:00:00', '18:00:00', TRUE),
(2, 'terca', '10:00:00', '18:00:00', TRUE),
(2, 'quarta', '10:00:00', '18:00:00', TRUE),
(2, 'quinta', '10:00:00', '18:00:00', TRUE),
(2, 'sexta', '10:00:00', '18:00:00', TRUE);

-- Dra. Maria Costa (Segunda a Sexta: 9h-16h)
INSERT INTO horarios_disponiveis (medico_id, dia_semana, hora_inicio, hora_fim, disponivel) VALUES
(3, 'segunda', '09:00:00', '16:00:00', TRUE),
(3, 'terca', '09:00:00', '16:00:00', TRUE),
(3, 'quarta', '09:00:00', '16:00:00', TRUE),
(3, 'quinta', '09:00:00', '16:00:00', TRUE),
(3, 'sexta', '09:00:00', '16:00:00', TRUE);

-- Dr. Pedro Oliveira (Segunda a Sexta: 14h-20h)
INSERT INTO horarios_disponiveis (medico_id, dia_semana, hora_inicio, hora_fim, disponivel) VALUES
(4, 'segunda', '14:00:00', '20:00:00', TRUE),
(4, 'terca', '14:00:00', '20:00:00', TRUE),
(4, 'quarta', '14:00:00', '20:00:00', TRUE),
(4, 'quinta', '14:00:00', '20:00:00', TRUE),
(4, 'sexta', '14:00:00', '20:00:00', TRUE);

-- ============================================
-- DADOS INICIAIS - CATEGORIAS DE SERVIÇOS
-- ============================================

INSERT INTO categorias_servicos (nome, descricao, icone, cor, ordem, ativo) VALUES
('Dermatologia Clínica', 'Diagnóstico e tratamento de doenças da pele', 'bi-hospital', 'primary', 1, TRUE),
('Estética Facial', 'Tratamentos estéticos e rejuvenescimento facial', 'bi-stars', 'info', 2, TRUE),
('Tratamentos a Laser', 'Procedimentos a laser para diversos fins', 'bi-lightning', 'warning', 3, TRUE),
('Prevenção', 'Rastreio e prevenção de doenças dermatológicas', 'bi-shield-check', 'success', 4, TRUE),
('Estética Corporal', 'Tratamentos estéticos corporais', 'bi-person', 'secondary', 5, TRUE),
('Tricologia', 'Tratamentos capilares especializados', 'bi-scissors', 'danger', 6, TRUE);

-- ============================================
-- DADOS INICIAIS - SERVIÇOS
-- ============================================

INSERT INTO servicos (categoria_id, nome, descricao, duracao_minutos, preco, ativo, destaque) VALUES
-- Dermatologia Clínica
(1, 'Consulta de Dermatologia Geral', 'Avaliação dermatológica completa e diagnóstico de condições cutâneas', 45, 60.00, TRUE, FALSE),
(1, 'Tratamento de Acne', 'Tratamento personalizado para acne leve, moderada e severa', 45, 80.00, TRUE, TRUE),
(1, 'Dermatite e Eczema', 'Diagnóstico e tratamento de dermatites e eczemas', 40, 70.00, TRUE, FALSE),
(1, 'Psoríase', 'Tratamento especializado para psoríase', 50, 90.00, TRUE, FALSE),
(1, 'Rosácea', 'Tratamento e controle da rosácea', 40, 75.00, TRUE, FALSE),

-- Estética Facial
(2, 'Toxina Botulínica (Botox)', 'Aplicação de botox para suavização de rugas', 30, 250.00, TRUE, TRUE),
(2, 'Preenchimento com Ácido Hialurónico', 'Preenchimento facial com ácido hialurónico', 45, 350.00, TRUE, TRUE),
(2, 'Peeling Químico', 'Renovação celular através de peeling químico', 60, 120.00, TRUE, FALSE),
(2, 'Microagulhamento', 'Estimulação de colágeno com microagulhamento', 75, 150.00, TRUE, FALSE),
(2, 'Limpeza de Pele Profunda', 'Limpeza profunda com extração e hidratação', 60, 80.00, TRUE, FALSE),

-- Tratamentos a Laser
(3, 'Laser de Rejuvenescimento', 'Rejuvenescimento facial a laser', 60, 200.00, TRUE, TRUE),
(3, 'Depilação a Laser', 'Remoção definitiva de pelos a laser', 30, 75.00, TRUE, FALSE),
(3, 'Remoção de Manchas', 'Tratamento de manchas e hiperpigmentação a laser', 45, 150.00, TRUE, FALSE),
(3, 'Remoção de Tatuagens', 'Remoção de tatuagens a laser', 45, 150.00, TRUE, FALSE),
(3, 'Tratamento de Vasinhos', 'Laser para vasinhos e telangiectasias', 40, 120.00, TRUE, FALSE),

-- Prevenção
(4, 'Mapeamento de Sinais', 'Rastreio completo de sinais e manchas com dermatoscopia digital', 60, 100.00, TRUE, TRUE),
(4, 'Check-up Dermatológico', 'Avaliação completa da pele e orientação preventiva', 45, 60.00, TRUE, FALSE),
(4, 'Avaliação de Risco Cutâneo', 'Avaliação de fatores de risco para câncer de pele', 40, 70.00, TRUE, FALSE),

-- Estética Corporal
(5, 'Criolipólise', 'Redução de gordura localizada', 90, 300.00, TRUE, TRUE),
(5, 'Tratamento de Celulite', 'Tratamento combinado para celulite', 75, 150.00, TRUE, FALSE),
(5, 'Drenagem Linfática', 'Drenagem linfática corporal', 60, 80.00, TRUE, FALSE),
(5, 'Radiofrequência Corporal', 'Firmeza e redução de medidas', 60, 120.00, TRUE, FALSE),

-- Tricologia
(6, 'Consulta de Tricologia', 'Avaliação completa do couro cabeludo e cabelos', 60, 100.00, TRUE, FALSE),
(6, 'Tratamento de Queda de Cabelo', 'Protocolo personalizado para alopecia', 60, 150.00, TRUE, TRUE),
(6, 'Mesoterapia Capilar', 'Aplicação de ativos para fortalecimento capilar', 45, 120.00, TRUE, FALSE),
(6, 'Tratamento de Caspa e Oleosidade', 'Controle de dermatite seborreica', 40, 80.00, TRUE, FALSE);

-- ============================================
-- DADOS INICIAIS - CONFIGURAÇÕES
-- ============================================

INSERT INTO configuracoes (chave, valor, tipo, descricao) VALUES
('site_nome', 'DermaCare', 'string', 'Nome da clínica'),
('site_slogan', 'Cuidando da sua pele com excelência', 'string', 'Slogan da clínica'),
('site_email', 'geral@dermacare.pt', 'string', 'Email principal'),
('site_telefone', '+351 210 000 000', 'string', 'Telefone principal'),
('site_whatsapp', '+351 912 345 678', 'string', 'WhatsApp para contacto'),
('site_endereco', 'Av. da Liberdade, 123, 1250-142 Lisboa', 'string', 'Endereço da clínica'),
('horario_segunda_sexta', '09:00 - 19:00', 'string', 'Horário Segunda a Sexta'),
('horario_sabado', '09:00 - 13:00', 'string', 'Horário Sábado'),
('horario_domingo', 'Fechado', 'string', 'Horário Domingo'),
('duracao_intervalo_marcacoes', '30', 'integer', 'Duração do intervalo entre marcações (minutos)'),
('antecedencia_minima_marcacao', '24', 'integer', 'Antecedência mínima para marcação (horas)'),
('antecedencia_minima_cancelamento', '24', 'integer', 'Antecedência mínima para cancelamento (horas)'),
('smtp_host', 'smtp.gmail.com', 'string', 'Servidor SMTP para envio de emails'),
('smtp_port', '587', 'integer', 'Porta SMTP'),
('smtp_user', '', 'string', 'Usuário SMTP (configurar)'),
('smtp_pass', '', 'string', 'Senha SMTP (configurar)'),
('google_maps_api_key', '', 'string', 'Google Maps API Key (configurar)'),
('facebook_page', 'https://facebook.com/dermacare', 'string', 'Página do Facebook'),
('instagram_page', 'https://instagram.com/dermacare', 'string', 'Página do Instagram'),
('linkedin_page', 'https://linkedin.com/company/dermacare', 'string', 'Página do LinkedIn');

-- ============================================
-- VIEWS ÚTEIS
-- ============================================

-- View de Marcações com Detalhes Completos
CREATE OR REPLACE VIEW vw_marcacoes_completas AS
SELECT 
    m.id,
    m.data_marcacao,
    m.hora_marcacao,
    m.duracao_minutos,
    m.status,
    m.tipo_consulta,
    m.valor,
    m.pago,
    CONCAT(u.nome, ' ', u.apelido) AS paciente_nome,
    u.email AS paciente_email,
    u.telefone AS paciente_telefone,
    u.telemovel AS paciente_telemovel,
    CONCAT(med.nome) AS medico_nome,
    med.especialidade AS medico_especialidade,
    m.data_criacao,
    m.email_enviado,
    m.lembrete_enviado
FROM marcacoes m
INNER JOIN usuarios u ON m.usuario_id = u.id
INNER JOIN medicos med ON m.medico_id = med.id;

-- View de Agenda Diária dos Médicos
CREATE OR REPLACE VIEW vw_agenda_medicos AS
SELECT 
    m.id AS marcacao_id,
    m.data_marcacao,
    m.hora_marcacao,
    m.duracao_minutos,
    ADDTIME(m.hora_marcacao, SEC_TO_TIME(m.duracao_minutos * 60)) AS hora_fim,
    m.status,
    med.id AS medico_id,
    med.nome AS medico_nome,
    med.especialidade,
    CONCAT(u.nome, ' ', u.apelido) AS paciente_nome,
    u.telefone AS paciente_telefone,
    m.tipo_consulta
FROM marcacoes m
INNER JOIN medicos med ON m.medico_id = med.id
INNER JOIN usuarios u ON m.usuario_id = u.id
WHERE m.status NOT IN ('cancelada')
ORDER BY m.data_marcacao, m.hora_marcacao;

-- View de Estatísticas Gerais
CREATE OR REPLACE VIEW vw_estatisticas AS
SELECT 
    (SELECT COUNT(*) FROM usuarios WHERE ativo = TRUE) AS total_usuarios,
    (SELECT COUNT(*) FROM medicos WHERE disponivel = TRUE) AS total_medicos,
    (SELECT COUNT(*) FROM marcacoes WHERE status = 'pendente') AS marcacoes_pendentes,
    (SELECT COUNT(*) FROM marcacoes WHERE status = 'confirmada') AS marcacoes_confirmadas,
    (SELECT COUNT(*) FROM marcacoes WHERE status = 'concluida') AS marcacoes_concluidas,
    (SELECT COUNT(*) FROM marcacoes WHERE data_marcacao = CURDATE()) AS marcacoes_hoje,
    (SELECT SUM(valor) FROM marcacoes WHERE status = 'concluida' AND pago = TRUE AND MONTH(data_marcacao) = MONTH(CURDATE())) AS faturamento_mes;

-- ============================================
-- STORED PROCEDURES
-- ============================================

DELIMITER //

-- Verificar disponibilidade de horário
DROP PROCEDURE IF EXISTS sp_verificar_disponibilidade//
CREATE PROCEDURE sp_verificar_disponibilidade(
    IN p_medico_id INT,
    IN p_data DATE,
    IN p_hora TIME,
    IN p_duracao INT
)
BEGIN
    SELECT COUNT(*) AS conflitos
    FROM marcacoes
    WHERE medico_id = p_medico_id
    AND data_marcacao = p_data
    AND status NOT IN ('cancelada')
    AND (
        (hora_marcacao <= p_hora AND ADDTIME(hora_marcacao, SEC_TO_TIME(duracao_minutos * 60)) > p_hora)
        OR
        (hora_marcacao < ADDTIME(p_hora, SEC_TO_TIME(p_duracao * 60)) AND hora_marcacao >= p_hora)
    );
END//

-- Obter próximas marcações do usuário
DROP PROCEDURE IF EXISTS sp_proximas_marcacoes_usuario//
CREATE PROCEDURE sp_proximas_marcacoes_usuario(
    IN p_usuario_id INT
)
BEGIN
    SELECT 
        m.id,
        m.data_marcacao,
        m.hora_marcacao,
        m.status,
        m.tipo_consulta,
        med.nome AS medico_nome,
        med.especialidade
    FROM marcacoes m
    INNER JOIN medicos med ON m.medico_id = med.id
    WHERE m.usuario_id = p_usuario_id
    AND m.data_marcacao >= CURDATE()
    AND m.status NOT IN ('cancelada', 'concluida')
    ORDER BY m.data_marcacao, m.hora_marcacao
    LIMIT 10;
END//

DELIMITER ;

-- ============================================
-- TRIGGERS
-- ============================================

DELIMITER //

-- Trigger: Atualizar status para confirmada quando email for enviado
DROP TRIGGER IF EXISTS tr_marcacao_email_enviado//
CREATE TRIGGER tr_marcacao_email_enviado
AFTER UPDATE ON marcacoes
FOR EACH ROW
BEGIN
    IF NEW.email_enviado = TRUE AND OLD.email_enviado = FALSE AND NEW.status = 'pendente' THEN
        UPDATE marcacoes SET status = 'confirmada' WHERE id = NEW.id;
    END IF;
END//

DELIMITER ;

-- ============================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- ============================================

CREATE INDEX IF NOT EXISTS idx_marcacoes_data_status ON marcacoes(data_marcacao, status);
CREATE INDEX IF NOT EXISTS idx_marcacoes_usuario_data ON marcacoes(usuario_id, data_marcacao DESC);
CREATE INDEX IF NOT EXISTS idx_usuarios_ativo ON usuarios(ativo);
CREATE INDEX IF NOT EXISTS idx_medicos_disponivel ON medicos(disponivel);

-- ============================================
-- FIM DA BASE DE DADOS
-- ============================================

COMMIT;
