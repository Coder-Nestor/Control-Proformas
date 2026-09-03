<?php

require_once __DIR__ . '/../core/Autoload.php';
require_once __DIR__ . '/../core/helpers.php';

use App\Models\Documento;
use Core\Database;

$pdo = Database::connection();

echo "=== MIGRACIÓN DE DOCUMENTOS HISTÓRICOS A TABLA 'documentos' ===\n\n";

$tablas = [
    'gestiones'        => ['tipo' => 'gestion',         'dir' => 'gestiones',       'col' => 'documento_pdf'],
    'proformas'        => ['tipo' => 'proforma',        'dir' => 'proformas',       'col' => 'documento_pdf'],
    'ordenes_compra'   => ['tipo' => 'orden_compra',    'dir' => 'ordenes_compra',  'col' => 'documento_pdf'],
    'facturas'         => ['tipo' => 'factura',         'dir' => 'facturas',        'col' => 'documento_pdf'],
    'entrega_facturas' => ['tipo' => 'entrega_factura', 'dir' => 'entregas',       'col' => 'documento_pdf'],
];

$migrados = 0;
$yaExistian = 0;

foreach ($tablas as $tabla => $info) {
    echo "Revisando $tabla...\n";
    $stmt = $pdo->query("SELECT id, {$info['col']} as archivo FROM {$tabla} WHERE {$info['col']} IS NOT NULL AND {$info['col']} != ''");
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($filas as $fila) {
        $idEntidad = (int) $fila['id'];
        $nombreArchivo = trim($fila['archivo']);

        if (empty($nombreArchivo)) continue;

        // Comprobar si ya existe en tabla documentos
        $chk = $pdo->prepare("SELECT COUNT(*) FROM documentos WHERE tipo_entidad = ? AND id_entidad = ? AND nombre_archivo = ? AND eliminado_en IS NULL");
        $chk->execute([$info['tipo'], $idEntidad, $nombreArchivo]);
        if ((int)$chk->fetchColumn() > 0) {
            $yaExistian++;
            continue;
        }

        $rutaCompleta = __DIR__ . '/../public/uploads/' . $info['dir'] . '/' . $nombreArchivo;
        $tamano = is_file($rutaCompleta) ? filesize($rutaCompleta) : 0;
        $mime = 'application/pdf';
        if (es_imagen($nombreArchivo)) {
            $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
            $mime = ($ext === 'png') ? 'image/png' : (($ext === 'gif') ? 'image/gif' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg'));
        }

        Documento::crearDelArchivo(
            $info['tipo'],
            $idEntidad,
            $nombreArchivo,
            $mime,
            $tamano,
            null,
            $nombreArchivo,
            1
        );
        $migrados++;
        echo "   -> Migrado: {$info['tipo']} #{$idEntidad} ($nombreArchivo)\n";
    }
}

echo "\nResultado:\n";
echo " - Documentos migrados a 'documentos': $migrados\n";
echo " - Documentos que ya estaban registrados: $yaExistian\n";
echo "=== FIN DE MIGRACIÓN ===\n";
