<?php
// Incluído por admin/index.php. Usa $users da base de dados

// Verificar se mysqli está disponível
if (!isset($mysqli) || !$mysqli) {
    $mysqli = db_connect();
}

// Atualizar role/admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    $stmt = $mysqli->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('ii', $is_admin, $user_id);
        $stmt->execute();
        $stmt->close();
        echo '<div class="alert alert-success">👤 Permissões atualizadas</div>';
        
        // Recarregar users
        $users = [];
        $users_result = $mysqli->query("SELECT * FROM users ORDER BY created_at DESC");
        if ($users_result) {
            while ($row = $users_result->fetch_assoc()) {
                $users[] = $row;
            }
        }
    }
}

// Reset de password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_pw'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $new_password = password_hash('changeme123', PASSWORD_DEFAULT);
    
    $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('si', $new_password, $user_id);
        $stmt->execute();
        $stmt->close();
        echo '<div class="alert alert-success">🔑 Password reposta para "changeme123"</div>';
    }
}

// Remover utilizador
if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ? AND id != 1");
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
        echo '<div class="alert alert-success">🗑️ Utilizador removido</div>';
        
        // Recarregar users
        $users = [];
        $users_result = $mysqli->query("SELECT * FROM users ORDER BY created_at DESC");
        if ($users_result) {
            while ($row = $users_result->fetch_assoc()) {
                $users[] = $row;
            }
        }
    }
}
?>

<h2 class="section-title">👥 Utilizadores (<?= count($users) ?>)</h2>

<?php if (!count($users)): ?>
  <p>Sem utilizadores registados.</p>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Email</th>
        <th>NIF</th>
        <th>Admin</th>
        <th>Data Registo</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><strong>#<?= $u['id'] ?></strong></td>
          <td><?= htmlspecialchars($u['nome'] ?? '') ?></td>
          <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
          <td><?= htmlspecialchars($u['nif'] ?? '-') ?></td>
          <td>
            <?php if ($u['is_admin']): ?>
              <span class="badge badge-success">✓ Admin</span>
            <?php else: ?>
              <span class="badge">Cliente</span>
            <?php endif; ?>
          </td>
          <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <form method="POST" style="display:inline-flex; gap:8px; align-items:center;">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <label style="margin:0; display:flex; align-items:center; gap:4px;">
                <input type="checkbox" name="is_admin" <?= $u['is_admin'] ? 'checked' : '' ?>>
                Admin
              </label>
              <button type="submit" name="update_role" class="btn btn-sm btn-primary">💾 Salvar</button>
            </form>
            
            <form method="POST" style="display:inline; margin-left:8px;">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button type="submit" name="reset_pw" class="btn btn-sm btn-success" onclick="return confirm('Repor password para changeme123?')">🔑 Reset PW</button>
            </form>
            
            <?php if ($u['id'] != 1): ?>
            <a href="?page=usuarios&delete_user=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remover utilizador?')" style="margin-left:8px;">🗑️</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
