<?php

/**
 * Configuración del sistema de compresión de archivos.
 * 
 * Este archivo permite personalizar los parámetros de compresión para
 * documentos (imágenes y PDFs) en el sistema.
 */

return [
    // Tamaño máximo permitido para archivos en bytes (5 MB = 5242880 bytes)
    'max_size_bytes' => 5 * 1024 * 1024,

    // Calidad JPEG para imágenes comprimidas (1-100, donde 100 es máxima calidad)
    // Valores más bajos = mayor compresión pero menor calidad
    // Recomendado: 75-85
    'compression_quality' => 75,

    // Ancho máximo para imágenes (en píxeles)
    // Las imágenes más grandes se reducirán proporcionalmente
    'max_image_width' => 2000,

    // Alto máximo para imágenes (en píxeles)
    // Las imágenes más altas se reducirán proporcionalmente
    'max_image_height' => 2000,

    // Habilitar o deshabilitar compresión automática
    'enabled' => true,

    // Habilitará logging detallado de compresión (requiere que uncomentes error_log en Controller.php)
    'debug' => false,
];
