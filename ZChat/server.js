const express = require("express");
const app = express();
const http = require("http").createServer(app);
const mysql = require("mysql2");
const session = require("express-session");

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
const usuariosOnline = {}; 

// ===============================
// CONEXIÓN A LA BASE DE DATOS (Pool para Railway)
// ===============================
const db = mysql.createPool({
    host: process.env.MYSQLHOST || "localhost",
    user: process.env.MYSQLUSER || "root",
    password: process.env.MYSQLPASSWORD || "",
    database: process.env.MYSQLDATABASE || "railway",
    port: process.env.MYSQLPORT || 3306,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Prueba de conexión inicial para el Pool
db.getConnection((err, connection) => {
    if (err) {
        console.error("❌ Error conectando a la DB en Railway:", err.message);
    } else {
        console.log("📡 Pool de conexiones MySQL listo");
        connection.release();
    }
});

// ===============================
// MIDDLEWARES
// ===============================
app.use(session({
    secret: 'te-llamo-desde-nexus',
    resave: false,
    saveUninitialized: true,
    cookie: { 
        secure: process.env.NODE_ENV === 'production', 
        maxAge: 1000 * 60 * 60 * 24 
    }
}));

// ===============================
// RUTAS API (Fuera de io.on)
// ===============================

app.get('/api/usuarios', (req, res) => {
    const query = "SELECT id, nombre, apellido, correo, foto_perfil, CONCAT(nombre, ' ', apellido) as nombre_completo FROM usuarios";
    db.query(query, (err, results) => {
        if (err) return res.status(500).json({ error: "Error en DB" });
        res.json(results);
    });
});

app.get("/api/devolver_usuario", (req, res) => {
    if (req.session && req.session.userId) {
        res.json({
            success: true,
            usuario: req.session.nombreCompleto,
            id_usuario: req.session.userId
        });
    } else {
        res.json({ success: false, message: "No hay sesión en Node" });
    }
});

// RUTA PUENTE: Para recibir datos del login externo
app.get("/api/set_session_externa", (req, res) => {
    const { id, nombre } = req.query;
    if (id && nombre) {
        req.session.userId = id;
        req.session.nombreCompleto = nombre;
        return res.send(`
            <script>
                console.log("Sesión sincronizada");
                window.location.href = "/"; 
            </script>
        `);
    }
    res.redirect("/");
});

// ===============================
// LÓGICA DE SOCKET.IO
// ===============================
io.on("connection", (socket) => {
    console.log("🟢 Usuario conectado:", socket.id);

    socket.on("usuario_online", (data) => {
        const usuarioData = typeof data === 'object' ? data : { nombre: data, id: null };
        socket.username = usuarioData.nombre;
        socket.userId = usuarioData.id;
        if (usuarioData.id) {
            usuariosOnline[usuarioData.id] = usuarioData.nombre;
        }
        io.emit("usuarios_online", usuariosOnline);
    });

    socket.on("unirse_sala", (data) => {
        const { sala, id_usuario, nombre_usuario } = data;
        socket.join(sala);
        socket.salaActual = sala;
        socket.userId = id_usuario;
        socket.username = nombre_usuario;

        const query = "SELECT usuario AS id_usuario, mensaje FROM mensajes WHERE sala = ? ORDER BY fecha ASC";
        db.query(query, [sala], (err, results) => {
            if (err) return console.error(err);
            socket.emit("cargar_historial", results);
        });
    });

    socket.on("nuevo_mensaje", (data) => {
        const { sala, id_usuario, nombre_usuario, mensaje } = data;
        const query = "INSERT INTO mensajes (sala, usuario, mensaje) VALUES (?, ?, ?)";
        db.query(query, [sala, id_usuario, mensaje], (err) => {
            if (err) return console.error(err);
            io.to(sala).emit("mensaje_recibido", data);

            // Lógica de notificaciones
            const idsSala = sala.split('-').map(id => parseInt(id));
            const idDestinatario = idsSala.find(id => id !== parseInt(id_usuario));
            
            if (idDestinatario) {
                Array.from(io.sockets.sockets.values())
                    .filter(s => s.userId == idDestinatario && s.salaActual !== sala)
                    .forEach(socketDest => {
                        socketDest.emit("notificacion_nuevo_mensaje", {
                            id_remitente: id_usuario,
                            nombre_remitente: nombre_usuario,
                            mensaje: mensaje,
                            sala: sala
                        });
                    });
            }
        });
    });

    socket.on("disconnect", () => {
        if (socket.userId) {
            delete usuariosOnline[socket.userId];
            io.emit("usuarios_online", usuariosOnline);
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
});

// ===============================
// INICIAR SERVIDOR
// ===============================

const PORT = process.env.PORT || 3000; // Railway siempre asigna el puerto en process.env.PORT
http.listen(PORT, "0.0.0.0", () => {
    console.log(`✅ Servidor NEXUS activo en el puerto ${PORT}`);
});
