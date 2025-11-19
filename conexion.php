<?php
$host = "localhost";
$usuario = "root";
$contraseña = "";
$db = "Proyectosidera";
$charset = "utf8mb4";

$conexion = mysqli_connect(·$host, $usuario, $contraseña, $db, $charset);

try {
    $pdo = new PDO($conexion);
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION;
}
catch (Exception $e) {
    die("Error al intentar conectar a la base de datos: ") . $e->getMessage()
}
?>