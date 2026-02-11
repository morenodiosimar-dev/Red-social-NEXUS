<?php
// 1. CONFIGURACIÓN DE CORS LIBRE PARA PRODUCCIÓN
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// 2. INICIAR SESIÓN
session_start();

// 3. VARIABLES DE ENTORNO PARA RAILWAY
$host = getenv('MYSQLHOST') ?: "127.0.0.1";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "";
$db   = getenv('MYSQLDATABASE') ?: "railway";
$port = getenv('MYSQLPORT') ?: "3306";

// Simulamos respuesta si no hay sesión para que el JS no rompa
if (isset($_SESSION['nombre'])) {
    $nombre_completo = trim($_SESSION['nombre'] . " " . ($_SESSION['apellido'] ?? ''));
    $id_usuario = $_SESSION['id'] ?? null;
    
    echo json_encode([
        "success" => true,
        "usuario" => $nombre_completo,
        "id_usuario" => $id_usuario
    ]);
} else {
    // IMPORTANTE: Si estás usando el proxy de Node, la sesión puede no persistir.
    // Envía un error claro para debuguear.
    echo json_encode([
        "success" => false, 
        "error" => "No hay sesión activa en PHP"
    ]);
}
?>