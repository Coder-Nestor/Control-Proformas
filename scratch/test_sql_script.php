<?php
$c = require __DIR__ . '/../config/config.php';
$db = $c['db'];
$pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['database'], $db['username'], $db['password']);

$sql = "
-- 1. Permisos Proveedores
UPDATE permisos SET slug = 'proveedores.crear', descripcion = 'Crear un nuevo proveedor' WHERE slug = 'proveedores.gestionar';
INSERT IGNORE INTO permisos (slug, modulo, descripcion) VALUES 
('proveedores.editar', 'Proveedores', 'Editar información de proveedores'),
('proveedores.activar', 'Proveedores', 'Activar/desactivar proveedores y pase a proforma');

-- 2. Permisos Usuarios
UPDATE permisos SET slug = 'usuarios.crear', descripcion = 'Crear un usuario nuevo' WHERE slug = 'usuarios.gestionar';
INSERT IGNORE INTO permisos (slug, modulo, descripcion) VALUES 
('usuarios.editar', 'Usuarios', 'Editar información y roles de usuarios'),
('usuarios.activar', 'Usuarios', 'Activar/desactivar cuentas de usuario');

-- 3. Asignaciones Administrador
INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permisos p
WHERE r.slug = 'administrador'
  AND p.slug IN ('proveedores.crear', 'proveedores.editar', 'proveedores.activar', 'usuarios.crear', 'usuarios.editar', 'usuarios.activar');

-- 4. Asignaciones Auditoría y Solicitante para Proveedores
INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permisos p
WHERE r.slug IN ('auditoria', 'solicitante')
  AND p.slug IN ('proveedores.crear', 'proveedores.editar', 'proveedores.activar');
";

$pdo->exec($sql);
echo "SQL ejecutado sin errores.\n";
