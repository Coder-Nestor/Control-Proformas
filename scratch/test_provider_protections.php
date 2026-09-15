<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../app/Models/Proveedor.php';

use App\Models\Proveedor;

echo "=== TEST PROVEEDORES CON ESTADÍSTICAS ===\n";
$proveedores = Proveedor::allConEstadisticas();
foreach ($proveedores as $p) {
    echo "ID: {$p['id']} | Nombre: '{$p['nombre']}' | Gestiones: {$p['total_gestiones']} | Proformas: {$p['total_proformas']}\n";
}

echo "\n=== TEST INTENTO DE ELIMINACIÓN DE PROVEEDOR EN USO ===\n";
foreach ($proveedores as $p) {
    $id = (int) $p['id'];
    $g = Proveedor::contarGestiones($id);
    $pr = Proveedor::contarProformas($id);
    if ($g > 0 || $pr > 0) {
        echo "Proveedor '{$p['nombre']}' (ID: $id) -> BLOQUEADO para eliminación (tiene $g gestiones y $pr proformas).\n";
    } else {
        echo "Proveedor '{$p['nombre']}' (ID: $id) -> PERMITIDO para eliminación (sin uso).\n";
    }
}
