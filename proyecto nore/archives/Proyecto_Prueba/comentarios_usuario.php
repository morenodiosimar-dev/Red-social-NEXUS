<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Caracas'); 

ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    $conn = new mysqli("127.0.0.1", "root", "", "nexus_db",3306);
    if($conn->connect_error) throw new Exception("Error de conexión");

    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    if ($usuario_id == 0) throw new Exception("Sesión no iniciada");

    $tipo = $_GET['tipo'] ?? 'mis';
    $inicio = $_GET['inicio'] ?? '';
    $fin = $_GET['fin'] ?? '';

    if ($tipo === 'mis') {
        // CAMBIADO: c.contenido en lugar de c.comentario
        $sql = "SELECT c.id, c.contenido, c.fecha, p.ruta_archivo, 
                       u.nombre AS nombre_objetivo, u.apellido AS apellido_objetivo
                FROM comentarios c
                JOIN publicaciones p ON c.publicacion_id = p.id
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE c.usuario_id = $usuario_id";
    } else {
        // CAMBIADO: c.contenido en lugar de c.comentario
        $sql = "SELECT c.id, c.contenido, c.fecha, p.ruta_archivo, 
                       u.nombre AS nombre_usuario, u.apellido AS apellido_usuario
                FROM comentarios c
                JOIN publicaciones p ON c.publicacion_id = p.id
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE p.usuario_id = $usuario_id";
    }

    if(!empty($inicio) && !empty($fin)) {
        $sql .= " AND c.fecha BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
    }

    $sql .= " ORDER BY c.fecha DESC";
    $res = $conn->query($sql);

    $data = [];
    if($res) {
        while($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode(["status" => "success", "data" => $data]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}