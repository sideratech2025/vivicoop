<?php
header('Content-Type: application/json');
require_once '../config/config.php'; 
$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'Missing user ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM Usuario WHERE IDUsuario = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Fetch failed: ' . $e->getMessage()]);
}
?>