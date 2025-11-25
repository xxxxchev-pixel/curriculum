<?php
// Redirecionamento para nova localização
header('Location: auth/register.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
exit;
