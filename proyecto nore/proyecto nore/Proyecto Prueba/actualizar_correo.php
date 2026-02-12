<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$correo = $_POST['correo'] ?? '';

if (empty($correo)) {
    echo json_encode(["status" => "error", "message" => "Correo vacío"]);
    exit;
}

// VALIDACIÓN BACKEND (SEGURIDAD)
if (!preg_match('/^[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com)$/', $correo)) {
    echo json_encode([
        "status" => "error",
        "message" => "Solo se permiten correos Gmail o Hotmail"
    ]);
    exit;
}

$conn = new mysqli("127.0.0.1", "root", "", "nexus_db", 3306);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

// Verificar si el correo ya existe
$check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$check->bind_param("s", $correo);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Este correo ya está en uso"
    ]);
    exit;
}
$check->close();

// Actualizar correo
$stmt = $conn->prepare("UPDATE usuarios SET correo = ? WHERE id = ?");
$stmt->bind_param("si", $correo, $usuario_id);

if ($stmt->execute()) {
    $_SESSION['correo'] = $correo; // actualizar sesión
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "No se pudo actualizar"]);
}

$stmt->close();
$conn->close();
