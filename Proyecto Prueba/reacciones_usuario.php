<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    
// DATOS DE RAILWAY (Copia y pega esto)
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "VDCVPmVJHDnmzZVPddmvGjriJbQdVHiU";
$dbname = "railway"; // <-- En Railway tu BD se llama así
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

    if($conn->connect_error) {
        throw new Exception("Error de base de datos.");
    }

    // Asegúrate de usar el ID real de la sesión cuando termines las pruebas
    $usuario_id = $_SESSION['usuario_id'] ?? 9; 

    $tipo = $_GET['tipo'] ?? 'mis';
    $inicio = $_GET['inicio'] ?? '';
    $fin = $_GET['fin'] ?? '';

    if($tipo === 'mis'){
        $sql = "SELECT r.*, p.caption AS publicacion, p.ruta_archivo, 
                       u.nombre AS nombre_objetivo, u.apellido AS apellido_objetivo
                FROM reacciones r
                JOIN publicaciones p ON r.publicacion_id = p.id
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE r.usuario_id = $usuario_id";
    } else {
        $sql = "SELECT r.*, p.caption AS publicacion, p.ruta_archivo, 
                       u.nombre AS nombre_usuario, u.apellido AS apellido_usuario
                FROM reacciones r
                JOIN publicaciones p ON r.publicacion_id = p.id
                JOIN usuarios u ON r.usuario_id = u.id
                WHERE p.usuario_id = $usuario_id";
    }

    // --- CORRECCIÓN DE FECHAS ---
    // Usamos DATE(r.fecha) para comparar solo el día, ignorando la hora exacta.
    if(!empty($inicio)) {
        $sql .= " AND DATE(r.fecha) >= '$inicio'";
    }
    if(!empty($fin)) {
        $sql .= " AND DATE(r.fecha) <= '$fin'";
    }

    $sql .= " ORDER BY r.fecha DESC";

    $res = $conn->query($sql);
    
    $reacciones = [];
    if($res) {
        while($row = $res->fetch_assoc()) {
            $reacciones[] = $row;
        }
    }

    echo json_encode(["status" => "success", "data" => $reacciones]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}