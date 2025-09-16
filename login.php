<?php
header('Content-Type: application/json');
require_once '../config/config.php'; 
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['mail'], $data['contrasena'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$mail = $data['mail'];
$contrasena = $data['contrasena'];

try {
    $stmt = $pdo->prepare("SELECT * FROM Usuario WHERE Mail = ? AND Contraseña = ?");
    $stmt->execute([$mail, $contrasena]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    if (!$user['EstadoCuenta']) {
        echo json_encode(['success' => false, 'message' => 'Account not approved']);
        exit;
    }

    echo json_encode(['success' => true, 'user' => ['id' => $user['IDUsuario'], 'nombre' => $user['Nombre']]]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Login failed: ' . $e->getMessage()]);
}
?>