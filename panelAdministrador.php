<?php
session_start();
if (!($_SESSION['is_admin'] ?? false)) {
    header('Location: loginAdministrador.php');
    exit;
}

require 'conexion.php';

// Traer usuarios con EstadoCuenta=FALSE
$stmt = $pdo->query('SELECT IDUsuario, Nombre, Mail, CI FROM usuarios WHERE EstadoCuenta = FALSE');
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>Usuarios pendientes de aprobación</h2>
<?php if (!$usuarios): ?>
  <p>No hay usuarios pendientes.</p>
<?php else: ?>
  <table border="1" cellpadding="5">
    <tr>
      <th>ID</th><th>Nombre</th><th>Mail</th><th>CI</th><th>Acción</th>
    </tr>
    <?php foreach ($usuarios as $u): ?>
    <tr>
      <td><?= htmlspecialchars($u['IDUsuario']) ?></td>
      <td><?= htmlspecialchars($u['Nombre']) ?></td>
      <td><?= htmlspecialchars($u['Mail']) ?></td>
      <td><?= htmlspecialchars($u['CI']) ?></td>
      <td>
        <form action="approve_user.php" method="POST" style="display:inline;">
          <input type="hidden" name="id" value="<?= $u['IDUsuario'] ?>">
          <button type="submit">Aprobar</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>
<a href="admin_logout.php">Cerrar sesión</a>
