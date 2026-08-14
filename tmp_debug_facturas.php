<?php
// Script temporal de depuración — eliminar después de usar
require __DIR__ . '/core/Autoload.php';

use App\Models\Factura;

try {
    $result = Factura::allConDetalle([]);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'count' => count($result),
        'sample' => array_slice($result, 0, 10),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
    ]);
}
