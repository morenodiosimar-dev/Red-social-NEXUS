<?php
session_start();
require_once __DIR__ . '/conn.php';
// $conn inicializada en conn.php

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    die("Sesión no iniciada");
}

$usuario_id = $_SESSION['usuario_id'];
$tipo_perfil = isset($_POST['tipo_perfil']) ? trim((string) $_POST['tipo_perfil']) : '';
$caption = isset($_POST['caption']) ? (string) $_POST['caption'] : '';
$archivo = $_FILES['archivo'] ?? null;

if ($tipo_perfil !== 'personal' && $tipo_perfil !== 'contenido') {
    echo json_encode(["success" => false, "error" => "tipo_perfil inválido", "debug" => ["tipo_perfil" => $tipo_perfil, "post_keys" => array_keys($_POST)]]);
    exit;
}

if (!$archivo || !isset($archivo['tmp_name'])) {
    echo json_encode(["success" => false, "error" => "No llegó archivo", "debug" => ["post_keys" => array_keys($_POST), "files_keys" => array_keys($_FILES)]]);
    exit;
}

$interes_id = null;
if ($tipo_perfil === 'contenido') {
    $interes_id_raw = isset($_POST['interes_id']) ? trim((string) $_POST['interes_id']) : '';
    $interes_id = (int) $interes_id_raw;

    // Debug: mostrar qué se está recibiendo
    error_log("DEBUG guardar_publicaciones.php - interes_id_raw: " . $interes_id_raw);
    error_log("DEBUG guardar_publicaciones.php - interes_id: " . $interes_id);
    error_log("DEBUG guardar_publicaciones.php - POST data: " . json_encode($_POST));

    if ($interes_id <= 0) {
        echo json_encode([
            "success" => false,
            "error" => "Falta seleccionar el interés para Contenido",
            "debug" => [
                "tipo_perfil" => $tipo_perfil,
                "interes_id_raw" => $interes_id_raw,
                "interes_id" => $interes_id,
                "post_keys" => array_keys($_POST)
            ]
        ]);
        exit;
    }
}

// Crear carpeta si no existe
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

$nombreArchivo = time() . "_" . $archivo['name'];
$rutaDestino = 'uploads/' . $nombreArchivo;

if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    $tipo_mime = $archivo['type'];

    if ($tipo_perfil === 'contenido') {
        $stmt = $conn->prepare("INSERT INTO publicaciones (usuario_id, tipo_perfil, interes_id, caption, ruta_archivo, tipo_archivo) VALUES (?, ?, ?, ?, ?, ?)");

        // Debug: verificar la consulta
        error_log("DEBUG - SQL para contenido: INSERT INTO publicaciones (usuario_id, tipo_perfil, interes_id, caption, ruta_archivo, tipo_archivo) VALUES (?, ?, ?, ?, ?, ?)");
        error_log("DEBUG - Parámetros: usuario_id=$usuario_id, tipo_perfil=$tipo_perfil, interes_id=$interes_id, caption=$caption, ruta_archivo=$rutaDestino, tipo_archivo=$tipo_mime");

        $stmt->bind_param("isisss", $usuario_id, $tipo_perfil, $interes_id, $caption, $rutaDestino, $tipo_mime);
    } else {
        $stmt = $conn->prepare("INSERT INTO publicaciones (usuario_id, tipo_perfil, caption, ruta_archivo, tipo_archivo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $usuario_id, $tipo_perfil, $caption, $rutaDestino, $tipo_mime);
    }

    if ($stmt->execute()) {
        // Debug: verificar si realmente se guardó
        $insert_id = $conn->insert_id;
        error_log("DEBUG - Publicación guardada con ID: $insert_id, interes_id: $interes_id");
        echo json_encode(["success" => true, "ruta" => $rutaDestino, "insert_id" => $insert_id, "interes_id_guardado" => $interes_id]);
    } else {
        // Debug: mostrar error exacto
        error_log("DEBUG - Error al insertar: " . $stmt->error);
        echo json_encode(["success" => false, "error" => $conn->error, "sql_error" => $stmt->error]);
    }
}
?>