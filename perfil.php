<?php
session_start();
require_once 'config.php';

// Determinar idusuario: preferir sesión
$idusuario = $_SESSION['idusuario'] ?? $_GET['id'] ?? null;

if (!$idusuario) {
    // No hay id; redirigir al login
    header('Location: InicioSesion.html');
    exit;
}

// Manejar envío de formulario para actualizar Mail y Nombre (no CI)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mailPost = $_POST['mail'] ?? '';
    $nombrePost = $_POST['nombre'] ?? '';

    // Validaciones básicas
    if (!filter_var($mailPost, FILTER_VALIDATE_EMAIL) || empty(trim($nombrePost))) {
        // Redirigir con error
        header('Location: perfil.php?error=1');
        exit;
    }

    try {
        $up = $pdo->prepare('UPDATE Usuario SET Mail = ?, Nombre = ? WHERE IDUsuario = ?');
        $up->execute([$mailPost, $nombrePost, $idusuario]);
        header('Location: perfil.php?updated=1');
        exit;
    } catch (PDOException $e) {
        // En caso de error, seguimos y mostraremos mensaje
        $updateError = 'Error al actualizar: ' . $e->getMessage();
    }
}

// Obtener datos del usuario (actualizados si hubo POST)
try {
    $stmt = $pdo->prepare('SELECT Mail, Nombre, CI FROM Usuario WHERE IDUsuario = ? LIMIT 1');
    $stmt->execute([$idusuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $user = null;
}

$mailVal = $user['Mail'] ?? '';
$nombreVal = $user['Nombre'] ?? '';
$ciVal = $user['CI'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Perfil - SideraTech</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="stylesPerfil.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    </head>
    <body>
        <nav class="perfil-nav">
            <div class="nav-left">
                <a href="home.php"><button class="back">← Atrás</button></a>
            </div>
            <div class="nav-title">Mi perfil</div>
            <div class="nav-right"></div>
        </nav>

        <main class="perfil-main">
            <div class="card perfil-card" role="region" aria-label="Perfil de usuario">
                <div class="avatar-wrap">
                    <div class="avatar">
                        <img src="person-fill.svg" alt="Avatar del usuario" />
                    </div>
                </div>

                <h2 class="card-title">Datos de la cuenta</h2>
                <p class="card-sub">Actualiza tu información. El CI no se puede modificar desde aquí.</p>

                <form id="perfilForm" class="perfil-form" autocomplete="off" method="POST" action="perfil.php">
                    <div class="form-group">
                        <label for="mail" class="field-label">Mail</label>
                        <input id="mail" name="mail" type="email" placeholder="usuario@ejemplo.com" value="<?php echo htmlspecialchars($mailVal); ?>" />
                        <small class="hint">Usa un email válido para recuperar tu cuenta.</small>
                    </div>

                    <div class="form-group">
                        <label for="nombre" class="field-label">Nombre</label>
                        <input id="nombre" name="nombre" type="text" placeholder="Tu nombre y apellido" value="<?php echo htmlspecialchars($nombreVal); ?>" />
                    </div>

                    <div class="form-group">
                        <label for="ci" class="field-label">CI</label>
                        <input id="ci" name="ci" type="text" readonly class="readonly" placeholder="12345678" value="<?php echo htmlspecialchars($ciVal); ?>" />
                        <small class="hint">Para cambiar el CI contacta al administrador.</small>
                    </div>

                            <div class="actions">
                                <button id="guardarBtn" type="submit" class="btn-guardar">Guardar cambios</button>
                            </div>

                    <div id="toast" class="toast">Guardado correctamente</div>
                </form>
            </div>
        </main>

        <footer class="perfil-footer">Proyecto escolar del grupo SideraTech</footer>

        <script>
            // Client-side validation then submit the form to server
            document.addEventListener('DOMContentLoaded', ()=>{
                const form = document.getElementById('perfilForm');
                const guardarBtn = document.getElementById('guardarBtn');
                const toast = document.getElementById('toast');

                form.addEventListener('submit', (e)=>{
                    const mail = document.getElementById('mail').value.trim();
                    const nombre = document.getElementById('nombre').value.trim();
                    if(!mail || !nombre){
                        e.preventDefault();
                        alert('Completa Mail y Nombre antes de guardar.');
                        return;
                    }
                    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if(!emailRe.test(mail)){
                        e.preventDefault();
                        alert('Ingresa un email válido');
                        return;
                    }
                    // Allow the form to submit normally; server will redirect back with ?updated=1
                    guardarBtn.disabled = true;
                    guardarBtn.textContent = 'Guardando...';
                });

                // Show success toast if redirected after update
                const params = new URLSearchParams(window.location.search);
                if (params.get('updated')) {
                    toast.classList.add('visible');
                    setTimeout(()=> toast.classList.remove('visible'), 1800);
                }
                if (params.get('error')) {
                    alert('Error: datos inválidos o actualización fallida.');
                }
            });
        </script>
    </body>
</html>
