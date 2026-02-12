<?php
session_start();
// Si no hay sesión, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// --- INICIO INTEGRACIÓN CHAT ---
require_once __DIR__ . '/INTEGRATION_HELPER.php';
// REEMPLAZA CON TU APP_KEY DE RAILWAY (La que empieza con base64:...)
$chatHelper = new ChatIntegration(
    'base64:ldevSFYVK4zAsSkDyuRIW98rVJ1hdWfnyGVF68HqinA=',
    'https://soothing-flexibility-production.up.railway.app'
);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus - Buscar</title>
    <link rel="stylesheet" href="busqueda.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

    <div class="busqueda" id="form-busqueda">
        <div class="superior">
            <div class="nombre-red">Nexus</div>
        </div>

        <div class="centro-busqueda">
            <div class="buscar-info">
                <input type="text" placeholder="¿A quién buscas hoy?" id="input-buscar" name="buscar">
                <ion-icon name="search-outline" class="icon-buscar"></ion-icon>
            </div>
        </div>
        
        <div id="resultados-busqueda" class="w-full max-w-[600px] mt-4 px-4"></div>
    </div>

        <div class="iconos-inferiores">
            <ion-icon name="home-outline" onclick="window.location.href='cuenta.php'" class="icon-gradient"></ion-icon>
            <ion-icon name="search-outline" class="icon-gradient  active-icon"onclick="window.location.href='busqueda.php'" ></ion-icon>
            <div class="icont" onclick="
                if(window.NexusChatConfig) {
                    const { user_id, signature, endpoint } = window.NexusChatConfig;
                    window.location.href = `${endpoint}?uid=${user_id}&sig=${signature}`;
                } else {
                    window.location.href='https://soothing-flexibility-production.up.railway.app';
                }
            ">
                <ion-icon name="chatbubble-outline"></ion-icon>
            </div>
            <ion-icon name="person-outline" onclick="window.location.href='Perfil.php'" class="icon-gradient"></ion-icon>
        </div>
<script src="scripts.js">
    </script>
    <script  type = "module"  src = " https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js " > </script> 
    <script nomodule src= " https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js " > </script>
</body>
</html>