<?php use Core\Auth ;$badgeEstado = [
    'correcta'     => 'bg-success-subtle text-success',
    'pendiente'    => 'bg-warning-subtle text-warning',
    'con_problema' => 'bg-danger-subtle text-danger',
]; ?>

<div class="container-fluid px-0">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1 fw-bold text-primary">
                <i class="bi bi-receipt me-2"></i>Emisión de facturas
            </h5>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Gestión y seguimiento de facturas emitidas
            </p>
        </div>

        <?php if (Auth::can('facturas.crear')): ?>
        <div class="mt-2 mt-sm-0 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnImprimirSeleccionadas" disabled>
                <i class="bi bi-printer me-2"></i>Imprimir seleccionadas (<span id="contadorSeleccionadas">0</span>)
            </button>
            <a href="<?= base_url('/facturas/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-2"></i>Nueva factura
            </a>
        </div>
       <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= base_url('/facturas') ?>" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="buscar" value="<?= e($filtros['buscar'] ?? '') ?>"
                               class="form-control border-start-0"
                               placeholder="Buscar por N° OCE o N° proforma...">
                    </div>
                </div>
                <div class="col-md-5">
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($filtros['estado'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
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

    <?php if (empty($facturas)): ?>
        <!-- Estado vacío -->
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <div class="mb-3">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                </div>
                <h6 class="fw-bold text-secondary">No hay facturas registradas</h6>
                <p class="text-muted small">Comienza registrando tu primera factura emitida</p>
                <a href="<?= base_url('/facturas/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-4 mt-2">
                    <i class="bi bi-plus-lg me-2"></i>Crear primera factura
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Tabla -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 tabla-facturas">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-2 text-center" style="width:36px;">
                                <input type="checkbox" class="form-check-input" id="checkTodas" title="Seleccionar todas">
                            </th>
                            <th class="fw-semibold text-secondary py-2">N° OCE e interna</th>
                            <th class="fw-semibold text-secondary py-2">N° Factura</th>
                            <th class="fw-semibold text-secondary py-2">N° Proforma</th>
                            <th class="fw-semibold text-secondary py-2">Proveedor</th>
                            <th class="fw-semibold text-secondary py-2">Fecha entrega factura</th>
                            <th class="fw-semibold text-secondary text-center py-2" style="min-width: 100px;">Tiempo</th>
                            <th class="fw-semibold text-secondary text-center py-2">Estado</th>
                            <th class="fw-semibold text-secondary text-end pe-3 py-2" style="min-width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($facturas as $fIndex => $f): ?>
                        <?php
                            $dias = days_between($f['fecha_envio_oce'], $f['fecha_entrega_factura']);
                            $bgClase = $fIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle';
                        ?>
                        <tr class="<?= $bgClase ?>">
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input check-factura"
                                       data-id="<?= (int) $f['id'] ?>"
                                       data-oce="<?= e($f['n_oce_interna'] ?? '—') ?>"
                                       data-factura="<?= !empty($f['n_factura']) ? e($f['n_factura']) : '—' ?>"
                                       data-proforma="<?= !empty($f['n_proforma']) ? e($f['n_proforma']) : '—' ?>"
                                       data-proveedor="<?= e($f['proveedor_nombre'] ?? '—') ?>"
                                       data-valor="<?= e(fmt_money($f['valor_proforma'])) ?>"
                                       data-fecha="<?= e(fmt_date($f['fecha_entrega_factura'])) ?>"
                                       data-estado="<?= e($estados[$f['estado']] ?? $f['estado']) ?>">
                            </td>
                            <td class="py-2"><span class="fw-medium"><?= e($f['n_oce_interna'] ?? '—') ?></span></td>
                            <td class="py-2">
                                <?php if (!empty($f['n_factura'])): ?>
                                    <span class="badge bg-dark-subtle text-dark fw-normal"><?= e($f['n_factura']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2">
                                <?php if (!empty($f['n_proforma'])): ?>
                                    <span class="badge bg-primary-subtle text-primary fw-normal"><?= e($f['n_proforma']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2"><?= e($f['proveedor_nombre'] ?? '—') ?></td>
                            <td class="py-2"><?= fmt_date($f['fecha_entrega_factura']) ?></td>
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
                                <span class="badge <?= $badgeEstado[$f['estado']] ?? 'bg-secondary bg-opacity-10 text-secondary' ?> fw-normal px-2 py-1">
                                    <?= e($estados[$f['estado']]) ?>
                                </span>
                                <?php if ($f['estado'] === 'correcta'): ?>
                                    <i class="bi bi-lock-fill text-muted ms-1" title="Cerrada — solo un Administrador puede editarla o eliminarla"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3 py-2">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if (!empty($f['documento_pdf'])): ?>
                                        <a href="<?= base_url('uploads/facturas/' . $f['documento_pdf']) ?>"
                                           target="_blank"
                                           class="btn btn-outline-danger btn-sm rounded-pill"
                                           title="Ver PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (Auth::can('facturas.editar') && ($f['estado'] !== 'correcta' || Auth::hasRole(['administrador']))): ?>
                                        <a href="<?= base_url('/facturas/' . $f['id'] . '/editar') ?>"
                                           class="btn btn-outline-primary btn-sm rounded-pill"
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('/facturas/' . $f['id']) ?>"
                                       class="btn btn-outline-primary btn-sm rounded-pill"
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (Auth::can('facturas.eliminar') && ($f['estado'] !== 'correcta' || Auth::hasRole(['administrador']))): ?>
                                        <form method="POST" action="<?= base_url('/facturas/' . $f['id'] . '/eliminar') ?>" class="d-inline" onsubmit="return confirm('¿Eliminar esta factura? La entrega asociada (si existe) también se eliminará. Esta acción no se puede deshacer.');">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-outline-danger btn-sm rounded-pill" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
                        Mostrando <?= count($facturas) ?> de <?= (int) ($paginacion['total_registros'] ?? count($facturas)) ?> factura(s)
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
                                return base_url('/facturas') . '?' . http_build_query($params);
                            };

                            $rango = 2;
                            $inicio = max(1, $paginaActual - $rango);
                            $fin    = min($totalPaginas, $paginaActual + $rango);
                        ?>
                        <nav aria-label="Paginación de facturas">
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
    .tabla-facturas {
        font-size: 0.85rem;
    }

    .tabla-facturas thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .tabla-facturas .badge {
        font-size: 0.72rem;
        font-weight: 500;
    }

    .tabla-facturas .btn {
        font-size: 0.78rem;
    }

    /* Estilos personalizados para mejorar la apariencia (idénticos al de Gestiones) */
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

<!-- ============================================================ -->
<!-- ÁREA DE IMPRESIÓN: formato oficial "Constancia de Entrega y  -->
<!-- Recepción de Documentación" de Azucarera Choluteca. Se llena -->
<!-- por JS, paginando de a 10 filas por hoja (con encabezado y   -->
<!-- firmas repetidos en cada hoja) para que sea uniforme sin     -->
<!-- importar cuántas facturas se marquen.                        -->
<!-- ============================================================ -->
<div class="d-none d-print-block" id="areaImpresionFacturas"></div>

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
        .container-fluid > *:not(#areaImpresionFacturas) {
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
    .const-tabla { width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-bottom: 6px; table-layout: fixed; }
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
        return Array.from(document.querySelectorAll('.check-factura'));
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
                '<td>' + chk.dataset.oce + '</td>' +
                '<td>' + chk.dataset.factura + '</td>' +
                '<td>' + chk.dataset.proforma + '</td>' +
                '<td>' + chk.dataset.proveedor + '</td>' +
                '<td>' + chk.dataset.valor + '</td>' +
                '<td>' + chk.dataset.fecha + '</td>' +
                '<td>' + chk.dataset.estado + '</td>' +
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
                    '<colgroup><col style="width:14%"><col style="width:13%"><col style="width:13%"><col style="width:16%"><col style="width:13%"><col style="width:15%"><col style="width:16%"></colgroup>' +
                    '<thead><tr><th>N° OCE e interna</th><th>N° Factura</th><th>N° Proforma</th><th>Proveedor</th><th>Valor</th><th>Fecha entrega</th><th>Estado</th></tr></thead>' +
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
            const contenedor = document.getElementById('areaImpresionFacturas');
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