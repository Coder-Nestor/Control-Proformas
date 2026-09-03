<?php

require_once __DIR__ . '/../core/Autoload.php';
require_once __DIR__ . '/../core/helpers.php';

use App\Models\Documento;
use Core\Compressor;

echo "=== VERIFICACIÓN DEL SISTEMA DE MÚLTIPLES DOCUMENTOS (5 MB) ===\n\n";

$errores = 0;

// 1. Verificar tabla documentos
echo "1. Verificando tabla 'documentos' en base de datos... ";
try {
    $count = Documento::contar('gestion', 999999);
    echo "OK (Conexión y tabla listas)\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $errores++;
}

// 2. Verificar helpers de Documento
echo "2. Verificando helpers de Documento... ";
$fmt1 = Documento::formatearTamano(5242880); // 5 MB
$fmt2 = Documento::formatearTamano(512000);  // 500 KB
$fmt3 = Documento::formatearTamano(500);     // 500 B
$isPdf1 = Documento::esPdf('application/pdf', 'test.pdf');
$isPdf2 = Documento::esPdf('image/jpeg', 'test.jpg');

if ($fmt1 === '5 MB' && $fmt2 === '500 KB' && $fmt3 === '500 B' && $isPdf1 === true && $isPdf2 === false) {
    echo "OK ($fmt1, $fmt2, $fmt3, isPdf: true/false)\n";
} else {
    echo "ERROR en formato o detección PDF\n";
    $errores++;
}

// 3. Verificar directorios de subida
echo "3. Verificando directorios de subida...\n";
$directorios = [
    'gestiones' => __DIR__ . '/../public/uploads/gestiones',
    'proformas' => __DIR__ . '/../public/uploads/proformas',
    'ordenes_compra' => __DIR__ . '/../public/uploads/ordenes_compra',
    'facturas' => __DIR__ . '/../public/uploads/facturas',
    'entregas' => __DIR__ . '/../public/uploads/entregas',
];

foreach ($directorios as $nombre => $path) {
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
    $isWritable = is_writable($path);
    echo "   - $nombre: " . ($isWritable ? "OK (Escribible)" : "ADVERTENCIA (No escribible)") . " -> $path\n";
    if (!$isWritable) $errores++;
}

// 4. Verificar configuración de compresión
echo "4. Verificando configuración de compresión... ";
$config = require __DIR__ . '/../config/compression.php';
if (isset($config['max_size_bytes']) && $config['max_size_bytes'] === 5 * 1024 * 1024) {
    echo "OK (Límite: " . ($config['max_size_bytes'] / (1024 * 1024)) . " MB)\n";
} else {
    echo "ERROR: max_size_bytes no coincide con 5 MB\n";
    $errores++;
}

// 5. Test CRUD de Documento
echo "5. Probando inserción y soft-delete de Documento... ";
try {
    $docId = Documento::crearDelArchivo(
        'test_entidad',
        99999,
        'test_archivo.pdf',
        'application/pdf',
        102400,
        1,
        'mi_documento_original.pdf',
        1
    );

    $docs = Documento::deEntidad('test_entidad', 99999);
    if (count($docs) !== 1 || $docs[0]['nombre_original'] !== 'mi_documento_original.pdf') {
        throw new \Exception("No se recuperó el documento insertado correctamente.");
    }

    Documento::softDelete($docId, 1);
    $docsDespues = Documento::deEntidad('test_entidad', 99999);
    if (count($docsDespues) !== 0) {
        throw new \Exception("Soft-delete no ocultó el documento de deEntidad().");
    }

    // Limpieza física del registro de prueba
    \Core\Database::connection()->exec("DELETE FROM documentos WHERE tipo_entidad = 'test_entidad' AND id_entidad = 99999");

    echo "OK (Insert, Select, SoftDelete verificados)\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $errores++;
}

// 6. Test de simulación handleMultipleUploads
echo "6. Probando handleMultipleUploads en Core\\Controller... ";
class TestController extends \Core\Controller {
    public function testUploads(string $field, string $subdir): array {
        return $this->handleMultipleUploads($field, $subdir);
    }
}

// Crear archivos temporales de prueba
$tmpValid = tempnam(sys_get_temp_dir(), 'tval_') . '.pdf';
file_put_contents($tmpValid, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

$tmpLarge = tempnam(sys_get_temp_dir(), 'tlar_') . '.pdf';
file_put_contents($tmpLarge, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");
// Simular tamaño > 5 MB
$tmpLargeSize = 6 * 1024 * 1024;

$_FILES['documentos'] = [
    'name' => ['doc1.pdf', 'doc_grande.pdf'],
    'type' => ['application/pdf', 'application/pdf'],
    'tmp_name' => [$tmpValid, $tmpLarge],
    'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
    'size' => [filesize($tmpValid), $tmpLargeSize],
];

$tc = new TestController();
$uploaded = $tc->testUploads('documentos', 'gestiones');

@unlink($tmpValid);
@unlink($tmpLarge);

if (count($uploaded) === 1 && $uploaded[0]['nombre_original'] === 'doc1.pdf') {
    // Limpiar el archivo subido en public/uploads/gestiones
    $subidoPath = __DIR__ . '/../public/uploads/gestiones/' . $uploaded[0]['nombre_archivo'];
    if (is_file($subidoPath)) @unlink($subidoPath);

    echo "OK (Subió el archivo de 5 MB o menor y rechazó el de 6 MB)\n";
} else {
    echo "ERROR: handleMultipleUploads no filtró correctamente (count=" . count($uploaded) . ")\n";
    $errores++;
}

echo "\n=======================================================\n";
if ($errores === 0) {
    echo "✅ TODAS LAS PRUEBAS PASARON EXITOSAMENTE (0 errores)\n";
} else {
    echo "❌ SE ENCONTRARON $errores ERRORES\n";
}
