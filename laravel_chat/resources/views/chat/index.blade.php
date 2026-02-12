<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NEXUS Chat - Laravel</title>
    <link rel="stylesheet" href="{{ asset('css/chat/index.css') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Laravel Echo & Pusher -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.min.js"></script>
</head>
<body>
    <div class="chat-container">
        <header class="chat-header">
            <div class="header-content">
                <h1 class="chat-title">
                    <span class="logo">💬</span>
                    NEXUS Chat
                </h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <img id="user-avatar-img" src="../assets/default-avatar.png" alt="Avatar">
                        <span class="status-dot online"></span>
                    </div>
                    <div class="user-details">
                        <span id="current-user">Cargando...</span>
                        <span class="user-status">En línea</span>
                    </div>
                    <button id="logout-btn" class="btn-logout" title="Cerrar sesión">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <main class="chat-main">
            <!-- Lista de Contactos -->
            <aside class="contacts-sidebar">
                <div class="search-container">
                    <div class="search-box">
                        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                        <input 
                            type="text" 
                            id="search-users" 
                            placeholder="Buscar usuarios o chats..."
                            autocomplete="off"
                        >
                        <button id="search-btn" class="search-btn" title="Buscar">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="contacts-section">
                    <div class="section-header">
                        <h3>Chats</h3>
                        <span class="contact-count" id="contact-count">0</span>
                    </div>
                    <div class="contacts-list" id="contacts-list">
                        <div class="loading-state">
                            <div class="loading-spinner"></div>
                            <span>Cargando contactos...</span>
                        </div>
                    </div>
                </div>

                <div class="online-section">
                    <div class="section-header">
                        <h3>Usuarios en línea</h3>
                        <span class="online-count" id="online-count">0</span>
                    </div>
                    <div class="online-users" id="online-list">
                        <div class="loading-state">
                            <span>Cargando usuarios...</span>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Ventana del Chat -->
            <section class="chat-window">
                <!-- Estado inicial (sin chat seleccionado) -->
                <div class="welcome-screen" id="welcome-screen">
                    <div class="welcome-content">
                        <div class="welcome-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2v10z"/>
                                <path d="M8 10h.01M12 10h.01M16 10h.01"/>
                            </svg>
                        </div>
                        <h2>👋 Bienvenido a NEXUS Chat</h2>
                        <p>Selecciona un contacto de la lista para comenzar a conversar</p>
                        <div class="welcome-features">
                            <div class="feature">
                                <span class="feature-icon">🔒</span>
                                <span>Chats privados y seguros</span>
                            </div>
                            <div class="feature">
                                <span class="feature-icon">⚡</span>
                                <span>Mensajes en tiempo real</span>
                            </div>
                            <div class="feature">
                                <span class="feature-icon">🔔</span>
                                <span>Notificaciones instantáneas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interfaz de chat (oculta inicialmente) -->
                <div class="chat-interface" id="chat-interface" style="display: none;">
                    <!-- Header del chat -->
                    <div class="chat-header-info">
                        <div class="contact-info">
                            <div class="contact-avatar">
                                <img id="chat-avatar" src="../assets/default-avatar.png" alt="Avatar">
                                <span class="status-dot" id="chat-status"></span>
                            </div>
                            <div class="contact-details">
                                <h3 id="chat-contact-name">Selecciona un contacto</h3>
                                <span id="chat-status-text">Elige un contacto para empezar</span>
                            </div>
                            <div class="chat-actions">
                                <button class="action-btn" title="Más opciones">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"/>
                                        <circle cx="12" cy="5" r="1"/>
                                        <circle cx="12" cy="19" r="1"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor de mensajes -->
                    <div class="messages-container" id="messages-container">
                        <!-- Los mensajes se cargarán aquí dinámicamente -->
                    </div>

                    <!-- Indicador de escritura -->
                    <div class="typing-indicator" id="typing-indicator" style="display: none;">
                        <div class="typing-content">
                            <div class="typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <span id="typing-text">Alguien está escribiendo...</span>
                        </div>
                    </div>

                    <!-- Área de entrada de mensajes -->
                    <div class="message-input-container">
                        <div class="input-group">
                            <button class="attach-btn" title="Adjuntar archivo">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
                                </svg>
                            </button>
                            <input 
                                type="text" 
                                id="message-input" 
                                placeholder="Escribe tu mensaje..." 
                                disabled
                                autocomplete="off"
                                maxlength="1000"
                            >
                            <button id="send-btn" class="send-btn" disabled title="Enviar mensaje">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"/>
                                    <polygon points="22,2 15,22 11,13 2,9 22,2"/>
                                </svg>
                            </button>
                        </div>
                        <div class="input-footer">
                            <span class="char-counter" id="char-counter">0 / 1000</span>
                            <span class="shortcuts-hint">Enter para enviar, Shift+Enter para nueva línea</span>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Configuración de Echo -->
    <script>
        // Variables globales para configuración
        window.NEXUS_CONFIG = {
            userId: null,
            userName: null,
            currentChat: null,
            apiBaseUrl: '{{ url("/chat") }}',
            pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
            pusherCluster: '{{ config('broadcasting.connections.pusher.app_cluster') }}'
        };

        // Configuración de variables de entorno para Laravel Echo
        window.process = {
            env: {
                MIX_PUSHER_APP_KEY: '{{ config('broadcasting.connections.pusher.key') }}',
                MIX_PUSHER_APP_CLUSTER: '{{ config('broadcasting.connections.pusher.app_cluster') }}'
            }
        };
    </script>
    
    <!-- Cargar la aplicación del chat -->
    <script src="{{ asset('js/echo-config.js') }}"></script>
    
    <!-- Script para contador de caracteres -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageInput = document.getElementById('message-input');
            const charCounter = document.getElementById('char-counter');
            
            if (messageInput && charCounter) {
                messageInput.addEventListener('input', function() {
                    const length = this.value.length;
                    charCounter.textContent = `${length} / 1000`;
                    
                    if (length > 900) {
                        charCounter.style.color = '#ff6b6b';
                    } else if (length > 700) {
                        charCounter.style.color = '#feca57';
                    } else {
                        charCounter.style.color = '#999';
                    }
                });
            }
        });
    </script>
</body>
</html>
