<?php
/**
 * Script para verificar y visualizar el tamaño de archivos subidos.
 * Accede desde: http://localhost/proformas-app/check_files.php
 */

require_once __DIR__ . '/core/Autoload.php';

use Core\Compressor;

$uploads_dir = __DIR__ . '/public/uploads';
$folders = ['proformas', 'gestiones', 'facturas', 'ordenes_compra', 'entregas'];

$total_size = 0;
$file_count = 0;
$compressed_count = 0;

// Función para detectar si un archivo fue comprimido (heurística)
function wasCompressed($filename, $size) {
    // Si el archivo es grande pero el tamaño es bajo, probablemente fue comprimido
    // (basado en el formato de nombre: dirname_YYYYMMDD_HHMMSS_randomhex.ext)
    
    // Para PDF: si es < 1.2 MB probablemente fue comprimido
    if (pathinfo($filename, PATHINFO_EXTENSION) === 'pdf' && $size < 1.2 * 1024 * 1024) {
        return true;
    }
    
    // Para imágenes: si es < 1.5 MB probablemente fue comprimido
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (in_array($ext, ['jpg', 'png', 'gif', 'webp']) && $size < 1.5 * 1024 * 1024) {
        return true;
    }
    
    return false;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Compresión de Archivos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 30px;
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        .folder-section {
            margin-bottom: 30px;
            border-left: 5px solid #667eea;
            padding-left: 20px;
        }
        .folder-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            margin-bottom: 8px;
            border-radius: 5px;
            border-left: 4px solid #ddd;
        }
        .file-item.compressed {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .file-name {
            flex: 1;
            word-break: break-all;
            color: #333;
        }
        .file-size {
            margin-left: 20px;
            font-weight: bold;
            color: #667eea;
            min-width: 120px;
            text-align: right;
        }
        .badge-compressed {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        .stats {
            background: #f0f2f5;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        .stat-item {
            text-align: center;
            padding: 15px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .empty-folder {
            color: #999;
            font-style: italic;
            padding: 10px;
        }
        .legend {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificador de Compresión de Archivos</h1>
        
        <div class="legend">
            <strong>Leyenda:</strong>
            <br>
            ✅ <span class="badge-compressed">COMPRIMIDO</span> = Archivo probablemente comprimido
            <br>
            📄 Gris = Archivo sin comprimir (dentro del límite o no requería)
        </div>

        <?php foreach ($folders as $folder): ?>
            <?php $folder_path = $uploads_dir . '/' . $folder; ?>
            
            <div class="folder-section">
                <div class="folder-title">📁 <?php echo ucfirst($folder); ?></div>
                
                <?php
                if (is_dir($folder_path)) {
                    $files = array_diff(scandir($folder_path), ['.', '..']);
                    
                    if (count($files) > 0) {
                        foreach ($files as $file) {
                            $file_path = $folder_path . '/' . $file;
                            if (is_file($file_path)) {
                                $size = filesize($file_path);
                                $size_formatted = Compressor::formatBytes($size);
                                $was_compressed = wasCompressed($file, $size);
                                $total_size += $size;
                                $file_count++;
                                
                                if ($was_compressed) {
                                    $compressed_count++;
                                }
                                
                                echo '<div class="file-item ' . ($was_compressed ? 'compressed' : '') . '">';
                                echo '<span class="file-name">' . htmlspecialchars($file) . '</span>';
                                echo '<div style="display: flex; align-items: center;">';
                                echo '<span class="file-size">' . $size_formatted . '</span>';
                                if ($was_compressed) {
                                    echo '<span class="badge-compressed">✅ COMPRIMIDO</span>';
                                }
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                    } else {
                        echo '<div class="empty-folder">📭 No hay archivos aún</div>';
                    }
                } else {
                    echo '<div class="empty-folder">⚠️ Carpeta no existe</div>';
                }
                ?>
            </div>
        <?php endforeach; ?>

        <div class="stats">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $file_count; ?></div>
                        <div class="stat-label">Total de archivos</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo Compressor::formatBytes($total_size); ?></div>
                        <div class="stat-label">Espacio total usado</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $compressed_count; ?></div>
                        <div class="stat-label">Archivos probablemente comprimidos</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: center; color: #666;">
            <small>🔄 Actualiza la página para ver cambios | ℹ️ La heurística de compresión es aproximada</small>
        </div>
    </div>
</body>
</html>
