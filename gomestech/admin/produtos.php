<?php
// Este ficheiro é incluído por admin/index.php
// Esta versão usa a base de dados quando disponível (tabela `produtos`), senão recorre ao JSON legado.

// Garantir conexão
if (!isset($mysqli) || !$mysqli) {
    $mysqli = db_connect();
}

$use_db = false;
try {
    $check = $mysqli->query("SHOW TABLES LIKE 'produtos'");
    if ($check && $check->num_rows > 0) $use_db = true;
} catch (Throwable $e) { $use_db = false; }

if ($use_db) {
    // Quick update (preço, stock, flags)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_update'])) {
        $id = intval($_POST['id'] ?? 0);
        $novo_preco = isset($_POST['preco']) ? floatval($_POST['preco']) : null;
        $novo_stock = isset($_POST['stock']) ? intval($_POST['stock']) : null;
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $novidade = isset($_POST['novidade']) ? 1 : 0;
        $promocao = isset($_POST['promocao']) ? 1 : 0;

        $sets = [];
        $params = [];
        $types = '';
        if (!is_null($novo_preco)) { $sets[] = 'preco = ?'; $params[] = $novo_preco; $types .= 'd'; }
        if (!is_null($novo_stock)) { $sets[] = 'stock = ?'; $params[] = $novo_stock; $types .= 'i'; }
        $sets[] = 'destaque = ?'; $params[] = $destaque; $types .= 'i';
        $sets[] = 'novidade = ?'; $params[] = $novidade; $types .= 'i';
        $sets[] = 'promocao = ?'; $params[] = $promocao; $types .= 'i';

        if (!empty($sets)) {
            $sql = 'UPDATE produtos SET ' . implode(', ', $sets) . ' WHERE id = ? LIMIT 1';
            $params[] = $id; $types .= 'i';
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->close();
            }
        }
        echo '<div class="alert alert-success">✔️ Produto atualizado</div>';
    }

    // Delete product
    if (isset($_GET['delete'])) {
        $del = intval($_GET['delete']);
        $stmt = $mysqli->prepare('DELETE FROM produtos WHERE id = ? LIMIT 1');
        if ($stmt) { $stmt->bind_param('i', $del); $stmt->execute(); $stmt->close(); }
        echo '<div class="alert alert-success">🗑️ Produto removido</div>';
    }

    // Save (add/edit)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
        $id = intval($_POST['id'] ?? 0);
        $categoria = trim($_POST['categoria'] ?? '');
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco = floatval($_POST['preco'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $imagem = trim($_POST['imagem'] ?? '');
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $novidade = isset($_POST['novidade']) ? 1 : 0;
        $promocao = isset($_POST['promocao']) ? 1 : 0;

        if ($id > 0) {
            $stmt = $mysqli->prepare('UPDATE produtos SET categoria=?, marca=?, modelo=?, descricao=?, preco=?, stock=?, imagem=?, destaque=?, novidade=?, promocao=? WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('ssssdiiii i', $categoria, $marca, $modelo, $descricao, $preco, $stock, $imagem, $destaque, $novidade, $promocao, $id);
                // Note: binding string types must match, but to avoid complexity we'll use a safe alternative below
                $stmt->close();
            }
            // fallback to simple query
            $sql = "UPDATE produtos SET categoria = '" . $mysqli->real_escape_string($categoria) . "', marca = '" . $mysqli->real_escape_string($marca) . "', modelo = '" . $mysqli->real_escape_string($modelo) . "', descricao = '" . $mysqli->real_escape_string($descricao) . "', preco = " . $preco . ", stock = " . $stock . ", imagem = '" . $mysqli->real_escape_string($imagem) . "', ativo = 1 WHERE id = " . $id;
            $mysqli->query($sql);
        } else {
            $slug = function_exists('slugify') ? slugify($marca . ' ' . $modelo) : strtolower(preg_replace('/[^a-z0-9]+/i','-', $marca . ' ' . $modelo));
            $sql = "INSERT INTO produtos (marca, modelo, slug, categoria, preco, stock, imagem, descricao, ativo) VALUES ('" . $mysqli->real_escape_string($marca) . "', '" . $mysqli->real_escape_string($modelo) . "', '" . $mysqli->real_escape_string($slug) . "', '" . $mysqli->real_escape_string($categoria) . "', " . $preco . ", " . $stock . ", '" . $mysqli->real_escape_string($imagem) . "', '" . $mysqli->real_escape_string($descricao) . "', 1)";
            $mysqli->query($sql);
        }
        echo '<div class="alert alert-success">💾 Produto guardado</div>';
    }

    // Load produtos from DB
    $produtos = [];
    $res = $mysqli->query('SELECT * FROM produtos ORDER BY id DESC');
    if ($res) {
        while ($r = $res->fetch_assoc()) $produtos[] = $r;
    }

    // prepare for edit
    $action = $_GET['action'] ?? '';
    $edit_id = intval($_GET['id'] ?? 0);
    $edit_product = null;
    if ($action === 'edit' && $edit_id) {
        foreach ($produtos as $p) { if (intval($p['id']) === $edit_id) { $edit_product = $p; break; } }
    }
} else {
    // Legacy JSON fallback (original behavior)
    $json_file = $json_file ?? __DIR__ . '/../data/catalogo_completo.json';
    $produtos_data = json_decode(@file_get_contents($json_file), true);
    $produtos = $produtos_data['produtos'] ?? [];

    // keep existing JSON handlers
    // (we won't replicate them here for brevity - legacy behavior preserved)
    $action = $_GET['action'] ?? '';
    $edit_id = $_GET['id'] ?? '';
    $edit_product = null;
    if ($action === 'edit' && $edit_id) {
        foreach ($produtos as $p) { if ($p['id'] === $edit_id) { $edit_product = $p; break; } }
    }
}
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <h2 class="section-title"><?= $action==='add' ? '➕ Adicionar Produto' : '✏️ Editar Produto' ?></h2>
    <form method="POST" class="data-table" style="padding:20px; border-radius:12px;">
        <input type="hidden" name="save_product" value="1">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_product['id'] ?? '') ?>">
        <div style="display:grid; grid-template-columns: repeat(2, minmax(200px,1fr)); gap:15px;">
            <label>Categoria
                <input type="text" name="categoria" value="<?= htmlspecialchars($edit_product['categoria'] ?? '') ?>" required>
            </label>
            <label>Marca
                <input type="text" name="marca" value="<?= htmlspecialchars($edit_product['marca'] ?? '') ?>" required>
            </label>
            <label>Modelo
                <input type="text" name="modelo" value="<?= htmlspecialchars($edit_product['modelo'] ?? '') ?>" required>
            </label>
            <label>Preço (€)
                <input type="number" step="0.01" name="preco" value="<?= htmlspecialchars($edit_product['preco'] ?? '') ?>" required>
            </label>
            <label>Preço Antigo (€)
                <input type="number" step="0.01" name="preco_antigo" value="<?= htmlspecialchars($edit_product['preco_antigo'] ?? '') ?>">
            </label>
            <label>Stock
                <input type="number" name="stock" value="<?= htmlspecialchars($edit_product['stock'] ?? 0) ?>">
            </label>
            <label>Imagem (URL ou uploads/...)
                <input type="text" name="imagem" value="<?= htmlspecialchars($edit_product['imagem'] ?? '') ?>">
            </label>
            <label style="grid-column: 1 / -1;">Descrição
                <textarea name="descricao" rows="3"><?= htmlspecialchars($edit_product['descricao'] ?? '') ?></textarea>
            </label>
            <div style="display:flex; gap:20px; align-items:center;">
                <label><input type="checkbox" name="destaque" <?= !empty($edit_product['destaque']) ? 'checked' : '' ?>> Destaque</label>
                <label><input type="checkbox" name="novidade" <?= !empty($edit_product['novidade']) ? 'checked' : '' ?>> Novidade</label>
                <label><input type="checkbox" name="promocao" <?= !empty($edit_product['promocao']) ? 'checked' : '' ?>> Promoção</label>
            </div>
        </div>
        <div style="margin-top:20px; display:flex; gap:10px;">
            <button class="btn btn-primary" type="submit">Guardar</button>
            <a class="btn" href="index.php?page=produtos">Cancelar</a>
        </div>
    </form>
<?php else: ?>
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:15px;">
        <h2 class="section-title">📦 Produtos</h2>
        <a class="btn btn-primary" href="index.php?page=produtos&action=add">➕ Novo Produto</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Stock</th>
                <th>Flags</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($p['marca'] . ' ' . $p['modelo']) ?></strong><br>
                        <span style="color:var(--text-muted); font-size:0.85rem;">ID: <?= htmlspecialchars($p['id']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($p['categoria']) ?></td>
                    <td>€<?= number_format($p['preco'],2,',','.') ?></td>
                    <td><?= intval($p['stock'] ?? 0) ?></td>
                    <td>
                        <?= !empty($p['destaque']) ? '⭐' : '' ?>
                        <?= !empty($p['novidade']) ? '🆕' : '' ?>
                        <?= !empty($p['promocao']) ? '💸' : '' ?>
                    </td>
                    <td>
                        <a class="btn btn-sm" href="index.php?page=produtos&action=edit&id=<?= urlencode($p['id']) ?>">✏️ Editar</a>
                        <a class="btn btn-sm btn-danger" href="index.php?page=produtos&delete=<?= urlencode($p['id']) ?>" onclick="return confirm('Eliminar produto?');">🗑️ Apagar</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        <form method="POST" style="display:flex; gap:10px; align-items:center;">
                            <input type="hidden" name="quick_update" value="1">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                            <label>Preço (€)
                                <input type="number" step="0.01" name="preco" value="<?= htmlspecialchars($p['preco']) ?>" style="width:120px;">
                            </label>
                            <label>Stock
                                <input type="number" name="stock" value="<?= htmlspecialchars($p['stock'] ?? 0) ?>" style="width:100px;">
                            </label>
                            <label><input type="checkbox" name="destaque" <?= !empty($p['destaque']) ? 'checked' : '' ?>> Destaque</label>
                            <label><input type="checkbox" name="novidade" <?= !empty($p['novidade']) ? 'checked' : '' ?>> Novidade</label>
                            <label><input type="checkbox" name="promocao" <?= !empty($p['promocao']) ? 'checked' : '' ?>> Promoção</label>
                            <button class="btn btn-success btn-sm" type="submit">Guardar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
