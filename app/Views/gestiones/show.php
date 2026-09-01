<?php
use Core\Auth;
$g = $gestion;
$dias = days_between($g['fecha_finalizacion_trabajo'], $g['fecha_revision_cotizacion']);
$diasEnCurso = $g['fecha_revision_cotizacion'] === null ? days_since($g['fecha_finalizacion_trabajo']) : null;
$totalValor = array_sum(array_map(fn($t) => (float) ($t['valor'] ?? 0), $trabajos));
?>

<div class="container-fluid px-0">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h4 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-clipboard-check me-2"></i>Gestión #<?= (int) $g['id'] ?>
                </h4>
                <span class="badge bg-primary-subtle text-primary fw-medium px-3 py-2">
                    <i class="bi bi-building me-1"></i><?= e($g['proveedor_nombre'] ?? '—') ?>
                </span>
                <?php if (!empty($g['n_cotizacion'])): ?>
                    <span class="badge bg-info-subtle text-info-emphasis fw-medium px-3 py-2">
                        <i class="bi bi-file-text me-1"></i>Cotización <?= e($g['n_cotizacion']) ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary fw-medium px-3 py-2">
                        <i class="bi bi-calendar-month me-1"></i>Mensualidad
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-muted small mb-0">
                <i class="bi bi-calendar3 me-1"></i>
                Creado: <?= fmt_date($g['creado_en'] ?? date('Y-m-d')) ?>
                <?php if (!empty($g['actualizado_en'])): ?>
                    · Actualizado: <?= fmt_date($g['actualizado_en']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (Auth::can('gestiones.editar')): ?>
            <a href="<?= base_url('/gestiones/' . $g['id'] . '/editar') ?>" class="btn btn-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <?php endif; ?>
            <?php if (Auth::can('gestiones.eliminar')): ?>
            <form method="POST" action="<?= base_url('/gestiones/' . $g['id'] . '/eliminar') ?>" 
                  onsubmit="return confirm('¿Eliminar esta gestión y todos sus trabajos? Esta acción no se puede deshacer.');" 
                  class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
            <?php endif; ?>
            <a href="<?= base_url('/gestiones') ?>" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Grid principal -->
    <div class="row g-4">
        <!-- Columna izquierda: Detalle y documento -->
        <div class="col-lg-7">
            <!-- Tarjeta de detalle -->
            <div class="card detail-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Información detallada
                    </h6>
                    <span class="badge bg-light text-muted border">Detalles</span>
                </div>
                <div class="card-body">
                    <!-- Grid ordenado de 6 campos -->
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-person me-1"></i> Solicitado por</span>
                                <span class="info-value"><?= e($g['solicitado_por'] ?? '—') ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-person-check me-1"></i> Aprobado por</span>
                                <span class="info-value"><?= e($g['aprobado_por'] ?? '—') ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-hash me-1"></i> N° Cotización</span>
                                <span class="info-value"><?= e($g['n_cotizacion'] ?? '—') ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-calendar-check me-1"></i> Aprobación ACHSA</span>
                                <span class="info-value"><?= fmt_date($g['fecha_aprobacion_trabajo']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-calendar2-check me-1"></i> Finalización HELIOS</span>
                                <span class="info-value"><?= fmt_date($g['fecha_finalizacion_trabajo']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-calendar-event me-1"></i> Revisión facturar</span>
                                <span class="info-value"><?= fmt_date($g['fecha_revision_cotizacion']) ?></span>
                            </div>
                        </div>

                        <!-- Banner de tiempo transcurrido -->
                        <div class="col-12 mt-3">
                            <?php if ($dias !== null): ?>
                                <div class="info-banner <?= $dias > 15 ? 'bg-danger bg-opacity-10 text-danger border border-danger-subtle' : 'bg-success bg-opacity-10 text-success border border-success-subtle' ?>">
                                    <i class="bi <?= $dias > 15 ? 'bi-exclamation-triangle-fill text-danger' : 'bi-check-circle-fill text-success' ?> fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Tiempo transcurrido (Finalizado → Revisado):</div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge <?= $dias > 15 ? 'bg-danger' : 'bg-success' ?> px-3 py-1 fs-6">
                                                <?= $dias ?> días
                                            </span>
                                            <span class="small text-muted"><?= $dias > 15 ? 'Superó el plazo esperado de 15 días' : 'Dentro del plazo establecido' ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($diasEnCurso !== null): ?>
                                <div class="info-banner <?= $diasEnCurso > 15 ? 'bg-danger bg-opacity-10 text-danger border border-danger-subtle' : 'bg-warning bg-opacity-10 text-dark border border-warning-subtle' ?>">
                                    <i class="bi bi-hourglass-split <?= $diasEnCurso > 15 ? 'text-danger' : 'text-warning-emphasis' ?> fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Tiempo en curso sin revisar:</div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge <?= $diasEnCurso > 15 ? 'bg-danger' : 'bg-warning text-dark' ?> px-3 py-1 fs-6">
                                                <?= $diasEnCurso ?> días transcurridos
                                            </span>
                                            <span class="small text-muted">Aún sin fecha de revisión para facturar</span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="info-banner bg-light text-secondary border">
                                    <i class="bi bi-info-circle text-muted fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Tiempo transcurrido:</div>
                                        <span class="small text-muted">Pendiente de registrar fecha de finalización.</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Comentario -->
                        <?php if (!empty($g['comentario'])): ?>
                            <div class="col-12 mt-2">
                                <div class="comment-box">
                                    <span class="info-label mb-1 text-primary"><i class="bi bi-chat-left-text me-1"></i> Comentario / Observaciones</span>
                                    <p class="mb-0 text-dark small"><?= nl2br(e($g['comentario'])) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de documento -->
            <div class="card detail-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Documento adjunto
                    </h6>
                    <span class="badge bg-light text-muted border">Archivo</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($g['documento_pdf'])): ?>
                        <div class="doc-preview-box">
                            <div class="doc-icon-wrap bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-truncate text-dark" title="<?= e($g['documento_pdf']) ?>">
                                    <?= e($g['documento_pdf']) ?>
                                </div>
                                <small class="text-muted d-block">Documento PDF adjunto a la gestión</small>
                            </div>
                            <div class="flex-shrink-0 d-flex gap-2">
                                <a href="<?= base_url('uploads/gestiones/' . e($g['documento_pdf'])) ?>" 
                                   target="_blank" 
                                   class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">
                                    <i class="bi bi-eye me-1"></i> Ver documento
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-arrow-up text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mb-2 small mt-2">No hay documento adjunto para esta gestión.</p>
                            <?php if (Auth::can('gestiones.editar')): ?>
                            <a href="<?= base_url('/gestiones/' . $g['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-upload me-1"></i> Subir documento
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Trabajos y historial -->
        <div class="col-lg-5">
            <!-- Tarjeta de trabajos -->
            <div class="card detail-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-list-task me-2 text-primary"></i>Trabajos
                        <span class="badge bg-primary-subtle text-primary fw-medium ms-1"><?= count($trabajos) ?></span>
                    </h6>
                    <span class="fw-bold text-success fs-6">
                        <i class="bi bi-cash me-1"></i><?= fmt_money($totalValor) ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($trabajos)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mb-2 small mt-2">Sin trabajos registrados en esta gestión.</p>
                            <?php if (Auth::can('gestiones.editar')): ?>
                            <a href="<?= base_url('/gestiones/' . $g['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-plus-lg me-1"></i> Agregar trabajos
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <?php $indexTrabajo = 1; foreach ($trabajos as $t): ?>
                                <div class="trabajo-item-card">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="d-flex align-items-start gap-2">
                                                <span class="badge bg-secondary-subtle text-secondary small flex-shrink-0 mt-1">#<?= $indexTrabajo++ ?></span>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <span class="fw-semibold text-dark small text-break d-inline-block"><?= e($t['descripcion']) ?></span>
                                                    <div class="mt-1">
                                                        <span class="text-success fw-bold small"><?= fmt_money($t['valor']) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 text-end ps-2">
                                            <?php if ($t['proforma_id']): ?>
                                                <a href="<?= base_url('/proformas/' . $t['proforma_id']) ?>" 
                                                   class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none px-2 py-1 small" 
                                                   title="Ver proforma asignada">
                                                    <i class="bi bi-file-earmark-check me-1"></i><?= e($t['n_proforma'] ?: ('#' . $t['proforma_id'])) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 small">
                                                    <i class="bi bi-clock me-1"></i>Sin asignar
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (Auth::can('gestiones.editar')): ?>
                        <div class="text-center pt-2 border-top">
                            <a href="<?= base_url('/gestiones/' . $g['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                <i class="bi bi-pencil me-1"></i> Gestionar trabajos
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjeta de historial -->
            <div class="card detail-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>Historial de cambios
                    </h6>
                    <span class="badge bg-light text-muted border">Auditoría</span>
                </div>
                <div class="card-body">
                    <?php if (empty($historial)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-clock text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mb-0 small mt-2">Sin movimientos registrados</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline-modern">
                            <?php foreach ($historial as $h): ?>
                                <div class="timeline-modern-item">
                                    <div class="timeline-modern-node">
                                        <i class="bi bi-arrow-right-short"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small text-dark"><?= e($h['accion']) ?></div>
                                        <div class="text-muted small mt-1">
                                            <i class="bi bi-person me-1"></i><?= e($h['usuario_nombre'] ?? 'Sistema') ?>
                                            <span class="mx-1">·</span>
                                            <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($h['creado_en'])) ?>
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