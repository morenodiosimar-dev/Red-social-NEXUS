
<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    "http://localhost:3000",
    "http://127.0.0.1:3000",
    "https://tu-app-en-railway.up.railway.app" // Reemplaza con tu URL real de Railway después
];

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

// --- 2. CONEXIÓN DINÁMICA A BASE DE DATOS ---
$host = getenv('MYSQLHOST') ?: "127.0.0.1";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "";
$db   = getenv('MYSQLDATABASE') ?: "railway";
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);

// Seleccionamos los campos incluyendo el ID
$res = $conn->query("SELECT id, nombre, apellido, correo, foto_perfil FROM usuarios");

$usuarios = [];
while($row = $res->fetch_assoc()) { 
    // Creamos la variable 'nombre_completo' que el JS está esperando
    $row['nombre_completo'] = $row['nombre'] . " " . $row['apellido'];
    if (empty($row['foto_perfil'])) {
        $row['foto_perfil'] = 'default.png';
    }
    $usuarios[] = $row; 
    
}

// Si el array está vacío, el JSON será []
echo json_encode($usuarios);
?>