<?php
/**
 * API de Registro de Usuários - DermaCare
 * 
 * Endpoint: /api/registrar.php
 * Método: POST
 * 
 * Recebe dados do formulário de registro e cria novo usuário no banco
 */

require_once 'config.php';

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderErro('Método não permitido. Use POST.', 405);
}

try {
    // Obter dados do POST (JSON ou form-data)
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $dados = json_decode(file_get_contents('php://input'), true);
    } else {
        $dados = $_POST;
    }
    
    // Validar campos obrigatórios
    $camposObrigatorios = ['nome', 'apelido', 'email', 'senha', 'nif', 'dataNascimento', 'telefone'];
    
    foreach ($camposObrigatorios as $campo) {
        if (empty($dados[$campo])) {
            responderErro("Campo obrigatório ausente: {$campo}");
        }
    }
    
    // Validações específicas
    
    // 1. Validar Email
    if (!validarEmail($dados['email'])) {
        responderErro('Email inválido!');
    }
    
    // 2. Validar NIF
    if (!validarNIF($dados['nif'])) {
        responderErro('NIF inválido! Deve ter 9 dígitos válidos.');
    }
    
    // 3. Validar Telefone
    if (!validarTelefone($dados['telefone'])) {
        responderErro('Telefone inválido! Formato esperado: 9 dígitos (ex: 912345678)');
    }
    
    // 4. Validar Telemóvel (se fornecido)
    if (!empty($dados['telemovel']) && !validarTelefone($dados['telemovel'])) {
        responderErro('Telemóvel inválido!');
    }
    
    // 5. Validar Senha
    if (strlen($dados['senha']) < 8) {
        responderErro('A senha deve ter no mínimo 8 caracteres.');
    }
    
    if (!preg_match('/[A-Za-z]/', $dados['senha']) || !preg_match('/[0-9]/', $dados['senha'])) {
        responderErro('A senha deve conter letras e números.');
    }
    
    // 6. Validar confirmação de senha
    if (isset($dados['confirmarSenha']) && $dados['senha'] !== $dados['confirmarSenha']) {
        responderErro('As senhas não coincidem!');
    }
    
    // 7. Validar idade mínima (18 anos)
    $dataNascimento = new DateTime($dados['dataNascimento']);
    $hoje = new DateTime();
    $idade = $hoje->diff($dataNascimento)->y;
    
    if ($idade < 18) {
        responderErro('Deve ter pelo menos 18 anos para se registar.');
    }
    
    // 8. Validar aceitação de termos
    if (empty($dados['termos']) || $dados['termos'] !== true && $dados['termos'] !== 'true' && $dados['termos'] !== '1') {
        responderErro('Deve aceitar os termos e condições.');
    }
    
    // Verificar se email já existe
    $conn = getConexao();
    
    $sqlCheckEmail = "SELECT id FROM usuarios WHERE email = ?";
    $stmtCheck = $conn->prepare($sqlCheckEmail);
    $stmtCheck->bind_param('s', $dados['email']);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    
    if ($resultCheck->num_rows > 0) {
        responderErro('Este email já está registado! Use outro email ou faça login.', 409);
    }
    $stmtCheck->close();
    
    // Verificar se NIF já existe
    $sqlCheckNIF = "SELECT id FROM usuarios WHERE nif = ?";
    $stmtCheckNIF = $conn->prepare($sqlCheckNIF);
    $stmtCheckNIF->bind_param('s', $dados['nif']);
    $stmtCheckNIF->execute();
    $resultCheckNIF = $stmtCheckNIF->get_result();
    
    if ($resultCheckNIF->num_rows > 0) {
        responderErro('Este NIF já está registado!', 409);
    }
    $stmtCheckNIF->close();
    
    // Preparar dados para inserção
    $senhaHash = hashSenha($dados['senha']);
    $tokenVerificacao = gerarToken(32);
    
    // Inserir novo usuário
    $sqlInsert = "INSERT INTO usuarios (
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
        token_verificacao
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sqlInsert);
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar inserção: " . $conn->error);
    }
    
    // Preparar valores
    $nome = $dados['nome'];
    $apelido = $dados['apelido'];
    $email = $dados['email'];
    $telefone = $dados['telefone'];
    $telemovel = $dados['telemovel'] ?? '';
    $nif = $dados['nif'];
    $dataNasc = $dados['dataNascimento'];
    $genero = $dados['genero'] ?? 'prefiro_nao_dizer';
    $endereco = $dados['endereco'] ?? '';
    $codigoPostal = $dados['codigoPostal'] ?? '';
    $cidade = $dados['cidade'] ?? '';
    $seguro = $dados['seguro'] ?? '';
    $numeroSeguro = $dados['numeroSeguro'] ?? '';
    $newsletter = isset($dados['newsletter']) && ($dados['newsletter'] === true || $dados['newsletter'] === 'true' || $dados['newsletter'] === '1') ? 1 : 0;
    
    $stmt->bind_param(
        'ssssssssssssssss',
        $nome,
        $apelido,
        $email,
        $senhaHash,
        $telefone,
        $telemovel,
        $nif,
        $dataNasc,
        $genero,
        $endereco,
        $codigoPostal,
        $cidade,
        $seguro,
        $numeroSeguro,
        $newsletter,
        $tokenVerificacao
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir usuário: " . $stmt->error);
    }
    
    $usuarioId = $stmt->insert_id;
    $stmt->close();
    
    // Registrar log de atividade
    $logMensagem = "Novo usuário registado: {$nome} {$apelido} ({$email})";
    error_log($logMensagem);
    
    // TODO: Enviar email de verificação (integrar com sistema de emails)
    /*
    require_once 'enviar-email.php';
    enviarEmailVerificacao($email, $nome, $tokenVerificacao);
    */
    
    // Preparar dados do usuário para retornar (sem senha!)
    $usuario = [
        'id' => $usuarioId,
        'nome' => $nome,
        'apelido' => $apelido,
        'email' => $email,
        'telefone' => $telefone,
        'telemovel' => $telemovel,
        'nif' => $nif,
        'data_nascimento' => $dataNasc,
        'genero' => $genero,
        'endereco' => $endereco,
        'codigo_postal' => $codigoPostal,
        'cidade' => $cidade,
        'seguro' => $seguro,
        'numero_seguro' => $numeroSeguro,
        'newsletter' => (bool)$newsletter,
        'email_verificado' => false
    ];
    
    // Responder com sucesso
    responderSucesso(
        $usuario,
        'Registo efetuado com sucesso! Bem-vindo ao DermaCare.'
    );
    
} catch (Exception $e) {
    error_log("Erro no registro: " . $e->getMessage());
    responderErro(
        'Erro ao criar conta. Por favor, tente novamente. Detalhes: ' . $e->getMessage(),
        500
    );
}
?>
