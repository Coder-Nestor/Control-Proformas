<?php

/**
 * Script de prueba para verificar la funcionalidad del sistema de compresión.
 * Accede desde: http://localhost/proformas-app/test_compression.php
 */

require_once __DIR__ . '/core/Autoload.php';

use Core\Compressor;

// Verificar extensiones disponibles
echo "=== DIAGNÓSTICO DE COMPRESIÓN ===\n\n";

echo "📦 Extensiones disponibles:\n";
echo "  - GD Library: " . (extension_loaded('gd') ? "✅ Disponible" : "❌ No disponible") . "\n";
echo "  - Imagick: " . (extension_loaded('imagick') ? "✅ Disponible" : "❌ No disponible") . "\n\n";

echo "🔧 Herramientas externas:\n";

// Verifica Ghostscript
$gsCommand = 'where gswin64c';
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    $gsCommand = 'which gs';
}

$output = [];
$returnCode = 0;
@exec($gsCommand, $output, $returnCode);

if ($returnCode === 0) {
    echo "  - Ghostscript: ✅ Disponible\n";
    echo "    Ubicación: " . (isset($output[0]) ? $output[0] : "No detectada") . "\n";
} else {
    echo "  - Ghostscript: ❌ No disponible (PDFs no se comprimirán automáticamente)\n";
    echo "    Descarga desde: https://www.ghostscript.com/download/gsdnld.html\n";
}

echo "\n📋 Configuración actual:\n";

$config = require __DIR__ . '/config/compression.php';
echo "  - Tamaño máximo: " . Compressor::formatBytes($config['max_size_bytes']) . "\n";
echo "  - Calidad JPEG: " . $config['compression_quality'] . "/100\n";
echo "  - Dimensión máxima: " . $config['max_image_width'] . "x" . $config['max_image_height'] . " px\n";
echo "  - Compresión habilitada: " . ($config['enabled'] ? "Sí" : "No") . "\n";

echo "\n✨ Información útil:\n";
echo "  - Archivo de configuración: config/compression.php\n";
echo "  - Clase de compresión: core/Compressor.php\n";
echo "  - Documentación: COMPRESION.md\n";

echo "\n💡 Recomendaciones:\n";
if (!extension_loaded('gd')) {
    echo "  ⚠️  Habilita GD Library en PHP para comprimir imágenes\n";
}
if ($returnCode !== 0) {
    echo "  ⚠️  Instala Ghostscript para comprimir PDFs automáticamente\n";
}
if (extension_loaded('imagick')) {
    echo "  ✅ Imagick está disponible, la compresión será de mejor calidad\n";
} else {
    echo "  💡 Considera instalar Imagick para mejor compresión de imágenes\n";
}

echo "\n✅ Sistema listo para comprimir documentos.\n";
echo "   Los archivos mayores a " . Compressor::formatBytes($config['max_size_bytes']) . " se comprimirán automáticamente al subirse.\n";
