<?php
/**
 * Script para criar as tabelas do DermaCare
 * 
 * Execute este arquivo UMA VEZ para criar a estrutura do banco de dados
 * Acesse: http://localhost/PSI_M17_04_Solução Web/api/criar-tabelas.php
 */

require_once 'config.php';

try {
    $conn = getConexao();
    
    // Array para armazenar resultados
    $resultados = [];
    
    // 1. Tabela de Usuários
    $sqlUsuarios = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        apelido VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        senha_hash VARCHAR(255) NOT NULL,
        telefone VARCHAR(20),
        telemovel VARCHAR(20),
        nif VARCHAR(9) UNIQUE,
        data_nascimento DATE,
        genero ENUM('masculino', 'feminino', 'outro', 'prefiro_nao_dizer'),
        endereco TEXT,
        codigo_postal VARCHAR(10),
        cidade VARCHAR(100),
        seguro VARCHAR(100),
        numero_seguro VARCHAR(50),
        newsletter BOOLEAN DEFAULT FALSE,
        foto_perfil VARCHAR(255),
        email_verificado BOOLEAN DEFAULT FALSE,
        token_verificacao VARCHAR(64),
        token_reset_senha VARCHAR(64),
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ultimo_login TIMESTAMP NULL,
        ativo BOOLEAN DEFAULT TRUE,
        INDEX idx_email (email),
        INDEX idx_nif (nif)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sqlUsuarios)) {
        $resultados[] = "✅ Tabela 'usuarios' criada com sucesso!";
    } else {
        $resultados[] = "❌ Erro ao criar tabela 'usuarios': " . $conn->error;
    }
    
    // 2. Tabela de Médicos
    $sqlMedicos = "CREATE TABLE IF NOT EXISTS medicos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        especialidade VARCHAR(100) NOT NULL,
        crm VARCHAR(20) NOT NULL UNIQUE,
        email VARCHAR(150),
        telefone VARCHAR(20),
        bio TEXT,
        foto VARCHAR(255),
        anos_experiencia INT,
        disponivel BOOLEAN DEFAULT TRUE,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_especialidade (especialidade)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sqlMedicos)) {
        $resultados[] = "✅ Tabela 'medicos' criada com sucesso!";
    } else {
        $resultados[] = "❌ Erro ao criar tabela 'medicos': " . $conn->error;
    }
    
    // 3. Tabela de Marcações
    $sqlMarcacoes = "CREATE TABLE IF NOT EXISTS marcacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        medico_id INT NOT NULL,
        data_marcacao DATE NOT NULL,
        hora_marcacao TIME NOT NULL,
        tipo_consulta VARCHAR(100),
        motivo TEXT,
        observacoes TEXT,
        status ENUM('pendente', 'confirmada', 'cancelada', 'concluida', 'falta') DEFAULT 'pendente',
        motivo_cancelamento TEXT,
        email_enviado BOOLEAN DEFAULT FALSE,
        lembrete_enviado BOOLEAN DEFAULT FALSE,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
        INDEX idx_usuario (usuario_id),
        INDEX idx_medico (medico_id),
        INDEX idx_data (data_marcacao),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sqlMarcacoes)) {
        $resultados[] = "✅ Tabela 'marcacoes' criada com sucesso!";
    } else {
        $resultados[] = "❌ Erro ao criar tabela 'marcacoes': " . $conn->error;
    }
    
    // 4. Tabela de Horários Disponíveis
    $sqlHorarios = "CREATE TABLE IF NOT EXISTS horarios_disponiveis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        medico_id INT NOT NULL,
        dia_semana ENUM('segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'),
        hora_inicio TIME NOT NULL,
        hora_fim TIME NOT NULL,
        disponivel BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
        INDEX idx_medico_dia (medico_id, dia_semana)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sqlHorarios)) {
        $resultados[] = "✅ Tabela 'horarios_disponiveis' criada com sucesso!";
    } else {
        $resultados[] = "❌ Erro ao criar tabela 'horarios_disponiveis': " . $conn->error;
    }
    
    // 5. Tabela de Serviços
    $sqlServicos = "CREATE TABLE IF NOT EXISTS servicos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(150) NOT NULL,
        descricao TEXT,
        duracao_minutos INT,
        preco DECIMAL(10, 2),
        categoria VARCHAR(50),
        ativo BOOLEAN DEFAULT TRUE,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sqlServicos)) {
        $resultados[] = "✅ Tabela 'servicos' criada com sucesso!";
    } else {
        $resultados[] = "❌ Erro ao criar tabela 'servicos': " . $conn->error;
    }
    
    // 6. Inserir médicos (SEMPRE, se não existirem - apenas médicos, não usuários)
    $sqlCheckMedicos = "SELECT COUNT(*) as total FROM medicos";
    $result = $conn->query($sqlCheckMedicos);
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        $sqlInsertMedicos = "INSERT INTO medicos (nome, especialidade, crm, email, telefone, bio, foto, anos_experiencia) VALUES
            ('Dra. Ana Silva', 'Dermatologia Clínica', 'CRM12345', 'ana.silva@dermacare.pt', '912345678', 'Especialista em dermatologia clínica com foco em tratamentos estéticos avançados.', 'assets/images/medicos/dra-ana.jpg', 15),
            ('Dr. Carlos Santos', 'Dermatologia Estética', 'CRM23456', 'carlos.santos@dermacare.pt', '913456789', 'Expert em procedimentos estéticos minimamente invasivos e rejuvenescimento facial.', 'assets/images/medicos/dr-carlos.jpg', 12),
            ('Dra. Maria Costa', 'Tricologia', 'CRM34567', 'maria.costa@dermacare.pt', '914567890', 'Especialista em saúde capilar e tratamentos para queda de cabelo.', 'assets/images/medicos/dra-maria.jpg', 10),
            ('Dr. Pedro Oliveira', 'Dermatologia Pediátrica', 'CRM45678', 'pedro.oliveira@dermacare.pt', '915678901', 'Dermatologista pediátrico com experiência em tratamento de doenças de pele em crianças.', 'assets/images/medicos/dr-pedro.jpg', 8)";
        
        if ($conn->query($sqlInsertMedicos)) {
            $resultados[] = "✅ Médicos cadastrados com sucesso!";
        } else {
            $resultados[] = "❌ Erro ao inserir médicos: " . $conn->error;
        }
    } else {
        $resultados[] = "ℹ️ Médicos já existem no banco de dados.";
    }
    
    // Verificar quantos usuários existem
    $sqlCheckUsuarios = "SELECT COUNT(*) as total FROM usuarios";
    $resultUsuarios = $conn->query($sqlCheckUsuarios);
    $rowUsuarios = $resultUsuarios->fetch_assoc();
    
    if ($rowUsuarios['total'] == 0) {
        $resultados[] = "✅ Tabela de usuários está LIMPA - Pronta para receber registros!";
        $resultados[] = "ℹ️ Nenhum usuário predefinido. Cada registro será salvo na base de dados.";
    } else {
        $resultados[] = "ℹ️ Existem {$rowUsuarios['total']} usuário(s) registrado(s) na base de dados.";
    }
    
    // Responder com resultados
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Criação de Tabelas - DermaCare</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 50px 0;
            }
            .container {
                max-width: 800px;
            }
            .card {
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            }
            .resultado {
                padding: 10px;
                margin: 5px 0;
                border-radius: 5px;
                font-family: monospace;
            }
            .sucesso {
                background-color: #d4edda;
                color: #155724;
            }
            .erro {
                background-color: #f8d7da;
                color: #721c24;
            }
            .info {
                background-color: #d1ecf1;
                color: #0c5460;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="bi bi-database"></i> Instalação do Banco de Dados</h3>
                </div>
                <div class="card-body">
                    <h4 class="mb-4">Resultados da Criação das Tabelas:</h4>
                    
                    <?php foreach ($resultados as $resultado): ?>
                        <?php
                        $classe = 'info';
                        if (strpos($resultado, '✅') !== false) {
                            $classe = 'sucesso';
                        } elseif (strpos($resultado, '❌') !== false) {
                            $classe = 'erro';
                        }
                        ?>
                        <div class="resultado <?php echo $classe; ?>">
                            <?php echo $resultado; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="alert alert-success mt-4">
                        <h5><i class="bi bi-check-circle"></i> Instalação Concluída!</h5>
                        <p class="mb-0">O banco de dados DermaCare está pronto para uso.</p>
                        <hr>
                        <p class="mb-0"><strong>Próximos passos:</strong></p>
                        <ol class="mb-0 mt-2">
                            <li>Ir para <a href="../site/registo.html" class="alert-link">Página de Registro</a></li>
                            <li>Criar uma conta de usuário</li>
                            <li>Fazer login e agendar consultas</li>
                        </ol>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <strong><i class="bi bi-info-circle"></i> Informação:</strong>
                        Este script pode ser executado múltiplas vezes com segurança.
                        As tabelas existentes não serão recriadas.
                    </div>
                </div>
            </div>
        </div>
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erro - DermaCare</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-danger text-white">
        <div class="container mt-5">
            <div class="alert alert-danger">
                <h3>❌ Erro ao criar banco de dados</h3>
                <p><strong>Mensagem:</strong> <?php echo $e->getMessage(); ?></p>
                <hr>
                <p><strong>Verificações:</strong></p>
                <ul>
                    <li>O WAMP está rodando?</li>
                    <li>O MySQL está ativo?</li>
                    <li>As configurações em config.php estão corretas?</li>
                </ul>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
