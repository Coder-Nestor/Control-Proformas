<?php
$o = $oc;
$badgeClass = ['correcta' => 'success', 'pendiente' => 'warning', 'con_problema' => 'danger'];
$estados = \App\Models\OrdenCompra::ESTADOS;
$dias = days_between($o['fecha_revision_proforma'], $o['fecha_envio_oce']);
$diasEnCurso = $o['fecha_envio_oce'] === null ? days_since($o['fecha_revision_proforma']) : null;
?>

<div class="container-fluid px-0">
    <!-- Encabezado mejorado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-cart-check me-2"></i>Orden de compra #<?= (int) $o['id'] ?>
                </h4>
                <span class="badge bg-primary-subtle text-primary fw-normal px-3 py-2">
                    <i class="bi bi-building me-1"></i><?= e($o['proveedor_nombre'] ?? '—') ?>
                </span>
                <?php if (!empty($o['n_oce_interna'])): ?>
                    <span class="badge bg-info-subtle text-info fw-normal px-3 py-2">
                        <i class="bi bi-file-text me-1"></i>OCE <?= e($o['n_oce_interna']) ?>
                    </span>
                <?php endif; ?>
                <span class="badge bg-success-subtle text-success fw-normal px-3 py-2">
                    <i class="bi bi-file-earmark me-1"></i><?= e($o['n_proforma'] ?: '#' . $o['proforma_id']) ?>
                </span>
            </div>
            <p class="text-muted small mb-0">
                <i class="bi bi-calendar3 me-1"></i>
                Creado: <?= fmt_date($o['creado_en'] ?? date('Y-m-d')) ?>
                <?php if (!empty($o['actualizado_en'])): ?>
                    · Actualizado: <?= fmt_date($o['actualizado_en']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
            <a href="<?= base_url('/ordenes/' . $o['id'] . '/editar') ?>" class="btn btn-primary rounded-pill px-3">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <form method="POST" action="<?= base_url('/ordenes/' . $o['id'] . '/eliminar') ?>" 
                  onsubmit="return confirm('¿Eliminar esta orden de compra?');" 
                  class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
            <a href="<?= base_url('/ordenes') ?>" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Grid principal -->
    <div class="row g-4">
        <!-- Columna izquierda: Detalle y documento -->
        <div class="col-lg-7">
            <!-- Tarjeta de detalle -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Información detallada
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Columna izquierda de datos -->
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">N° OCE e interna</span>
                                    <span class="fw-semibold"><?= e($o['n_oce_interna'] ?? '—') ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">Fecha de envío de OCE</span>
                                    <span class="fw-semibold"><?= fmt_date($o['fecha_envio_oce']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">Valor proforma</span>
                                    <span class="fw-semibold text-success"><?= fmt_money($o['valor_proforma']) ?></span>
                                </div>
                            </div>
                        </div>
                        <!-- Columna derecha de datos -->
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">Estado</span>
                                    <span class="badge text-bg-<?= $badgeClass[$o['estado']] ?? 'secondary' ?>"><?= e($estados[$o['estado']]) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">Proforma</span>
                                    <span class="fw-semibold"><?= e($o['n_proforma'] ?: '#' . $o['proforma_id']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">Proveedor</span>
                                    <span class="fw-semibold"><?= e($o['proveedor_nombre'] ?? '—') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tiempo transcurrido (full width) -->
                        <div class="col-12">
                            <div class="bg-light rounded-3 p-3 mt-1">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-clock-history text-primary" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <span class="text-muted small">Tiempo entre revisión de proforma y envío de OCE:</span>
                                        <?php if ($dias !== null): ?>
                                            <span class="badge <?= $dias > 8 ? 'bg-danger' : 'bg-success' ?> fw-normal px-3 py-2">
                                                <i class="bi bi-check-circle me-1"></i><?= $dias ?> días
                                            </span>
                                        <?php elseif ($diasEnCurso !== null): ?>
                                            <span class="badge <?= $diasEnCurso > 8 ? 'bg-warning text-dark' : 'bg-warning text-dark' ?> fw-normal px-3 py-2">
                                                <i class="bi bi-hourglass-split me-1"></i><?= $diasEnCurso ?> días sin enviar
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary fw-normal px-3 py-2">Sin fecha de revisión</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comentario -->
                        <?php if (!empty($o['comentario'])): ?>
                            <div class="col-12">
                                <hr>
                                <div class="d-flex gap-2">
                                    <i class="bi bi-chat-dots text-primary mt-1"></i>
                                    <div>
                                        <span class="text-muted small d-block">Comentario</span>
                                        <p class="mb-0"><?= nl2br(e($o['comentario'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de documento -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Documento escaneado
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($o['documento_pdf'])): ?>
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 2rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold"><?= e($o['documento_pdf']) ?></div>
                                <small class="text-muted">Documento PDF adjunto</small>
                            </div>
                            <a href="<?= base_url('uploads/ordenes_compra/' . e($o['documento_pdf'])) ?>" 
                               target="_blank" 
                               class="btn btn-danger rounded-pill px-4">
                                <i class="bi bi-eye me-1"></i> Ver PDF
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-pdf text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2">No hay documento adjunto</p>
                            <a href="<?= base_url('/ordenes/' . $o['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="bi bi-upload me-1"></i> Subir documento
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjeta de factura -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-receipt me-2 text-primary"></i>Factura
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($factura): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                            <div>
                                <div class="fw-semibold">Factura #<?= (int) $factura['id'] ?></div>
                                <div class="mt-1">
                                    <span class="badge text-bg-<?= $badgeClass[$factura['estado']] ?? 'secondary' ?>">
                                        <?= e(\App\Models\Factura::ESTADOS[$factura['estado']] ?? $factura['estado']) ?>
                                    </span>
                                    <small class="text-muted ms-2">
                                        <i class="bi bi-calendar3 me-1"></i><?= fmt_date($factura['fecha_entrega_factura']) ?>
                                    </small>
                                </div>
                            </div>
                            <a href="<?= base_url('/facturas/' . $factura['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-eye me-1"></i> Ver detalle
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-receipt text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-2">Esta orden de compra todavía no tiene una factura generada.</p>
                            <a href="<?= base_url('/facturas/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-plus-lg me-1"></i> Registrar factura
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Proforma relacionada y historial -->
        <div class="col-lg-5">
            <!-- Tarjeta de proforma relacionada -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i>Proforma relacionada
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                        <div>
                            <div class="fw-semibold"><?= e($o['n_proforma'] ?: '#' . $o['proforma_id']) ?></div>
                            <small class="text-muted">
                                <i class="bi bi-building me-1"></i><?= e($o['proveedor_nombre'] ?? '—') ?>
                            </small>
                        </div>
                        <a href="<?= base_url('/proformas/' . $o['proforma_id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-eye me-1"></i> Ver detalle
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de historial -->
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
                            <?php $historialCount = count($historial); ?>
                            <?php $historialIndex = 0; ?>
                            <?php foreach ($historial as $h): ?>
                                <div class="timeline-item pb-3 <?= $historialIndex < $historialCount - 1 ? 'border-bottom' : '' ?>">
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
                                <?php $historialIndex++; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos idénticos al show de gestiones */
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
    
    .bg-primary-subtle {
        background-color: #cfe2ff !important;
    }
    
    .bg-info-subtle {
        background-color: #cff4fc !important;
    }
    
    .bg-success-subtle {
        background-color: #d1e7dd !important;
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