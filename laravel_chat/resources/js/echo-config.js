/**
 * Configuración de Laravel Echo para Nexus Chat
 * Reemplaza la conexión Socket.IO original por Laravel Echo + Pusher
 * Compatible con hosting compartido
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Configuración global de Echo
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY || 'mi_pusher_app_key',
    cluster: process.env.MIX_PUSHER_APP_CLUSTER || 'mt1',
    forceTLS: true,
    encrypted: true,
    enabledTransports: ['ws', 'wss'],
    wsHost: window.location.hostname,
    wsPort: 6001,
    disableStats: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    }
});

// Clase principal del Chat Nexus (migrada de Socket.IO)
class NexusChatLaravel {
    constructor() {
        this.currentUser = null;
        this.currentUserId = null;
        this.currentChat = null;
        this.onlineUsers = {};
        this.typingTimer = null;
        this.contacts = [];
        this.channels = {};
        
        this.init();
    }

    async init() {
        try {
            await this.loadUserData();
            this.setupEchoListeners();
            this.setupEventListeners();
            this.loadContacts();
            this.requestNotificationPermission();
        } catch (error) {
            console.error("Error inicializando chat Laravel:", error);
            this.showError("Error al inicializar el chat");
        }
    }

    async loadUserData() {
        try {
            const response = await fetch('/chat/devuelve', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            const data = await response.json();
            this.currentUser = data.usuario;
            this.currentUserId = data.id_usuario;
            
            document.getElementById('current-user').textContent = this.currentUser;
            
            if (this.currentUserId) {
                this.connectUserChannels();
            }
        } catch (error) {
            console.error("Error cargando datos de usuario:", error);
            this.currentUser = "Invitado";
            this.currentUserId = null;
            document.getElementById('current-user').textContent = this.currentUser;
        }
    }

    connectUserChannels() {
        if (!this.currentUserId) return;

        // Canal privado para notificaciones del usuario
        this.channels.userChannel = window.Echo.private(`user.${this.currentUserId}`);
        
        // Canal para estado de usuario
        this.channels.statusChannel = window.Echo.private(`user-status.${this.currentUserId}`);
        
        // Canal público de usuarios online
        this.channels.onlineChannel = window.Echo.channel('online-users');
        
        // Canal público de indicadores de escritura
        this.channels.typingChannel = window.Echo.channel('typing-indicators');
    }

    setupEchoListeners() {
        // Escuchar nuevos mensajes
        window.Echo.private('chat.*')
            .listen('NewMessage', (e) => {
                this.handleNewMessage(e);
            });

        // Escuchar cambios de estado de usuario
        window.Echo.channel('online-users')
            .listen('UserOnline', (e) => {
                this.handleUserStatusChange(e);
            });

        // Escuchar indicadores de escritura
        window.Echo.channel('typing-indicators')
            .listen('TypingEvent', (e) => {
                this.handleTypingIndicator(e);
            });
    }

    handleNewMessage(event) {
        console.log('📨 Nuevo mensaje recibido:', event);
        
        // Mostrar mensaje en la interfaz
        this.displayMessage(event);
        
        // Actualizar último mensaje en lista de contactos
        this.updateLastMessage(event);
        
        // Mostrar notificación si no estamos en el chat activo
        if (event.sala !== this.currentChat) {
            this.showNotification(event.nombre_usuario, event.mensaje);
        }
    }

    handleUserStatusChange(event) {
        console.log('👤 Cambio de estado usuario:', event);
        
        if (event.is_online) {
            this.onlineUsers[event.user_id] = event.nombre_completo;
        } else {
            delete this.onlineUsers[event.user_id];
        }
        
        this.updateOnlineUsersList();
    }

    handleTypingIndicator(event) {
        console.log('⌨️ Indicador de escritura:', event);
        
        if (event.sala === this.currentChat && event.is_typing) {
            this.showTypingIndicator(event);
            
            // Ocultar después de 3 segundos
            setTimeout(() => {
                this.hideTypingIndicator();
            }, 3000);
        }
    }

    async sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        
        if (!message || !this.currentChat || !this.currentUserId) return;

        const messageData = {
            sala: this.currentChat,
            mensaje: message
        };

        try {
            // Enviar vía HTTP API
            const response = await fetch('/chat/mensaje', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(messageData),
                credentials: 'include'
            });

            if (response.ok) {
                input.value = '';
                this.stopTyping();
            } else {
                throw new Error('Error enviando mensaje');
            }
        } catch (error) {
            console.error("Error enviando mensaje:", error);
            this.showError("Error al enviar mensaje");
        }
    }

    async openChat(userId) {
        if (!this.currentUserId) {
            this.showError('Debes estar autenticado para chatear');
            return;
        }

        const contact = this.contacts.find(c => c.id == userId);
        if (!contact) return;

        // Generar ID de sala (misma lógica que sistema original)
        this.currentChat = this.generateRoomId(this.currentUserId, userId);
        
        // Actualizar UI
        document.getElementById('chat-contact-name').textContent = contact.nombre_completo;
        document.getElementById('chat-avatar').src = `../uploads/${contact.foto_perfil}`;
        document.getElementById('message-input').disabled = false;
        document.getElementById('send-btn').disabled = false;

        // Unirse al canal privado de la sala
        this.joinChatRoom(this.currentChat);

        // Cargar historial
        this.loadChatHistory(this.currentChat);

        // Resaltar contacto activo
        document.querySelectorAll('.contact-item').forEach(item => {
            item.classList.toggle('active', item.dataset.userId == userId);
        });
    }

    joinChatRoom(sala) {
        // Salir del canal anterior si existe
        if (this.channels.chatChannel) {
            window.Echo.leaveChannel(`chat.${this.currentChat}`);
        }

        // Unirse al nuevo canal
        this.channels.chatChannel = window.Echo.private(`chat.${sala}`);
        
        // Escuchar mensajes específicos de esta sala
        this.channels.chatChannel.listen('NewMessage', (e) => {
            if (e.sala === sala) {
                this.displayMessage(e);
            }
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

    // Métodos de UI (similares al sistema original)
    renderContactsList() {
        const contactsList = document.getElementById('contacts-list');
        if (!contactsList) return;

        if (this.contacts.length === 0) {
            contactsList.innerHTML = '<div class="no-contacts">No tienes conversaciones aún</div>';
            return;
        }

        contactsList.innerHTML = this.contacts.map(contact => `
            <div class="contact-item" data-user-id="${contact.id}">
                <img src="../uploads/${contact.foto_perfil}" alt="${contact.nombre_completo}" class="contact-avatar">
                <div class="contact-info">
                    <h4>${contact.nombre_completo}</h4>
                    <span class="contact-status ${this.onlineUsers[contact.id] ? 'online' : 'offline'}">
                        ${this.onlineUsers[contact.id] ? '🟢 En línea' : '⚫ Desconectado'}
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

    renderMessages(messages) {
        const container = document.getElementById('messages-container');
        if (!container) return;

        container.innerHTML = messages.map(msg => {
            const isOwn = msg.propio;
            return `
                <div class="message ${isOwn ? 'own' : 'other'}">
                    <div class="message-content">
                        <p>${msg.mensaje}</p>
                        <span class="message-time">${msg.tiempo_formateado}</span>
                    </div>
                </div>
            `;
        }).join('');

        this.scrollToBottom();
    }

    displayMessage(messageData) {
        const container = document.getElementById('messages-container');
        if (!container) return;

        const isOwn = messageData.usuario == this.currentUserId;
        const messageHtml = `
            <div class="message ${isOwn ? 'own' : 'other'}">
                <div class="message-content">
                    <p>${messageData.mensaje}</p>
                    <span class="message-time">${messageData.tiempo_formateado}</span>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', messageHtml);
        this.scrollToBottom();
    }

    // Métodos helper (heredados del sistema original)
    generateRoomId(userId1, userId2) {
        return userId1 < userId2 ? `${userId1}-${userId2}` : `${userId2}-${userId1}`;
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

    showTypingIndicator(data) {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.innerHTML = `<span>${data.nombre_usuario} está escribiendo...</span>`;
            indicator.style.display = 'block';
        }
    }

    hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
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
                    this.startTyping();
                }
            });

            sendBtn.addEventListener('click', () => this.sendMessage());
        }

        // Búsqueda de usuarios
        const searchInput = document.getElementById('search-users');
        if (searchInput) {
            searchInput.addEventListener('input', () => this.searchUsers(searchInput.value));
        }

        // Logout
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.logout());
        }
    }

    async startTyping() {
        if (!this.currentChat || !this.currentUserId) return;

        // Enviar indicador de escritura
        try {
            await fetch('/chat/typing', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    sala: this.currentChat,
                    is_typing: true
                }),
                credentials: 'include'
            });
        } catch (error) {
            console.error("Error con typing:", error);
        }

        // Limpiar timer anterior
        if (this.typingTimer) clearTimeout(this.typingTimer);
        
        // Dejar de mostrar "escribiendo" después de 3 segundos
        this.typingTimer = setTimeout(() => {
            this.stopTyping();
        }, 3000);
    }

    async stopTyping() {
        if (!this.currentChat || !this.currentUserId) return;

        try {
            await fetch('/chat/typing', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    sala: this.currentChat,
                    is_typing: false
                }),
                credentials: 'include'
            });
        } catch (error) {
            console.error("Error stop typing:", error);
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
                <img src="../uploads/${user.foto_perfil}" alt="${user.nombre}" class="contact-avatar">
                <div class="contact-info">
                    <h4>${user.nombre}</h4>
                    <span class="contact-status ${user.is_online ? 'online' : 'offline'}">
                        ${user.is_online ? '🟢 En línea' : '⚫ Desconectado'}
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

    async logout() {
        try {
            await fetch('/chat/logout', {
                method: 'POST',
                credentials: 'include'
            });
            
            // Redirigir al logout del sistema principal
            window.location.href = '../logout.php';
        } catch (error) {
            console.error("Error en logout:", error);
        }
    }
}

// Inicializar el chat cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.nexusChat = new NexusChatLaravel();
});

// Exportar para uso global
window.NexusChatLaravel = NexusChatLaravel;
