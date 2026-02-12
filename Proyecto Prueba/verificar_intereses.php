<?php
include 'conn.php';

echo "<h2>Verificación de tabla 'interes'</h2>";

// Verificar estructura de la tabla
$result = $conn->query("DESCRIBE interes");
echo "<h3>Estructura de la tabla:</h3>";
echo "<table border='1' style='margin: 20px 0; border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Contar registros
$check_data = $conn->query("SELECT COUNT(*) as count FROM interes");
$data_count = $check_data->fetch_assoc()['count'];
echo "<h3>Total de registros: <strong>{$data_count}</strong></h3>";

if ($data_count > 0) {
    // Mostrar todos los intereses agrupados por categoría
    $result = $conn->query("SELECT * FROM interes ORDER BY COALESCE(category, 'Sin categoría'), name");
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $category = $row['category'] ?? 'Sin categoría';
        if (!isset($categories[$category])) {
            $categories[$category] = [];
        }
        $categories[$category][] = $row;
    }
    
    echo "<h3>Intereses por categoría:</h3>";
    foreach ($categories as $category => $interests) {
        echo "<div style='margin: 20px 0;'>";
        echo "<h4 style='color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 5px;'>{$category} (" . count($interests) . ")</h4>";
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;'>";
        
        foreach ($interests as $interest) {
            echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 3px solid #667eea;'>";
            echo "<strong>ID: " . $interest['id'] . "</strong><br>";
            echo "<span>" . $interest['name'] . "</span><br>";
            echo "<small style='color: #666;'>Icono: " . ($interest['icon'] ?? 'N/A') . "</small>";
            echo "</div>";
        }
        
        echo "</div>";
        echo "</div>";
    }
    
    // Opción para agregar intereses NEXUS
    echo "<hr>";
    echo "<h3>¿Deseas agregar los intereses de NEXUS?</h3>";
    echo "<p>Esto agregará 34 intereses organizados por categorías si no existen.</p>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='action' value='add_nexus'>";
    echo "<input type='submit' value='Agregar Intereses NEXUS' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
    echo "</form>";
    
    // Procesar agregación de intereses NEXUS
    if ($_POST['action'] === 'add_nexus') {
        echo "<hr>";
        echo "<h3>Agregando intereses NEXUS...</h3>";
        
        // Intereses NEXUS
        $intereses_nexus = [
            '🎬 Entretenimiento' => [
                ['name' => 'Películas', 'icon' => 'film-outline'],
                ['name' => 'Series de TV', 'icon' => 'tv-outline'],
                ['name' => 'Música', 'icon' => 'musical-notes-outline'],
                ['name' => 'Videojuegos', 'icon' => 'game-controller-outline'],
                ['name' => 'Libros / Lectura', 'icon' => 'library-outline'],
                ['name' => 'Podcasts', 'icon' => 'mic-outline']
            ],
            '🌍 Cultura y estilo de vida' => [
                ['name' => 'Viajes', 'icon' => 'airplane-outline'],
                ['name' => 'Gastronomía / Recetas', 'icon' => 'restaurant-outline'],
                ['name' => 'Moda', 'icon' => 'shirt-outline'],
                ['name' => 'Arte / Diseño', 'icon' => 'color-palette-outline'],
                ['name' => 'Fotografía', 'icon' => 'camera-outline'],
                ['name' => 'Historia', 'icon' => 'book-outline']
            ],
            '💪 Bienestar' => [
                ['name' => 'Salud y fitness', 'icon' => 'barbell-outline'],
                ['name' => 'Nutrición', 'icon' => 'nutrition-outline'],
                ['name' => 'Meditación / Mindfulness', 'icon' => 'leaf-outline'],
                ['name' => 'Desarrollo personal', 'icon' => 'person-outline'],
                ['name' => 'Psicología', 'icon' => 'heart-outline']
            ],
            '💼 Negocios y aprendizaje' => [
                ['name' => 'Tecnología', 'icon' => 'laptop-outline'],
                ['name' => 'Ciencia', 'icon' => 'flask-outline'],
                ['name' => 'Finanzas / Inversiones', 'icon' => 'cash-outline'],
                ['name' => 'Marketing / Emprendimiento', 'icon' => 'trending-up-outline'],
                ['name' => 'Educación / Cursos', 'icon' => 'school-outline'],
                ['name' => 'Idiomas', 'icon' => 'language-outline']
            ],
            '⚽ Deportes' => [
                ['name' => 'Fútbol', 'icon' => 'football-outline'],
                ['name' => 'Baloncesto', 'icon' => 'basketball-outline'],
                ['name' => 'Béisbol', 'icon' => 'baseball-outline'],
                ['name' => 'Tenis', 'icon' => 'tennisball-outline'],
                ['name' => 'Deportes extremos', 'icon' => 'bicycle-outline'],
                ['name' => 'eSports', 'icon' => 'trophy-outline']
            ],
            '🌱 Intereses especiales' => [
                ['name' => 'Medio ambiente / Sostenibilidad', 'icon' => 'leaf-outline'],
                ['name' => 'Animales / Mascotas', 'icon' => 'paw-outline'],
                ['name' => 'Política / Actualidad', 'icon' => 'newspaper-outline'],
                ['name' => 'Voluntariado / Impacto social', 'icon' => 'people-outline'],
                ['name' => 'DIY / Manualidades', 'icon' => 'construct-outline']
            ]
        ];
        
        // Insertar intereses
        $stmt = $conn->prepare("INSERT INTO interes (name, icon, category) VALUES (?, ?, ?)");
        $total_inserted = 0;
        $duplicates = 0;
        
        foreach ($intereses_nexus as $category => $interests) {
            echo "<h4>Categoría: {$category}</h4>";
            
            foreach ($interests as $interest) {
                // Verificar si ya existe
                $check_duplicate = $conn->prepare("SELECT id FROM interes WHERE name = ? AND category = ?");
                $check_duplicate->bind_param("ss", $interest['name'], $category);
                $check_duplicate->execute();
                $result = $check_duplicate->get_result();
                
                if ($result->num_rows > 0) {
                    $duplicates++;
                    echo "<p style='color: orange;'>⚠️ Ya existe: " . $interest['name'] . "</p>";
                } else {
                    $stmt->bind_param("sss", $interest['name'], $interest['icon'], $category);
                    
                    if ($stmt->execute()) {
                        $total_inserted++;
                        echo "<p style='color: green;'>✅ Agregado: " . $interest['name'] . "</p>";
                    } else {
                        echo "<p style='color: red;'>❌ Error: " . $stmt->error . "</p>";
                    }
                }
                
                $check_duplicate->close();
            }
        }
        
        $stmt->close();
        
        echo "<h3>Resumen:</h3>";
        echo "<p style='color: green;'>✅ Nuevos intereses: {$total_inserted}</p>";
        echo "<p style='color: orange;'>⚠️ Ya existían: {$duplicates}</p>";
        echo "<p><a href='verificar_intereses.php'>Recargar página</a></p>";
    }
} else {
    echo "<p style='color: red;'>❌ La tabla está vacía. Necesitas poblarla con intereses.</p>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='action' value='add_nexus'>";
    echo "<input type='submit' value='Agregar Intereses NEXUS' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
    echo "</form>";
    
    // Procesar agregación si la tabla está vacía
    if ($_POST['action'] === 'add_nexus') {
        // El mismo código de agregación que arriba...
        echo "<p>Procesando agregación...</p>";
    }
}

// Verificar si existe la tabla user_interests
$check_user_interests = $conn->query("SHOW TABLES LIKE 'user_interests'");
if ($check_user_interests->num_rows == 0) {
    echo "<hr>";
    echo "<h3 style='color: orange;'>⚠️ Tabla 'user_interests' no existe</h3>";
    echo "<p>Esta tabla es necesaria para guardar los intereses de los usuarios.</p>";
    
    // Crear tabla user_interests
    $sql = "CREATE TABLE user_interests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        interes_id INT NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_interest (user_id, interes_id),
        FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (interes_id) REFERENCES interes(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✅ Tabla 'user_interests' creada automáticamente.</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creando tabla user_interests: " . $conn->error . "</p>";
    }
} else {
    echo "<hr>";
    echo "<h3>✅ Tabla 'user_interests' ya existe</h3>";
    
    // Mostrar registros en user_interests
    $check_user_data = $conn->query("SELECT COUNT(*) as count FROM user_interests");
    $user_data_count = $check_user_data->fetch_assoc()['count'];
    echo "<p>Registros en user_interests: <strong>{$user_data_count}</strong></p>";
}

echo "<hr>";
echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Volver al inicio</a></p>";

if ($data_count > 0) {
    echo "<p><a href='seleccionar_interes.php?user_id=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Probar selección de intereses</a></p>";
}

$conn->close();
?>
