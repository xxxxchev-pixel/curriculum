<?php
// Redirecionamento para catálogo genérico
$categoria = 'Eletrodomésticos';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
