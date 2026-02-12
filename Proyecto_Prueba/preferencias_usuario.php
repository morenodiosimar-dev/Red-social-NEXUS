<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conn.php';
// $conn inicializada en conn.php
$u_id = $_SESSION['usuario_id'] ?? 0;

// Recibimos 'personal' o 'contenido' (en minúsculas como en tu perfil.php)
$tab = $_GET['tipo'] ?? 'personal';

// Usamos la columna 'tipo_perfil' que es la que usas en tu perfil.php
$sql = "SELECT id, ruta_archivo, fecha_creacion AS fecha, tipo_archivo 
        FROM publicaciones 
        WHERE usuario_id = $u_id 
        AND tipo_perfil = '$tab'
        ORDER BY id DESC";

$res = $conn->query($sql);
$data = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode(["status" => "success", "data" => $data]);