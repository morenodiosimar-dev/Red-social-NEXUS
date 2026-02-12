<?php
// Iniciar sesión solo si no hay una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = getenv("DB_HOST") ?: "127.0.0.1";
$username = getenv("DB_USERNAME") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$dbname = getenv("DB_DATABASE") ?: "nexus_db";
$port = getenv("DB_PORT") ?: 3306;

// Soporte para variables específicas de Railway (si usan nombres diferentes)
if (getenv("MYSQLHOST")) {
    $servername = getenv("MYSQLHOST");
    $username = getenv("MYSQLUSER");
    $password = getenv("MYSQLPASSWORD");
    $dbname = getenv("MYSQLDATABASE");
    $port = getenv("MYSQLPORT");
}

// Validación: Si estamos en producción (no localhost) y no hay credenciales, detener.
// Asumimos producción si existe alguna variable de entorno típica o si DB_HOST no es localhost
$is_production = getenv("RAILWAY_ENVIRONMENT") || getenv("RAILWAY_STATIC_URL") || (getenv("DB_HOST") && getenv("DB_HOST") !== "127.0.0.1");

if ($is_production && empty($password) && empty($username)) {
    // Fail fast 500 error en lugar de timeout 502
    http_response_code(500);
    die("Error de Configuración: Variables de entorno de base de datos no detectadas. Por favor configure MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE en Railway.");
}

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_errno) {
    http_response_code(500);
    // En producción no mostrar detalles sensibles, solo error genérico o loguearlo
    error_log("MySQL Connection Error: " . $conn->connect_error);
    die("Error de conexión a la base de datos (Ver logs)");
}
?>