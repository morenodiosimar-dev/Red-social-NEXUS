/**
 * NEXUS Chat - Cliente Frontend
 * Maneja la autenticación, WebSocket y UI
 */

document.addEventListener('DOMContentLoaded', async function () {
    // 1. Obtener credenciales de la URL (Integración con Proyecto_Prueba)
    const urlParams = new URLSearchParams(window.location.search);
    const uid = urlParams.get('uid');
    const sig = urlParams.get('sig');

    // Elementos UI
    const ui = {
        currentUser: document.getElementById('current-user'),
        avatar: document.getElementById('user-avatar-img'),
        status: document.querySelector('.status-dot'),
        contactsList: document.getElementById('contacts-list'),
        chatInterface: document.getElementById('chat-interface'),
        welcomeScreen: document.getElementById('welcome-screen'),
        messageInput: document.getElementById('message-input'),
        sendBtn: document.getElementById('send-btn'),
        messagesContainer: document.getElementById('messages-container'),
        chatContactName: document.getElementById('chat-contact-name'),
        chatStatus: document.getElementById('chat-status'),
        chatStatusText: document.getElementById('chat-status-text'),
        chatAvatar: document.getElementById('chat-avatar'),
        typingIndicator: document.getElementById('typing-indicator'),
        typingText: document.getElementById('typing-text')
    };

    let currentUser = null;
    let currentChatId = null;
    let echo = null;
    let typingTimer = null;

    // 2. Autenticación Inicial
    async function login() {
        try {
            const formData = new FormData();
            if (uid && sig) {
                formData.append('user_id', uid);
                formData.append('signature', sig);
            }

            const response = await fetch('/chat/devuelve', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data && (data.id || data.user_id)) {
                currentUser = data;
                ui.currentUser.textContent = `${data.nombre} ${data.apellido}`;
                ui.avatar.src = data.foto_perfil || '../assets/default-avatar.png';
                ui.status.classList.add('online');

                // Inicializar WebSocket
                initEcho(data.id);
                loadContacts();
            } else {
                ui.currentUser.textContent = "Error de autenticación";
                alert("No se pudo iniciar sesión en el chat. Verifica que estés logueado en Nexus.");
            }

        } catch (error) {
            console.error("Login error:", error);
            ui.currentUser.textContent = "Error de conexión";
        }
    }

    // 3. Configurar Laravel Echo
    function initEcho(userId) {
        if (!window.Echo) return;

        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: window.NEXUS_CONFIG.pusherKey,
            cluster: window.NEXUS_CONFIG.pusherCluster,
            forceTLS: true
        });

        // Escuchar mensajes globales o privados
        window.Echo.private(`chat.${userId}`)
            .listen('NewMessage', (e) => {
                console.log("Nuevo mensaje:", e);
                if (currentChatId == e.message.from_id) {
                    appendMessage(e.message, false);
                    scrollToBottom();
                } else {
                    // Mostrar notificación o badge en la lista de contactos
                    updateUnreadCount(e.message.from_id);
                }
            })
            .listen('TypingEvent', (e) => {
                if (currentChatId == e.user.id) {
                    showTyping(e.user.nombre);
                }
            });

        // Estado Online
        window.Echo.join('online')
            .here((users) => {
                document.getElementById('online-count').textContent = users.length;
            })
            .joining((user) => {
                console.log(user.name + ' entró');
            })
            .leaving((user) => {
                console.log(user.name + ' salió');
            });
    }

    // 4. Cargar Contactos
    async function loadContacts() {
        try {
            const response = await fetch('/chat/contactos');
            const contacts = await response.json();

            ui.contactsList.innerHTML = '';
            document.getElementById('contact-count').textContent = contacts.length;

            contacts.forEach(user => {
                const el = document.createElement('div');
                el.className = 'contact-item';
                el.dataset.id = user.id;
                el.innerHTML = `
                    <div class="contact-avatar">
                        <img src="${user.foto_perfil || '../assets/default-avatar.png'}" alt="${user.nombre}">
                        <span class="status-dot ${user.is_online ? 'online' : 'offline'}"></span>
                    </div>
                    <div class="contact-info">
                        <div class="contact-name">${user.nombre} ${user.apellido}</div>
                        <div class="last-message text-truncate">${user.last_message || '¡Saluda!'}</div>
                    </div>
                `;
                el.onclick = () => openChat(user);
                ui.contactsList.appendChild(el);
            });

        } catch (error) {
            console.error("Error cargando contactos:", error);
        }
    }

    // 5. Abrir Chat
    async function openChat(user) {
        currentChatId = user.id;

        // Actualizar UI Header
        ui.chatContactName.textContent = `${user.nombre} ${user.apellido}`;
        ui.chatAvatar.src = user.foto_perfil || '../assets/default-avatar.png';
        ui.chatStatus.className = `status-dot ${user.is_online ? 'online' : 'offline'}`;
        ui.chatStatusText.textContent = user.is_online ? 'En línea' : 'Desconectado';

        // Cambiar Vistas
        ui.welcomeScreen.style.display = 'none';
        ui.chatInterface.style.display = 'flex';

        // Habilitar inputs
        ui.messageInput.disabled = false;
        ui.sendBtn.disabled = false;

        // Cargar Mensajes
        loadMessages(user.id);
    }

    // 6. Cargar Mensajes
    async function loadMessages(chatId) {
        ui.messagesContainer.innerHTML = '<div class="loading-messages">Cargando historial...</div>';

        try {
            const response = await fetch(`/chat/mensajes/${chatId}`);
            const messages = await response.json();

            ui.messagesContainer.innerHTML = '';

            messages.forEach(msg => {
                const isMe = msg.from_id == currentUser.id;
                appendMessage(msg, isMe);
            });

            scrollToBottom();

        } catch (error) {
            console.error("Error mensajes:", error);
            ui.messagesContainer.innerHTML = '<div class="error-messages">Error al cargar historial</div>';
        }
    }

    // 7. Enviar Mensaje
    async function sendMessage() {
        const text = ui.messageInput.value.trim();
        if (!text || !currentChatId) return;

        // Optimistic UI update
        const tempMsg = {
            id: Date.now(),
            body: text,
            created_at: new Date().toISOString(),
            from_id: currentUser.id
        };
        appendMessage(tempMsg, true);
        scrollToBottom();
        ui.messageInput.value = '';

        try {
            await fetch('/chat/mensaje', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    to_id: currentChatId,
                    message: text
                })
            });
        } catch (error) {
            console.error("Error enviando:", error);
            // Mostrar error en el mensaje
        }
    }

    // Helper: Mostrar mensaje en UI
    function appendMessage(msg, isMe) {
        const div = document.createElement('div');
        div.className = `message ${isMe ? 'sent' : 'received'}`;

        // Formatear hora
        const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        div.innerHTML = `
            <div class="message-content">
                <p>${msg.body}</p>
                <span class="message-time">${time}</span>
            </div>
        `;
        ui.messagesContainer.appendChild(div);
    }

    function scrollToBottom() {
        ui.messagesContainer.scrollTop = ui.messagesContainer.scrollHeight;
    }

    function showTyping(name) {
        ui.typingIndicator.style.display = 'flex';
        ui.typingText.textContent = `${name} está escribiendo...`;

        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            ui.typingIndicator.style.display = 'none';
        }, 3000);
    }

    // Event Listeners
    ui.sendBtn.addEventListener('click', sendMessage);
    ui.messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // 8. Logout
    document.getElementById('logout-btn').addEventListener('click', async () => {
        if (!confirm('¿Estás seguro de cerrar sesión del chat?')) return;

        try {
            await fetch('/chat/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            // Redirigir al origen si existe, o recargar
            window.location.href = '/';
        } catch (error) {
            console.error("Error logout:", error);
            alert("Error al cerrar sesión");
        }
    });

    // Iniciar
    login();
});
