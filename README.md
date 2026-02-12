# Proyecto NORE - Monorepo

Este repositorio contiene múltiples aplicaciones del proyecto NORE.

## 📁 Estructura del Repositorio

### Proyectos Activos

#### 📱 Proyecto_Prueba
- **Tipo**: Aplicación PHP standalone
- **Descripción**: Sistema principal de gestión de usuarios, perfiles y publicaciones
- **Deployment**: Hosting PHP tradicional (cPanel, Plesk, etc.)
- **Archivos**: 44 archivos PHP + assets
- **Base de datos**: MySQL (nexus_db compartida)

#### 💬 laravel_chat  
- **Tipo**: Aplicación Laravel 10
- **Descripción**: Sistema de chat en tiempo real con WebSockets
- **Deployment**: **Railway** (region: us-east4)
- **Stack**: Laravel + Pusher + Laravel Echo + MySQL
- **Documentación**: [laravel_chat/DESPLEGUE_COMPLETO.md](laravel_chat/DESPLEGUE_COMPLETO.md)

### Archivos Legacy (Archivados)

#### archives/Chat
- Sistema de chat Node.js legacy (reemplazado por laravel_chat)
- **Estado**: Archivado, NO en producción
- Preservado solo como referencia histórica

---

## 🚀 Deployment

### Proyecto_Prueba

**Hosting Tradicional PHP**:
1. Subir archivos vía FTP/cPanel File Manager
2. Configurar base de datos MySQL en `conn.php`
3. Configurar permisos de directorio `uploads/`
4. Acceder vía navegador

**Requisitos**:
- PHP 7.4+
- MySQL 5.7+
- Soporte para sesiones PHP

---

### laravel_chat (Railway)

#### Configuración Inicial

**1. Preparar Repositorio Git**
```bash
# Subir a GitHub/GitLab
git remote add origin https://github.com/TU_USUARIO/TU_REPO.git
git push -u origin main
```

**2. Crear Proyecto en Railway**
1. Ir a [railway.app](https://railway.app)
2. "New Project" → "Deploy from GitHub repo"
3. Seleccionar este repositorio
4. Agregar MySQL database (Add → Database → MySQL)

**3. ⚠️ CRÍTICO: Configurar Root Directory**

En Railway Dashboard:
- Ir a: **Service → Settings → Source**
- **Root Directory**: `laravel_chat`

> Esto le dice a Railway que solo construya y despliegue el contenido de `laravel_chat/`, ignorando `Proyecto_Prueba/` y otros archivos del repo.

**4. Variables de Entorno**

Configurar en **Service → Variables**:

```env
# Application
APP_NAME=NexusChat
APP_ENV=production
APP_DEBUG=false
APP_KEY=                    # Generar con: php artisan key:generate --show
APP_URL=                    # Auto-asignado por Railway

# Database (Railway MySQL - auto-populated)
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}

# Broadcasting
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=mt1

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

**5. Deploy**

Railway detectará automáticamente Laravel y ejecutará:
- Install: `composer install --no-dev --optimize-autoloader`
- Build: `php artisan config:cache && php artisan route:cache`
- Start: `php artisan serve --host=0.0.0.0 --port=$PORT`

**6. Migraciones**

Ejecutar después del primer deploy:
```bash
# Instalar Railway CLI
npm i -g @railway/cli

# Conectar al proyecto
railway link

# Ejecutar migraciones
railway run php artisan migrate --force
```

---

## 📊 Arquitectura del Monorepo

```
proyecto nore/
├── .git/                       # Control de versiones
├── .gitignore                  # Excluye vendor, .env, node_modules
├── .nixpacks.toml              # Config Nixpacks para Railway
├── Procfile                    # Proceso Railway
├── railway.toml                # Config Railway (region, restart policy)
├── README.md                   # Este archivo
│
├── Proyecto_Prueba/            # ✅ PROYECTO ACTIVO 1
│   ├── Index.html              # Landing page
│   ├── conn.php                # Conexión DB
│   ├── login.php, ...          # Múltiples módulos PHP
│   └── uploads/                # Assets de usuarios
│
├── laravel_chat/               # ✅ PROYECTO ACTIVO 2 (Railway)
│   ├── app/                    # Código Laravel
│   ├── public/                 # Web root
│   ├── routes/                 # Rutas
│   ├── composer.json           # Dependencias
│   ├── artisan                 # CLI Laravel
│   └── .env.example            # Template de config
│
└── archives/                   # 📦 Legacy (no deploy)
    └── Chat/                   # Node.js app antigua
```

---

## ⚙️ Base de Datos Compartida

Ambos proyectos pueden compartir la misma base de datos MySQL (`nexus_db`):

- **Proyecto_Prueba**: Conexión directa vía `conn.php`
- **laravel_chat**: Conexión vía Laravel Eloquent

**Tablas**:
- `usuarios` - Compartida entre ambos proyectos
- `mensajes` - Solo para laravel_chat
- `publicaciones`, `comentarios`, etc. - Solo para Proyecto_Prueba

---

## 🔧 Desarrollo Local

### Proyecto_Prueba
```bash
# Servidor PHP built-in
cd Proyecto_Prueba
php -S localhost:8000
```

### laravel_chat
```bash
cd laravel_chat
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
# Acceder: http://localhost:8000
```

---

## 🛡️ Notas de Seguridad

- `.gitignore` excluye `.env` con credenciales sensibles
- `uploads/` de Proyecto_Prueba puede ser ignorado (opcional)
- Variables de entorno usadas para credenciales en producción
- Railway auto-maneja SSL/HTTPS

---

## 📚 Documentación Adicional

- **Laravel Chat**: Ver [laravel_chat/DESPLEGUE_COMPLETO.md](laravel_chat/DESPLEGUE_COMPLETO.md)
- **Railway**: Ver [RAILWAY_DEPLOYMENT_SUMMARY.md](RAILWAY_DEPLOYMENT_SUMMARY.md)
- **Pusher Setup**: https://pusher.com/docs

---

## ✅ Ventajas del Monorepo

1. **Un solo repositorio Git** para todos los proyectos relacionados
2. **Deployment flexible**: Cada proyecto puede ir a diferente hosting
3. **Código compartido** fácil (modelos, utilidades)
4. **Historial unificado** de cambios
5. **CI/CD simplificado** con Railway auto-deploy

---

## 🆘 Troubleshooting

### Railway construye proyecto incorrecto

**Solución**: Verificar que **Root Directory = `laravel_chat`** en Settings

### Nixpacks no detecta Laravel

**Solución**: Asegurar que `composer.json` existe en `laravel_chat/`

### Ambos proyectos comparten DB pero tienen conflictos

**Solución**: Usar prefijos de tabla diferentes o schemas separados

---

## 👥 Contribuir

Este es un proyecto privado. Para contribuir:
1. Crear branch feature: `git checkout -b feature/nueva-funcionalidad`
2. Commit cambios: `git commit -m "feat: descripción"`
3. Push branch: `git push origin feature/nueva-funcionalidad`
4. Crear Pull Request

---

**Desarrollado con ❤️ para NORE**
