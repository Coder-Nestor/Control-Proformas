<?php
$c = require __DIR__ . '/../config/config.php';
$db = $c['db'];
$pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['database'], $db['username'], $db['password']);

echo "=== MIGRACIÓN DE PERMISOS DE PROVEEDORES ===\n";

// 1. Cambiar proveedores.gestionar -> proveedores.crear
$stmt = $pdo->prepare("UPDATE permisos SET slug = 'proveedores.crear', descripcion = 'Crear un nuevo proveedor' WHERE slug = 'proveedores.gestionar'");
$stmt->execute();
echo "Permiso 'proveedores.crear' actualizado.\n";

// 2. Insertar proveedores.editar si no existe
$stmtCheck = $pdo->prepare("SELECT id FROM permisos WHERE slug = 'proveedores.editar'");
$stmtCheck->execute();
$editarId = $stmtCheck->fetchColumn();

if (!$editarId) {
    $stmtIns = $pdo->prepare("INSERT INTO permisos (slug, modulo, descripcion) VALUES ('proveedores.editar', 'Proveedores', 'Editar y activar/desactivar proveedores')");
    $stmtIns->execute();
    $editarId = $pdo->lastInsertId();
    echo "Permiso 'proveedores.editar' creado con ID $editarId.\n";
} else {
    echo "Permiso 'proveedores.editar' ya existía con ID $editarId.\n";
}

// 3. Obtener el ID de proveedores.crear
$stmtCrear = $pdo->prepare("SELECT id FROM permisos WHERE slug = 'proveedores.crear'");
$stmtCrear->execute();
$crearId = $stmtCrear->fetchColumn();

// 4. Asegurar que los roles que tenían proveedores.crear también tengan proveedores.editar
if ($crearId && $editarId) {
    $stmtRoles = $pdo->prepare("SELECT rol_id FROM rol_permisos WHERE permiso_id = :crear_id");
    $stmtRoles->execute(['crear_id' => $crearId]);
    $roles = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);

    $stmtAsignar = $pdo->prepare("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) VALUES (:rol_id, :permiso_id)");
    foreach ($roles as $rolId) {
        $stmtAsignar->execute(['rol_id' => $rolId, 'permiso_id' => $editarId]);
        echo "Asignado 'proveedores.editar' al rol ID $rolId.\n";
    }
}

echo "\n--- ESTADO ACTUAL DE PERMISOS EN PROVEEDORES ---\n";
$stmt = $pdo->query("SELECT * FROM permisos WHERE modulo = 'Proveedores' ORDER BY id");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- MATRIZ DE ROL_PERMISOS PROVEEDORES ---\n";
$stmt = $pdo->query("SELECT rp.rol_id, r.nombre AS rol_nombre, p.id AS permiso_id, p.slug FROM rol_permisos rp INNER JOIN roles r ON r.id = rp.rol_id INNER JOIN permisos p ON p.id = rp.permiso_id WHERE p.modulo = 'Proveedores' ORDER BY rp.rol_id, p.id");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
