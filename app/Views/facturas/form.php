<?php
$f = $factura ?? [];
$isEdit = !empty($f);
$val = fn($campo) => e($f[$campo] ?? '');
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold text-primary">
                <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'receipt' ?> me-2"></i><?= $isEdit ? 'Editar factura #' . (int) $f['id'] : 'Nueva factura' ?>
            </h4>
            <small class="text-muted"><?= $isEdit ? 'Actualiza los datos de esta factura' : 'Registra la emisión de una factura sobre una orden de compra' ?></small>
        </div>
        <a href="<?= base_url('/facturas') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <?php if (!$isEdit && empty($ordenesCompra)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-info-circle text-warning" style="font-size: 2.5rem;"></i>
                <p class="mt-3 mb-1 fw-semibold">No hay órdenes de compra disponibles sin factura</p>
                <p class="text-muted small">Todas las OC registradas ya tienen su factura generada, o aún no has creado ninguna orden de compra.</p>
                <a href="<?= base_url('/ordenes/crear') ?>" class="btn btn-primary rounded-pill px-4 mt-2">
                    <i class="bi bi-plus-lg me-1"></i> Crear orden de compra
                </a>
            </div>
        </div>
    <?php else: ?>

    <form method="POST" action="<?= $isEdit ? base_url('/facturas/' . $f['id']) : base_url('/facturas') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Sección: Datos generales -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold text-secondary">
                    <i class="bi bi-cart-check me-2 text-primary"></i>Orden de compra y datos generales
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Orden de compra relacionada <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                            <select name="orden_compra_id" class="form-select" required <?= $isEdit ? 'disabled' : '' ?>>
                                <option value="">Selecciona...</option>
                                <?php foreach ($ordenesCompra as $oc): ?>
                                    <option value="<?= (int) $oc['id'] ?>" <?= (string) ($f['orden_compra_id'] ?? '') === (string) $oc['id'] ? 'selected' : '' ?>>
                                        N° OCE <?= e($oc['n_oce_interna'] ?: '#' . $oc['id']) ?> — Proforma <?= e($oc['n_proforma'] ?? '') ?> (<?= e($oc['proveedor_nombre'] ?? '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="orden_compra_id" value="<?= (int) $f['orden_compra_id'] ?>">
                            <small class="text-muted">La orden de compra de una factura ya creada no se puede cambiar.</small>
                        <?php else: ?>
                            <small class="text-muted">El N° OCE se toma automáticamente de la orden de compra que elijas aquí.</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">N° Factura</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-hash"></i></span>
                            <input type="text" name="n_factura" value="<?= $val('n_factura') ?>" class="form-control" placeholder="Ej. 5176">
                        </div>
                        <small class="text-muted">Número de factura emitido por el proveedor.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Estado</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-flag"></i></span>
                            <select name="estado" class="form-select">
                                <?php foreach ($estados as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= ($f['estado'] ?? 'pendiente') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fecha de entrega de factura</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                            <input type="date" name="fecha_entrega_factura" value="<?= $val('fecha_entrega_factura') ?>" class="form-control">
                        </div>
                        <small class="text-muted">Cuando el proveedor entrega la factura a Auditoría.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Documento -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold text-secondary">
                    <i class="bi bi-file-pdf me-2 text-danger"></i>Documento escaneado
                </h6>
            </div>
            <div class="card-body">
                <?php if ($isEdit && !empty($f['documento_pdf'])): ?>
                    <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 mb-3">
                        <span><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Documento actual: <strong><?= e($f['documento_pdf']) ?></strong></span>
                        <a href="<?= base_url('uploads/facturas/' . e($f['documento_pdf'])) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> Ver PDF
                        </a>
                    </div>
                <?php endif; ?>
                <div class="dropzone-wrapper border rounded p-4 text-center bg-light">
                    <div class="mb-2">
                        <i class="bi bi-cloud-upload text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <p class="mb-1">Arrastra tu archivo aquí o haz clic para seleccionar</p>
                    <input type="file" name="documento_pdf" accept="application/pdf" class="form-control">
                    <small class="text-muted">Solo PDF, máx. 10 MB<?= $isEdit ? ' — sube uno nuevo solo si deseas reemplazar el actual' : '' ?></small>
                </div>
            </div>
        </div>

        <!-- Sección: Comentario -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold text-secondary">
                    <i class="bi bi-chat-dots me-2 text-primary"></i>Comentarios adicionales
                </h6>
            </div>
            <div class="card-body">
                <textarea name="comentario" rows="3" class="form-control" placeholder="Escribe aquí cualquier observación o detalle adicional..."><?= $val('comentario') ?></textarea>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex gap-2 mb-5">
            <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill">
                <i class="bi bi-save me-2"></i> <?= $isEdit ? 'Guardar cambios' : 'Registrar factura' ?>
            </button>
            <a href="<?= base_url('/facturas') ?>" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                Cancelar
            </a>
        </div>
    </form>
    <?php endif; ?>
</div>

<style>
    .dropzone-wrapper {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px dashed #dee2e6 !important;
        background-color: #f8f9fa;
    }

    .dropzone-wrapper:hover {
        border-color: #0d6efd !important;
        background-color: #f0f7ff;
    }

    .card { border-radius: 12px !important; overflow: hidden; }
    .card-header { padding: 0.75rem 1.25rem; background-color: transparent; }
    .form-label { font-size: 0.85rem; margin-bottom: 0.25rem; }
    .input-group-text { border: 1px solid #ced4da; border-right: none; background-color: #f8f9fa; }
    .input-group .form-control, .input-group .form-select { border-left: none; }
    .input-group .form-control:focus, .input-group .form-select:focus { border-left: none; box-shadow: none; }
    .btn.rounded-pill { border-radius: 50px !important; }
</style>
