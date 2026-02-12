console.log("NEXUS Chat Laravel: Sistema cargado");

class NexusChat {
    constructor() {
        this.socket = null;
        this.currentUser = null;
        this.currentUserId = null;
        this.currentChat = null;
        this.onlineUsers = {};
        this.typingTimer = null;
        this.contacts = [];
        
        this.init();
    }

    async init() {
        try {
            await this.loadUserData();
            this.initSocket();
            this.setupEventListeners();
            this.loadContacts();
            this.requestNotificationPermission();
        } catch (error) {
            console.error("Error inicializando chat:", error);
            this.showError("Error al inicializar el chat");
        }
    }

    async loadUserData() {
        try {
            const response = await fetch('/chat/devuelve', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            const data = await response.json();
            this.currentUser = data.usuario;
            this.currentUserId = data.id_usuario;
            
            document.getElementById('current-user').textContent = this.currentUser;
            
            if (this.currentUserId) {
                this.emit('user_online', {
                    nombre: this.currentUser,
                    id: this.currentUserId
                });
            }
        } catch (error) {
            console.error("Error cargando datos de usuario:", error);
            this.currentUser = "Invitado";
            this.currentUserId = null;
            document.getElementById('current-user').textContent = this.currentUser;
        }
    }

    initSocket() {
        // Conectar al WebSocket de Laravel Websockets o Pusher
        if (typeof io !== 'undefined') {
            // Fallback a Socket.IO si está disponible
            this.socket = io('http://localhost:3000', { withCredentials: true });
        } else {
            // Usar Laravel Echo con Pusher/Websockets
            this.initLaravelEcho();
        }

        if (this.socket) {
            this.setupSocketListeners();
        }
    }

    initLaravelEcho() {
        // Implementación con Laravel Echo (requiere configuración adicional)
        console.log("Inicializando Laravel Echo...");
        // Aquí iría la configuración de Echo
    }

    setupSocketListeners() {
        if (!this.socket) return;

        this.socket.on('connect', () => {
            console.log('🟢 Conectado al servidor de chat');
        });

        this.socket.on('usuarios_online', (users) => {
            this.onlineUsers = users;
            this.updateOnlineUsersList();
        });

        this.socket.on('mensaje_recibido', (data) => {
            this.displayMessage(data);
            this.updateLastMessage(data);
            
            if (data.sala !== this.currentChat) {
                this.showNotification(data.nombre_usuario, data.mensaje);
            }
        });

        this.socket.on('notificacion_nuevo_mensaje', (data) => {
            this.showNotification(data.nombre_remitente, data.mensaje);
        });

        this.socket.on('display_typing', (data) => {
            this.showTypingIndicator(data);
        });

        this.socket.on('cargar_historial', (messages) => {
            this.loadHistory(messages);
        });
    }

    setupEventListeners() {
        // Envío de mensajes
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');

        if (messageInput && sendBtn) {
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                } else {
                    this.emitTyping();
                }
            });

            sendBtn.addEventListener('click', () => this.sendMessage());
        }

        // Búsqueda de usuarios
        const searchInput = document.getElementById('search-users');
        const searchBtn = document.getElementById('search-btn');

        if (searchInput && searchBtn) {
            searchInput.addEventListener('input', () => this.searchUsers(searchInput.value));
            searchBtn.addEventListener('click', () => this.searchUsers(searchInput.value));
        }

        // Logout
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.logout());
        }
    }

    async loadContacts() {
        try {
            const response = await fetch('/chat/contactos', {
                credentials: 'include'
            });
            
            this.contacts = await response.json();
            this.renderContactsList();
        } catch (error) {
            console.error("Error cargando contactos:", error);
        }
    }

    renderContactsList() {
        const contactsList = document.getElementById('contacts-list');
        if (!contactsList) return;

        if (this.contacts.length === 0) {
            contactsList.innerHTML = '<div class="no-contacts">No tienes conversaciones aún</div>';
            return;
        }

        contactsList.innerHTML = this.contacts.map(contact => `
            <div class="contact-item" data-user-id="${contact.id}">
                <img src="uploads/${contact.foto_perfil}" alt="${contact.nombre_completo}" class="contact-avatar">
                <div class="contact-info">
                    <h4>${contact.nombre_completo}</h4>
                    <span class="contact-status ${this.onlineUsers[contact.id] ? 'online' : 'offline'}">
                        ${this.onlineUsers[contact.id] ? '🟢 En línea' : '⚫ Desconectado'}
                    </span>
                </div>
            </div>
        `).join('');

        // Agregar event listeners a los contactos
        contactsList.querySelectorAll('.contact-item').forEach(item => {
            item.addEventListener('click', () => {
                const userId = item.dataset.userId;
                this.openChat(userId);
            });
        });
    }

    async openChat(userId) {
        if (!this.currentUserId) {
            this.showError('Debes estar autenticado para chatear');
            return;
        }

        const contact = this.contacts.find(c => c.id == userId);
        if (!contact) return;

        this.currentChat = this.generateRoomId(this.currentUserId, userId);
        
        // Actualizar UI
        document.getElementById('chat-contact-name').textContent = contact.nombre_completo;
        document.getElementById('chat-avatar').src = `uploads/${contact.foto_perfil}`;
        document.getElementById('message-input').disabled = false;
        document.getElementById('send-btn').disabled = false;

        // Unirse a la sala
        this.emit('join_room', {
            sala: this.currentChat,
            id_usuario: this.currentUserId,
            nombre_usuario: this.currentUser
        });

        // Cargar historial
        this.loadChatHistory(this.currentChat);

        // Resaltar contacto activo
        document.querySelectorAll('.contact-item').forEach(item => {
            item.classList.toggle('active', item.dataset.userId == userId);
        });
    }

    async loadChatHistory(roomId) {
        try {
            const response = await fetch(`/chat/mensajes/${roomId}`, {
                credentials: 'include'
            });
            
            const messages = await response.json();
            this.renderMessages(messages);
        } catch (error) {
            console.error("Error cargando historial:", error);
        }
    }

    renderMessages(messages) {
        const container = document.getElementById('messages-container');
        if (!container) return;

        container.innerHTML = messages.map(msg => {
            const isOwn = msg.id_usuario == this.currentUserId;
            return `
                <div class="message ${isOwn ? 'own' : 'other'}">
                    <div class="message-content">
                        <p>${msg.mensaje}</p>
                        <span class="message-time">${this.formatTime(msg.fecha)}</span>
                    </div>
                </div>
            `;
        }).join('');

        this.scrollToBottom();
    }

    sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        
        if (!message || !this.currentChat || !this.currentUserId) return;

        const messageData = {
            sala: this.currentChat,
            id_usuario: this.currentUserId,
            nombre_usuario: this.currentUser,
            mensaje: message
        };

        // Enviar via WebSocket
        this.emit('new_message', messageData);

        // Guardar en base de datos via HTTP
        fetch('/chat/mensaje', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(messageData),
            credentials: 'include'
        });

        input.value = '';
        this.emit('stop_typing');
    }

    emit(event, data) {
        if (this.socket) {
            this.socket.emit(event, data);
        }
    }

    generateRoomId(userId1, userId2) {
        return userId1 < userId2 ? `${userId1}-${userId2}` : `${userId2}-${userId1}`;
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    }

    scrollToBottom() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    showNotification(title, message) {
        if (Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico'
            });
        }
    }

    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    showError(message) {
        console.error(message);
        // Implementar UI de error según sea necesario
    }

    updateOnlineUsersList() {
        const onlineList = document.getElementById('online-list');
        if (!onlineList) return;

        const onlineUsers = Object.entries(this.onlineUsers)
            .filter(([id]) => id != this.currentUserId)
            .map(([id, name]) => `<div class="online-user">🟢 ${name}</div>`)
            .join('');

        onlineList.innerHTML = onlineUsers || '<div class="no-online">No hay usuarios en línea</div>';
    }

    emitTyping() {
        if (this.typingTimer) clearTimeout(this.typingTimer);
        
        this.emit('typing', {
            sala: this.currentChat,
            nombre_usuario: this.currentUser
        });

        this.typingTimer = setTimeout(() => {
            this.emit('stop_typing');
        }, 1000);
    }

    showTypingIndicator(data) {
        const indicator = document.getElementById('typing-indicator');
        if (indicator && data.sala === this.currentChat) {
            indicator.style.display = 'block';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 3000);
        }
    }

    async searchUsers(query) {
        if (!query.trim()) {
            this.renderContactsList();
            return;
        }

        try {
            const response = await fetch(`/chat/usuarios?q=${encodeURIComponent(query)}`, {
                credentials: 'include'
            });
            
            const users = await response.json();
            this.renderSearchResults(users);
        } catch (error) {
            console.error("Error buscando usuarios:", error);
        }
    }

    renderSearchResults(users) {
        const contactsList = document.getElementById('contacts-list');
        if (!contactsList) return;

        if (users.length === 0) {
            contactsList.innerHTML = '<div class="no-results">No se encontraron usuarios</div>';
            return;
        }

        contactsList.innerHTML = users.map(user => `
            <div class="contact-item search-result" data-user-id="${user.id}">
                <img src="uploads/${user.foto_perfil}" alt="${user.nombre}" class="contact-avatar">
                <div class="contact-info">
                    <h4>${user.nombre}</h4>
                    <span class="contact-status ${this.onlineUsers[user.id] ? 'online' : 'offline'}">
                        ${this.onlineUsers[user.id] ? '🟢 En línea' : '⚫ Desconectado'}
                    </span>
                </div>
            </div>
        `).join('');

        // Agregar event listeners
        contactsList.querySelectorAll('.contact-item').forEach(item => {
            item.addEventListener('click', () => {
                const userId = item.dataset.userId;
                this.openChat(userId);
            });
        });
    }

    logout() {
        window.location.href = '../logout.php';
    }
}

// Inicializar el chat cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.nexusChat = new NexusChat();
});
