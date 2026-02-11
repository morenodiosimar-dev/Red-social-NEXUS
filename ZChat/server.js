console.log("Servidor NEXUS: Iniciando sistema...");

const express = require("express");
const app = express();
const http = require("http").createServer(app);
const mysql = require("mysql2");
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
const usuariosOnline = {}; 

// ===============================
// CONEXIÓN A LA BASE DE DATOS
// ===============================

const db = mysql.createPool({
    host: process.env.MYSQLHOST,
    user: process.env.MYSQLUSER,
    password: process.env.MYSQLPASSWORD,
    database: process.env.MYSQLDATABASE,
    port: process.env.MYSQLPORT,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

db.query("SELECT 1", (err) => {
    if (err) {
        console.error("❌ MySQL no responde:", err.message);
    } else {
        console.log("📡 MySQL listo y operativo");
    }
});


setInterval(() => { db.query('SELECT 1'); }, 5000);

// Sustituye a 'usuarios.php'
app.get("/api/usuarios", (req, res) => {
    const query = "SELECT id, nombre, apellido, correo, foto_perfil FROM usuarios";
    db.query(query, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        
        const usuarios = results.map(u => ({
            ...u,
            nombre_completo: `${u.nombre} ${u.apellido}`,
            foto_perfil: (u.foto_perfil && u.foto_perfil !== 'default.png') ? u.foto_perfil : 'default.png'
        }));
        res.json(usuarios);
    });
});

// Sustituye a 'devuelve.php'
app.get("/api/devolver_usuario", (req, res) => {
    // Simulación de sesión (aquí puedes integrar tu sistema de login real después)
    res.json({ 
        success: true, 
        id_usuario: 1, 
        usuario: "Admin" 
    });
});

app.use(express.static(path.join(__dirname, "public")));

app.get("/", (req, res) => {
    res.sendFile(path.join(__dirname, "public", "index.html"));
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
        
        const query = "SELECT usuario AS id_usuario, mensaje FROM mensajes WHERE sala = ? ORDER BY fecha ASC";
        db.query(query, [sala], (err, results) => {
            if (err) return console.error("❌ Error historial:", err);
            socket.emit("cargar_historial", results);
        });
    });

    socket.on("nuevo_mensaje", (data) => {
        const { sala, id_usuario, nombre_usuario, mensaje } = data;
        const query = "INSERT INTO mensajes (sala, usuario, mensaje) VALUES (?, ?, ?)";
        db.query(query, [sala, id_usuario, mensaje], (err) => {
            if (err) return console.error("❌ Error guardando mensaje:", err);
            io.to(sala).emit("mensaje_recibido", data);
            
            // Lógica de notificaciones
            const ids = sala.split('-').map(Number);
            const idDest = ids.find(id => id !== Number(id_usuario));
            
            Array.from(io.sockets.sockets.values())
                .filter(s => s.userId == idDest && s.salaActual !== sala)
                .forEach(s => s.emit("notificacion_nuevo_mensaje", {
                    id_remitente: id_usuario,
                    nombre_remitente: nombre_usuario,
                    mensaje: mensaje,
                    sala: sala
                }));
        });
    });

    socket.on("typing", (data) => {
        socket.to(data.sala).emit("display_typing", data);
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


// INICIAR SERVIDOR
// ===============================

http.listen(PORT, () => {
    console.log(`✅ Servidor NEXUS activo en el puerto ${PORT}`);
});


process.on("uncaughtException", err => {
    console.error("🔥 Uncaught Exception:", err);
});

process.on("unhandledRejection", err => {
    console.error("🔥 Unhandled Rejection:", err);
});
