<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

session_start();

if (isset($_SESSION['nombre'])) {
    // Unimos el nombre y apellido de la sesión
    $nombre_completo = trim($_SESSION['nombre'] . " " . ($_SESSION['apellido'] ?? ''));
    
    // Obtenemos el ID del usuario de la sesión (asumiendo que está en $_SESSION['id'])
    $id_usuario = $_SESSION['id'] ?? null;
    
    // Si no está en la sesión, lo buscamos en la base de datos
    if (!$id_usuario) {
        $conn = new mysqli("127.0.0.1", "root", "", "nexus_db");
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