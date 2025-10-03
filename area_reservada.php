/* area_reservada.php */
<?php
session_start();
if (!isset($_SESSION["autenticado"]) || $_SESSION["autenticado"] !== true) {
    header("Location: login.php");
    exit();
}
?>