console.log("Servidor NEXUS: Iniciando sistema...");

const express = require("express");
const app = express();
const http = require("http").createServer(app);
const path = require("path");
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
const usuariosOnline = {}; // Usuarios conectados

// ===============================
// SERVIR FRONTEND
// ===============================
app.use(express.static(path.join(__dirname, "public"))); // carpeta public

app.get("/", (req, res) => {
    res.sendFile(path.join(__dirname, "public", "index.html"));
});

// ===============================
// SOCKET.IO (SIN CONEXIÓN DIRECTA A BD)
// ===============================
io.on("connection", (socket) => {
    console.log("🟢 Usuario conectado:", socket.id);

    // -------------------
    // Usuario online
    // -------------------
    socket.on("usuario_online", (data) => {
        const usuarioData = typeof data === 'object' ? data : { nombre: data, id: null };
        socket.username = usuarioData.nombre;
        socket.userId = usuarioData.id;
        if (usuarioData.id) usuariosOnline[usuarioData.id] = usuarioData.nombre;
        io.emit("usuarios_online", usuariosOnline);
    });

    // -------------------
    // Unirse a sala y emitir historial (desde API)
    // -------------------
    socket.on("unirse_sala", (data) => {
        const { sala, historial } = data; // historial debe venir desde la API
        socket.join(sala);
        socket.salaActual = sala;

        socket.emit("cargar_historial", historial || []);
    });

    // -------------------
    // Nuevo mensaje
    // -------------------
    socket.on("nuevo_mensaje", (data) => {
        const { sala } = data;
        io.to(sala).emit("mensaje_recibido", data);

        // Notificación a destinatario
        const ids = sala.split('-').map(Number);
        const idDest = ids.find(id => id !== Number(data.id_usuario));
        Array.from(io.sockets.sockets.values())
            .filter(s => s.userId == idDest && s.salaActual !== sala)
            .forEach(s => s.emit("notificacion_nuevo_mensaje", data));
    });

    // -------------------
    // Escribiendo
    // -------------------
    socket.on("typing", (data) => {
        socket.to(data.sala).emit("display_typing", data);
    });

    // -------------------
    // Desconexión
    // -------------------
    socket.on("disconnect", () => {
        if (socket.userId) {
            delete usuariosOnline[socket.userId];
            io.emit("usuarios_online", usuariosOnline);
        }
    });

    // -------------------
    // Historial de contactos (debe venir de API)
    // -------------------
    socket.on("obtener_historial_contactos", (data) => {
        socket.emit("enviar_historial_contactos", data.contactos || []);
    });
});

// ===============================
// INICIAR SERVIDOR
// ===============================
http.listen(PORT, () => {
    console.log(`✅ Servidor NEXUS activo en el puerto ${PORT}`);
});

process.on("uncaughtException", err => console.error("🔥 Uncaught Exception:", err));
process.on("unhandledRejection", err => console.error("🔥 Unhandled Rejection:", err));
