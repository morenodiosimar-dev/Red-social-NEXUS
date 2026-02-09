<?php
// --- 1. CONFIGURACIÓN DE CORS DINÁMICO ---
// Detectamos el origen de la solicitud
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Lista de URLs permitidas (Tu local y tu futura URL de Railway)
$allowed_origins = [
    "http://localhost:3000",
    "http://127.0.0.1:3000",
    "https://tu-app-en-railway.up.railway.app" // Cambia esto cuando tengas la URL de Railway
];

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

session_start();

$host = getenv('MYSQLHOST') ?: "127.0.0.1";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "";
$db   = getenv('MYSQLDATABASE') ?: "railway";
$port = getenv('MYSQLPORT') ?: "3306";

if (isset($_SESSION['nombre'])) {
    // Unimos el nombre y apellido de la sesión
    $nombre_completo = trim($_SESSION['nombre'] . " " . ($_SESSION['apellido'] ?? ''));
    
    // Obtenemos el ID del usuario de la sesión (asumiendo que está en $_SESSION['id'])
    $id_usuario = $_SESSION['id'] ?? null;
    
    // Si no está en la sesión, lo buscamos en la base de datos
    if (!$id_usuario) {
        // Usamos la configuración dinámica de arriba
        $conn = new mysqli($host, $user, $pass, $db, $port);
        if ($conn->connect_error) {
            echo json_encode(["success" => false, "error" => "Error de conexión"]);
            exit;
        }
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre = ? AND apellido = ?");
        $nombre = $_SESSION['nombre'];
        $apellido = $_SESSION['apellido'] ?? '';
        $stmt->bind_param("ss", $nombre, $apellido);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id_usuario = $row['id'];
        }
        $stmt->close();
        $conn->close();
    }
    
    echo json_encode([
        "success" => true,
        "usuario" => $nombre_completo,
        "id_usuario" => $id_usuario
    ]);
} else {
    echo json_encode(["success" => false]);
}
?>