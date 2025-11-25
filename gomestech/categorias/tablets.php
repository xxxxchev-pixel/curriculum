<?php
// Redirecionamento para catálogo genérico
$categoria = 'Tablets';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
