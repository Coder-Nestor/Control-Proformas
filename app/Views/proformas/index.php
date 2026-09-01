<?php use Core\Auth; ?>
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1 fw-bold text-primary">
                <i class="bi bi-file-earmark-check me-2"></i>Proformas
            </h5>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Listado de proformas registradas en el sistema
            </p>
        </div>
        <?php if (Auth::can('proformas.crear')): ?>
        <div class="mt-2 mt-sm-0 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnImprimirSeleccionadas" disabled>
                <i class="bi bi-printer me-2"></i>Imprimir seleccionadas (<span id="contadorSeleccionadas">0</span>)
            </button>
            <a href="<?= base_url('/proformas/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-2"></i>Nueva proforma
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= base_url('/proformas') ?>" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="buscar" value="<?= e($filtros['buscar']) ?>"
                               class="form-control border-start-0"
                               placeholder="Buscar por N° proforma o solicitante...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="proveedor_id" class="form-select form-select-sm">
                        <option value="">Todos los proveedores</option>
                        <?php foreach ($proveedores as $p): ?>
                            <option value="<?= (int) $p['id'] ?>" <?= (string) $filtros['proveedor_id'] === (string) $p['id'] ? 'selected' : '' ?>><?= e($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" id="sinOc" name="sin_oc" value="1" <?= $filtros['sin_oc'] === '1' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label small fw-semibold text-secondary" for="sinOc">
                            <i class="bi bi-exclamation-circle me-1"></i>Sólo sin OC
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

    <?php if (empty($proformas)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <div class="mb-3">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                </div>
                <h6 class="fw-bold text-secondary">No hay proformas registradas</h6>
                <p class="text-muted small">Comienza creando tu primera proforma</p>
                <a href="<?= base_url('/proformas/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-4 mt-2">
                    <i class="bi bi-plus-lg me-2"></i>Crear primera proforma
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 tabla-proformas">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-2 text-center" style="width:36px;">
                                <input type="checkbox" class="form-check-input" id="checkTodas" title="Seleccionar todas">
                            </th>
                            <th class="fw-semibold text-secondary ps-3 py-2"># Proforma</th>
                            <th class="fw-semibold text-secondary py-2">Proveedor</th>
                            <th class="fw-semibold text-secondary py-2">Solicitado por</th>
                            <th class="fw-semibold text-secondary text-center py-2">Tiempo solicitud → revisión</th>
                            <th class="fw-semibold text-secondary text-end py-2">Valor</th>
                            <th class="fw-semibold text-secondary py-2">Trabajo</th>
                            <th class="fw-semibold text-secondary text-center py-2">Orden de compra</th>
                            <th class="fw-semibold text-secondary text-end pe-3 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($proformas as $pIndex => $p): ?>
                        <?php $rowClass = $pIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle'; ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input check-proforma"
                                       data-id="<?= (int) $p['id'] ?>"
                                       data-proforma="<?= e($p['n_proforma'] ?: '#' . $p['id']) ?>"
                                       data-proveedor="<?= e($p['proveedor_nombre'] ?? '—') ?>"
                                       data-solicitado="<?= e($p['solicitado_por'] ?? '—') ?>"
                                       data-valor="<?= e(fmt_money($p['valor_proforma'])) ?>"
                                       data-trabajo="<?= e($p['trabajos_desc'] ?: 'Sin trabajos asignados') ?>"
                                       data-oc="<?= !empty($p['n_oce_interna']) ? e($p['n_oce_interna']) : ($p['tiene_oc'] ? 'Generada (sin número capturado)' : 'Sin OC') ?>">
                            </td>
                            <td class="ps-3 py-2">
                                <div class="fw-semibold text-primary"><?= e($p['n_proforma'] ?? '—') ?></div>
                            </td>
                            <td class="py-2"><?= e($p['proveedor_nombre'] ?? '—') ?></td>
                            <td class="py-2"><?= e($p['solicitado_por'] ?? '—') ?></td>
                            <td class="text-center py-2">
                                <?php if (!empty($p['fecha_solicitud']) && !empty($p['fecha_revision_proforma'])): ?>
                                    <?php $dias = days_between($p['fecha_solicitud'], $p['fecha_revision_proforma']); ?>
                                    <span class="badge <?= $dias > 15 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> fw-normal">
                                        <i class="bi bi-clock me-1"></i><?= $dias ?> días
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap py-2"><?= fmt_money($p['valor_proforma']) ?></td>
                            <td class="py-2">
        <?php if (!empty($p['trabajos_desc'])): ?>
            <div class="text-truncate" style="max-width: 260px;" title="<?= e($p['trabajos_desc']) ?>">
                <?= e($p['trabajos_desc']) ?>
            </div>
            <?php if ((int) $p['total_trabajos'] > 1): ?>
                <span class="badge bg-light text-dark fw-normal border mt-1"><?= (int) $p['total_trabajos'] ?> trabajos</span>
            <?php endif; ?>
        <?php else: ?>
            <span class="text-muted small">Sin trabajos asignados</span>
        <?php endif; ?>
    </td>

                            <td class="text-center py-2">
                                <?php if ($p['tiene_oc']): ?>
                                    <span class="badge bg-success-subtle text-success fw-normal px-2 py-1">Generada</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal px-2 py-1">Sin OC</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3 py-2">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if (!empty($p['documento_pdf'])): ?>
                                        <a href="<?= base_url('uploads/proformas/' . $p['documento_pdf']) ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill" title="Ver PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                    <?php endif; ?>
                                    <?php if (Auth::can('proformas.editar')): ?>
                                        <a href="<?= base_url('/proformas/' . $p['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('/proformas/' . $p['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill" title="Ver detalles"><i class="bi bi-eye"></i></a>
                                    <?php if (Auth::can('proformas.eliminar')): ?>
                                        <form method="POST" action="<?= base_url('/proformas/' . $p['id'] . '/eliminar') ?>" class="d-inline" onsubmit="return confirm('¿Eliminar esta proforma? Los trabajos asociados quedarán sin asignar. Esta acción no se puede deshacer.');">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-outline-danger btn-sm rounded-pill" title="Eliminar"><i class="bi bi-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top-0 py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted">
                        <i class="bi bi-database me-1"></i>
                        Mostrando <?= count($proformas) ?> de <?= (int) ($paginacion['total_registros'] ?? count($proformas)) ?> proforma(s)
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
                                return base_url('/proformas') . '?' . http_build_query($params);
                            };

                            $rango = 2;
                            $inicio = max(1, $paginaActual - $rango);
                            $fin    = min($totalPaginas, $paginaActual + $rango);
                        ?>
                        <nav aria-label="Paginación de proformas">
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
    .tabla-proformas {
        font-size: 0.85rem;
    }

    .tabla-proformas thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .tabla-proformas .badge {
        font-size: 0.72rem;
        font-weight: 500;
    }

    .tabla-proformas .btn {
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

    .badge.bg-light.text-dark.border {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        color: #6c757d !important;
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

<!-- ============================================================ -->
<!-- ÁREA DE IMPRESIÓN: formato oficial "Constancia de Entrega y  -->
<!-- Recepción de Documentación" de Azucarera Choluteca. Se llena -->
<!-- por JS, paginando de a 10 filas por hoja (con encabezado y   -->
<!-- firmas repetidos en cada hoja) para que sea uniforme sin     -->
<!-- importar cuántas proformas se marquen.                       -->
<!-- ============================================================ -->
<div class="d-none d-print-block" id="areaImpresionProformas"></div>

<style>
    @media print {
        @page {
            size: letter portrait;
            margin: 15mm 16mm;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        html, body {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        #layout-wrapper,
        #layout-wrapper > .flex-grow-1,
        #layout-wrapper > .flex-grow-1 > main,
        .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .sidebar,
        .topbar,
        .sidebar-backdrop,
        .alert,
        .container-fluid > *:not(#areaImpresionProformas) {
            display: none !important;
        }
    }

    .hoja-constancia {
        font-family: Arial, sans-serif;
        color: #000;
        page-break-after: always;
        display: flex;
        flex-direction: column;
        min-height: 245mm;
    }
    .hoja-constancia:last-child { page-break-after: auto; }
    .const-header { display: flex; align-items: center; gap: 14px; margin-bottom: 12px; }
    .const-header img { height: 46px; width: auto; }
    .const-header .empresa { font-size: 11pt; font-weight: 600; }
    .const-titulo { text-align: center; font-weight: 700; font-size: 13pt; margin: 6px 0 12px 0; text-transform: uppercase; }
    .const-parrafo { text-align: justify; line-height: 1.4; margin-bottom: 12px; font-size: 10pt; }
    .const-detalle-label { font-weight: 700; margin-bottom: 6px; font-size: 10.5pt; }
    .const-tabla { width: 100%; border-collapse: collapse; font-size: 8pt; margin-bottom: 6px; table-layout: fixed; }
    .const-tabla th, .const-tabla td { border: 1px solid #333; padding: 3px 4px; overflow-wrap: break-word; text-align: left; }
    .const-tabla th { background: #f0f0f0; font-weight: 700; }
    .const-pagina-num { text-align: right; font-size: 8pt; color: #666; margin-bottom: 6px; }
    .const-spacer { flex-grow: 1; }
    .const-footer { margin-top: 20px; font-size: 10.5pt; }
    .const-firma-bloque { margin-bottom: 22px; }
    .const-firma-bloque .rotulo { font-weight: 700; margin-bottom: 4px; }
    .const-firma-linea { margin-top: 30px; }
    .const-cc { display: flex; justify-content: space-between; margin-top: 10px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkTodas = document.getElementById('checkTodas');
    const btnImprimir = document.getElementById('btnImprimirSeleccionadas');
    const contador = document.getElementById('contadorSeleccionadas');
    const FILAS_POR_PAGINA = 10;

    function checks() {
        return Array.from(document.querySelectorAll('.check-proforma'));
    }

    function actualizarContador() {
        const marcadas = checks().filter(c => c.checked).length;
        contador.textContent = marcadas;
        if (btnImprimir) btnImprimir.disabled = marcadas === 0;
    }

    checks().forEach(function (chk) {
        chk.addEventListener('change', function () {
            actualizarContador();
            if (!chk.checked && checkTodas) checkTodas.checked = false;
        });
    });

    if (checkTodas) {
        checkTodas.addEventListener('change', function () {
            checks().forEach(function (chk) { chk.checked = checkTodas.checked; });
            actualizarContador();
        });
    }

    function construirHojaHTML(grupo, paginaActual, totalPaginas) {
        let filasHtml = '';
        grupo.forEach(function (chk) {
            filasHtml +=
                '<tr>' +
                '<td>' + chk.dataset.proforma + '</td>' +
                '<td>' + chk.dataset.proveedor + '</td>' +
                '<td>' + chk.dataset.solicitado + '</td>' +
                '<td>' + chk.dataset.valor + '</td>' +
                '<td>' + chk.dataset.trabajo + '</td>' +
                '<td>' + chk.dataset.oc + '</td>' +
                '</tr>';
        });

        return '' +
            '<div class="hoja-constancia">' +
                '<div class="const-header">' +
                    '<img src="<?= asset("img/logo.png") ?>" alt="Logo">' +
                    '<div class="empresa">Azucarera Choluteca S. A. de C.V.</div>' +
                '</div>' +
                '<div class="const-titulo">Constancia de Entrega y Recepción de Documentación</div>' +
                '<div class="const-parrafo">' +
                    'Por medio del presente documento se hace constar que el Departamento de <strong>Auditoría Interna</strong> hace entrega al Departamento de ________________________________ la siguiente documentación, las cuales han sido previamente revisadas:' +
                '</div>' +
                '<div class="const-detalle-label">Detalle:</div>' +
                '<table class="const-tabla">' +
                    '<colgroup><col style="width:14%"><col style="width:15%"><col style="width:14%"><col style="width:13%"><col style="width:27%"><col style="width:17%"></colgroup>' +
                    '<thead><tr><th># Proforma</th><th>Proveedor</th><th>Solicitado por</th><th>Valor</th><th>Trabajo</th><th>N° OC</th></tr></thead>' +
                    '<tbody>' + filasHtml + '</tbody>' +
                '</table>' +
                (totalPaginas > 1 ? '<div class="const-pagina-num">Página ' + paginaActual + ' de ' + totalPaginas + '</div>' : '') +
                '<div class="const-spacer"></div>' +
                '<div class="const-footer">' +
                    '<div class="const-firma-bloque">' +
                        '<div class="rotulo">ENTREGADO POR</div>' +
                        '<div>Auditoría Interna</div>' +
                        '<div class="const-firma-linea">Firma: ________________________</div>' +
                    '</div>' +
                    '<div class="const-firma-bloque">' +
                        '<div class="rotulo">RECIBIDO POR</div>' +
                        '<div>Departamento de ________________________________.</div>' +
                        '<div class="const-firma-linea">Firma: ________________________</div>' +
                    '</div>' +
                    '<div class="const-cc">' +
                        '<div>CC.<br>Archivo.</div>' +
                        '<div>Fecha: ________________.</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
    }

    if (btnImprimir) {
        btnImprimir.addEventListener('click', function () {
            const seleccionadas = checks().filter(c => c.checked);
            if (seleccionadas.length === 0) return;

            const totalPaginas = Math.ceil(seleccionadas.length / FILAS_POR_PAGINA);
            const contenedor = document.getElementById('areaImpresionProformas');
            let html = '';
            for (let p = 0; p < totalPaginas; p++) {
                const grupo = seleccionadas.slice(p * FILAS_POR_PAGINA, (p + 1) * FILAS_POR_PAGINA);
                html += construirHojaHTML(grupo, p + 1, totalPaginas);
            }
            contenedor.innerHTML = html;

            window.print();
        });
    }

    actualizarContador();
});
</script>