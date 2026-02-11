<?php
// 1. CONFIGURACIÓN DE CABECERAS (Sin duplicados)
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

// 2. CONEXIÓN DINÁMICA (RAILWAY)
$host = getenv('MYSQLHOST') ?: "127.0.0.1";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "";
$db   = getenv('MYSQLDATABASE') ?: "railway";
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida"]));
}

// 3. CONSULTA
$res = $conn->query("SELECT id, nombre, apellido, correo, foto_perfil FROM usuarios");

$usuarios = [];
if ($res) {
    while($row = $res->fetch_assoc()) { 
        $row['nombre_completo'] = trim($row['nombre'] . " " . $row['apellido']);
        if (empty($row['foto_perfil'])) {
            $row['foto_perfil'] = 'default.png';
        }
        $usuarios[] = $row; 
    }
}

echo json_encode($usuarios);
?>