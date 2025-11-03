<?php
require_once __DIR__ . '/../config.php';
session_start();

// Autenticação simples (use um sistema mais robusto em produção)
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
