<?php
/**
 * Script de debug para verificar si la protección de gestiones funciona
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../core/Autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Models\Gestion;
use App\Models\Trabajo;
use App\Models\OrdenCompra;
use App\Models\Factura;
use App\Models\EntregaFactura;

// ID de la gestión a verificar
$gestion_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Gestión - Protección Eliminar</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        .status { margin: 15px 0; padding: 10px; border-radius: 4px; }
        .ok { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .section { margin-top: 20px; border: 1px solid #ddd; padding: 15px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        table td, table th { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background: #f5f5f5; font-weight: bold; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        input[type="number"] { padding: 5px; }
        button { padding: 10px 15px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #004499; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug: Protección de Gestiones al Eliminar</h1>
        
        <div class="section">
            <h2>Buscar Gestión</h2>
            <form method="GET">
                <label for="id">ID de Gestión:</label>
                <input type="number" id="id" name="id" value="<?php echo $gestion_id ?: ''; ?>" min="1">
                <button type="submit">Verificar</button>
            </form>
        </div>

        <?php if ($gestion_id): ?>
            <?php
            $gestion = Gestion::find($gestion_id);
            
            if (!$gestion) {
                echo '<div class="status error">❌ Gestión no encontrada con ID: ' . $gestion_id . '</div>';
            } else {
                echo '<div class="section">';
                echo '<h2>Información de Gestión</h2>';
                echo '<table>';
                echo '<tr><th>ID</th><td>' . $gestion['id'] . '</td></tr>';
                echo '<tr><th>Número Cotización</th><td>' . ($gestion['n_cotizacion'] ?? '(sin número)') . '</td></tr>';
                echo '<tr><th>Proveedor ID</th><td>' . ($gestion['proveedor_id'] ?? '-') . '</td></tr>';
                echo '</table>';
                echo '</div>';

                // Obtener trabajos
                $trabajos = Trabajo::deGestion($gestion_id);
                echo '<div class="section">';
                echo '<h2>Trabajos de la Gestión (' . count($trabajos) . ')</h2>';
                
                if (empty($trabajos)) {
                    echo '<div class="status warning">⚠️ Sin trabajos asociados</div>';
                } else {
                    echo '<table>';
                    echo '<tr><th>ID Trabajo</th><th>Descripción</th><th>Proforma ID</th><th>Valor</th></tr>';
                    foreach ($trabajos as $trabajo) {
                        echo '<tr>';
                        echo '<td>' . $trabajo['id'] . '</td>';
                        echo '<td>' . $trabajo['descripcion'] . '</td>';
                        echo '<td>' . ($trabajo['proforma_id'] ?? '-') . '</td>';
                        echo '<td>' . ($trabajo['valor'] ?? '-') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
                echo '</div>';

                // Verificar cadena de documentos
                echo '<div class="section">';
                echo '<h2>Verificación de Cadena Documento</h2>';
                
                $llego_a_entrega = false;
                foreach ($trabajos as $trabajo) {
                    if (empty($trabajo['proforma_id'])) {
                        echo '<div class="status warning">⚠️ Trabajo "' . $trabajo['descripcion'] . '" sin Proforma ID</div>';
                        continue;
                    }
                    
                    $proforma_id = (int)$trabajo['proforma_id'];
                    echo '<div style="margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 3px solid #0066cc;">';
                    echo '<strong>Trabajo:</strong> ' . $trabajo['descripcion'] . ' (Proforma ID: ' . $proforma_id . ')<br>';
                    
                    // Buscar Orden de Compra
                    $oc = OrdenCompra::findPorProforma($proforma_id);
                    if (!$oc) {
                        echo '<span class="status warning" style="display: inline-block;">❌ No hay Orden de Compra para Proforma ' . $proforma_id . '</span><br>';
                        continue;
                    }
                    echo '✅ Orden de Compra ID: ' . $oc['id'] . '<br>';
                    
                    // Buscar Factura
                    $factura = Factura::findPorOrdenCompra((int)$oc['id']);
                    if (!$factura) {
                        echo '<span class="status warning" style="display: inline-block;">❌ No hay Factura para OC ' . $oc['id'] . '</span><br>';
                        continue;
                    }
                    echo '✅ Factura ID: ' . $factura['id'] . '<br>';
                    
                    // Buscar Entrega de Factura
                    $entrega = EntregaFactura::findPorFactura((int)$factura['id']);
                    if (!$entrega) {
                        echo '<span class="status warning" style="display: inline-block;">❌ No hay Entrega de Factura para Factura ' . $factura['id'] . '</span><br>';
                    } else {
                        echo '<span class="status ok" style="display: inline-block;">✅ Entrega de Factura ID: ' . $entrega['id'] . ' - GESTIÓN PROTEGIDA</span><br>';
                        $llego_a_entrega = true;
                    }
                    echo '</div>';
                }
                
                echo '<div class="section">';
                if ($llego_a_entrega) {
                    echo '<div class="status ok"><strong>✅ RESULTADO:</strong> Esta gestión llegó hasta Entrega de Factura y DEBE estar protegida contra eliminación.</div>';
                } else {
                    echo '<div class="status warning"><strong>⚠️ RESULTADO:</strong> Esta gestión NO llegó hasta Entrega de Factura. Puede ser eliminada.</div>';
                }
                echo '</div>';
            }
            ?>
        <?php endif; ?>

        <div class="section" style="background: #e8f4f8; margin-top: 20px;">
            <h3>Instrucciones</h3>
            <ol>
                <li>Ingresa el ID de una gestión que hayas intentado eliminar</li>
                <li>Este debug te mostrará si tiene Entrega de Factura asociada</li>
                <li>Si hay Entrega, la gestión debe estar protegida contra eliminación</li>
                <li>Si NO hay Entrega pero sigue sin poder eliminarla, hay un bug en Controller::destroy()</li>
            </ol>
        </div>
    </div>
</body>
</html>
