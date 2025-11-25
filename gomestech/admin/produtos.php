<?php
// Gestão de Produtos com Filtros Avançados
if (!isset($mysqli) || !$mysqli) {
    $mysqli = db_connect();
}

// FILTROS
$filtro_categoria = $_GET['filtro_categoria'] ?? '';
$filtro_marca = $_GET['filtro_marca'] ?? '';
$filtro_destaque = $_GET['filtro_destaque'] ?? '';
$filtro_novidade = $_GET['filtro_novidade'] ?? '';
$filtro_promocao = $_GET['filtro_promocao'] ?? '';
$filtro_stock_baixo = $_GET['filtro_stock_baixo'] ?? '';
$pesquisa = trim($_GET['pesquisa'] ?? '');

// QUICK UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_update'])) {
    $id = intval($_POST['id'] ?? 0);
    $preco = floatval($_POST['preco'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $novidade = isset($_POST['novidade']) ? 1 : 0;
    $promocao = isset($_POST['promocao']) ? 1 : 0;
    
    $stmt = $mysqli->prepare('UPDATE produtos SET preco=?, stock=?, destaque=?, novidade=?, promocao=? WHERE id=?');
    if ($stmt) {
        $stmt->bind_param('diiiii', $preco, $stock, $destaque, $novidade, $promocao, $id);
        $stmt->execute();
        $stmt->close();
        echo '<div class="alert alert-success">✅ Produto atualizado!</div>';
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $mysqli->prepare('DELETE FROM produtos WHERE id=?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        echo '<div class="alert alert-success">🗑️ Produto removido!</div>';
    }
}

// SAVE (ADD/EDIT)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $id = intval($_POST['id'] ?? 0);
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $preco = floatval($_POST['preco'] ?? 0);
    $preco_antigo = floatval($_POST['preco_antigo'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $imagem = trim($_POST['imagem'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $novidade = isset($_POST['novidade']) ? 1 : 0;
    $promocao = isset($_POST['promocao']) ? 1 : 0;
    
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $marca . '-' . $modelo));
    
    if ($id > 0) {
        $stmt = $mysqli->prepare('UPDATE produtos SET marca=?, modelo=?, categoria=?, slug=?, preco=?, preco_antigo=?, stock=?, imagem=?, descricao=?, destaque=?, novidade=?, promocao=? WHERE id=?');
        if ($stmt) {
            $stmt->bind_param('ssssddiisiiii', $marca, $modelo, $categoria, $slug, $preco, $preco_antigo, $stock, $imagem, $descricao, $destaque, $novidade, $promocao, $id);
            $stmt->execute();
            $stmt->close();
            echo '<div class="alert alert-success">💾 Produto atualizado!</div>';
        }
    } else {
        $stmt = $mysqli->prepare('INSERT INTO produtos (marca, modelo, categoria, slug, preco, preco_antigo, stock, imagem, descricao, destaque, novidade, promocao) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        if ($stmt) {
            $stmt->bind_param('ssssddissiii', $marca, $modelo, $categoria, $slug, $preco, $preco_antigo, $stock, $imagem, $descricao, $destaque, $novidade, $promocao);
            $stmt->execute();
            $stmt->close();
            echo '<div class="alert alert-success">✅ Novo produto criado!</div>';
        }
    }
}

// CONSTRUIR QUERY COM FILTROS
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
if ($filtro_destaque) {
    $where[] = 'destaque = 1';
}
if ($filtro_novidade) {
    $where[] = 'novidade = 1';
}
if ($filtro_promocao) {
    $where[] = 'promocao = 1';
}
if ($filtro_stock_baixo) {
    $where[] = 'stock < 10';
}
if ($pesquisa) {
    $where[] = '(marca LIKE ? OR modelo LIKE ? OR descricao LIKE ?)';
    $search_param = '%' . $pesquisa . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$sql = 'SELECT * FROM produtos WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC';
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

// Get unique values for filters
$categorias_result = $mysqli->query('SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL AND categoria != "" ORDER BY categoria');
$categorias_disponiveis = [];
if ($categorias_result) {
    while ($row = $categorias_result->fetch_assoc()) {
        $categorias_disponiveis[] = $row['categoria'];
    }
}

$marcas_result = $mysqli->query('SELECT DISTINCT marca FROM produtos WHERE marca IS NOT NULL AND marca != "" ORDER BY marca');
$marcas_disponiveis = [];
if ($marcas_result) {
    while ($row = $marcas_result->fetch_assoc()) {
        $marcas_disponiveis[] = $row['marca'];
    }
}

// Edit mode
$action = $_GET['action'] ?? '';
$edit_id = intval($_GET['id'] ?? 0);
$edit_product = null;
if ($action === 'edit' && $edit_id) {
    $stmt = $mysqli->prepare('SELECT * FROM produtos WHERE id=?');
    if ($stmt) {
        $stmt->bind_param('i', $edit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_product = $result->fetch_assoc();
        $stmt->close();
    }
}
?>

<!-- Filtros -->
<div style="background: var(--card-bg); padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid var(--border); box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="margin: 0 0 15px 0; display: flex; align-items: center; gap: 8px;">
        <span>🔍</span>
        <span>Filtros de Pesquisa</span>
    </h3>
    <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
        <input type="hidden" name="page" value="produtos">
        
        <div>
            <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem;">Pesquisar:</label>
            <input type="text" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>" placeholder="Marca, modelo, descrição..." style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 0.95rem;">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem;">Categoria:</label>
            <select name="filtro_categoria" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 0.95rem;">
                <option value="">📂 Todas</option>
                <?php foreach ($categorias_disponiveis as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $filtro_categoria === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem;">Marca:</label>
            <select name="filtro_marca" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 0.95rem;">
                <option value="">🏷️ Todas</option>
                <?php foreach ($marcas_disponiveis as $marca): ?>
                    <option value="<?= htmlspecialchars($marca) ?>" <?= $filtro_marca === $marca ? 'selected' : '' ?>><?= htmlspecialchars($marca) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 8px; justify-content: flex-end; padding-bottom: 2px;">
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.9rem;">
                <input type="checkbox" name="filtro_destaque" value="1" <?= $filtro_destaque ? 'checked' : '' ?> style="cursor: pointer;">
                <span>⭐ Destaque</span>
            </label>
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.9rem;">
                <input type="checkbox" name="filtro_novidade" value="1" <?= $filtro_novidade ? 'checked' : '' ?> style="cursor: pointer;">
                <span>✨ Novidade</span>
            </label>
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.9rem;">
                <input type="checkbox" name="filtro_promocao" value="1" <?= $filtro_promocao ? 'checked' : '' ?> style="cursor: pointer;">
                <span>💸 Promoção</span>
            </label>
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.9rem;">
                <input type="checkbox" name="filtro_stock_baixo" value="1" <?= $filtro_stock_baixo ? 'checked' : '' ?> style="cursor: pointer;">
                <span>⚠️ Stock &lt; 10</span>
            </label>
        </div>
        
        <div style="display: flex; gap: 10px; align-items: flex-end;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 10px; font-weight: 600;">🔍 Filtrar</button>
            <a href="?page=produtos" class="btn" style="flex: 1; text-align: center; padding: 10px; background: var(--secondary-bg); font-weight: 600;">🔄 Limpar</a>
        </div>
    </form>
</div>

<!-- Cabeçalho -->
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <h2 class="section-title" style="margin: 0; display: flex; align-items: center; gap: 10px;">
        <span>📦</span>
        <span>Produtos</span>
        <span style="background: var(--accent); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;"><?= count($produtos) ?></span>
    </h2>
    <a href="?page=produtos&action=add" class="btn btn-success" style="padding: 10px 20px; font-weight: 600;">➕ Adicionar Produto</a>
</div>

<?php if ($action === 'add' || $edit_product): ?>
<!-- Formulário Add/Edit -->
<div style="background: var(--card-bg); padding: 30px; border-radius: 12px; margin-bottom: 25px; border: 2px solid var(--accent); box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <h3 style="margin: 0 0 25px 0; display: flex; align-items: center; gap: 10px;">
        <?= $edit_product ? '<span>✏️</span><span>Editar Produto</span>' : '<span>➕</span><span>Adicionar Novo Produto</span>' ?>
    </h3>
    <form method="POST" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
        <input type="hidden" name="save_product" value="1">
        <input type="hidden" name="id" value="<?= $edit_product['id'] ?? 0 ?>">
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem;">Marca: <span style="color: var(--danger);">*</span></label>
            <input type="text" name="marca" value="<?= htmlspecialchars($edit_product['marca'] ?? '') ?>" required style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 1rem;" placeholder="Ex: Samsung, Apple...">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem;">Modelo: <span style="color: var(--danger);">*</span></label>
            <input type="text" name="modelo" value="<?= htmlspecialchars($edit_product['modelo'] ?? '') ?>" required style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 1rem;" placeholder="Ex: Galaxy S21, iPhone 13...">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem;">Categoria: <span style="color: var(--danger);">*</span></label>
            <select name="categoria" required style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 1rem;">
                <option value="">Selecione...</option>
                <?php foreach ($categorias_disponiveis as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= ($edit_product['categoria'] ?? '') === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem;">Preço (€): <span style="color: var(--danger);">*</span></label>
            <input type="number" name="preco" value="<?= $edit_product['preco'] ?? '' ?>" step="0.01" min="0" required style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 1rem;" placeholder="999.99">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem;">Preço Original (€):</label>
            <input type="number" name="preco_antigo" value="<?= $edit_product['preco_antigo'] ?? '' ?>" step="0.01" min="0" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 1rem;" placeholder="1299.99">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem;">Stock: <span style="color: var(--danger);">*</span></label>
            <input type="number" name="stock" value="<?= $edit_product['stock'] ?? 100 ?>" min="0" required style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 1rem;" placeholder="100">
        </div>
        
        <div style="grid-column: 1 / -1;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem;">URL da Imagem:</label>
            <input type="url" name="imagem" value="<?= htmlspecialchars($edit_product['imagem'] ?? '') ?>" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 1rem;" placeholder="https://exemplo.com/imagem.jpg">
        </div>
        
        <div style="grid-column: 1 / -1;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem;">Descrição:</label>
            <textarea name="descricao" rows="4" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; background: var(--secondary-bg); color: var(--text); font-size: 1rem; resize: vertical;" placeholder="Descrição detalhada do produto..."><?= htmlspecialchars($edit_product['descricao'] ?? '') ?></textarea>
        </div>
        
        <div style="grid-column: 1 / -1; display: flex; gap: 25px; padding: 15px; background: var(--secondary-bg); border-radius: 8px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 1rem;">
                <input type="checkbox" name="destaque" <?= ($edit_product['destaque'] ?? 0) ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
                <span>⭐ Produto em Destaque</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 1rem;">
                <input type="checkbox" name="novidade" <?= ($edit_product['novidade'] ?? 0) ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
                <span>✨ Novidade</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 1rem;">
                <input type="checkbox" name="promocao" <?= ($edit_product['promocao'] ?? 0) ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
                <span>💸 Em Promoção</span>
            </label>
        </div>
        
        <div style="grid-column: 1 / -1; display: flex; gap: 15px; margin-top: 10px;">
            <button type="submit" class="btn btn-success" style="padding: 12px 30px; font-size: 1rem; font-weight: 600;">💾 Guardar Produto</button>
            <a href="?page=produtos" class="btn" style="padding: 12px 30px; font-size: 1rem; font-weight: 600; background: var(--secondary-bg);">❌ Cancelar</a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Tabela de Produtos -->
<?php if (empty($produtos)): ?>
    <div class="alert alert-warning" style="padding: 20px; text-align: center; font-size: 1.1rem;">
        <strong>⚠️ Nenhum produto encontrado</strong><br>
        <span style="color: var(--text-muted);">Ajuste os filtros ou adicione novos produtos</span>
    </div>
<?php else: ?>
<div style="overflow-x: auto; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead style="background: var(--card-bg); position: sticky; top: 0;">
            <tr>
                <th style="padding: 15px; text-align: left; font-weight: 700; width: 80px;">ID</th>
                <th style="padding: 15px; text-align: left; font-weight: 700;">Produto</th>
                <th style="padding: 15px; text-align: left; font-weight: 700; width: 150px;">Categoria</th>
                <th style="padding: 15px; text-align: center; font-weight: 700; width: 120px;">Preço</th>
                <th style="padding: 15px; text-align: center; font-weight: 700; width: 100px;">Stock</th>
                <th style="padding: 15px; text-align: center; font-weight: 700; width: 120px;">Status</th>
                <th style="padding: 15px; text-align: center; font-weight: 700; width: 220px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p): 
                $stock_baixo = ($p['stock'] ?? 0) < 10;
                $sem_stock = ($p['stock'] ?? 0) <= 0;
            ?>
            <tr style="border-bottom: 1px solid var(--border); <?= $stock_baixo ? 'background: rgba(255,193,7,0.05);' : '' ?>">
                <td style="padding: 15px; font-weight: 700; color: var(--text-muted);">#<?= $p['id'] ?></td>
                <td style="padding: 15px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <?php if ($p['imagem']): ?>
                            <img src="<?= htmlspecialchars($p['imagem']) ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 2px solid var(--border);">
                        <?php else: ?>
                            <div style="width: 60px; height: 60px; background: var(--secondary-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">📦</div>
                        <?php endif; ?>
                        <div>
                            <strong style="display: block; margin-bottom: 4px; font-size: 1rem;"><?= htmlspecialchars($p['marca'] . ' ' . $p['modelo']) ?></strong>
                            <span style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($p['slug'] ?? '') ?></span>
                        </div>
                    </div>
                </td>
                <td style="padding: 15px;">
                    <span style="background: var(--accent); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; display: inline-block;"><?= htmlspecialchars($p['categoria']) ?></span>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <form method="POST" style="display: inline-flex; align-items: center; gap: 5px;">
                        <input type="hidden" name="quick_update" value="1">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="stock" value="<?= $p['stock'] ?? 0 ?>">
                        <input type="hidden" name="destaque" value="<?= $p['destaque'] ?? 0 ?>">
                        <input type="hidden" name="novidade" value="<?= $p['novidade'] ?? 0 ?>">
                        <input type="hidden" name="promocao" value="<?= $p['promocao'] ?? 0 ?>">
                        <input type="number" name="preco" value="<?= number_format($p['preco'], 2, '.', '') ?>" step="0.01" style="width: 85px; padding: 6px; border: 2px solid var(--border); border-radius: 6px; background: var(--secondary-bg); color: var(--text); text-align: center; font-weight: 600;">
                        <button type="submit" class="btn btn-sm btn-success" title="Salvar Preço" style="padding: 6px 10px;">💾</button>
                    </form>
                    <?php if (($p['preco_antigo'] ?? 0) && ($p['preco_antigo'] ?? 0) > $p['preco']): ?>
                        <div style="margin-top: 4px; font-size: 0.8rem; color: var(--text-muted); text-decoration: line-through;">€<?= number_format($p['preco_antigo'], 2, ',', '.') ?></div>
                    <?php endif; ?>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <form method="POST" style="display: inline-flex; align-items: center; gap: 5px; flex-direction: column;">
                        <input type="hidden" name="quick_update" value="1">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="preco" value="<?= $p['preco'] ?>">
                        <input type="hidden" name="destaque" value="<?= $p['destaque'] ?? 0 ?>">
                        <input type="hidden" name="novidade" value="<?= $p['novidade'] ?? 0 ?>">
                        <input type="hidden" name="promocao" value="<?= $p['promocao'] ?? 0 ?>">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <input type="number" name="stock" value="<?= $p['stock'] ?? 0 ?>" style="width: 60px; padding: 6px; border: 2px solid <?= $sem_stock ? 'var(--danger)' : ($stock_baixo ? 'var(--warning)' : 'var(--border)') ?>; border-radius: 6px; background: var(--secondary-bg); color: var(--text); text-align: center; font-weight: 600;">
                            <button type="submit" class="btn btn-sm btn-success" title="Salvar Stock" style="padding: 6px 10px;">💾</button>
                        </div>
                        <?php if ($sem_stock): ?>
                            <span style="font-size: 0.75rem; color: var(--danger); font-weight: 700;">❌ SEM STOCK</span>
                        <?php elseif ($stock_baixo): ?>
                            <span style="font-size: 0.75rem; color: var(--warning); font-weight: 700;">⚠️ BAIXO</span>
                        <?php endif; ?>
                    </form>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <form method="POST">
                        <input type="hidden" name="quick_update" value="1">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="preco" value="<?= $p['preco'] ?>">
                        <input type="hidden" name="stock" value="<?= $p['stock'] ?? 0 ?>">
                        <div style="display: flex; flex-direction: column; gap: 6px; align-items: center;">
                            <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 0.9rem; padding: 4px 8px; border-radius: 6px; background: var(--secondary-bg); width: 100%;">
                                <input type="checkbox" name="destaque" <?= ($p['destaque'] ?? 0) ? 'checked' : '' ?> onchange="this.form.submit()" style="cursor: pointer;">
                                <span>⭐ Destaque</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 0.9rem; padding: 4px 8px; border-radius: 6px; background: var(--secondary-bg); width: 100%;">
                                <input type="checkbox" name="novidade" <?= ($p['novidade'] ?? 0) ? 'checked' : '' ?> onchange="this.form.submit()" style="cursor: pointer;">
                                <span>✨ Novidade</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 0.9rem; padding: 4px 8px; border-radius: 6px; background: var(--secondary-bg); width: 100%;">
                                <input type="checkbox" name="promocao" <?= ($p['promocao'] ?? 0) ? 'checked' : '' ?> onchange="this.form.submit()" style="cursor: pointer;">
                                <span>💸 Promoção</span>
                            </label>
                        </div>
                    </form>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                        <a href="?page=produtos&action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-primary" title="Editar Produto" style="padding: 8px 12px; font-weight: 600;">✏️ Editar</a>
                        <a href="?page=produtos&delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('⚠️ Tem certeza que deseja remover este produto?\n\n<?= htmlspecialchars($p['marca'] . ' ' . $p['modelo']) ?>')" title="Remover Produto" style="padding: 8px 12px; font-weight: 600;">🗑️ Apagar</a>
                        <a href="../produto.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm" title="Ver no Site" style="padding: 8px 12px; background: var(--secondary-bg); font-weight: 600;">👁️ Ver</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
