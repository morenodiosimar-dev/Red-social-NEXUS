console.log("Servidor NEXUS: Iniciando sistema...");

const express = require("express");
const app = express();
const http = require("http").createServer(app);
const mysql = require("mysql2");
const path = require("path");
const PORT = process.env.PORT || 3000;

console.log(`🚀 Intentando iniciar en puerto: ${PORT}`);

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
// CONEXIÓN A LA BASE DE DATOS
// ===============================
console.log("🔍 Verificando variables de entorno MySQL:");
console.log("MYSQLHOST:", process.env.MYSQLHOST ? "✅ Configurado" : "❌ NO configurado");
console.log("MYSQLUSER:", process.env.MYSQLUSER ? "✅ Configurado" : "❌ NO configurado");
console.log("MYSQLDATABASE:", process.env.MYSQLDATABASE ? "✅ Configurado" : "❌ NO configurado");
console.log("MYSQLPORT:", process.env.MYSQLPORT ? "✅ Configurado" : "❌ NO configurado");

const dbConfig = {
    host: process.env.MYSQLHOST,
    user: process.env.MYSQLUSER,
    password: process.env.MYSQLPASSWORD,
    database: process.env.MYSQLDATABASE,
    port: process.env.MYSQLPORT || 3306,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
    enableKeepAlive: true,
    keepAliveInitialDelay: 10000,
    connectTimeout: 60000, // 60 segundos para conectar
    acquireTimeout: 60000, // 60 segundos para adquirir conexión
    timeout: 60000, // 60 segundos timeout general
    ssl: (process.env.MYSQLHOST && !process.env.MYSQLHOST.includes('internal'))
        ? { rejectUnauthorized: false }
        : false
};

console.log("📡 Host MySQL:", process.env.MYSQLHOST);
if (process.env.MYSQLHOST && process.env.MYSQLHOST.includes('internal')) {
    console.log("🔒 Conexión Interna: SSL desactivado para mayor compatibilidad.");
}

let db = mysql.createPool(dbConfig);

// Verificar conexión inicial SIN tumbar el servidor
db.getConnection((err, conn) => {
    if (err) {
        console.error("❌ Error inicial de MySQL (El servidor seguirá funcionando):", err.message);
        console.error("Código de error:", err.code);
        console.error("⚠️ IMPORTANTE: Verifica las variables de entorno en Railway");
    } else {
        console.log("📡 MySQL listo y operativo");
        conn.release();
    }
});

// Manejo de errores del Pool para evitar "Uncaught Exception"
db.on('error', (err) => {
    console.error('🔥 Error en el Pool de MySQL:', err.code);
    if (err.code === 'PROTOCOL_CONNECTION_LOST' || err.code === 'ECONNREFUSED') {
        console.log('🔄 Intentando reconstruir el Pool...');
        db = mysql.createPool(dbConfig);
    }
});

// Mantener viva la conexión (solo si está conectada)
setInterval(() => {
    db.query('SELECT 1', (err) => {
        if (err) console.error("⚠️ Error de ping a MySQL:", err.code);
    });
}, 30000); // Cada 30 segundos

// ===============================
// SERVIR FRONTEND Y API
// ===============================
app.use(express.static(path.join(__dirname, "public")));

// Servir archivos de uploads (fotos de perfil)
app.use('/uploads', express.static(path.join(__dirname, "uploads")));

// Health check indestructible para Railway (Elimina el 502)
app.get("/health", (req, res) => {
    res.status(200).send("OK");
});

app.get("/", (req, res) => {
    res.sendFile(path.join(__dirname, "public", "index.html"));
});

// API para obtener el usuario actual (desde query params)
app.get("/api/devolver_usuario", (req, res) => {
    // Intentar obtener de query params
    const userId = req.query.id || req.query.usuario_id;

    if (userId) {
        const query = "SELECT id, nombre, apellido, correo, foto_perfil FROM usuarios WHERE id = ?";
        db.query(query, [userId], (err, results) => {
            if (err || results.length === 0) {
                return res.json({
                    usuario: "Invitado",
                    id_usuario: null
                });
            }
            const user = results[0];
            res.json({
                usuario: `${user.nombre} ${user.apellido}`,
                id_usuario: user.id,
                nombre: user.nombre,
                apellido: user.apellido,
                correo: user.correo,
                foto_perfil: user.foto_perfil
            });
        });
    } else {
        // Si no hay ID, devolver invitado
        res.json({
            usuario: "Invitado",
            id_usuario: null
        });
    }
});

// API de Usuarios con manejo de errores
app.get("/api/usuarios", (req, res) => {
    const query = "SELECT id, nombre, apellido, correo, foto_perfil FROM usuarios";
    db.query(query, (err, results) => {
        if (err) return res.status(500).json({ error: "Base de datos desconectada" });
        const usuarios = results.map(u => ({
            ...u,
            nombre_completo: `${u.nombre} ${u.apellido}`,
            foto_perfil: u.foto_perfil || 'default.png'
        }));
        res.json(usuarios);
    });
});
// ===============================
// SOCKET.IO
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
    // Unirse a sala y cargar historial
    // -------------------
    socket.on("unirse_sala", (data) => {
        const { sala, id_usuario } = data;
        socket.join(sala);
        socket.salaActual = sala;
        socket.userId = id_usuario;

        const query = "SELECT usuario AS id_usuario, mensaje, fecha FROM mensajes WHERE sala = ? ORDER BY fecha ASC";
        db.query(query, [sala], (err, results) => {
            if (err) return console.error("❌ Error historial:", err);
            socket.emit("cargar_historial", results);
        });
    });

    // -------------------
    // Nuevo mensaje
    // -------------------
    socket.on("nuevo_mensaje", (data) => {
        const { sala, id_usuario, nombre_usuario, mensaje } = data;

        const query = "INSERT INTO mensajes (sala, usuario, mensaje) VALUES (?, ?, ?)";
        db.query(query, [sala, id_usuario, mensaje], (err) => {
            if (err) return console.error("❌ Error guardando mensaje:", err);

            // Emitir mensaje a la sala
            io.to(sala).emit("mensaje_recibido", data);

            // Notificación a destinatario si no está en la sala
            const ids = sala.split('-').map(Number);
            const idDest = ids.find(id => id !== Number(id_usuario));

            Array.from(io.sockets.sockets.values())
                .filter(s => s.userId == idDest && s.salaActual !== sala)
                .forEach(s => s.emit("notificacion_nuevo_mensaje", {
                    id_remitente: id_usuario,
                    nombre_remitente: nombre_usuario,
                    mensaje,
                    sala
                }));
        });
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
    // Historial de contactos
    // -------------------
    socket.on("obtener_historial_contactos", (data) => {
        const miId = typeof data === 'object' ? data.id_usuario : data;

        const query = "SELECT DISTINCT sala FROM mensajes WHERE sala LIKE ? OR sala LIKE ?";
        const busqueda1 = `${miId}-%`;
        const busqueda2 = `%-${miId}`;

        db.query(query, [busqueda1, busqueda2], (err, results) => {
            if (err) return console.error(err);

            const contactosIds = [];
            results.forEach(row => {
                const partes = row.sala.split('-').map(id => parseInt(id));
                const otroId = partes.find(id => id !== parseInt(miId));
                if (otroId && !contactosIds.includes(otroId)) contactosIds.push(otroId);
            });

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

                const contactos = usuarios.map(u => ({
                    id: u.id,
                    nombre_completo: `${u.nombre} ${u.apellido}`.trim(),
                    // Si ya tiene uploads/ en la base de datos, lo usamos, si no, lo agregamos
                    foto_perfil: u.foto_perfil ? (u.foto_perfil.startsWith('uploads/') ? u.foto_perfil : 'uploads/' + u.foto_perfil) : 'img/default.png'
                }));

                socket.emit("enviar_historial_contactos", contactos);
            });
        });
    });
});

// ===============================
// INICIAR SERVIDOR
// ===============================
http.listen(PORT, "0.0.0.0", () => {
    console.log("------------------------------------------");
    console.log(`✅ Servidor NEXUS ONLINE en puerto ${PORT}`);
    console.log(`📡 Escuchando en 0.0.0.0 (Requerido por Railway)`);
    console.log("------------------------------------------");
});

process.on("uncaughtException", err => console.error("🔥 Uncaught Exception:", err));
process.on("unhandledRejection", err => console.error("🔥 Unhandled Rejection:", err));
