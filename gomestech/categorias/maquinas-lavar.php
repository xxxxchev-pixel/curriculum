<?php
// Redirecionamento para catálogo genérico
$categoria = 'Máquinas de Lavar';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
