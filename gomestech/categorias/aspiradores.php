<?php
// Redirecionamento para catálogo genérico
$categoria = 'Aspiradores';
header('Location: catalogo.php?categoria=' . urlencode($categoria));
exit;
