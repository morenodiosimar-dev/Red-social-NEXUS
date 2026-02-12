<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/conn.php';
// $conn ya está inicializada en conn.php.
// Eliminamos la redeclaración de variables para usar las de conn.php

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión"
    ]);
    exit;
}

$correo = $_POST['correo'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

if (empty($correo) || empty($contrasena)) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id,nombre, apellido, contraseña FROM usuarios WHERE correo = ?"
);

$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {

    $usuario = $result->fetch_assoc();

    if (password_verify($contrasena, $usuario['contraseña'])) {

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['apellido'] = $usuario['apellido'];
        $_SESSION['correo'] = $correo;

        echo json_encode([
            "success" => true,
            "nombre" => $usuario['nombre'],
            "apellido" => $usuario['apellido']
        ]);
        exit;
    }
}

echo json_encode([
    "success" => false,
    "message" => "Correo o contraseña incorrectos"
]);

$stmt->close();
$conn->close();

?>