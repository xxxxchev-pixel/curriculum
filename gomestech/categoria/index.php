<?php
// Front controller para rota /categoria/:slug
session_start();
$slug = $_GET['slug'] ?? '';
// Também suporta PATH_INFO quando configurado
if(!$slug && !empty($_SERVER['PATH_INFO'])){
  $slug = trim($_SERVER['PATH_INFO'],'/');
}
$slug = preg_replace('/[^a-z0-9_-]/i','', $slug);

// Preservar query string relevante
$allowed = ['marca','min','max','sort','pagina','q'];
$params = [];
foreach($allowed as $k){ if(isset($_GET[$k])) $params[$k] = $_GET[$k]; }
$params['categoria'] = $slug;

$query = http_build_query($params);
$target = "../categorias/catalogo.php" . ($query ? ("?".$query) : '');
// Redirecionar (mantém semântica da URL pública)
header("Location: $target");
exit;
