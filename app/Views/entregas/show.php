<?php use Core\Auth;
$e = $entrega;
$diasRecepcionEntrega = days_between($e['fecha_entrega_factura'], $e['fecha_entrega_dueno']);
$diasPasarFactura = days_between($e['fecha_entrega_dueno'], $e['fecha_solicitud_revision_pago']);
$diasPasarFacturaEnCurso = $e['fecha_solicitud_revision_pago'] === null ? days_since($e['fecha_entrega_dueno']) : null;
?>

<div class="container-fluid px-0">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h4 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-truck me-2"></i>Entrega de factura <?= !empty($e['n_factura']) ? '#' . e($e['n_factura']) : '' ?>
                </h4>
                <span class="badge bg-primary-subtle text-primary fw-medium px-3 py-2">
                    <i class="bi bi-building me-1"></i><?= e($e['proveedor_nombre'] ?? '—') ?>
                </span>
                <?php if (!empty($e['n_oce_interna'])): ?>
                    <span class="badge bg-info-subtle text-info-emphasis fw-medium px-3 py-2">
                        <i class="bi bi-file-text me-1"></i>OCE <?= e($e['n_oce_interna']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($e['n_proforma'])): ?>
                    <span class="badge bg-success-subtle text-success-emphasis fw-medium px-3 py-2">
                        <i class="bi bi-file-earmark me-1"></i>Proforma <?= e($e['n_proforma']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-muted small mb-0">
                <i class="bi bi-calendar3 me-1"></i>
                Creado: <?= fmt_date($e['creado_en'] ?? date('Y-m-d')) ?>
                <?php if (!empty($e['actualizado_en'])): ?>
                    · Actualizado: <?= fmt_date($e['actualizado_en']) ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if(Auth::can('entregas.editar')): ?>
            <a href="<?= base_url('/entregas/' . $e['id'] . '/editar') ?>" class="btn btn-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <?php endif; ?>
            <?php if(Auth::can('entregas.eliminar')): ?>
            <form method="POST" action="<?= base_url('/entregas/' . $e['id'] . '/eliminar') ?>"
                  onsubmit="return confirm('¿Eliminar este registro de entrega?');"
                  class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
            <?php endif; ?>
            <a href="<?= base_url('/entregas') ?>" class="btn btn-outline-secondary rounded-pill px-3">
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
                    <span class="badge bg-light text-muted border">Fechas de Entrega</span>
                </div>
                <div class="card-body">
                    <!-- Grid de fechas -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-calendar-event me-1"></i> Entrega por Proveedor</span>
                                <span class="info-value"><?= fmt_date($e['fecha_entrega_factura']) ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-person-check me-1"></i> Entregada al dueño</span>
                                <span class="info-value"><?= fmt_date($e['fecha_entrega_dueno']) ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-send-check me-1"></i> Solicitud revisión/pago</span>
                                <span class="info-value"><?= fmt_date($e['fecha_solicitud_revision_pago']) ?></span>
                            </div>
                        </div>

                        <!-- Tiempo 1: recepción -> entrega al dueño -->
                        <div class="col-12 mt-3">
                            <div class="info-banner <?= ($diasRecepcionEntrega !== null && $diasRecepcionEntrega > 8) ? 'bg-danger bg-opacity-10 text-danger border border-danger-subtle' : 'bg-light text-dark border' ?>">
                                <i class="bi bi-clock-history text-primary fs-4"></i>
                                <div>
                                    <div class="fw-semibold small">Tiempo entre recepción de factura y entrega al dueño:</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <?php if ($diasRecepcionEntrega !== null): ?>
                                            <span class="badge <?= $diasRecepcionEntrega > 8 ? 'bg-danger' : 'bg-success' ?> px-3 py-1 fs-6">
                                                <?= $diasRecepcionEntrega ?> días
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary px-3 py-1">Sin fechas suficientes</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tiempo 2: para pasar la factura (regla de los 8 días) -->
                        <div class="col-12">
                            <?php if ($diasPasarFactura !== null): ?>
                                <div class="info-banner <?= $diasPasarFactura > 8 ? 'bg-danger bg-opacity-10 text-danger border border-danger-subtle' : 'bg-success bg-opacity-10 text-success border border-success-subtle' ?>">
                                    <i class="bi <?= $diasPasarFactura > 8 ? 'bi-exclamation-triangle-fill text-danger' : 'bi-check-circle-fill text-success' ?> fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Total tiempo para pasar la factura:</div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge <?= $diasPasarFactura > 8 ? 'bg-danger' : 'bg-success' ?> px-3 py-1 fs-6">
                                                <?= $diasPasarFactura ?> días
                                            </span>
                                            <span class="small <?= $diasPasarFactura > 8 ? 'text-danger' : 'text-muted' ?>">
                                                <?= $diasPasarFactura > 8 ? 'Superó el máximo de 8 días establecido' : 'Dentro del plazo máximo de 8 días' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($diasPasarFacturaEnCurso !== null): ?>
                                <div class="info-banner <?= $diasPasarFacturaEnCurso >= 8 ? 'bg-danger bg-opacity-10 text-danger border border-danger-subtle' : 'bg-warning bg-opacity-10 text-dark border border-warning-subtle' ?>">
                                    <i class="bi bi-hourglass-split <?= $diasPasarFacturaEnCurso >= 8 ? 'text-danger' : 'text-warning-emphasis' ?> fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Tiempo transcurrido (aún sin solicitar revisión):</div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge <?= $diasPasarFacturaEnCurso >= 8 ? 'bg-danger' : 'bg-warning text-dark' ?> px-3 py-1 fs-6">
                                                <?= $diasPasarFacturaEnCurso ?> días
                                            </span>
                                            <span class="small text-muted">Aún pendiente de solicitar revisión de pago</span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="info-banner bg-light text-secondary border">
                                    <i class="bi bi-info-circle text-muted fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Total tiempo para pasar la factura:</div>
                                        <span class="small text-muted">Sin fechas suficientes registradas.</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Comentario -->
                        <?php if (!empty($e['comentario'])): ?>
                            <div class="col-12 mt-2">
                                <div class="comment-box">
                                    <span class="info-label mb-1 text-primary"><i class="bi bi-chat-left-text me-1"></i> Comentario / Observaciones</span>
                                    <p class="mb-0 text-dark small"><?= nl2br(e($e['comentario'])) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de documentos -->
            <div class="card detail-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-paperclip me-2 text-primary"></i>Documentos adjuntos
                        <?php if (!empty($documentos)): ?>
                            <span class="badge bg-primary-subtle text-primary fw-medium ms-1"><?= count($documentos) ?></span>
                        <?php endif; ?>
                    </h6>
                    <span class="badge bg-light text-muted border">Archivos</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($documentos)): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($documentos as $doc): ?>
                                <?php 
                                    $isPdf = \App\Models\Documento::esPdf($doc['mime_type'] ?? '', $doc['nombre_archivo'] ?? '');
                                    $tamanoFmt = \App\Models\Documento::formatearTamano((int)($doc['tamano_bytes'] ?? 0));
                                ?>
                                <div class="doc-preview-box">
                                    <div class="doc-icon-wrap <?= $isPdf ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary' ?>">
                                        <i class="bi <?= $isPdf ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' ?>"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-semibold text-truncate text-dark" title="<?= e($doc['nombre_original'] ?: $doc['nombre_archivo']) ?>">
                                            <?= e($doc['nombre_original'] ?: $doc['nombre_archivo']) ?>
                                        </div>
                                        <small class="text-muted d-block"><?= $tamanoFmt ?><?= !empty($doc['creado_en']) ? ' • Subido el ' . fmt_date($doc['creado_en']) : '' ?></small>
                                    </div>
                                    <div class="flex-shrink-0 d-flex gap-2">
                                        <a href="<?= base_url('uploads/entregas/' . e($doc['nombre_archivo'])) ?>" 
                                           target="_blank" 
                                           class="btn <?= $isPdf ? 'btn-danger' : 'btn-primary' ?> btn-sm rounded-pill px-3 shadow-sm">
                                            <i class="bi bi-eye me-1"></i> Ver
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($e['documento_pdf'])): ?>
                        <div class="doc-preview-box">
                            <div class="doc-icon-wrap bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-truncate text-dark" title="<?= e($e['documento_pdf']) ?>">
                                    <?= e($e['documento_pdf']) ?>
                                </div>
                                <small class="text-muted d-block">Documento adjunto a la entrega</small>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="<?= base_url('uploads/entregas/' . e($e['documento_pdf'])) ?>"
                                   target="_blank"
                                   class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">
                                    <i class="bi bi-eye me-1"></i> Ver documento
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-arrow-up text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mb-2 small mt-2">No hay documentos adjuntos para esta entrega.</p>
                            <?php if (Auth::can('entregas.editar')): ?>
                            <a href="<?= base_url('/entregas/' . $e['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-upload me-1"></i> Subir documentos
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: relaciones e historial -->
        <div class="col-lg-5">
            <!-- Factura relacionada -->
            <div class="card detail-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-receipt me-2 text-primary"></i>Factura vinculada
                    </h6>
                    <span class="badge bg-light text-muted border">Origen</span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 border">
                        <div>
                            <div class="fw-semibold text-dark">
                                <?= !empty($e['n_factura']) ? 'Factura ' . e($e['n_factura']) : 'Factura #' . (int)$e['factura_id'] ?>
                            </div>
                            <small class="text-muted d-block">OCE <?= e($e['n_oce_interna'] ?? '—') ?> · Proforma <?= e($e['n_proforma'] ?? '—') ?></small>
                            <small class="text-muted"><i class="bi bi-building me-1"></i><?= e($e['proveedor_nombre'] ?? '—') ?></small>
                        </div>
                        <a href="<?= base_url('/facturas/' . $e['factura_id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-eye me-1"></i> Ver detalle
                        </a>
                    </div>
                </div>
            </div>

            <!-- Historial -->
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