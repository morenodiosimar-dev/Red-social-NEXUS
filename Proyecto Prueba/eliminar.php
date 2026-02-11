<?php
session_start();
header('Content-Type: application/json');

// DATOS DE RAILWAY (Copia y pega esto)
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "VDCVPmVJHDnmzZVPddmvGjriJbQdVHiU";
$dbname = "railway"; // <-- En Railway tu BD se llama así
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error || !isset($_SESSION['usuario_id'])) {
    echo json_encode(["success" => false, "error" => "No autorizado"]);
    exit;
}

$id = intval($_POST['id']);
$tipo = $_POST['tipo'];
$user_id = $_SESSION['usuario_id'];

if ($tipo === 'comentario') {
    // El dueño del comentario o el dueño del post pueden borrar
    $stmt = $conn->prepare("DELETE c FROM comentarios c 
                            LEFT JOIN publicaciones p ON c.publicacion_id = p.id 
                            WHERE c.id = ? AND (c.usuario_id = ? OR p.usuario_id = ?)");
    $stmt->bind_param("iii", $id, $user_id, $user_id);
} else {
    $stmt = $conn->prepare("DELETE FROM publicaciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
}

$res = $stmt->execute();
echo json_encode(["success" => $res]);