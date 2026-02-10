<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}


// DATOS DE RAILWAY (Copia y pega esto)
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "BpFRhFTLghAcqTRozKXkQyajMlYVqZCw";
$dbname = "railway"; // <-- En Railway tu BD se llama así
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) { die("Error de conexión"); }

$nombre_completo = ($_SESSION['nombre'] ?? 'Usuario') . " " . ($_SESSION['apellido'] ?? '');
$usuario_id = $_SESSION['usuario_id'];

// Verificar si el usuario tiene intereses guardados (solo para nuevos usuarios)
$checkUserInterests = $conn->prepare("SELECT COUNT(*) as count FROM user_interests WHERE user_id = ?");
$checkUserInterests->bind_param("i", $usuario_id);
$checkUserInterests->execute();
$resultCheck = $checkUserInterests->get_result();
$userInterestsCount = $resultCheck->fetch_assoc()['count'];
$checkUserInterests->close();

// Si el usuario no tiene intereses, redirigir a selección de intereses (solo para nuevos usuarios)
if ($userInterestsCount == 0) {
    header("Location: seleccionar_interes.php?user_id=" . $usuario_id);
    exit;
}

// Obtener los intereses del usuario organizados por categorías
$query_user_interests = "
    SELECT i.id, i.name, i.icon, i.category 
    FROM user_interests ui 
    JOIN interes i ON ui.interes_id = i.id 
    WHERE ui.user_id = ? 
    ORDER BY i.category, i.name";
$stmt_interests = $conn->prepare($query_user_interests);
$stmt_interests->bind_param("i", $usuario_id);
$stmt_interests->execute();
$result_interests = $stmt_interests->get_result();

$user_interests_by_category = [];
while ($interest = $result_interests->fetch_assoc()) {
    $category = $interest['category'] ?? 'Otros';
    $user_interests_by_category[$category][] = $interest;
}
$stmt_interests->close();

// 1. Consultar la foto del usuario logueado
$res_mi_foto = $conn->query("SELECT foto_perfil FROM usuarios WHERE id = $usuario_id");
$user_info = $res_mi_foto->fetch_assoc();
$mi_foto_perfil = trim($user_info['foto_perfil'] ?? '');

// 2. Consulta para sugerencias
$query_sugerencias = "SELECT id, nombre, apellido, foto_perfil 
                      FROM usuarios 
                      WHERE id != $usuario_id 
                      AND id NOT IN (
                          SELECT seguido_id 
                          FROM seguidores 
                          WHERE seguidor_id = $usuario_id
                      ) 
                      ORDER BY RAND() 
                      LIMIT 5";

$res_sugerencias = $conn->query($query_sugerencias);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus - Mi Cuenta</title>
    <link rel="stylesheet" href="cuenta.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
.info-usuario{
    width: 100%;
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa !important;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.info-usuario .avatar-container{
    width: 45px;
    height: 45px;
    min-width: 45px;
    border: 2px solid #a855f7;
    border-radius: 50%;
    overflow: hidden;
    background-color: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-usuario .avatar-container img{
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.info-usuario .avatar-morado{
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(130deg, rgb(255, 97, 242), rgb(183, 2, 255), rgb(115, 45, 245)) !important;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-usuario .avatar-morado ion-icon{
    color: white !important;
    font-size: 45px;
}

.info-usuario .usuario-n{
    margin: 0 !important;
    font-weight: bold;
}

.btn-seguir-feed {
    background: linear-gradient(45deg, #ff00cc, #3333ff);
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    letter-spacing: 0.5px;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 15px;
    color: white;
    cursor: pointer;
}
.con { background-color: #e9ecef !important; }
body { background-color: #e9ecef !important; }
.contenedor-cuenta { background-color: #e9ecef !important; }
.avatar-container {
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 50%;
    background-color: #f3f4f6;
}
/* Importante: esto hace que el icono ocupe todo el espacio del círculo */
.avatar-container ion-icon {
    width: 100%;
    height: 100%;
}

/* Estilos para la nueva columna izquierda */
.columna-izquierda {
    width: 280px;
    min-width: 280px;
    margin-right: 20px;
    position: sticky;
    top: 20px;
}

.filtro-intereses-container {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.layout-global .columna-izquierda .filtro-intereses-container {
    max-height: calc(100vh - 120px);
    overflow-y: auto;
}

.filtro-titulo {
    color: #374151;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 16px;
    text-align: center;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

#filtro-intereses {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #374151;
    font-size: 14px;
    transition: all 0.2s ease;
}

#filtro-intereses:focus {
    outline: none;
    border-color: #a855f7;
    box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1);
}

.botones-intereses {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(40px, 1fr));
    gap: 8px;
    margin-top: 16px;
}

.interes-boton {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.interes-boton:hover {
    border-color: #a855f7;
    background: #faf5ff;
    transform: scale(1.05);
}

.interes-boton.active {
    border-color: #a855f7;
    background: #ede9fe;
    color: #7c3aed;
}

.interes-boton ion-icon {
    font-size: 18px;
    color: #6b7280;
}

.interes-boton:hover ion-icon,
.interes-boton.active ion-icon {
    color: #7c3aed;
}

.boton-limpiar {
    width: 100%;
    padding: 6px 10px;
    margin-top: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    font-weight: 500;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.boton-limpiar:hover {
    background: #faf5ff;
    border-color: #a855f7;
    color: #7c3aed;
}

.categorias-intereses {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.categoria {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
}

.categoria-summary {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    cursor: pointer;
    user-select: none;
    font-weight: 600;
    color: #374151;
}

.categoria-summary::-webkit-details-marker {
    display: none;
}

.categoria-summary ion-icon {
    font-size: 18px;
    color: #7c3aed;
}

.categoria-body {
    padding: 10px 12px 12px;
    border-top: 1px solid #f3f4f6;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.categoria-empty {
    font-size: 13px;
    color: #9ca3af;
}

.interes-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border: 1px solid #f3f4f6;
    border-radius: 10px;
    background: #fafafa;
    cursor: pointer;
}

.interes-item:hover {
    border-color: #a855f7;
    background: #faf5ff;
}

.interes-item input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: #7c3aed;
}

.interes-item ion-icon {
    font-size: 16px;
    color: #6b7280;
}

.interes-item span {
    font-size: 13px;
    color: #374151;
}

/* Layout responsive */
.layout-global {
    display: grid;
    grid-template-columns: 200px 1fr 300px;
    column-gap: 20px;
    width: 100vw;
    margin: 0;
    padding: 0;
    align-items: start;
    position: relative;
    left: 0;
    right: 0;
}

.columna-izquierda {
    width: 200px !important;
    min-width: 250px !important;
    position: sticky;
    top: 20px;
    margin-top: 12px;
    margin-right: 0;
    align-self: start;
}

#feed-publicaciones {
    width: 100% !important;
    max-width: 500px !important;
    justify-self: center !important;
}

.columna-sugerencias {
    width: 300px !important;
    min-width: 300px !important;
    align-self: start;
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 20px);
    overflow-y: auto;
    overflow-x: hidden;
}

.columna-sugerencias::-webkit-scrollbar {
    width: 6px;
}

.columna-sugerencias::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.columna-sugerencias::-webkit-scrollbar-thumb {
    background: #a855f7;
    border-radius: 3px;
}

.columna-sugerencias::-webkit-scrollbar-thumb:hover {
    background: #7c3aed;
}

@media (max-width: 1024px) {
    .columna-izquierda {
        display: none;
    }
    .layout-global {
        grid-template-columns: 1fr;
        padding: 0 15px;
        row-gap: 20px;
    }
    #feed-publicaciones {
        max-width: 100%;
        width: 100%;
    }
    .columna-sugerencias {
        width: 100%;
        max-width: 600px;
    }
}

/* Resto de estilos */

/* Estilos para botón de logout */
.logout-btn {
    position: absolute;
    right: 70px;
    top: 50%;
    transform: translateY(-50%) !important;
    background: var(--gradient);
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 24px;
    color: white;
    cursor: pointer;
    transition: none !important;
}

.logout-btn:hover {
    transform: translateY(-50%) scale(1) !important;
    transition: none !important;
}

.logout-btn ion-icon {
    font-size: 24px;
    color: white;
}

/* Estilos para modal de confirmación de logout */
.modal-confirmacion-logout {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-confirmacion-logout.show {
    display: flex;
}

.modal-contenido-logout {
    background: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    max-width: 400px;
    width: 90%;
}

.modal-contenido-logout h3 {
    margin: 0 0 15px 0;
    color: #374151;
    font-size: 18px;
    font-weight: 600;
}

.modal-contenido-logout p {
    margin: 0 0 25px 0;
    color: #6b7280;
    font-size: 14px;
}

.modal-botones-logout {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.modal-botones-logout button {
    padding: 8px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-confirmar-logout {
    background: #ef4444;
    color: white;
}

.btn-confirmar-logout:hover {
    background: #dc2626;
}

.btn-cancelar-logout {
    background: #f3f4f6;
    color: #374151;
}

.btn-cancelar-logout:hover {
    background: #e5e7eb;
}
</style>
</head>
<body class="con">
 
 <div class="titulo-cuenta">
     <div class="centro">
     <ion-icon name="add-outline" class="agg-fotos" id="btn-publicar"></ion-icon>
     <input type="file" id="input-foto" accept="image/*, video/*" style="display: none">
     <div class="NombreRED">Nexus</div>
     
     <div class="notifications" onclick="toggleNotificaciones()">
         <ion-icon name="notifications-outline"></ion-icon>
         <span id="dot-notificacion"></span> 
     </div>

     <div class="logout-btn" onclick="mostrarConfirmacionLogout()">
         <ion-icon name="log-out-outline"></ion-icon>
     </div>

     <div id="panel-notificaciones" style="display:none;">
         <div class="noti-header">Notificaciones</div>
         <div id="lista-notificaciones-content" class="noti-lista"></div>
     </div>
 </div>
 </div>
 
 <div class="contenedor-cuenta">
     <div class="info-usuario">
         <div class="avatar-container">
             <?php if (!empty($mi_foto_perfil) && $mi_foto_perfil !== 'default.png'): ?>
                 <img src="<?php echo $mi_foto_perfil; ?>">
             <?php else: ?>
                 <div class="avatar-morado">
                     <ion-icon name="person-circle-outline"></ion-icon>
                 </div>
             <?php endif; ?>
         </div>
         <span id="nombre-usuario" class="usuario-n"><?php echo $nombre_completo; ?></span>
     </div>
     
     <div class="layout-global">
        <!-- Sidebar izquierdo con filtros de intereses -->
        <aside class="columna-izquierda">
            <div class="filtro-intereses-container">
                <h2 class="filtro-titulo">Preferencias de Contenido</h2>

            <?php
            $all_interests_query = "SELECT id, name, icon, category FROM interes ORDER BY category, name";
            $all_interests_result = $conn->query($all_interests_query);
            $interests_by_category = [];
            if ($all_interests_result) {
                while ($interest = $all_interests_result->fetch_assoc()) {
                    $catRaw = trim($interest['category'] ?? '');
                    // Normalizar categorías (en BD vienen como "💪 Bienestar", "⚽ Deportes", etc.)
                    $cat = preg_replace('/^[^\p{L}]+\s*/u', '', $catRaw);
                    $cat = trim($cat);
                    $interests_by_category[$cat][] = $interest;
                }
            }

            $categorias = [
                'Entretenimiento' => 'film-outline',
                'Cultura y estilo de vida' => 'color-palette-outline',
                'Bienestar' => 'leaf-outline',
                'Negocios y aprendizaje' => 'school-outline',
                'Deportes' => 'football-outline'
            ];
            ?>

            <div class="categorias-intereses">
                <?php foreach ($categorias as $nombreCat => $iconCat): ?>
                    <details class="categoria">
                        <summary class="categoria-summary">
                            <ion-icon name="<?php echo $iconCat; ?>"></ion-icon>
                            <span><?php echo htmlspecialchars($nombreCat); ?></span>
                        </summary>
                        <div class="categoria-body">
                            <?php
                            $lista = $interests_by_category[$nombreCat] ?? [];
                            if (empty($lista)) {
                                echo '<div class="categoria-empty">Sin intereses</div>';
                            } else {
                                foreach ($lista as $it) {
                                    $id = (int)$it['id'];
                                    $name = $it['name'] ?? '';
                                    $icon = $it['icon'] ?? 'pricetag-outline';
                                    echo '<label class="interes-item">'
                                        . '<input type="checkbox" class="filtro-interes-checkbox" value="' . $id . '">' 
                                        . '<ion-icon name="' . htmlspecialchars($icon) . '"></ion-icon>'
                                        . '<span>' . htmlspecialchars($name) . '</span>'
                                        . '</label>';
                                }
                            }
                            ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>

            <button id="limpiar-filtros" class="boton-limpiar">Limpiar</button>
        </div>
    </aside>

    <div id="feed-publicaciones" class="flex flex-col items-center w-full mt-4 px-1">
    <?php
    $query = "SELECT p.*, u.nombre, u.apellido, u.foto_perfil 
            FROM publicaciones p 
            JOIN usuarios u ON p.usuario_id = u.id 
            ORDER BY p.id DESC";
              
    $resultado = $conn->query($query);

    if ($resultado && $resultado->num_rows > 0) {
        while ($post = $resultado->fetch_assoc()) {
            $id_post = $post['id'];
            $autor = $post['nombre'] . " " . $post['apellido'];
            $foto_autor = trim($post['foto_perfil'] ?? '');
            ?>
            
            <div class="publicacion-card bg-white w-full mb-8 shadow-sm border border-gray-200 overflow-hidden rounded-xl">
                <div class="p-3 flex items-center gap-2">
                    <div class="avatar-container" style="width: 36px; height: 36px; min-width: 36px; border: 1px solid #eee;">
                        <?php if (!empty($foto_autor) && $foto_autor !== 'default.png'): ?>
                            <img src="<?php echo $foto_autor; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <ion-icon name="person-circle-outline" style="color: #9ca3af;"></ion-icon>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex flex-col">
                        <span class="font-bold text-[14px] leading-tight text-gray-800"><?php echo $autor; ?></span>
                    </div>
                    
                    <?php
                    // Lógica para el botón Seguir
                    $checkSeguido = $conn->query("SELECT id FROM seguidores WHERE seguidor_id = $usuario_id AND seguido_id = ".$post['usuario_id']);
                    if ($checkSeguido->num_rows == 0 && $post['usuario_id'] != $usuario_id) {
                       echo '<button class="btn-seguir-feed ml-auto" data-seguido="'.$post['usuario_id'].'"> Seguir </button>';
                    }
                    ?>
                </div>
              
                <div class="w-full bg-gray-50 flex items-center justify-center border-y border-gray-100">
                     <?php if (strpos($post['tipo_archivo'], 'image') !== false): ?>
                        <img src="<?php echo $post['ruta_archivo']; ?>" class="w-full h-auto">
                    <?php else: ?>
                        <video src="<?php echo $post['ruta_archivo']; ?>" controls class="w-full h-auto"></video>
                    <?php endif; ?>
                </div>

                <div class="p-4">
                    <div class="flex gap-5 mb-3 text-2xl">
                        <div class="flex items-center gap-1.5">
                            <button onclick="reaccionar(<?php echo $id_post; ?>)" class="hover:scale-125 transition active:scale-90">
                                <?php
                                $reaccion_check = $conn->query("SELECT id FROM reacciones WHERE publicacion_id = $id_post AND usuario_id = $usuario_id");
                                $icono = ($reaccion_check->num_rows > 0) ? 'heart' : 'heart-outline';
                                $color = ($reaccion_check->num_rows > 0) ? 'color: #ff2d55;' : 'color: #262626;';
                                ?>
                                <ion-icon name="<?php echo $icono; ?>" id="heart-<?php echo $id_post; ?>" style="<?php echo $color; ?>"></ion-icon>
                            </button>
                            <?php
                            $likes = $conn->query("SELECT COUNT(*) as total FROM reacciones WHERE publicacion_id = $id_post")->fetch_assoc();
                            ?>
                            <span class="text-sm font-bold text-gray-800" id="count-<?php echo $id_post; ?>"><?php echo $likes['total']; ?></span>
                        </div>

                        <button onclick="document.getElementById('input-com-<?php echo $id_post; ?>').focus()" class="hover:scale-125 transition">
                            <ion-icon name="chatbubble-outline" style="color: #262626;"></ion-icon>
                        </button>

                        <button onclick="mandarRepublicacion(<?php echo $id_post; ?>)" class="hover:scale-125 transition">
                            <ion-icon name="repeat-outline" style="color: #262626;"></ion-icon>
                        </button>
                    </div>

                    <div class="text-sm mb-3 text-gray-800">
                        <span class="font-bold mr-1"><?php echo $post['nombre']; ?></span> 
                        <span><?php echo htmlspecialchars($post['caption']); ?></span>
                    </div>

                    <div id="comentarios-lista-<?php echo $id_post; ?>" class="mt-2 text-xs">
                        <?php
                        $res_coms = $conn->query("SELECT c.*, u.nombre FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.publicacion_id = $id_post ORDER BY c.id ASC LIMIT 3");
                        while ($c = $res_coms->fetch_assoc()) {
                            echo "<p class='mb-1'><span class='font-bold'>{$c['nombre']}:</span> ".htmlspecialchars($c['contenido'])."</p>";
                        }
                        ?>
                    </div>

                    <div class="flex items-center gap-2 border-t border-gray-100 pt-3 mt-3">
                        <input type="text" id="input-com-<?php echo $id_post; ?>" placeholder="Añadir comentario..." class="text-sm w-full outline-none bg-transparent">
                        <button onclick="enviarComentario(<?php echo $id_post; ?>)" class="text-blue-500 font-bold text-sm hover:text-blue-700">Publicar</button>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>

    <aside class="columna-sugerencias">
        <!-- Sección de Sugerencias -->
        <div style="background-color: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 class="text-sm font-bold text-gray-700 mb-4" style="letter-spacing: 0.5px;">SUGERENCIAS PARA TI</h2>
            <div class="flex flex-col gap-3 max-h-[500px] overflow-y-auto" style="scrollbar-width: thin;">
                <?php while($sug = $res_sugerencias->fetch_assoc()): 
                    $foto_s = trim($sug['foto_perfil'] ?? '');
                ?>
                <div class="sugerencia-item flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition" data-user-id="<?php echo $sug['id']; ?>">
                    <div class="avatar-container" style="width: 45px; height: 45px; min-width: 45px; border: 2px solid #a855f7;">
                        <?php if (!empty($foto_s) && $foto_s !== 'default.png'): ?>
                            <img src="<?php echo $foto_s; ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <ion-icon name="person-circle-outline" style="color: #a855f7; font-size: 45px;"></ion-icon>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0 flex items-center justify-between gap-2">
                        <span class="text-sm font-bold text-gray-800 truncate cursor-pointer" onclick="window.location.href='perfil_ver.php?id=<?php echo $sug['id']; ?>'"><?php echo $sug["nombre"] . " " . $sug["apellido"]; ?></span>
                        <button onclick="gestionarSeguimiento(<?php echo $sug['id']; ?>, this)" class="btn-seguir-sug" data-seguido="<?php echo $sug['id']; ?>">Seguir</button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </aside>
</div>

<div class="iconos-inferiores">
    <div class="active-icon icont" onclick="window.location.href='cuenta.php'">
        <ion-icon name="home-outline"></ion-icon>
    </div>
    
    <div class="icont" onclick="window.location.href='busqueda.php'">
        <ion-icon name="search-outline"></ion-icon>
    </div>
    
    <div class="icont" onclick="window.location.href='https://red-social-nexus-production.up.railway.app/'">
        <ion-icon name="chatbubble-outline"></ion-icon>
    </div>
    
    <div class="icont" onclick="window.location.href='perfil.php'">
        <ion-icon name="person-outline"></ion-icon>
    </div>
</div>


<div id="publicacion-modal-overlay" class="modal-overlay hidden">
     <div id="publicacion-modal-content" class="modal-content">
        <div class="media" id="media-contenedor"></div>
        <textarea class="w-full p-2 border rounded focus:ring-2 focus:ring-purple-400 outline-none" id="c-textarea" placeholder="¿Qué estás pensando?" rows="3"></textarea>
        <div class="my-3 bg-gray-50 p-2 rounded-lg border border-gray-100">
            <p class="text-xs font-bold mb-2 text-gray-500 uppercase tracking-wider">¿Dónde publicar?</p>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="destino" value="personal" checked class="accent-purple-600"> Personal
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="destino" value="contenido" class="accent-purple-600"> Contenido
                </label>
            </div>
        </div>

        <div id="bloque-interes-contenido" class="my-3 bg-gray-50 p-2 rounded-lg border border-gray-100" style="display:none;">
            <p class="text-xs font-bold mb-2 text-gray-500 uppercase tracking-wider">¿En qué interés será?</p>
            <select id="select-interes-contenido" class="w-full p-2 border rounded outline-none">
                <option value="">Selecciona un interés</option>
                <?php
                $res_intereses_modal = $conn->query("SELECT id, name, category FROM interes ORDER BY category, name");
                if ($res_intereses_modal) {
                    while ($row_i = $res_intereses_modal->fetch_assoc()) {
                        echo '<option value="' . (int)$row_i['id'] . '">'
                            . htmlspecialchars(($row_i['category'] ? ($row_i['category'] . ' - ') : '') . ($row_i['name'] ?? ''))
                            . '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="flex gap-3">
            <button class="b-cancelar flex-1 p-2" id="cancelar-boton">Cancelar</button>
            <button class="b-publicar flex-1 p-2" id="Publicar-boton">Publicar 🚀</button>
        </div>    
    </div>
</div>
<div id="modal-detalle" class="hidden fixed inset-0 bg-black/90 z-[99999] flex items-center justify-center p-2 md:p-4" onclick="cerrarDetalle(event)">
    <div class="bg-white w-[95%] max-w-5xl h-[90vh] rounded-2xl overflow-hidden relative shadow-2xl flex flex-col" onclick="event.stopPropagation()">
        
        <button type="button" onclick="window.cerrarModalDirecto()" 
                class="absolute top-4 right-4 z-[110] text-gray-500 hover:text-red-500 bg-white/90 rounded-full w-10 h-10 flex items-center justify-center shadow-lg">
            <ion-icon name="close-outline" style="font-size: 32px;"></ion-icon>
        </button>

        <div id="contenido-detalle" class="flex-1 overflow-y-auto overflow-x-hidden bg-white">
            </div>
    </div>
</div>
<!-- Modal de confirmación de logout -->
<div id="modal-confirmacion-logout" class="modal-confirmacion-logout">
    <div class="modal-contenido-logout">
        <h3>¿Estás seguro de cerrar sesión?</h3>
        <p>Se cerrará tu sesión actual y serás redirigido a la página principal.</p>
        <div class="modal-botones-logout">
            <button class="btn-cancelar-logout" onclick="ocultarConfirmacionLogout()">Cancelar</button>
            <button class="btn-confirmar-logout" onclick="confirmarLogout()">Cerrar sesión</button>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
<script src="publicacion.js?v=<?php echo time(); ?>"></script>
<script src="scripts.js?v=<?php echo time(); ?>"></script>
<script>
// funcionalidad para los seguidos lado derecha en modulo cuenta
function gestionarSeguimiento(usuarioId, btn) {
    fetch('acciones_perfil.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `seguido_id=${usuarioId}`
    })
    .then(res => res.json())
    .then(data => {
        if (!data.status) return;

        // Actualizar TODOS los botones que correspondan a este usuario
        actualizarEstadoBoton(usuarioId, data.status);

        // Solo actualizar tu contador de seguidos si existe
        const contadorSeguidos = document.getElementById('contador-seguidos');
        if (contadorSeguidos && data.total_seguidos !== undefined) {
            contadorSeguidos.textContent = data.total_seguidos;
        }
    })
    .catch(err => console.error(err));
}

function actualizarEstadoBoton(usuarioId, estado) {
    document.querySelectorAll(`.btn-seguir-feed[data-seguido="${usuarioId}"]`).forEach(b => {
        if (estado === 'follow') {
            b.textContent = 'Siguiendo';
            b.classList.add('siguiendo');
        } else {
            b.textContent = 'Seguir';
            b.classList.remove('siguiendo');
        }
    });

    // Para las sugerencias: hacer que desaparezcan con animación
    if (estado === 'follow') {
        document.querySelectorAll(`.btn-seguir-sug[data-seguido="${usuarioId}"]`).forEach(b => {
            const contenedor = b.closest('.sugerencia-item');
            if(contenedor) {
                contenedor.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                contenedor.style.opacity = '0';
                contenedor.style.transform = 'translateX(20px)';
                setTimeout(() => contenedor.remove(), 300);
            }
        });
    } else {
        document.querySelectorAll(`.btn-seguir-sug[data-seguido="${usuarioId}"]`).forEach(b => {
            b.textContent = 'Seguir';
            b.classList.remove('siguiendo');
        });
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('btn-seguir-feed')) return;
    const btn = e.target;
    const usuarioId = btn.dataset.seguido;
    gestionarSeguimiento(usuarioId, btn);
});

async function actualizarNotificaciones() {
    try {
        const response = await fetch('obtener_notificaciones.php');
        const data = await response.json();
        const dot = document.getElementById('dot-notificacion');
        const lista = document.getElementById('lista-notificaciones-content');
        if (data.nuevas > 0) dot.style.display = 'block';
        else dot.style.display = 'none';

        if (data.lista && data.lista.length > 0) {
            lista.innerHTML = data.lista.map(n => {
                let mensaje = "";
                if(n.tipo === 'comentario') mensaje = "comentó tu publicación";
                if(n.tipo === 'like' || n.tipo === 'reaccion') mensaje = "reaccionó a tu publicación";
                if(n.tipo === 'republicar') mensaje = "republicó tu post";
                if(n.tipo === 'seguir') mensaje = "empezó a seguirte";

                // Lógica de foto en notificaciones (usamos el mismo icono si falla)
                let fotoIcono = n.foto_perfil 
                    ? `<img src="${n.foto_perfil.includes('uploads/') ? n.foto_perfil : 'uploads/' + n.foto_perfil}" class="w-10 h-10 rounded-full object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<ion-icon name=\'person-circle-outline\' style=\'font-size: 40px; color: #9ca3af;\'></ion-icon>'">`
                    : `<ion-icon name="person-circle-outline" style="font-size: 40px; color: #9ca3af;"></ion-icon>`;

                const fechaNoti = new Date(n.fecha);
                const ahora = new Date();
                const hoy = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate());
                const diaNoti = new Date(fechaNoti.getFullYear(), fechaNoti.getMonth(), fechaNoti.getDate());

                let actionClick = (n.tipo === 'seguir') 
                    ? `onclick="window.location.href='perfil_ver.php?id=${n.emisor_id}'"` 
                    : (n.publicacion_id ? `onclick="abrirDetalle(${n.publicacion_id}); toggleNotificaciones();"` : "");

                const diffTiempo = hoy - diaNoti;
                const diffDias = Math.round(diffTiempo / (1000 * 60 * 60 * 24));
                let fechaTexto = (diffDias === 0) ? "Hoy" : (diffDias === 1 ? "Ayer" : fechaNoti.toLocaleDateString());

                return `
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-100 transition border-b border-gray-50" ${actionClick} style="cursor:pointer;">
                        <div class="w-10 h-10 flex items-center justify-center">${fotoIcono}</div>
                        <div class="flex-grow">
                            <div class="text-sm leading-snug">
                                <span class="font-bold text-gray-800">${n.nombre} ${n.apellido}</span> 
                                <span class="text-gray-600">${mensaje}</span>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">${fechaTexto}</div>
                        </div>
                        ${n.leido == 0 ? '<div class="w-2.5 h-2.5 bg-purple-500 rounded-full"></div>' : ''}
                    </div>`;
            }).join('');
        } else {
            lista.innerHTML = '<p class="p-4 text-center text-gray-400 text-sm">No tienes notificaciones</p>';
        }
    } catch (e) { console.error("Error notis:", e); }
}

function toggleNotificaciones() {
    const panel = document.getElementById('panel-notificaciones');
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'flex';
        panel.style.flexDirection = 'column';
        fetch('obtener_notificaciones.php?leer=1').then(() => {
            const dot = document.getElementById('dot-notificacion');
            if(dot) dot.style.display = 'none';
        });
    } else {
        panel.style.display = 'none';
    }
}

window.addEventListener('click', function(e) {
    const panel = document.getElementById('panel-notificaciones');
    const btn = document.querySelector('.notifications');
    if (panel && btn && !btn.contains(e.target) && !panel.contains(e.target)) {
        panel.style.display = 'none';
    }
});

actualizarNotificaciones();
setInterval(actualizarNotificaciones, 15000);

 // Mostrar selector de interés solo cuando "Contenido" está seleccionado en el modal de publicación
document.addEventListener('DOMContentLoaded', function() {
    const bloqueInteres = document.getElementById('bloque-interes-contenido');
    const selectInteres = document.getElementById('select-interes-contenido');
    const radiosDestino = document.querySelectorAll('input[name="destino"]');

    function syncInteresContenido() {
        const checked = document.querySelector('input[name="destino"]:checked');
        const destino = checked ? checked.value : 'personal';
        if (!bloqueInteres) return;

        if (destino === 'contenido') {
            bloqueInteres.style.display = '';
        } else {
            bloqueInteres.style.display = 'none';
            if (selectInteres) selectInteres.value = '';
        }
    }

    radiosDestino.forEach(r => r.addEventListener('change', syncInteresContenido));
    syncInteresContenido();
});

 // Funcionalidad de filtros por intereses
 document.addEventListener('DOMContentLoaded', function() {
     const limpiarFiltrosBtn = document.getElementById('limpiar-filtros');
     const checkboxes = document.querySelectorAll('.filtro-interes-checkbox');
     const categorias = document.querySelectorAll('.categorias-intereses .categoria');

     // Opción B: solo una categoría abierta a la vez
     categorias.forEach(det => {
         det.addEventListener('toggle', function() {
             if (!this.open) return;
             categorias.forEach(otro => {
                 if (otro !== this) otro.open = false;
             });
         });
     });

     function getSeleccionados() {
         return Array.from(checkboxes)
             .filter(cb => cb.checked)
             .map(cb => cb.value);
     }

     async function filtrarPublicaciones(interesesSeleccionados) {
         let url = '';
         try {
             const container = document.getElementById('feed-publicaciones');
             if (container) {
                 container.innerHTML = `
                     <div class="text-center py-8">
                         <ion-icon name="hourglass-outline" style="font-size: 48px; color: #9ca3af;"></ion-icon>
                         <p class="mt-4 text-gray-500">Cargando publicaciones...</p>
                     </div>
                 `;
             }
             const controller = new AbortController();
             const timeoutId = setTimeout(() => controller.abort(), 10000);
             url = `filtrar_publicaciones.php?intereses=${interesesSeleccionados.join(',')}`;
             const response = await fetch(url, {
                 signal: controller.signal
             });
             clearTimeout(timeoutId);

             if (!response.ok) {
                 if (container) {
                     container.innerHTML = `
                         <div class="text-center py-8">
                             <ion-icon name="alert-circle-outline" style="font-size: 48px; color: #ef4444;"></ion-icon>
                             <p class="mt-4 text-gray-500">${response.status} ${response.statusText}</p>
                             <p class="mt-2 text-xs text-gray-400">Revisa consola (F12) para más detalle</p>
                         </div>
                     `;
                 }
             } else {
                 let data;
                 const responseClone = response.clone();
                 try {
                     data = await response.json();
                 } catch (e) {
                     const txt = await responseClone.text();
                     console.error('Respuesta no-JSON:', txt);
                     if (container) {
                         container.innerHTML = `
                             <div class="text-center py-8">
                                 <ion-icon name="alert-circle-outline" style="font-size: 48px; color: #ef4444;"></ion-icon>
                                 <p class="mt-4 text-gray-500">El servidor devolvió una respuesta inválida</p>
                                 <p class="mt-2 text-xs text-gray-400">Revisa consola (F12) para más detalle</p>
                             </div>
                         `;
                     }
                     return;
                 }
                 if (data.success) {
                     if (data.html !== undefined) {
                         if (container) {
                             container.innerHTML = data.html || `
                                 <div class="text-center py-8">
                                     <ion-icon name="search-outline" style="font-size: 48px; color: #9ca3af;"></ion-icon>
                                     <p class="mt-4 text-gray-500">No se encontraron publicaciones relacionadas con el interés seleccionado</p>
                                 </div>
                             `;
                         }
                     } else {
                         mostrarPublicacionesFiltradas(data.publicaciones);
                     }
                 } else {
                     console.error('Error al filtrar:', data.error);
                     if (container) {
                         container.innerHTML = `
                             <div class="text-center py-8">
                                 <ion-icon name="alert-circle-outline" style="font-size: 48px; color: #ef4444;"></ion-icon>
                                 <p class="mt-4 text-gray-500">${data.error || 'Error al filtrar publicaciones'}</p>
                                 <p class="mt-2 text-xs text-gray-400">Revisa consola (F12) para más detalle</p>
                             </div>
                         `;
                     }
                 }
             }
         } catch (error) {
             console.error('Error en la petición a', url, ':', error);
             const container = document.getElementById('feed-publicaciones');
             if (container) {
                 const msg = (error && error.name === 'AbortError')
                     ? 'La petición tardó demasiado (timeout)'
                     : (error && error.message ? error.message : 'Error en la petición');
                 container.innerHTML = `
                     <div class="text-center py-8">
                         <ion-icon name="alert-circle-outline" style="font-size: 48px; color: #ef4444;"></ion-icon>
                         <p class="mt-4 text-gray-500">${msg}</p>
                         <p class="mt-2 text-xs text-gray-400">Revisa consola (F12) para más detalle</p>
                     </div>
                 `;
             }
         }
     }

     function mostrarPublicacionesFiltradas(publicaciones) {
         const container = document.getElementById('feed-publicaciones');
         if (!container) return;

         if (!publicaciones || publicaciones.length === 0) {
             container.innerHTML = `
                 <div class="text-center py-8">
                     <ion-icon name="search-outline" style="font-size: 48px; color: #9ca3af;"></ion-icon>
                     <p class="mt-4 text-gray-500">No se encontraron publicaciones relacionadas con tus intereses seleccionados</p>
                 </div>
             `;
             return;
         }

         container.innerHTML = publicaciones.map(post => {
             const rel = (post.relacion_intereses && post.relacion_intereses.length > 0)
                 ? `<div class="mt-2 flex flex-wrap gap-1">
                      ${post.relacion_intereses.map(interes =>
                         `<span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 rounded-full text-xs">
                             <ion-icon name="${interes.icon}" style="font-size: 10px;"></ion-icon>
                             ${interes.name}
                         </span>`
                      ).join('')}
                    </div>`
                 : '';

             return `
                 <div class="publicacion-card bg-white w-full mb-8 shadow-sm border border-gray-200 overflow-hidden rounded-xl">
                     <div class="p-3 flex items-center gap-2">
                         <div class="avatar-container" style="width: 36px; height: 36px; min-width: 36px; border: 1px solid #eee;">
                             ${post.foto_autor && post.foto_autor !== 'default.png'
                                 ? `<img src="${post.foto_autor}" style="width: 100%; height: 100%; object-fit: cover;">`
                                 : `<ion-icon name="person-circle-outline" style="color: #9ca3af;"></ion-icon>`
                             }
                         </div>
                         <div class="flex flex-col">
                             <span class="font-bold text-[14px] leading-tight text-gray-800">${post.autor}</span>
                         </div>
                     </div>

                     <div class="w-full bg-gray-50 flex items-center justify-center border-y border-gray-100">
                         ${post.tipo_archivo.includes('image')
                             ? `<img src="${post.ruta_archivo}" class="w-full h-auto">`
                             : `<video src="${post.ruta_archivo}" controls class="w-full h-auto"></video>`
                         }
                     </div>

                     <div class="p-4">
                         <div class="text-sm mb-3 text-gray-800">
                             <span class="font-bold mr-1">${(post.autor || '').split(' ')[0] || ''}</span>
                             <span>${post.caption || ''}</span>
                         </div>
                         ${rel}
                     </div>
                 </div>
             `;
         }).join('');
     }

     checkboxes.forEach(cb => {
         cb.addEventListener('change', function() {
             // Selección única: si se marca uno, se desmarcan los demás
             if (this.checked) {
                 checkboxes.forEach(otro => {
                     if (otro !== this) otro.checked = false;
                 });
                 filtrarPublicaciones([this.value]);
             } else {
                 filtrarPublicaciones([]);
             }
         });
     });

     if (limpiarFiltrosBtn) {
         limpiarFiltrosBtn.addEventListener('click', function() {
             checkboxes.forEach(cb => { cb.checked = false; });
             filtrarPublicaciones([]);
         });
     }
 });

// Funciones para el logout
function mostrarConfirmacionLogout() {
    const modal = document.getElementById('modal-confirmacion-logout');
    modal.classList.add('show');
}

function ocultarConfirmacionLogout() {
    const modal = document.getElementById('modal-confirmacion-logout');
    modal.classList.remove('show');
}

function confirmarLogout() {
    // Crear un formulario y enviarlo para cerrar sesión
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'logout.php';
    
    // Opcional: agregar CSRF token si lo usas
    // const csrfToken = document.createElement('input');
    // csrfToken.type = 'hidden';
    // csrfToken.name = 'csrf_token';
    // csrfToken.value = 'tu_token_aqui';
    // form.appendChild(csrfToken);
    
    document.body.appendChild(form);
    form.submit();
}

// Cerrar modal al hacer clic fuera del contenido
document.addEventListener('click', function(e) {
    const modal = document.getElementById('modal-confirmacion-logout');
    const modalContent = document.querySelector('.modal-contenido-logout');
    
    if (modal && modal.classList.contains('show') && 
        !modalContent.contains(e.target) && 
        !e.target.closest('.logout-btn')) {
        ocultarConfirmacionLogout();
    }
});

// Cerrar modal con la tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modal-confirmacion-logout');
        if (modal && modal.classList.contains('show')) {
            ocultarConfirmacionLogout();
        }
    }
});
</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>