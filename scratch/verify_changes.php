<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../app/Models/Gestion.php';
require_once __DIR__ . '/../app/Models/Proveedor.php';

use App\Models\Gestion;
use App\Models\Proveedor;

echo "=== 1. VERIFICAR GESTIONES BÚSQUEDA POR PROVEEDOR ===\n";

$gestionesMario = Gestion::allConDetalle(['buscar' => 'Mario']);
$totalMario = Gestion::contarConDetalle(['buscar' => 'Mario']);
echo "Total gestiones encontradas buscando 'Mario': $totalMario\n";
foreach ($gestionesMario as $g) {
    echo "- ID: {$g['id']} | Cotización: {$g['n_cotizacion']} | Proveedor: {$g['proveedor_nombre']}\n";
}

$gestionesSun = Gestion::allConDetalle(['buscar' => 'Sun']);
$totalSun = Gestion::contarConDetalle(['buscar' => 'Sun']);
echo "\nTotal gestiones encontradas buscando 'Sun': $totalSun\n";
foreach ($gestionesSun as $g) {
    echo "- ID: {$g['id']} | Cotización: {$g['n_cotizacion']} | Proveedor: {$g['proveedor_nombre']}\n";
}

echo "\n=== 2. VERIFICAR SIMILITUD DE PROVEEDORES ===\n";
$queries = [
    'Mario Perez',
    'mario perez',
    'Mario Pérez',
    'Perez Mario',
    'Marrio Perez',
    'Mario Perez S.A.',
    'Ares',
    'Ares Sun Corp',
    'CIT Solutions',
    'Carlos Lopez', // no existente
];

foreach ($queries as $q) {
    $res = Proveedor::buscarSimilares($q);
    echo "Query: '$q' -> Encontrados: " . count($res) . "\n";
    foreach ($res as $sim) {
        echo "   -> [Score: {$sim['score']}%] ID: {$sim['id']} | Nombre: '{$sim['nombre']}' | Motivo: {$sim['motivo']}\n";
    }
}
