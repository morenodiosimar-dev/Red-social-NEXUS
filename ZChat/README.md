# NEXUS Chat - Configuración para Railway

## 📋 Variables de Entorno Requeridas

Para que el chat funcione correctamente en Railway, debes configurar las siguientes variables de entorno en tu proyecto:

### Variables de MySQL (Railway)

Cuando creas una base de datos MySQL en Railway, automáticamente se generan estas variables. Debes copiarlas a tu servicio de Node.js:

```
MYSQLHOST=<tu-host-mysql>.railway.app
MYSQLUSER=root
MYSQLPASSWORD=<tu-password>
MYSQLDATABASE=railway
MYSQLPORT=3306
```

## 🚀 Pasos para Desplegar en Railway

### 1. Crear Base de Datos MySQL

1. En tu proyecto de Railway, haz clic en **"+ New"**
2. Selecciona **"Database"** → **"Add MySQL"**
3. Espera a que se cree la base de datos
4. Railway generará automáticamente las variables de entorno

### 2. Crear las Tablas Necesarias

Conéctate a tu base de datos MySQL en Railway y ejecuta:

```sql
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(255) UNIQUE NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sala VARCHAR(50) NOT NULL,
    usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sala (sala),
    INDEX idx_usuario (usuario)
);
```

### 3. Configurar el Servicio de Node.js

1. En Railway, haz clic en **"+ New"** → **"GitHub Repo"** (o "Empty Service")
2. Selecciona tu repositorio del chat
3. Ve a **"Variables"** en el servicio
4. Haz clic en **"Reference"** y selecciona las variables de MySQL:
   - `MYSQLHOST`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`
   - `MYSQLDATABASE`
   - `MYSQLPORT`

### 4. Verificar el Despliegue

1. Espera a que el despliegue termine
2. Abre la URL de tu aplicación
3. Agrega `/health` al final de la URL para verificar el estado
   - Ejemplo: `https://tu-app.railway.app/health`
   - Deberías ver: `{"status":"healthy","database":"connected","timestamp":"..."}`

## 🔧 Solución de Problemas

### Error 502 Bad Gateway

**Causas comunes:**
- Variables de entorno no configuradas
- Base de datos MySQL no está lista
- Timeout de conexión

**Soluciones:**
1. Verifica que todas las variables de entorno estén configuradas
2. Revisa los logs en Railway: `View Logs`
3. Busca mensajes como "❌ Error inicial de MySQL"
4. Asegúrate de que la base de datos MySQL esté en el mismo proyecto

### Base de datos no se conecta

1. Verifica que las variables estén correctamente referenciadas
2. Asegúrate de que ambos servicios (Node.js y MySQL) estén en el mismo proyecto
3. Revisa los logs para ver el código de error específico

### Mensajes no se guardan

1. Verifica que las tablas estén creadas
2. Revisa los logs del servidor
3. Prueba el endpoint `/health` para ver si la conexión está activa

## 📝 Notas Importantes

- El servidor inicia INCLUSO si MySQL no está disponible (evita error 502)
- La conexión se reintenta automáticamente cada 30 segundos
- El healthcheck verifica tanto el servidor como la base de datos
- SSL está configurado automáticamente para Railway

## 🔗 Endpoints Disponibles

- `/` - Página principal del chat
- `/health` - Estado del servidor y base de datos
- `/api/usuarios` - Lista de usuarios registrados

## 📞 Soporte

Si sigues teniendo problemas, revisa los logs en Railway y busca mensajes de error específicos.
