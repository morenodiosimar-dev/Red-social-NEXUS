<?php
// Validador de estructura de base de datos para migración de chat
header('Content-Type: text/plain; charset=utf-8');

$conn = new mysqli("127.0.0.1", "root", "", "nexus_db", 3306);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error . "\n");
}

echo "=== VALIDACIÓN DE BASE DE DATOS NEXUS_DB ===\n\n";

// 1. Verificar tabla usuarios
echo "1. TABLA USUARIOS:\n";
$result = $conn->query("DESCRIBE usuarios");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "   - {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']}\n";
    }
} else {
    echo "   ❌ ERROR: Tabla usuarios no encontrada\n";
}
echo "\n";

// 2. Verificar tabla mensajes
echo "2. TABLA MENSAJES:\n";
$result = $conn->query("DESCRIBE mensajes");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "   - {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']}\n";
    }
    
    // Contar mensajes existentes
    $count = $conn->query("SELECT COUNT(*) as total FROM mensajes")->fetch_assoc()['total'];
    echo "   📊 Total mensajes: $count\n";
} else {
    echo "   ❌ ERROR: Tabla mensajes no encontrada\n";
    echo "   🔄 Creando tabla mensajes...\n";
    
    $create_sql = "
    CREATE TABLE mensajes (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        sala VARCHAR(50) NOT NULL,
        usuario BIGINT NOT NULL,
        mensaje TEXT NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        leido TINYINT(1) DEFAULT 0,
        INDEX idx_sala_fecha (sala, fecha),
        INDEX idx_usuario (usuario),
        FOREIGN KEY (usuario) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_sql)) {
        echo "   ✅ Tabla mensajes creada exitosamente\n";
    } else {
        echo "   ❌ Error creando tabla: " . $conn->error . "\n";
    }
}
echo "\n";

// 3. Verificar otras tablas relevantes
echo "3. OTRAS TABLAS RELEVANTES:\n";
$tables = ['publicaciones', 'comentarios', 'reacciones', 'notificaciones'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "   ✅ $table existe\n";
    } else {
        echo "   ⚠️  $table no encontrada\n";
    }
}
echo "\n";

// 4. Verificar datos de muestra
echo "4. DATOS DE MUESTRA:\n";
$usuarios_count = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
echo "   👥 Total usuarios: $usuarios_count\n";

if ($usuarios_count > 0) {
    $sample = $conn->query("SELECT id, nombre, apellido, foto_perfil FROM usuarios LIMIT 3");
    while ($row = $sample->fetch_assoc()) {
        echo "   - ID: {$row['id']}, Nombre: {$row['nombre']} {$row['apellido']}, Foto: " . ($row['foto_perfil'] ?: 'default.png') . "\n";
    }
}

// 5. Verificar configuración de sesión
echo "\n5. CONFIGURACIÓN DE SESIÓN:\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   Session Save Path: " . session_save_path() . "\n";
echo "   Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "\n";

$conn->close();

echo "\n=== VALIDACIÓN COMPLETADA ===\n";
?>
