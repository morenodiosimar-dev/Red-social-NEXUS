<?php
session_start();
$conn = new mysqli("127.0.0.1", "root", "", "nexus_db", 3306);

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit;
}

$usuario_id = $_SESSION['usuario_id']; // quien visita
$id_perfil = $_GET['id'] ?? null;      // perfil visitado

if (!$id_perfil || $id_perfil == $usuario_id) {
    header("Location: perfil.php"); // redirige a tu propio perfil
    exit;
}

// Datos del usuario visitado
$stmt = $conn->prepare("SELECT nombre, apellido, foto_perfil FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_perfil);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

if (!$user_data) die("Usuario no encontrado");

$nombre_visitado = $user_data['nombre'] . " " . $user_data['apellido'];
$foto_db = $user_data['foto_perfil'];

$foto_perfil = null;

if (!empty($foto_db)) {
    // Si la base de datos ya trae "uploads/", la usamos directo. Si no, se la agregamos.
    $ruta_final = (strpos($foto_db, 'uploads/') === 0) ? $foto_db : 'uploads/' . $foto_db;
    
    // Verificamos si el archivo existe físicamente y no es el valor por defecto
    if (file_exists($ruta_final) && $foto_db !== 'default.png') {
        $foto_perfil = $ruta_final;
    }
}

// Verificar si ya sigue al usuario
$stmt_check = $conn->prepare("SELECT id FROM seguidores WHERE seguidor_id=? AND seguido_id=?");
$stmt_check->bind_param("ii", $usuario_id, $id_perfil);
$stmt_check->execute();
$ya_sigue = $stmt_check->get_result()->num_rows > 0;

// Contadores iniciales del perfil visitado
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id=?");
$stmt_count->bind_param("i", $id_perfil);
$stmt_count->execute();
$seguidores = $stmt_count->get_result()->fetch_assoc()['total'];

$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id=?");
$stmt_count->bind_param("i", $id_perfil);
$stmt_count->execute();
$seguidos = $stmt_count->get_result()->fetch_assoc()['total'];

// Pestaña activa
$tab = $_GET['tab'] ?? 'personal';

// CONSULTAS DE CONTENIDO (Igual que en perfil.php pero con ID_PERFIL)
// 1. Publicaciones del usuario visitado
$query_propias = "SELECT id, ruta_archivo, tipo_archivo FROM publicaciones 
                  WHERE usuario_id = $id_perfil 
                  AND tipo_perfil = '$tab'
                  ORDER BY fecha_creacion DESC";
$res_propias = $conn->query($query_propias);

// 2. Republicaciones del usuario visitado
$query_reposts = "SELECT p.id, p.ruta_archivo, p.tipo_archivo, r.fecha 
                  FROM republicaciones r
                  JOIN publicaciones p ON r.publicacion_id = p.id
                  WHERE r.usuario_id = $id_perfil
                  ORDER BY r.fecha DESC";
$res_reposts = $conn->query($query_reposts);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $nombre_visitado; ?> - Nexus</title>
<link rel="stylesheet" href="perfil.css">
<link rel="icon" href="logo.jpeg">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
#btn-seguir{
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
#btn-seguir.siguiendo{
    background: linear-gradient(45deg, #ff00cc, #3333ff);
    color: white;
}
</style>
</head>
<body class="bg-white">

<div class="perfil">
    <div class="perfil-usuario">
        <div class="superior" >
            <div class="nombre-red" style="font-size:35px;font-weight:bold;background:linear-gradient(45deg,#ff00cc,#3333ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Nexus</div>
        </div>

        <div class="flex items-center gap-4">
    <div class="w-20 h-20 flex-shrink-0">
        <?php if ($foto_perfil): ?>
            <img src="<?php echo $foto_perfil; ?>" 
                 class="w-full h-full rounded-full object-cover border-2 border-purple-500 shadow-sm">
        <?php else: ?>
            <ion-icon name="person-circle-outline" 
                      class="text-gray-300" 
                      style="font-size: 80px; line-height: 1;"></ion-icon>
        <?php endif; ?>
    </div>

    <div class="texto-principal">
        <h2 class="text-xl font-bold"><?php echo $nombre_visitado; ?></h2>
        <p class="text-gray-500 text-sm">Usuario de Nexus</p>
        <div class="flex gap-4 text-sm text-gray-600 mt-1">
            <span><strong id="contador-seguidores"><?php echo $seguidores; ?></strong> seguidores</span>
            <span><strong id="contador-seguidos"><?php echo $seguidos; ?></strong> seguidos</span>
        </div>
        <div class="flex gap-2 mt-2">
            <button id="btn-seguir"
                data-seguido="<?php echo $id_perfil; ?>"
                class="<?php echo $ya_sigue ? 'siguiendo bg-gray-400' : ''; ?>"
                onclick="ejecutarSeguir(<?php echo $id_perfil; ?>)">
                <?php echo $ya_sigue ? 'Siguiendo' : 'Seguir'; ?>
            </button>
            <button class="bg-gray-200 text-black px-4 py-1 rounded-full text-xs font-bold"
                onclick="window.location.href='http://localhost:3000?contacto=<?php echo urlencode($nombre_visitado); ?>'">
                Mensaje
            </button>
        </div>
    </div>
</div>
<!-- Cambie cosas para que el boton de seguir me lograra funcionalidad -->
        <div class="perfiles flex justify-around border-b mt-4">
            <a href="?id=<?php echo $id_perfil; ?>&tab=personal" class="perso p-2 <?php echo $tab=='personal'?'active-tab':'';?>">Personal</a>
            <a href="?id=<?php echo $id_perfil; ?>&tab=contenido" class="cont p-2 <?php echo $tab=='contenido'?'active-tab':'';?>">Contenido</a>
        </div>

        <!-- CARRUSEL DE FOTOS -->
        <div class="mt-6">
            <h3 class="px-6 text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Fotos (<?= ucfirst($tab) ?>)</h3>
            
            <div class="relative w-full overflow-hidden"> 
                <button onclick="window.deslizarCarrusel(-1)" class="absolute left-2 top-1/2 -translate-y-1/2 z-40 bg-white/70 hover:bg-white rounded-full p-2 shadow-md">
                    <ion-icon name="chevron-back-outline" style="font-size: 24px; color: black;"></ion-icon>
                </button>

                <div id="carrusel-fotos" class="flex gap-2 p-2">
                    <?php if ($res_propias && $res_propias->num_rows > 0): 
                        while($p = $res_propias->fetch_assoc()): 
                          $archivo = file_exists("uploads/".$p['ruta_archivo']) ? "uploads/".$p['ruta_archivo'] : $p['ruta_archivo'];
                    ?>
                        <div class="min-w-[140px] h-[200px] rounded-2xl overflow-hidden bg-gray-100 shadow-md flex-shrink-0 cursor-pointer hover:scale-105 transition-all" 
                             onclick="abrirDetalle(<?= $p['id'] ?>)"> 
                            <?php if (strpos($p['tipo_archivo'], 'video') !== false): ?>
                                <video src="<?= $archivo ?>" class="w-full h-full object-cover"></video>
                            <?php else: ?>
                                <img src="<?= $archivo ?>" class="w-full h-full object-cover">
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

        <!-- COMPARTIDOS -->
        <div class="px-6 pb-28">
            <h3 class="text-xs font-bold text-gray-400 uppercase mb-4 flex items-center gap-2">
                <ion-icon name="repeat-outline" class="text-green-500 text-lg"></ion-icon> Compartidos 
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <?php if ($res_reposts && $res_reposts->num_rows > 0): 
                    while($r = $res_reposts->fetch_assoc()): 
                        $archivo_r = file_exists("uploads/".$r['ruta_archivo']) ? "uploads/".$r['ruta_archivo'] : $r['ruta_archivo'];
                    ?>
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer" 
                         onclick="abrirDetalle(<?= $r['id'] ?>)">
                        <div class="relative aspect-square">
                            <?php if (strpos($r['tipo_archivo'], 'video') !== false): ?>
                                <video src="<?= $archivo_r ?>" class="w-full h-full object-cover"></video>
                            <?php else: ?>
                                <img src="<?= $archivo_r ?>" class="w-full h-full object-cover">
                            <?php endif; ?>
                            <div class="absolute top-2 left-2 bg-green-500/90 text-white p-1.5 rounded-full text-xs shadow-lg">
                                <ion-icon name="repeat-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="p-2 text-[10px] text-gray-500 text-center bg-gray-50">
                            Compartido el <?= date('d/m', strtotime($r['fecha'])) ?>
                        </div>
                    </div>
                <?php endwhile; else: ?>
                    <div class="col-span-2 text-center py-10 text-gray-400 text-sm">No hay contenido compartido.</div>
                <?php endif; ?>
            </div>
        </div>


    </div>

    <!-- Iconos inferiores -->
    <div class="iconos-inferiores fixed bottom-0 w-full bg-white border-t p-3 flex justify-around">
        <ion-icon name="home-outline" class="icon-gradient text-2xl" onclick="window.location.href='cuenta.php'"></ion-icon>
        <ion-icon name="search-outline" class="icon-gradient text-2xl" onclick="window.location.href='busqueda.php'"></ion-icon>
        <ion-icon name="chatbubble-outline" class="icon-gradient text-2xl" onclick="window.location.href='http://localhost:3000'"></ion-icon>
        <ion-icon name="person-outline" class="icon-gradient text-2xl" onclick="window.location.href='perfil.php'"></ion-icon>
    </div>
</div>

<!-- MODAL DETALLE -->
<div id="modal-detalle" 
     class="hidden fixed inset-0 bg-black bg-opacity-80 z-[9999] flex items-center justify-center" 
     onclick="cerrarDetalle(event)">
    <div class="bg-white w-full max-w-4xl h-[80vh] rounded-lg overflow-hidden relative" 
         onclick="event.stopPropagation()">
        <div id="contenido-detalle" class="flex-1 overflow-y-auto overflow-x-hidden bg-white h-full"></div>
    </div>
</div>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="scripts.js"></script>
<script src="publicacion.js"></script>

<script> //Nuevo script
function ejecutarSeguir(idDestino){
    const btn = document.getElementById('btn-seguir');
    const datos = new FormData();
    datos.append('seguido_id', idDestino);

    fetch('acciones_perfil.php',{
        method:'POST',
        body:datos
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.status==='follow'){
            btn.textContent='Siguiendo';
            btn.classList.add('bg-gray-400');
        } else if(res.status==='unfollow'){
            btn.textContent='Seguir';
            btn.classList.remove('bg-gray-400');
            // btn.classList.add('bg-[color-original]'); // Si se pierde el estilo
        }
        // Actualizar contadores
        const seg = document.getElementById('contador-seguidores');
        if(seg && res.total_seguidores !== undefined) seg.textContent = res.total_seguidores;
    });
}
</script>

</body>
</html>
