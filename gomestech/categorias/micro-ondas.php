<?php
// Redirecionamento para catálogo genérico
$categoria = 'Micro-ondas';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
