<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conn.php';
// $conn inicializada en conn.php

$usuario_id = $_SESSION['usuario_id'];
$post_id = $_POST['post_id'];
$accion = $_POST['accion'];

if ($accion == 'reaccionar') {
    $check = $conn->query("SELECT id FROM reacciones WHERE publicacion_id = $post_id AND usuario_id = $usuario_id");
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM reacciones WHERE publicacion_id = $post_id AND usuario_id = $usuario_id");
        $status = 'removed';
    } else {
        $conn->query("INSERT INTO reacciones (publicacion_id, usuario_id) VALUES ($post_id, $usuario_id)");
        $status = 'added';
        //Codigo de notificacion
        $res_owner = $conn->query("SELECT usuario_id FROM publicaciones WHERE id = $post_id");
        $owner = $res_owner->fetch_assoc();
        // Solo notificar si no es mi propia publicación
        if ($owner['usuario_id'] != $usuario_id) {
            $destinatario = $owner['usuario_id'];
            $conn->query("INSERT INTO notificaciones (usuario_id, emisor_id, publicacion_id, tipo) 
                          VALUES ($destinatario, $usuario_id, $post_id, 'reaccion')");
        }
    }
    $total = $conn->query("SELECT COUNT(*) as total FROM reacciones WHERE publicacion_id = $post_id")->fetch_assoc();
    echo json_encode(['status' => $status, 'total' => $total['total']]);
}

if ($accion == 'comentar') {
    $contenido = $_POST['contenido'];
    $stmt = $conn->prepare("INSERT INTO comentarios (publicacion_id, usuario_id, contenido) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $usuario_id, $contenido);

    if ($stmt->execute()) {
        // CAPTURAR EL ID RECIÉN GENERADO
        $nuevo_id = $stmt->insert_id;

        $nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';

        // Notificación al dueño del post
        $res_owner = $conn->query("SELECT usuario_id FROM publicaciones WHERE id = $post_id");
        $owner = $res_owner->fetch_assoc();
        if ($owner['usuario_id'] != $usuario_id) {
            $destinatario = $owner['usuario_id'];
            $conn->query("INSERT INTO notificaciones (usuario_id, emisor_id, publicacion_id, tipo) 
                          VALUES ($destinatario, $usuario_id, $post_id, 'comentario')");
        }

        // DEVOLVER EL ID AL JAVASCRIPT
        echo json_encode([
            "status" => "success",
            "id" => $nuevo_id, // <--- ESTO ES LO QUE FALTABA
            "nombre" => $nombre_usuario,
            "texto" => $contenido
        ]);
    } else {
        echo json_encode(["status" => "error", "error" => $conn->error]);
    }
    exit;
}