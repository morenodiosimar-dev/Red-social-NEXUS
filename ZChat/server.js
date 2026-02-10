console.log("Servidor NEXUS: Iniciando sistema...");

const express = require("express");
const app = express();
const http = require("http").createServer(app);
const mysql = require("mysql2");
const path = require("path");
const fetch = (...args) => import('node-fetch').then(({default: fetch}) => fetch(...args)); // Necesario para el puente

const PORT = process.env.PORT || 3000;
// ===============================
// CONFIGURACIÓN DE SOCKET.IO
// ===============================
const io = require("socket.io")(http, {
    cors: {
       origin: "*", 
       methods: ["GET", "POST"],
       credentials: true
    }
});
const usuariosOnline = {}; // { id: nombre }

// ===============================
// CONEXIÓN A LA BASE DE DATOS (Configuración para Railway)
// ===============================
const db = mysql.createConnection({
    // Railway inyecta estas variables automáticamente si usas su MySQL
    host: process.env.MYSQLHOST || "localhost",
    user: process.env.MYSQLUSER || "root",
    password: process.env.MYSQLPASSWORD || "",
    database: process.env.MYSQLDATABASE || "railway",
    port: process.env.MYSQLPORT || 3306
});

db.connect(err => {
    if (err) {
        console.error("❌ Error conectando a la DB:", err.message);
        return;
    }
    console.log("📡 Conectado a la base de datos MySQL");
});

// Mantener la conexión viva (Evita el error PROTOCOL_CONNECTION_LOST)
setInterval(() => {
    db.query('SELECT 1');
}, 5000);

app.use(express.static(__dirname));

app.get("/api/usuarios", async (req, res) => {
    try {
        const fetch = (await import('node-fetch')).default; // Importación dinámica
        const response = await fetch('http://127.0.0.1:3000/usuarios.php');
        const data = await response.json();
        res.json(data);
    } catch (error) {
        console.error("Error conectando con PHP:", error);
        res.status(500).json({ error: "Error interno" });
    }
});

app.get("/", (req, res) => {
    res.sendFile(path.join(__dirname, "index.html"));
});
    // Unirse a una sala y CARGAR HISTORIAL
    socket.on("unirse_sala", (data) => {
        const sala = (typeof data === 'object') ? data.sala : data;
        const idUsuario = (typeof data === 'object') ? data.id_usuario : null;
        const nombreUsuario = (typeof data === 'object') ? data.nombre_usuario : "Invitado";
        
        socket.join(sala);
        socket.salaActual = sala; // Guardar la sala actual
        if (idUsuario) socket.userId = idUsuario;
        socket.username = nombreUsuario;
        
        console.log(`Usuario ${nombreUsuario} (ID: ${idUsuario}) se unió a la sala: ${sala}`);

        // CONSULTA DE HISTORIAL: El campo 'usuario' ahora guarda el ID del usuario
        const query = "SELECT usuario AS id_usuario, mensaje FROM mensajes WHERE sala = ? ORDER BY fecha ASC";
        db.query(query, [sala], (err, results) => {
            if (err) return console.error("❌ Error obteniendo historial:", err);
            
            // Enviamos los mensajes guardados SOLO al usuario que se acaba de unir
            socket.emit("cargar_historial", results);
        });
    });

    // Enviar y GUARDAR nuevo mensaje
    socket.on("nuevo_mensaje", (data) => {
        const { sala, id_usuario, nombre_usuario, mensaje } = data;
        
        // 1. GUARDAMOS EN LA DB: Guardamos el ID del usuario en el campo 'usuario'
        const query = "INSERT INTO mensajes (sala, usuario, mensaje) VALUES (?, ?, ?)";
        db.query(query, [sala, id_usuario, mensaje], (err) => {
            if (err) return console.error("❌ Error al guardar mensaje:", err);
            
            // 2. ENVIAR A LA SALA: Enviamos solo a la sala, esto incluye a todos los miembros de la sala
            io.to(sala).emit("mensaje_recibido", data);
            
            // 3. ENVIAR NOTIFICACIÓN AL DESTINATARIO SI NO ESTÁ EN LA SALA ACTIVA
            const idsSala = sala.split('-').map(id => parseInt(id));
            const idDestinatario = idsSala.find(id => id !== parseInt(id_usuario));
            
            if (idDestinatario) {
                // Buscar si el destinatario está conectado y en qué sala está
                const socketsDestinatario = Array.from(io.sockets.sockets.values())
                    .filter(s => s.userId === idDestinatario);
                
                socketsDestinatario.forEach(socketDest => {
                    // Si el destinatario no está en la misma sala, enviar notificación
                    if (socketDest.salaActual !== sala) {
                        socketDest.emit("notificacion_nuevo_mensaje", {
                            id_remitente: id_usuario,
                            nombre_remitente: nombre_usuario,
                            mensaje: mensaje,
                            sala: sala
                        });
                    }
                });
            }
            
            // Opcional: Actualizar el resumen del último mensaje en las listas
            io.emit("actualizar_ultimo_mensaje", data);
        });
    });

    // Lógica de "Escribiendo..."
    socket.on("typing", (data) => {
        // Avisa a la persona en la sala específica
        socket.to(data.sala).emit("display_typing", data);
        
        // Avisa globalmente para que aparezca en la lista principal de contactos
        socket.broadcast.emit("display_typing", data);
    });

    // Desconexión
    socket.on("disconnect", () => {
        if (socket.userId) {
            delete usuariosOnline[socket.userId];
            io.emit("usuarios_online", usuariosOnline);
            console.log("🔴 Usuario desconectado:", socket.username);
        }
    });

    // Obtener historial de contactos (basado en IDs)
    socket.on("obtener_historial_contactos", (data) => {
        const miId = typeof data === 'object' ? data.id_usuario : data;
        
        // Buscar salas que contengan el ID del usuario
        const query = "SELECT DISTINCT sala FROM mensajes WHERE sala LIKE ? OR sala LIKE ?";
        const busqueda1 = `${miId}-%`;
        const busqueda2 = `%-${miId}`;

        db.query(query, [busqueda1, busqueda2], (err, results) => {
            if (err) return console.error(err);

            // Extraer el ID del contacto (el que no es el usuario actual)
            const contactosIds = [];
            results.forEach(row => {
                const partes = row.sala.split('-').map(id => parseInt(id));
                const otroId = partes.find(id => id !== parseInt(miId));
                if (otroId && !contactosIds.includes(otroId)) {
                    contactosIds.push(otroId);
                }
            });

            // Obtener los nombres de los contactos desde la base de datos
            if (contactosIds.length === 0) {
                socket.emit("enviar_historial_contactos", []);
                return;
            }

            const placeholders = contactosIds.map(() => '?').join(',');
            const queryNombres = `SELECT id, nombre, apellido, foto_perfil FROM usuarios WHERE id IN (${placeholders})`;
            db.query(queryNombres, contactosIds, (err2, usuarios) => {
                if (err2) {
                    console.error("Error obteniendo nombres:", err2);
                    socket.emit("enviar_historial_contactos", contactosIds.map(id => id.toString()));
                    return;
                }

                // Crear mapa de ID a nombre completo y foto de perfil
                const contactos = usuarios.map(u => ({
                    id: u.id,
                    nombre_completo: `${u.nombre} ${u.apellido}`.trim(),
                    foto_perfil: u.foto_perfil || 'default.png'
                }));

                socket.emit("enviar_historial_contactos", contactos);
            });
        });
    });


// ===============================
// INICIAR SERVIDOR
// ===============================

http.listen(PORT, "0.0.0.0", () => {
    console.log(`✅ Servidor NEXUS activo en el puerto ${PORT}`);
});
