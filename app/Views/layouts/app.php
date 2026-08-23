<?php use Core\Auth; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Proformas y Facturas</title>

    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>

<?php if (Auth::check()): ?>
<div class="d-flex" id="layout-wrapper">

    <nav class="sidebar bg-dark text-white" id="sidebar">
        <div class="sidebar-brand px-3 py-3 border-bottom border-secondary d-flex align-items-center gap-2">
            <img src="<?= asset('img/logo.png') ?>" alt="Azucarera Choluteca" class="sidebar-logo" height="44" style="height:44px;width:auto;max-width:100%;">
            <span class="fw-semibold sidebar-brand-text">Control Proformas</span>
        </div>
        <ul class="nav flex-column py-2">
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/dashboard') ?>" title="Dashboard">
                    <i class="bi bi-speedometer2 me-2"></i> <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <?php if (Auth::can('gestiones.ver')): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/gestiones') ?>" title="Gestiones">
                    <i class="bi bi-tools me-2"></i> <span class="nav-text">Gestiones</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::can('proformas.ver')): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/proformas') ?>" title="Proformas">
                    <i class="bi bi-file-earmark-text me-2"></i> <span class="nav-text">Proformas</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::can('ordenes.ver')): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/ordenes') ?>" title="Órdenes de compra">
                    <i class="bi bi-cart-check me-2"></i> <span class="nav-text">Órdenes de compra</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::can('facturas.ver')): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/facturas') ?>" title="Emisión de facturas">
                    <i class="bi bi-receipt me-2"></i> <span class="nav-text">Emisión de facturas</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::can('entregas.ver')): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/entregas') ?>" title="Entrega de facturas">
                    <i class="bi bi-truck me-2"></i> <span class="nav-text">Entrega de facturas</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::can('proveedores.ver')): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/proveedores') ?>" title="Proveedores">
                    <i class="bi bi-truck me-2"></i> <span class="nav-text">Proveedores</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::can('areas.ver')): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/areas') ?>" title="Áreas">
                    <i class="bi bi-diagram-3 me-2"></i> <span class="nav-text">Áreas</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::can('historial.ver')): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/historial') ?>" title="Historial / Auditoría">
                    <i class="bi bi-clock-history me-2"></i> <span class="nav-text">Historial</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::can('usuarios.ver')): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/usuarios') ?>" title="Usuarios">
                    <i class="bi bi-people me-2"></i> <span class="nav-text">Usuarios</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasRole(['administrador'])): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/roles') ?>" title="Roles y Permisos">
                    <i class="bi bi-shield-lock me-2"></i> <span class="nav-text">Roles y Permisos</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="flex-grow-1">
        <header class="topbar d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-white">
            <button class="btn btn-sm btn-outline-secondary" id="btnToggleSidebar" title="Mostrar/ocultar menú">
                <i class="bi bi-list"></i>
            </button>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-muted small">
                    <i class="bi bi-person-circle me-1"></i><?= e(current_user_name()) ?>
                    <span class="badge text-bg-secondary ms-1"><?= e(Auth::role()) ?></span>
                </span>
                <form method="POST" action="<?= base_url('/logout') ?>" class="m-0">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger" type="submit">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </button>
                </form>
            </div>
        </header>

        <main class="p-3 p-md-4">
            <?php if ($msg = flash_get('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($msg = flash_get('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $content() ?>
        </main>
    </div>
</div>
<?php else: ?>
    <?= $content() ?>
<?php endif; ?>

<script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('vendor/chartjs/chart.js') ?>"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>