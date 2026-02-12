#!/bin/bash
# Script de inicio para Railway
# Este script ayuda a Railpack a identificar como iniciar tu aplicación

echo "🚀 Iniciando aplicación en Railway..."

# Intentar detectar si estamos en la raíz del repositorio con carpetas anidadas
if [ -d "proyecto nore/Proyecto Prueba" ]; then
    echo "Carpeta Proyecto Prueba detectada. Entrando..."
    cd "proyecto nore/Proyecto Prueba"
fi

# Iniciar servidor PHP incorporado como fallback
# Railway usualmente configura Apache/Nginx automáticamente si detecta un index.php
# Pero esto asegura que algo corra si la detección automática falla.
php -S 0.0.0.0:$PORT Index.html
