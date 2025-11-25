<?php
// Redirecionamento para catálogo genérico
$categoria = 'TVs';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
