<?php
session_start();

// DATOS DE RAILWAY (Copia y pega esto)
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "BpFRhFTLghAcqTRozKXkQyajMlYVqZCw";
$dbname = "railway"; // <-- En Railway tu BD se llama así
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

$usuario_id = $_SESSION['usuario_id'];

$sql = "
SELECT n.*, u.nombre, u.apellido, u.foto_perfil
FROM notificaciones n
JOIN usuarios u ON u.id = n.emisor_id
WHERE n.usuario_id = ?
ORDER BY n.fecha DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo '<p class="text-gray-400 text-center p-6">No tienes notificaciones.</p>';
    exit;
}

while ($n = $res->fetch_assoc()) {

    switch ($n['tipo']) {
        case 'like': $texto = 'le dio ❤️ a tu publicación'; break;
        case 'comentario': $texto = 'comentó tu publicación'; break;
        case 'seguir': $texto = 'empezó a seguirte'; break;
        default: $texto = 'tienes una notificación';
    }

    $bg = $n['leido'] ? 'bg-white' : 'bg-purple-50';
    ?>

    <div class="flex gap-4 p-3 rounded-lg <?= $bg ?> hover:bg-gray-50 transition cursor-pointer">
        
        <img src="<?= $n['foto_perfil'] ?: 'placeholder.png' ?>"
             class="w-12 h-12 rounded-full object-cover shadow"
             onerror="this.src='placeholder.png'">

        <div class="flex-1">
            <p class="text-sm text-gray-800 leading-snug">
                <span class="font-bold"><?= $n['nombre'].' '.$n['apellido'] ?></span>
                <?= $texto ?>
            </p>
            <p class="text-xs text-gray-400 mt-1">🕒 <?= $n['fecha'] ?></p>
        </div>

        <?php if(!$n['leido']): ?>
            <span class="w-2 h-2 bg-purple-500 rounded-full mt-2"></span>
        <?php endif; ?>
    </div>

<?php } ?>
