<?php
session_start();
header('Content-Type: application/json');

// DATOS DE RAILWAY (Copia y pega esto)
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "BpFRhFTLghAcqTRozKXkQyajMlYVqZCw";
$dbname = "railway"; // <-- En Railway tu BD se llama así
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

$usuario_id = $_SESSION['usuario_id'] ?? null;
$post_id = $_POST['post_id'] ?? null;

if (!$usuario_id || !$post_id) {
    echo json_encode(["success" => false, "error" => "Sesión o ID faltante"]);
    exit;
}

// Verificar si ya lo republicaste para no duplicar
$check = $conn->query("SELECT id FROM republicaciones WHERE usuario_id = $usuario_id AND publicacion_id = $post_id");
if($check->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Ya has compartido esta publicación"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO republicaciones (usuario_id, publicacion_id) VALUES (?, ?)");
$stmt->bind_param("ii", $usuario_id, $post_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}
?>