<?php
session_start();
$mail = $_POST['mail'];
$contrasena = $_POST['contrasena'];
require_once 'config.php'; 


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: InicioSesion.html');
    exit;
}

if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    $msg = urlencode('Email inválido');
    header("Location: InicioSesion.html?error=1&message={$msg}");
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT IDUsuario, Nombre, Contraseña, EstadoCuenta FROM Usuario WHERE Mail = ? LIMIT 1');
    $stmt->execute([$mail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $msg = urlencode('Credenciales inválidas');
        header("Location: InicioSesion.html?error=1&message={$msg}");
        exit;
    }

    // Verificar estado de la cuenta
    if (isset($user['EstadoCuenta']) && !$user['EstadoCuenta']) {
        $msg = urlencode('Cuenta no aprobada');
        header("Location: InicioSesion.html?error=1&message={$msg}");
        exit;
    }

    $stored = $user['Contraseña'];
    $ok = false;

    // Preferir password_verify (hash), si no coincide, permitir comparación directa como fallback (inseguro)
    if (password_verify($contrasena, $stored)) {
        $ok = true;
    } elseif (hash_equals($stored, $contrasena)) {
        // WARNING: esto asume que en la BD hay contraseñas en texto plano (mala práctica)
        $ok = true;
    }

    if (!$ok) {
        $msg = urlencode('Credenciales inválidas');
        header("Location: InicioSesion.html?error=1&message={$msg}");
        exit;
    }

    // Login exitoso: iniciar sesión y redirigir al home
    // Guardar ID y nombre en sesión con keys compatibles
    $_SESSION['user_id'] = $user['IDUsuario'];
    $_SESSION['user_nombre'] = $user['Nombre'];
    // Claves usadas en otras partes del proyecto:
    $_SESSION['idusuario'] = $user['IDUsuario'];
    $_SESSION['nombre'] = $user['Nombre'];

    header('Location: home.php');
    exit;

} catch (PDOException $e) {
    // En producción: registrar error y mostrar mensaje genérico
    error_log('Login error: ' . $e->getMessage());
    $msg = urlencode('Error del servidor');
    header("Location: InicioSesion.html?error=1&message={$msg}");
    exit;
}
?>