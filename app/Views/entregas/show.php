<?php
$e = $entrega;
$diasRecepcionEntrega = days_between($e['fecha_entrega_factura'], $e['fecha_entrega_dueno']);
$diasPasarFactura = days_between($e['fecha_entrega_dueno'], $e['fecha_solicitud_revision_pago']);
$diasPasarFacturaEnCurso = $e['fecha_solicitud_revision_pago'] === null ? days_since($e['fecha_entrega_dueno']) : null;
?>

<div class="container-fluid px-0">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h4 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-truck me-2"></i>Entrega de factura #<?= e($e['n_factura']) ?>
                </h4>
                <span class="badge bg-primary-subtle text-primary fw-normal px-3 py-2">
                    <i class="bi bi-building me-1"></i><?= e($e['proveedor_nombre'] ?? '—') ?>
                </span>
                <span class="badge bg-info-subtle text-info fw-normal px-3 py-2">
                    <i class="bi bi-upc-scan me-1"></i>OCE <?= e($e['n_oce_interna'] ?? '—') ?>
                </span>

            </div>
            <p class="text-muted small mb-0">
                <i class="bi bi-file-earmark-check me-1"></i>Proforma <?= e($e['n_proforma'] ?? '—') ?>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
            <a href="<?= base_url('/entregas/' . $e['id'] . '/editar') ?>" class="btn btn-primary rounded-pill px-3">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <form method="POST" action="<?= base_url('/entregas/' . $e['id'] . '/eliminar') ?>"
                  onsubmit="return confirm('¿Eliminar este registro de entrega?');"
                  class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
            <a href="<?= base_url('/entregas') ?>" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Grid principal -->
    <div class="row g-4">
        <!-- Columna izquierda: Detalle y documento -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Información detallada
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex flex-column gap-2">
                                <span class="text-muted small">Factura entregada por Ares Sun</span>
                                <span class="fw-semibold"><?= fmt_date($e['fecha_entrega_factura']) ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column gap-2">
                                <span class="text-muted small">Entregada al dueño</span>
                                <span class="fw-semibold"><?= fmt_date($e['fecha_entrega_dueno']) ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column gap-2">
                                <span class="text-muted small">Solicitud de revisión y pago</span>
                                <span class="fw-semibold"><?= fmt_date($e['fecha_solicitud_revision_pago']) ?></span>
                            </div>
                        </div>

                        <!-- Tiempo 1: recepción -> entrega al dueño -->
                        <div class="col-12">
                            <div class="bg-light rounded-3 p-3 mt-1">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-clock-history text-primary" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <span class="text-muted small">Tiempo entre recepción (Ares Sun) y entrega al dueño:</span>
                                        <?php if ($diasRecepcionEntrega !== null): ?>
                                            <span class="badge <?= $diasRecepcionEntrega > 8 ? 'bg-danger' : 'bg-success' ?> fw-normal px-3 py-2">
                                                <i class="bi bi-check-circle me-1"></i><?= $diasRecepcionEntrega ?> días
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary fw-normal px-3 py-2">Sin fechas suficientes</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tiempo 2: para pasar la factura (regla de los 8 días) -->
                        <div class="col-12">
                            <div class="bg-light rounded-3 p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-hourglass-split text-primary" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <span class="text-muted small">Total tiempo para pasar la factura:</span>
                                        <?php if ($diasPasarFactura !== null): ?>
                                            <span class="badge <?= $diasPasarFactura > 8 ? 'bg-danger' : 'bg-success' ?> fw-normal px-3 py-2">
                                                <i class="bi bi-check-circle me-1"></i><?= $diasPasarFactura ?> días
                                            </span>
                                            <?php if ($diasPasarFactura > 8): ?>
                                                <div class="text-danger small mt-2"><i class="bi bi-exclamation-triangle me-1"></i>Superó el máximo de 8 días establecido.</div>
                                            <?php endif; ?>
                                        <?php elseif ($diasPasarFacturaEnCurso !== null): ?>
                                            <span class="badge <?= $diasPasarFacturaEnCurso >= 8 ? 'bg-danger' : 'bg-warning text-dark' ?> fw-normal px-3 py-2">
                                                <i class="bi bi-hourglass-split me-1"></i><?= $diasPasarFacturaEnCurso ?> días transcurridos (aún sin solicitar revisión)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary fw-normal px-3 py-2">Sin fechas suficientes</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($e['comentario'])): ?>
                            <div class="col-12">
                                <hr>
                                <div class="d-flex gap-2">
                                    <i class="bi bi-chat-dots text-primary mt-1"></i>
                                    <div>
                                        <span class="text-muted small d-block">Comentario</span>
                                        <p class="mb-0"><?= nl2br(e($e['comentario'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Documento escaneado -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Documento escaneado
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($e['documento_pdf'])): ?>
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 2rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold"><?= e($e['documento_pdf']) ?></div>
                                <small class="text-muted">Documento PDF adjunto</small>
                            </div>
                            <a href="<?= base_url('uploads/entregas/' . e($e['documento_pdf'])) ?>"
                               target="_blank"
                               class="btn btn-danger rounded-pill px-4">
                                <i class="bi bi-eye me-1"></i> Ver PDF
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-pdf text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2">No hay documento adjunto</p>
                            <a href="<?= base_url('/entregas/' . $e['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="bi bi-upload me-1"></i> Subir documento
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: relaciones e historial -->
        <div class="col-lg-5">
            <!-- Factura relacionada -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-receipt me-2 text-primary"></i>Factura relacionada
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">
                                <?= !empty($e['n_factura']) ? 'Factura ' . e($e['n_factura']) : 'OCE ' . e($e['n_oce_interna'] ?? '—') ?>
                            </div>
                            <small class="text-muted">OCE <?= e($e['n_oce_interna'] ?? '—') ?> · Proforma <?= e($e['n_proforma'] ?? '—') ?></small>
                        </div>
                        <a href="<?= base_url('/facturas/' . $e['factura_id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>

            <!-- Historial -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>Historial de cambios
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($historial)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-clock text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0 mt-2">Sin movimientos registrados</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($historial as $h): ?>
                                <div class="timeline-item pb-3">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                                <i class="bi bi-arrow-right-circle text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small"><?= e($h['accion']) ?></div>
                                            <div class="text-muted small">
                                                <i class="bi bi-person me-1"></i><?= e($h['usuario_nombre'] ?? 'Sistema') ?>
                                                <span class="mx-1">·</span>
                                                <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($h['creado_en'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 12px !important;
        overflow: hidden;
        transition: box-shadow 0.2s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }

    .card-header {
        padding: 1rem 1.25rem 0.5rem 1.25rem;
        background-color: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .card-body {
        padding: 1.25rem;
    }

    .badge {
        font-weight: 500;
        border-radius: 50px;
    }

    .btn.rounded-pill {
        border-radius: 50px !important;
    }

    .timeline .timeline-item:last-child {
        padding-bottom: 0 !important;
    }

    .timeline .timeline-item .bg-primary.bg-opacity-10 {
        background-color: rgba(13, 110, 253, 0.1) !important;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .d-flex.flex-wrap.gap-2 {
            gap: 0.5rem !important;
        }

        .btn.rounded-pill {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            font-size: 0.875rem;
        }
    }
</style>