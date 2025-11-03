<?php
require_once __DIR__ . '/../config.php';
$mysqli = db_connect();
$cols = [];
$res = $mysqli->query("SHOW COLUMNS FROM users");
while ($r = $res->fetch_assoc()) { $cols[] = $r['Field']; }

$to_add = [
    'telefone' => "VARCHAR(20) DEFAULT NULL",
    'morada' => "TEXT DEFAULT NULL",
    'nif' => "VARCHAR(9) DEFAULT NULL",
    'codigo_postal' => "VARCHAR(20) DEFAULT NULL"
];

$failed = [];
foreach ($to_add as $col => $type) {
    if (!in_array($col, $cols)) {
        $sql = "ALTER TABLE users ADD COLUMN $col $type";
        if (!$mysqli->query($sql)) {
            $failed[$col] = $mysqli->error;
        }
    }
}

if (empty($failed)) {
    echo "Columns added or already exist.\n";
} else {
    echo "Some alters failed:\n";
    print_r($failed);
}
$mysqli->close();
