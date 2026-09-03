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

        <!-- Sección: Documentos -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold text-secondary">
                    <i class="bi bi-paperclip me-2 text-primary"></i>Documentos adjuntos
                </h6>
            </div>
            <div class="card-body">
                <?php if ($isEdit && !empty($documentos)): ?>
                    <label class="form-label small fw-semibold text-secondary mb-2">Documentos actuales en esta factura:</label>
                    <div class="list-group mb-3">
                        <?php foreach ($documentos as $doc): ?>
                            <?php 
                                $isPdf = \App\Models\Documento::esPdf($doc['mime_type'] ?? '', $doc['nombre_archivo'] ?? '');
                                $tamanoFmt = \App\Models\Documento::formatearTamano((int)($doc['tamano_bytes'] ?? 0));
                            ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2 bg-white border rounded-3 mb-2 shadow-xs" id="doc-item-<?= $doc['id'] ?>">
                                <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                <i class="bi <?= $isPdf ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-primary' ?> fs-4"></i>
                                    <div class="text-truncate">
                                        <span class="fw-medium text-dark d-block text-truncate" title="<?= e($doc['nombre_original'] ?: $doc['nombre_archivo']) ?>">
                                            <?= e($doc['nombre_original'] ?: $doc['nombre_archivo']) ?>
                                        </span>
                                        <small class="text-muted"><?= $tamanoFmt ?><?= !empty($doc['creado_en']) ? ' • Subido el ' . fmt_date($doc['creado_en']) : '' ?></small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <a href="<?= base_url('uploads/facturas/' . e($doc['nombre_archivo'])) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                    <div class="form-check form-switch m-0" title="Marcar para eliminar">
                                        <input class="form-check-input check-eliminar-doc" type="checkbox" name="eliminar_documentos[]" value="<?= $doc['id'] ?>" id="del-doc-<?= $doc['id'] ?>" role="switch">
                                        <label class="form-check-label small text-danger" for="del-doc-<?= $doc['id'] ?>">Eliminar</label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="dropzone-wrapper border rounded-3 p-4 text-center bg-light" id="dropzoneContainer">
                    <div class="mb-2" id="dropzoneIconWrap">
                        <i class="bi bi-cloud-arrow-up text-primary" id="dropzoneIcon" style="font-size: 2.2rem;"></i>
                    </div>
                    <p class="mb-1 fw-semibold text-dark" id="dropzoneTexto">Arrastra tus archivos aquí o haz clic para seleccionar</p>
                    <small class="text-muted d-block mb-2">Puedes seleccionar múltiples archivos PDF o imágenes (JPG, PNG, GIF, WEBP). Límite de 5 MB por archivo.</small>
                    <input type="file" name="documentos[]" accept="application/pdf,image/jpeg,image/png,image/gif,image/webp" class="form-control" id="pdfInput" multiple>
                </div>
                <div id="filePreviewList" class="mt-2 d-none"></div>
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
        position: relative;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px dashed #dee2e6 !important;
        background-color: #f8f9fa;
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

    .card { border-radius: 12px !important; overflow: hidden; }
    .card-header { padding: 0.75rem 1.25rem; background-color: transparent; }
    .form-label { font-size: 0.85rem; margin-bottom: 0.25rem; }
    .input-group-text { border: 1px solid #ced4da; border-right: none; background-color: #f8f9fa; }
    .input-group .form-control, .input-group .form-select { border-left: none; }
    .input-group .form-control:focus, .input-group .form-select:focus { border-left: none; box-shadow: none; }
    .btn.rounded-pill { border-radius: 50px !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    const pdfInput = document.getElementById('pdfInput');
    const filePreviewList = document.getElementById('filePreviewList');
    const dropzoneTexto = document.getElementById('dropzoneTexto');
    const dropzoneIcon = document.getElementById('dropzoneIcon');

    if (pdfInput) {
        pdfInput.addEventListener('change', function () {
            if (filePreviewList) filePreviewList.innerHTML = '';
            if (pdfInput.files && pdfInput.files.length > 0) {
                const count = pdfInput.files.length;
                let hayErrorTamano = false;
                let itemsHtml = '<div class="alert alert-light border py-2 px-3 mb-2 rounded-3"><div class="fw-semibold small mb-2 text-primary"><i class="bi bi-paperclip me-1"></i>' + count + ' archivo(s) seleccionado(s) para subir:</div><ul class="list-unstyled mb-0 small">';
                
                for (let i = 0; i < pdfInput.files.length; i++) {
                    const file = pdfInput.files[i];
                    const mb = (file.size / (1024 * 1024)).toFixed(2);
                    const kb = (file.size / 1024).toFixed(0);
                    const sizeFmt = file.size >= 1048576 ? mb + ' MB' : kb + ' KB';
                    const isTooLarge = file.size > 5 * 1024 * 1024;
                    if (isTooLarge) hayErrorTamano = true;
                    
                    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                    const icon = isPdf ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-primary';
                    
                    itemsHtml += '<li class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">' +
                        '<span><i class="bi ' + icon + ' me-2"></i> ' + escapeHtml(file.name) + '</span>' +
                        '<span class="badge ' + (isTooLarge ? 'bg-danger' : 'bg-secondary') + '">' + sizeFmt + (isTooLarge ? ' (Excede 5 MB)' : '') + '</span>' +
                        '</li>';
                }
                itemsHtml += '</ul>';
                if (hayErrorTamano) {
                    itemsHtml += '<div class="text-danger small mt-2 fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Atención: Uno o más archivos superan el límite de 5 MB y serán ignorados.</div>';
                }
                itemsHtml += '</div>';
                
                if (filePreviewList) {
                    filePreviewList.innerHTML = itemsHtml;
                    filePreviewList.classList.remove('d-none');
                }
                if (dropzoneTexto) {
                    dropzoneTexto.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check2-all me-1"></i>' + count + ' archivo(s) listo(s) para subir</span>';
                }
                if (dropzoneIcon) {
                    dropzoneIcon.className = 'bi bi-file-earmark-check text-success';
                }
            } else {
                if (filePreviewList) filePreviewList.classList.add('d-none');
                if (dropzoneTexto) dropzoneTexto.textContent = 'Arrastra tus archivos aquí o haz clic para seleccionar';
                if (dropzoneIcon) dropzoneIcon.className = 'bi bi-cloud-arrow-up text-primary';
            }
        });
    }

    // Efecto visual al marcar checkbox de eliminar documentos existentes
    document.querySelectorAll('.check-eliminar-doc').forEach(function (chk) {
        chk.addEventListener('change', function () {
            const item = document.getElementById('doc-item-' + this.value);
            if (item) {
                if (this.checked) {
                    item.classList.add('bg-danger-subtle', 'text-decoration-line-through', 'border-danger');
                } else {
                    item.classList.remove('bg-danger-subtle', 'text-decoration-line-through', 'border-danger');
                }
            }
        });
    });
});
</script>
