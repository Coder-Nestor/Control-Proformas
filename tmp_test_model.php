<?php
require __DIR__ . '/core/Autoload.php';

use App\Models\Trabajo;

$trabajos = Trabajo::porNumeroCotizacion('S06603');
echo "Found " . count($trabajos) . " trabajos\n";
foreach ($trabajos as $t) {
    echo $t['id'] . "\t" . $t['descripcion'] . "\t" . $t['valor'] . "\t" . $t['proforma_id'] . "\n";
}
