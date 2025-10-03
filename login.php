<?php
session_start();
$erro = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utilizador = $_POST["utilizador"];
    $password = $_POST["password"];
    // Utilizador e password definidos no código
    if ($utilizador == "aluno" && $password == "12345") {
        $_SESSION["autenticado"] = true;
        header("Location: area_reservada.php");
        exit();
    } else {
        $erro = "Utilizador ou palavra-passe incorretos.";
    }
}
?>