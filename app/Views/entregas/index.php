<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1 fw-bold text-primary">
                <i class="bi bi-truck me-2"></i>Entrega de facturas
            </h5>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Seguimiento de entrega al dueño y solicitud de orden de pago
            </p>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="<?= base_url('/entregas/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-2"></i>Nueva entrega
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= base_url('/entregas') ?>" class="row g-3 align-items-end">
                <div class="col-md-10">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="buscar" value="<?= e($filtros['buscar'] ?? '') ?>"
                               class="form-control border-start-0"
                               placeholder="Buscar por N° OCE o N° proforma...">
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100 rounded-pill" title="Filtrar">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($entregas)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <div class="mb-3">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                </div>
                <h6 class="fw-bold text-secondary">No hay entregas registradas</h6>
                <p class="text-muted small">Comienza registrando tu primera entrega de factura</p>
                <a href="<?= base_url('/entregas/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-4 mt-2">
                    <i class="bi bi-plus-lg me-2"></i>Crear primera entrega
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 tabla-entregas">
                    <thead class="bg-light">
                        <tr>

                            <th class="fw-semibold text-secondary py-2">N° OCE</th>
                            <th class="fw-semibold text-secondary py-2">N° Factura</th>
                            <th class="fw-semibold text-secondary py-2">Proforma</th>
                            <th class="fw-semibold text-secondary py-2">Proveedor</th>
                            <th class="fw-semibold text-secondary py-2">Recibida (Ares Sun)</th>
                            <th class="fw-semibold text-secondary text-center py-2" style="min-width: 140px;">Tiempo recepción → entrega</th>
                            <th class="fw-semibold text-secondary py-2">Entregada al dueño</th>
                            <th class="fw-semibold text-secondary py-2">Solicitud de pago</th>
                            <th class="fw-semibold text-secondary text-center py-2" style="min-width: 140px;">Días para pasar factura</th>
                            <th class="fw-semibold text-secondary text-end pe-3 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($entregas as $eIndex => $e): ?>
                        <?php
                            $rowClass = $eIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle';

                            $diasRecepcionEntrega = days_between($e['fecha_entrega_factura'], $e['fecha_entrega_dueno']);

                            $dias = days_between($e['fecha_entrega_dueno'], $e['fecha_solicitud_revision_pago']);
                            $diasEnCurso = $e['fecha_solicitud_revision_pago'] === null ? days_since($e['fecha_entrega_dueno']) : null;
                        ?>
                        <tr class="<?= $rowClass ?>">

                          <td class="py-2"><span class="fw-medium"><?= e($e['n_oce_interna'] ?? '—') ?></span></td>
                        <td class="py-2">
                            <?php if (!empty($e['n_factura'])): ?>
                                <span class="badge bg-dark-subtle text-dark fw-normal"><?= e($e['n_factura']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2">
                            <?php if (!empty($e['n_proforma'])): ?>
                                <span class="badge bg-primary-subtle text-primary fw-normal"><?= e($e['n_proforma']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                            <td class="py-2"><?= e($e['proveedor_nombre'] ?? '—') ?></td>
                            <td class="py-2"><?= fmt_date($e['fecha_entrega_factura']) ?></td>
                            <td class="text-center py-2">
                                <?php if ($diasRecepcionEntrega !== null): ?>
                                    <span class="badge <?= $diasRecepcionEntrega > 8 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> fw-normal">
                                        <i class="bi bi-clock-history me-1"></i><?= $diasRecepcionEntrega ?> días
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2"><?= fmt_date($e['fecha_entrega_dueno']) ?></td>
                            <td class="py-2"><?= fmt_date($e['fecha_solicitud_revision_pago']) ?></td>
                            <td class="text-center py-2">
                                <?php if ($dias !== null): ?>
                                    <span class="badge <?= $dias > 8 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> fw-normal">
                                        <i class="bi bi-clock me-1"></i><?= $dias ?> días
                                    </span>
                                <?php elseif ($diasEnCurso !== null): ?>
                                    <span class="badge <?= $diasEnCurso >= 8 ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning' ?> fw-normal">
                                        <i class="bi bi-hourglass-split me-1"></i><?= $diasEnCurso ?> días (en curso)
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3 py-2">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if (!empty($e['documento_pdf'])): ?>
                                        <a href="<?= base_url('uploads/entregas/' . $e['documento_pdf']) ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill" title="Ver PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('/entregas/' . $e['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill" title="Ver detalles"><i class="bi bi-eye"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer de la tabla con contador y paginación -->
            <div class="card-footer bg-white border-top-0 py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted">
                        <i class="bi bi-database me-1"></i>
                        Mostrando <?= count($entregas) ?> de <?= (int) ($paginacion['total_registros'] ?? count($entregas)) ?> entrega(s)
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
                                return base_url('/entregas') . '?' . http_build_query($params);
                            };

                            $rango = 2;
                            $inicio = max(1, $paginaActual - $rango);
                            $fin    = min($totalPaginas, $paginaActual + $rango);
                        ?>
                        <nav aria-label="Paginación de entregas">
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
    .tabla-entregas {
        font-size: 0.85rem;
    }

    .tabla-entregas thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .tabla-entregas .badge {
        font-size: 0.72rem;
        font-weight: 500;
    }

    .tabla-entregas .btn {
        font-size: 0.78rem;
    }

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

    .btn-outline-primary.btn-sm.rounded-pill,
    .btn-outline-danger.btn-sm.rounded-pill {
        padding: 0.25rem 0.75rem;
    }

    .card {
        border-radius: 12px !important;
        overflow: hidden;
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

    /* Paginación */
    .pagination .page-link {
        color: #0d6efd;
        border-color: #e9ecef;
        font-size: 0.8rem;
    }

    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .pagination .page-item.disabled .page-link {
        color: #adb5bd;
    }
</style>