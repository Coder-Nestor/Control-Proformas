<?php
use Core\Auth;
$p = $proforma;
$dias = days_between($p['fecha_solicitud'], $p['fecha_revision_proforma']);
$diasEnCurso = $p['fecha_revision_proforma'] === null ? days_since($p['fecha_solicitud']) : null;
$totalTrabajos = array_sum(array_map(fn($t) => (float) ($t['valor'] ?? 0), $trabajos));
?>

<div class="container-fluid px-0">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h4 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-file-earmark-text me-2"></i>Proforma <?= e($p['n_proforma'] ?: '#' . $p['id']) ?>
                </h4>
                <span class="badge bg-primary-subtle text-primary fw-medium px-3 py-2">
                    <i class="bi bi-building me-1"></i><?= e($p['proveedor_nombre'] ?? '—') ?>
                </span>
                <?php if (empty($p['n_cotizacion'])): ?>
                    <span class="badge bg-secondary-subtle text-secondary fw-medium px-3 py-2">
                        <i class="bi bi-calendar-month me-1"></i>Mensualidad
                    </span>
                <?php else: ?>
                    <span class="badge bg-info-subtle text-info-emphasis fw-medium px-3 py-2">
                        <i class="bi bi-file-text me-1"></i>Cotización <?= e($p['n_cotizacion']) ?>
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
        <div class="d-flex flex-wrap gap-2">
            <?php if (Auth::can('proformas.editar')): ?>
            <a href="<?= base_url('/proformas/' . $p['id'] . '/editar') ?>" class="btn btn-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <?php endif; ?>
            <?php if (Auth::can('proformas.eliminar')): ?>
            <form method="POST" action="<?= base_url('/proformas/' . $p['id'] . '/eliminar') ?>" 
                  onsubmit="return confirm('¿Eliminar esta proforma? Los trabajos asociados quedarán sin asignar y la orden de compra vinculada (si existe) se eliminará también. ¿Continuar?');" 
                  class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
            <?php endif; ?>
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
                                <span class="info-value"><?= e($p['solicitado_por'] ?? '—') ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-calendar-plus me-1"></i> Fecha solicitud</span>
                                <span class="info-value"><?= fmt_date($p['fecha_solicitud']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-cash me-1"></i> Valor proforma</span>
                                <span class="info-value text-success"><?= fmt_money($p['valor_proforma']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-check2-circle me-1"></i> Fecha revisión</span>
                                <span class="info-value"><?= fmt_date($p['fecha_revision_proforma']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-tag me-1"></i> Tipo</span>
                                <span class="info-value"><?= empty($p['n_cotizacion']) ? 'Mensualidad' : 'Cotización ' . e($p['n_cotizacion']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-briefcase me-1"></i> Trabajo</span>
                                <span class="info-value"><?= e($p['trabajo'] ?? '—') ?></span>
                            </div>
                        </div>

                        <!-- Banner de tiempo transcurrido -->
                        <div class="col-12 mt-3">
                            <?php if ($dias !== null): ?>
                                <div class="info-banner <?= $dias > 15 ? 'bg-danger bg-opacity-10 text-danger border border-danger-subtle' : 'bg-success bg-opacity-10 text-success border border-success-subtle' ?>">
                                    <i class="bi <?= $dias > 15 ? 'bi-exclamation-triangle-fill text-danger' : 'bi-check-circle-fill text-success' ?> fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Tiempo transcurrido (Solicitud → Revisión):</div>
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
                                            <span class="small text-muted">Aún sin fecha de revisión de proforma</span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="info-banner bg-light text-secondary border">
                                    <i class="bi bi-info-circle text-muted fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Tiempo transcurrido:</div>
                                        <span class="small text-muted">Pendiente de registrar fecha de solicitud.</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Comentario -->
                        <?php if (!empty($p['comentario'])): ?>
                            <div class="col-12 mt-2">
                                <div class="comment-box">
                                    <span class="info-label mb-1 text-primary"><i class="bi bi-chat-left-text me-1"></i> Comentario / Observaciones</span>
                                    <p class="mb-0 text-dark small"><?= nl2br(e($p['comentario'])) ?></p>
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
                    <?php if (!empty($p['documento_pdf'])): ?>
                        <div class="doc-preview-box">
                            <div class="doc-icon-wrap bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-truncate text-dark" title="<?= e($p['documento_pdf']) ?>">
                                    <?= e($p['documento_pdf']) ?>
                                </div>
                                <small class="text-muted d-block">Documento PDF adjunto a la proforma</small>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="<?= base_url('uploads/proformas/' . e($p['documento_pdf'])) ?>" 
                                   target="_blank" 
                                   class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">
                                    <i class="bi bi-eye me-1"></i> Ver documento
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-arrow-up text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mb-2 small mt-2">No hay documento adjunto para esta proforma.</p>
                            <?php if (Auth::can('proformas.editar')): ?>
                            <a href="<?= base_url('/proformas/' . $p['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-upload me-1"></i> Subir documento
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjeta de orden de compra -->
            <div class="card detail-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-cart-check me-2 text-primary"></i>Orden de compra vinculada
                    </h6>
                    <span class="badge bg-light text-muted border">Proceso</span>
                </div>
                <div class="card-body">
                    <?php if ($oc): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                    <i class="bi bi-cart-check fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">OCE <?= e($oc['n_oce_interna'] ?? '#' . $oc['id']) ?></div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>Enviada: <?= fmt_date($oc['fecha_envio_oce']) ?>
                                    </small>
                                </div>
                            </div>
                            <a href="<?= base_url('/ordenes/' . $oc['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-eye me-1"></i> Ver detalle
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-cart-x text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mb-2 small mt-2">Esta proforma todavía no tiene una orden de compra generada.</p>
                            <?php if (Auth::can('ordenes.crear')): ?>
                            <a href="<?= base_url('/ordenes/crear?proforma_id=' . (int) $p['id']) ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                <i class="bi bi-plus-lg me-1"></i> Crear orden de compra
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Trabajos y historial -->
        <div class="col-lg-5">
            <!-- Tarjeta de trabajos incluidos -->
            <div class="card detail-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-list-task me-2 text-primary"></i>Trabajos incluidos
                        <span class="badge bg-primary-subtle text-primary fw-medium ms-1"><?= count($trabajos) ?></span>
                    </h6>
                    <span class="fw-bold text-success fs-6">
                        <i class="bi bi-cash me-1"></i><?= fmt_money($totalTrabajos) ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($trabajos)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mb-1 small mt-2">Sin trabajos asignados a esta proforma.</p>
                            <small class="text-muted d-block">Ve a la Gestión correspondiente y selecciona esta proforma en el trabajo.</small>
                        </div>
                    <?php else: ?>
                        <div class="mb-2">
                            <?php $idxT = 1; foreach ($trabajos as $t): ?>
                                <div class="trabajo-item-card">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="d-flex align-items-start gap-2">
                                                <span class="badge bg-secondary-subtle text-secondary small flex-shrink-0 mt-1">#<?= $idxT++ ?></span>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <a href="<?= base_url('/gestiones/' . $t['gestion_id']) ?>" class="text-decoration-none fw-semibold text-dark small text-break d-inline-block" title="Ver gestión">
                                                        <?= e($t['descripcion']) ?>
                                                    </a>
                                                    <div class="text-muted small mt-1 text-break">
                                                        <?= e($t['proveedor_nombre'] ?? '—') ?> · Cot. <?= e($t['n_cotizacion'] ?? '—') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 text-end ps-2">
                                            <span class="text-success fw-bold small d-block"><?= fmt_money($t['valor']) ?></span>
                                            <a href="<?= base_url('/gestiones/' . $t['gestion_id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0 mt-1" style="font-size: 0.75rem;" title="Ver gestión">
                                                <i class="bi bi-eye"></i> Gestión
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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