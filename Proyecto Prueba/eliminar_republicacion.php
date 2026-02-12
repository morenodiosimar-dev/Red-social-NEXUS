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

$usuario_id = $_SESSION['usuario_id'] ?? null;
$repost_id = $_POST['id'] ?? null; // ID de la tabla republicaciones

if (!$usuario_id || !$repost_id) {
    echo json_encode(["success" => false, "error" => "Sesión inválida"]);
    exit;
}

// Solo borrar si la republicación pertenece al usuario en sesión
$stmt = $conn->prepare("DELETE FROM republicaciones WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $repost_id, $usuario_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}
?>