<?php
// Redirecionamento para catálogo genérico
$categoria = 'Audio';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
