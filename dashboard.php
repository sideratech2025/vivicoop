<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['admin_nombre']; ?></h1>
    <p><a href="users.php">Panel de Usuarios (Verificar Registros)</a></p>
    <p><a href="comprobantes.php">Panel de Comprobantes (Aceptar Pagos)</a></p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>