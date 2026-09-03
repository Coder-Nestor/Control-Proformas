<?php
/**
 * Script detallado de diagnóstico del sistema de compresión.
 * Accede desde: http://localhost/proformas-app/debug_compression.php
 */

require_once __DIR__ . '/core/Autoload.php';

use Core\Compressor;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Compresión - Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .check-ok { color: #28a745; font-weight: bold; }
        .check-fail { color: #dc3545; font-weight: bold; }
        .code-block { background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; overflow-x: auto; }
        .section-title { font-size: 1.2rem; font-weight: bold; color: #333; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container-lg">
        <h1 class="mb-4">🔍 Diagnóstico Completo del Sistema de Compresión</h1>

        <div class="card">
            <div class="card-body">
                <div class="section-title">📋 Información del Sistema PHP</div>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Versión PHP:</strong></td>
                        <td><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td><strong>OS:</strong></td>
                        <td><?php echo PHP_OS; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Memoria máxima:</strong></td>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Upload máximo:</strong></td>
                        <td><?php echo ini_get('upload_max_filesize'); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title">🖼️ Extensiones de Imagen</div>
                
                <?php
                $gdExtensions = [
                    'imagecreatefromjpeg' => 'JPEG',
                    'imagecreatefrompng' => 'PNG',
                    'imagecreatefromgif' => 'GIF',
                    'imagecreatefromwebp' => 'WebP',
                    'imagejpeg' => 'Guardar JPEG',
                    'imagepng' => 'Guardar PNG',
                    'imagegif' => 'Guardar GIF',
                    'imagewebp' => 'Guardar WebP',
                ];
                
                if (extension_loaded('gd')) {
                    echo '<p><span class="check-ok">✅ GD Library está habilitada</span></p>';
                    echo '<table class="table table-sm">';
                    foreach ($gdExtensions as $func => $name) {
                        $status = function_exists($func) ? 'check-ok' : 'check-fail';
                        $symbol = function_exists($func) ? '✅' : '❌';
                        echo "<tr><td>$name:</td><td><span class='$status'>$symbol " . (function_exists($func) ? 'Disponible' : 'NO disponible') . "</span></td></tr>";
                    }
                    echo '</table>';
                } else {
                    echo '<p><span class="check-fail">❌ GD Library NO está habilitada</span></p>';
                    echo '<p>Para habilitar GD en XAMPP:</p>';
                    echo '<ol>';
                    echo '<li>Abre <code>C:\xampp\php\php.ini</code></li>';
                    echo '<li>Busca <code>;extension=gd</code></li>';
                    echo '<li>Quita el <code>;</code> → <code>extension=gd</code></li>';
                    echo '<li>Reinicia Apache desde el Panel de Control de XAMPP</li>';
                    echo '</ol>';
                }
                ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title">🔧 Herramientas Externas</div>
                
                <?php
                $tools = [
                    'Ghostscript' => ['where gswin64c', 'which gs'],
                    'qpdf' => ['where qpdf', 'which qpdf'],
                ];
                
                foreach ($tools as $name => $commands) {
                    $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? $commands[0] : $commands[1];
                    $output = [];
                    $returnCode = 0;
                    @exec($command, $output, $returnCode);
                    
                    $status = $returnCode === 0 ? 'check-ok' : 'check-fail';
                    $symbol = $returnCode === 0 ? '✅' : '❌';
                    
                    echo "<p><span class='$status'>$symbol $name: " . ($returnCode === 0 ? 'Disponible' : 'NO disponible') . "</span></p>";
                }
                ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title">🧪 Prueba de Compresión de Imagen</div>
                
                <?php
                // Crea una imagen JPEG de prueba
                $testImagePath = __DIR__ . '/test_image.jpg';
                
                if (!file_exists($testImagePath)) {
                    echo '<p>Creando imagen de prueba...</p>';
                    
                    if (function_exists('imagecreatetruecolor')) {
                        // Crea una imagen JPEG de prueba (500x500, 5 MB aprox)
                        $image = imagecreatetruecolor(500, 500);
                        $colors = [];
                        for ($i = 0; $i < 10; $i++) {
                            $colors[] = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
                        }
                        
                        // Llena la imagen con patrones
                        for ($i = 0; $i < 50; $i++) {
                            imagefilledrectangle($image, rand(0, 500), rand(0, 500), rand(0, 500), rand(0, 500), $colors[array_rand($colors)]);
                        }
                        
                        imagejpeg($image, $testImagePath, 100); // Máxima calidad para hacer grande
                        imagedestroy($image);
                        echo '<p class="check-ok">✅ Imagen de prueba creada</p>';
                    } else {
                        echo '<p class="check-fail">❌ No se puede crear imagen (imagecreatetruecolor no disponible)</p>';
                    }
                }
                
                if (file_exists($testImagePath)) {
                    $sizeBefore = filesize($testImagePath);
                    echo "<p><strong>Imagen de prueba:</strong> test_image.jpg</p>";
                    echo "<p><strong>Tamaño original:</strong> " . Compressor::formatBytes($sizeBefore) . "</p>";
                    
                    // Crea copia para probar
                    $testCopy = $testImagePath . '.backup';
                    copy($testImagePath, $testCopy);
                    
                    // Comprime
                    $result = Compressor::compress($testCopy, 'image/jpeg');
                    
                    echo '<div class="code-block">';
                    echo '<strong>Resultado de compresión:</strong><br>';
                    echo 'Éxito: ' . ($result['success'] ? '✅ Sí' : '❌ No') . '<br>';
                    echo 'Comprimido: ' . ($result['compressed'] ? '✅ Sí' : '❌ No') . '<br>';
                    echo 'Mensaje: ' . htmlspecialchars($result['message']) . '<br>';
                    echo 'Tamaño después: ' . Compressor::formatBytes($result['sizeAfter']) . '<br>';
                    if ($result['compressed']) {
                        $reduction = (($result['sizeBefore'] - $result['sizeAfter']) / $result['sizeBefore']) * 100;
                        echo 'Reducción: ' . round($reduction, 1) . '%<br>';
                    }
                    echo '</div>';
                    
                    unlink($testCopy);
                }
                ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title">📄 Prueba de Compresión de PDF</div>
                
                <?php
                $testPdfPath = __DIR__ . '/test_pdf.pdf';
                
                if (!file_exists($testPdfPath)) {
                    // Crea un PDF de prueba simple
                    $pdfContent = "%PDF-1.4\n";
                    $pdfContent .= "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n";
                    $pdfContent .= "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n";
                    $pdfContent .= "3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R>>endobj\n";
                    $pdfContent .= "4 0 obj<</Length " . strlen("BT /F1 12 Tf 50 750 Td (Test) Tj ET") . ">>stream\n";
                    $pdfContent .= "BT /F1 12 Tf 50 750 Td (Test) Tj ET\n";
                    $pdfContent .= "endstream endobj\n";
                    $pdfContent .= "xref\n0 5\n";
                    $pdfContent .= "0000000000 65535 f\n";
                    $pdfContent .= "0000000009 00000 n\n";
                    $pdfContent .= "0000000058 00000 n\n";
                    $pdfContent .= "0000000115 00000 n\n";
                    $pdfContent .= "0000000205 00000 n\n";
                    $pdfContent .= "trailer<</Size 5/Root 1 0 R>>\n";
                    $pdfContent .= "startxref\n305\n%%EOF\n";
                    
                    file_put_contents($testPdfPath, $pdfContent);
                }
                
                if (file_exists($testPdfPath)) {
                    $sizeBefore = filesize($testPdfPath);
                    echo "<p><strong>PDF de prueba:</strong> test_pdf.pdf</p>";
                    echo "<p><strong>Tamaño original:</strong> " . Compressor::formatBytes($sizeBefore) . "</p>";
                    
                    $testCopy = $testPdfPath . '.backup';
                    copy($testPdfPath, $testCopy);
                    
                    $result = Compressor::compress($testCopy, 'application/pdf');
                    
                    echo '<div class="code-block">';
                    echo '<strong>Resultado de compresión:</strong><br>';
                    echo 'Éxito: ' . ($result['success'] ? '✅ Sí' : '❌ No') . '<br>';
                    echo 'Comprimido: ' . ($result['compressed'] ? '✅ Sí' : '❌ No') . '<br>';
                    echo 'Mensaje: ' . htmlspecialchars($result['message']) . '<br>';
                    echo 'Tamaño después: ' . Compressor::formatBytes($result['sizeAfter']) . '<br>';
                    if ($result['compressed']) {
                        $reduction = (($result['sizeBefore'] - $result['sizeAfter']) / $result['sizeBefore']) * 100;
                        echo 'Reducción: ' . round($reduction, 1) . '%<br>';
                    }
                    echo '</div>';
                    
                    unlink($testCopy);
                }
                ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title">✅ Solución Rápida</div>
                <p><strong>Si las imágenes NO se comprimen:</strong></p>
                <ol>
                    <li>Abre <code>C:\xampp\php\php.ini</code></li>
                    <li>Busca la línea <code>;extension=gd</code></li>
                    <li>Quita el <code>;</code> para que quede: <code>extension=gd</code></li>
                    <li>Guarda el archivo</li>
                    <li><strong>Reinicia Apache:</strong> Panel de Control XAMPP → Apache → Stop → Start</li>
                    <li>Recarga esta página para verificar</li>
                </ol>
                
                <p style="margin-top: 20px;"><strong>Si los PDFs siguen dañados:</strong></p>
                <ol>
                    <li>Descarga Ghostscript: https://www.ghostscript.com/download/gsdnld.html</li>
                    <li>O qpdf: https://sourceforge.net/projects/qpdf/files/</li>
                    <li>Instala en tu PC</li>
                    <li>El sistema usará automáticamente estas herramientas</li>
                </ol>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px; color: #666;">
            <small>🔄 Recarga para ver cambios | Última actualización: <?php echo date('H:i:s'); ?></small>
        </div>
    </div>
</body>
</html>
