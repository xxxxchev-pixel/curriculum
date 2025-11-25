<?php
require_once __DIR__ . '/../config.php';
// session_start() já foi chamado em config.php

// Autenticação simples
$admin_password = 'admin123'; // ALTERE ESTA PASSWORD!

if (isset($_POST['logout'])) {
    unset($_SESSION['admin_logged']);
    header('Location: imagens.php');
    exit;
}

if (!isset($_SESSION['admin_logged'])) {
    if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged'] = true;
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin - Login</title>
            <link rel="stylesheet" href="css/admin-global.css">
        </head>
        <body style="display:flex;align-items:center;justify-content:center;min-height:100vh;">
            <div class="card" style="max-width:400px;width:100%;">
                <h2 style="text-align:center;color:var(--primary);margin-bottom:30px;">🔐 Área Administrativa</h2>
                <?php if (isset($_POST['password'])): ?>
                    <div class="alert alert-danger">❌ Password incorreta</div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Entrar</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// FILTROS
$filtro_categoria = $_GET['filtro_categoria'] ?? '';
$filtro_marca = $_GET['filtro_marca'] ?? '';
$filtro_tipo_imagem = $_GET['filtro_tipo_imagem'] ?? ''; // local, externa, todas
$pesquisa = trim($_GET['pesquisa'] ?? '');

// Conectar ao banco de dados
$mysqli = db_connect();

// Construir query com filtros
$where = ['1=1'];
$params = [];
$types = '';

if ($filtro_categoria) {
    $where[] = 'categoria = ?';
    $params[] = $filtro_categoria;
    $types .= 's';
}

if ($filtro_marca) {
    $where[] = 'marca = ?';
    $params[] = $filtro_marca;
    $types .= 's';
}

if ($pesquisa) {
    $where[] = '(marca LIKE ? OR modelo LIKE ?)';
    $search_param = '%' . $pesquisa . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$sql = 'SELECT * FROM produtos WHERE ' . implode(' AND ', $where) . ' ORDER BY categoria, marca, modelo';
$stmt = $mysqli->prepare($sql);

if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    $stmt->close();
} else {
    $result = $mysqli->query($sql);
    $produtos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $produtos[] = $row;
        }
    }
}

// Filtro tipo de imagem (após buscar do BD)
if ($filtro_tipo_imagem === 'local') {
    $produtos = array_filter($produtos, fn($p) => strpos($p['imagem'] ?? '', 'uploads/') === 0);
} elseif ($filtro_tipo_imagem === 'externa') {
    $produtos = array_filter($produtos, fn($p) => strpos($p['imagem'] ?? '', 'http') === 0 || empty($p['imagem']));
}

// Buscar categorias e marcas únicas
$categorias_result = $mysqli->query('SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL ORDER BY categoria');
$categorias_disponiveis = [];
if ($categorias_result) {
    while ($row = $categorias_result->fetch_assoc()) {
        $categorias_disponiveis[] = $row['categoria'];
    }
}

$marcas_result = $mysqli->query('SELECT DISTINCT marca FROM produtos WHERE marca IS NOT NULL ORDER BY marca');
$marcas_disponiveis = [];
if ($marcas_result) {
    while ($row = $marcas_result->fetch_assoc()) {
        $marcas_disponiveis[] = $row['marca'];
    }
}

// Processar upload
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagem'])) {
    $produto_id = intval($_POST['produto_id'] ?? 0);
    $file = $_FILES['imagem'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $new_filename = 'produto_' . $produto_id . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $imagem_url = 'uploads/' . $new_filename;
                $stmt = $mysqli->prepare('UPDATE produtos SET imagem=? WHERE id=?');
                if ($stmt) {
                    $stmt->bind_param('si', $imagem_url, $produto_id);
                    $stmt->execute();
                    $stmt->close();
                    $message = "✅ Imagem carregada com sucesso!";
                    
                    // Recarregar produtos
                    header('Location: imagens.php?' . http_build_query($_GET));
                    exit;
                }
            } else {
                $error = "❌ Erro ao mover o arquivo.";
            }
        } else {
            $error = "❌ Formato não permitido. Use: JPG, PNG, GIF ou WEBP.";
        }
    } else {
        $error = "❌ Erro no upload.";
    }
}

// Estatísticas
$total_produtos = count($produtos);
$com_imagem_local = count(array_filter($produtos, fn($p) => strpos($p['imagem'] ?? '', 'uploads/') === 0));
$com_imagem_externa = count(array_filter($produtos, fn($p) => strpos($p['imagem'] ?? '', 'http') === 0));
$sem_imagem = count(array_filter($produtos, fn($p) => empty($p['imagem'])));

// Agrupar por categoria
$produtos_por_categoria = [];
foreach ($produtos as $p) {
    $cat = $p['categoria'] ?? 'Sem Categoria';
    if (!isset($produtos_por_categoria[$cat])) {
        $produtos_por_categoria[$cat] = [];
    }
    $produtos_por_categoria[$cat][] = $p;
}
ksort($produtos_por_categoria);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Imagens - Admin GomesTech</title>
    <link rel="stylesheet" href="css/admin-global.css">
</head>
<body>
    <div class="main-content" style="margin-left:0;">
        <!-- Header -->
        <div class="admin-header">
            <h1>📸 Gestão de Imagens</h1>
            <div style="display:flex;gap:12px;">
                <a href="index.php" class="btn btn-secondary">⬅️ Voltar</a>
                <form method="POST" style="margin:0;">
                    <button type="submit" name="logout" class="btn btn-danger">🚪 Sair</button>
                </form>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-4" style="margin-bottom:30px;">
            <div class="stat-card">
                <div class="stat-icon primary">📦</div>
                <div class="stat-content">
                    <h3><?= $total_produtos ?></h3>
                    <p>Total de Produtos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">✅</div>
                <div class="stat-content">
                    <h3><?= $com_imagem_local ?></h3>
                    <p>Imagens Locais</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info">🌐</div>
                <div class="stat-content">
                    <h3><?= $com_imagem_externa ?></h3>
                    <p>Imagens Externas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">⚠️</div>
                <div class="stat-content">
                    <h3><?= $sem_imagem ?></h3>
                    <p>Sem Imagem</p>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-box">
            <h3>🔍 Filtros de Pesquisa</h3>
            <form method="GET">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Pesquisar:</label>
                        <input type="text" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>" placeholder="Marca ou modelo...">
                    </div>
                    
                    <div class="filter-group">
                        <label>Categoria:</label>
                        <select name="filtro_categoria">
                            <option value="">Todas</option>
                            <?php foreach ($categorias_disponiveis as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $filtro_categoria === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Marca:</label>
                        <select name="filtro_marca">
                            <option value="">Todas</option>
                            <?php foreach ($marcas_disponiveis as $marca): ?>
                                <option value="<?= htmlspecialchars($marca) ?>" <?= $filtro_marca === $marca ? 'selected' : '' ?>><?= htmlspecialchars($marca) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Tipo de Imagem:</label>
                        <select name="filtro_tipo_imagem">
                            <option value="">Todas</option>
                            <option value="local" <?= $filtro_tipo_imagem === 'local' ? 'selected' : '' ?>>📁 Locais</option>
                            <option value="externa" <?= $filtro_tipo_imagem === 'externa' ? 'selected' : '' ?>>🌐 Externas</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary" style="flex:1;">🔍 Filtrar</button>
                        <a href="imagens.php" class="btn btn-secondary" style="flex:1;">🔄 Limpar</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Produtos por Categoria -->
        <?php if (empty($produtos)): ?>
            <div class="alert alert-warning">⚠️ Nenhum produto encontrado com os filtros aplicados.</div>
        <?php else: ?>
            <?php foreach ($produtos_por_categoria as $categoria => $prods): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📦 <?= htmlspecialchars($categoria) ?> <span class="badge badge-primary"><?= count($prods) ?></span></h2>
                    </div>
                    
                    <div class="image-grid">
                        <?php foreach ($prods as $produto): 
                            $tem_imagem_local = strpos($produto['imagem'] ?? '', 'uploads/') === 0;
                            $tem_imagem = !empty($produto['imagem']);
                        ?>
                            <div class="image-item">
                                <img src="<?= $tem_imagem ? '../' . htmlspecialchars($produto['imagem']) : 'https://via.placeholder.com/200/F5F5F7/1D1D1F?text=Sem+Imagem' ?>" 
                                     alt="<?= htmlspecialchars($produto['modelo']) ?>"
                                     onerror="this.src='https://via.placeholder.com/200/F5F5F7/1D1D1F?text=Erro'">
                                
                                <div class="image-item-info">
                                    <div class="image-item-name" title="<?= htmlspecialchars($produto['marca'] . ' ' . $produto['modelo']) ?>">
                                        <?= htmlspecialchars($produto['marca'] . ' ' . $produto['modelo']) ?>
                                    </div>
                                    <div class="image-item-size">
                                        ID: <?= $produto['id'] ?> | €<?= number_format($produto['preco'], 2, ',', '.') ?>
                                    </div>
                                    <div style="margin-top:8px;">
                                        <?php if ($tem_imagem_local): ?>
                                            <span class="badge badge-success">✅ Local</span>
                                        <?php elseif ($tem_imagem): ?>
                                            <span class="badge badge-info">🌐 Externa</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">⚠️ Sem Imagem</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="image-item-actions">
                                    <form method="POST" enctype="multipart/form-data" style="width:100%;" onsubmit="return confirm('Carregar imagem?')">
                                        <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                                        <input type="file" 
                                               name="imagem" 
                                               id="file_<?= $produto['id'] ?>" 
                                               accept="image/jpeg,image/png,image/gif,image/webp"
                                               style="display:none;"
                                               onchange="this.form.querySelector('button').textContent='⬆️ Enviar'; this.form.querySelector('button').classList.add('btn-success');">
                                        <button type="button" 
                                                class="btn btn-primary btn-sm" 
                                                style="width:100%;margin-bottom:8px;"
                                                onclick="document.getElementById('file_<?= $produto['id'] ?>').click()">
                                            📁 Escolher
                                        </button>
                                        <button type="submit" class="btn btn-secondary btn-sm" style="width:100%;">⬆️ Carregar</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Auto-submit quando seleciona arquivo
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const form = this.closest('form');
                    const btn = form.querySelector('button[type="submit"]');
                    btn.textContent = '⬆️ Enviar ' + this.files[0].name.substring(0, 15);
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-success');
                }
            });
        });
    </script>
</body>
</html>

if (isset($_POST['logout'])) {
    unset($_SESSION['admin_logged']);
    header('Location: imagens.php');
    exit;
}

if (!isset($_SESSION['admin_logged'])) {
    if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged'] = true;
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin - Login</title>
            <link rel="stylesheet" href="../css/style.css">
            <style>
                .login-box { max-width: 400px; margin: 100px auto; background: var(--card-bg); padding: 40px; border-radius: 12px; box-shadow: var(--shadow-lg); }
                .login-box h2 { text-align: center; color: var(--accent); margin-bottom: 30px; }
                .form-group { margin-bottom: 20px; }
                .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
                .form-group input { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 16px; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2>🔐 Área Administrativa</h2>
                <?php if (isset($_POST['password'])): ?>
                    <p style="color: #FF4444; text-align: center; margin-bottom: 20px;">❌ Password incorreta</p>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required autofocus>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%;">Entrar</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Carregar produtos
$json_file = __DIR__ . '/../data/catalogo_completo.json';
$json_data = file_get_contents($json_file);
$data = json_decode($json_data, true);
$produtos = $data['produtos'] ?? [];

// Processar upload de imagem
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagem'])) {
    $produto_id = $_POST['produto_id'] ?? '';
    $file = $_FILES['imagem'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Encontrar o produto
            $produto_index = -1;
            foreach ($produtos as $index => $p) {
                if ($p['id'] === $produto_id) {
                    $produto_index = $index;
                    break;
                }
            }
            
            if ($produto_index !== -1) {
                // Criar nome único para o arquivo
                $new_filename = 'produto_' . $produto_id . '_' . time() . '.' . $ext;
                $upload_path = __DIR__ . '/../uploads/' . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Atualizar o caminho da imagem no JSON
                    $produtos[$produto_index]['imagem'] = 'uploads/' . $new_filename;
                    $data['produtos'] = $produtos;
                    
                    // Salvar JSON
                    file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    
                    $message = "✅ Imagem carregada com sucesso para o produto: " . $produtos[$produto_index]['modelo'];
                } else {
                    $error = "❌ Erro ao mover o arquivo.";
                }
            } else {
                $error = "❌ Produto não encontrado.";
            }
        } else {
            $error = "❌ Formato de arquivo não permitido. Use: JPG, PNG, GIF ou WEBP.";
        }
    } else {
        $error = "❌ Erro no upload: " . $file['error'];
    }
    
    // Recarregar produtos após atualização
    $json_data = file_get_contents($json_file);
    $data = json_decode($json_data, true);
    $produtos = $data['produtos'] ?? [];
}

// Agrupar produtos por categoria
$produtos_por_categoria = [];
foreach ($produtos as $p) {
    $cat = $p['categoria'];
    if (!isset($produtos_por_categoria[$cat])) {
        $produtos_por_categoria[$cat] = [];
    }
    $produtos_por_categoria[$cat][] = $p;
}
ksort($produtos_por_categoria);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestão de Imagens</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .admin-header { background: var(--card-bg); padding: 20px; border-radius: 12px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { color: var(--accent); margin: 0; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background: #00C851; color: white; }
        .alert-error { background: #FF4444; color: white; }
        .category-section { background: var(--card-bg); padding: 20px; border-radius: 12px; margin-bottom: 30px; }
        .category-section h2 { color: var(--accent); margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--border); }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .product-card-admin { background: var(--secondary-bg); border: 1px solid var(--border); border-radius: 8px; padding: 15px; transition: all 0.3s; }
        .product-card-admin:hover { border-color: var(--accent); transform: translateY(-2px); }
        .product-image-preview { width: 100%; height: 200px; object-fit: contain; background: var(--primary-bg); border-radius: 8px; margin-bottom: 15px; }
        .product-info-admin { margin-bottom: 15px; }
        .product-info-admin h3 { font-size: 16px; margin-bottom: 5px; color: var(--text); }
        .product-info-admin p { font-size: 14px; color: var(--text-muted); margin-bottom: 5px; }
        .upload-form { display: flex; flex-direction: column; gap: 10px; }
        .file-input-wrapper { position: relative; overflow: hidden; display: inline-block; width: 100%; }
        .file-input-wrapper input[type=file] { position: absolute; left: -9999px; }
        .file-input-label { display: block; padding: 10px; background: var(--primary-bg); border: 2px dashed var(--border); border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.3s; font-size: 14px; }
        .file-input-label:hover { border-color: var(--accent); background: var(--card-bg); }
        .file-selected { border-color: var(--accent); background: var(--card-bg); }
        .btn-upload { background: var(--accent); color: white; padding: 10px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .btn-upload:hover { background: var(--accent-hover); transform: translateY(-2px); }
        .btn-logout { background: #FF4444; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--border); }
        .stat-card h3 { font-size: 14px; color: var(--text-muted); margin-bottom: 10px; }
        .stat-card .number { font-size: 32px; font-weight: 700; color: var(--accent); }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>📸 Gestão de Imagens dos Produtos</h1>
            <div>
                <a href="index.php" class="btn-primary" style="margin-right:10px;">⬅️ Voltar ao Painel</a>
                <form method="POST" style="display:inline-block; margin:0;">
                    <button type="submit" name="logout" class="btn-logout">🚪 Sair</button>
                </form>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Total de Produtos</h3>
                <div class="number"><?php echo count($produtos); ?></div>
            </div>
            <div class="stat-card">
                <h3>Categorias</h3>
                <div class="number"><?php echo count($produtos_por_categoria); ?></div>
            </div>
            <div class="stat-card">
                <h3>Com Imagem Local</h3>
                <div class="number"><?php echo count(array_filter($produtos, function($p) { return strpos($p['imagem'], 'uploads/') === 0; })); ?></div>
            </div>
        </div>

        <?php foreach ($produtos_por_categoria as $categoria => $prods): ?>
            <div class="category-section">
                <h2>📦 <?php echo htmlspecialchars($categoria); ?> (<?php echo count($prods); ?> produtos)</h2>
                <div class="products-grid">
                    <?php foreach ($prods as $produto): ?>
                        <div class="product-card-admin">
                            <img src="../<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                 alt="<?php echo htmlspecialchars($produto['modelo']); ?>"
                                 class="product-image-preview"
                                 onerror="this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400'">
                            
                            <div class="product-info-admin">
                                <h3><?php echo htmlspecialchars($produto['marca'] . ' ' . $produto['modelo']); ?></h3>
                                <p><strong>ID:</strong> <?php echo htmlspecialchars($produto['id']); ?></p>
                                <p><strong>Preço:</strong> €<?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                <?php if (strpos($produto['imagem'], 'uploads/') === 0): ?>
                                    <p style="color: var(--success);">✅ Imagem local carregada</p>
                                <?php else: ?>
                                    <p style="color: var(--warning);">⚠️ Usando imagem externa</p>
                                <?php endif; ?>
                            </div>

                            <form method="POST" enctype="multipart/form-data" class="upload-form" onsubmit="return validateUpload(this)">
                                <input type="hidden" name="produto_id" value="<?php echo htmlspecialchars($produto['id']); ?>">
                                <div class="file-input-wrapper">
                                    <input type="file" 
                                           name="imagem" 
                                           id="file_<?php echo $produto['id']; ?>" 
                                           accept="image/jpeg,image/png,image/gif,image/webp"
                                           onchange="updateLabel(this)">
                                    <label for="file_<?php echo $produto['id']; ?>" class="file-input-label">
                                        📁 Escolher Imagem
                                    </label>
                                </div>
                                <button type="submit" class="btn-upload">⬆️ Carregar Imagem</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function updateLabel(input) {
            const label = input.nextElementSibling;
            if (input.files && input.files[0]) {
                label.textContent = '✓ ' + input.files[0].name;
                label.classList.add('file-selected');
            } else {
                label.textContent = '📁 Escolher Imagem';
                label.classList.remove('file-selected');
            }
        }

        function validateUpload(form) {
            const fileInput = form.querySelector('input[type="file"]');
            if (!fileInput.files || !fileInput.files[0]) {
                alert('Por favor, selecione uma imagem primeiro.');
                return false;
            }
            
            const file = fileInput.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (file.size > maxSize) {
                alert('A imagem é muito grande. Tamanho máximo: 5MB');
                return false;
            }
            
            return confirm('Deseja carregar esta imagem?');
        }
    </script>
</body>
</html>
