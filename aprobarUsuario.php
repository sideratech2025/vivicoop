<?php
session_start();
if (!($_SESSION['is_admin'] ?? false)) {
    header('Location: admin_login.php');
    exit;
}

require 'conexion.php';

$id = (int)($_POST['id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare('UPDATE usuarios SET EstadoCuenta = TRUE WHERE IDUsuario = ?');
    $stmt->execute([$id]);
}

// Volver al panel
header('Location: panelAdministradoor.php');
exit;
?>
