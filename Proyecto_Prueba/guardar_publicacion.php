<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/conn.php';
// $conn inicializada en conn.php

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["success" => false, "error" => "Sesión no iniciada"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$tipo_perfil = $_POST['tipo_perfil'] ?? ''; // 'personal' o 'contenido'
$caption = $_POST['caption'] ?? '';
$archivo = $_FILES['archivo'] ?? null;

// Validaciones básicas
if (empty($tipo_perfil) || !$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "error" => "Datos incompletos o error en el archivo"]);
    exit;
}

// Si es contenido, validar que se haya seleccionado un interés
if ($tipo_perfil === 'contenido') {
    $interes_id = $_POST['interes_id'] ?? '';
    if (empty($interes_id)) {
        echo json_encode(["success" => false, "error" => "Debes seleccionar un interés para publicar en Contenido"]);
        exit;
    }
}

// Crear carpeta si no existe
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

$nombreArchivo = time() . "_" . basename($archivo['name']);
$rutaDestino = 'uploads/' . $nombreArchivo;

if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    $stmt = $conn->prepare("INSERT INTO publicaciones (usuario_id, tipo_perfil, caption, ruta_archivo, tipo_archivo, interes_id) VALUES (?, ?, ?, ?, ?, ?)");
    $tipo_mime = $archivo['type'];
    $interes_id_final = ($tipo_perfil === 'contenido') ? $_POST['interes_id'] : null;

    $stmt->bind_param("issssi", $usuario_id, $tipo_perfil, $caption, $rutaDestino, $tipo_mime, $interes_id_final);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "ruta" => $rutaDestino,
            "message" => "Publicación guardada correctamente"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Error al guardar en base de datos: " . $conn->error,
            "debug" => [
                "usuario_id" => $usuario_id,
                "tipo_perfil" => $tipo_perfil,
                "interes_id" => $interes_id_final,
                "ruta" => $rutaDestino
            ]
        ]);
    }
    $stmt->close();
} else {
    echo json_encode([
        "success" => false,
        "error" => "Error al subir el archivo",
        "debug" => [
            "upload_error" => $archivo['error'] ?? 'unknown',
            "tmp_name" => $archivo['tmp_name'] ?? 'missing',
            "destination" => $rutaDestino
        ]
    ]);
}

$conn->close();
?>