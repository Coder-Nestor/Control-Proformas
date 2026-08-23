<?php
/**
 * Definición de rutas: [método, patrón, Controlador@método, roles_permitidos|permiso|null]
 * roles_permitidos = null        -> cualquier usuario autenticado
 * roles_permitidos = ['*']       -> público (no requiere login)
 * roles_permitidos = ['admin',..]-> array de roles fijos (solo /roles la sigue usando, a propósito)
 * roles_permitidos = 'modulo.accion' -> permiso dinámico (editable desde /roles)
 */

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\GestionController;
use App\Controllers\ProformaController;
use App\Controllers\OrdenCompraController;
use App\Controllers\FacturaController;
use App\Controllers\EntregaFacturaController;
use App\Controllers\ProveedorController;
use App\Controllers\UsuarioController;
use App\Controllers\AreaController;
use App\Controllers\HistorialController;
use App\Controllers\RolController;

return [
    // Autenticación (público)
    ['GET',  '/login',  [AuthController::class, 'showLogin'],  ['*']],
    ['POST', '/login',  [AuthController::class, 'login'],      ['*']],
    ['POST', '/logout', [AuthController::class, 'logout'],     null],

    // Dashboard
    ['GET', '/',          [DashboardController::class, 'index'], null],
    ['GET', '/dashboard', [DashboardController::class, 'index'], null],

    // Gestiones — MIGRADO
    ['GET',  '/gestiones',               [GestionController::class, 'index'],   'gestiones.ver'],
    ['GET',  '/gestiones/crear',         [GestionController::class, 'create'],  'gestiones.crear'],
    ['POST', '/gestiones',               [GestionController::class, 'store'],   'gestiones.crear'],
    ['GET',  '/gestiones/{id}',          [GestionController::class, 'show'],    'gestiones.ver'],
    ['GET',  '/gestiones/{id}/editar',   [GestionController::class, 'edit'],    'gestiones.editar'],
    ['POST', '/gestiones/{id}',          [GestionController::class, 'update'],  'gestiones.editar'],
    ['POST', '/gestiones/{id}/eliminar', [GestionController::class, 'destroy'], 'gestiones.eliminar'],

    // Proformas — MIGRADO
    ['GET',  '/proformas',               [ProformaController::class, 'index'],   'proformas.ver'],
    ['GET',  '/proformas/crear',         [ProformaController::class, 'create'],  'proformas.crear'],
    ['GET',  '/proformas/cotizacion',    [ProformaController::class, 'buscarTrabajosPorCotizacion'], 'proformas.crear'],
    ['POST', '/proformas',               [ProformaController::class, 'store'],   'proformas.crear'],
    ['GET',  '/proformas/{id}',          [ProformaController::class, 'show'],    'proformas.ver'],
    ['GET',  '/proformas/{id}/editar',   [ProformaController::class, 'edit'],    'proformas.editar'],
    ['POST', '/proformas/{id}',          [ProformaController::class, 'update'],  'proformas.editar'],
    ['POST', '/proformas/{id}/eliminar', [ProformaController::class, 'destroy'], 'proformas.eliminar'],

    // Órdenes de compra — MIGRADO
    ['GET',  '/ordenes',               [OrdenCompraController::class, 'index'],   'ordenes.ver'],
    ['GET',  '/ordenes/crear',         [OrdenCompraController::class, 'create'],  'ordenes.crear'],
    ['POST', '/ordenes',               [OrdenCompraController::class, 'store'],   'ordenes.crear'],
    ['GET',  '/ordenes/{id}',          [OrdenCompraController::class, 'show'],    'ordenes.ver'],
    ['GET',  '/ordenes/{id}/editar',   [OrdenCompraController::class, 'edit'],    'ordenes.editar'],
    ['POST', '/ordenes/{id}',          [OrdenCompraController::class, 'update'],  'ordenes.editar'],
    ['POST', '/ordenes/{id}/eliminar', [OrdenCompraController::class, 'destroy'], 'ordenes.eliminar'],

    // Emisión de facturas — MIGRADO
    ['GET',  '/facturas',               [FacturaController::class, 'index'],   'facturas.ver'],
    ['GET',  '/facturas/crear',         [FacturaController::class, 'create'],  'facturas.crear'],
    ['POST', '/facturas',               [FacturaController::class, 'store'],   'facturas.crear'],
    ['GET',  '/facturas/{id}',          [FacturaController::class, 'show'],    'facturas.ver'],
    ['GET',  '/facturas/{id}/editar',   [FacturaController::class, 'edit'],    'facturas.editar'],
    ['POST', '/facturas/{id}',          [FacturaController::class, 'update'],  'facturas.editar'],
    ['POST', '/facturas/{id}/eliminar', [FacturaController::class, 'destroy'], 'facturas.eliminar'],

    // Entrega de facturas — MIGRADO
    ['GET',  '/entregas',               [EntregaFacturaController::class, 'index'],   'entregas.ver'],
    ['GET',  '/entregas/crear',         [EntregaFacturaController::class, 'create'],  'entregas.crear'],
    ['POST', '/entregas',               [EntregaFacturaController::class, 'store'],   'entregas.crear'],
    ['GET',  '/entregas/{id}',          [EntregaFacturaController::class, 'show'],    'entregas.ver'],
    ['GET',  '/entregas/{id}/editar',   [EntregaFacturaController::class, 'edit'],    'entregas.editar'],
    ['POST', '/entregas/{id}',          [EntregaFacturaController::class, 'update'],  'entregas.editar'],
    ['POST', '/entregas/{id}/eliminar', [EntregaFacturaController::class, 'destroy'], 'entregas.eliminar'],

    // Proveedores (ya migrado antes)
    ['GET',  '/proveedores',               [ProveedorController::class, 'index'],   'proveedores.ver'],
    ['POST', '/proveedores',               [ProveedorController::class, 'store'],   'proveedores.gestionar'],
    ['POST', '/proveedores/{id}',          [ProveedorController::class, 'update'],  'proveedores.gestionar'],
    ['POST', '/proveedores/{id}/eliminar', [ProveedorController::class, 'destroy'], 'proveedores.eliminar'],

    // Áreas — MIGRADO
    ['GET',  '/areas',               [AreaController::class, 'index'],   'areas.ver'],
    ['POST', '/areas',               [AreaController::class, 'store'],   'areas.gestionar'],
    ['POST', '/areas/{id}',          [AreaController::class, 'update'],  'areas.gestionar'],
    ['POST', '/areas/{id}/eliminar', [AreaController::class, 'destroy'], 'areas.eliminar'],

    // Historial / Auditoría — MIGRADO
    ['GET', '/historial', [HistorialController::class, 'index'], 'historial.ver'],

    // Usuarios (ya migrado antes)
    ['GET',  '/usuarios',               [UsuarioController::class, 'index'],  'usuarios.ver'],
    ['GET',  '/usuarios/crear',         [UsuarioController::class, 'create'], 'usuarios.gestionar'],
    ['POST', '/usuarios',               [UsuarioController::class, 'store'],  'usuarios.gestionar'],
    ['GET',  '/usuarios/{id}/editar',   [UsuarioController::class, 'edit'],   'usuarios.gestionar'],
    ['POST', '/usuarios/{id}',          [UsuarioController::class, 'update'], 'usuarios.gestionar'],
    ['POST', '/usuarios/{id}/eliminar', [UsuarioController::class, 'destroy'], 'usuarios.eliminar'],


    //roles

    ['GET',  '/roles',               [RolController::class, 'index'], ['administrador']],
    ['POST', '/roles',               [RolController::class, 'store'], ['administrador']],
    ['GET',  '/roles/{id}/permisos', [RolController::class, 'permisos'], ['administrador']],
    ['POST', '/roles/{id}/permisos', [RolController::class, 'guardarPermisos'], ['administrador']],
    ['POST', '/roles/{id}/eliminar', [RolController::class, 'destroy'], ['administrador']],
];