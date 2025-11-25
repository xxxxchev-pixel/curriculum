<?php
// Redirecionamento para catálogo genérico
$categoria = 'Frigoríficos';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
