<?php use Core\Auth; ?>
<div class="container-fluid px-0">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1 fw-bold text-primary">
                <i class="bi bi-grid-3x3-gap-fill me-2"></i>Gestiones
            </h5>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Listado de trabajos por proveedor y estado de asignación
            </p>
        </div>
        <?php if (Auth::can('gestiones.crear')): ?>
        <div class="mt-2 mt-sm-0">
            <a href="<?= base_url('/gestiones/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-2"></i>Nueva gestión
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filtros mejorados -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= base_url('/gestiones') ?>" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="buscar" value="<?= e($filtros['buscar']) ?>" 
                               class="form-control border-start-0" 
                               placeholder="Buscar por trabajo o N° cotización...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="proveedor_id" class="form-select form-select-sm">
                        <option value="">Todos los proveedores</option>
                        <?php foreach ($proveedores as $p): ?>
                            <option value="<?= (int) $p['id'] ?>" 
                                    <?= (string) $filtros['proveedor_id'] === (string) $p['id'] ? 'selected' : '' ?>>
                                <?= e($p['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" id="sinAsignar" 
                               name="sin_asignar" value="1" 
                               <?= $filtros['sin_asignar'] === '1' ? 'checked' : '' ?> 
                               onchange="this.form.submit()">
                        <label class="form-check-label small fw-semibold text-secondary" for="sinAsignar">
                            <i class="bi bi-exclamation-circle me-1"></i>Sin proforma
                        </label>
                    </div>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary btn-sm w-100 rounded-pill" title="Filtrar">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($gestiones)): ?>
        <!-- Estado vacío mejorado -->
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <div class="mb-3">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                </div>
                <h6 class="fw-bold text-secondary">No hay gestiones registradas</h6>
                <p class="text-muted small">Comienza creando tu primera gestión de trabajo</p>
                <?php if (Auth::can('gestiones.crear')): ?>
                <a href="<?= base_url('/gestiones/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-4 mt-2">
                    <i class="bi bi-plus-lg me-2"></i>Crear primera gestión
                </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Tabla mejorada -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 tabla-gestiones">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-semibold text-secondary ps-3 py-2">Gestión</th>
                            <th class="fw-semibold text-secondary ps-3 py-2">Proveedor</th>
                            <th class="fw-semibold text-secondary ps-3 py-2">Cotización</th>
                            <th class="fw-semibold text-secondary ps-3 py-2">Trabajo</th>
                            <th class="fw-semibold text-secondary text-end ps-3 py-2">Valor</th>
                            <th class="fw-semibold text-secondary text-center ps-3 py-2" style="min-width: 100px;">Tiempo</th>
                            <th class="fw-semibold text-secondary text-center ps-3 py-2">Proforma</th>
                            <th class="fw-semibold text-secondary text-end pe-3 ps-3 py-2" style="min-width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($gestiones as $gIndex => $g): ?>
                        <?php
                            $trabajos = $trabajosPorGestion[$g['id']] ?? [];
                            $rowspan = max(1, count($trabajos));
                            $bgClase = $gIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle';
                        ?>
                        <?php if (empty($trabajos)): ?>
                            <tr class="<?= $bgClase ?>">
                                <td class="ps-3 py-2">
                                    <span class="text-primary fw-semibold">#<?= (int) $g['id'] ?></span>
                                    <div class="mt-1">
                                        <span class="badge bg-light text-dark fw-normal">
                                            <i class="bi bi-cash me-1"></i><?= fmt_money($g['valor_total']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td><?= e($g['proveedor_nombre'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($g['n_cotizacion'])): ?>
                                        <span class="badge bg-primary-subtle text-primary fw-normal">
                                            <?= e($g['n_cotizacion']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Sin cotización</span>
                                    <?php endif; ?>
                                </td>
                                <td colspan="3" class="text-muted fst-italic">
                                    <i class="bi bi-info-circle me-1"></i>Sin trabajos registrados
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        Sin proforma
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= base_url('/gestiones/' . $g['id']) ?>" 
                                       class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i>Ver
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trabajos as $index => $t): ?>
                                <tr class="<?= $bgClase ?>">
                                    <?php if ($index === 0): ?>
                                        <td rowspan="<?= $rowspan ?>" class="ps-3 py-2 align-middle">
                                            <div class="text-primary fw-semibold">#<?= (int) $g['id'] ?></div>
                                            <div class="mt-1">
                                                <span class="badge bg-success-subtle text-success fw-normal">
                                                    <i class="bi bi-cash me-1"></i><?= fmt_money($g['valor_total']) ?>
                                                </span>
                                            </div>
                                            <div class="mt-1">
                                                <span class="badge bg-light text-dark fw-normal">
                                                    <i class="bi bi-list-task me-1"></i><?= (int) $g['total_trabajos'] ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td rowspan="<?= $rowspan ?>" class="align-middle">
                                            <?= e($g['proveedor_nombre'] ?? '—') ?>
                                        </td>
                                        <td rowspan="<?= $rowspan ?>" class="align-middle">
                                            <?php if (!empty($g['n_cotizacion'])): ?>
                                                <span class="badge bg-primary-subtle text-primary fw-normal">
                                                    <?= e($g['n_cotizacion']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    
                                    <td class="py-2">
                                        <span class="fw-medium"><?= e($t['descripcion']) ?></span>
                                    </td>
                                    
                                    <td class="text-end text-nowrap">
                                        <span class="fw-semibold"><?= fmt_money($t['valor']) ?></span>
                                    </td>
                                    
                                    <?php if ($index === 0): ?>
                                        <td class="text-center align-middle" rowspan="<?= $rowspan ?>">
                                            <?php if (!empty($g['fecha_finalizacion_trabajo'])): ?>
                                                <?php $diasEnCurso = days_since($g['fecha_finalizacion_trabajo']); ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal px-2 py-1">
                                                    <i class="bi bi-clock me-1"></i><?= $diasEnCurso ?> días
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    
                                    <td class="text-center">
                                        <?php if (!empty($t['proforma_id'])): ?>
                                            <a href="<?= base_url('/proformas/' . $t['proforma_id']) ?>" 
                                               class="badge bg-success text-white text-decoration-none fw-normal px-2 py-1">
                                                <i class="bi bi-file-earmark-check me-1"></i>
                                                <?= e($t['n_proforma'] ?: ('#' . $t['proforma_id'])) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal px-2 py-1">
                                                <i class="bi bi-hourglass-split me-1"></i>Sin asignar
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <?php if ($index === 0): ?>
                                        <td class="text-end pe-3 align-middle" rowspan="<?= $rowspan ?>">
                                            <div class="d-flex justify-content-end gap-1">
                                                <?php if (!empty($g['documento_pdf'])): ?>
                                                    <a href="<?= base_url('uploads/gestiones/' . $g['documento_pdf']) ?>" 
                                                       target="_blank" 
                                                       class="btn btn-outline-danger btn-sm rounded-pill" 
                                                       title="Ver PDF">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('/gestiones/' . $g['id']) ?>" 
                                                   class="btn btn-outline-primary btn-sm rounded-pill" 
                                                   title="Ver detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
<!-- Footer de la tabla con contador y paginación -->
            <div class="card-footer bg-white border-top-0 py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted">
                        <i class="bi bi-database me-1"></i>
                        Mostrando <?= count($gestiones) ?> de <?= (int) ($paginacion['total_registros'] ?? count($gestiones)) ?> gestión(es)
                        <?php if (!empty($paginacion['total_paginas']) && $paginacion['total_paginas'] > 1): ?>
                            &middot; Página <?= (int) $paginacion['pagina_actual'] ?> de <?= (int) $paginacion['total_paginas'] ?>
                        <?php endif; ?>
                    </small>

                    <?php if (!empty($paginacion['total_paginas']) && $paginacion['total_paginas'] > 1): ?>
                        <?php
                            $paginaActual  = (int) $paginacion['pagina_actual'];
                            $totalPaginas  = (int) $paginacion['total_paginas'];

                            $paramsBase = $filtros;
                            $buildUrl = function ($pagina) use ($paramsBase) {
                                $params = $paramsBase;
                                $params['pagina'] = $pagina;
                                $params = array_filter($params, fn($v) => $v !== null && $v !== '');
                                return base_url('/gestiones') . '?' . http_build_query($params);
                            };

                            $rango = 2;
                            $inicio = max(1, $paginaActual - $rango);
                            $fin    = min($totalPaginas, $paginaActual + $rango);
                        ?>
                        <nav aria-label="Paginación de gestiones">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-start-pill" href="<?= $paginaActual > 1 ? $buildUrl(1) : '#' ?>" title="Primera página">
                                        <i class="bi bi-chevron-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $paginaActual > 1 ? $buildUrl($paginaActual - 1) : '#' ?>" title="Anterior">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>

                                <?php if ($inicio > 1): ?>
                                    <li class="page-item disabled d-none d-sm-block"><span class="page-link border-0">…</span></li>
                                <?php endif; ?>

                                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $buildUrl($i) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($fin < $totalPaginas): ?>
                                    <li class="page-item disabled d-none d-sm-block"><span class="page-link border-0">…</span></li>
                                <?php endif; ?>

                                <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $paginaActual < $totalPaginas ? $buildUrl($paginaActual + 1) : '#' ?>" title="Siguiente">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-end-pill" href="<?= $paginaActual < $totalPaginas ? $buildUrl($totalPaginas) : '#' ?>" title="Última página">
                                        <i class="bi bi-chevron-double-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                    <small class="text-muted d-none d-md-inline">
                        <i class="bi bi-clock me-1"></i>
                        Última actualización: <?= date('d/m/Y H:i') ?>
                    </small>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Reduce el tamaño de letra general de la tabla para mejor lectura */
    .tabla-gestiones {
        font-size: 0.85rem;
    }

    .tabla-gestiones thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .tabla-gestiones .badge {
        font-size: 0.72rem;
        font-weight: 500;
    }

    .tabla-gestiones .btn {
        font-size: 0.78rem;
    }

    /* Estilos personalizados para mejorar la apariencia */
    .table > :not(caption) > * > * {
        border-bottom-color: rgba(0,0,0,0.05);
    }
    
    .table > thead {
        border-bottom: 2px solid #e9ecef;
    }
    
    .table-hover > tbody > tr:hover {
        background-color: rgba(13, 110, 253, 0.04) !important;
        transition: background-color 0.15s ease;
    }
    
    .btn-outline-primary.btn-sm.rounded-pill {
        padding: 0.25rem 0.75rem;
    }
    
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .card {
        border-radius: 12px !important;
        overflow: hidden;
    }
    
    .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
    }
    
    .input-group .form-control {
        border-left: none;
        padding-left: 0.5rem;
    }
    
    .input-group .form-control:focus {
        border-left: none;
        box-shadow: none;
    }
</style>