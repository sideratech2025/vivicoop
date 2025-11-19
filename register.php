<?php
$nombre = $_POST['nombre'];
$mail = $_POST['mail'];
$contrasena = $_POST['contrasena'];
$ci = $_POST['ci'];
$tipo = 'Vecino';
header('Content-Type: application/json');
require_once 'config.php'; 
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_POST['nombre'], $_POST['mail'], $_POST['contrasena'], $_POST['ci'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}



$sa = $pdo->prepare('SELECT IDUsuario FROM Usuario WHERE Mail = ? LIMIT 1');
$sa->execute([$mail]);
$existing = $sa->fetch(PDO::FETCH_ASSOC);
if ($existing) {
    echo json_encode(['error' => 'Email ya registrado']);
    exit;
} else {
   try {
    $stmt = $pdo->prepare("INSERT INTO Usuario ( Nombre, Mail, Contraseña, CI, TIPO) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $mail, $contrasena, $ci, $tipo]);
    $userId = $pdo->lastInsertId();
    $stmtVecino = $pdo->prepare("INSERT INTO Vecino (IDUsuario) VALUES (?)");
    $stmtVecino->execute([$userId]);
    // Registro exitoso: redirigir al index
    header('Location: index.html');
    exit;
    } catch (PDOException $e) {
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
    exit;
    }
}


?>