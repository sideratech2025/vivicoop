<?php
require 'conexion.php';

// Recibir datos por POST
$mail = trim($_POST['mail'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$mail || !$password) {
    die('Mail y contraseña requeridos.');
}

// Buscar usuario
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE Mail = ?');
$stmt->execute([$mail]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['Contraseña'])) {
    echo 'Inicio de sesión correcto. IDUsuario: ' . $user['IDUsuario'];
} else {
    echo 'Credenciales incorrectas.';
}
?>
