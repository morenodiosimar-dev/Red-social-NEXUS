<?php
session_start();
header('Content-Type: application/json');
// Conexión usando tus datos

// DATOS DE RAILWAY (Copia y pega esto)
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "BpFRhFTLghAcqTRozKXkQyajMlYVqZCw";
$dbname = "railway"; // <-- En Railway tu BD se llama así
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

$usuario_id = $_SESSION['usuario_id'];

// Si el JS envía "?leer=1", marcamos como leídas
if (isset($_GET['leer'])) {
    $conn->query("UPDATE notificaciones SET leido = 1 WHERE usuario_id = $usuario_id");
}

// Consultamos las notificaciones uniendo con la tabla usuarios para saber quién hizo la acción
$query = "SELECT n.*, u.nombre, u.apellido 
          FROM notificaciones n 
          JOIN usuarios u ON n.emisor_id = u.id 
          WHERE n.usuario_id = $usuario_id 
          ORDER BY n.fecha DESC LIMIT 10";

$res = $conn->query($query);
$notis = [];
$nuevas = 0;

while($row = $res->fetch_assoc()) {
    if ($row['leido'] == 0) $nuevas++;
    $notis[] = $row;
}

echo json_encode(["lista" => $notis, "nuevas" => $nuevas]);
?>