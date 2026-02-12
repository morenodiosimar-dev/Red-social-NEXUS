#!/bin/bash

# Script de Pruebas para Nexus Chat Laravel
# Ejecuta todas las pruebas del sistema de chat migrado

echo "🚀 Iniciando Pruebas del Sistema de Chat Laravel"
echo "================================================"

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: No se encuentra el archivo artisan"
    echo "   Por favor, ejecuta este script desde el directorio raíz de Laravel"
    exit 1
fi

# Verificar variables de entorno
if [ ! -f ".env" ]; then
    echo "⚠️  Advertencia: No se encuentra el archivo .env"
    echo "   Creando archivo .env desde .env.example..."
    cp .env.example .env
    echo "   ✅ Archivo .env creado"
fi

# Generar clave de aplicación si no existe
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generando clave de aplicación..."
    php artisan key:generate
    echo "   ✅ Clave generada"
fi

# Limpiar caché anterior
echo "🧹 Limpiando caché anterior..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "   ✅ Caché limpiado"

# Ejecutar migraciones (si es necesario)
echo "🗄️ Verificando migraciones..."
php artisan migrate --force
echo "   ✅ Migraciones verificadas"

# Ejecutar pruebas unitarias
echo "🧪 Ejecutando pruebas unitarias..."
php artisan test --filter=ChatSystemTest

# Verificar resultado de las pruebas
if [ $? -eq 0 ]; then
    echo ""
    echo "🎉 ¡TODAS LAS PRUEBAS PASARON!"
    echo "   El sistema de chat está listo para producción"
    echo ""
    echo "📊 Resumen de funcionalidades probadas:"
    echo "   ✅ Autenticación compartida"
    echo "   ✅ Envío de mensajes"
    echo "   ✅ Obtención de contactos"
    echo "   ✅ Historial de mensajes"
    echo "   ✅ Indicadores de escritura"
    echo "   ✅ Estados de usuarios en línea"
    echo "   ✅ Validación de datos"
    echo "   ✅ Rendimiento con múltiples mensajes"
    echo "   ✅ Control de acceso a salas"
    echo "   ✅ Generación de IDs de sala"
    echo "   ✅ Métodos de modelos"
    echo ""
    echo "🚀 El sistema está listo para:"
    echo "   1. Desarrollo local: php artisan serve"
    echo "   2. Producción: Subir a hosting compatible"
    echo ""
    echo "📋 Próximos pasos:"
    echo "   1. Configurar credenciales de Pusher en .env"
    echo "   2. Probar en entorno de desarrollo"
    echo "   3. Desplegar en hosting compartido"
    echo "   4. Eliminar sistema Node.js original"
else
    echo ""
    echo "❌ ALGUNAS PRUEBAS FALLARON"
    echo "   Por favor, revisa los errores arriba"
    echo "   Corrige los problemas antes de continuar"
    exit 1
fi

echo ""
echo "🔍 Verificación final de estructura..."
echo "   Estructura de directorios:"
ls -la

echo ""
echo "   Archivos de configuración:"
ls -la config/ | head -10

echo ""
echo "   Controladores:"
ls -la app/Http/Controllers/ | head -10

echo ""
echo "   Modelos:"
ls -la app/Models/ | head -10

echo ""
echo "   Vistas:"
ls -la resources/views/ | head -10

echo ""
echo "🏁 Pruebas completadas. El sistema está listo para producción."
