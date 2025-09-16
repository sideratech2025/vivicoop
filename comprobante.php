<?php
header('Content-Type: application/json');
require_once '../config/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['idUsuario'], $data['tipo'])) {
    echo json_encode(['error' => 'Missing required fields: idUsuario and tipo']);
    exit;
}

$idUsuario = $data['idUsuario'];
$tipo = $data['tipo'];
$fechaEnvio = date('Y-m-d');

try {
    $pdo->beginTransaction();

    $stmtComp = $pdo->prepare("INSERT INTO Comprobante (IDComp, FechaEnvio) VALUES (NULL, ?)");
    $stmtComp->execute([$fechaEnvio]);
    $idComp = $pdo->lastInsertId();

    switch ($tipo) {
        case 'inicial':
            if (!isset($data['montoInicial'])) {
                throw new Exception('Missing montoInicial for inicial type');
            }
            $montoInicial = $data['montoInicial'];
            $stmtInicial = $pdo->prepare("INSERT INTO ComprobanteInicial (IDComp, MontoInicial) VALUES (?, ?)");
            $stmtInicial->execute([$idComp, $montoInicial]);
            $stmtEnviar = $pdo->prepare("INSERT INTO EnviarComprobanteInicial (IDComp, IDUsuario, MontoInicial) VALUES (?, ?, ?)");
            $stmtEnviar->execute([$idComp, $idUsuario, $montoInicial]);
            break;

        case 'mensual':
            if (!isset($data['montoMensual'])) {
                throw new Exception('Missing montoMensual for mensual type');
            }
            $montoMensual = $data['montoMensual'];
            $stmtMensual = $pdo->prepare("INSERT INTO ComprobanteMensual (IDComp, MontoMensual) VALUES (?, ?)");
            $stmtMensual->execute([$idComp, $montoMensual]);
            $stmtEnviar = $pdo->prepare("INSERT INTO EnviarComprobanteMensual (IDComp, IDUsuario, MontoMensual) VALUES (?, ?, ?)");
            $stmtEnviar->execute([$idComp, $idUsuario, $montoMensual]);
            break;

        case 'trabajo':
            if (!isset($data['tiempoTrabajo'])) {
                throw new Exception('Missing tiempoTrabajo for trabajo type');
            }
            $tiempoTrabajo = $data['tiempoTrabajo'];
            $stmtTrabajo = $pdo->prepare("INSERT INTO ComprobanteTrabajo (IDComp, TiempoTrabajo) VALUES (?, ?)");
            $stmtTrabajo->execute([$idComp, $tiempoTrabajo]);
            $stmtEnviar = $pdo->prepare("INSERT INTO EnviarComprobanteTrabajo (IDComp, IDUsuario, TiempoTrabajo) VALUES (?, ?, ?)");
            $stmtEnviar->execute([$idComp, $idUsuario, $tiempoTrabajo]);
            break;

        default:
            throw new Exception('Invalid comprobante type');
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => ucfirst($tipo) . ' receipt registered', 'idComp' => $idComp]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
}
?>