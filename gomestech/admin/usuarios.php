<?php
// Incluído por admin/index.php. Usa $users, $users_file

// Atualizar role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'user';
    foreach ($users as &$u) {
        if (($u['email'] ?? '') === $email) { $u['role'] = $role; break; }
    }
    unset($u);
    $to_save = ['users' => $users];
    file_put_contents($users_file, json_encode($to_save, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    echo '<div class="alert alert-success">👤 Role atualizado</div>';
}

// Reset de password (para 'changeme123')
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_pw'])) {
    $email = $_POST['email'] ?? '';
    foreach ($users as &$u) {
        if (($u['email'] ?? '') === $email) { 
            $u['password'] = password_hash('changeme123', PASSWORD_DEFAULT);
            $u['must_change_password'] = true;
            break; 
        }
    }
    unset($u);
    $to_save = ['users' => $users];
    file_put_contents($users_file, json_encode($to_save, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    echo '<div class="alert alert-success">🔑 Password reposta para "changeme123"</div>';
}

// Remover utilizador
if (isset($_GET['delete'])) {
    $email = $_GET['delete'];
    $users = array_values(array_filter($users, fn($u) => ($u['email'] ?? '') !== $email));
    $to_save = ['users' => $users];
    file_put_contents($users_file, json_encode($to_save, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    echo '<div class="alert alert-success">🗑️ Utilizador removido</div>';
}

$users_data = json_decode(@file_get_contents($users_file), true) ?: [];
$users = $users_data['users'] ?? [];
?>

<h2 class="section-title">👥 Utilizadores</h2>
<?php if (!count($users)): ?>
  <p>Sem utilizadores registados.</p>
<?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>Nome</th>
        <th>Email</th>
        <th>Role</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['nome'] ?? '') ?></td>
          <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
          <td><?= htmlspecialchars($u['role'] ?? 'user') ?></td>
          <td>
            <form method="POST" style="display:inline-flex; gap:8px; align-items:center;">
              <input type="hidden" name="update_role" value="1">
              <input type="hidden" name="email" value="<?= htmlspecialchars($u['email'] ?? '') ?>">
              <select name="role">
                <?php foreach (['user','admin'] as $r): ?>
                  <option value="<?= $r ?>" <?= (($u['role'] ?? 'user')===$r)?'selected':'' ?>><?= ucfirst($r) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-success btn-sm" type="submit">Guardar</button>
            </form>
            <form method="POST" style="display:inline-block; margin-left:8px;">
              <input type="hidden" name="reset_pw" value="1">
              <input type="hidden" name="email" value="<?= htmlspecialchars($u['email'] ?? '') ?>">
              <button class="btn btn-sm" type="submit">🔑 Reset PW</button>
            </form>
            <a class="btn btn-danger btn-sm" href="index.php?page=usuarios&delete=<?= urlencode($u['email'] ?? '') ?>" onclick="return confirm('Remover utilizador?');">🗑️ Apagar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
