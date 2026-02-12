<?php
session_start();
// Error reporting en 0 para evitar que mensajes de error rompan el JSON o el HTML del modal
error_reporting(0);

$conn = new mysqli("127.0.0.1", "root", "", "nexus_db", 3306);

if (!isset($_GET['id'])) die("ID no recibido");

$post_id = intval($_GET['id']);
$user_id = $_SESSION['usuario_id'] ?? 0; 

// 1. Obtener los datos de la publicación y del autor
$query = "SELECT p.*, u.nombre, u.apellido, u.foto_perfil, r.id as repost_id, r.usuario_id as repost_user_id
          FROM publicaciones p 
          JOIN usuarios u ON p.usuario_id = u.id 
          LEFT JOIN republicaciones r ON r.publicacion_id = p.id AND r.usuario_id = $user_id
          WHERE p.id = $post_id";
$res = $conn->query($query);
$post = $res->fetch_assoc();

if (!$post) die("Error: No se encontró la publicación.");

$es_publicacion_propia = ($post['usuario_id'] == $user_id);
$es_republicacion_propia = ($post['repost_id'] && $post['repost_user_id'] == $user_id);
$mostrar_boton = ($es_publicacion_propia || $es_republicacion_propia);

// 2. Lógica de Reacciones
$likes_res = $conn->query("SELECT COUNT(*) as total FROM reacciones WHERE publicacion_id = $post_id");
$total_likes = ($likes_res) ? $likes_res->fetch_assoc()['total'] : 0;

$check_like = $conn->query("SELECT id FROM reacciones WHERE publicacion_id = $post_id AND usuario_id = $user_id");
$is_liked = ($check_like && $check_like->num_rows > 0);
?>

<div class="flex flex-col md:flex-row h-full max-h-[80vh] bg-white relative">
    <button type="button" onclick="window.cerrarModalDirecto()" 
            class="absolute top-4 right-4 z-[110] text-gray-500 hover:text-red-500 bg-white/90 rounded-full w-10 h-10 flex items-center justify-center shadow-md">
        <ion-icon name="close-outline" style="font-size: 32px;"></ion-icon>
    </button>
    
    <div class="md:w-3/5 bg-black flex items-center justify-center">
        <?php if (strpos($post['tipo_archivo'], 'image') !== false): ?>
            <img src="<?php echo $post['ruta_archivo']; ?>" class="max-w-full max-h-full object-contain">
        <?php else: ?>
            <video src="<?php echo $post['ruta_archivo']; ?>" controls class="max-w-full max-h-full"></video>
        <?php endif; ?>
    </div>

    <div class="md:w-2/5 flex flex-col h-full bg-white">
        
        <div class="p-4 border-b flex justify-between items-center">
            <div>
                <span class="font-bold text-sm"><?php echo htmlspecialchars($post['nombre'] . " " . $post['apellido']); ?></span>
            </div>
        </div>

        <div class="p-4 text-sm border-b bg-gray-50">
            <p><?php echo htmlspecialchars($post['caption']); ?></p>
        </div>

<div id="comentarios-lista-<?php echo $post_id; ?>" class="flex-grow overflow-y-auto p-4 space-y-3" style="max-height: 300px; scroll-behavior: smooth;">
    <?php
    $coms = $conn->query("SELECT c.*, u.nombre FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.publicacion_id = $post_id ORDER BY c.id ASC");
    while ($c = $coms->fetch_assoc()): 
        $es_mi_comentario = ($c['usuario_id'] == $user_id);
        $es_mi_muro = ($post['usuario_id'] == $user_id);
    ?>
    <div id="comentario-<?php echo $c['id']; ?>" class="flex justify-between items-start py-1">
        <p class="text-sm">
            <span class="font-bold text-black"><?php echo htmlspecialchars($c['nombre']); ?>:</span> 
            <span class="text-gray-800"><?php echo htmlspecialchars($c['contenido']); ?></span>
        </p>
        
        <?php if ($es_mi_comentario || $es_mi_muro): ?>
            <button onclick="eliminarElemento(<?php echo $c['id']; ?>, 'comentario')" 
                    class="text-red-500 hover:text-red-700 ml-2">
                <ion-icon name="trash-outline" style="font-size: 16px;"></ion-icon>
            </button>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>

        <div class="p-4 border-t">
            <div class="flex justify-between items-center mb-2">
                <div class="flex gap-4">
                    <ion-icon name="<?php echo $is_liked ? 'heart' : 'heart-outline'; ?>" 
                              id="modal-heart-<?php echo $post_id; ?>" 
                              onclick="reaccionarModal(<?php echo $post_id; ?>)"
                              style="color: <?php echo $is_liked ? 'red' : 'black'; ?>; font-size: 28px; cursor: pointer;">
                    </ion-icon>
                    
                    <ion-icon name="chatbubble-outline" 
                              onclick="document.getElementById('modal-input-com-<?php echo $post_id; ?>').focus()" 
                              style="font-size: 28px; cursor: pointer;">
                    </ion-icon>

                    <ion-icon name="repeat-outline" 
                              onclick="mandarRepublicacion(<?php echo $post_id; ?>)" 
                              style="font-size: 28px; color: #10b981; cursor: pointer;">
                    </ion-icon>
                </div>

                <?php if ($mostrar_boton): ?>
                    <button onclick="<?php echo $es_republicacion_propia ? "window.eliminarRepublicacion({$post['repost_id']})" : "eliminarElemento({$post_id}, 'publicacion')"; ?>" 
                            class="text-red-500 flex items-center gap-1 text-sm font-semibold">
                        <ion-icon name="trash-outline"></ion-icon> Borrar
                    </button>
                <?php endif; ?>
            </div>
            
            <p class="text-sm font-bold mb-3" id="modal-count-<?php echo $post_id; ?>"><?php echo $total_likes; ?> reacciones</p>
            
            <div class="flex gap-2">
                <input type="text" id="modal-input-com-<?php echo $post_id; ?>" 
                       placeholder="Añadir un comentario..." 
                       class="flex-grow border rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:border-purple-500" 
                       style="border-color: rgb(255, 97, 242);">
                
                <button onclick="enviarComentarioModal(<?php echo $post_id; ?>)" 
                        class="font-bold text-sm px-4 py-2 rounded-full text-white transition-all hover:scale-105" 
                        style="background: linear-gradient(130deg, rgb(255, 97, 242), rgb(183, 2, 255), rgb(115, 45, 245));">
                    Publicar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Esto asegura que si el modal es muy largo, el input sea visible
    setTimeout(() => {
        const lista = document.getElementById('comentarios-lista-<?php echo $post_id; ?>');
        if(lista) lista.scrollTop = lista.scrollHeight;
    }, 100);
</script>