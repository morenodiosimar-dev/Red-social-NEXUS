<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conn.php';
// $conn inicializada en conn.php

$usuario_id = $_SESSION['usuario_id'] ?? null;
$repost_id = $_POST['id'] ?? null; // ID de la tabla republicaciones

if (!$usuario_id || !$repost_id) {
    echo json_encode(["success" => false, "error" => "Sesión inválida"]);
    exit;
}

// Solo borrar si la republicación pertenece al usuario en sesión
$stmt = $conn->prepare("DELETE FROM republicaciones WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $repost_id, $usuario_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}
?>