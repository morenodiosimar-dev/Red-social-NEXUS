#!/bin/bash
if [ -d "Proyecto Prueba" ]; then
    cd "Proyecto Prueba"
    php -S 0.0.0.0:$PORT index.php
else
    echo "Carpeta Proyecto Prueba no encontrada"
    exit 1
fi
