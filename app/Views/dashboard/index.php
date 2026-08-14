<div class="container-fluid px-0">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </h4>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Lo que necesita tu atención hoy
            </p>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="<?= base_url('/gestiones/crear') ?>" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i>Nueva gestión
            </a>
        </div>
    </div>

    <!-- Fila 1: 4 alertas de acción, tamaño uniforme -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <a href="<?= base_url('/gestiones?sin_asignar=1') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-4">
                        <div class="bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;font-size:1.5rem;">
                            <i class="bi bi-inboxes"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold"><?= (int) $sinAsignar ?></div>
                            <div class="text-muted small">Gestiones sin proforma</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= base_url('/gestiones') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-4">
                        <div class="bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;font-size:1.5rem;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold"><?= count($atrasadas) ?></div>
                            <div class="text-muted small">Gestiones atrasadas</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= base_url('/proformas?sin_oc=1') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-4">
                        <div class="bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;font-size:1.5rem;">
                            <i class="bi bi-cart"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold"><?= (int) $proformasSinOc ?></div>
                            <div class="text-muted small">Proformas sin OC</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= base_url('/entregas/crear') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-4">
                        <div class="bg-info-subtle text-info rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;font-size:1.5rem;">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold"><?= (int) $facturasSinEntrega ?></div>
                            <div class="text-muted small">Facturas sin entrega</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Fila 2: la alerta más crítica destacada + el valor total, ambas más grandes -->
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <a href="<?= base_url('/entregas') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                    <div class="card-body d-flex align-items-center gap-4 py-4">
                        <div class="bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:64px;height:64px;font-size:1.9rem;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <div class="fs-1 fw-bold text-danger lh-1"><?= count($entregasAtrasadas) ?></div>
                            <div class="fw-semibold mt-1">Entregas atrasadas</div>
                            <div class="text-muted small">Más de <?= (int) $umbralFactura ?> días sin solicitar orden de pago — regla crítica del proceso</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-4 py-4">
                    <div class="bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:64px;height:64px;font-size:1.9rem;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold lh-1"><?= fmt_money($valorTotalCotizado) ?></div>
                        <div class="text-muted small mt-1">Valor total cotizado</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

   

    <!-- Resumen del proceso: Cotización -> Proforma -> OCE -> Factura, tal como en el Excel -->
    <div class="card border-0 shadow-sm" id="cardResumen">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <ul class="nav nav-tabs card-header-tabs" id="tabsResumen" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#panelResumenProformas" type="button">
                            <i class="bi bi-file-earmark-text me-1"></i> Resumen · Proformas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#panelResumenFacturas" type="button">
                            <i class="bi bi-receipt me-1"></i> Resumen · Facturas
                        </button>
                    </li>
                </ul>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="exportarResumenPDF()">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Exportar PDF
                </button>
            </div>
        </div>
        <div class="tab-content">
            <!-- Panel: Resumen de Proformas -->
            <div class="tab-pane fade show active" id="panelResumenProformas">
                <div class="card-body pt-3">
                    <div class="d-none d-print-flex align-items-center gap-3 pb-3 mb-3 print-header">
                        <img src="<?= asset('img/logo.png') ?>" alt="Logo" class="print-logo">
                        <div>
                            <h5 class="mb-0">Resumen de Proformas</h5>
                            <small class="text-muted">Control de Proformas y Facturas</small>
                        </div>
                    </div>
                    <?php if (empty($resumenProformas)): ?>
                        <p class="text-muted mb-0 text-center py-4">Todavía no hay trabajos asignados a ninguna proforma.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                <tr>
                                    <th class="fw-semibold text-secondary ps-3 py-2">N° Cotización</th>
                                    <th class="fw-semibold text-secondary py-2">N° Proforma</th>
                                    <th class="fw-semibold text-secondary text-end py-2">Valor Proforma</th>
                                    <th class="fw-semibold text-secondary py-2">Solicitado por</th>
                                    <th class="fw-semibold text-secondary pe-3 py-2">Comentario</th>
                                </tr>
                                </thead>
                                <tbody id="tbodyResumenProformas">
                                <?php foreach ($resumenProformas as $rIndex => $r): ?>
                                    <tr class="<?= $rIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle' ?>">
                                        <td class="ps-3 py-2">
                                            <span class="badge bg-secondary-subtle text-secondary fw-normal"><?= e($r['n_cotizacion'] ?? '—') ?></span>
                                        </td>
                                        <td class="py-2">
                                            <a href="<?= base_url('/proformas/' . $r['proforma_id']) ?>" class="badge bg-primary-subtle text-primary text-decoration-none fw-normal">
                                                <?= e($r['n_proforma'] ?: '#' . $r['proforma_id']) ?>
                                            </a>
                                        </td>
                                        <td class="text-end py-2 fw-semibold"><?= fmt_money($r['valor_proforma']) ?></td>
                                        <td class="py-2"><?= e($r['solicitado_por'] ?? '—') ?></td>
                                        <td class="pe-3 py-2 text-muted small"><?= e($r['comentario'] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="paginacionResumenProformas" class="mt-3 d-print-none"></div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Panel: Resumen de Facturas -->
            <div class="tab-pane fade" id="panelResumenFacturas">
                <div class="card-body pt-3">
                    <div class="d-none d-print-flex align-items-center gap-3 pb-3 mb-3 print-header">
                        <img src="<?= asset('img/logo.png') ?>" alt="Logo" class="print-logo">
                        <div>
                            <h5 class="mb-0">Resumen de Facturas</h5>
                            <small class="text-muted">Control de Proformas y Facturas</small>
                        </div>
                    </div>
                    <?php if (empty($resumenFacturas)): ?>
                        <p class="text-muted mb-0 text-center py-4">Todavía no hay facturas emitidas sobre ninguna proforma.</p>
                    <?php else: ?>
                        <?php $badgeEstado = ['correcta' => 'success', 'pendiente' => 'warning', 'con_problema' => 'danger']; ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                <tr>
                                    <th class="fw-semibold text-secondary ps-3 py-2">N° Cotización</th>
                                    <th class="fw-semibold text-secondary py-2">N° Proforma</th>
                                    <th class="fw-semibold text-secondary text-end py-2">Valor Proforma</th>
                                    <th class="fw-semibold text-secondary py-2">N° OCE e interna</th>
                                    <th class="fw-semibold text-secondary py-2">N° Factura</th>
                                    <th class="fw-semibold text-secondary py-2">Estado</th>
                                    <th class="fw-semibold text-secondary pe-3 py-2">Comentario</th>
                                </tr>
                                </thead>
                                <tbody id="tbodyResumenFacturas">
                                <?php foreach ($resumenFacturas as $rIndex => $r): ?>
                                    <tr class="<?= $rIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle' ?>">
                                        <td class="ps-3 py-2">
                                            <span class="badge bg-secondary-subtle text-secondary fw-normal"><?= e($r['n_cotizacion'] ?? '—') ?></span>
                                        </td>
                                        <td class="py-2 fw-medium"><?= e($r['n_proforma'] ?? '—') ?></td>
                                        <td class="text-end py-2 fw-semibold"><?= fmt_money($r['valor_proforma']) ?></td>
                                        <td class="py-2"><?= e($r['n_oce_interna'] ?? '—') ?></td>
                                        <td class="py-2">
                                            <?php if (!empty($r['n_factura'])): ?>
                                                <a href="<?= base_url('/facturas/' . $r['factura_id']) ?>" class="badge bg-dark-subtle text-dark text-decoration-none fw-normal">
                                                    <?= e($r['n_factura']) ?>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= base_url('/facturas/' . $r['factura_id'] . '/editar') ?>" class="text-decoration-none text-muted small fst-italic" title="Todavía no se capturó el N° Factura — clic para completarlo">
                                                    Sin capturar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2">
                                            <span class="badge bg-<?= $badgeEstado[$r['estado']] ?? 'secondary' ?>-subtle text-<?= $badgeEstado[$r['estado']] ?? 'secondary' ?> fw-normal">
                                                <?= e(\App\Models\Factura::ESTADOS[$r['estado']] ?? $r['estado']) ?>
                                            </span>
                                        </td>
                                        <td class="pe-3 py-2 text-muted small"><?= e($r['comentario'] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="paginacionResumenFacturas" class="mt-3 d-print-none"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0 py-2">
            <small class="text-muted">
                <i class="bi bi-clock me-1"></i>
                Última actualización: <?= date('d/m/Y H:i') ?>
            </small>
        </div>
    </div>
</div>

<style>
    .card { border-radius: 12px !important; overflow: hidden; }
    .card-header { padding: 1rem 1.25rem 0.5rem 1.25rem; background-color: transparent; border-bottom: 1px solid rgba(0,0,0,0.05); }
    .badge { font-weight: 500; }
    .btn.rounded-pill { border-radius: 50px !important; }
    .table > thead { border-bottom: 2px solid #e9ecef; }
    .table-hover > tbody > tr:hover { background-color: rgba(13, 110, 253, 0.04) !important; }
    .card-header-tabs .nav-link { border: none; color: #6c757d; font-weight: 500; }
    .card-header-tabs .nav-link.active { color: #0d6efd; border-bottom: 2px solid #0d6efd; background: transparent; }

    /* ===================== Exportar PDF (impresión) ===================== */
    @media print {
        /* Hoja en horizontal, con margen — las tablas de Facturas tienen
           7 columnas y no caben bien en vertical. */
        @page {
            size: landscape;
            margin: 12mm 10mm;
        }

        /* Fuerza a que se impriman los colores de fondo (badges, franjas) —
           sin esto, la mayoría de navegadores los omiten por defecto. */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        body {
            background: #fff !important;
        }

        /* Oculta todo lo que no sea la tarjeta de Resumen */
        .sidebar, .topbar, .sidebar-backdrop {
            display: none !important;
        }
        .container-fluid > *:not(#cardResumen) {
            display: none !important;
        }
        #cardResumen {
            box-shadow: none !important;
            border: none !important;
        }
        #cardResumen .nav-tabs,
        #cardResumen .btn,
        #cardResumen .card-footer {
            display: none !important;
        }

        /* Membrete */
        .print-header {
            display: flex !important;
            border-bottom: 2px solid #212529;
        }
        .print-logo {
            height: 42px;
            width: auto;
        }

        /* Tabla con formato de reporte: bordes, encabezado repetido en
           cada página, y filas que no se cortan a la mitad entre páginas. */
        #cardResumen table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 10.5pt;
        }
        #cardResumen table th,
        #cardResumen table td {
            border: 1px solid #adb5bd !important;
            padding: 6px 8px !important;
        }
        #cardResumen thead {
            display: table-header-group;
        }
        #cardResumen tbody tr {
            page-break-inside: avoid;
        }
        #cardResumen .badge {
            border: 1px solid rgba(0,0,0,.15);
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var FILAS_POR_PAGINA = 10;
    var tablasPaginadas = [];

    /**
     * Pagina una tabla en el navegador (sin recargar la página).
     * Guarda referencias para poder "mostrar todas las filas" al exportar a PDF
     * y luego volver a la página en la que estaba el usuario.
     */
    function paginarTabla(tbodyId, contenedorPaginacionId) {
        var tbody = document.getElementById(tbodyId);
        var contenedor = document.getElementById(contenedorPaginacionId);
        if (!tbody || !contenedor) {
            return;
        }

        var filas = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        var totalPaginas = Math.max(1, Math.ceil(filas.length / FILAS_POR_PAGINA));
        var paginaActual = 1;

        function mostrarPagina(pagina) {
            paginaActual = Math.min(Math.max(1, pagina), totalPaginas);
            var inicio = (paginaActual - 1) * FILAS_POR_PAGINA;
            var fin = inicio + FILAS_POR_PAGINA;
            filas.forEach(function (fila, i) {
                fila.style.display = (i >= inicio && i < fin) ? '' : 'none';
            });
            renderControles();
        }

        function renderControles() {
            if (totalPaginas <= 1) {
                contenedor.innerHTML = '';
                return;
            }
            var html = '<nav><ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">';
            html += botonPagina(paginaActual - 1, '‹ Anterior', paginaActual === 1);
            for (var i = 1; i <= totalPaginas; i++) {
                html += '<li class="page-item' + (i === paginaActual ? ' active' : '') + '">' +
                        '<button type="button" class="page-link" data-pagina="' + i + '">' + i + '</button></li>';
            }
            html += botonPagina(paginaActual + 1, 'Siguiente ›', paginaActual === totalPaginas);
            html += '</ul></nav>';
            contenedor.innerHTML = html;

            contenedor.querySelectorAll('.page-link[data-pagina]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    mostrarPagina(parseInt(this.getAttribute('data-pagina'), 10));
                });
            });
        }

        function botonPagina(pagina, texto, deshabilitado) {
            return '<li class="page-item' + (deshabilitado ? ' disabled' : '') + '">' +
                   '<button type="button" class="page-link" data-pagina="' + pagina + '">' + texto + '</button></li>';
        }

        mostrarPagina(1);

        // Se guarda la referencia para poder mostrar/restaurar desde exportarResumenPDF().
        tablasPaginadas.push({
            mostrarTodas: function () {
                filas.forEach(function (fila) { fila.style.display = ''; });
            },
            restaurar: function () {
                mostrarPagina(paginaActual);
            }
        });
    }

    paginarTabla('tbodyResumenProformas', 'paginacionResumenProformas');
    paginarTabla('tbodyResumenFacturas', 'paginacionResumenFacturas');

    // Al exportar a PDF, se muestran TODAS las filas de la tabla activa
    // (no solo la página visible), para que el reporte impreso quede completo.
    window.exportarResumenPDF = function () {
        tablasPaginadas.forEach(function (t) { t.mostrarTodas(); });
        window.print();
        setTimeout(function () {
            tablasPaginadas.forEach(function (t) { t.restaurar(); });
        }, 300);
    };
});
</script>