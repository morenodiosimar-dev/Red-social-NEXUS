<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión solo si no hay una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'conn.php';

// CAPTURAR DATOS 
$nombre     = $_POST['nombre'] ?? '';
$apellido   = $_POST['apellido'] ?? '';
$fechaN     = $_POST['FechaN'] ?? '';
$sexo       = $_POST['sexo'] ?? '';
$correo     = $_POST['correo'] ?? '';
$telefono   = $_POST['telefono'] ?? '';
$contrasena = $_POST['contrasena'] ?? ''; 

// VALIDACIONES BÁSICAS
if (empty($nombre) || empty($correo) || empty($contrasena)) {
    echo "Error: Datos obligatorios faltantes.";
    exit;
}

// VALIDACIÓN DE EDAD MÍNIMA
if (!empty($fechaN)) {
    $fechaNacimiento = new DateTime($fechaN);
    $hoy = new DateTime();
    $edad = $hoy->diff($fechaNacimiento)->y;
    
    if ($edad < 12) {
        echo "Error: Debes tener al menos 12 años para registrarte.";
        exit;
    }
}

// VERIFICAR SI EL CORREO YA EXISTE
$stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$stmt_check->bind_param("s", $correo);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    echo "Error: El correo electrónico ya está registrado.";
    exit;
}
$stmt_check->close();

// Encriptar contraseña
$passHash = password_hash($contrasena, PASSWORD_DEFAULT);

// Preparar la inserción
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, fechaN, sexo, correo, telefono, contraseña) VALUES (?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}
$stmt->bind_param("sssssss", $nombre, $apellido, $fechaN, $sexo, $correo, $telefono, $passHash);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    
    // Debug: verificar el ID
    error_log("Registro exitoso - ID generado: " . $user_id);
    
    // Guardar datos en sesión para que el usuario quede logueado
    $_SESSION['usuario_id'] = $user_id;
    $_SESSION['nombre'] = $nombre;
    $_SESSION['apellido'] = $apellido;
    $_SESSION['correo'] = $correo;
    
    error_log("Sesión iniciada para usuario ID: " . $user_id);
    
    // Redirigir directamente a seleccionar_interes.php
    header("Location: seleccionar_interes.php?user_id=" . $user_id);
    exit();
} else {
    $error = "Error al registrar: " . $stmt->error;
    error_log("Error en registro: " . $error);
    echo $error;
}

$stmt->close();
$conn->close();
?>