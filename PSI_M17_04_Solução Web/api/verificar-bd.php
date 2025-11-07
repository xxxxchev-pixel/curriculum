<?php
/**
 * Script para VERIFICAR o estado da base de dados
 * 
 * Mostra todos os usuários registrados e informações do sistema
 * 
 * Acesse: http://localhost/PSI_M17_04_Solução Web/api/verificar-bd.php
 */

require_once 'config.php';

try {
    $conn = getConexao();
    
    // Obter todos os usuários
    $sqlUsuarios = "SELECT 
        id, 
        nome, 
        apelido, 
        email, 
        nif, 
        telefone,
        data_nascimento,
        genero,
        cidade,
        newsletter,
        email_verificado,
        data_criacao,
        ultimo_login,
        ativo
    FROM usuarios 
    ORDER BY id DESC";
    
    $resultUsuarios = $conn->query($sqlUsuarios);
    
    // Contar totais
    $sqlCountUsuarios = "SELECT COUNT(*) as total FROM usuarios";
    $sqlCountMedicos = "SELECT COUNT(*) as total FROM medicos";
    $sqlCountMarcacoes = "SELECT COUNT(*) as total FROM marcacoes";
    
    $totalUsuarios = $conn->query($sqlCountUsuarios)->fetch_assoc()['total'];
    $totalMedicos = $conn->query($sqlCountMedicos)->fetch_assoc()['total'];
    $totalMarcacoes = $conn->query($sqlCountMarcacoes)->fetch_assoc()['total'];
    
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verificar Base de Dados - DermaCare</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 30px 0;
            }
            .card {
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                margin-bottom: 20px;
            }
            .stat-card {
                text-align: center;
                padding: 20px;
            }
            .stat-number {
                font-size: 3rem;
                font-weight: bold;
            }
            .table-container {
                max-height: 500px;
                overflow-y: auto;
            }
            .badge-custom {
                font-size: 0.8rem;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">
                                <i class="bi bi-database-check"></i> Estado da Base de Dados - DermaCare
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Estatísticas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stat-card bg-light">
                        <div class="card-body">
                            <i class="bi bi-people-fill text-primary" style="font-size: 2rem;"></i>
                            <div class="stat-number text-primary"><?php echo $totalUsuarios; ?></div>
                            <h5>Usuários Registrados</h5>
                            <small class="text-muted">Total de contas criadas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card bg-light">
                        <div class="card-body">
                            <i class="bi bi-hospital text-success" style="font-size: 2rem;"></i>
                            <div class="stat-number text-success"><?php echo $totalMedicos; ?></div>
                            <h5>Médicos Disponíveis</h5>
                            <small class="text-muted">Dermatologistas cadastrados</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card bg-light">
                        <div class="card-body">
                            <i class="bi bi-calendar-check text-info" style="font-size: 2rem;"></i>
                            <div class="stat-number text-info"><?php echo $totalMarcacoes; ?></div>
                            <h5>Marcações Realizadas</h5>
                            <small class="text-muted">Total de agendamentos</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabela de Usuários -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul"></i> Usuários Registrados na Base de Dados
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($totalUsuarios == 0): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                                    <h4 class="mt-3 text-muted">Nenhum Usuário Registrado</h4>
                                    <p class="text-muted">A base de dados está limpa e pronta para novos registros!</p>
                                    <a href="../site/registo.html" class="btn btn-primary mt-3">
                                        <i class="bi bi-person-plus"></i> Criar Primeiro Usuário
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-container">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-dark sticky-top">
                                            <tr>
                                                <th>ID</th>
                                                <th>Nome Completo</th>
                                                <th>Email</th>
                                                <th>NIF</th>
                                                <th>Telefone</th>
                                                <th>Cidade</th>
                                                <th>Gênero</th>
                                                <th>Data Registro</th>
                                                <th>Último Login</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($usuario = $resultUsuarios->fetch_assoc()): ?>
                                                <tr>
                                                    <td><strong>#<?php echo $usuario['id']; ?></strong></td>
                                                    <td>
                                                        <i class="bi bi-person-circle"></i>
                                                        <?php echo htmlspecialchars($usuario['nome'] . ' ' . $usuario['apelido']); ?>
                                                    </td>
                                                    <td>
                                                        <i class="bi bi-envelope"></i>
                                                        <?php echo htmlspecialchars($usuario['email']); ?>
                                                        <?php if ($usuario['email_verificado']): ?>
                                                            <span class="badge bg-success badge-custom ms-1">
                                                                <i class="bi bi-check-circle"></i> Verificado
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($usuario['nif']); ?></td>
                                                    <td>
                                                        <i class="bi bi-telephone"></i>
                                                        <?php echo htmlspecialchars($usuario['telefone']); ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($usuario['cidade'] ?? '-'); ?></td>
                                                    <td>
                                                        <?php 
                                                        $generoIcons = [
                                                            'masculino' => 'bi-gender-male',
                                                            'feminino' => 'bi-gender-female',
                                                            'outro' => 'bi-gender-ambiguous',
                                                            'prefiro_nao_dizer' => 'bi-question-circle'
                                                        ];
                                                        $icon = $generoIcons[$usuario['genero']] ?? 'bi-person';
                                                        echo "<i class='bi {$icon}'></i> ";
                                                        echo ucfirst(str_replace('_', ' ', $usuario['genero']));
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?php 
                                                            $data = new DateTime($usuario['data_criacao']);
                                                            echo $data->format('d/m/Y H:i');
                                                            ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?php 
                                                            if ($usuario['ultimo_login']) {
                                                                $login = new DateTime($usuario['ultimo_login']);
                                                                echo $login->format('d/m/Y H:i');
                                                            } else {
                                                                echo '<span class="text-muted">Nunca</span>';
                                                            }
                                                            ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?php if ($usuario['ativo']): ?>
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle"></i> Ativo
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">
                                                                <i class="bi bi-x-circle"></i> Inativo
                                                            </span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($usuario['newsletter']): ?>
                                                            <span class="badge bg-info ms-1">
                                                                <i class="bi bi-envelope-at"></i> Newsletter
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ações -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="mb-3"><i class="bi bi-tools"></i> Ações Disponíveis</h5>
                            <a href="criar-tabelas.php" class="btn btn-primary me-2">
                                <i class="bi bi-database-add"></i> Recriar Tabelas
                            </a>
                            <a href="limpar-usuarios.php" class="btn btn-warning me-2">
                                <i class="bi bi-trash3"></i> Limpar Usuários
                            </a>
                            <a href="../site/registo.html" class="btn btn-success me-2">
                                <i class="bi bi-person-plus"></i> Novo Registro
                            </a>
                            <a href="../site/login.html" class="btn btn-info">
                                <i class="bi bi-box-arrow-in-right"></i> Ir para Login
                            </a>
                            <button onclick="location.reload()" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Atualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informações -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> Como Funciona</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="bi bi-check-circle-fill text-success"></i> Sistema Real Implementado:</h6>
                                    <ul>
                                        <li>✅ Base de dados MySQL persistente</li>
                                        <li>✅ Sem usuários predefinidos</li>
                                        <li>✅ Cada registro salva permanentemente</li>
                                        <li>✅ Login funciona com dados do MySQL</li>
                                        <li>✅ Senhas criptografadas com bcrypt</li>
                                        <li>✅ Validações completas (NIF, email, etc.)</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="bi bi-shield-check text-primary"></i> Segurança:</h6>
                                    <ul>
                                        <li>🔒 Email e NIF únicos (sem duplicação)</li>
                                        <li>🔒 Senhas nunca armazenadas em texto</li>
                                        <li>🔒 Proteção contra SQL Injection</li>
                                        <li>🔒 Validação de idade mínima (18 anos)</li>
                                        <li>🔒 Validação de formato de telefone PT</li>
                                        <li>🔒 Token de verificação gerado</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            // Auto-refresh a cada 30 segundos
            setTimeout(() => {
                location.reload();
            }, 30000);
        </script>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Erro - DermaCare</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-danger">
        <div class="container mt-5">
            <div class="alert alert-danger">
                <h3>❌ Erro ao verificar base de dados</h3>
                <p><strong>Mensagem:</strong> <?php echo $e->getMessage(); ?></p>
                <hr>
                <p>Certifique-se que:</p>
                <ul>
                    <li>O WAMP está rodando</li>
                    <li>O MySQL está ativo</li>
                    <li>O banco 'dermacare' existe</li>
                    <li>Execute criar-tabelas.php primeiro</li>
                </ul>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
