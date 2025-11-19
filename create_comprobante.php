<?php
session_start();
require_once 'config.php';

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_comprobante.html');
    exit;
}

// Verificar que el usuario esté autenticado (se espera que la app guarde id en sesión)
if (empty($_SESSION['idusuario'])) {
    // No hay usuario activo; redirigir a inicio de sesión o devolver error
    echo "Debe iniciar sesión para crear un comprobante.";
    exit;
}

$fechaEnvio = $_POST['FechaEnvio'] ?? null;
$tipo = $_POST['tipo'] ?? null;
$monto = $_POST['monto'] ?? null;
$idUsuario = $_SESSION['idusuario'];

// Validar datos básicos
if (!$fechaEnvio || !$tipo || $monto === null) {
    echo "Faltan datos requeridos.";
    exit;
}

// Normalizar tipo: usar 'horas' o 'pago' según formulario
$estado = 0; // EstadoComprobante inicial 0

try {
    $stmt = $pdo->prepare('INSERT INTO Comprobante (FechaEnvio, EstadoComprobante, tipo, monto, IDUsuario) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$fechaEnvio, $estado, $tipo, $monto, $idUsuario]);
    // Redirigir al home después de crear el comprobante
    header('Location: home.php');
    exit;
} catch (PDOException $e) {
    echo 'Error al guardar comprobante: ' . $e->getMessage();
    exit;
}
?>