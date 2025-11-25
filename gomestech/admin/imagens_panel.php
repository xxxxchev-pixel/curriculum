<?php
// Este arquivo é incluído pelo admin/index.php
// NÃO deve ter autenticação própria

// Processar upload ANTES de buscar produtos
$message_img = '';
$error_img = '';
$upload_success = false;

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
                    if ($stmt->execute()) {
                        $message_img = "✅ Imagem carregada com sucesso!";
                        $upload_success = true;
                    } else {
                        $error_img = "❌ Erro ao atualizar a base de dados: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error_img = "❌ Erro ao preparar a query: " . $mysqli->error;
                }
            } else {
                $error_img = "❌ Erro ao mover o arquivo. Verifique as permissões da pasta uploads/";
            }
        } else {
            $error_img = "❌ Formato não permitido. Use: JPG, PNG, GIF ou WEBP.";
        }
    } else {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do PHP)',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande',
            UPLOAD_ERR_PARTIAL => 'Upload parcial',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada',
            UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever no disco',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
        ];
        $error_img = "❌ " . ($error_messages[$file['error']] ?? "Erro no upload: {$file['error']}");
    }
}

// FILTROS
$filtro_categoria = $_GET['filtro_categoria'] ?? '';
$filtro_marca = $_GET['filtro_marca'] ?? '';
$filtro_tipo_imagem = $_GET['filtro_tipo_imagem'] ?? ''; // local, externa, todas
$pesquisa = trim($_GET['pesquisa'] ?? '');

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
    $produtos_imagens = [];
    while ($row = $result->fetch_assoc()) {
        $produtos_imagens[] = $row;
    }
    $stmt->close();
} else {
    $result = $mysqli->query($sql);
    $produtos_imagens = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $produtos_imagens[] = $row;
        }
    }
}

// Filtro tipo de imagem (após buscar do BD)
if ($filtro_tipo_imagem === 'local') {
    $produtos_imagens = array_filter($produtos_imagens, fn($p) => strpos($p['imagem'] ?? '', 'uploads/') === 0);
} elseif ($filtro_tipo_imagem === 'externa') {
    $produtos_imagens = array_filter($produtos_imagens, fn($p) => strpos($p['imagem'] ?? '', 'http') === 0 || empty($p['imagem']));
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

// Estatísticas
$total_produtos_img = count($produtos_imagens);
$com_imagem_local = count(array_filter($produtos_imagens, fn($p) => strpos($p['imagem'] ?? '', 'uploads/') === 0));
$com_imagem_externa = count(array_filter($produtos_imagens, fn($p) => strpos($p['imagem'] ?? '', 'http') === 0));
$sem_imagem = count(array_filter($produtos_imagens, fn($p) => empty($p['imagem'])));

// Agrupar por categoria
$produtos_por_categoria = [];
foreach ($produtos_imagens as $p) {
    $cat = $p['categoria'] ?? 'Sem Categoria';
    if (!isset($produtos_por_categoria[$cat])) {
        $produtos_por_categoria[$cat] = [];
    }
    $produtos_por_categoria[$cat][] = $p;
}
ksort($produtos_por_categoria);
?>

<?php if ($message_img): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message_img) ?></div>
<?php endif; ?>
<?php if ($error_img): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_img) ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="grid grid-4" style="margin-bottom:30px;">
    <div class="stat-card">
        <div class="stat-icon primary">📦</div>
        <div class="stat-content">
            <h3><?= $total_produtos_img ?></h3>
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
        <input type="hidden" name="page" value="imagens">
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
                <a href="?page=imagens" class="btn btn-secondary" style="flex:1;">🔄 Limpar</a>
            </div>
        </div>
    </form>
</div>

<!-- Produtos por Categoria -->
<?php if (empty($produtos_imagens)): ?>
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
                    <div class="image-item" id="item_<?= $produto['id'] ?>">
                        <div class="image-wrapper" style="position:relative; background:#f5f5f7; border-radius:12px; overflow:hidden; height:200px; display:flex; align-items:center; justify-content:center;">
                            <img id="img_<?= $produto['id'] ?>" 
                                 src="<?= $tem_imagem ? '../' . htmlspecialchars($produto['imagem']) : 'https://via.placeholder.com/200/F5F5F7/1D1D1F?text=Sem+Imagem' ?>" 
                                 alt="<?= htmlspecialchars($produto['modelo']) ?>"
                                 style="max-width:100%; max-height:100%; object-fit:contain; display:block;"
                                 onerror="this.src='https://via.placeholder.com/200/F5F5F7/1D1D1F?text=Erro'">
                        </div>
                        
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
                            <form method="POST" enctype="multipart/form-data" style="width:100%;">
                                <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                                <input type="file" 
                                       name="imagem" 
                                       id="file_<?= $produto['id'] ?>" 
                                       accept="image/jpeg,image/png,image/gif,image/webp"
                                       style="display:none;"
                                       onchange="previewImage(this, <?= $produto['id'] ?>)">
                                <button type="button" 
                                        class="btn btn-primary btn-sm" 
                                        style="width:100%;margin-bottom:8px;"
                                        onclick="document.getElementById('file_<?= $produto['id'] ?>').click()">
                                    📁 Escolher Imagem
                                </button>
                                <button type="submit" 
                                        class="btn btn-secondary btn-sm" 
                                        id="btn_upload_<?= $produto['id'] ?>"
                                        style="width:100%; display:none;">
                                    ⬆️ Carregar Agora
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
// Preview da imagem antes de fazer upload
function previewImage(input, productId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const img = document.getElementById('img_' + productId);
        const btn = document.getElementById('btn_upload_' + productId);
        
        reader.onload = function(e) {
            img.src = e.target.result;
            btn.style.display = 'block';
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-success');
            btn.textContent = '⬆️ Carregar: ' + input.files[0].name.substring(0, 20);
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
