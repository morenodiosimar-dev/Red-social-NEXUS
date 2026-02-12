
<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

// Aquí sigue tu código de session_start() y validación...

$conn = new mysqli("127.0.0.1", "root", "", "nexus_db");

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