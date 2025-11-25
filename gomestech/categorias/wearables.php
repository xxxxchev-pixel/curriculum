<?php
// Redirecionamento para catálogo genérico
$categoria = 'Wearables';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
