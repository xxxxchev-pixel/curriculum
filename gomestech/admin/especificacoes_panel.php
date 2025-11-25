<?php
// Este arquivo é incluído pelo admin/index.php
// Gestão de especificações técnicas dos produtos

// Processar atualização de especificações
$message_spec = '';
$error_spec = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_specs'])) {
    $produto_id = intval($_POST['produto_id'] ?? 0);
    $especificacoes = $_POST['especificacoes'] ?? [];
    
    // Converter array de especificações para JSON
    $specs_json = json_encode($especificacoes, JSON_UNESCAPED_UNICODE);
    
    $stmt = $mysqli->prepare('UPDATE produtos SET especificacoes=? WHERE id=?');
    if ($stmt) {
        $stmt->bind_param('si', $specs_json, $produto_id);
        if ($stmt->execute()) {
            $message_spec = "✅ Especificações atualizadas com sucesso!";
        } else {
            $error_spec = "❌ Erro ao atualizar: " . $stmt->error;
        }
        $stmt->close();
    }
}

// FILTROS
$filtro_categoria = $_GET['filtro_categoria'] ?? '';
$filtro_marca = $_GET['filtro_marca'] ?? '';
$pesquisa = trim($_GET['pesquisa'] ?? '');
$produto_selecionado = intval($_GET['produto'] ?? 0);

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
    $produtos_specs = [];
    while ($row = $result->fetch_assoc()) {
        $produtos_specs[] = $row;
    }
    $stmt->close();
} else {
    $result = $mysqli->query($sql);
    $produtos_specs = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $produtos_specs[] = $row;
        }
    }
}

// Buscar categorias e marcas
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
$total_produtos = count($produtos_specs);
$com_specs = count(array_filter($produtos_specs, fn($p) => !empty($p['especificacoes'])));
$sem_specs = $total_produtos - $com_specs;

// Especificações padrão por categoria
$specs_templates = [
    'Smartphones' => [
        'ecra' => 'Ecrã',
        'processador' => 'Processador',
        'ram' => 'RAM',
        'armazenamento' => 'Armazenamento',
        'cameras' => 'Câmaras',
        'bateria' => 'Bateria',
        'sistema' => 'Sistema'
    ],
    'Laptops' => [
        'ecra' => 'Ecrã',
        'processador' => 'Processador',
        'ram' => 'RAM',
        'armazenamento' => 'Armazenamento',
        'grafica' => 'Gráfica',
        'bateria' => 'Bateria',
        'sistema' => 'Sistema Operativo'
    ],
    'TVs' => [
        'tamanho' => 'Tamanho',
        'resolucao' => 'Resolução',
        'tecnologia' => 'Tecnologia',
        'smart' => 'Smart TV',
        'hdmi' => 'Portas HDMI',
        'usb' => 'Portas USB'
    ],
    'Default' => [
        'especificacao1' => 'Especificação 1',
        'especificacao2' => 'Especificação 2',
        'especificacao3' => 'Especificação 3',
        'especificacao4' => 'Especificação 4'
    ]
];
?>

<?php if ($message_spec): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message_spec) ?></div>
<?php endif; ?>
<?php if ($error_spec): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_spec) ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="grid grid-3" style="margin-bottom:30px;">
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
            <h3><?= $com_specs ?></h3>
            <p>Com Especificações</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">⚠️</div>
        <div class="stat-content">
            <h3><?= $sem_specs ?></h3>
            <p>Sem Especificações</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filters-box">
    <h3>🔍 Filtros de Pesquisa</h3>
    <form method="GET">
        <input type="hidden" name="page" value="especificacoes">
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
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary" style="flex:1;">🔍 Filtrar</button>
                <a href="?page=especificacoes" class="btn btn-secondary" style="flex:1;">🔄 Limpar</a>
            </div>
        </div>
    </form>
</div>

<!-- Lista de Produtos -->
<?php if (empty($produtos_specs)): ?>
    <div class="alert alert-warning">⚠️ Nenhum produto encontrado.</div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📋 Produtos</h2>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagem</th>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos_specs as $produto): 
                    $tem_specs = !empty($produto['especificacoes']);
                ?>
                    <tr>
                        <td><?= $produto['id'] ?></td>
                        <td>
                            <img src="../<?= htmlspecialchars($produto['imagem'] ?? 'https://via.placeholder.com/50') ?>" 
                                 alt="<?= htmlspecialchars($produto['modelo']) ?>"
                                 style="width:50px; height:50px; object-fit:cover; border-radius:8px;"
                                 onerror="this.src='https://via.placeholder.com/50'">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($produto['marca']) ?></strong><br>
                            <small><?= htmlspecialchars($produto['modelo']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($produto['categoria']) ?></td>
                        <td>
                            <?php if ($tem_specs): ?>
                                <span class="badge badge-success">✅ Com Specs</span>
                            <?php else: ?>
                                <span class="badge badge-warning">⚠️ Sem Specs</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=especificacoes&produto=<?= $produto['id'] ?>&<?= http_build_query($_GET) ?>" 
                               class="btn btn-primary btn-sm">
                                ✏️ Editar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal de Edição -->
<?php if ($produto_selecionado > 0):
    $produto_edit = null;
    foreach ($produtos_specs as $p) {
        if ($p['id'] == $produto_selecionado) {
            $produto_edit = $p;
            break;
        }
    }
    
    if ($produto_edit):
        $specs_atuais = [];
        if (!empty($produto_edit['especificacoes'])) {
            $specs_atuais = json_decode($produto_edit['especificacoes'], true) ?? [];
        }
        
        // Escolher template baseado na categoria
        $template = $specs_templates[$produto_edit['categoria']] ?? $specs_templates['Default'];
?>
    <div style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:9999; display:flex; align-items:center; justify-content:center; padding:20px;">
        <div class="card" style="max-width:800px; width:100%; max-height:90vh; overflow-y:auto; margin:0;">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h2 class="card-title">📝 Editar Especificações</h2>
                <a href="?page=especificacoes&<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'produto', ARRAY_FILTER_USE_KEY)) ?>" 
                   class="btn btn-secondary btn-sm">✖️ Fechar</a>
            </div>
            
            <div style="padding:20px;">
                <div style="margin-bottom:20px; padding:15px; background:#f5f5f7; border-radius:8px;">
                    <strong><?= htmlspecialchars($produto_edit['marca'] . ' ' . $produto_edit['modelo']) ?></strong><br>
                    <small>Categoria: <?= htmlspecialchars($produto_edit['categoria']) ?></small>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="update_specs" value="1">
                    <input type="hidden" name="produto_id" value="<?= $produto_edit['id'] ?>">
                    
                    <div style="display:grid; gap:15px;">
                        <?php foreach ($template as $key => $label): ?>
                            <div class="filter-group">
                                <label><?= htmlspecialchars($label) ?>:</label>
                                <input type="text" 
                                       name="especificacoes[<?= htmlspecialchars($key) ?>]" 
                                       value="<?= htmlspecialchars($specs_atuais[$key] ?? '') ?>"
                                       placeholder="Ex: <?= $key === 'ecra' ? '6.1\" Full HD+' : '' ?><?= $key === 'ram' ? '4GB a 12GB' : '' ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top:20px; display:flex; gap:10px;">
                        <button type="submit" class="btn btn-success" style="flex:1;">💾 Guardar</button>
                        <a href="?page=especificacoes&<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'produto', ARRAY_FILTER_USE_KEY)) ?>" 
                           class="btn btn-secondary" style="flex:1;">❌ Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; endif; ?>
