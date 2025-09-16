<?php
header('Content-Type: application/json');
require_once '../config/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['idUsuario'], $data['tiempoTrabajo'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$idUsuario = $data['idUsuario'];
$tiempoTrabajo = $data['tiempoTrabajo'];
$fechaEnvio = date('Y-m-d');

try {
    $pdo->beginTransaction();

    $stmtComp = $pdo->prepare("INSERT INTO Comprobante (IDComp, FechaEnvio) VALUES (NULL, ?)");
    $stmtComp->execute([$fechaEnvio]);
    $idComp = $pdo->lastInsertId();

    $stmtTrabajo = $pdo->prepare("INSERT INTO ComprobanteTrabajo (IDComp, TiempoTrabajo) VALUES (?, ?)");
    $stmtTrabajo->execute([$idComp, $tiempoTrabajo]);

    $stmtEnviar = $pdo->prepare("INSERT INTO EnviarComprobanteTrabajo (IDComp, IDUsuario, TiempoTrabajo) VALUES (?, ?, ?)");
    $stmtEnviar->execute([$idComp, $idUsuario, $tiempoTrabajo]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Work hours registered', 'idComp' => $idComp]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
}
?>