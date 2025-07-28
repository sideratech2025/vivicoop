<?php
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = trim($_POST['mail'] ?? '');
    $pass = trim($_POST['pass'] ?? '');

    // Buscar admin
    $stmt = $pdo->prepare('SELECT * FROM Administrador WHERE Mail = ? AND Contraseña = ?');
    $stmt->execute([$mail, $pass]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_name'] = $admin['Nombre'];
        header('Location: PanelAdministrador.php');
        exit;
    } else {
        $error = 'Credenciales incorrectas';
    }
}
?>
<h2>Login Administrador</h2>
<?php
if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
  Mail: <input type="email" name="mail"><br>
  Contraseña: <input type="password" name="pass"><br>
  <button type="submit">Ingresar</button>
</form>