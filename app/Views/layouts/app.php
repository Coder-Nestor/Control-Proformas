<?php use Core\Auth; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Proformas y Facturas</title>

    <!-- Todas las librerías se sirven LOCALMENTE desde /assets/vendor (sin CDN),
         para funcionar detrás del firewall Fortinet del servidor local. -->
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        /* Blindaje del layout: evita que el sidebar se comprima cuando el
           contenido de una página (tablas anchas, tarjetas con texto largo, etc.)
           empuja el ancho del contenedor flex. */
        #layout-wrapper {
            align-items: stretch;
        }

        #sidebar {
            flex-shrink: 0;
        }

        #layout-wrapper > .flex-grow-1 {
            min-width: 0; /* clave: permite que el contenido se contraiga/scrollee en vez de expandir el flex container */
        }

        #layout-wrapper > .flex-grow-1 main {
            overflow-x: auto; /* si algo interno es muy ancho, hace scroll horizontal solo ahí, no empuja el sidebar */
        }

        .sidebar .nav-link {
            white-space: nowrap;
        }
    </style>
</head>
<body>

<?php if (Auth::check()): ?>
<div class="d-flex" id="layout-wrapper">

    <!-- Sidebar -->
    <nav class="sidebar bg-dark text-white" id="sidebar">
        <div class="sidebar-brand px-3 py-3 border-bottom border-secondary d-flex align-items-center gap-2">
            <img src="<?= asset('img/logo.png') ?>" alt="Azucarera Choluteca" style="height: 36px; width: auto;">
            <span class="fw-semibold">Control Proformas</span>
        </div>
        <ul class="nav flex-column py-2">
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/dashboard') ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/gestiones') ?>">
                    <i class="bi bi-tools me-2"></i> Gestiones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/proformas') ?>">
                    <i class="bi bi-file-earmark-text me-2"></i> Proformas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/ordenes') ?>">
                    <i class="bi bi-cart-check me-2"></i> Órdenes de compra
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/facturas') ?>">
                    <i class="bi bi-receipt me-2"></i> Emisión de facturas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/entregas') ?>">
                    <i class="bi bi-truck me-2"></i> Entrega de facturas
                </a>
            </li>
            <?php if (Auth::hasRole(['administrador', 'auditoria'])): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/proveedores') ?>">
                    <i class="bi bi-truck me-2"></i> Proveedores
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasRole(['administrador'])): ?>
 <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/areas') ?>" title="Áreas">
                    <i class="bi bi-diagram-3 me-2"></i> <span class="nav-text">Áreas</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasRole(['administrador'])): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= base_url('/usuarios') ?>" title="Usuarios">
                    <i class="bi bi-people me-2"></i> <span class="nav-text">Usuarios</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Contenido -->
    <div class="flex-grow-1">
        <header class="topbar d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-white">
            <button class="btn btn-sm btn-outline-secondary d-xl-none" id="btnToggleSidebar">
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