<?php
require_once __DIR__ . '/config.php';
header('Content-Type: text/html; charset=utf-8');

$errors = [];$info = [];$counts = [];
try {
    $db = db_connect();
    $info[] = 'Ligação MySQL OK (' . DB_HOST . ')';

    // Base de dados e tabelas
    $tables = ['categories','brands','produtos','orders','order_items'];
    foreach ($tables as $t) {
        $res = $db->query("SHOW TABLES LIKE '".$db->real_escape_string($t)."'");
        if ($res && $res->num_rows) {
            $cntRes = $db->query("SELECT COUNT(*) AS c FROM `{$t}`");
            $row = $cntRes->fetch_assoc();
            $counts[$t] = (int)$row['c'];
        } else {
            $errors[] = "Tabela em falta: {$t}";
        }
    }
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8" />
<title>Diagnóstico - GomesTech</title>
<style>
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial,sans-serif;background:#0f1115;color:#e8e8e8;padding:24px}
.card{background:#151923;border:1px solid #23283a;border-radius:12px;padding:24px;max-width:900px;margin:0 auto}
h1{margin:0 0 12px}
.ok{color:#1bd760}.bad{color:#ff7272}.muted{color:#a0a4b1}
.list{margin:0;padding-left:18px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-top:16px}
.item{background:#0f1320;border:1px solid #22273a;border-radius:8px;padding:12px}
a.btn{display:inline-block;background:#FF6A00;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;margin-right:8px}
</style>
</head>
<body>
<div class="card">
  <h1>Diagnóstico do Sistema</h1>
  <p class="muted">Verifica a ligação à base de dados e contagens principais.</p>

  <h3>Estado</h3>
  <ul class="list">
    <?php foreach($info as $i): ?><li class="ok">✅ <?php echo htmlspecialchars($i); ?></li><?php endforeach; ?>
    <?php foreach($errors as $e): ?><li class="bad">❌ <?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
  </ul>

  <div class="grid">
    <div class="item">Categorias: <strong class="ok"><?php echo $counts['categories'] ?? 0; ?></strong></div>
    <div class="item">Marcas: <strong class="ok"><?php echo $counts['brands'] ?? 0; ?></strong></div>
    <div class="item">Produtos: <strong class="ok"><?php echo $counts['produtos'] ?? 0; ?></strong></div>
    <div class="item">Encomendas: <strong><?php echo $counts['orders'] ?? 0; ?></strong></div>
  </div>

  <p style="margin-top:18px">
    <a class="btn" href="/gomestech/database/importar_catalogo_json.php">Reimportar Catálogo</a>
    <a class="btn" href="/gomestech/categorias/catalogo.php">Abrir Catálogo</a>
  </p>
</div>
</body>
</html>
