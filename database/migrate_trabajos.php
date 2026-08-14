<?php
/**
 * Migración: separa los "trabajos" guardados como texto concatenado en
 * gestiones.trabajo hacia la nueva tabla `trabajos`, preservando la
 * proforma que ya tenía asignada cada gestión.
 *
 * Ejecutar UNA sola vez desde la raíz del proyecto:
 *   php database/migrate_trabajos.php
 */

require __DIR__ . '/../core/Autoload.php';
require __DIR__ . '/../core/helpers.php';

use Core\Database;
use App\Models\Gestion;

$pdo = Database::connection();

$gestiones = $pdo->query('SELECT id, trabajo, valor_cotizacion, proforma_id FROM gestiones')->fetchAll();

$stmt = $pdo->prepare(
    'INSERT INTO trabajos (gestion_id, proforma_id, descripcion, valor, orden)
     VALUES (:gestion_id, :proforma_id, :descripcion, :valor, :orden)'
);

$insertados = 0;

foreach ($gestiones as $g) {
    $items = Gestion::trabajosDesglosados($g['trabajo'] ?? '');

    // Si el texto no tenía el formato "descripción | valor" (trabajos capturados
    // antes de esa función, o de una sola línea sin "|"), se migra como un solo
    // trabajo usando el valor_cotizacion que tenía la gestión completa.
    if (empty($items)) {
        $items = [[
            'trabajo' => trim($g['trabajo'] ?? '') ?: 'Trabajo sin descripción',
            'valor'   => $g['valor_cotizacion'],
        ]];
    }

    $orden = 0;
    foreach ($items as $item) {
        $descripcion = $item['trabajo'] !== '' ? $item['trabajo'] : 'Trabajo sin descripción';
        $valor = $item['valor'] ?? (count($items) === 1 ? $g['valor_cotizacion'] : null);

        $stmt->execute([
            'gestion_id'  => $g['id'],
            'proforma_id' => $g['proforma_id'],
            'descripcion' => $descripcion,
            'valor'       => $valor,
            'orden'       => $orden++,
        ]);
        $insertados++;
    }

    echo "Gestión #{$g['id']}: " . count($items) . " trabajo(s) migrado(s).\n";
}

echo "\n--- Migración completa ---\n";
echo count($gestiones) . " gestiones procesadas, {$insertados} trabajos creados en total.\n";
echo "Revisa la tabla `trabajos` en phpMyAdmin antes de continuar con el Paso 2 (eliminar columnas antiguas).\n";
