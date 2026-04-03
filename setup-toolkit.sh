#!/bin/bash

echo "🏗️  Iniciando creación de estructura para Triskelion Toolkit..."

# Arreglo de carpetas según nuestra arquitectura modular
folders=(
    "assets/vendor/prism"
    "includes/modules/code-console"
    "includes/modules/custom-quotes"
    "src/blocks/code-console"
    "src/blocks/custom-quotes"
    "scripts"
)

# Crear carpetas y añadir .gitkeep
for folder in "${folders[@]}"; do
    mkdir -p "$folder"
    touch "$folder/.gitkeep"
    echo "✅ Carpeta creada: $folder (con .gitkeep)"
done

# Crear archivos base vacíos para que PhpStorm los reconozca de inmediato
touch triskelion-toolkit.php
touch includes/class-autoloader.php
touch includes/class-toolkit.php
touch scripts/sync-assets.js

echo "✨ ¡Estructura lista! Ya puedes abrir el proyecto en PhpStorm."
