#!/bin/bash
echo "🚀 Iniciando aplicación PHP..."
# Nixpacks para PHP usualmente corre Apache o Nginx automáticamente.
# Si detecta index.php y composer.json, no necesita este script, 
# pero lo ponemos para evitar el error de Railway.
php -S 0.0.0.0:$PORT index.php
