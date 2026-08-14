<?php
$o = $oc ?? [];
$isEdit = !empty($o);
$val = fn($campo) => e($o[$campo] ?? '');
$selectedProformaId = isset($selectedProformaId) ? (int) $selectedProformaId : 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-primary">
            <i class="bi bi-cart-plus me-2"></i><?= $isEdit ? 'Editar orden de compra #' . (int) $o['id'] : 'Nueva orden de compra' ?>
        </h4>
        <small class="text-muted"><?= $isEdit ? 'Actualiza la información de la orden de compra' : 'Ingresa los datos de la nueva orden de compra' ?></small>
    </div>
    <a href="<?= base_url('/ordenes') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?php if (!$isEdit && empty($proformas)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-5">
            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-secondary">No hay proformas disponibles</h5>
            <p class="text-muted">Todas las proformas registradas ya tienen su OC generada, o aún no has creado ninguna proforma.</p>
            <a href="<?= base_url('/proformas/crear') ?>" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i>Crear proforma
            </a>
        </div>
    </div>
<?php else: ?>

<form method="POST" action="<?= $isEdit ? base_url('/ordenes/' . $o['id']) : base_url('/ordenes') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Sección: Datos generales -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary">
                <i class="bi bi-info-circle me-2 text-primary"></i>Datos generales de la orden de compra
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Proforma relacionada <span class="text-danger">*</span></label>
                    <select name="proforma_id" class="form-select" required <?= $isEdit ? 'disabled' : '' ?>>
                        <option value="">Selecciona una proforma...</option>
                        <?php foreach ($proformas as $p): ?>
                            <option value="<?= (int) $p['id'] ?>" <?= ((string) ($o['proforma_id'] ?? '') === (string) $p['id']) || (!$isEdit && $selectedProformaId === (int) $p['id']) ? 'selected' : '' ?>>
                                <?= e($p['n_proforma'] ?: ('Proforma #' . $p['id'])) ?> — <?= e($p['proveedor_nombre'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="proforma_id" value="<?= (int) $o['proforma_id'] ?>">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>La proforma de una orden de compra ya creada no se puede cambiar.</small>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Estado</label>
                    <select name="estado" class="form-select">
                        <?php foreach ($estados as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($o['estado'] ?? 'pendiente') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Fecha de envío de la OCE</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="fecha_envio_oce" value="<?= $val('fecha_envio_oce') ?>" class="form-control">
                    </div>
                    <small class="text-muted">(al proveedor)</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">N° OCE e interna</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-hash"></i></span>
                        <input type="text" name="n_oce_interna" value="<?= $val('n_oce_interna') ?>" class="form-control" placeholder="Ej. 4978">
                    </div>
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
            <?php if ($isEdit && !empty($o['documento_pdf'])): ?>
                <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 mb-3">
                    <span><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Documento actual: <strong><?= e($o['documento_pdf']) ?></strong></span>
                    <a href="<?= base_url('uploads/ordenes_compra/' . e($o['documento_pdf'])) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
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
            <i class="bi bi-save me-2"></i> <?= $isEdit ? 'Guardar cambios' : 'Registrar orden de compra' ?>
        </button>
        <a href="<?= base_url('/ordenes') ?>" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
            Cancelar
        </a>
    </div>
</form>
<?php endif; ?>

<style>
    /* Estilos idénticos a los otros formularios */
    .dropzone-wrapper {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px dashed #dee2e6 !important;
        background-color: #f8f9fa;
        position: relative;
    }

    .dropzone-wrapper:hover {
        border-color: #0d6efd !important;
        background-color: #f0f7ff;
    }

    .dropzone-wrapper input[type="file"] {
        opacity: 0;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
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

    .input-group .form-control {
        border-left: none;
    }

    .input-group .form-control:focus {
        border-left: none;
        box-shadow: none;
    }

    .btn.rounded-pill {
        border-radius: 50px !important;
    }

    .badge {
        font-weight: 500;
        border-radius: 50px;
    }

    .alert {
        border-radius: 8px;
    }

    .text-muted i {
        margin-right: 4px;
    }
</style>