<?php
/**
 * Script para LIMPAR todos os usuários da base de dados
 * 
 * ⚠️ ATENÇÃO: Este script remove TODOS os usuários registrados!
 * Use apenas para testes ou para recomeçar do zero.
 * 
 * Os médicos NÃO serão removidos (apenas usuários).
 * 
 * Acesse: http://localhost/PSI_M17_04_Solução Web/api/limpar-usuarios.php
 */

require_once 'config.php';

try {
    $conn = getConexao();
    
    // Verificar quantos usuários existem
    $sqlCount = "SELECT COUNT(*) as total FROM usuarios";
    $result = $conn->query($sqlCount);
    $row = $result->fetch_assoc();
    $totalUsuarios = $row['total'];
    
    // Se não tem usuários, apenas informa
    if ($totalUsuarios == 0) {
        $mensagem = "✅ A base de dados já está limpa! Nenhum usuário para remover.";
        $classe = "success";
    } else {
        // Deletar todos os usuários
        $sqlDelete = "DELETE FROM usuarios";
        
        if ($conn->query($sqlDelete)) {
            // Reset auto increment para começar do 1 novamente
            $conn->query("ALTER TABLE usuarios AUTO_INCREMENT = 1");
            
            $mensagem = "✅ Base de dados limpa com sucesso! {$totalUsuarios} usuário(s) removido(s).";
            $classe = "success";
        } else {
            $mensagem = "❌ Erro ao limpar base de dados: " . $conn->error;
            $classe = "danger";
        }
    }
    
    // Verificar quantos médicos existem
    $sqlCountMedicos = "SELECT COUNT(*) as total FROM medicos";
    $resultMedicos = $conn->query($sqlCountMedicos);
    $rowMedicos = $resultMedicos->fetch_assoc();
    $totalMedicos = $rowMedicos['total'];
    
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Limpar Base de Dados - DermaCare</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 50px 0;
            }
            .card {
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            }
            .icon-lg {
                font-size: 3rem;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-<?php echo $classe; ?> text-white">
                            <h3 class="mb-0">
                                <i class="bi bi-trash3"></i> Limpeza da Base de Dados
                            </h3>
                        </div>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-check-circle-fill text-<?php echo $classe; ?> icon-lg"></i>
                            <h4 class="mt-4"><?php echo $mensagem; ?></h4>
                            
                            <div class="alert alert-info mt-4">
                                <h5><i class="bi bi-info-circle"></i> Status da Base de Dados:</h5>
                                <hr>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="text-muted">Usuários Registrados</h6>
                                                <h2 class="mb-0">
                                                    <?php 
                                                    // Recontar após limpeza
                                                    $resultAtual = $conn->query($sqlCount);
                                                    $rowAtual = $resultAtual->fetch_assoc();
                                                    echo $rowAtual['total'];
                                                    ?>
                                                </h2>
                                                <small class="text-muted">Cada registro salva na BD</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="text-muted">Médicos Disponíveis</h6>
                                                <h2 class="mb-0"><?php echo $totalMedicos; ?></h2>
                                                <small class="text-muted">Não foram removidos</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning mt-4">
                                <strong><i class="bi bi-exclamation-triangle"></i> Importante:</strong>
                                <ul class="text-start mt-3 mb-0">
                                    <li>✅ Base de dados limpa - pronta para novos registros</li>
                                    <li>✅ Nenhum usuário predefinido existe</li>
                                    <li>✅ Cada registro é salvo permanentemente no MySQL</li>
                                    <li>✅ Médicos permanecem disponíveis para consultas</li>
                                    <li>✅ Login funciona apenas com usuários registrados</li>
                                </ul>
                            </div>
                            
                            <div class="mt-4">
                                <a href="../site/registo.html" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-person-plus"></i> Criar Primeira Conta
                                </a>
                                <a href="../site/login.html" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Ir para Login
                                </a>
                            </div>
                            
                            <div class="mt-4">
                                <small class="text-muted">
                                    <i class="bi bi-shield-check"></i> 
                                    Todos os novos registros serão salvos automaticamente
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-terminal"></i> Como Funciona Agora</h5>
                        </div>
                        <div class="card-body">
                            <ol>
                                <li class="mb-2">
                                    <strong>Registro:</strong> 
                                    Cada novo usuário é salvo em <code>usuarios</code> com senha criptografada
                                </li>
                                <li class="mb-2">
                                    <strong>Login:</strong> 
                                    Sistema verifica email/senha no banco de dados MySQL
                                </li>
                                <li class="mb-2">
                                    <strong>Persistência:</strong> 
                                    Dados permanecem salvos - usuário pode logar quando quiser
                                </li>
                                <li class="mb-2">
                                    <strong>Segurança:</strong> 
                                    Senhas criptografadas com bcrypt, NIF e email únicos
                                </li>
                            </ol>
                            
                            <div class="alert alert-success mt-3 mb-0">
                                <strong>✅ Sistema 100% Real:</strong> Não há mais dados simulados ou predefinidos!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                <h3>❌ Erro ao limpar base de dados</h3>
                <p><strong>Mensagem:</strong> <?php echo $e->getMessage(); ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
