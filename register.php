<?php
require 'conexion.php';

// Recibir datos por POST
$nombre = trim($_POST['nombre'] ?? '');
$mail = trim($_POST['mail'] ?? '');
$password = trim($_POST['password'] ?? '');
$ci = trim($_POST['ci'] ?? '');

if (!$nombre || !$mail || !$password || !$ci) {
    die('Todos los campos son requeridos.');
}

// Verificar si ya existe el correo o la CI
$stmt = $pdo->prepare('SELECT IDUsuario FROM usuarios WHERE Mail = ? OR CI = ?');
$stmt->execute([$mail, $ci]);
if ($stmt->fetch()) {
    die('El mail o la cédula ya están registrados.');
}

// Encriptar la contraseña
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insertar el nuevo usuario (EstadoCuenta se coloca por defecto en FALSE)
$stmt = $pdo->prepare('INSERT INTO usuarios (Nombre, Mail, Contraseña, CI) VALUES (?, ?, ?, ?)');
if ($stmt->execute([$nombre, $mail, $hashedPassword, $ci])) {
    echo 'Usuario registrado correctamente.';
} else {
    echo 'Error al registrar el usuario.';
}
?>

