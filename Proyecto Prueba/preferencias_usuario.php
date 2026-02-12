<?php
session_start();
header('Content-Type: application/json; charset=utf-8');


// DATOS DE RAILWAY (Copia y pega esto)
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "VDCVPmVJHDnmzZVPddmvGjriJbQdVHiU";
$dbname = "railway"; // <-- En Railway tu BD se llama así
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

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