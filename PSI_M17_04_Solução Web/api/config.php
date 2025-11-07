<?php
/**
 * Configuração da Base de Dados - DermaCare
 * 
 * Este arquivo contém as configurações de conexão com o MySQL
 * e funções auxiliares para gestão da base de dados.
 */

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Usuário padrão do WAMP
define('DB_PASS', '');               // Senha vazia no WAMP por padrão
define('DB_NAME', 'dermacare');      // Nome da base de dados

// Configurações de erro
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers para JSON API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar OPTIONS request (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Função para obter conexão com o banco de dados
 * 
 * @return mysqli Conexão MySQLi
 * @throws Exception Se não conseguir conectar
 */
function getConexao() {
    static $conexao = null;
    
    if ($conexao === null) {
        try {
            $conexao = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            // Verificar conexão
            if ($conexao->connect_error) {
                throw new Exception("Erro de conexão: " . $conexao->connect_error);
            }
            
            // Definir charset
            $conexao->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            // Se o banco não existe, tentar criar
            $conexao = new mysqli(DB_HOST, DB_USER, DB_PASS);
            
            if ($conexao->connect_error) {
                throw new Exception("Erro ao conectar ao MySQL: " . $conexao->connect_error);
            }
            
            // Criar banco de dados
            if (!$conexao->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                throw new Exception("Erro ao criar banco de dados: " . $conexao->error);
            }
            
            $conexao->select_db(DB_NAME);
            $conexao->set_charset("utf8mb4");
        }
    }
    
    return $conexao;
}

/**
 * Executar query com prepared statement
 * 
 * @param string $sql Query SQL com placeholders (?)
 * @param array $params Parâmetros para bind
 * @param string $types Tipos dos parâmetros (s=string, i=int, d=double, b=blob)
 * @return mysqli_result|bool Resultado da query
 */
function executarQuery($sql, $params = [], $types = '') {
    $conn = getConexao();
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar query: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}

/**
 * Responder com JSON
 * 
 * @param mixed $data Dados para enviar
 * @param int $statusCode Código HTTP
 */
function responderJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * Responder com erro
 * 
 * @param string $mensagem Mensagem de erro
 * @param int $statusCode Código HTTP
 */
function responderErro($mensagem, $statusCode = 400) {
    responderJSON([
        'sucesso' => false,
        'erro' => $mensagem
    ], $statusCode);
}

/**
 * Responder com sucesso
 * 
 * @param mixed $dados Dados de resposta
 * @param string $mensagem Mensagem de sucesso
 */
function responderSucesso($dados = null, $mensagem = 'Operação realizada com sucesso') {
    $resposta = [
        'sucesso' => true,
        'mensagem' => $mensagem
    ];
    
    if ($dados !== null) {
        $resposta['dados'] = $dados;
    }
    
    responderJSON($resposta, 200);
}

/**
 * Validar email
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar telefone português
 */
function validarTelefone($telefone) {
    // Remove espaços e caracteres especiais
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Telefone fixo: 9 dígitos começando com 2
    // Telemóvel: 9 dígitos começando com 9
    return preg_match('/^(2|9)\d{8}$/', $telefone);
}

/**
 * Validar NIF português
 */
function validarNIF($nif) {
    // Remove espaços
    $nif = preg_replace('/\s+/', '', $nif);
    
    // Deve ter 9 dígitos
    if (!preg_match('/^\d{9}$/', $nif)) {
        return false;
    }
    
    // Validar checksum
    $check = 0;
    for ($i = 0; $i < 8; $i++) {
        $check += intval($nif[$i]) * (9 - $i);
    }
    
    $checkDigit = 11 - ($check % 11);
    if ($checkDigit >= 10) {
        $checkDigit = 0;
    }
    
    return intval($nif[8]) === $checkDigit;
}

/**
 * Hash seguro de senha
 */
function hashSenha($senha) {
    return password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verificar senha
 */
function verificarSenha($senha, $hash) {
    return password_verify($senha, $hash);
}

/**
 * Gerar token aleatório
 */
function gerarToken($length = 32) {
    return bin2hex(random_bytes($length));
}

?>
