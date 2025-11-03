<?php
// Incluído por admin/index.php. Usa $orders, $orders_file

// Preferir uso da BD quando existir a tabela `encomendas`.
if (!isset($mysqli) || !$mysqli) $mysqli = db_connect();
$use_db = false;
try {
  $chk = $mysqli->query("SHOW TABLES LIKE 'encomendas'");
  if ($chk && $chk->num_rows > 0) $use_db = true;
} catch (Throwable $e) { $use_db = false; }

if ($use_db) {
  // Atualizar estado via BD
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = intval($_POST['id'] ?? 0);
    $new_status = $_POST['status'] ?? 'pendente';

    // Buscar estado atual
    $stmt = $mysqli->prepare('SELECT status FROM encomendas WHERE id = ? LIMIT 1');
    if ($stmt) {
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $res = $stmt->get_result();
      $row = $res->fetch_assoc();
      $old_status = $row['status'] ?? null;
      $stmt->close();
    } else { $old_status = null; }

    // Atualizar
    $u = $mysqli->prepare('UPDATE encomendas SET status = ? WHERE id = ? LIMIT 1');
    if ($u) { $u->bind_param('si', $new_status, $id); $u->execute(); $u->close(); }

    // Se passou para 'entregue' e antes não era 'entregue', decrementar stock
    if ($new_status === 'entregue' && $old_status !== 'entregue') {
      // Buscar itens da encomenda
      $it = $mysqli->prepare('SELECT produto_id, qty FROM encomenda_itens WHERE encomenda_id = ?');
      if ($it) {
        $it->bind_param('i', $id);
        $it->execute();
        $res = $it->get_result();
        while ($row = $res->fetch_assoc()) {
          $pid = intval($row['produto_id']);
          $qty = intval($row['qty']);
          // Subtrair stock (não menos que zero)
          $update = $mysqli->prepare('UPDATE produtos SET stock = GREATEST(stock - ?, 0) WHERE id = ?');
          if ($update) { $update->bind_param('ii', $qty, $pid); $update->execute(); $update->close(); }
        }
        $it->close();
      }
    }

    echo '<div class="alert alert-success">📦 Estado atualizado</div>';
  }

  // Ler encomendas da BD
  $orders = [];
  $res = $mysqli->query('SELECT e.*, u.nome as user_name FROM encomendas e LEFT JOIN users u ON e.user_id = u.id ORDER BY e.created_at DESC');
  if ($res) {
    while ($r = $res->fetch_assoc()) {
      $r['user'] = $r['user_name'] ?? 'Cliente';
      $orders[] = $r;
    }
  }

} else {
  // Legacy JSON flow
  // Atualizar estado
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? 'pendente';
    foreach ($orders as &$o) {
      if (($o['id'] ?? '') === $id) { $o['status'] = $status; break; }
    }
    unset($o);
    $to_save = ['orders' => $orders];
    file_put_contents($orders_file, json_encode($to_save, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    echo '<div class="alert alert-success">📦 Estado atualizado</div>';
  }

  $orders_data = json_decode(@file_get_contents($orders_file), true) ?: [];
  $orders = $orders_data['orders'] ?? [];
}
?>
<h2 class="section-title">🛒 Pedidos</h2>
<?php if (!count($orders)): ?>
  <p>Sem pedidos ainda.</p>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Cliente</th>
        <th>Total</th>
        <th>Status</th>
        <th>Data</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><?= htmlspecialchars($o['id'] ?? '') ?></td>
          <td><?= htmlspecialchars($o['user'] ?? 'Anónimo') ?></td>
          <td>€<?= number_format($o['total'] ?? 0, 2, ',', '.') ?></td>
          <td><span class="badge badge-warning"><?= htmlspecialchars($o['status'] ?? 'pendente') ?></span></td>
          <td><?= htmlspecialchars($o['date'] ?? '') ?></td>
          <td>
            <form method="POST" style="display:flex; gap:8px; align-items:center;">
              <input type="hidden" name="update_status" value="1">
              <input type="hidden" name="id" value="<?= htmlspecialchars($o['id'] ?? '') ?>">
              <select name="status">
                <?php foreach (['pendente','pago','enviado','cancelado'] as $s): ?>
                  <option value="<?= $s ?>" <?= (($o['status'] ?? '')===$s)?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-success btn-sm" type="submit">Guardar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
