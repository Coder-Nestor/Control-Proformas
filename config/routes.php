<?php
/**
 * Definición de rutas: [método, patrón, Controlador@método, roles_permitidos|null]
 * roles_permitidos = null  -> cualquier usuario autenticado
 * roles_permitidos = ['*'] -> público (no requiere login)
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

return [
    // Autenticación (público)
    ['GET',  '/login',  [AuthController::class, 'showLogin'],  ['*']],
    ['POST', '/login',  [AuthController::class, 'login'],      ['*']],
    ['POST', '/logout', [AuthController::class, 'logout'],     null],

    // Dashboard
    ['GET', '/',          [DashboardController::class, 'index'], null],
    ['GET', '/dashboard', [DashboardController::class, 'index'], null],

    // Gestiones (trabajos por proveedor -> se les asigna una Proforma)
    ['GET',  '/gestiones',               [GestionController::class, 'index'],   null],
    ['GET',  '/gestiones/crear',         [GestionController::class, 'create'],  ['administrador', 'auditoria', 'solicitante']],
    ['POST', '/gestiones',               [GestionController::class, 'store'],   ['administrador', 'auditoria', 'solicitante']],
    ['GET',  '/gestiones/{id}',          [GestionController::class, 'show'],    null],
    ['GET',  '/gestiones/{id}/editar',   [GestionController::class, 'edit'],    ['administrador', 'auditoria', 'solicitante']],
    ['POST', '/gestiones/{id}',          [GestionController::class, 'update'],  ['administrador', 'auditoria', 'solicitante']],
    ['POST', '/gestiones/{id}/eliminar', [GestionController::class, 'destroy'], ['administrador']],

    // Proformas (agrupan Gestiones)
    ['GET',  '/proformas',               [ProformaController::class, 'index'],   null],
    ['GET',  '/proformas/crear',         [ProformaController::class, 'create'],  ['administrador', 'auditoria', 'solicitante']],
    ['GET',  '/proformas/cotizacion',    [ProformaController::class, 'buscarTrabajosPorCotizacion'], ['administrador', 'auditoria', 'solicitante']],
    ['POST', '/proformas',               [ProformaController::class, 'store'],   ['administrador', 'auditoria', 'solicitante']],
    ['GET',  '/proformas/{id}',          [ProformaController::class, 'show'],    null],
    ['GET',  '/proformas/{id}/editar',   [ProformaController::class, 'edit'],    ['administrador', 'auditoria', 'solicitante']],
    ['POST', '/proformas/{id}',          [ProformaController::class, 'update'],  ['administrador', 'auditoria', 'solicitante']],
    ['POST', '/proformas/{id}/eliminar', [ProformaController::class, 'destroy'], ['administrador']],

    // Órdenes de compra (1 a 1 con Proforma)
    ['GET',  '/ordenes',               [OrdenCompraController::class, 'index'],   null],
    ['GET',  '/ordenes/crear',         [OrdenCompraController::class, 'create'],  ['administrador', 'auditoria']],
    ['POST', '/ordenes',               [OrdenCompraController::class, 'store'],   ['administrador', 'auditoria']],
    ['GET',  '/ordenes/{id}',          [OrdenCompraController::class, 'show'],    null],
    ['GET',  '/ordenes/{id}/editar',   [OrdenCompraController::class, 'edit'],    ['administrador', 'auditoria']],
    ['POST', '/ordenes/{id}',          [OrdenCompraController::class, 'update'],  ['administrador', 'auditoria']],
    ['POST', '/ordenes/{id}/eliminar', [OrdenCompraController::class, 'destroy'], ['administrador']],

    // Emisión de facturas
    ['GET',  '/facturas',               [FacturaController::class, 'index'],   null],
    ['GET',  '/facturas/crear',         [FacturaController::class, 'create'],  ['administrador', 'auditoria']],
    ['POST', '/facturas',               [FacturaController::class, 'store'],   ['administrador', 'auditoria']],
    ['GET',  '/facturas/{id}',          [FacturaController::class, 'show'],    null],
    ['GET',  '/facturas/{id}/editar',   [FacturaController::class, 'edit'],    ['administrador', 'auditoria']],
    ['POST', '/facturas/{id}',          [FacturaController::class, 'update'],  ['administrador', 'auditoria']],
    ['POST', '/facturas/{id}/eliminar', [FacturaController::class, 'destroy'], ['administrador']],

    // Entrega de facturas
    ['GET',  '/entregas',               [EntregaFacturaController::class, 'index'],   null],
    ['GET',  '/entregas/crear',         [EntregaFacturaController::class, 'create'],  ['administrador', 'auditoria']],
    ['POST', '/entregas',               [EntregaFacturaController::class, 'store'],   ['administrador', 'auditoria']],
    ['GET',  '/entregas/{id}',          [EntregaFacturaController::class, 'show'],    null],
    ['GET',  '/entregas/{id}/editar',   [EntregaFacturaController::class, 'edit'],    ['administrador', 'auditoria']],
    ['POST', '/entregas/{id}',          [EntregaFacturaController::class, 'update'],  ['administrador', 'auditoria']],
    ['POST', '/entregas/{id}/eliminar', [EntregaFacturaController::class, 'destroy'], ['administrador']],

    // Proveedores
    ['GET',  '/proveedores',               [ProveedorController::class, 'index'],   ['administrador', 'auditoria']],
    ['POST', '/proveedores',               [ProveedorController::class, 'store'],   ['administrador']],
    ['POST', '/proveedores/{id}',          [ProveedorController::class, 'update'],  ['administrador']],
    ['POST', '/proveedores/{id}/eliminar', [ProveedorController::class, 'destroy'], ['administrador']],

    // Áreas
    ['GET',  '/areas',               [AreaController::class, 'index'],   ['administrador', 'auditoria']],
    ['POST', '/areas',               [AreaController::class, 'store'],   ['administrador']],
    ['POST', '/areas/{id}',          [AreaController::class, 'update'],  ['administrador']],
    ['POST', '/areas/{id}/eliminar', [AreaController::class, 'destroy'], ['administrador']],

    // Usuarios (solo administrador)
    ['GET',  '/usuarios',               [UsuarioController::class, 'index'],  ['administrador']],
    ['GET',  '/usuarios/crear',         [UsuarioController::class, 'create'], ['administrador']],
    ['POST', '/usuarios',               [UsuarioController::class, 'store'],  ['administrador']],
    ['GET',  '/usuarios/{id}/editar',   [UsuarioController::class, 'edit'],   ['administrador']],
    ['POST', '/usuarios/{id}',          [UsuarioController::class, 'update'], ['administrador']],
    ['POST', '/usuarios/{id}/eliminar', [UsuarioController::class, 'destroy'], ['administrador']],
];
