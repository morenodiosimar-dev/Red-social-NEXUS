<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/conn.php';
// $conn inicializada en conn.php

if ($conn->connect_error) {
    echo json_encode(['error' => 'No se pudo conectar a la DB']);
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['seguidores' => 0, 'seguidos' => 0]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Contar seguidores
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$seguidores = $stmt->get_result()->fetch_assoc()['total'];

// Contar seguidos
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$seguidos = $stmt->get_result()->fetch_assoc()['total'];

echo json_encode([
    'seguidores' => $seguidores,
    'seguidos' => $seguidos
]);
