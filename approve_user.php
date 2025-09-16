<?php
session_start();
require_once '../config/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$idAdmin = $_SESSION['admin_id'];

if (!$id) {
    die('Missing ID');
}

try {
    $stmt = $pdo->prepare("UPDATE Usuario SET EstadoCuenta = TRUE WHERE IDUsuario = ?");
    $stmt->execute([$id]);

    $stmtAct = $pdo->prepare("INSERT INTO Actualizacion (IDUsuario, IDAdmin, EstadoCuenta) VALUES (?, ?, TRUE)");
    $stmtAct->execute([$id, $idAdmin]);

    header('Location: users.php');
} catch (PDOException $e) {
    die('Approval failed: ' . $e->getMessage());
}
?>