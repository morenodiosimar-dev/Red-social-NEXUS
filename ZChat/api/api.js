const express = require("express");
const mysql = require("mysql2/promise");
const cors = require("cors");

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 3001;

// Conexión a MySQL de Railway
const db = mysql.createPool({
  host: process.env.MYSQLHOST || "mysql.railway.internal",
  user: process.env.MYSQLUSER || "root",
  password: process.env.MYSQLPASSWORD || "BpFRhFTLghAcqTRozKXkQyajMlYVqZCw",
  database: process.env.MYSQLDATABASE || "railway",
  port: process.env.MYSQLPORT || 3306,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

// Endpoint para usuarios
app.get("/api/usuarios", async (req, res) => {
  try {
    const [rows] = await db.query("SELECT id, nombre, apellido, correo, foto_perfil FROM usuarios");
    res.json(rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "No se pudo obtener usuarios" });
  }
});

// Endpoint para historial de mensajes de una sala
app.get("/api/mensajes/:sala", async (req, res) => {
  try {
    const sala = req.params.sala;
    const [rows] = await db.query("SELECT usuario AS id_usuario, mensaje FROM mensajes WHERE sala = ? ORDER BY fecha ASC", [sala]);
    res.json(rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "No se pudo obtener mensajes" });
  }
});

// Endpoint para enviar mensajes
app.post("/api/mensajes", async (req, res) => {
  try {
    const { sala, id_usuario, mensaje } = req.body;
    await db.query("INSERT INTO mensajes (sala, usuario, mensaje) VALUES (?, ?, ?)", [sala, id_usuario, mensaje]);
    res.json({ success: true });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "No se pudo enviar mensaje" });
  }
});

app.listen(PORT, () => console.log(`API corriendo en puerto ${PORT}`));
