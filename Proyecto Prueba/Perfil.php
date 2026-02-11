<?php
session_start();
// 1. Conexión a la base de datos

// DATOS DE RAILWAY (Copia y pega esto)
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "VDCVPmVJHDnmzZVPddmvGjriJbQdVHiU";
$dbname = "railway"; // <-- En Railway tu BD se llama así
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 2. Seguridad
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_completo = ($_SESSION['nombre'] ?? 'Usuario') . " " . ($_SESSION['apellido'] ?? '');
$tab = $_GET['tab'] ?? 'personal';

// CONSULTA DE FOTO (Lógica de cuenta.php)
$res_u = $conn->query("SELECT foto_perfil FROM usuarios WHERE id = $usuario_id");
$u_data = $res_u->fetch_assoc();
$foto_v = trim($u_data['foto_perfil'] ?? '');

// CONTADORES
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$seguidores = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$seguidos = $stmt->get_result()->fetch_assoc()['total'];

$tipo_perfil_filtro = ($tab == 'personal') ? 'personal' : 'contenido';

// PUBLICACIONES
$res_propias = $conn->query("SELECT id, ruta_archivo, tipo_archivo FROM publicaciones WHERE usuario_id = $usuario_id AND tipo_perfil = '$tipo_perfil_filtro' ORDER BY fecha_creacion DESC");
$res_reposts = $conn->query("SELECT p.id, p.ruta_archivo, p.tipo_archivo, r.fecha, r.id AS repost_id FROM republicaciones r JOIN publicaciones p ON r.publicacion_id = p.id WHERE r.usuario_id = $usuario_id ORDER BY r.fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Nexus</title>
    <link rel="stylesheet" href="perfil.css">
    <link rel="icon" href="logo.jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos de soporte para el icono como en cuenta.php */
        .avatar-perfil-container {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 50%;
            background-color: #f3f4f6;
        }
        .avatar-perfil-container ion-icon {
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
    <div class="perfil" id="form-perfil">
        <div class="perfil-usuario">
            <div class="superior">
                <div class="nombre-red">Nexus</div>
                <ion-icon name="menu-outline" class="icon-menu cursor-pointer hover:text-purple-500" onclick="window.location.href='conf.php'"></ion-icon>
            </div>
        </div>

        <div class="perfil-usuario flex items-center gap-4 p-4">
            <div onclick="document.getElementById('input-foto-perfil').click()" 
                 class="avatar-perfil-container relative w-24 h-24 min-w-[96px] border-2 border-purple-500 cursor-pointer shadow-lg" 
                 id="contenedor-foto-perfil">
                
                <?php if (!empty($foto_v) && $foto_v !== 'default.png'): ?>
                    <img src="<?php echo $foto_v; ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <ion-icon name="person-circle-outline" style="color: #a855f7;"></ion-icon>
                <?php endif; ?>
                
                <div class="absolute inset-0 bg-black/10 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center">
                    <ion-icon name="camera-outline" class="text-white text-xl" style="width: 30px; height: 30px;"></ion-icon>
                </div>
            </div>

            <div class="texto-principal">
                <h2 class="text-xl font-bold leading-tight"><?php echo $nombre_completo; ?></h2>
                <div class="flex gap-4 text-sm text-gray-600 mt-1">
                    <span><strong id="contador-seguidores"><?php echo $seguidores; ?></strong> seguidores</span>
                    <span><strong id="contador-seguidos"><?php echo $seguidos; ?></strong> seguidos</span>
                </div>
                <input type="file" id="input-foto-perfil" class="hidden" accept="image/*">
            </div>
        </div>
    <div class="perfiles flex justify-around border-b">
        <a href="perfil.php?tab=personal" class="perso p-2 <?php echo $tab == 'personal' ? 'active-tab' : ''; ?>">Personal</a>
        <a href="perfil.php?tab=contenido" class="cont p-2 <?php echo $tab == 'contenido' ? 'active-tab' : ''; ?>">Contenido</a>
    </div>

    <div class="mt-6">
        <h3 class="px-6 text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Mis Fotos (<?= ucfirst($tab) ?>)</h3>
        <div class="relative w-full overflow-hidden"> 
            <button onclick="window.deslizarCarrusel(-1)" class="absolute left-2 top-1/2 -translate-y-1/2 z-40 bg-white/70 hover:bg-white rounded-full p-2 shadow-md">
                <ion-icon name="chevron-back-outline" style="font-size: 24px; color: black;"></ion-icon>
            </button>
            <div id="carrusel-fotos" class="flex gap-2 p-2">
                <?php if ($res_propias->num_rows > 0): 
                    while($p = $res_propias->fetch_assoc()): ?>
                    <div class="min-w-[140px] h-[200px] rounded-2xl overflow-hidden bg-gray-100 shadow-md flex-shrink-0 cursor-pointer hover:scale-105 transition-all" 
                         onclick="abrirDetalle(<?= $p['id'] ?>)"> 
                        <?php if (strpos($p['tipo_archivo'], 'video') !== false): ?>
                            <video src="<?= $p['ruta_archivo'] ?>" class="w-full h-full object-cover"></video>
                        <?php else: ?>
                            <img src="<?= $p['ruta_archivo'] ?>" class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>
                <?php endwhile; else: ?>
                    <p class="text-gray-400 text-sm italic ml-4">No hay publicaciones en esta sección.</p>
                <?php endif; ?>
            </div>
            <button onclick="window.deslizarCarrusel(1)" class="absolute right-2 top-1/2 -translate-y-1/2 z-40 bg-white/70 hover:bg-white rounded-full p-2 shadow-md">
                <ion-icon name="chevron-forward-outline" class="text-xl"></ion-icon>
            </button>
        </div>
    </div>

    <hr class="my-8 border-gray-100 mx-6">

    <div class="px-6 pb-28">
        <h3 class="text-xs font-bold text-gray-400 uppercase mb-4 flex items-center gap-2">
            <ion-icon name="repeat-outline" class="text-green-500 text-lg"></ion-icon> Compartidos 
        </h3>
        
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <?php if ($res_reposts->num_rows > 0): 
                while($r = $res_reposts->fetch_assoc()): ?>
                <div class="relative group bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                    <div class="relative aspect-square" onclick="abrirDetalle(<?= $r['id'] ?>)">
                        <?php if (strpos($r['tipo_archivo'], 'video') !== false): ?>
                            <video src="<?= $r['ruta_archivo'] ?>" class="w-full h-full object-cover"></video>
                        <?php else: ?>
                            <img src="<?= $r['ruta_archivo'] ?>" class="w-full h-full object-cover">
                        <?php endif; ?>
                        
                        <div class="absolute top-2 left-2 bg-green-500/90 text-white p-1.5 rounded-full text-xs shadow-lg">
                            <ion-icon name="repeat-outline"></ion-icon>
                        </div>
                    </div>

                    <button onclick="event.stopPropagation(); window.eliminarRepublicacion(<?= $r['repost_id'] ?>)" 
                            class="absolute top-2 right-2 bg-red-500/80 hover:bg-red-600 text-white p-1.5 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                        <ion-icon name="trash-outline"></ion-icon>
                    </button>

                    <div class="p-2 text-[10px] text-gray-500 text-center bg-gray-50">
                        Compartido el <?= date('d/m', strtotime($r['fecha'])) ?>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <div class="col-span-2 text-center py-10 text-gray-400 text-sm">No hay contenido compartido.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="iconos-inferiores">
        <ion-icon name="home-outline" class="icon-gradient" onclick="window.location.href='cuenta.php'"></ion-icon>
        <ion-icon name="search-outline" class="icon-gradient" onclick="window.location.href='busqueda.php'"></ion-icon>
        <ion-icon name="chatbubble-outline" class="icon-gradient" onclick="window.location.href='http://localhost:3000'"></ion-icon>
        <ion-icon name="person-outline" class="icon-gradient active-icon" onclick="window.location.href='perfil.php'"></ion-icon>
    </div>
</div>

<div id="modal-detalle" class="hidden fixed inset-0 bg-black bg-opacity-80 z-[9999] flex items-center justify-center" onclick="cerrarDetalle(event)">
    <div class="bg-white w-full max-w-4xl h-[80vh] rounded-lg overflow-hidden" onclick="event.stopPropagation()">
        <div id="contenido-detalle" class="flex-1 overflow-y-auto overflow-x-hidden bg-white"></div>
    </div>
</div>

<script src="scripts.js"></script>
<script src="publicacion.js"></script> 
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<script>
async function actualizarContadores() {
    try {
        const response = await fetch('contador_seguidos_personal.php');
        const data = await response.json();
        if (data.seguidores !== undefined) {
            document.getElementById('contador-seguidores').textContent = data.seguidores;
        }
        if (data.seguidos !== undefined) {
            document.getElementById('contador-seguidos').textContent = data.seguidos;
        }
    } catch (err) {
        console.error("Error actualizando contadores:", err);
    }
}
document.addEventListener('DOMContentLoaded', () => {
    actualizarContadores();
    setInterval(actualizarContadores, 10000);
});
</script>
</body>
</html>