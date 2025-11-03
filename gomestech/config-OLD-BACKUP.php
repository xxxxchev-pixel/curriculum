<?php
// Configuração da Base de Dados GomesTech
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'gomestech');
define('DB_USER', 'root');
define('DB_PASS', '');

// Função para conectar à base de dados
function db_connect() {
    try {
        // Tentar conexão sem especificar base de dados primeiro
        $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
        
        if ($mysqli->connect_errno) {
            throw new Exception('Erro de ligação ao MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }
        
        // Verificar se a base de dados existe
        $db_check = $mysqli->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        if ($db_check->num_rows == 0) {
            // Base de dados não existe
            $mysqli->close();
            $error_msg = '
            <!DOCTYPE html>
            <html lang="pt">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Base de Dados Não Encontrada - GomesTech</title>
                <style>
                    body { font-family: Arial, sans-serif; background: #1a1a1a; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
                    .error-box { background: rgba(255,255,255,0.05); border: 2px solid #f44336; border-radius: 12px; padding: 40px; max-width: 600px; text-align: center; }
                    h1 { color: #f44336; margin: 0 0 20px 0; }
                    p { line-height: 1.6; margin: 15px 0; }
                    .btn { display: inline-block; background: #FF6A00; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 5px; }
                    .btn:hover { background: #ff8c33; }
                    code { background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; }
                    ol { text-align: left; margin: 20px auto; max-width: 500px; }
                    li { margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class="error-box">
                    <h1>⚠️ Base de Dados Não Encontrada</h1>
                    <p>A base de dados <code>gomestech</code> não existe.</p>
                    <p><strong>Como corrigir:</strong></p>
                    <ol>
                        <li>Abra o phpMyAdmin clicando no botão abaixo</li>
                        <li>Crie uma base de dados chamada <code>gomestech</code></li>
                        <li>Vá ao separador "Importar"</li>
                        <li>Importe o ficheiro: <code>database/INSTALAR_AQUI.sql</code></li>
                        <li>Depois, no navegador, abra: <code>http://localhost/gomestech/database/importar_catalogo_json.php</code> para carregar todos os produtos do projeto.</li>
                        <li>Volte a esta página</li>
                    </ol>
                    <a href="http://localhost/phpmyadmin" class="btn" target="_blank">Abrir phpMyAdmin</a>
                    <a href="diagnostico.php" class="btn">Ver Diagnóstico</a>
                </div>
            </body>
            </html>';
            die($error_msg);
        }
        
        // Selecionar a base de dados
        $mysqli->select_db(DB_NAME);
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        
        $error_msg = '
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Erro de Conexão - GomesTech</title>
            <style>
                body { font-family: Arial, sans-serif; background: #1a1a1a; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
                .error-box { background: rgba(255,255,255,0.05); border: 2px solid #f44336; border-radius: 12px; padding: 40px; max-width: 600px; text-align: center; }
                h1 { color: #f44336; margin: 0 0 20px 0; }
                p { line-height: 1.6; margin: 15px 0; }
                .btn { display: inline-block; background: #FF6A00; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 5px; }
                .btn:hover { background: #ff8c33; }
                code { background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; }
                ul { text-align: left; margin: 20px auto; max-width: 500px; }
                li { margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <h1>❌ Erro ao Conectar à Base de Dados</h1>
                <p>Não foi possível estabelecer conexão com o MySQL.</p>
                <p><strong>Verifique:</strong></p>
                <ul>
                    <li>O WAMP está a correr? (ícone deve estar verde)</li>
                    <li>O MySQL está ativo?</li>
                    <li>As credenciais em <code>config.php</code> estão corretas?</li>
                </ul>
                <p><strong>Erro técnico:</strong><br><code>' . htmlspecialchars($e->getMessage()) . '</code></p>
                <a href="diagnostico.php" class="btn">Ver Diagnóstico Completo</a>
            </div>
        </body>
        </html>';
        die($error_msg);
    }
}

// Função para obter todos os produtos
function get_all_produtos($mysqli) {
    $query = "SELECT * FROM produtos ORDER BY categoria, marca, modelo";
    $result = $mysqli->query($query);
    
    if (!$result) {
        return [];
    }
    
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    
    return $produtos;
}

// Função para obter produto por ID
function get_produto_by_id($mysqli, $id) {
    $stmt = $mysqli->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Função para obter produtos por categoria
function get_produtos_by_categoria($mysqli, $categoria) {
    $stmt = $mysqli->prepare("SELECT * FROM produtos WHERE categoria = ? ORDER BY marca, modelo");
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    
    return $produtos;
}

// Função para filtrar produtos
function filter_produtos($mysqli, $filters = []) {
    $query = "SELECT p.*, c.name as categoria_nome, c.slug as categoria_slug, b.name as marca_nome, b.slug as marca_slug 
              FROM produtos p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE 1=1";
    $params = [];
    $types = "";
    
    // Filtro por categoria (aceita slug ou nome)
    if (!empty($filters['categoria'])) {
        $query .= " AND (p.categoria = ? OR c.slug = ? OR c.name = ?)";
        $params[] = $filters['categoria'];
        $params[] = $filters['categoria'];
        $params[] = $filters['categoria'];
        $types .= "sss";
    }
    
    // Filtro por marca (aceita slug ou nome)
    if (!empty($filters['marca'])) {
        $query .= " AND (p.marca = ? OR b.slug = ? OR b.name = ?)";
        $params[] = $filters['marca'];
        $params[] = $filters['marca'];
        $params[] = $filters['marca'];
        $types .= "sss";
    }
    
    // Filtro por preço mínimo
    if (isset($filters['min_preco']) && $filters['min_preco'] > 0) {
        $query .= " AND p.preco >= ?";
        $params[] = $filters['min_preco'];
        $types .= "d";
    }
    
    // Filtro por preço máximo
    if (isset($filters['max_preco']) && $filters['max_preco'] > 0) {
        $query .= " AND p.preco <= ?";
        $params[] = $filters['max_preco'];
        $types .= "d";
    }
    
    // Pesquisa por texto
    if (!empty($filters['search'])) {
        $query .= " AND (p.modelo LIKE ? OR p.descricao LIKE ? OR p.marca LIKE ? OR p.categoria LIKE ?)";
        $search = "%" . $filters['search'] . "%";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "ssss";
    }
    
    // Filtro por destaque
    if (isset($filters['destaque']) && $filters['destaque']) {
        $query .= " AND p.destaque = 1";
    }
    
    // Ordenação
    $sort = $filters['sort'] ?? 'modelo_asc';
    switch ($sort) {
        case 'preco_asc':
            $query .= " ORDER BY p.preco ASC";
            break;
        case 'preco_desc':
            $query .= " ORDER BY p.preco DESC";
            break;
        case 'modelo_desc':
            $query .= " ORDER BY p.modelo DESC";
            break;
        case 'destaque':
            $query .= " ORDER BY p.destaque DESC, p.preco ASC";
            break;
        default:
            $query .= " ORDER BY p.modelo ASC";
    }
    
    $stmt = $mysqli->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    
    return $produtos;
}

// Função para obter todas as categorias ativas
function get_all_categories($mysqli) {
    $query = "SELECT * FROM categories WHERE active = 1 ORDER BY display_order ASC, name ASC";
    $result = $mysqli->query($query);
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}

// Função para obter todas as marcas ativas
function get_all_brands($mysqli) {
    $query = "SELECT * FROM brands WHERE active = 1 ORDER BY display_order ASC, name ASC";
    $result = $mysqli->query($query);
    
    $brands = [];
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row;
    }
    
    return $brands;
}

// Função para obter marcas filtradas por categoria
function get_brands_by_category($mysqli, $categoria_slug) {
    if (empty($categoria_slug)) {
        return get_all_brands($mysqli);
    }
    
    $query = "
        SELECT DISTINCT b.* 
        FROM brands b
        INNER JOIN produtos p ON p.brand_id = b.id
        INNER JOIN categories c ON p.category_id = c.id
        WHERE b.active = 1 
        AND (c.slug = ? OR c.name = ?)
        ORDER BY b.display_order ASC, b.name ASC
    ";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $categoria_slug, $categoria_slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $brands = [];
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row;
    }
    
    return $brands;
}

// Função para verificar login
function check_login($mysqli, $email, $password) {
    $stmt = $mysqli->prepare("SELECT id, nome, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    
    return false;
}

// Função para registar novo utilizador
function register_user($mysqli, $data) {
    // Preparar variáveis para bind_param (precisa de variáveis, não expressões)
    $nome = $data['nome'];
    $email = $data['email'];
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $telefone = $data['telefone'] ?? null;
    $morada = $data['morada'] ?? null;
    $nif = $data['nif'] ?? null;
    $codigo_postal = $data['codigo_postal'] ?? null;

    $stmt = $mysqli->prepare("INSERT INTO users (nome, email, password, telefone, morada, nif, codigo_postal) VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssss", 
        $nome,
        $email,
        $password_hash,
        $telefone,
        $morada,
        $nif,
        $codigo_postal
    );
    
    return $stmt->execute();
}

// Função para criar encomenda
function create_order($mysqli, $order_data, $items) {
    $mysqli->begin_transaction();
    
    try {
        // Inserir encomenda
        $stmt = $mysqli->prepare("INSERT INTO orders (user_id, nome, email, telefone, morada, cidade, codigo_postal, metodo_pagamento, total, produtos_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $produtos_json = json_encode($items);
        
        $stmt->bind_param("isssssssds",
            $order_data['user_id'],
            $order_data['nome'],
            $order_data['email'],
            $order_data['telefone'],
            $order_data['morada'],
            $order_data['cidade'],
            $order_data['codigo_postal'],
            $order_data['metodo_pagamento'],
            $order_data['total'],
            $produtos_json
        );
        
        $stmt->execute();
        $order_id = $mysqli->insert_id;
        
        // Inserir itens da encomenda
        $stmt = $mysqli->prepare("INSERT INTO order_items (order_id, produto_id, quantidade, preco_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $subtotal = $item['preco'] * $item['quantidade'];
            $stmt->bind_param("iiidd",
                $order_id,
                $item['id'],
                $item['quantidade'],
                $item['preco'],
                $subtotal
            );
            $stmt->execute();
        }
        
        $mysqli->commit();
        return $order_id;
        
    } catch (Exception $e) {
        $mysqli->rollback();
        error_log($e->getMessage());
        return false;
    }
}

// ============================================
// FUNÇÕES DE AUTENTICAÇÃO
// ============================================

/**
 * Gerar token CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Verificar token CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/**
 * Criar utilizador
 * @return array ['success' => bool, 'user_id' => int|null, 'error' => string|null]
 */
function create_user($mysqli, $data) {
    // Validação
    $errors = [];
    
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $phone = trim($data['phone'] ?? '');
    $address_line1 = trim($data['address_line1'] ?? '');
    $city = trim($data['city'] ?? '');
    $postal_code = trim($data['postal_code'] ?? '');
    
    // Validar nome
    if (strlen($name) < 2 || strlen($name) > 120) {
        $errors[] = 'Nome deve ter entre 2 e 120 caracteres.';
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido.';
    }
    
    // Verificar se email já existe
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return [
            'success' => false,
            'user_id' => null,
            'error' => 'email_exists',
            'message' => 'Já existe uma conta com este email. Por favor, faz login.'
        ];
    }
    
    // Validar password
    if (strlen($password) < 8) {
        $errors[] = 'A palavra-passe deve ter no mínimo 8 caracteres.';
    }
    if (!preg_match('/[A-Za-z]/', $password)) {
        $errors[] = 'A palavra-passe deve conter pelo menos uma letra.';
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = 'A palavra-passe deve conter pelo menos um número.';
    }
    
    // Validar código postal português (opcional mas recomendado)
    if (!empty($postal_code) && !preg_match('/^\d{4}-?\d{3}$/', $postal_code)) {
        $errors[] = 'Código postal inválido (formato: NNNN-NNN).';
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'user_id' => null,
            'error' => 'validation',
            'message' => implode(' ', $errors)
        ];
    }
    
    // Hash da password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Inserir utilizador
    try {
        $stmt = $mysqli->prepare("
            INSERT INTO users (name, email, password_hash, phone, address_line1, city, postal_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssss", $name, $email, $password_hash, $phone, $address_line1, $city, $postal_code);
        $stmt->execute();
        
        $user_id = $mysqli->insert_id;
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'error' => null,
            'message' => 'Conta criada com sucesso!'
        ];
        
    } catch (Exception $e) {
        error_log('Erro ao criar utilizador: ' . $e->getMessage());
        return [
            'success' => false,
            'user_id' => null,
            'error' => 'database',
            'message' => 'Erro ao criar conta. Por favor, tenta novamente.'
        ];
    }
}

/**
 * Autenticar utilizador
 * @return array ['success' => bool, 'user_id' => int|null, 'error' => string|null]
 */
function authenticate_user($mysqli, $email, $password) {
    $email = trim($email);
    
    if (empty($email) || empty($password)) {
        return [
            'success' => false,
            'user_id' => null,
            'error' => 'Preenche todos os campos.'
        ];
    }
    
    // Buscar utilizador
    $stmt = $mysqli->prepare("SELECT id, password_hash, name FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return [
            'success' => false,
            'user_id' => null,
            'error' => 'Credenciais inválidas.'
        ];
    }
    
    $user = $result->fetch_assoc();
    
    // Verificar password
    if (!password_verify($password, $user['password_hash'])) {
        return [
            'success' => false,
            'user_id' => null,
            'error' => 'Credenciais inválidas.'
        ];
    }
    
    return [
        'success' => true,
        'user_id' => $user['id'],
        'name' => $user['name'],
        'error' => null
    ];
}

/**
 * Obter dados do utilizador
 */
function get_user_data($mysqli, $user_id) {
    $stmt = $mysqli->prepare("
        SELECT id, name, email, phone, address_line1, address_line2, city, postal_code, country, created_at 
        FROM users 
        WHERE id = ? 
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

/**
 * Verificar se utilizador está autenticado
 */
function is_authenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Guard para páginas que requerem autenticação
 */
function require_auth($redirect_to = '/auth/login.php') {
    if (!is_authenticated()) {
        $current_page = $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirect_to . '?next=' . urlencode($current_page));
        exit;
    }
}

/**
 * Logout
 */
function logout_user() {
    $_SESSION = [];
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}
?>
