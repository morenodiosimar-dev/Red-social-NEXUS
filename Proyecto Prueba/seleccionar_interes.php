<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'conn.php';

// Obtener user_id de la URL o de la sesión
$user_id = $_GET['user_id'] ?? $_SESSION['usuario_id'] ?? null;

// Debug: mostrar qué recibimos
error_log("Debug seleccionar_interes.php - GET user_id: " . ($_GET['user_id'] ?? 'null'));
error_log("Debug seleccionar_interes.php - SESSION user_id: " . ($_SESSION['usuario_id'] ?? 'null'));
error_log("Debug seleccionar_interes.php - user_id final: " . ($user_id ?? 'null'));

if (!$user_id) {
    error_log("Error: No hay user_id, redirigiendo a index.html");
    header("Location: index.php");
    exit();
}

// Obtener datos del usuario para mostrar en la página
$stmt_user = $conn->prepare("SELECT nombre, apellido, correo FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$usuario = $result_user->fetch_assoc();
$stmt_user->close();

// Guardar en sesión para uso posterior
$_SESSION['usuario_id'] = $user_id;
$_SESSION['nombre'] = $usuario['nombre'];
$_SESSION['apellido'] = $usuario['apellido'];
$_SESSION['correo'] = $usuario['correo'];

// Verificar si el usuario ya tiene intereses guardados (solo para nuevos usuarios)
$checkUserInterests = $conn->prepare("SELECT COUNT(*) as count FROM user_interests WHERE user_id = ?");
$checkUserInterests->bind_param("i", $user_id);
$checkUserInterests->execute();
$resultCheck = $checkUserInterests->get_result();
$userInterestsCount = $resultCheck->fetch_assoc()['count'];
$checkUserInterests->close();

// Si el usuario ya tiene intereses, redirigir a su cuenta
if ($userInterestsCount > 0) {
    header("Location: cuenta.php");
    exit();
}

// Verificar si existe la columna category en la tabla interes, si no, agregarla
$checkColumn = $conn->query("SHOW COLUMNS FROM interes LIKE 'category'");
if ($checkColumn->num_rows == 0) {
    // Intentar agregar la columna category
    if (!$conn->query("ALTER TABLE interes ADD COLUMN category VARCHAR(100) AFTER icon")) {
        die("Error: No se pudo agregar la columna 'category' a la tabla 'interes'.");
    }
}

// Verificar si la tabla tiene datos, si no, mostrar mensaje con enlace
$checkData = $conn->query("SELECT COUNT(*) as count FROM interes");
$dataCount = $checkData->fetch_assoc()['count'];
// Si no hay datos en la tabla, usar intereses por defecto
if ($dataCount == 0) {
    // Intereses por defecto si la tabla está vacía
    $categories = [
        '🎬 Entretenimiento' => [
            ['id' => 1, 'name' => 'Películas', 'icon' => 'film-outline'],
            ['id' => 2, 'name' => 'Series de TV', 'icon' => 'tv-outline'],
            ['id' => 3, 'name' => 'Música', 'icon' => 'musical-notes-outline'],
            ['id' => 4, 'name' => 'Videojuegos', 'icon' => 'game-controller-outline'],
            ['id' => 5, 'name' => 'Libros / Lectura', 'icon' => 'library-outline'],
            ['id' => 6, 'name' => 'Podcasts', 'icon' => 'mic-outline']
        ],
        '🌍 Cultura y estilo de vida' => [
            ['id' => 7, 'name' => 'Viajes', 'icon' => 'airplane-outline'],
            ['id' => 8, 'name' => 'Gastronomía / Recetas', 'icon' => 'restaurant-outline'],
            ['id' => 9, 'name' => 'Moda', 'icon' => 'shirt-outline'],
            ['id' => 10, 'name' => 'Arte / Diseño', 'icon' => 'color-palette-outline'],
            ['id' => 11, 'name' => 'Fotografía', 'icon' => 'camera-outline'],
            ['id' => 12, 'name' => 'Historia', 'icon' => 'book-outline']
        ],
        '💪 Bienestar' => [
            ['id' => 13, 'name' => 'Salud y fitness', 'icon' => 'barbell-outline'],
            ['id' => 14, 'name' => 'Nutrición', 'icon' => 'nutrition-outline'],
            ['id' => 15, 'name' => 'Meditación / Mindfulness', 'icon' => 'leaf-outline'],
            ['id' => 16, 'name' => 'Desarrollo personal', 'icon' => 'person-outline'],
            ['id' => 17, 'name' => 'Psicología', 'icon' => 'heart-outline']
        ],
        '💼 Negocios y aprendizaje' => [
            ['id' => 18, 'name' => 'Tecnología', 'icon' => 'laptop-outline'],
            ['id' => 19, 'name' => 'Ciencia', 'icon' => 'flask-outline'],
            ['id' => 20, 'name' => 'Finanzas / Inversiones', 'icon' => 'cash-outline'],
            ['id' => 21, 'name' => 'Marketing / Emprendimiento', 'icon' => 'trending-up-outline'],
            ['id' => 22, 'name' => 'Educación / Cursos', 'icon' => 'school-outline'],
            ['id' => 23, 'name' => 'Idiomas', 'icon' => 'language-outline']
        ],
        '⚽ Deportes' => [
            ['id' => 24, 'name' => 'Fútbol', 'icon' => 'football-outline'],
            ['id' => 25, 'name' => 'Baloncesto', 'icon' => 'basketball-outline'],
            ['id' => 26, 'name' => 'Béisbol', 'icon' => 'baseball-outline'],
            ['id' => 27, 'name' => 'Tenis', 'icon' => 'tennisball-outline'],
            ['id' => 28, 'name' => 'Deportes extremos', 'icon' => 'bicycle-outline'],
            ['id' => 29, 'name' => 'eSports', 'icon' => 'trophy-outline']
        ],
        '🌱 Intereses especiales' => [
            ['id' => 30, 'name' => 'Medio ambiente / Sostenibilidad', 'icon' => 'leaf-outline'],
            ['id' => 31, 'name' => 'Animales / Mascotas', 'icon' => 'paw-outline'],
            ['id' => 32, 'name' => 'Política / Actualidad', 'icon' => 'newspaper-outline'],
            ['id' => 33, 'name' => 'Voluntariado / Impacto social', 'icon' => 'people-outline'],
            ['id' => 34, 'name' => 'DIY / Manualidades', 'icon' => 'construct-outline']
        ]
    ];
    $totalRows = 34;
} else {
    // Obtener intereses agrupados por categoría (solo los que tienen categoría)
    $sql = "SELECT * FROM interes WHERE category IS NOT NULL AND category != '' ORDER BY category, name";
    $result = $conn->query($sql);

    if (!$result) {
        die("Error al obtener intereses: " . $conn->error);
    }

    $categories = [];
    $totalRows = 0;

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $category = trim($row['category'] ?? '');
            if (empty($category)) {
                $category = 'Otros';
            }
            $categories[$category][] = $row;
            $totalRows++;
        }
    }
}

// Si no hay categorías después de obtener los datos
if (empty($categories)) {
    // Verificar si hay datos sin categoría
    $checkWithoutCategory = $conn->query("SELECT COUNT(*) as count FROM interes WHERE category IS NULL OR category = ''");
    $withoutCatData = $checkWithoutCategory->fetch_assoc();
    
    if ($withoutCatData['count'] > 0) {
        // Hay datos pero sin categoría, redirigir a setup
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Configuración Requerida - NEXUS</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(130deg, rgb(255, 97, 242), rgb(183, 2, 255), rgb(115, 45, 245));
                    min-height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    padding: 20px;
                }
                .message-box {
                    background: white;
                    padding: 40px;
                    border-radius: 20px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                    text-align: center;
                    max-width: 600px;
                }
                .message-box h2 {
                    color: #333;
                    margin-bottom: 20px;
                }
                .message-box p {
                    color: #666;
                    margin-bottom: 30px;
                    line-height: 1.6;
                }
                .btn-setup {
                    display: inline-block;
                    padding: 15px 30px;
                    background: linear-gradient(130deg, rgb(255, 97, 242), rgb(183, 2, 255));
                    color: white;
                    text-decoration: none;
                    border-radius: 30px;
                    font-weight: 600;
                    font-size: 16px;
                    transition: transform 0.2s, box-shadow 0.2s;
                    box-shadow: 0 5px 20px rgba(183, 2, 255, 0.4);
                }
                .btn-setup:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 8px 25px rgba(183, 2, 255, 0.5);
                }
            </style>
        </head>
        <body>
            <div class="message-box">
                <h2>⚠️ Intereses sin Categorías</h2>
                <p>Se encontraron <strong><?php echo $withoutCatData['count']; ?></strong> intereses en la base de datos pero <strong>ninguno tiene categoría asignada</strong>.</p>
                <p>Es necesario ejecutar el script de configuración para asignar las categorías correctamente.</p>
                <a href="setuo_interests.php" class="btn-setup">Ejecutar Configuración</a>
            </div>
        </body>
        </html>
        <?php
        exit();
    } else {
        // No hay datos en absoluto
        die("No se encontraron intereses en la base de datos. Por favor ejecuta 'setuo_interests.php' para poblar la base de datos. <a href='setuo_interests.php'>Ejecutar ahora</a>");
    }
}

// Debug: mostrar cuántas categorías se encontraron (puedes comentar esto después)
// echo "<!-- Debug: Se encontraron " . count($categories) . " categorías con un total de {$totalRows} intereses -->";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecciona tus Intereses - NEXUS</title>
    <link rel="stylesheet" href="intereses.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

    <div class="container-intereses">
        <h1 class="title-intereses">¡Hola, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?>!</h1>
        <p class="subtitle-intereses">Selecciona al menos 3 temas que te gusten para personalizar tu experiencia.</p>

        <form action="guardar_intereses.php" method="POST" id="form-intereses">
            
            <?php foreach ($categories as $categoryName => $interests): ?>
                <div class="category-section">
                    <h2 class="category-title"><?php echo htmlspecialchars($categoryName); ?></h2>
                    <div class="interests-grid">
                        <?php foreach ($interests as $interest): ?>
                            <label class="interest-card">
                                <input type="checkbox" name="intereses[]" value="<?php echo $interest['id']; ?>" class="interest-checkbox">
                                <ion-icon name="<?php echo htmlspecialchars($interest['icon'] ?: 'star-outline'); ?>"></ion-icon>
                                <span><?php echo htmlspecialchars($interest['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="btn-container">
                <div class="counter" id="counter">0 seleccionados</div>
                <button type="submit" class="btn-submit" id="btn-submit" disabled>Continuar</button>
            </div>
        </form>
    </div>

    <script>
        // Función para manejar la selección de intereses
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.interest-card');
            const counter = document.getElementById('counter');
            const submitBtn = document.getElementById('btn-submit');

            cards.forEach(card => {
                card.addEventListener('click', function() {
                    const checkbox = this.querySelector('.interest-checkbox');
                    
                    setTimeout(() => {
                        if (checkbox.checked) {
                            this.classList.add('selected');
                        } else {
                            this.classList.remove('selected');
                        }
                        updateCounter();
                    }, 10);
                });
            });

            function updateCounter() {
                const checked = document.querySelectorAll('.interest-checkbox:checked');
                const count = checked.length;
                
                counter.textContent = `${count} seleccionados`;
                
                if (count >= 3) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = `Continuar (${count})`;
                } else {
                    submitBtn.disabled = true;
                    submitBtn.textContent = `Continuar (${count}/3)`;
                }
            }

            // Inicializar contador
            updateCounter();
        });
    </script>
</body>
</html>