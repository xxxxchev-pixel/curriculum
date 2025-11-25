<?php
/**
 * GomesTech - Configuração Refatorada
 * 
 * Segurança: sessões hardened, CSRF, prepared statements
 * Performance: utf8mb4, conexão otimizada
 * Manutenibilidade: helpers organizados, .env support
 */

// ===== SESSÕES SEGURAS =====
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? '1' : '0');
    ini_set('session.gc_maxlifetime', '7200'); // 2 horas
    session_start();
}

// ===== CARREGAR .ENV (opcional mas recomendado) =====
if (file_exists(__DIR__ . '/.env')) {
    foreach (file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (!strlen($line) || str_starts_with($line, '#')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $_ENV[$k] = $v;
        putenv("$k=$v");
    }
}

// ===== CONSTANTES DE BD =====
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'gomestech');
define('ENV', $_ENV['ENV'] ?? 'development');
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/gomestech');

// ===== CONEXÃO À BASE DE DADOS =====
function db_connect(): mysqli {
    static $mysqli = null;
    
    if ($mysqli !== null) {
        return $mysqli;
    }
    
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
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #1a1a1a; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
                    .error-box { background: rgba(255,255,255,0.05); border: 2px solid #f44336; border-radius: 16px; padding: 40px; max-width: 600px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
                    h1 { color: #f44336; margin: 0 0 20px 0; font-size: 2em; }
                    p { line-height: 1.6; margin: 15px 0; }
                    .btn { display: inline-block; background: #FF6A00; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: 600; transition: all .2s ease; }
                    .btn:hover { background: #ff8c33; transform: translateY(-2px); box-shadow: 0 8px 16px rgba(255,106,0,0.3); }
                    code { background: rgba(255,255,255,0.1); padding: 3px 8px; border-radius: 4px; font-family: "Courier New", monospace; }
                    ol { text-align: left; margin: 20px auto; max-width: 500px; line-height: 1.8; }
                    li { margin: 12px 0; }
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
                        <li>Importe o ficheiro: <code>database/INSTALAR_BASE_DADOS.sql</code></li>
                        <li>Depois execute: <code>database/importar_catalogo_json.php</code></li>
                        <li>Volte a esta página</li>
                    </ol>
                    <a href="http://localhost/phpmyadmin" class="btn" target="_blank">Abrir phpMyAdmin</a>
                    <a href="' . BASE_URL . '/diagnostico.php" class="btn">Ver Diagnóstico</a>
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
        if (ENV === 'development') {
            error_log($e->getMessage());
        }
        
        $error_msg = '
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Erro de Conexão - GomesTech</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #1a1a1a; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
                .error-box { background: rgba(255,255,255,0.05); border: 2px solid #f44336; border-radius: 16px; padding: 40px; max-width: 600px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
                h1 { color: #f44336; margin: 0 0 20px 0; font-size: 2em; }
                p { line-height: 1.6; margin: 15px 0; }
                .btn { display: inline-block; background: #FF6A00; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: 600; transition: all .2s ease; }
                .btn:hover { background: #ff8c33; transform: translateY(-2px); box-shadow: 0 8px 16px rgba(255,106,0,0.3); }
                code { background: rgba(255,255,255,0.1); padding: 3px 8px; border-radius: 4px; font-family: "Courier New", monospace; }
                ul { text-align: left; margin: 20px auto; max-width: 500px; line-height: 1.8; }
                li { margin: 12px 0; }
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
                    <li>As credenciais em <code>.env</code> ou <code>config.php</code> estão corretas?</li>
                </ul>' .
                (ENV === 'development' ? '<p><strong>Erro técnico:</strong><br><code>' . htmlspecialchars($e->getMessage()) . '</code></p>' : '') .
                '<a href="' . BASE_URL . '/diagnostico.php" class="btn">Ver Diagnóstico Completo</a>
            </div>
        </body>
        </html>';
        die($error_msg);
    }
}

// ===== HELPERS DE SEGURANÇA =====

/**
 * Escape HTML para prevenir XSS
 */
function h(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/**
 * Gerar token CSRF
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verify_csrf_token(?string $token): bool {
    return is_string($token) 
        && isset($_SESSION['csrf_token']) 
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerar ID de sessão (após login)
 */
function regenerate_session(): void {
    session_regenerate_id(true);
}

/**
 * Verificar se utilizador está autenticado
 */
function check_login(): bool {
    return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * Verificar se utilizador é admin
 */
function check_admin(): bool {
    // Allow admin access either via a dedicated admin session flag or via a logged-in user with is_admin
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Redirecionar para login se não autenticado
 */
function require_login(): void {
    if (!check_login()) {
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}

/**
 * Redirecionar para admin login se não for admin
 */
function require_admin(): void {
    if (!check_admin()) {
        header('Location: ' . BASE_URL . '/admin/login_admin.php');
        exit();
    }
}

// ===== HELPERS DE BASE DE DADOS =====

/**
 * Obter todos os produtos
 */
function get_all_produtos(mysqli $mysqli): array {
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

/**
 * Obter produto por ID
 */
function get_produto_by_id(mysqli $mysqli, int $id): ?array {
    $stmt = $mysqli->prepare("SELECT * FROM produtos WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $produto = $result->fetch_assoc();
    $stmt->close();
    
    return $produto ?: null;
}

/**
 * Obter produto por slug
 */
function get_produto_by_slug(mysqli $mysqli, string $slug): ?array {
    $stmt = $mysqli->prepare("SELECT * FROM produtos WHERE slug = ? LIMIT 1");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $produto = $result->fetch_assoc();
    $stmt->close();
    
    return $produto ?: null;
}

/**
 * Obter produtos por categoria
 */
function get_produtos_by_categoria(mysqli $mysqli, string $categoria, int $limit = 0, int $offset = 0): array {
    if ($limit > 0) {
        $stmt = $mysqli->prepare("SELECT * FROM produtos WHERE categoria = ? ORDER BY marca, modelo LIMIT ? OFFSET ?");
        $stmt->bind_param("sii", $categoria, $limit, $offset);
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM produtos WHERE categoria = ? ORDER BY marca, modelo");
        $stmt->bind_param("s", $categoria);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    
    $stmt->close();
    return $produtos;
}

/**
 * Contar produtos por categoria
 */
function count_produtos_by_categoria(mysqli $mysqli, string $categoria): int {
    $stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM produtos WHERE categoria = ?");
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return (int)$row['total'];
}

/**
 * Filtrar produtos
 */
function filter_produtos(mysqli $mysqli, array $filters = [], int $limit = 0, int $offset = 0): array {
    $where = [];
    $params = [];
    $types = '';
    
    if (!empty($filters['categoria'])) {
        $where[] = "categoria = ?";
        $params[] = $filters['categoria'];
        $types .= 's';
    }
    
    if (!empty($filters['marca'])) {
        $where[] = "marca = ?";
        $params[] = $filters['marca'];
        $types .= 's';
    }
    
    if (isset($filters['preco_min'])) {
        $where[] = "preco >= ?";
        $params[] = (float)$filters['preco_min'];
        $types .= 'd';
    }
    
    if (isset($filters['preco_max'])) {
        $where[] = "preco <= ?";
        $params[] = (float)$filters['preco_max'];
        $types .= 'd';
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(marca LIKE ? OR modelo LIKE ? OR descricao LIKE ?)";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'sss';
    }
    
    $sql = "SELECT * FROM produtos";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY marca, modelo";
    
    if ($limit > 0) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
    }
    
    $stmt = $mysqli->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    
    $stmt->close();
    return $produtos;
}

/**
 * Pesquisar produtos (para autocomplete)
 */
function search_produtos(mysqli $mysqli, string $query, int $limit = 10): array {
    $search_term = '%' . $query . '%';
    $stmt = $mysqli->prepare("
        SELECT id, marca, modelo, preco, imagem, slug 
        FROM produtos 
        WHERE marca LIKE ? OR modelo LIKE ? OR descricao LIKE ?
        ORDER BY 
            CASE 
                WHEN marca LIKE ? THEN 1
                WHEN modelo LIKE ? THEN 2
                ELSE 3
            END
        LIMIT ?
    ");
    
    $start_term = $query . '%';
    $stmt->bind_param("sssssi", $search_term, $search_term, $search_term, $start_term, $start_term, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    
    $stmt->close();
    return $produtos;
}

/**
 * Obter categorias disponíveis
 */
function get_categorias(mysqli $mysqli): array {
    $result = $mysqli->query("SELECT DISTINCT categoria FROM produtos ORDER BY categoria");
    $categorias = [];
    
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row['categoria'];
    }
    
    return $categorias;
}

/**
 * Obter marcas por categoria
 */
function get_marcas_by_categoria(mysqli $mysqli, string $categoria): array {
    $stmt = $mysqli->prepare("SELECT DISTINCT marca FROM produtos WHERE categoria = ? ORDER BY marca");
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $marcas = [];
    while ($row = $result->fetch_assoc()) {
        $marcas[] = $row['marca'];
    }
    
    $stmt->close();
    return $marcas;
}

/**
 * Criar slug a partir de string
 */
if (!function_exists('slugify')) {
    function slugify(string $text): string {
        // Converter para ASCII
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        // Lowercase e substituir não-alfanuméricos por hífen
        $text = strtolower(preg_replace('/[^a-z0-9]+/', '-', $text));
        // Remover hífens do início e fim
        $text = trim($text, '-');
        
        return $text;
    }
}

// Função para obter todas as categorias ativas (compatibilidade para chamadas existentes)
if (!function_exists('get_all_categories')) {
    function get_all_categories(mysqli $mysqli): array {
            // Tentativa 1: tabela `categories` (se existir)
            $query = "SHOW TABLES LIKE 'categories'";
            $res = $mysqli->query($query);
            $categories = [];
            if ($res && $res->num_rows > 0) {
                $q = "SELECT * FROM categories WHERE active = 1 ORDER BY COALESCE(display_order,0) ASC, name ASC";
                $result = $mysqli->query($q);
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $categories[] = $row;
                    }
                }
                return $categories;
            }

            // Fallback: construir categorias a partir da coluna `categoria` da tabela `produtos`
            $res2 = $mysqli->query("SELECT DISTINCT categoria FROM produtos ORDER BY categoria");
            if ($res2) {
                while ($r = $res2->fetch_assoc()) {
                    $name = $r['categoria'];
                    $categories[] = [
                        'id' => null,
                        'name' => $name,
                        'slug' => function_exists('slugify') ? slugify($name) : preg_replace('/[^a-z0-9]+/i','-',strtolower($name)),
                        'parent_id' => null,
                        'icon' => ''
                    ];
                }
            }

            return $categories;
    }

    // Compatibilidade: obter marcas/brands no formato esperado pelo catálogo
    if (!function_exists('get_brands_by_category')) {
        function get_brands_by_category(mysqli $mysqli, string $categoria): array {
            // Se existir tabela `brands` e ligação por categoria, tentar usar
            $brands = [];
            $check = $mysqli->query("SHOW TABLES LIKE 'brands'");
            if ($check && $check->num_rows > 0) {
                // Tentar uma coluna `category` ou `categoria`
                $q = "SELECT DISTINCT name FROM brands";
                $res = $mysqli->query($q);
                if ($res) {
                    while ($r = $res->fetch_assoc()) {
                        $n = $r['name'];
                        $brands[] = ['name' => $n, 'slug' => (function_exists('slugify')?slugify($n):preg_replace('/[^a-z0-9]+/i','-',strtolower($n)))];
                    }
                    return $brands;
                }
            }

            // Fallback: obter marcas a partir de produtos filtrando por categoria
            if (empty($categoria)) {
                $stmt = $mysqli->prepare("SELECT DISTINCT marca FROM produtos ORDER BY marca");
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $stmt = $mysqli->prepare("SELECT DISTINCT marca FROM produtos WHERE categoria = ? ORDER BY marca");
                $stmt->bind_param('s', $categoria);
                $stmt->execute();
                $result = $stmt->get_result();
            }

            if ($result) {
                while ($r = $result->fetch_assoc()) {
                    $n = $r['marca'];
                    $brands[] = ['name' => $n, 'slug' => (function_exists('slugify')?slugify($n):preg_replace('/[^a-z0-9]+/i','-',strtolower($n)))];
                }
            }

            return $brands;
        }
    }

    // Verificar se usuário está autenticado
    if (!function_exists('is_authenticated')) {
        function is_authenticated(): bool {
            return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
        }
    }

    // Autenticação: verificar email/password e retornar o utilizador
    if (!function_exists('authenticate_user')) {
        function authenticate_user(mysqli $mysqli, string $email, string $password): array {
            $stmt = $mysqli->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            if (!$stmt) {
                return ['success' => false, 'error' => 'Erro ao conectar à base de dados.'];
            }
            
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();

            if (!$user) {
                return ['success' => false, 'error' => 'Email ou password incorretos.'];
            }

            // Coluna de password pode ter nomes diferentes (password, passwd) — tentar detectar
            $pw_field = 'password';
            if (!isset($user[$pw_field])) {
                foreach ($user as $k => $v) {
                    if (stripos($k, 'pass') !== false) { $pw_field = $k; break; }
                }
            }

            $hash = $user[$pw_field] ?? '';
            if ($hash) {
                // Se estiver em formato hash (bcrypt/argon), usar password_verify
                if (password_verify($password, $hash)) {
                    $user['is_admin'] = isset($user['is_admin']) && ($user['is_admin'] == 1 || $user['is_admin'] === '1');
                    return [
                        'success' => true,
                        'user_id' => $user['id'],
                        'name' => $user['nome'] ?? $user['name'] ?? 'Utilizador',
                        'email' => $user['email'],
                        'is_admin' => $user['is_admin']
                    ];
                }

                // Compatibilidade: alguns registos antigos podem ter password em texto plano.
                // Se o hash armazenado for idêntico à password enviada (login antigo), aceitar e atualizar para hash seguro.
                if ($hash === $password) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $u_stmt = $mysqli->prepare("UPDATE users SET $pw_field = ? WHERE id = ?");
                    if ($u_stmt) {
                        $u_stmt->bind_param('si', $new_hash, $user['id']);
                        $u_stmt->execute();
                        $u_stmt->close();
                    }
                    $user[$pw_field] = $new_hash;
                    $user['is_admin'] = isset($user['is_admin']) && ($user['is_admin'] == 1 || $user['is_admin'] === '1');
                    return [
                        'success' => true,
                        'user_id' => $user['id'],
                        'name' => $user['nome'] ?? $user['name'] ?? 'Utilizador',
                        'email' => $user['email'],
                        'is_admin' => $user['is_admin']
                    ];
                }
            }

            return ['success' => false, 'error' => 'Email ou password incorretos.'];
        }
    }

    // Criar novo utilizador
    if (!function_exists('create_user')) {
        function create_user(mysqli $mysqli, array $data): array {
            // Validar dados obrigatórios
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                return ['success' => false, 'error' => 'Nome, email e password são obrigatórios.'];
            }

            // Validar formato do email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'error' => 'Email inválido.'];
            }

            // Verificar se email já existe
            $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            if (!$stmt) {
                return ['success' => false, 'error' => 'Erro ao conectar à base de dados.'];
            }
            
            $stmt->bind_param('s', $data['email']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $stmt->close();
                return ['success' => false, 'error' => 'Este email já está registado.'];
            }
            $stmt->close();

            // Hash da password
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

            // Preparar dados para inserção
            $nome = $data['name'];
            $email = $data['email'];
            $telefone = $data['phone'] ?? '';
            $morada = $data['address_line1'] ?? '';
            $codigo_postal = $data['postal_code'] ?? '';
            $nif = $data['nif'] ?? '';

            // Inserir utilizador (sem coluna 'cidade' que não existe)
            $query = "INSERT INTO users (nome, email, password, telefone, morada, codigo_postal, nif, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $mysqli->prepare($query);
            if (!$stmt) {
                return ['success' => false, 'error' => 'Erro ao criar conta.'];
            }

            $stmt->bind_param('sssssss', $nome, $email, $password_hash, $telefone, $morada, $codigo_postal, $nif);
            
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $stmt->close();
                
                return [
                    'success' => true,
                    'user_id' => $user_id,
                    'name' => $nome,
                    'email' => $email
                ];
            } else {
                $stmt->close();
                return ['success' => false, 'error' => 'Erro ao criar conta.'];
            }
        }
    }
}

// Função para registar novo utilizador (compatibilidade com registo.php)
if (!function_exists('register_user')) {
    function register_user($mysqli, $data) {
        // Construir inserção dinâmica com base nas colunas existentes na tabela `users`
        $cols_res = $mysqli->query("SHOW COLUMNS FROM users");
        if (!$cols_res) return false;

        $existing = [];
        while ($r = $cols_res->fetch_assoc()) {
            $existing[] = $r['Field'];
        }

        // Mapear campos esperados para keys do $data
        $map = [
            'nome' => 'nome',
            'email' => 'email',
            'password' => 'password', // armazenaremos hash na coluna 'password'
            'telefone' => 'telefone',
            'morada' => 'morada',
            'nif' => 'nif',
            'codigo_postal' => 'codigo_postal'
        ];

        $insert_cols = [];
        $values = [];

        foreach ($map as $key => $col) {
            if (in_array($col, $existing)) {
                if ($key === 'password') {
                    if (!isset($data['password'])) continue;
                    $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
                } else {
                    $values[] = $data[$key] ?? null;
                }
                $insert_cols[] = $col;
            }
        }

        if (empty($insert_cols)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($insert_cols), '?'));
        $sql = "INSERT INTO users (" . implode(',', $insert_cols) . ") VALUES ($placeholders)";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) return false;

        // Montar tipos para bind_param
        $types = '';
        foreach ($values as $v) {
            if (is_int($v)) $types .= 'i';
            elseif (is_float($v)) $types .= 'd';
            else $types .= 's';
        }

        $refs = [];
        $refs[] = & $types;
        for ($i = 0; $i < count($values); $i++) {
            $refs[] = & $values[$i];
        }

        call_user_func_array([$stmt, 'bind_param'], $refs);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}

/**
 * Formatar preço em formato português
 */
function format_price(float $price): string {
    return number_format($price, 2, ',', '.') . ' €';
}

/**
 * Formatar data em português
 */
function format_date(string $date): string {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Flash messages (para feedback ao utilizador)
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ===== HEADERS DE SEGURANÇA =====
header_remove('X-Powered-By');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CSP básica (ajustar conforme necessário)
if (ENV === 'production') {
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self';");
}

// ===== INICIALIZAR CONEXÃO GLOBAL (opcional, para compatibilidade) =====
$mysqli = db_connect();
