<?php
session_start();
// Obtener IDUsuario desde la sesión (establecido en login.php)
$idusuario = $_SESSION['idusuario'] ?? null;
$nombre = $_SESSION['nombre'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home - SideraTech</title>
        <link rel="stylesheet" href="stylesHome.css">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="topnav">
        <div class="brand">SideraTech</div>
        <div style="display:flex;gap:8px;align-items:center">
            <a href="perfil.php" class="action-btn" aria-label="Perfil">+</a>
            <a href="logout.php" class="action-btn" aria-label="Cerrar sesión">Salir</a>
        </div>
    </nav>

    <main>
        <div class="panel">
            <h2>Panel de control</h2>
            <?php if ($idusuario): ?>
                <p>Bienvenido, <?php echo htmlspecialchars($nombre ?? 'Usuario'); ?></p>
            <?php else: ?>
                <p>No has iniciado sesión.</p>
            <?php endif; ?>
            <div class="buttons">
                <a href="create_comprobante.html"><button class="oval-btn primary">Envío de comprobantes</button></a>
            </div>
        </div>
    </main>

</body>
</html>
