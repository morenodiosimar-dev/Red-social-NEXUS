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

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_errno) {
    die("Error en la conexión a MySQL: " . $conn->connect_error);
}
?>