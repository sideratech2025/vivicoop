<?php
require_once 'config.php';
// Página para listar usuarios con EstadoCuenta = 0 y aprobarlos.

// Manejar petición de aprobación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    $approveId = intval($_POST['approve_id']);
    try {
        $up = $pdo->prepare('UPDATE Usuario SET EstadoCuenta = 1 WHERE IDUsuario = ?');
        $up->execute([$approveId]);
        // Redirigir para evitar reenvío de formulario y actualizar lista
        header('Location: user.php?approved=1');
        exit;
    } catch (PDOException $e) {
        $error = 'Update failed: ' . $e->getMessage();
    }
}

// Obtener usuarios pendientes
try {
    $stmt = $pdo->query('SELECT IDUsuario, Nombre, CI, Mail FROM Usuario WHERE EstadoCuenta = 0');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Fetch failed: ' . $e->getMessage();
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios pendientes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <style>body{padding:20px}</style>
</head>
<body>
    <div class="container">
        <h1>Usuarios pendientes de aprobación</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['approved'])): ?>
            <div class="alert alert-success">Usuario aprobado correctamente.</div>
        <?php endif; ?>

        <?php if (empty($users)): ?>
            <p>No hay usuarios pendientes.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>CI</th>
                        <th>Mail</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['Nombre']); ?></td>
                        <td><?php echo htmlspecialchars($u['CI']); ?></td>
                        <td><?php echo htmlspecialchars($u['Mail']); ?></td>
                        <td>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="approve_id" value="<?php echo (int)$u['IDUsuario']; ?>">
                                <button type="submit" class="btn btn-success btn-sm">Aprobar</button>
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
