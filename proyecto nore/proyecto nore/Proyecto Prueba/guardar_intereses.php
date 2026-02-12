<?php
session_start();
include 'conn.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['usuario_id'];

// Verificar si se recibieron intereses
if (!isset($_POST['intereses']) || empty($_POST['intereses'])) {
    header("Location: seleccionar_interes.php");
    exit();
}

$intereses = $_POST['intereses'];

// Validar que se seleccionaron al menos 3 intereses
if (count($intereses) < 3) {
    header("Location: seleccionar_interes.php");
    exit();
}

// Insertar los intereses del usuario
$stmt = $conn->prepare("INSERT INTO user_interests (user_id, interes_id) VALUES (?, ?)");
if (!$stmt) {
    die("Error en preparación: " . $conn->error);
}

$success = true;
foreach ($intereses as $interes_id) {
    $stmt->bind_param("ii", $user_id, $interes_id);
    if (!$stmt->execute()) {
        $success = false;
        break;
    }
}

$stmt->close();
$conn->close();

if ($success) {
    // Redirigir a la cuenta del usuario
    header("Location: cuenta.php");
    exit();
} else {
    die("Error al guardar intereses");
}
?>
