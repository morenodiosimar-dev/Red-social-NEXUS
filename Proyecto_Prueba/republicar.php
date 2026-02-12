<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conn.php';
// $conn inicializada en conn.php

$usuario_id = $_SESSION['usuario_id'] ?? null;
$post_id = $_POST['post_id'] ?? null;

if (!$usuario_id || !$post_id) {
    echo json_encode(["success" => false, "error" => "Sesión o ID faltante"]);
    exit;
}

// Verificar si ya lo republicaste para no duplicar
$check = $conn->query("SELECT id FROM republicaciones WHERE usuario_id = $usuario_id AND publicacion_id = $post_id");
if ($check->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Ya has compartido esta publicación"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO republicaciones (usuario_id, publicacion_id) VALUES (?, ?)");
$stmt->bind_param("ii", $usuario_id, $post_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}
?>