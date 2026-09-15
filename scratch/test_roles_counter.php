<?php
require_once __DIR__ . '/../core/autoload.php';

$total = \App\Models\Rol::contarTotalPermisos();
$roles = \App\Models\Rol::todos();

echo "Total permisos en el sistema: {$total}\n\n";

foreach ($roles as $r) {
    $asignados = count(\App\Models\Rol::permisosDe((int)$r['id']));
    echo "- {$r['nombre']} ({$r['slug']}): {$asignados} de {$total} permisos asignados\n";
}
