<?php
session_start();

// DATOS DE RAILWAY (Copia y pega esto)
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "VDCVPmVJHDnmzZVPddmvGjriJbQdVHiU";
$dbname = "railway"; // <-- En Railway tu BD se llama así
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

$usuario_id = $_SESSION['usuario_id'];

if (isset($_FILES['foto'])) {
    $file = $_FILES['foto'];
    $tipo = $file['type'];
    
    if (strpos($tipo, 'image/') === 0) { // Validar que solo sean FOTOS
        $ruta = 'uploads/perfil_' . $usuario_id . time() . '.jpg';
        if (move_uploaded_file($file['tmp_name'], $ruta)) {
$conn->query("UPDATE usuarios SET foto_perfil = '$ruta' WHERE id = $usuario_id");
$_SESSION['foto_perfil'] = $ruta; // <-- AGREGA ESTA LÍNEA
echo json_encode(["success" => true, "ruta" => $ruta]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Solo se permiten imágenes"]);
    }
}