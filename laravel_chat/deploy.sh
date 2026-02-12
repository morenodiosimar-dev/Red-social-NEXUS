#!/bin/bash

# 🚀 SCRIPT DE DESPLIEGUE AUTOMÁTICO - NEXUS CHAT LARAVEL
# Ejecutar en el servidor de producción

set -e  # Detener en caso de error

echo "🚀 INICIANDO DESPLIEGUE AUTOMÁTICO"
echo "=================================="

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir con colores
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    print_error "No se encuentra el archivo artisan"
    print_info "Ejecuta este script desde el directorio raíz de Laravel"
    exit 1
fi

print_status "✓ Directorio correcto detectado"

# Backup de archivos importantes
echo ""
print_info "🗄️ Creando backup de archivos importantes..."
BACKUP_DIR="backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p $BACKUP_DIR

if [ -f ".env" ]; then
    cp .env $BACKUP_DIR/
    print_status "✓ .env backup creado"
fi

if [ -d "storage" ]; then
    cp -r storage $BACKUP_DIR/
    print_status "✓ storage backup creado"
fi

# Verificar requisitos del sistema
echo ""
print_info "🔍 Verificando requisitos del sistema..."

# PHP Version
PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
REQUIRED_PHP="8.0"

if [ "$(printf '%s\n' "$REQUIRED_PHP" "$PHP_VERSION" | sort -V | head -n1)" = "$REQUIRED_PHP" ]; then
    print_status "✓ PHP $PHP_VERSION (requerido: $REQUIRED_PHP+)"
else
    print_error "PHP $PHP_VERSION (requerido: $REQUIRED_PHP+)"
    exit 1
fi

# Composer
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version | cut -d' ' -f3)
    print_status "✓ Composer $COMPOSER_VERSION"
else
    print_error "Composer no encontrado"
    exit 1
fi

# MySQL
if command -v mysql &> /dev/null; then
    print_status "✓ MySQL disponible"
else
    print_warning "MySQL no encontrado en PATH (puede estar instalado)"
fi

# Configuración del entorno
echo ""
print_info "⚙️ Configurando entorno..."

# Verificar .env
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_status "✓ .env creado desde .env.example"
        print_warning "⚠️  Debes configurar las variables de entorno manualmente"
    else
        print_error "No se encuentra .env.example"
        exit 1
    fi
else
    print_status "✓ .env existe"
fi

# Generar clave de aplicación
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
    print_status "✓ Clave de aplicación generada"
else
    print_status "✓ Clave de aplicación ya existe"
fi

# Instalar dependencias
echo ""
print_info "📦 Instalando dependencias de Composer..."

if [ "$APP_ENV" = "production" ] || [ "$APP_ENV" = "prod" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
    print_status "✓ Dependencias de producción instaladas"
else
    composer install --optimize-autoloader --no-interaction
    print_status "✓ Dependencias instaladas"
fi

# Optimizar Laravel
echo ""
print_info "⚡ Optimizando Laravel..."

php artisan config:clear
php artisan cache:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

print_status "✓ Cachés optimizados"

# Verificar base de datos
echo ""
print_info "🗄️ Verificando conexión a base de datos..."

if php artisan migrate:status &> /dev/null; then
    print_status "✓ Conexión a base de datos OK"
    
    # Ejecutar migraciones si es necesario
    if php artisan migrate:status | grep -q "Pending"; then
        print_info "Ejecutando migraciones pendientes..."
        php artisan migrate --force
        print_status "✓ Migraciones ejecutadas"
    else
        print_status "✓ Todas las migraciones están actualizadas"
    fi
else
    print_error "❌ Error de conexión a base de datos"
    print_info "Verifica la configuración DB_* en el archivo .env"
    exit 1
fi

# Configurar permisos
echo ""
print_info "🔐 Configurando permisos..."

# Permisos de carpetas
chmod -R 755 storage bootstrap/cache
chmod -R 755 public

# Permisos de archivos
find . -type f -name "*.php" -exec chmod 644 {} \;

# Propietario (ajustar según servidor)
# chown -R www-data:www-data storage bootstrap/cache public

print_status "✓ Permisos configurados"

# Verificar .htaccess
echo ""
print_info "🌐 Verificando configuración web..."

if [ ! -f "public/.htaccess" ]; then
    print_warning ".htaccess no encontrado en public/"
    print_info "Creando .htaccess por defecto..."
    
    cat > public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Disable PHP Error Display
php_flag display_errors off
php_value error_reporting E_ALL & ~E_DEPRECATED & ~E_STRICT

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
</IfModule>
EOF
    
    print_status "✓ .htaccess creado"
else
    print_status "✓ .htaccess existe"
fi

# Verificar configuración de Pusher
echo ""
print_info "📡 Verificando configuración de Pusher..."

if grep -q "PUSHER_APP_ID=" .env && grep -q "PUSHER_APP_KEY=" .env; then
    PUSHER_KEY=$(grep "PUSHER_APP_KEY=" .env | cut -d'=' -f2)
    if [ "$PUSHER_KEY" != "mi_pusher_app_key" ]; then
        print_status "✓ Pusher configurado"
    else
        print_warning "⚠️  Pusher configurado con valores por defecto"
        print_info "Debes actualizar las credenciales de Pusher en .env"
    fi
else
    print_warning "⚠️  Pusher no configurado"
    print_info "El chat funcionará sin tiempo real hasta configurar Pusher"
fi

# Test de funcionalidad básica
echo ""
print_info "🧪 Ejecutando tests básicos..."

# Test de ruta principal
if curl -s http://localhost/ | grep -q "Nexus Chat API"; then
    print_status "✓ API responde correctamente"
else
    print_warning "⚠️  API no responde (puede ser normal en producción)"
fi

# Test de conexión a base de datos
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';" 2>/dev/null | grep -q "DB OK"; then
    print_status "✓ Conexión a base de datos OK"
else
    print_error "❌ Error en conexión a base de datos"
    exit 1
fi

# Limpieza final
echo ""
print_info "🧹 Limpieza final..."

php artisan config:clear
php artisan cache:clear

print_status "✓ Limpieza completada"

# Resumen del despliegue
echo ""
echo "🎉 DESPLIEGUE COMPLETADO EXITOSAMENTE"
echo "====================================="

print_status "✓ Sistema de chat Laravel desplegado"
print_status "✓ Base de datos configurada"
print_status "✓ Permisos establecidos"
print_status "✓ Caches optimizados"

echo ""
print_info "📋 Próximos pasos:"

echo "1. Configurar credenciales de Pusher:"
echo "   - Editar .env"
echo "   - Actualizar PUSHER_APP_ID, PUSHER_APP_KEY, PUSHER_APP_SECRET"

echo ""
echo "2. Actualizar enlaces en sistema principal:"
echo "   - Cambiar http://localhost:3000 por /chat"

echo ""
echo "3. Probar funcionalidad:"
echo "   - Acceder a /chat desde el sistema principal"
echo "   - Verificar que los mensajes se envían y reciben"

echo ""
echo "4. Eliminar sistema Node.js:"
echo "   - Detener servidor Node.js"
echo "   - Eliminar archivos de Chat/ (opcional)"

echo ""
print_info "📁 Archivos importantes:"
echo "   - Configuración: .env"
echo "   - Logs: storage/logs/laravel.log"
echo "   - Backup: $BACKUP_DIR/"

echo ""
print_status "🚀 El sistema está listo para producción!"

# Opcional: Abrir en navegador (solo en desarrollo)
if [ "$APP_ENV" != "production" ]; then
    echo ""
    print_info "🌐 Abriendo en navegador..."
    if command -v xdg-open &> /dev/null; then
        xdg-open http://localhost:8000
    elif command -v open &> /dev/null; then
        open http://localhost:8000
    fi
fi

echo ""
print_status "🏁 Despliegue completado. ¡Disfruta tu nuevo chat Laravel!"
