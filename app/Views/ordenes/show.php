<?php use Core\Auth;
$o = $oc;
$badgeClass = ['correcta' => 'success', 'pendiente' => 'warning', 'con_problema' => 'danger'];
$estados = \App\Models\OrdenCompra::ESTADOS;
$dias = days_between($o['fecha_revision_proforma'], $o['fecha_envio_oce']);
$diasEnCurso = $o['fecha_envio_oce'] === null ? days_since($o['fecha_revision_proforma']) : null;
?> 

<div class="container-fluid px-0">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h4 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-cart-check me-2"></i>Orden de compra #<?= (int) $o['id'] ?>
                </h4>
                <span class="badge bg-primary-subtle text-primary fw-medium px-3 py-2">
                    <i class="bi bi-building me-1"></i><?= e($o['proveedor_nombre'] ?? '—') ?>
                </span>
                <?php if (!empty($o['n_oce_interna'])): ?>
                    <span class="badge bg-info-subtle text-info-emphasis fw-medium px-3 py-2">
                        <i class="bi bi-file-text me-1"></i>OCE <?= e($o['n_oce_interna']) ?>
                    </span>
                <?php endif; ?>
                <span class="badge bg-success-subtle text-success-emphasis fw-medium px-3 py-2">
                    <i class="bi bi-file-earmark me-1"></i>Proforma <?= e($o['n_proforma'] ?: '#' . $o['proforma_id']) ?>
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
        <div class="d-flex flex-wrap gap-2">
            <?php if (Auth::can('ordenes.editar')): ?>
            <a href="<?= base_url('/ordenes/' . $o['id'] . '/editar') ?>" class="btn btn-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <?php endif; ?>
            <?php if (Auth::can('ordenes.eliminar')): ?>
            <form method="POST" action="<?= base_url('/ordenes/' . $o['id'] . '/eliminar') ?>" 
                  onsubmit="return confirm('¿Eliminar esta orden de compra?');" 
                  class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
            <?php endif; ?>
            <a href="<?= base_url('/ordenes') ?>" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Grid principal -->
    <div class="row g-4">
        <!-- Columna izquierda: Detalle, documento y Factura -->
        <div class="col-lg-7">
            <!-- Tarjeta de detalle -->
            <div class="card detail-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Información detallada
                    </h6>
                    <span class="badge text-bg-<?= $badgeClass[$o['estado']] ?? 'secondary' ?> px-3 py-1">
                        <?= e($estados[$o['estado']] ?? $o['estado']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <!-- Grid ordenado de 6 campos -->
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-hash me-1"></i> N° OCE e interna</span>
                                <span class="info-value"><?= e($o['n_oce_interna'] ?? '—') ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-send me-1"></i> Fecha envío OCE</span>
                                <span class="info-value"><?= fmt_date($o['fecha_envio_oce']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-cash me-1"></i> Valor proforma</span>
                                <span class="info-value text-success"><?= fmt_money($o['valor_proforma'] ?? 0) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-flag me-1"></i> Estado</span>
                                <div>
                                    <span class="badge text-bg-<?= $badgeClass[$o['estado']] ?? 'secondary' ?> px-2 py-1">
                                        <?= e($estados[$o['estado']] ?? $o['estado']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-file-earmark me-1"></i> Proforma</span>
                                <span class="info-value"><?= e($o['n_proforma'] ?: '#' . $o['proforma_id']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-field">
                                <span class="info-label"><i class="bi bi-building me-1"></i> Proveedor</span>
                                <span class="info-value"><?= e($o['proveedor_nombre'] ?? '—') ?></span>
                            </div>
                        </div>

                        <!-- Banner de tiempo transcurrido -->
                        <div class="col-12 mt-3">
                            <?php if ($dias !== null): ?>
                                <div class="info-banner <?= $dias > 8 ? 'bg-danger bg-opacity-10 text-danger border border-danger-subtle' : 'bg-success bg-opacity-10 text-success border border-success-subtle' ?>">
                                    <i class="bi <?= $dias > 8 ? 'bi-exclamation-triangle-fill text-danger' : 'bi-check-circle-fill text-success' ?> fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Tiempo entre revisión de proforma y envío de OCE:</div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge <?= $dias > 8 ? 'bg-danger' : 'bg-success' ?> px-3 py-1 fs-6">
                                                <?= $dias ?> días
                                            </span>
                                            <span class="small text-muted"><?= $dias > 8 ? 'Superó el plazo máximo de 8 días' : 'Dentro del plazo establecido' ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($diasEnCurso !== null): ?>
                                <div class="info-banner <?= $diasEnCurso > 8 ? 'bg-danger bg-opacity-10 text-danger border border-danger-subtle' : 'bg-warning bg-opacity-10 text-dark border border-warning-subtle' ?>">
                                    <i class="bi bi-hourglass-split <?= $diasEnCurso > 8 ? 'text-danger' : 'text-warning-emphasis' ?> fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Tiempo en curso sin enviar OCE:</div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge <?= $diasEnCurso > 8 ? 'bg-danger' : 'bg-warning text-dark' ?> px-3 py-1 fs-6">
                                                <?= $diasEnCurso ?> días transcurridos
                                            </span>
                                            <span class="small text-muted">Aún sin fecha de envío de OCE</span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="info-banner bg-light text-secondary border">
                                    <i class="bi bi-info-circle text-muted fs-4"></i>
                                    <div>
                                        <div class="fw-semibold small">Tiempo transcurrido:</div>
                                        <span class="small text-muted">Sin fecha de revisión registrada en la proforma.</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Comentario -->
                        <?php if (!empty($o['comentario'])): ?>
                            <div class="col-12 mt-2">
                                <div class="comment-box">
                                    <span class="info-label mb-1 text-primary"><i class="bi bi-chat-left-text me-1"></i> Comentario / Observaciones</span>
                                    <p class="mb-0 text-dark small"><?= nl2br(e($o['comentario'])) ?></p>
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
                    <?php if (!empty($o['documento_pdf'])): ?>
                        <div class="doc-preview-box">
                            <div class="doc-icon-wrap bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-truncate text-dark" title="<?= e($o['documento_pdf']) ?>">
                                    <?= e($o['documento_pdf']) ?>
                                </div>
                                <small class="text-muted d-block">Documento PDF adjunto a la orden de compra</small>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="<?= base_url('uploads/ordenes_compra/' . e($o['documento_pdf'])) ?>" 
                                   target="_blank" 
                                   class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">
                                    <i class="bi bi-eye me-1"></i> Ver documento
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-arrow-up text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mb-2 small mt-2">No hay documento adjunto para esta orden de compra.</p>
                            <?php if (Auth::can('ordenes.editar')): ?>
                            <a href="<?= base_url('/ordenes/' . $o['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-upload me-1"></i> Subir documento
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjeta de factura -->
            <div class="card detail-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-receipt me-2 text-primary"></i>Factura vinculada
                    </h6>
                    <span class="badge bg-light text-muted border">Proceso</span>
                </div>
                <div class="card-body">
                    <?php if ($factura): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                    <i class="bi bi-receipt fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Factura #<?= (int) $factura['id'] ?> <?= !empty($factura['n_factura']) ? '— ' . e($factura['n_factura']) : '' ?></div>
                                    <div class="mt-1">
                                        <span class="badge text-bg-<?= $badgeClass[$factura['estado']] ?? 'secondary' ?> px-2 py-1">
                                            <?= e(\App\Models\Factura::ESTADOS[$factura['estado']] ?? $factura['estado']) ?>
                                        </span>
                                        <small class="text-muted ms-2">
                                            <i class="bi bi-calendar3 me-1"></i>Entrega: <?= fmt_date($factura['fecha_entrega_factura']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <a href="<?= base_url('/facturas/' . $factura['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-eye me-1"></i> Ver detalle
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-receipt text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mb-2 small mt-2">Esta orden de compra todavía no tiene una factura generada.</p>
                            <?php if (Auth::can('facturas.crear')): ?>
                            <a href="<?= base_url('/facturas/crear') ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                <i class="bi bi-plus-lg me-1"></i> Registrar factura
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Proforma relacionada y historial -->
        <div class="col-lg-5">
            <!-- Tarjeta de proforma relacionada -->
            <div class="card detail-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i>Proforma vinculada
                    </h6>
                    <span class="badge bg-light text-muted border">Origen</span>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 border">
                        <div>
                            <div class="fw-semibold text-dark">Proforma <?= e($o['n_proforma'] ?: '#' . $o['proforma_id']) ?></div>
                            <small class="text-muted d-block">
                                <i class="bi bi-building me-1"></i><?= e($o['proveedor_nombre'] ?? '—') ?>
                            </small>
                            <small class="text-success fw-bold">
                                <?= fmt_money($o['valor_proforma'] ?? 0) ?>
                            </small>
                        </div>
                        <a href="<?= base_url('/proformas/' . $o['proforma_id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-eye me-1"></i> Ver detalle
                        </a>
                    </div>
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