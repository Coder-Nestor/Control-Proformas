<div class="container-fluid px-0">
    <!-- ============================================================ -->
    <!-- ENCABEZADO -->
    <!-- ============================================================ -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary d-flex align-items-center gap-2">
                <i class="bi bi-speedometer2"></i> Dashboard
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

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <a href="<?= base_url('/gestiones?sin_asignar=1') ?>" class="text-decoration-none d-block h-100">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="kpi-icon bg-warning-subtle text-warning">
                            <i class="bi bi-inboxes"></i>
                        </div>
                        <div class="min-width-0">
                            <div class="fs-2 fw-bold lh-1"><?= (int) $sinAsignar ?></div>
                            <div class="text-muted small">Gestiones sin proforma</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= base_url('/gestiones') ?>" class="text-decoration-none d-block h-100">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="kpi-icon bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="min-width-0">
                            <div class="fs-2 fw-bold lh-1"><?= count($atrasadas) ?></div>
                            <div class="text-muted small">Gestiones atrasadas</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= base_url('/proformas?sin_oc=1') ?>" class="text-decoration-none d-block h-100">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="kpi-icon bg-warning-subtle text-warning">
                            <i class="bi bi-cart"></i>
                        </div>
                        <div class="min-width-0">
                            <div class="fs-2 fw-bold lh-1"><?= (int) $proformasSinOc ?></div>
                            <div class="text-muted small">Proformas sin OC</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= base_url('/entregas/crear') ?>" class="text-decoration-none d-block h-100">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="kpi-icon bg-info-subtle text-info">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="min-width-0">
                            <div class="fs-2 fw-bold lh-1"><?= (int) $facturasSinEntrega ?></div>
                            <div class="text-muted small">Facturas sin entrega</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-4 py-4">
                    <div class="kpi-icon kpi-icon-lg bg-success-subtle text-success">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold lh-1 text-success"><?= fmt_money($valorTotalCotizado) ?></div>
                        <div class="text-muted small mt-1">Valor total cotizado</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6 d-none d-lg-block"></div>
    </div>

    <div class="card border-0 shadow-sm" id="cardResumen">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <ul class="nav nav-tabs card-header-tabs border-0" id="tabsResumen" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#panelResumenProformas" type="button">
                            <i class="bi bi-file-earmark-text me-1"></i> Resumen · Proformas
                            <span class="badge bg-primary-subtle text-primary ms-1"><?= count($resumenProformas) ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#panelResumenFacturas" type="button">
                            <i class="bi bi-receipt me-1"></i> Resumen · Facturas
                            <span class="badge bg-primary-subtle text-primary ms-1"><?= count($resumenFacturas) ?></span>
                        </button>
                    </li>
                </ul>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="exportarResumenPDF()">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Exportar PDF
                </button>
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="panelResumenProformas">
                <div class="card-body pt-3">
                    <div class="d-none d-print-flex align-items-center gap-3 pb-3 mb-3 print-header">
                        <img src="<?= asset('img/logo.png') ?>" alt="Logo" class="print-logo">
                        <div>
                            <h5 class="mb-0">Resumen de Proformas</h5>
                            <small class="text-muted">Control de Proformas y Facturas</small>
                        </div>
                        <div class="ms-auto text-end d-none d-print-block">
                            <small class="text-muted d-block">Generado: <?= date('d/m/Y H:i') ?></small>
                            <small class="text-muted d-block">Por: <?= e(current_user_name()) ?></small>
                        </div>
                    </div>

                    <?php if (empty($resumenProformas)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted mb-0">Todavía no hay trabajos asignados a ninguna proforma.</p>
                        </div>
                    <?php else: ?>
                        <?php $totalValorProformas = array_sum(array_column($resumenProformas, 'valor_proforma')); ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold text-secondary ps-3 py-2" style="width:15%">N° Cotización</th>
                                        <th class="fw-semibold text-secondary py-2" style="width:15%">N° Proforma</th>
                                        <th class="fw-semibold text-secondary text-end py-2" style="width:15%">Valor Proforma</th>
                                        <th class="fw-semibold text-secondary py-2" style="width:20%">Solicitado por</th>
                                        <th class="fw-semibold text-secondary pe-3 py-2" style="width:35%">Comentario</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyResumenProformas">
                                    <?php foreach ($resumenProformas as $rIndex => $r): ?>
                                        <tr class="<?= $rIndex % 2 === 0 ? '' : 'table-light' ?>">
                                            <td class="ps-3 py-2">
                                                <?php if (!empty($r['n_cotizacion'])): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary fw-normal"><?= e($r['n_cotizacion']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-info-subtle text-info fw-normal">
                                                        <i class="bi bi-calendar3 me-1"></i>Mensualidad
                                                    </span>
                                                <?php endif; ?>
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
                                <tfoot>
                                    <tr class="fw-semibold" style="border-top: 2px solid #212529;">
                                        <td class="ps-3 py-2" colspan="2">Total (<?= count($resumenProformas) ?> proforma<?= count($resumenProformas) === 1 ? '' : 's' ?>)</td>
                                        <td class="text-end py-2"><?= fmt_money($totalValorProformas) ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="paginacionResumenProformas" class="mt-3 d-print-none"></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="panelResumenFacturas">
                <div class="card-body pt-3">
                    <div class="d-none d-print-flex align-items-center gap-3 pb-3 mb-3 print-header">
                        <img src="<?= asset('img/logo.png') ?>" alt="Logo" class="print-logo">
                        <div>
                            <h5 class="mb-0">Resumen de Facturas</h5>
                            <small class="text-muted">Control de Proformas y Facturas</small>
                        </div>
                        <div class="ms-auto text-end d-none d-print-block">
                            <small class="text-muted d-block">Generado: <?= date('d/m/Y H:i') ?></small>
                            <small class="text-muted d-block">Por: <?= e(current_user_name()) ?></small>
                        </div>
                    </div>

                    <?php if (empty($resumenFacturas)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-receipt fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted mb-0">Todavía no hay facturas emitidas sobre ninguna proforma.</p>
                        </div>
                    <?php else: ?>
                        <?php
                            $badgeEstado = ['correcta' => 'success', 'pendiente' => 'warning', 'con_problema' => 'danger'];
                            $totalValorFacturas = array_sum(array_column($resumenFacturas, 'valor_proforma'));
                        ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold text-secondary ps-3 py-2" style="width:12%">N° Cotización</th>
                                        <th class="fw-semibold text-secondary py-2" style="width:12%">N° Proforma</th>
                                        <th class="fw-semibold text-secondary text-end py-2" style="width:12%">Valor Proforma</th>
                                        <th class="fw-semibold text-secondary py-2" style="width:12%">N° OCE e interna</th>
                                        <th class="fw-semibold text-secondary py-2" style="width:12%">N° Factura</th>
                                        <th class="fw-semibold text-secondary py-2" style="width:12%">Estado</th>
                                        <th class="fw-semibold text-secondary pe-3 py-2" style="width:28%">Comentario</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyResumenFacturas">
                                    <?php foreach ($resumenFacturas as $rIndex => $r): ?>
                                        <tr class="<?= $rIndex % 2 === 0 ? '' : 'table-light' ?>">
                                            <td class="ps-3 py-2">
                                                <?php if (!empty($r['n_cotizacion'])): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary fw-normal"><?= e($r['n_cotizacion']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-info-subtle text-info fw-normal">
                                                        <i class="bi bi-calendar3 me-1"></i>Mensualidad
                                                    </span>
                                                <?php endif; ?>
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
                                <tfoot>
                                    <tr class="fw-semibold" style="border-top: 2px solid #212529;">
                                        <td class="ps-3 py-2" colspan="2">Total (<?= count($resumenFacturas) ?> factura<?= count($resumenFacturas) === 1 ? '' : 's' ?>)</td>
                                        <td class="text-end py-2"><?= fmt_money($totalValorFacturas) ?></td>
                                        <td colspan="4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="paginacionResumenFacturas" class="mt-3 d-print-none"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 py-2 d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted">
                <i class="bi bi-clock me-1"></i>
                Última actualización: <?= date('d/m/Y H:i') ?>
            </small>
            <small class="text-muted">
                <i class="bi bi-layers me-1"></i>
                Total proformas: <?= count($resumenProformas) ?> · Total facturas: <?= count($resumenFacturas) ?>
            </small>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 12px !important;
        overflow: hidden;
        transition: box-shadow 0.2s ease;
        border: none !important;
    }

    .card-header {
        padding: 1rem 1.25rem 0.5rem 1.25rem;
        background-color: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .card-footer {
        background-color: transparent;
        border-top: 1px solid rgba(0, 0, 0, 0.04);
    }

    .badge {
        font-weight: 500;
    }

    .btn.rounded-pill {
        border-radius: 50px !important;
    }

    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .kpi-icon-lg {
        width: 64px;
        height: 64px;
        font-size: 2rem;
        border-radius: 16px;
    }

    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08) !important;
    }

    .card-header-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 0;
        background: transparent;
        position: relative;
    }

    .card-header-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 2.5px solid #0d6efd;
        background: transparent;
    }

    .card-header-tabs .nav-link:hover:not(.active) {
        color: #0d6efd;
        background: rgba(13, 110, 253, 0.04);
    }

    .card-header-tabs .nav-link .badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
    }

    .table > thead {
        border-bottom: 2px solid #e9ecef;
    }

    .table-hover > tbody > tr:hover {
        background-color: rgba(13, 110, 253, 0.04) !important;
    }

    .table-light {
        background-color: rgba(0, 0, 0, 0.02) !important;
    }

    .min-width-0 {
        min-width: 0;
    }

    /* ============================================================ */
    /* IMPRESIÓN (PDF) */
    /* ============================================================ */
    @media print {
        @page {
            size: landscape;
            margin: 12mm 10mm;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
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
        .container-fluid,
        #cardResumen {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            float: none !important;
        }

        .sidebar,
        .topbar,
        .sidebar-backdrop,
        .container-fluid > *:not(#cardResumen),
        #cardResumen .nav-tabs,
        #cardResumen .btn,
        #cardResumen .card-footer,
        #cardResumen .d-print-none {
            display: none !important;
        }

        #cardResumen {
            box-shadow: none !important;
            border: none !important;
        }

        .print-header {
            display: flex !important;
            border-bottom: 2px solid #212529;
        }

        .print-logo {
            height: 42px;
            width: auto;
        }

        #cardResumen table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 10pt;
        }

        #cardResumen table th,
        #cardResumen table td {
            border: 1px solid #adb5bd !important;
            padding: 4px 6px !important;
        }

        #cardResumen thead {
            display: table-header-group;
        }

        #cardResumen tfoot {
            display: table-footer-group;
        }

        #cardResumen tfoot tr {
            background-color: #f1f1f1 !important;
        }

        #cardResumen tbody tr {
            page-break-inside: avoid;
        }

        #cardResumen .badge {
            border: 1px solid rgba(0, 0, 0, 0.15);
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const FILAS_POR_PAGINA = 10;
    const tablasPaginadas = [];

    function paginarTabla(tbodyId, contenedorId) {
        const tbody = document.getElementById(tbodyId);
        const contenedor = document.getElementById(contenedorId);
        if (!tbody || !contenedor) return;

        const filas = Array.from(tbody.querySelectorAll('tr'));
        const totalPaginas = Math.max(1, Math.ceil(filas.length / FILAS_POR_PAGINA));
        let paginaActual = 1;

        function mostrarPagina(pagina) {
            paginaActual = Math.min(Math.max(1, pagina), totalPaginas);
            const inicio = (paginaActual - 1) * FILAS_POR_PAGINA;
            const fin = inicio + FILAS_POR_PAGINA;
            filas.forEach((fila, i) => {
                fila.style.display = (i >= inicio && i < fin) ? '' : 'none';
            });
            renderControles();
        }

        function renderControles() {
            if (totalPaginas <= 1) {
                contenedor.innerHTML = '';
                return;
            }

            let html = `<nav><ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">`;
            html += botonPagina(paginaActual - 1, '‹ Anterior', paginaActual === 1);
            for (let i = 1; i <= totalPaginas; i++) {
                html += `<li class="page-item ${i === paginaActual ? 'active' : ''}">
                            <button class="page-link" data-pagina="${i}">${i}</button>
                        </li>`;
            }
            html += botonPagina(paginaActual + 1, 'Siguiente ›', paginaActual === totalPaginas);
            html += `</ul></nav>`;
            contenedor.innerHTML = html;

            contenedor.querySelectorAll('.page-link[data-pagina]').forEach(btn => {
                btn.addEventListener('click', () => {
                    mostrarPagina(parseInt(btn.dataset.pagina, 10));
                });
            });
        }

        function botonPagina(pagina, texto, deshabilitado) {
            return `<li class="page-item ${deshabilitado ? 'disabled' : ''}">
                        <button class="page-link" data-pagina="${pagina}">${texto}</button>
                    </li>`;
        }

        mostrarPagina(1);

        tablasPaginadas.push({
            mostrarTodas: () => filas.forEach(f => f.style.display = ''),
            restaurar: () => mostrarPagina(paginaActual)
        });
    }

    paginarTabla('tbodyResumenProformas', 'paginacionResumenProformas');
    paginarTabla('tbodyResumenFacturas', 'paginacionResumenFacturas');

    window.exportarResumenPDF = function () {
        tablasPaginadas.forEach(t => t.mostrarTodas());
        window.print();
        setTimeout(() => tablasPaginadas.forEach(t => t.restaurar()), 400);
    };
});
</script>