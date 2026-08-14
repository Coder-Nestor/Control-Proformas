<?php
$e = $entrega ?? [];
$isEdit = !empty($e['id']);
$val = fn($campo) => e($e[$campo] ?? '');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-primary">
            <i class="bi bi-truck me-2"></i><?= $isEdit ? 'Editar entrega #' . (int) $e['id'] : 'Nueva entrega de factura' ?>
        </h4>
        <small class="text-muted"><?= $isEdit ? 'Actualiza la información de la entrega' : 'Registra la entrega de una factura ya emitida' ?></small>
    </div>
    <a href="<?= base_url('/entregas') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?php if (!$isEdit && empty($facturas)): ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-info-circle text-warning" style="font-size: 2.5rem;"></i>
            <p class="mt-3 mb-1 fw-semibold">No hay facturas disponibles sin registro de entrega</p>
            <p class="text-muted small">Todas las facturas emitidas ya tienen su entrega registrada, o aún no has emitido ninguna factura.</p>
            <a href="<?= base_url('/facturas/crear') ?>" class="btn btn-primary rounded-pill px-4 mt-2">
                <i class="bi bi-plus-lg me-1"></i> Registrar una factura
            </a>
        </div>
    </div>
<?php else: ?>

<form method="POST" action="<?= $isEdit ? base_url('/entregas/' . $e['id']) : base_url('/entregas') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Sección: Datos generales -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary">
                <i class="bi bi-receipt me-2 text-primary"></i>Factura y fechas
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Factura relacionada <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                        <select name="factura_id" class="form-select" required <?= $isEdit ? 'disabled' : '' ?>>
                            <option value="">Selecciona una factura...</option>
                            <?php foreach ($facturas as $f): ?>
                                <option value="<?= (int) $f['id'] ?>" <?= (string) ($e['factura_id'] ?? '') === (string) $f['id'] ? 'selected' : '' ?>>
                                    N° OCE <?= e($f['n_oce_interna'] ?: '#' . $f['id']) ?> — Proforma <?= e($f['n_proforma'] ?? '') ?> (<?= e($f['proveedor_nombre'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="factura_id" value="<?= (int) $e['factura_id'] ?>">
                        <small class="text-muted">La factura de una entrega ya creada no se puede cambiar.</small>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Fecha de entrega de factura al dueño</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                        <input type="date" name="fecha_entrega_dueno" value="<?= $val('fecha_entrega_dueno') ?>" class="form-control">
                    </div>
                    <small class="text-muted">Cuando Auditoría entrega la factura al dueño del gasto.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Solicitud de revisión y orden de pago</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="fecha_solicitud_revision_pago" value="<?= $val('fecha_solicitud_revision_pago') ?>" class="form-control">
                    </div>
                    <small class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Se espera un máximo de <strong>8 días</strong> desde la entrega al dueño.</small>
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
            <?php if ($isEdit && !empty($e['documento_pdf'])): ?>
                <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 mb-3">
                    <span><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Documento actual: <strong><?= e($e['documento_pdf']) ?></strong></span>
                    <a href="<?= base_url('uploads/entregas/' . e($e['documento_pdf'])) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Ver PDF
                    </a>
                </div>
            <?php endif; ?>
            <div class="dropzone-wrapper border rounded p-4 text-center bg-light">
                <div class="mb-2">
                    <i class="bi bi-cloud-upload text-primary" style="font-size: 2rem;"></i>
                </div>
                <p class="mb-1">Arrastra tu archivo aquí o haz clic para seleccionar</p>
                <input type="file" name="documento_pdf" accept="application/pdf" class="form-control" id="pdfInput">
                <small class="text-muted">Solo PDF, máx. 10 MB</small>
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
            <i class="bi bi-save me-2"></i> <?= $isEdit ? 'Guardar cambios' : 'Registrar entrega' ?>
        </button>
        <a href="<?= base_url('/entregas') ?>" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
            Cancelar
        </a>
    </div>
</form>
<?php endif; ?>

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

    .card {
        border-radius: 12px !important;
        overflow: hidden;
    }

    .card-header {
        padding: 0.75rem 1.25rem;
    }

    .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }

    .input-group-text {
        border: 1px solid #ced4da;
        border-right: none;
        background-color: #f8f9fa;
    }

    .input-group .form-control,
    .input-group .form-select {
        border-left: none;
    }

    .input-group .form-control:focus,
    .input-group .form-select:focus {
        border-left: none;
        box-shadow: none;
    }
</style>