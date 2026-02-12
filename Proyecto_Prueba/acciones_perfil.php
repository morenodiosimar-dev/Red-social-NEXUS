<?php
session_start();
require_once __DIR__ . '/conn.php';
// $conn inicializada en conn.php

$usuario_id = $_SESSION['usuario_id']; // quien da click
$seguido_id = $_POST['seguido_id'];    // quien recibe el follow

// Verificar si ya sigue
$stmt_check = $conn->prepare("SELECT id FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
$stmt_check->bind_param("ii", $usuario_id, $seguido_id);
$stmt_check->execute();
$ya_sigue = $stmt_check->get_result()->num_rows > 0;

if ($ya_sigue) {
    $stmt_del = $conn->prepare("DELETE FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
    $stmt_del->bind_param("ii", $usuario_id, $seguido_id);
    $stmt_del->execute();
    $status = 'unfollow';
} else {
    $stmt_ins = $conn->prepare("INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
    $stmt_ins->bind_param("ii", $usuario_id, $seguido_id);
    $stmt_ins->execute();
    $status = 'follow';

    // Insertar notificación
    // tipo='seguir', usuario_id=RECEPTOR(seguido_id), emisor_id=QUIEN_SIGUE(usuario_id), publicacion_id=NULL, fecha=NOW(), leido=0
    $stmt_noti = $conn->prepare("INSERT INTO notificaciones (usuario_id, emisor_id, tipo, fecha, leido) VALUES (?, ?, 'seguir', NOW(), 0)");
    $stmt_noti->bind_param("ii", $seguido_id, $usuario_id);
    $stmt_noti->execute();
}

// Contar seguidores del perfil visitado
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id = ?");
$stmt_count->bind_param("i", $seguido_id);
$stmt_count->execute();
$total_seguidores = $stmt_count->get_result()->fetch_assoc()['total'];

// Contar seguidos del perfil visitado
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id = ?");
$stmt_count->bind_param("i", $seguido_id);
$stmt_count->execute();
$total_seguidos = $stmt_count->get_result()->fetch_assoc()['total'];

echo json_encode([
    'status' => $status,
    'total_seguidores' => $total_seguidores,
    'total_seguidos' => $total_seguidos
]);
