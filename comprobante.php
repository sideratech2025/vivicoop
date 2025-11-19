<?php
require_once 'config.php';

// Si se envía un formulario de aprobación (desde la lista), actualizar EstadoComprobante a 1
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    $approveId = intval($_POST['approve_id']);
    try {
        $up = $pdo->prepare('UPDATE Comprobante SET EstadoComprobante = 1 WHERE IDComp = ?');
        $up->execute([$approveId]);
        header('Location: comprobante.php?approved=1');
        exit;
    } catch (PDOException $e) {
        $error = 'Update failed: ' . $e->getMessage();
    }
}

// Si es GET, mostrar la lista de comprobantes cuyo EstadoComprobante = 0
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Leer directamente de la tabla Comprobante los campos solicitados
        $sql = "SELECT IDComp, FechaEnvio, COALESCE(EstadoComprobante,0) AS EstadoComprobante, tipo, monto, IDUsuario
                FROM Comprobante
                WHERE COALESCE(EstadoComprobante,0) = 0
                ORDER BY FechaEnvio DESC, IDComp DESC";
        $stmt = $pdo->query($sql);
        $comprobantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Error fetching comprobantes: ' . $e->getMessage();
        $comprobantes = [];
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Comprobantes pendientes</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
        <style>body{padding:20px}</style>
    </head>
    <body>
        <div class="container">
            <h1>Comprobantes pendientes</h1>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['approved'])): ?>
                <div class="alert alert-success">Comprobante aprobado correctamente.</div>
            <?php endif; ?>

            <?php if (empty($comprobantes)): ?>
                <p>No hay comprobantes pendientes.</p>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>FechaEnvío</th>
                            <th>EstadoComprobante</th>
                            <th>Tipo</th>
                            <th>Usuario</th>
                            <th>Monto</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($comprobantes as $c): ?>
                        <?php
                        // Obtener nombre de usuario si existe
                        $nombreUsuario = '';
                        if (!empty($c['IDUsuario'])) {
                            try {
                                $su = $pdo->prepare('SELECT Nombre FROM Usuario WHERE IDUsuario = ? LIMIT 1');
                                $su->execute([$c['IDUsuario']]);
                                $row = $su->fetch(PDO::FETCH_ASSOC);
                                if ($row) $nombreUsuario = $row['Nombre'];
                            } catch (PDOException $e) {
                                $nombreUsuario = '';
                            }
                        }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c['FechaEnvio']); ?></td>
                            <?php
                                $estadoRaw = $c['EstadoComprobante'] ?? null;
                                if ($estadoRaw === null) {
                                    $estadoLabel = 'desconocido';
                                } elseif ($estadoRaw == 0) {
                                    $estadoLabel = 'vigente';
                                } elseif ($estadoRaw == 1) {
                                    $estadoLabel = 'aprobado';
                                } else {
                                    $estadoLabel = htmlspecialchars($estadoRaw);
                                }
                            ?>
                            <td><?php echo $estadoLabel; ?></td>
                            <td><?php echo htmlspecialchars($c['tipo']); ?></td>
                            <td><?php echo htmlspecialchars($nombreUsuario); ?></td>
                            <td><?php echo htmlspecialchars($c['monto']); ?></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="approve_id" value="<?php echo (int)$c['IDComp']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Marcar como aprobado</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <a href="dashboard.php" class="btn btn-secondary">Volver al dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Si llega POST con JSON (API), conservar comportamiento original
header('Content-Type: application/json');
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Si no es JSON o no tiene los campos esperados, devolver error
if (!is_array($data) || !isset($data['idUsuario'], $data['tipo'])) {
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