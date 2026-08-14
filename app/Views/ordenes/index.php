<?php $badgeClass = ['correcta' => 'success', 'pendiente' => 'warning', 'con_problema' => 'danger']; ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1 fw-bold text-primary">
                <i class="bi bi-receipt-cutoff me-2"></i>Órdenes de compra
            </h5>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Listado de órdenes de compra y su estado de gestión
            </p>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="<?= base_url('/ordenes/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-2"></i>Nueva orden de compra
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= base_url('/ordenes') ?>" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="buscar" value="<?= e($filtros['buscar'] ?? '') ?>"
                               class="form-control border-start-0"
                               placeholder="Buscar por N° OCE o N° proforma...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($filtros['estado'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100 rounded-pill" title="Filtrar">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($ordenes)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <div class="mb-3">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                </div>
                <h6 class="fw-bold text-secondary">No hay órdenes de compra registradas</h6>
                <p class="text-muted small">Comienza creando tu primera orden de compra</p>
                <a href="<?= base_url('/ordenes/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-4 mt-2">
                    <i class="bi bi-plus-lg me-2"></i>Crear primera orden de compra
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 tabla-ordenes">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-semibold text-secondary py-2">N° OCE e interna</th>
                            <th class="fw-semibold text-secondary py-2">Proforma</th>
                            <th class="fw-semibold text-secondary py-2">Proveedor</th>
                            <th class="fw-semibold text-secondary py-2">Fecha envío</th>
                            <th class="fw-semibold text-secondary text-center py-2">Tiempo (proforma → OC)</th>
                            <th class="fw-semibold text-secondary text-center py-2">Estado</th>
                            <th class="fw-semibold text-secondary text-end pe-3 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ordenes as $ocIndex => $oc): ?>
                        <?php
                            $rowClass = $ocIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle';
                            $dias = days_between($oc['fecha_revision_proforma'], $oc['fecha_envio_oce']);
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="py-2"><span class="fw-medium"><?= e($oc['n_oce_interna'] ?? '—') ?></span></td>
                            <td class="py-2">
                                <a href="<?= base_url('/proformas/' . $oc['proforma_id']) ?>" class="badge bg-primary-subtle text-primary text-decoration-none fw-normal">
                                    <?= e($oc['n_proforma'] ?: '#' . $oc['proforma_id']) ?>
                                </a>
                            </td>
                            <td class="py-2"><?= e($oc['proveedor_nombre'] ?? '—') ?></td>
                            <td class="py-2"><?= fmt_date($oc['fecha_envio_oce']) ?></td>
                            <td class="text-center py-2">
                                <?php if ($dias !== null): ?>
                                    <span class="badge <?= $dias > 8 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> fw-normal">
                                        <i class="bi bi-clock me-1"></i><?= $dias ?> días
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center py-2">
                                <span class="badge text-bg-<?= $badgeClass[$oc['estado']] ?? 'bg-secondary bg-opacity-10 text-secondary' ?> fw-normal px-2 py-1"><?= e($estados[$oc['estado']]) ?></span>
                            </td>
                            <td class="text-end pe-3 py-2">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if (!empty($oc['documento_pdf'])): ?>
                                        <a href="<?= base_url('uploads/ordenes_compra/' . $oc['documento_pdf']) ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill" title="Ver PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('/ordenes/' . $oc['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill" title="Ver detalles"><i class="bi bi-eye"></i></a>
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
                        Mostrando <?= count($ordenes) ?> de <?= (int) ($paginacion['total_registros'] ?? count($ordenes)) ?> orden(es) de compra
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
                                return base_url('/ordenes') . '?' . http_build_query($params);
                            };

                            $rango = 2;
                            $inicio = max(1, $paginaActual - $rango);
                            $fin    = min($totalPaginas, $paginaActual + $rango);
                        ?>
                        <nav aria-label="Paginación de órdenes de compra">
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
    .tabla-ordenes {
        font-size: 0.85rem;
    }

    .tabla-ordenes thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .tabla-ordenes .badge {
        font-size: 0.72rem;
        font-weight: 500;
    }

    .tabla-ordenes .btn {
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