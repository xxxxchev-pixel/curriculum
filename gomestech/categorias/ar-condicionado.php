<?php
// Redirecionamento para catálogo genérico
$categoria = 'Ar Condicionado';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
