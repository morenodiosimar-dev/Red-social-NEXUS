<?php
ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();
include 'conn.php';

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        $output = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Fatal error en el servidor',
            'fatal' => $error,
            'debug_output' => $output
        ]);
        return;
    }

    // Si no hubo fatal, dejamos el output tal cual (ya debería ser JSON)
    ob_end_flush();
});

if (!isset($_SESSION['usuario_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$interesesRaw = $_GET['intereses'] ?? [];

// Aceptar: intereses=1,2,3 o intereses[]=1&intereses[]=2
if (is_array($interesesRaw)) {
    $interesesArray = $interesesRaw;
} else {
    $interesesRaw = trim((string)$interesesRaw);
    $interesesArray = ($interesesRaw === '')
        ? []
        : preg_split('/\s*,\s*/', $interesesRaw, -1, PREG_SPLIT_NO_EMPTY);
}

$interesesArray = array_map('intval', $interesesArray);
$interesesArray = array_values(array_filter($interesesArray, fn($v) => $v > 0));

try {
    if (empty($interesesArray)) {
        // Si no hay intereses seleccionados, mostrar todas las publicaciones
        $query = "SELECT p.*, u.nombre, u.apellido, u.foto_perfil 
                  FROM publicaciones p 
                  JOIN usuarios u ON p.usuario_id = u.id 
                  ORDER BY p.id DESC 
                  LIMIT 50";
    } else {
        // Filtrar por intereses seleccionados
        $interesesList = implode(',', $interesesArray);

        // Mostrar publicaciones cuyo interes_id coincide
        $query = "SELECT DISTINCT p.*, u.nombre, u.apellido, u.foto_perfil, i.name AS interes_name, i.icon AS interes_icon, i.category AS interes_category
                  FROM publicaciones p 
                  JOIN usuarios u ON p.usuario_id = u.id 
                  LEFT JOIN interes i ON p.interes_id = i.id
                  WHERE p.interes_id IN ($interesesList)
                  ORDER BY p.id DESC 
                  LIMIT 50";
    }
    
    if (!empty($interesesArray)) {
        // Para consultas con IN, ejecutamos directamente
        $result = $conn->query($query);
    } else {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'error' => 'Error preparando la consulta',
                'mysql_error' => $conn->error,
                'query' => $query
            ]);
            exit;
        }
        $stmt->execute();
        $result = $stmt->get_result();
    }

    if ($result === false) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => 'Error ejecutando la consulta',
            'mysql_error' => $conn->error,
            'query' => $query,
            'intereses' => $interesesArray
        ]);
        exit;
    }
    
    $publicaciones = [];
    $html = '';
    while ($post = $result->fetch_assoc()) {
        $id_post = (int)$post['id'];
        $autor_id = (int)$post['usuario_id'];
        $autor = ($post['nombre'] ?? '') . ' ' . ($post['apellido'] ?? '');
        $foto_autor = trim($post['foto_perfil'] ?? '');
        $ruta_archivo = $post['ruta_archivo'] ?? '';
        $tipo_archivo = $post['tipo_archivo'] ?? '';
        $caption = $post['caption'] ?? '';

        // Likes
        $likesRow = $conn->query("SELECT COUNT(*) as total FROM reacciones WHERE publicacion_id = $id_post")->fetch_assoc();
        $likesTotal = (int)($likesRow['total'] ?? 0);
        $reaccion_check = $conn->query("SELECT id FROM reacciones WHERE publicacion_id = $id_post AND usuario_id = $usuario_id");
        $yaReacciono = ($reaccion_check && $reaccion_check->num_rows > 0);
        $icono = $yaReacciono ? 'heart' : 'heart-outline';
        $color = $yaReacciono ? 'color: #ff2d55;' : 'color: #262626;';

        // Comentarios (top 3)
        $comentariosHtml = '';
        $res_coms = $conn->query("SELECT c.*, u.nombre FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.publicacion_id = $id_post ORDER BY c.id ASC LIMIT 3");
        if ($res_coms) {
            while ($c = $res_coms->fetch_assoc()) {
                $nombre_c = htmlspecialchars($c['nombre'] ?? '');
                $contenido_c = htmlspecialchars($c['contenido'] ?? '');
                $comentariosHtml .= "<p class='mb-1'><span class='font-bold'>{$nombre_c}:</span> {$contenido_c}</p>";
            }
        }

        // Media
        $mediaHtml = '';
        if (strpos($tipo_archivo, 'image') !== false) {
            $mediaHtml = '<img src="' . htmlspecialchars($ruta_archivo) . '" class="w-full h-auto">';
        } else {
            $mediaHtml = '<video src="' . htmlspecialchars($ruta_archivo) . '" controls class="w-full h-auto"></video>';
        }

        // Relación interés (si aplica)
        $relHtml = '';
        if (!empty($post['interes_name'])) {
            $relHtml = '<div class="mt-2 flex flex-wrap gap-1">'
                . '<span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 rounded-full text-xs">'
                . '<ion-icon name="' . htmlspecialchars($post['interes_icon'] ?? 'pricetag-outline') . '" style="font-size: 10px;"></ion-icon>'
                . htmlspecialchars($post['interes_name'] ?? '')
                . '</span>'
                . '</div>';
        }

        $fotoAutorHtml = (!empty($foto_autor) && $foto_autor !== 'default.png')
            ? '<img src="' . htmlspecialchars($foto_autor) . '" style="width: 100%; height: 100%; object-fit: cover;">'
            : '<ion-icon name="person-circle-outline" style="color: #9ca3af;"></ion-icon>';

        $html .= '<div class="publicacion-card bg-white w-full mb-8 shadow-sm border border-gray-200 overflow-hidden rounded-xl">'
            . '<div class="p-3 flex items-center gap-2">'
            . '<div class="avatar-container" style="width: 36px; height: 36px; min-width: 36px; border: 1px solid #eee;">'
            . $fotoAutorHtml
            . '</div>'
            . '<div class="flex flex-col">'
            . '<span class="font-bold text-[14px] leading-tight text-gray-800">' . htmlspecialchars($autor) . '</span>'
            . '</div>'
            . '</div>'
            . '<div class="w-full bg-gray-50 flex items-center justify-center border-y border-gray-100">'
            . $mediaHtml
            . '</div>'
            . '<div class="p-4">'
            . '<div class="flex gap-5 mb-3 text-2xl">'
            . '<div class="flex items-center gap-1.5">'
            . '<button onclick="reaccionar(' . $id_post . ')" class="hover:scale-125 transition active:scale-90">'
            . '<ion-icon name="' . $icono . '" id="heart-' . $id_post . '" style="' . $color . '"></ion-icon>'
            . '</button>'
            . '<span class="text-sm font-bold text-gray-800" id="count-' . $id_post . '">' . $likesTotal . '</span>'
            . '</div>'
            . '<button onclick="document.getElementById(\'input-com-' . $id_post . '\').focus()" class="hover:scale-125 transition">'
            . '<ion-icon name="chatbubble-outline" style="color: #262626;"></ion-icon>'
            . '</button>'
            . '<button onclick="mandarRepublicacion(' . $id_post . ')" class="hover:scale-125 transition">'
            . '<ion-icon name="repeat-outline" style="color: #262626;"></ion-icon>'
            . '</button>'
            . '</div>'
            . '<div class="text-sm mb-3 text-gray-800">'
            . '<span class="font-bold mr-1">' . htmlspecialchars($post['nombre'] ?? '') . '</span>'
            . '<span>' . htmlspecialchars($caption) . '</span>'
            . '</div>'
            . $relHtml
            . '<div id="comentarios-lista-' . $id_post . '" class="mt-2 text-xs">'
            . $comentariosHtml
            . '</div>'
            . '<div class="flex items-center gap-2 border-t border-gray-100 pt-3 mt-3">'
            . '<input type="text" id="input-com-' . $id_post . '" placeholder="Añadir comentario..." class="text-sm w-full outline-none bg-transparent">'
            . '<button onclick="enviarComentario(' . $id_post . ')" class="text-blue-500 font-bold text-sm hover:text-blue-700">Publicar</button>'
            . '</div>'
            . '</div>'
            . '</div>';

        // Formatear publicación
        $publicacion = [
            'id' => $post['id'],
            'autor' => $post['nombre'] . ' ' . $post['apellido'],
            'foto_autor' => $post['foto_perfil'],
            'ruta_archivo' => $post['ruta_archivo'],
            'tipo_archivo' => $post['tipo_archivo'],
            'caption' => $post['caption'],
            'fecha' => $post['fecha'] ?? null,
            'relacion_intereses' => []
        ];

        // Si esta publicación tiene interés, exponerlo para render
        if (!empty($post['interes_name'])) {
            $publicacion['relacion_intereses'][] = [
                'name' => $post['interes_name'],
                'icon' => $post['interes_icon'],
                'category' => $post['interes_category']
            ];
        }
        
        $publicaciones[] = $publicacion;
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'publicaciones' => $publicaciones,
        'html' => $html,
        'total' => count($publicaciones)
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>