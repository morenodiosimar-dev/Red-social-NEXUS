<?php
session_start();
$conn = new mysqli("127.0.0.1", "root", "", "nexus_db", 3306);

$buscar = $_POST['query'] ?? '';
$mi_id = $_SESSION['usuario_id'];

if ($buscar !== '') {
    $stmt = $conn->prepare("SELECT id, nombre, apellido, foto_perfil FROM usuarios 
                            WHERE (nombre LIKE ? OR apellido LIKE ?) 
                            AND id != ? LIMIT 10");
    
    $termino = "%$buscar%";
    $stmt->bind_param("ssi", $termino, $termino, $mi_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id_usuario = $row['id'];
            $nombre_completo = $row['nombre'] . ' ' . $row['apellido'];
            $foto_db = $row['foto_perfil'];

            // 1. Limpiamos el nombre del archivo (quitamos 'uploads/' si ya lo trae)
            $solo_nombre_archivo = str_replace('uploads/', '', $foto_db);
            
            // 2. Definimos la ruta real para verificar existencia y la ruta para el HTML
            $ruta_para_verificar = 'uploads/' . $solo_nombre_archivo;
            
            // 3. Verificación lógica
            $mostrar_foto = false;
            if (!empty($solo_nombre_archivo) && file_exists($ruta_para_verificar)) {
                $mostrar_foto = true;
            }

            echo '
            <div class="contenedor-horizontal-busqueda">
                <div class="foto-y-datos">
                    <div class="mini-avatar">';
            
            if ($mostrar_foto) {
                // Si existe, mostramos la foto real con la ruta correcta
                echo '<img src="'.$ruta_para_verificar.'" alt="perfil">';
            } else {
                // Si no existe o está vacío, el icono de Ion-Icon
                echo '<div class="avatar-vacio">
                        <ion-icon name="person-outline"></ion-icon>
                      </div>';
            }

            echo '  </div>
                    <div class="datos-columna">
                        <p class="nombre-usuario">'.$nombre_completo.'</p>
                        <p class="subtitulo-nexus">Ver perfil de Nexus</p>
                    </div>
                </div>
                <button onclick="window.location.href=\'perfil_ver.php?id='.$id_usuario.'\'" class="btn-ver-nexus">
                    Ver
                </button>
            </div>';
        }
    } else {
        echo '<p class="text-center text-gray-500 py-10">No se encontraron resultados.</p>';
    }
}
?>