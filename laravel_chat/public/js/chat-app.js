/**
 * NEXUS Chat - Cliente Frontend
 * Maneja la autenticación, WebSocket y UI
 */

document.addEventListener('DOMContentLoaded', async function () {
    // 1. Obtener credenciales de la URL (Integración con Proyecto_Prueba)
    const urlParams = new URLSearchParams(window.location.search);
    const uid = urlParams.get('uid');
    const sig = urlParams.get('sig');

    // Elementos UI (Actualizados para nuevo diseño)
    const ui = {
        currentUser: document.body, // No se usa display directo en este diseño
        contactsList: document.getElementById('contacts-list'),
        desktopContactsList: document.getElementById('lista-derecha'),

        // Módulos
        moduleList: document.getElementById('modulo-lista-chats'),
        moduleChat: document.getElementById('chat-interface'),

        // Chat Interface
        messageInput: document.getElementById('message-input'),
        sendBtn: document.getElementById('send-btn'),
        messagesContainer: document.getElementById('messages-container'),

        // Header Info
        chatContactName: document.getElementById('nombreChatSeleccionado'),
        chatAvatar: document.getElementById('chat-avatar'),
        chatStatus: document.getElementById('estadoChat'),

        // Botones
        closeChatBtn: document.getElementById('close-chat-btn'),
        logoutBtn: document.getElementById('logout-btn')
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
                initEcho(data.id);
                loadContacts();
            } else {
                alert("No se pudo iniciar sesión en el chat.");
            }

        } catch (error) {
            console.error("Login error:", error);
        }
    }

    // 3. Echo (Configuración igual)
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
                if (currentChatId == e.message.from_id) {
                    appendMessage(e.message, false);
                    scrollToBottom();
                } else {
                    // Actualizar contador o mover contacto arriba
                    loadContacts();
                }
            })
            .listen('TypingEvent', (e) => {
                if (currentChatId == e.user.id) {
                    showTyping();
                }
            });
    }

    // 4. Cargar Contactos
    async function loadContacts() {
        try {
            const response = await fetch('/chat/contactos');
            const contacts = await response.json();

            // Renderizar en ambas listas (Móvil y Escritorio)
            renderContacts(contacts, ui.contactsList);
            if (ui.desktopContactsList) renderContacts(contacts, ui.desktopContactsList, true);

        } catch (error) {
            console.error("Error cargando contactos:", error);
        }
    }

    function renderContacts(contacts, container, isDesktop = false) {
        container.innerHTML = '';
        contacts.forEach(user => {
            const el = document.createElement('div');
            el.className = isDesktop ? 'contacto-derecha' : 'chat-item';

            // HTML Diferente para lista escritorio vs móvil si se requiere, pero usaremos genérico
            if (isDesktop) {
                el.innerHTML = `
                    <img src="${user.foto_perfil || '../assets/default-avatar.png'}" class="foto-mini">
                    <div class="info-derecha">
                        <span>${user.nombre} ${user.apellido}</span>
                    </div>
                `;
            } else {
                el.innerHTML = `
                    <img src="${user.foto_perfil || '../assets/default-avatar.png'}" class="chat-photo">
                    <div class="chat-info">
                        <div class="chat-name">${user.nombre} ${user.apellido}</div>
                        <p class="chat-last">${user.last_message || 'Toca para chatear'}</p>
                    </div>
                `;
            }

            el.onclick = () => openChat(user);
            container.appendChild(el);
        });
    }

    // 5. Abrir Chat
    function openChat(user) {
        currentChatId = user.id;

        // Actualizar UI Header
        ui.chatContactName.textContent = `${user.nombre} ${user.apellido}`;
        ui.chatAvatar.src = user.foto_perfil || '../assets/default-avatar.png';

        // Mostrar interfaz chat
        ui.moduleList.style.display = 'none'; // Ocultar lista en móvil
        ui.moduleChat.style.display = 'flex'; // Mostrar chat

        // En escritorio, mantener lista visible si el CSS lo permite (el CSS user tiene .messenger fixed full width)
        // El CSS del usuario pone .messenger fixed z-index 2000 full width.
        // Así que ocultará la lista subyacente.

        loadMessages(user.id);
    }

    // Cerrar Chat (Volver a lista)
    ui.closeChatBtn.addEventListener('click', () => {
        ui.moduleChat.style.display = 'none';
        ui.moduleList.style.display = 'flex';
        currentChatId = null;
    });

    // 6. Cargar Mensajes
    async function loadMessages(chatId) {
        ui.messagesContainer.innerHTML = '';

        try {
            const response = await fetch(`/chat/mensajes/${chatId}`);
            const messages = await response.json();

            messages.forEach(msg => {
                const isMe = msg.from_id == currentUser.id;
                appendMessage(msg, isMe);
            });
            scrollToBottom();
        } catch (error) {
            console.error("Error mensajes:", error);
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
            console.error("Error enviando");
        }
    }

    // Helper: Mostrar mensaje en UI
    function appendMessage(msg, isMe) {
        const div = document.createElement('div');
        div.className = `msg ${isMe ? 'msg-right' : 'msg-left'}`;
        div.textContent = msg.body; // textContent para seguridad
        ui.messagesContainer.appendChild(div);
    }

    function scrollToBottom() {
        ui.messagesContainer.scrollTop = ui.messagesContainer.scrollHeight;
    }

    function showTyping() {
        ui.chatStatus.style.display = 'block';
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            ui.chatStatus.style.display = 'none';
        }, 3000);
    }

    // Event Listeners
    ui.sendBtn.addEventListener('click', sendMessage);
    ui.messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // Logout
    if (ui.logoutBtn) {
        ui.logoutBtn.addEventListener('click', async () => {
            if (!confirm('¿Salir?')) return;
            await fetch('/chat/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
            window.location.href = '/';
        });
    }

    // Iniciar
    login();
});
