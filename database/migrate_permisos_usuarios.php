<?php
$c = require __DIR__ . '/../config/config.php';
$db = $c['db'];
$pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['database'], $db['username'], $db['password']);

echo "=== MIGRACIÓN DE PERMISOS DE USUARIOS ===\n";

// 1. Cambiar usuarios.gestionar -> usuarios.crear
$stmt = $pdo->prepare("UPDATE permisos SET slug = 'usuarios.crear', descripcion = 'Crear un usuario nuevo' WHERE slug = 'usuarios.gestionar'");
$stmt->execute();
echo "Permiso 'usuarios.crear' actualizado.\n";

// 2. Insertar usuarios.editar si no existe
$stmtCheck = $pdo->prepare("SELECT id FROM permisos WHERE slug = 'usuarios.editar'");
$stmtCheck->execute();
$editarId = $stmtCheck->fetchColumn();

if (!$editarId) {
    $stmtIns = $pdo->prepare("INSERT INTO permisos (slug, modulo, descripcion) VALUES ('usuarios.editar', 'Usuarios', 'Editar y activar/desactivar usuarios')");
    $stmtIns->execute();
    $editarId = $pdo->lastInsertId();
    echo "Permiso 'usuarios.editar' creado con ID $editarId.\n";
} else {
    echo "Permiso 'usuarios.editar' ya existía con ID $editarId.\n";
}

// 3. Obtener el ID de usuarios.crear
$stmtCrear = $pdo->prepare("SELECT id FROM permisos WHERE slug = 'usuarios.crear'");
$stmtCrear->execute();
$crearId = $stmtCrear->fetchColumn();

// 4. Asegurar que los roles que tenían usuarios.crear también tengan usuarios.editar
if ($crearId && $editarId) {
    $stmtRoles = $pdo->prepare("SELECT rol_id FROM rol_permisos WHERE permiso_id = :crear_id");
    $stmtRoles->execute(['crear_id' => $crearId]);
    $roles = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);

    $stmtAsignar = $pdo->prepare("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) VALUES (:rol_id, :permiso_id)");
    foreach ($roles as $rolId) {
        $stmtAsignar->execute(['rol_id' => $rolId, 'permiso_id' => $editarId]);
        echo "Asignado 'usuarios.editar' al rol ID $rolId.\n";
    }
}

echo "\n--- ESTADO ACTUAL DE PERMISOS EN USUARIOS ---\n";
$stmt = $pdo->query("SELECT * FROM permisos WHERE modulo = 'Usuarios' ORDER BY id");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- MATRIZ DE ROL_PERMISOS USUARIOS ---\n";
$stmt = $pdo->query("SELECT rp.rol_id, r.nombre AS rol_nombre, p.id AS permiso_id, p.slug FROM rol_permisos rp INNER JOIN roles r ON r.id = rp.rol_id INNER JOIN permisos p ON p.id = rp.permiso_id WHERE p.modulo = 'Usuarios' ORDER BY rp.rol_id, p.id");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
