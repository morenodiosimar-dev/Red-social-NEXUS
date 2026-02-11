-- ===============================================
-- NEXUS Chat - Script de Base de Datos
-- Para ejecutar en Railway MySQL
-- ===============================================

-- ⚠️ IMPORTANTE: La tabla 'usuarios' YA EXISTE en tu base de datos
-- Este script solo crea la tabla de mensajes para el chat

-- Verificar que la tabla usuarios existe
SELECT 'Verificando tabla usuarios...' AS status;
SELECT COUNT(*) AS total_usuarios FROM usuarios;

-- Crear tabla de mensajes (si no existe)
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sala VARCHAR(50) NOT NULL,
    usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE,
    INDEX idx_sala (sala),
    INDEX idx_usuario (usuario),
    INDEX idx_fecha (fecha),
    FOREIGN KEY (usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar que las tablas están listas
SELECT 'Tablas creadas exitosamente' AS status;
SHOW TABLES;

-- Verificar estructura de usuarios (debe tener: id, nombre, apellido, correo, foto_perfil, etc.)
DESCRIBE usuarios;

-- Verificar estructura de mensajes
DESCRIBE mensajes;

-- Verificar cuántos usuarios hay registrados
SELECT COUNT(*) AS total_usuarios FROM usuarios;
SELECT id, nombre, apellido, correo FROM usuarios LIMIT 5;
