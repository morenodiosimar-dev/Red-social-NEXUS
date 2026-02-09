<?php
session_start();
header('Content-Type: application/json; charset=utf-8');


// DATOS DE RAILWAY (Copia y pega esto)
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "BpFRhFTLghAcqTRozKXkQyajMlYVqZCw";
$dbname = "railway"; // <-- En Railway tu BD se llama así
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);


if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

// 2. Obtener el ID del usuario
$u_id = $_SESSION['usuario_id'] ?? 0;

if ($u_id === 0) {
    echo json_encode(["status" => "error", "message" => "Sesión no iniciada"]);
    exit;
}

// 3. Recibir y limpiar datos
// Trim elimina espacios vacíos al inicio y al final
$nom = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$ape = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';

// --- VALIDACIÓN DE SEGURIDAD (LADO DEL SERVIDOR) ---
// Esta expresión regular permite letras de la A a la Z (mayúsculas/minúsculas) y tildes.
// Si encuentra algo que NO sea una letra, dará error.
$patron = "/^[a-zA-ZÀ-ÿ]+$/u";

if (!preg_match($patron, $nom) || !preg_match($patron, $ape)) {
    echo json_encode([
        "status" => "error", 
        "message" => "El nombre y apellido solo deben contener letras, sin espacios, números ni símbolos."
    ]);
    exit;
}

// 4. Sanitizar para SQL (Prevención de Inyección SQL)
$nom_safe = $conn->real_escape_string($nom);
$ape_safe = $conn->real_escape_string($ape);

// 5. Ejecutar la actualización
$sql = "UPDATE usuarios SET nombre = '$nom_safe', apellido = '$ape_safe' WHERE id = $u_id";

if ($conn->query($sql)) {
    // Actualizar la sesión para que el cambio se vea en toda la web de inmediato
    $_SESSION['nombre'] = $nom;
    $_SESSION['apellido'] = $ape;

    echo json_encode(["status" => "success", "message" => "Datos actualizados correctamente"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al actualizar: " . $conn->error]);
}

$conn->close();
?>