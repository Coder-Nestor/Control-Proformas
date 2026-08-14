<?php
$p = $proforma;
$dias = days_between($p['fecha_solicitud'], $p['fecha_revision_proforma']);
$diasEnCurso = $p['fecha_revision_proforma'] === null ? days_since($p['fecha_solicitud']) : null;
?>

<div class="container-fluid px-0">
    <!-- Encabezado mejorado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-file-earmark-text me-2"></i>Proforma <?= e($p['n_proforma'] ?: '#' . $p['id']) ?>
                </h4>
                <span class="badge bg-primary-subtle text-primary fw-normal px-3 py-2">
                    <i class="bi bi-building me-1"></i><?= e($p['proveedor_nombre'] ?? '—') ?>
                </span>
                <?php if (empty($p['n_cotizacion'])): ?>
                    <span class="badge bg-info-subtle text-info fw-normal px-3 py-2">
                        <i class="bi bi-calendar-month me-1"></i>Mensualidad
                    </span>
                <?php else: ?>
                    <span class="badge bg-info-subtle text-info fw-normal px-3 py-2">
                        <i class="bi bi-file-text me-1"></i><?= e($p['n_cotizacion']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-muted small mb-0">
                <i class="bi bi-calendar3 me-1"></i>
                Creado: <?= fmt_date($p['creado_en'] ?? date('Y-m-d')) ?>
                <?php if (!empty($p['actualizado_en'])): ?>
                    · Actualizado: <?= fmt_date($p['actualizado_en']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
            <a href="<?= base_url('/proformas/' . $p['id'] . '/editar') ?>" class="btn btn-primary rounded-pill px-3">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <form method="POST" action="<?= base_url('/proformas/' . $p['id'] . '/eliminar') ?>" 
                  onsubmit="return confirm('¿Eliminar esta proforma? Los trabajos asociados quedarán sin asignar y la orden de compra vinculada (si existe) se eliminará también. ¿Continuar?');" 
                  class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
            <a href="<?= base_url('/proformas') ?>" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Grid principal -->
    <div class="row g-4">
        <!-- Columna izquierda: Detalle, documento y OC -->
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
                                    <span class="text-muted small">Solicitado por</span>
                                    <span class="fw-semibold"><?= e($p['solicitado_por'] ?? '—') ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">Fecha de solicitud</span>
                                    <span class="fw-semibold"><?= fmt_date($p['fecha_solicitud']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">Valor proforma</span>
                                    <span class="fw-semibold text-success"><?= fmt_money($p['valor_proforma']) ?></span>
                                </div>
                            </div>
                        </div>
                        <!-- Columna derecha de datos -->
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">Fecha de revisión</span>
                                    <span class="fw-semibold"><?= fmt_date($p['fecha_revision_proforma']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">Tipo</span>
                                    <span class="fw-semibold"><?= empty($p['n_cotizacion']) ? 'Mensualidad' : 'Cotización ' . e($p['n_cotizacion']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-1">
                                    <span class="text-muted small">Trabajo</span>
                                    <span class="fw-semibold"><?= e($p['trabajo'] ?? '—') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tiempo transcurrido (full width) -->
                        <div class="col-12">
                            <div class="bg-light rounded-3 p-3 mt-1">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-clock-history text-primary" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <span class="text-muted small">Tiempo transcurrido:</span>
                                        <?php if ($dias !== null): ?>
                                            <span class="badge <?= $dias > 15 ? 'bg-danger' : 'bg-success' ?> fw-normal px-3 py-2">
                                                <i class="bi bi-check-circle me-1"></i><?= $dias ?> días (solicitud → revisión)
                                            </span>
                                        <?php elseif ($diasEnCurso !== null): ?>
                                            <span class="badge <?= $diasEnCurso > 15 ? 'bg-warning text-dark' : 'bg-warning text-dark' ?> fw-normal px-3 py-2">
                                                <i class="bi bi-hourglass-split me-1"></i><?= $diasEnCurso ?> días sin revisar
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary fw-normal px-3 py-2">Sin fecha de solicitud</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comentario -->
                        <?php if (!empty($p['comentario'])): ?>
                            <div class="col-12">
                                <hr>
                                <div class="d-flex gap-2">
                                    <i class="bi bi-chat-dots text-primary mt-1"></i>
                                    <div>
                                        <span class="text-muted small d-block">Comentario</span>
                                        <p class="mb-0"><?= nl2br(e($p['comentario'])) ?></p>
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
                    <?php if (!empty($p['documento_pdf'])): ?>
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 2rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold"><?= e($p['documento_pdf']) ?></div>
                                <small class="text-muted">Documento PDF adjunto</small>
                            </div>
                            <a href="<?= base_url('uploads/proformas/' . e($p['documento_pdf'])) ?>" 
                               target="_blank" 
                               class="btn btn-danger rounded-pill px-4">
                                <i class="bi bi-eye me-1"></i> Ver PDF
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-pdf text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2">No hay documento adjunto</p>
                            <a href="<?= base_url('/proformas/' . $p['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="bi bi-upload me-1"></i> Subir documento
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjeta de orden de compra -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-cart-check me-2 text-primary"></i>Orden de compra
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($oc): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                            <div>
                                <div class="fw-semibold">N° OCE: <?= e($oc['n_oce_interna'] ?? '—') ?></div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>Enviada: <?= fmt_date($oc['fecha_envio_oce']) ?>
                                </small>
                            </div>
                            <a href="<?= base_url('/ordenes/' . $oc['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-eye me-1"></i> Ver detalle
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-cart-x text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-2">Esta proforma todavía no tiene una orden de compra generada.</p>
                            <a href="<?= base_url('/ordenes/crear?proforma_id=' . (int) $p['id']) ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-plus-lg me-1"></i> Crear orden de compra
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Trabajos y historial -->
        <div class="col-lg-5">
            <!-- Tarjeta de trabajos -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-secondary mb-0">
                            <i class="bi bi-list-task me-2 text-primary"></i>Trabajos incluidos
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-normal ms-1"><?= count($trabajos) ?></span>
                        </h6>
                        <a href="<?= base_url('/gestiones') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-plus-lg me-1"></i> Gestiones
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($trabajos)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0 mt-2">Sin trabajos asignados</p>
                            <small class="text-muted">Ve a la Gestión correspondiente y selecciona esta proforma.</small>
                        </div>
                    <?php else: ?>
                        <?php $totalTrabajos = 0; ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($trabajos as $t): 
                                $totalTrabajos += (float) ($t['valor'] ?? 0);
                            ?>
                                <div class="list-group-item px-0 py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="flex-grow-1">
                                            <a href="<?= base_url('/gestiones/' . $t['gestion_id']) ?>" class="text-decoration-none fw-semibold">
                                                <?= e($t['descripcion']) ?>
                                            </a>
                                            <div class="text-muted small">
                                                <?= e($t['proveedor_nombre'] ?? '—') ?> · 
                                                Cot. <?= e($t['n_cotizacion'] ?? '—') ?> · 
                                                <span class="text-success fw-semibold"><?= fmt_money($t['valor']) ?></span>
                                            </div>
                                        </div>
                                        <a href="<?= base_url('/gestiones/' . $t['gestion_id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-end border-top pt-2 mt-2">
                            <span class="fw-bold text-success">
                                <i class="bi bi-cash me-1"></i>Total: <?= fmt_money($totalTrabajos) ?>
                            </span>
                        </div>
                    <?php endif; ?>
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
    
    .list-group-item {
        background-color: transparent;
        border-color: rgba(0, 0, 0, 0.05);
    }
    
    .list-group-item:last-child {
        border-bottom: 0 !important;
    }
    
    .list-group-item a {
        color: #212529;
    }
    
    .list-group-item a:hover {
        color: #0d6efd;
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