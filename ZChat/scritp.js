console.log("NEXUS Chat: Sistema cargado");

const IS_LOCAL = window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1";
const SOCKET_URL = IS_LOCAL ? "http://localhost:3000" : window.location.origin;

const socket = io(SOCKET_URL, {
    withCredentials: true,
    transports: ['websocket', 'polling']
});

let salaActual = null;
let usuarioActual = null;
let idUsuarioActual = null;
let usuariosOnline = {}; // Cambiado a objeto: { id: nombre }
let mapaUsuarios = {}; // Mapa de ID a objeto usuario completo
let timer; // Para el escribiendo
let contactoDirectoProcesado = false; // Bandera para evitar procesar contacto directo múltiples veces
let borradores = {}; // Mapa para guardar lo que el usuario escribe en cada sala

// 1. SOLICITAR PERMISOS Y LOGIN (Tu lógica de inicio)
if (Notification.permission !== "granted" && Notification.permission !== "denied") {
    Notification.requestPermission();
}

document.addEventListener("DOMContentLoaded", () => {
    fetch(`/api/devolver_usuario`, { 
        method: 'GET',
        credentials: 'include' // Esto permite que PHP reconozca quién está logueado
    })
        .then(res => res.json())
        .then(data => {
            usuarioActual = data.usuario || "Invitado-" + Math.floor(Math.random() * 1000);
            idUsuarioActual = data.id_usuario || null;
            socket.emit("usuario_online", { nombre: usuarioActual, id: idUsuarioActual });
            iniciarChat();
        })
        .catch(err => {
            console.error("Error identificando usuario:", err);
            usuarioActual = "Invitado";
            idUsuarioActual = null;
            iniciarChat();
        });
});


// 2. ESCUCHADORES DE SOCKET (Movidos fuera para que funcionen siempre)

socket.on("usuarios_online", (lista) => {
    usuariosOnline = lista; // Ahora es un objeto { id: nombre }
    actualizarEstados();
});

// Listener para notificaciones de nuevos mensajes
socket.on("notificacion_nuevo_mensaje", (data) => {
    const { id_remitente, nombre_remitente, mensaje, sala } = data;
    
    // Enviar notificación del navegador si la pestaña no está activa o el usuario no está en el chat
    if (document.hidden || salaActual !== sala) {
        enviarNotificacionSistema(nombre_remitente, mensaje);
    }
    
    // Marcar mensaje como no leído en la lista
    marcarMensajeNoLeido(id_remitente);
    
    // Actualizar último mensaje en la lista si existe
    actualizarUltimoMensajeEnLista(id_remitente, mensaje);
});

// Detectar cuando la pestaña gana foco para limpiar notificaciones
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        // Limpiar todas las notificaciones cuando el usuario vuelve a la pestaña
        if ('clear' in Notification) {
            Notification.clear();
        }
    }
});

socket.on("display_typing", (data) => {
    const { id_usuario, sala } = data;
    const visualEscribiendo = document.getElementById("Escribiendo");

    // Ventana chat actual
    if (sala === salaActual && id_usuario !== idUsuarioActual) {
        visualEscribiendo.textContent = "escribiendo...";
        visualEscribiendo.style.display = "block";
    }

    // Lista Principal - buscar por ID
    const itemPrincipal = document.querySelector(`.chat-item[data-id="${id_usuario}"] .status-text`);
    if (itemPrincipal) {
        if (!itemPrincipal.dataset.original) itemPrincipal.dataset.original = itemPrincipal.textContent;
        itemPrincipal.textContent = "escribiendo...";
        itemPrincipal.style.color = "#00c853";
    }

    // Lista Derecha - buscar por ID
    const itemDerechaStatus = document.querySelector(`.contacto-derecha[data-id="${id_usuario}"] .typing-status`);
    if (itemDerechaStatus) {
        itemDerechaStatus.textContent = "escribiendo...";
        itemDerechaStatus.style.display = "block";
    }

    clearTimeout(timer);
    timer = setTimeout(() => {
        if (visualEscribiendo) visualEscribiendo.style.display = "none";
        if (itemPrincipal) {
            itemPrincipal.textContent = itemPrincipal.dataset.original;
            itemPrincipal.style.color = "";
        }
        if (itemDerechaStatus) {
            itemDerechaStatus.textContent = "";
            itemDerechaStatus.style.display = "none";
        }
    }, 2000);
});

socket.off("mensaje_recibido");
socket.on("mensaje_recibido", (data) => {
    if (data.sala === salaActual) {
        // Comparar IDs convirtiendo ambos a números
        const lado = (parseInt(data.id_usuario) === parseInt(idUsuarioActual)) ? "right" : "left";
        agregarMensaje(data.mensaje, lado);
    } else if (parseInt(data.id_usuario) !== parseInt(idUsuarioActual)) {
        const nombreUsuario = data.nombre_usuario || usuariosOnline[data.id_usuario] || "Usuario";
        enviarNotificacionSistema(nombreUsuario, data.mensaje);
        marcarMensajeNoLeido(data.id_usuario);
    }
});

socket.on("cargar_historial", (mensajes) => {
    const msnBody = document.getElementById("msnBody");
    msnBody.innerHTML = "";
    mensajes.forEach(msg => {
        // Comparar IDs convirtiendo ambos a números para evitar problemas de tipo
        const lado = (parseInt(msg.id_usuario) === parseInt(idUsuarioActual)) ? "right" : "left";
        agregarMensaje(msg.mensaje, lado);
    });
});

// 3. FUNCIONES DE APOYO (Tal cual las tenías)

function agregarMensaje(texto, lado) {
    const div = document.createElement("div");
    div.classList.add("msg", lado === "right" ? "msg-right" : "msg-left");
    div.textContent = texto;
    const msnBody = document.getElementById("msnBody");
    msnBody.appendChild(div);
    msnBody.scrollTop = msnBody.scrollHeight;
}

function actualizarEstados() {
    // Lista Principal - usar ID
    document.querySelectorAll(".chat-item").forEach(item => {
        const idUsuario = item.dataset.id;
        const estado = item.querySelector(".estado");
        if (estado && idUsuario) {
            estado.classList.toggle("en-linea", usuariosOnline[idUsuario] !== undefined);
        }
    });

    // Lista Derecha - usar ID
    document.querySelectorAll(".contacto-derecha").forEach(item => {
        const idUsuario = item.dataset.id;
        const estado = item.querySelector(".estado");
        if (estado && idUsuario) {
            estado.classList.toggle("en-linea", usuariosOnline[idUsuario] !== undefined);
        }
    });

    // Header
    if (salaActual) {
        const idsSala = salaActual.split("-");
        const otroId = idsSala.find(id => parseInt(id) !== idUsuarioActual);
        const estadoChat = document.getElementById("estadoChat");
        if (estadoChat) {
            const estaEnLinea = usuariosOnline[otroId] !== undefined;
            estadoChat.textContent = estaEnLinea ? "En línea" : "Desconectado";
            estadoChat.style.color = estaEnLinea ? "#00c853" : "#9e9e9e";
        }
    }
}

function enviarNotificacionSistema(usuario, mensaje) {
    // Solicitar permisos si no se han concedido
    if (Notification.permission === "default") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                crearNotificacion(usuario, mensaje);
            }
        });
    } else if (Notification.permission === "granted") {
        crearNotificacion(usuario, mensaje);
    }
}

function crearNotificacion(usuario, mensaje) {
    const noti = new Notification(`Nuevo mensaje de ${usuario}`, {
        body: mensaje,
     icon: "./img/default.png",
        badge: "http://localhost/Chat/img/default.png",
        tag: 'nexus-chat', // Para agrupar notificaciones
        requireInteraction: false,
        silent: false
    });
    
    // Hacer sonido de notificación
    try {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
        audio.volume = 0.3;
        audio.play().catch(() => {}); // Ignorar errores de autoplay
    } catch (e) {
        console.log('No se pudo reproducir sonido de notificación');
    }
    
    // Al hacer clic, enfocar la ventana y abrir el chat
    noti.onclick = () => {
        window.focus();
        // Buscar y abrir el chat del remitente si existe
        const chatItem = document.querySelector(`.chat-item[data-id="${usuario}"]`);
        if (chatItem) {
            chatItem.click();
        }
        noti.close();
    };
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => noti.close(), 5000);
}

function actualizarUltimoMensajeEnLista(idUsuario, mensaje) {
    // Buscar en la lista principal
    const item = document.querySelector(`.chat-item[data-id="${idUsuario}"]`);
    if (item) {
        const ultimoMsg = item.querySelector(".ultimo-mensaje");
        if (ultimoMsg) {
            ultimoMsg.textContent = mensaje;
            // Resaltar el item temporalmente
            item.style.backgroundColor = "#e3f2fd";
            setTimeout(() => {
                item.style.backgroundColor = "";
            }, 1000);
        }
    }
}

function marcarMensajeNoLeido(idUsuario) {
    const item = document.querySelector(`.chat-item[data-id="${idUsuario}"]`);
    if (item) {
        let badge = item.querySelector(".badge-notificacion");
        if (!badge) {
            badge = document.createElement("span");
            badge.classList.add("badge-notificacion");
            badge.style.cssText = "background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; margin-left: auto;";
            badge.textContent = "1";
            item.appendChild(badge);
        } else {
            badge.textContent = parseInt(badge.textContent) + 1;
        }
    }
}

function limpiarMensajesNoLeidos(idUsuario) {
    const item = document.querySelector(`.chat-item[data-id="${idUsuario}"]`);
    if (item) {
        const badge = item.querySelector(".badge-notificacion");
        if (badge) {
            badge.remove();
        }
    }
}

// 4. FUNCIONALIDAD PRINCIPAL DEL CHAT

function iniciarChat() {
    const moduloLista = document.getElementById("modulo-lista-chats");
    const moduloChat = document.getElementById("modulo-chat");
    const nombreChat = document.getElementById("nombreChatSeleccionado");
    const btnCerrar = document.getElementById("btnCerrarChat");
    const lista = document.getElementById("lista");
    const btnEnviar = document.getElementById("btnEnviar");
    const msgInput = document.getElementById("msgInput");
    const barraInferior = document.querySelector(".iconos-inferiores");
    const inputBusca = document.getElementById("Busca");
    const listaDerecha = document.getElementById("lista-derecha");

    // CARGAR LISTA PRINCIPAL
    fetch("/api/usuarios")
        .then(res => res.json())
        .then(usuarios => {
            lista.innerHTML = "";
            mapaUsuarios = {}; // Reiniciar el mapa
            usuarios.forEach(u => {
                if (parseInt(u.id) !== parseInt(idUsuarioActual)) {
                    mapaUsuarios[u.id] = u;
                    const div = document.createElement("div");
                    div.classList.add("chat-item");
                    div.dataset.id = u.id;
                    div.dataset.nombre = u.nombre_completo;
            
                    // LÓGICA DE FOTO CORREGIDA
                    let fotoFinal;
                    if (!u.foto_perfil || u.foto_perfil === 'default.png') {
                        fotoFinal = "./img/default.png";
                    } else {
                        // Si ya trae "uploads/", lo usamos directo desde la raíz
                        fotoFinal = `./${u.foto_perfil}`;
                    }
                    div.dataset.foto = fotoFinal; // Guardamos la ruta real corregida
            
                    div.innerHTML = `
                        <img src="${fotoFinal}" class="foto-chat" style="width:45px; height:45px; border-radius:50%; margin-right:10px;" onerror="this.src='./img/default.png'">
                        <div>
                            <h4 style="margin:0;">${u.nombre_completo} <span class="estado" id="estado-${u.id}"></span></h4>
                            <p class="status-text" style="margin:0; font-size:12px; color:gray;">${u.correo}</p> 
                        </div>`;
                    lista.appendChild(div);
                }
            });
            actualizarEstados();
            revisarMensajeDirecto();
        });

    // CARGAR LISTA DERECHA
    fetch("/api/usuarios")
        .then(res => res.json())
        .then(usuarios => {
            listaDerecha.innerHTML = "";
            usuarios.forEach(u => {
                // Comparar IDs convirtiendo ambos a números para evitar problemas de tipo
                if (parseInt(u.id) !== parseInt(idUsuarioActual)) {
                    const div = document.createElement("div");
                    div.classList.add("contacto-derecha");
                    div.dataset.id = u.id; // Usar ID
                    div.dataset.nombre = u.nombre_completo; // Mantener nombre
                    // Manejar foto_perfil (puede ser null, undefined, o string vacío)
                    const fotoPerfil = (u.foto_perfil && u.foto_perfil !== null && String(u.foto_perfil).trim() !== '')
                        ? String(u.foto_perfil).trim()
                        : 'default.png';
                    div.dataset.foto = fotoPerfil; // Guardar foto para usar después

                    div.innerHTML = `
                        <div class="contenedor-foto" style="position:relative;">
                            <img src="./img/${fotoPerfil}" style="width:35px; height:35px; border-radius:50%;" onerror="this.src='./img/default.png'">
                            <span class="estado" style="position:absolute; bottom:0; right:0;"></span>
                        </div>
                        <div class="info-derecha">
                            <span class="nombre-user">${u.nombre_completo}</span>
                            <small class="typing-status"></small>
                        </div>`;
                    listaDerecha.appendChild(div);
                }
            });
            actualizarEstados();
        });

    // BUSCADOR
    inputBusca.addEventListener("input", () => {
        const textoFiltro = inputBusca.value.toLowerCase();
        document.querySelectorAll(".chat-item").forEach(contacto => {
            const nombreContacto = contacto.dataset.nombre.toLowerCase();
            contacto.style.display = nombreContacto.includes(textoFiltro) ? "flex" : "none";
        });
    });

    // CLIC EN LISTA PRINCIPAL
    lista.addEventListener("click", (e) => {
        const item = e.target.closest(".chat-item");
        if (!item) return;
        const idContacto = item.dataset.id;
        const nombreSeleccionado = item.dataset.nombre;
        const fotoContacto = item.dataset.foto || 'default.png';

        // Limpiar mensajes no leídos al abrir el chat
        limpiarMensajesNoLeidos(idContacto);

        // Guardar borrador de la sala anterior si existe
        if (salaActual) {
            borradores[salaActual] = document.getElementById("msgInput").value;
        }

        // Crear sala usando IDs ordenados
        const idsSala = [idUsuarioActual, idContacto].map(id => parseInt(id)).sort((a, b) => a - b);
        salaActual = idsSala.join("-");

        // Restaurar borrador o limpiar
        const inputMsg = document.getElementById("msgInput");
        inputMsg.value = borradores[salaActual] || "";

        // Sincronizar selección en lista derecha
        const itemDerecha = document.querySelector(`.contacto-derecha[data-id="${idContacto}"]`);
        if (itemDerecha) {
            document.querySelectorAll(".contacto-derecha").forEach(el => el.classList.remove("contacto-activo"));
            itemDerecha.classList.add("contacto-activo");
            // Opcional: Scrollear para mostrarlo si está oculto
            // itemDerecha.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }

        document.getElementById("msnBody").innerHTML = "<p style='text-align:center;'>Cargando mensajes privados...</p>";
        socket.emit("unirse_sala", { sala: salaActual, id_usuario: idUsuarioActual, nombre_usuario: usuarioActual });

        moduloLista.style.display = "none";
        moduloChat.style.display = "flex";
        if (barraInferior) barraInferior.classList.add("oculto");
        if (nombreChat) nombreChat.textContent = nombreSeleccionado;

        // Actualizar la foto del contacto en el header
        const fotoHeader = document.getElementById("fotoChatSeleccionado");
        if (fotoHeader) {
            fotoHeader.src = `./img/${fotoContacto}`;
            fotoHeader.onerror = function () {
                this.src = './img/default.png';
            };
        }

        actualizarEstados();
    });

    // CLIC EN LISTA DERECHA
    listaDerecha.addEventListener("click", (e) => {
        const item = e.target.closest(".contacto-derecha");
        if (!item) return;
        document.querySelectorAll(".contacto-derecha").forEach(el => el.classList.remove("contacto-activo"));
        item.classList.add("contacto-activo");

        const idContacto = item.dataset.id;
        const nombreContacto = item.dataset.nombre;
        const fotoContacto = item.dataset.foto || 'default.png';

        // Guardar borrador de la sala anterior si existe
        if (salaActual) {
            document.getElementById("msgInput").value = ""; // Limpiar visualmente, aunque se guarda al cambiar
        }

        // --- INICIO MODIFICACIÓN ---
        // Recuperar sala anterior real antes de sobrescribir
        // (Aunque `salaActual` es global)
        const salaAnterior = salaActual;
        if (salaAnterior) {
            borradores[salaAnterior] = document.getElementById("msgInput").value;
        }

        // Crear sala usando IDs ordenados
        const idsSala = [idUsuarioActual, idContacto].map(id => parseInt(id)).sort((a, b) => a - b);
        salaActual = idsSala.join("-");

        // Restaurar borrador
        document.getElementById("msgInput").value = borradores[salaActual] || "";
        // --- FIN MODIFICACIÓN ---

        document.getElementById("msnBody").innerHTML = "";
        document.getElementById("nombreChatSeleccionado").textContent = nombreContacto;

        // Actualizar la foto del contacto en el header
        const fotoHeader = document.getElementById("fotoChatSeleccionado");
        if (fotoHeader) {
            fotoHeader.src = `./img/${fotoContacto}`;
            fotoHeader.onerror = function () {
                this.src = 'img/default.png';
            };
        }

        socket.emit("unirse_sala", { sala: salaActual, id_usuario: idUsuarioActual, nombre_usuario: usuarioActual });
        actualizarEstados();
    });

    // ENVIAR MENSAJES Y TYPING
    function enviarMensaje() {
        const texto = msgInput.value.trim();
        if (texto === "" || !salaActual) return;
        // NO agregamos el mensaje localmente, esperamos a que el servidor lo confirme
        msgInput.disabled = true;
        socket.emit("nuevo_mensaje", {
            sala: salaActual,
            id_usuario: idUsuarioActual,
            nombre_usuario: usuarioActual,
            mensaje: texto
        });
        msgInput.disabled = false;
        msgInput.focus();
        msgInput.value = "";
    }

    btnEnviar.addEventListener("click", enviarMensaje);
    msgInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") enviarMensaje();
        socket.emit("typing", { sala: salaActual, id_usuario: idUsuarioActual, nombre_usuario: usuarioActual });
    });

    btnCerrar.addEventListener("click", () => {
        moduloChat.style.display = "none";
        moduloLista.style.display = "block";
        if (barraInferior) barraInferior.classList.remove("oculto");

        // Guardar borrador antes de salir
        if (salaActual) {
            borradores[salaActual] = document.getElementById("msgInput").value;
        }
        salaActual = null;
    });

    function revisarMensajeDirecto() {
        // Evitar procesar múltiples veces
        if (contactoDirectoProcesado) return;

        const params = new URLSearchParams(window.location.search);
        const contactoURL = params.get('contacto'); // Esto puede ser ID o nombre
        if (contactoURL) {
            contactoDirectoProcesado = true; // Marcar como procesado

            setTimeout(() => {
                // Limpiar el parámetro de la URL para evitar recargas
                const url = new URL(window.location.href);
                url.searchParams.delete('contacto');
                window.history.replaceState({}, '', url);

                // Intentar buscar por ID primero (convertir a número si es posible)
                let idContacto = contactoURL;
                // Si el ID contiene ":" (como "1:1"), tomar solo la primera parte
                if (contactoURL.includes(':')) {
                    idContacto = contactoURL.split(':')[0];
                }

                let item = document.querySelector(`.chat-item[data-id="${idContacto}"]`);
                if (!item) {
                    // Intentar buscar por nombre como fallback
                    item = document.querySelector(`.chat-item[data-nombre="${contactoURL}"]`);
                }
                if (item) {
                    item.click();
                }
            }, 800);
        }
    }
}
