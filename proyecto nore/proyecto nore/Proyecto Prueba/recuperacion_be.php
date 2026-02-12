<?php
header('Content-Type: application/json');
$conn = new mysqli("127.0.0.1", "root", "", "nexus_db", 3306);

$accion = $_POST['accion'] ?? '';
$correo = $_POST['correo'] ?? '';

if ($accion == 'verificar_correo') {
    // Agregamos "nombre" a la consulta
    $stmt = $conn->prepare("SELECT nombre FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        // Devolvemos el nombre en la respuesta JSON
        echo json_encode([
            "success" => true, 
            "nombre" => $usuario['nombre']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Este correo no está registrado"]);
    }
    exit;
}

if ($accion == 'actualizar_pass') {
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE correo = ?");
    $stmt->bind_param("ss", $pass, $correo);
    echo json_encode(["success" => $stmt->execute()]);
    exit;
}
?>