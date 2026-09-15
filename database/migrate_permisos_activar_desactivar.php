<?php
$c = require __DIR__ . '/../config/config.php';
$db = $c['db'];
$pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['database'], $db['username'], $db['password']);

echo "=== MIGRACIÓN DE PERMISOS ACTIVAR/DESACTIVAR SEPARADOS ===\n";

// 1. Actualizar descripciones de editar
$pdo->exec("UPDATE permisos SET descripcion = 'Editar información de proveedores' WHERE slug = 'proveedores.editar'");
$pdo->exec("UPDATE permisos SET descripcion = 'Editar información y roles de usuarios' WHERE slug = 'usuarios.editar'");

// 2. Insertar proveedores.activar si no existe
$stmtCheck = $pdo->prepare("SELECT id FROM permisos WHERE slug = 'proveedores.activar'");
$stmtCheck->execute();
$provActivarId = $stmtCheck->fetchColumn();

if (!$provActivarId) {
    $stmtIns = $pdo->prepare("INSERT INTO permisos (slug, modulo, descripcion) VALUES ('proveedores.activar', 'Proveedores', 'Activar/desactivar proveedores y pase a proforma')");
    $stmtIns->execute();
    $provActivarId = $pdo->lastInsertId();
    echo "Permiso 'proveedores.activar' creado con ID $provActivarId.\n";
} else {
    echo "Permiso 'proveedores.activar' ya existía con ID $provActivarId.\n";
}

// 3. Insertar usuarios.activar si no existe
$stmtCheck = $pdo->prepare("SELECT id FROM permisos WHERE slug = 'usuarios.activar'");
$stmtCheck->execute();
$userActivarId = $stmtCheck->fetchColumn();

if (!$userActivarId) {
    $stmtIns = $pdo->prepare("INSERT INTO permisos (slug, modulo, descripcion) VALUES ('usuarios.activar', 'Usuarios', 'Activar/desactivar cuentas de usuario')");
    $stmtIns->execute();
    $userActivarId = $pdo->lastInsertId();
    echo "Permiso 'usuarios.activar' creado con ID $userActivarId.\n";
} else {
    echo "Permiso 'usuarios.activar' ya existía con ID $userActivarId.\n";
}

// 4. Asignar proveedores.activar a roles que tienen proveedores.editar
$stmt = $pdo->prepare("SELECT rol_id FROM rol_permisos WHERE permiso_id = (SELECT id FROM permisos WHERE slug = 'proveedores.editar')");
$stmt->execute();
$rolesProv = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmtAsignar = $pdo->prepare("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) VALUES (:rol_id, :permiso_id)");
foreach ($rolesProv as $rId) {
    $stmtAsignar->execute(['rol_id' => $rId, 'permiso_id' => $provActivarId]);
    echo "Asignado 'proveedores.activar' al rol $rId.\n";
}

// 5. Asignar usuarios.activar a roles que tienen usuarios.editar
$stmt = $pdo->prepare("SELECT rol_id FROM rol_permisos WHERE permiso_id = (SELECT id FROM permisos WHERE slug = 'usuarios.editar')");
$stmt->execute();
$rolesUser = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($rolesUser as $rId) {
    $stmtAsignar->execute(['rol_id' => $rId, 'permiso_id' => $userActivarId]);
    echo "Asignado 'usuarios.activar' al rol $rId.\n";
}

echo "\n--- ESTADO FINAL DE PERMISOS EN PROVEEDORES Y USUARIOS ---\n";
$stmt = $pdo->query("SELECT id, slug, modulo, descripcion FROM permisos WHERE modulo IN ('Proveedores', 'Usuarios') ORDER BY modulo, id");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
