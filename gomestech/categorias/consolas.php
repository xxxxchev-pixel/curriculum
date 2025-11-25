<?php
// Redirecionamento para catálogo genérico
$categoria = 'Consolas';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
