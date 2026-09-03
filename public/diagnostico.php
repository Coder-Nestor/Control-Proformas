<?php
/**
 * Diagnóstico de Compresión
 * Verifica disponibilidad de GD, Ghostscript, qpdf
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Compresión</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        .status { margin: 15px 0; padding: 10px; border-radius: 4px; }
        .ok { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .section { margin-top: 20px; }
        h2 { color: #0066cc; margin-top: 20px; border-bottom: 1px solid #0066cc; padding-bottom: 5px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico de Compresión</h1>
        
        <div class="section">
            <h2>Extensiones PHP Disponibles</h2>
            
            <div class="status <?php echo extension_loaded('gd') ? 'ok' : 'error'; ?>">
                <strong>GD Library:</strong> 
                <?php echo extension_loaded('gd') ? '✅ HABILITADA' : '❌ NO HABILITADA'; ?>
                <p style="margin: 5px 0 0 0; font-size: 0.9em;">
                    Estado: <?php echo extension_loaded('gd') ? 'Compresión de imágenes disponible' : 'Edita C:\xampp\php\php.ini y habilita extension=gd'; ?>
                </p>
            </div>

            <div class="status <?php echo extension_loaded('imagick') ? 'ok' : 'warning'; ?>">
                <strong>Imagick:</strong> 
                <?php echo extension_loaded('imagick') ? '✅ Disponible (no usado)' : '⚠️ No disponible (OK, usando GD)'; ?>
            </div>
        </div>

        <div class="section">
            <h2>Herramientas Externas</h2>
            
            <?php
            // Verifica Ghostscript
            $gs_cmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where gswin64c' : 'which gs';
            $ret = 0;
            @exec($gs_cmd, $out, $ret);
            $gs_available = $ret === 0;
            ?>
            <div class="status <?php echo $gs_available ? 'ok' : 'warning'; ?>">
                <strong>Ghostscript:</strong>
                <?php echo $gs_available ? '✅ INSTALADO' : '⚠️ NO ENCONTRADO'; ?>
                <p style="margin: 5px 0 0 0; font-size: 0.9em;">
                    <?php if ($gs_available) { 
                        echo 'PDFs se comprimen a ~60-80% de tamaño original';
                    } else {
                        echo 'Descargar: https://www.ghostscript.com/download/gsdnld.html';
                    } ?>
                </p>
            </div>

            <?php
            // Verifica qpdf
            $qpdf_cmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where qpdf' : 'which qpdf';
            $ret = 0;
            @exec($qpdf_cmd, $out, $ret);
            $qpdf_available = $ret === 0;
            ?>
            <div class="status <?php echo $qpdf_available ? 'ok' : 'warning'; ?>">
                <strong>qpdf:</strong>
                <?php echo $qpdf_available ? '✅ INSTALADO' : '⚠️ NO ENCONTRADO'; ?>
                <p style="margin: 5px 0 0 0; font-size: 0.9em;">
                    <?php if ($qpdf_available) {
                        echo 'PDFs se comprimen a ~30-50% (más ligero que Ghostscript)';
                    } else {
                        echo 'Alternativa ligera para compresión de PDFs';
                    } ?>
                </p>
            </div>
        </div>

        <div class="section">
            <h2>Resumen</h2>
            <div class="status <?php echo extension_loaded('gd') ? 'ok' : 'error'; ?>">
                <?php if (extension_loaded('gd')): ?>
                    <strong>✅ Sistema listo para comprimir:</strong>
                    <ul style="margin: 5px 0; padding-left: 20px;">
                        <li><strong>Imágenes:</strong> Comprimidas con GD (JPEG, PNG, GIF, WebP)</li>
                        <li><strong>PDFs:</strong> <?php echo $gs_available ? 'Comprimidos con Ghostscript' : ($qpdf_available ? 'Comprimidos con qpdf' : 'Guardados sin compresión'); ?></li>
                    </ul>
                <?php else: ?>
                    <strong>❌ IMPORTANTE:</strong> GD no está habilitado
                    <ol style="margin: 5px 0; padding-left: 20px;">
                        <li>Abre: <code>C:\xampp\php\php.ini</code></li>
                        <li>Busca: <code>;extension=gd</code></li>
                        <li>Cambia a: <code>extension=gd</code> (quita el ;)</li>
                        <li>Guarda y reinicia Apache</li>
                    </ol>
                <?php endif; ?>
            </div>
        </div>

        <div class="section">
            <h2>Prueba Rápida</h2>
            <?php
            // Intenta comprimir una imagen de prueba
            $testDir = __DIR__ . '/uploads/test_compression/';
            if (!is_dir($testDir)) {
                @mkdir($testDir, 0755, true);
            }

            // Crea una imagen de prueba pequeña
            if (!function_exists('imagecreatetruecolor')) {
                echo '<div class="status error">⚠️ No se pudo crear imagen de prueba (imagecreatetruecolor no disponible)</div>';
            } else {
                $img = imagecreatetruecolor(100, 100);
                $red = imagecolorallocate($img, 255, 0, 0);
                imagefilledrectangle($img, 0, 0, 100, 100, $red);
                $testFile = $testDir . 'test.jpg';
                imagejpeg($img, $testFile, 75);
                imagedestroy($img);
                
                $size = filesize($testFile);
                echo '<div class="status ok">';
                echo '✅ Imagen de prueba creada: ' . round($size/1024, 2) . ' KB<br>';
                echo 'El sistema está funcionando correctamente.';
                echo '</div>';
                
                @unlink($testFile);
            }
            ?>
        </div>

        <div class="section" style="background: #e8f4f8; padding: 15px; border-radius: 4px; margin-top: 20px;">
            <h3 style="margin-top: 0;">📝 Próximos Pasos</h3>
            <ol>
                <li>Si GD no está habilitado: <strong>Habilítalo en php.ini y reinicia Apache</strong></li>
                <li>Sube un archivo grande (imagen o PDF) a cualquier módulo</li>
                <li>Verifica el tamaño en: <a href="/proformas-app/check_files.php" target="_blank">check_files.php</a></li>
                <li>¿Se comprimió? ✅ Sistema funcionando | ❌ Contacta soporte</li>
            </ol>
        </div>
    </div>
</body>
</html>
