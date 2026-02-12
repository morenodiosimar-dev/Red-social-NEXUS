<?php
ob_clean(); // LIMPIA cualquier salida previa
session_start();

header('Content-Type: application/json; charset=utf-8');
error_reporting(0); // evita warnings rompiendo JSON


if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["success" => false, "message" => "Sesión no válida"]);
    exit;
}

$conn = new mysqli("127.0.0.1", "root", "", "nexus_db", 3306);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$actual = $_POST['actual'] ?? '';
$nueva = $_POST['nueva'] ?? '';

if (!$actual || !$nueva) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

// Validación BACKEND (OBLIGATORIA)
$regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/';
if (!preg_match($regex, $nueva)) {
    echo json_encode(["success" => false, "message" => "La nueva contraseña no cumple los requisitos"]);
    exit;
}

// Obtener contraseña actual
$stmt = $conn->prepare("SELECT contraseña FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
    exit;
}

$usuario = $res->fetch_assoc();

if (!password_verify($actual, $usuario['contraseña'])) {
    echo json_encode(["success" => false, "message" => "La contraseña actual es incorrecta"]);
    exit;
}

// Actualizar contraseña
$nueva_hash = password_hash($nueva, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
$update->bind_param("si", $nueva_hash, $usuario_id);

if ($update->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "No se pudo actualizar"]);
}

$update->close();
$stmt->close();
$conn->close();
