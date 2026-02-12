<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NEXUS - Chat</title>
    <link rel="stylesheet" href="{{ asset('css/chat/index.css') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Icons FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Echo & Pusher -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.min.js"></script>
</head>

<body>

    <!-- MÓDULO 1: LISTA DE CHATS -->
    <div id="modulo-lista-chats">
        <div class="header-container" style="position: relative; width: 100%;">
            <a href="https://proyecto-prueba.com/cuenta" class="devolver" title="Volver">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="titulo">
                NEXUS Chat
            </div>
        </div>

        <div class="top-bar">
            <input type="text" id="search-users" class="busqueda" placeholder="Buscar usuarios...">
            <span class="Lupa"><i class="fas fa-search"></i></span>
        </div>

        <div class="lista-chats" id="contacts-list">
            <!-- Los items de chat se insertarán aquí dinámicamente -->
        </div>

        <!-- Navbar Inferior (Opcional según diseño) -->
        <div class="iconos-inferiores">
            <div class="icon active-icon"><i class="fas fa-comment"></i></div>
            <div class="icon" id="logout-btn" title="Salir"><i class="fas fa-sign-out-alt"></i></div>
        </div>
    </div>

    <!-- MÓDULO 2: MENSAJERÍA (Messenger) -->
    <!-- Inicialmente oculto, se muestra al abrir un chat -->
    <div id="chat-interface" class="messenger" style="display: none;">

        <!-- Área Izquierda (Chat Real) -->
        <div class="msn-left">
            <div class="msn-header">
                <div class="info-usuario-header">
                    <div class="salida" id="close-chat-btn">
                        <i class="fas fa-arrow-left"></i>
                    </div>

                    <img id="chat-avatar" src="" alt="Avatar"
                        style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid white;">

                    <div class="texto-header">
                        <span id="nombreChatSeleccionado">Nombre Usuario</span>
                        <div id="estadoChat" class="escribiendo" style="display: none;">Escribiendo...</div>
                    </div>
                </div>
            </div>

            <div class="msn-body" id="messages-container">
                <!-- Mensajes dinámicos -->
            </div>

            <div class="msn-input">
                <input type="text" id="message-input" placeholder="Escribe un mensaje..." autocomplete="off">
                <div class="enviar" id="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>

        <!-- Área Derecha (Lista rápida en escritorio) -->
        <div class="msn-right">
            <div class="titulo" style="font-size: 20px;">Contactos</div>
            <div class="lista-contactos-mini" id="lista-derecha">
                <!-- Clon de la lista de contactos para escritorio -->
            </div>
        </div>
    </div>

    <!-- CONFIG -->
    <script>
        window.NEXUS_CONFIG = {
            userId: null,
            apiBaseUrl: '{{ url("/chat") }}',
            pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
            pusherCluster: '{{ config('broadcasting.connections.pusher.app_cluster') }}'
        };
    </script>
    <script src="{{ asset('js/chat-app.js') }}"></script>
</body>

</html>