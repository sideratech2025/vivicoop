<?php
session_start();
require_once '../config/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$idComp = $_GET['id'] ?? null;
$type = $_GET['type'] ?? null;
$idAdmin = $_SESSION['admin_id'];

if (!$idComp || !$type) {
    die('Missing parameters');
}

try {
    $stmt = $pdo->prepare("UPDATE Comprobante SET EstadoComprobante = TRUE WHERE IDComp = ?");
    $stmt->execute([$idComp]);

    switch ($type) {
        case 'inicial':
            $stmtVer = $pdo->prepare("INSERT INTO VerificarEnvioInicial (IDComp, IDAdmin) VALUES (?, ?)");
            break;
        case 'mensual':
            $stmtVer = $pdo->prepare("INSERT INTO VerificarEnvioMensual (IDComp, IDAdmin) VALUES (?, ?)");
            break;
        case 'trabajo':
            $stmtVer = $pdo->prepare("INSERT INTO VerificarEnvioTrabajo (IDComp, IDAdmin) VALUES (?, ?)");
            break;
        default:
            die('Invalid type');
    }
    $stmtVer->execute([$idComp, $idAdmin]);

    header('Location: comprobantes.php');
} catch (PDOException $e) {
    die('Approval failed: ' . $e->getMessage());
}
?>