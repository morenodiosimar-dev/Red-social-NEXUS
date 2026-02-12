<?php
// Iniciar sesión solo si no hay una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = getenv('MYSQLHOST') ?: "127.0.0.1";
$username = getenv('MYSQLUSER') ?: "root";
$password = getenv('MYSQLPASSWORD') ?: "";
$dbname = getenv('MYSQLDATABASE') ?: "nexus_db";
$port = getenv('MYSQLPORT') ?: 3306;


$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_errno) {
    die("Error en la conexión a MySQL: " . $conn->connect_error);
}
?>