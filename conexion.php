<?php
$host = "localhost";
$db = "ProyectoFinal";
$usuario = "root";
$contraseña = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $usuario, $contraseña);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al intentar conectar a la base de datos: " . $e->getMessage());
}
?>
