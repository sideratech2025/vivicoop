<?php
header('Content-Type: application/json');
require_once '../config/config.php'; 
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nombre'], $data['mail'], $data['contrasena'], $data['ci'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$nombre = $data['nombre'];
$mail = $data['mail'];
$contrasena = $data['contrasena'];
$ci = $data['ci'];
$tipo = 'Vecino';

try {
    $stmt = $pdo->prepare("INSERT INTO Usuario (IDUsuario, Nombre, Mail, Contraseña, CI, TIPO) VALUES (NULL, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $mail, $contrasena, $ci, $tipo]);
    $idUsuario = $pdo->lastInsertId();
    $stmtVecino = $pdo->prepare("INSERT INTO Vecino (IDUsuario) VALUES (?)");
    $stmtVecino->execute([$idUsuario]);
    echo json_encode(['success' => true, 'message' => 'User registered, awaiting approval']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
}
?>