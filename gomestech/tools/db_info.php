<?php
require_once __DIR__ . '/../config.php';
$mysqli = db_connect();
$tables = [];
$res = $mysqli->query('SHOW TABLES');
while ($r = $res->fetch_row()) {
    $tables[] = $r[0];
}
echo "Tables:\n";
foreach ($tables as $t) echo " - $t\n";

foreach (['users','usuarios'] as $table) {
    echo "\nColumns in $table:\n";
    $res = $mysqli->query("SHOW COLUMNS FROM $table");
    if (!$res) { echo "  (table not found)\n"; continue; }
    while ($c = $res->fetch_assoc()) {
        echo "  {$c['Field']} ({$c['Type']})\n";
    }
}
$mysqli->close();
