<?php
// Redirecionamento para catálogo genérico
$categoria = 'Máquinas de Café';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
