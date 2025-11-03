<?php
session_start();
require_once __DIR__ . '/../config.php';

logout_user();

header('Location: /index.php');
exit;
?>
