<?php
/**
 * API de Login - DermaCare
 * 
 * Endpoint: /api/login.php
 * Método: POST
 * 
 * Autentica usuário com email e senha
 */

require_once 'config.php';

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderErro('Método não permitido. Use POST.', 405);
}

try {
    // Obter dados do POST
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $dados = json_decode(file_get_contents('php://input'), true);
    } else {
        $dados = $_POST;
    }
    
    // Validar campos obrigatórios
    if (empty($dados['email']) || empty($dados['senha'])) {
        responderErro('Email e senha são obrigatórios!');
    }
    
    // Validar formato de email
    if (!validarEmail($dados['email'])) {
        responderErro('Email inválido!');
    }
    
    $email = $dados['email'];
    $senha = $dados['senha'];
    
    // Buscar usuário no banco
    $conn = getConexao();
    
    $sql = "SELECT 
                id, 
                nome, 
                apelido, 
                email, 
                senha_hash, 
                telefone, 
                telemovel, 
                nif, 
                data_nascimento, 
                genero, 
                endereco, 
                codigo_postal, 
                cidade, 
                seguro, 
                numero_seguro, 
                newsletter, 
                foto_perfil, 
                email_verificado,
                ativo
            FROM usuarios 
            WHERE email = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Verificar se usuário existe
    if ($result->num_rows === 0) {
        responderErro('Email ou senha incorretos!', 401);
    }
    
    $usuario = $result->fetch_assoc();
    $stmt->close();
    
    // Verificar se usuário está ativo
    if (!$usuario['ativo']) {
        responderErro('Conta desativada. Entre em contato com o suporte.', 403);
    }
    
    // Verificar senha
    if (!verificarSenha($senha, $usuario['senha_hash'])) {
        responderErro('Email ou senha incorretos!', 401);
    }
    
    // Atualizar último login
    $sqlUpdateLogin = "UPDATE usuarios SET ultimo_login = CURRENT_TIMESTAMP WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdateLogin);
    $stmtUpdate->bind_param('i', $usuario['id']);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    
    // Remover senha_hash dos dados retornados
    unset($usuario['senha_hash']);
    unset($usuario['ativo']);
    
    // Converter valores booleanos
    $usuario['newsletter'] = (bool)$usuario['newsletter'];
    $usuario['email_verificado'] = (bool)$usuario['email_verificado'];
    
    // Registrar log
    error_log("Login bem-sucedido: {$usuario['nome']} {$usuario['apelido']} ({$email})");
    
    // Responder com sucesso
    responderSucesso(
        $usuario,
        'Login efetuado com sucesso! Bem-vindo de volta.'
    );
    
} catch (Exception $e) {
    error_log("Erro no login: " . $e->getMessage());
    responderErro(
        'Erro ao efetuar login. Tente novamente.',
        500
    );
}
?>
