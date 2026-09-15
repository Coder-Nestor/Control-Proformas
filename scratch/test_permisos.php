<?php
require_once __DIR__ . '/../core/autoload.php';

$c = require __DIR__ . '/../config/config.php';
$db = $c['db'];
$pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['database'], $db['username'], $db['password']);

echo "=== PERMISOS DE PROVEEDORES Y USUARIOS EN BASE DE DATOS ===\n";
$stmt = $pdo->query("SELECT id, slug, modulo, descripcion FROM permisos WHERE modulo IN ('Proveedores', 'Usuarios') ORDER BY modulo, id");
$permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($permisos as $p) {
    echo "- [{$p['modulo']}] ID {$p['id']} => {$p['slug']} ({$p['descripcion']})\n";
}

echo "\n=== RUTAS DEFINIDAS EN CONFIG/ROUTES.PHP ===\n";
$routes = require __DIR__ . '/../config/routes.php';
foreach ($routes as $r) {
    $method = $r[0];
    $uri = $r[1];
    $perm = is_array($r[3]) ? implode(',', $r[3]) : ($r[3] ?? 'público');
    if (strpos($uri, 'proveedores') !== false || strpos($uri, 'usuarios') !== false) {
        echo "{$method} {$uri} => Permiso: {$perm}\n";
    }
}
