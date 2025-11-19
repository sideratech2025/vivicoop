<?php
session_start();
require_once 'config.php';


if (empty($_POST['mail']) || empty($_POST['contrasena'])) {
    header('Location: sesionadmin.html');
    exit;
}

$mail = $_POST['mail'];
$contrasena = $_POST['contrasena'];


try {
    $stmt = $pdo->prepare('SELECT IDUsuario, Nombre, TIPO, Contraseña FROM Usuario WHERE Mail = ? LIMIT 1');
    $stmt->execute([$mail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error en la base de datos';
    exit;
}

if (!$user) {
    echo 'los datos no son correctos';
    exit;
}

if ($contrasena !== $user['Contraseña']) {
    echo 'los datos no son correctos';
    exit;
}

if (strtolower($user['TIPO']) !== 'admin') {
    echo 'el usuario no es administrador';
    exit;
}

$_SESSION['idusuario'] = $user['IDUsuario'];
$_SESSION['nombre'] = $user['Nombre'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['nombre']; ?></h1>
    <p><a href="user.php">Panel de Usuarios (Verificar Registros)</a></p>
    <p><a href="comprobante.php">Panel de Comprobantes (Aceptar Pagos)</a></p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>