<?php
$g = $gestion ?? [];
$isEdit = !empty($g['id']);
$trabajos = $trabajos ?? [];
$areas = $areas ?? [];
$val = fn($campo) => e($g[$campo] ?? '');

$cotizRaw = trim((string)($g['n_cotizacion'] ?? ''));
$cotizUpper = mb_strtoupper($cotizRaw);
$esGestionInterna = $isEdit && ($cotizUpper === 'GESTIÓN INTERNA' || $cotizUpper === 'GESTION INTERNA');
$esMensualidad = $isEdit && $cotizUpper === 'MENSUALIDAD';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-primary">
            <i class="bi bi-clipboard-check me-2"></i><?= $isEdit ? 'Editar gestión #' . (int) $g['id'] : 'Nueva gestión' ?>
        </h4>
        <small class="text-muted"><?= $isEdit ? 'Actualiza la información de la gestión' : 'Ingresa los datos de la nueva gestión' ?></small>
    </div>
    <a href="<?= base_url('/gestiones') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<form method="POST" action="<?= $isEdit ? base_url('/gestiones/' . $g['id']) : base_url('/gestiones') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Sección: Datos generales -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary">
                <i class="bi bi-building me-2 text-primary"></i>Datos generales
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 mb-1">
                    <label class="form-label fw-semibold d-block">Tipo de gestión</label>
                    <div class="btn-group flex-wrap" role="group" aria-label="Tipo de gestión">
                        <input type="radio" class="btn-check" name="tipo_gestion" id="tipoCotizacion" value="cotizacion" autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm" for="tipoCotizacion">
                            <i class="bi bi-receipt me-1"></i>Cotización
                        </label>

                        <input type="radio" class="btn-check" name="tipo_gestion" id="tipoMensualidad" value="mensualidad" autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm" for="tipoMensualidad">
                            <i class="bi bi-calendar3 me-1"></i>Mensualidad (sin cotización)
                        </label>

                        <input type="radio" class="btn-check" name="tipo_gestion" id="tipoInterna" value="interna" autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm" for="tipoInterna">
                            <i class="bi bi-shield-check me-1"></i>Gestión Interna
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1" id="tipoGestionAyuda">
                       Registra una gestión con número de cotización.
                    </small>
                </div>
                <div class="col-md-6" id="campoProveedor">
                    <label class="form-label fw-semibold">Proveedor <span class="text-danger">*</span></label>
                    <select name="proveedor_id" class="form-select" required>
                        <option value="">Selecciona un proveedor...</option>
                        <?php foreach ($proveedores as $p): ?>
                            <option value="<?= (int) $p['id'] ?>" <?= (string) ($g['proveedor_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>><?= e($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6" id="campoNCotizacion">
                    <label class="form-label fw-semibold">N° Cotización</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-hash"></i></span>
                        <input type="text" name="n_cotizacion" id="inputNCotizacion" value="<?= $val('n_cotizacion') ?>" class="form-control" placeholder="Ej. S06603">
                    </div>
                </div>
                <div class="col-md-6" id="campoSolicitadoPor">
                    <label class="form-label fw-semibold">Solicitado por</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                        <select name="solicitado_por" id="selectSolicitadoPor" class="form-select">
                            <option value="">Selecciona un área solicitante...</option>
                            <?php foreach ($areas as $a): ?>
                                <option value="<?= e($a['nombre']) ?>" <?= ($g['solicitado_por'] ?? '') === $a['nombre'] ? 'selected' : '' ?>><?= e($a['nombre']) ?></option>
                            <?php endforeach; ?>
                            <?php if (!empty($g['solicitado_por']) && !in_array($g['solicitado_por'], array_column($areas, 'nombre'))): ?>
                                <option value="<?= e($g['solicitado_por']) ?>" selected><?= e($g['solicitado_por']) ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6" id="campoAprobadoPor">
                    <label class="form-label fw-semibold">Aprobado por</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person-check"></i></span>
                        <input type="text" name="aprobado_por" id="inputAprobadoPor" value="<?= $val('aprobado_por') ?>" class="form-control" placeholder="Nombre del aprobador">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Trabajos -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-secondary mb-0">
                    <i class="bi bi-list-task me-2 text-primary"></i>Detalle de trabajos
                </h6>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" id="btnAgregarTrabajo">
                    <i class="bi bi-plus-lg"></i> Agregar trabajo
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="trabajosContainer"></div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>Registra los trabajos o servicios correspondientes
                </small>
                <div class="bg-primary bg-opacity-10 px-4 py-2 rounded">
                    <strong>Total: <span class="text-primary" id="totalTrabajos">L. 0.00</span></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Fechas -->
    <div class="card border-0 shadow-sm mb-4" id="seccionFechas">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary">
                <i class="bi bi-calendar-event me-2 text-primary"></i>Fechas clave
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Aprobación por ACHSA</label>
                    <input type="date" name="fecha_aprobacion_trabajo" id="inputFechaAprobacion" value="<?= $val('fecha_aprobacion_trabajo') ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Finalización por HELIOS</label>
                    <input type="date" name="fecha_finalizacion_trabajo" id="inputFechaFinalizacion" value="<?= $val('fecha_finalizacion_trabajo') ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Revisión para facturar</label>
                    <input type="date" name="fecha_revision_cotizacion" id="inputFechaRevision" value="<?= $val('fecha_revision_cotizacion') ?>" class="form-control">
                    <small class="text-muted">Deja en blanco si aún no se revisa</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Documentos -->
    <div class="card border-0 shadow-sm mb-4" id="seccionDocumentos">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary">
                <i class="bi bi-paperclip me-2 text-primary"></i>Documentos adjuntos
            </h6>
        </div>
        <div class="card-body">
            <?php if ($isEdit && !empty($documentos)): ?>
                <label class="form-label small fw-semibold text-secondary mb-2">Documentos actuales en esta gestión:</label>
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
                                <a href="<?= base_url('uploads/gestiones/' . e($doc['nombre_archivo'])) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
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
                <p class="mb-1 fw-semibold text-dark" id="dropzoneTexto">Arrastra tus archivos aquí o haz clic para seleccionar (máximo 2)</p>
                <small class="text-muted d-block mb-2">Puedes seleccionar un máximo de 2 archivos (PDF o imágenes JPG, PNG, GIF, WEBP). Límite de 5 MB por archivo.</small>
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
            <i class="bi bi-save me-2"></i> <?= $isEdit ? 'Guardar cambios' : 'Registrar gestión' ?>
        </button>
        <a href="<?= base_url('/gestiones') ?>" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
            Cancelar
        </a>
    </div>
</form>

<style>
    /* Estilos personalizados para mejorar la apariencia */
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

    .trabajo-row {
        background-color: #fafbfc;
        padding: 1rem;
        border-radius: 8px;
        transition: background-color 0.2s ease;
    }

    .trabajo-row:hover {
        background-color: #f0f4ff;
    }

    .trabajo-row .btn-outline-danger {
        border-color: transparent;
    }

    .trabajo-row .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
        border-color: #dc3545;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // --- Tipo de gestión: Cotización vs Mensualidad vs Gestión Interna ---
    const radioCotizacion = document.getElementById('tipoCotizacion');
    const radioMensualidad = document.getElementById('tipoMensualidad');
    const radioInterna = document.getElementById('tipoInterna');
    
    const campoNCotizacion = document.getElementById('campoNCotizacion');
    const inputNCotizacion = document.getElementById('inputNCotizacion');
    const campoSolicitadoPor = document.getElementById('campoSolicitadoPor');
    const selectSolicitadoPor = document.getElementById('selectSolicitadoPor');
    const campoAprobadoPor = document.getElementById('campoAprobadoPor');
    const inputAprobadoPor = document.getElementById('inputAprobadoPor');
    const seccionFechas = document.getElementById('seccionFechas');
    const tipoGestionAyuda = document.getElementById('tipoGestionAyuda');

    let valorNCotizacionGuardado = (inputNCotizacion.value !== 'Gestión Interna' && inputNCotizacion.value !== 'Gestion Interna') ? inputNCotizacion.value : '';
    let valorSolicitadoPorGuardado = selectSolicitadoPor ? selectSolicitadoPor.value : '';
    let valorAprobadoPorGuardado = inputAprobadoPor.value;

    function aplicarTipoGestion(tipo) {
        if (tipo === 'interna') {
            campoNCotizacion.classList.add('d-none');
            if (inputNCotizacion.value && inputNCotizacion.value !== 'Gestión Interna') valorNCotizacionGuardado = inputNCotizacion.value;
            inputNCotizacion.value = 'Gestión Interna';

            campoSolicitadoPor.classList.remove('d-none');
            if (selectSolicitadoPor && !selectSolicitadoPor.value) {
                selectSolicitadoPor.value = valorSolicitadoPorGuardado;
            }

            campoAprobadoPor.classList.add('d-none');
            if (inputAprobadoPor.value) valorAprobadoPorGuardado = inputAprobadoPor.value;
            inputAprobadoPor.value = '';

            seccionFechas.classList.add('d-none');
            tipoGestionAyuda.innerHTML = '<i class="bi bi-info-circle me-1"></i>Esta gestión quedará registrada como <strong>Gestión Interna</strong>. Se llenarán proveedor, área solicitante, trabajo, valor, documentos y comentarios.';
        } else if (tipo === 'mensualidad') {
            campoNCotizacion.classList.add('d-none');
            if (inputNCotizacion.value && inputNCotizacion.value !== 'Gestión Interna') valorNCotizacionGuardado = inputNCotizacion.value;
            inputNCotizacion.value = '';

            campoSolicitadoPor.classList.remove('d-none');
            if (selectSolicitadoPor && !selectSolicitadoPor.value) {
                selectSolicitadoPor.value = valorSolicitadoPorGuardado;
            }

            campoAprobadoPor.classList.add('d-none');
            if (inputAprobadoPor.value) valorAprobadoPorGuardado = inputAprobadoPor.value;
            inputAprobadoPor.value = '';

            seccionFechas.classList.remove('d-none');
            tipoGestionAyuda.innerHTML = '<i class="bi bi-info-circle me-1"></i>Esta gestión quedará registrada como <strong>Mensualidad</strong>, sin número de cotización.';
        } else { // 'cotizacion'
            campoNCotizacion.classList.remove('d-none');
            if (!inputNCotizacion.value || inputNCotizacion.value === 'Gestión Interna') {
                inputNCotizacion.value = valorNCotizacionGuardado;
            }

            campoSolicitadoPor.classList.remove('d-none');
            if (selectSolicitadoPor && !selectSolicitadoPor.value) {
                selectSolicitadoPor.value = valorSolicitadoPorGuardado;
            }

            campoAprobadoPor.classList.remove('d-none');
            if (!inputAprobadoPor.value) {
                inputAprobadoPor.value = valorAprobadoPorGuardado;
            }

            seccionFechas.classList.remove('d-none');
            tipoGestionAyuda.innerHTML = 'Registra una gestión con número de cotización.';
        }
    }

    if (radioCotizacion) radioCotizacion.addEventListener('change', function () { if (this.checked) aplicarTipoGestion('cotizacion'); });
    if (radioMensualidad) radioMensualidad.addEventListener('change', function () { if (this.checked) aplicarTipoGestion('mensualidad'); });
    if (radioInterna) radioInterna.addEventListener('change', function () { if (this.checked) aplicarTipoGestion('interna'); });

    const esGestionInterna = <?= $esGestionInterna ? 'true' : 'false' ?>;
    const esMensualidad = <?= $esMensualidad ? 'true' : 'false' ?>;

    if (esGestionInterna) {
        radioInterna.checked = true;
        aplicarTipoGestion('interna');
    } else if (esMensualidad) {
        radioMensualidad.checked = true;
        aplicarTipoGestion('mensualidad');
    } else {
        radioCotizacion.checked = true;
        aplicarTipoGestion('cotizacion');
    }

    const container = document.getElementById('trabajosContainer');
    const btnAgregar = document.getElementById('btnAgregarTrabajo');
    const totalSpan = document.getElementById('totalTrabajos');

    function actualizarTotal() {
        let total = 0;
        container.querySelectorAll('.valor-input').forEach(function (input) {
            const v = parseFloat(input.value);
            if (!isNaN(v)) total += v;
        });
        totalSpan.textContent = 'L. ' + total.toFixed(2);
    }

    function crearFila(data) {
        data = data || {};
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end trabajo-row mb-3';

        row.innerHTML =
            '<div class="col-md-7">' +
            '   <label class="form-label small fw-semibold">Descripción del trabajo <span class="text-danger">*</span></label>' +
            '   <input type="text" name="trabajo_descripcion[]" class="form-control form-control-sm" placeholder="Ej. Reparación de equipo / Servicio técnico" required>' +
            '   <input type="hidden" name="trabajo_proforma_id[]" value="' + escapeHtml(data.proforma_id || '') + '">' +
            '</div>' +
            '<div class="col-md-4">' +
            '   <label class="form-label small fw-semibold">Valor (L.)</label>' +
            '   <input type="number" step="0.01" name="trabajo_valor[]" class="form-control form-control-sm valor-input" placeholder="0.00">' +
            '</div>' +
            '<div class="col-md-1 d-flex justify-content-end">' +
            '   <button type="button" class="btn btn-outline-danger btn-sm btn-quitar-trabajo" title="Quitar este trabajo">' +
            '       <i class="bi bi-trash"></i>' +
            '   </button>' +
            '</div>';

        row.querySelector('input[name="trabajo_descripcion[]"]').value = data.descripcion || '';
        row.querySelector('input[name="trabajo_valor[]"]').value = data.valor ?? '';

        row.querySelector('.btn-quitar-trabajo').addEventListener('click', function () {
            if (container.querySelectorAll('.trabajo-row').length > 1) {
                row.remove();
                actualizarTotal();
            } else {
                row.querySelector('input[name="trabajo_descripcion[]"]').value = '';
                row.querySelector('input[name="trabajo_valor[]"]').value = '';
                actualizarTotal();
            }
        });

        row.querySelector('.valor-input').addEventListener('input', actualizarTotal);

        return row;
    }

    if (btnAgregar) {
        btnAgregar.addEventListener('click', function () {
            container.appendChild(crearFila());
        });
    }

    // Cargar trabajos existentes
    const trabajosExistentes = <?= json_encode(array_map(fn($t) => [
        'id' => $t['id'],
        'descripcion' => $t['descripcion'],
        'valor' => $t['valor'],
        'proforma_id' => $t['proforma_id'],
    ], $trabajos), JSON_UNESCAPED_UNICODE) ?>;

    if (trabajosExistentes.length) {
        trabajosExistentes.forEach(function (t) {
            container.appendChild(crearFila(t));
        });
    } else {
        container.appendChild(crearFila());
    }

    actualizarTotal();

    // --- Múltiples documentos con acumulación y eliminación interactiva (Máx 2) ---
    const MAX_ARCHIVOS = 2;
    const MAX_MB = 5;
    const pdfInput = document.getElementById('pdfInput');
    const filePreviewList = document.getElementById('filePreviewList');
    const dropzoneTexto = document.getElementById('dropzoneTexto');
    const dropzoneIcon = document.getElementById('dropzoneIcon');

    const dropzoneContainer = document.getElementById('dropzoneContainer');

    // Documentos que YA están guardados en esta gestión (cuentan para el límite de 2)
    const totalDocumentosExistentes = <?= json_encode($isEdit && !empty($documentos) ? count($documentos) : 0) ?>;
    let documentosMarcadosParaEliminar = 0;

    let archivosSeleccionados = [];

    function espaciosDisponibles() {
        const existentesActivos = totalDocumentosExistentes - documentosMarcadosParaEliminar;
        return MAX_ARCHIVOS - existentesActivos - archivosSeleccionados.length;
    }

    function sincronizarInputFiles() {
        if (!pdfInput) return;
        try {
            const dt = new DataTransfer();
            archivosSeleccionados.forEach(file => dt.items.add(file));
            pdfInput.files = dt.files;
        } catch (e) {
            console.error('Error al sincronizar archivos con DataTransfer:', e);
        }
    }

    function renderizarListaArchivos() {
        const disponibles = espaciosDisponibles();

        if (filePreviewList) {
            filePreviewList.innerHTML = '';

            if (archivosSeleccionados.length === 0) {
                filePreviewList.classList.add('d-none');
            } else {
                const count = archivosSeleccionados.length;
                let itemsHtml = '<div class="card border border-primary-subtle shadow-xs mt-3">' +
                    '<div class="card-header bg-primary bg-opacity-10 py-2 px-3 d-flex justify-content-between align-items-center">' +
                        '<span class="fw-semibold small text-primary"><i class="bi bi-paperclip me-1"></i>' + count + ' documento(s) nuevo(s) listo(s) para subir</span>' +
                        '<span class="badge bg-primary rounded-pill">' + count + '/' + MAX_ARCHIVOS + '</span>' +
                    '</div>' +
                    '<div class="card-body p-2">' +
                        '<div class="d-flex flex-column gap-2">';

                archivosSeleccionados.forEach((file, index) => {
                    const mb = (file.size / (1024 * 1024)).toFixed(2);
                    const kb = (file.size / 1024).toFixed(0);
                    const sizeFmt = file.size >= 1048576 ? mb + ' MB' : kb + ' KB';
                    const isTooLarge = file.size > MAX_MB * 1024 * 1024;
                    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                    const icon = isPdf ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-primary';

                    itemsHtml += '<div class="d-flex justify-content-between align-items-center p-2 bg-white rounded-3 border">' +
                        '<div class="d-flex align-items-center gap-2 text-truncate me-2">' +
                            '<i class="bi ' + icon + ' fs-4"></i>' +
                            '<div class="text-truncate">' +
                                '<span class="fw-medium text-dark d-block text-truncate" title="' + escapeHtml(file.name) + '">' + escapeHtml(file.name) + '</span>' +
                                '<small class="text-muted">' + sizeFmt + (isTooLarge ? ' <span class="text-danger fw-bold">(Excede ' + MAX_MB + ' MB)</span>' : '') + '</small>' +
                            '</div>' +
                        '</div>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 flex-shrink-0 btn-quitar-nuevo-doc" data-index="' + index + '" title="Quitar este documento">' +
                            '<i class="bi bi-trash me-1"></i> Quitar' +
                        '</button>' +
                    '</div>';
                });

                itemsHtml += '</div></div></div>';
                filePreviewList.innerHTML = itemsHtml;
                filePreviewList.classList.remove('d-none');

                filePreviewList.querySelectorAll('.btn-quitar-nuevo-doc').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const idx = parseInt(this.dataset.index, 10);
                        archivosSeleccionados.splice(idx, 1);
                        sincronizarInputFiles();
                        renderizarListaArchivos();
                    });
                });
            }
        }

        // Estado del dropzone según el cupo disponible (existentes + nuevos)
        // IMPORTANTE: nunca deshabilitamos pdfInput (input.disabled = true), porque un
        // input deshabilitado NO se envía con el formulario y se perderían los archivos
        // ya seleccionados. En vez de eso, solo bloqueamos visualmente y evitamos que se
        // abra el diálogo de selección (ver listener de 'click' más abajo).
        if (disponibles <= 0) {
            dropzoneContainer.classList.add('opacity-50');
            dropzoneContainer.style.cursor = 'not-allowed';
            if (dropzoneTexto) {
                dropzoneTexto.innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-exclamation-circle me-1"></i>Límite de ' + MAX_ARCHIVOS + ' documentos alcanzado. Elimina uno para poder subir otro.</span>';
            }
            if (dropzoneIcon) {
                dropzoneIcon.className = 'bi bi-slash-circle text-danger';
            }
        } else {
            dropzoneContainer.classList.remove('opacity-50');
            dropzoneContainer.style.cursor = 'pointer';

            if (archivosSeleccionados.length === 0) {
                if (dropzoneTexto) dropzoneTexto.textContent = 'Arrastra tus archivos aquí o haz clic para seleccionar (máximo 2)';
                if (dropzoneIcon) dropzoneIcon.className = 'bi bi-cloud-arrow-up text-primary';
            } else {
                if (dropzoneTexto) {
                    dropzoneTexto.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check2-all me-1"></i>' + archivosSeleccionados.length + ' documento(s) seleccionado(s)' + (disponibles > 0 ? ' · Puedes agregar ' + disponibles + ' más' : '') + '</span>';
                }
                if (dropzoneIcon) dropzoneIcon.className = 'bi bi-file-earmark-check text-success';
            }
        }
    }

    function procesarNuevosArchivos(nuevosFiles) {
        if (!nuevosFiles || nuevosFiles.length === 0) return;

        let agregados = 0;
        let excedioLimite = false;

        for (let i = 0; i < nuevosFiles.length; i++) {
            const file = nuevosFiles[i];

            if (espaciosDisponibles() <= 0) {
                excedioLimite = true;
                break;
            }

            const yaExiste = archivosSeleccionados.some(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified);
            if (!yaExiste) {
                if (file.size > MAX_MB * 1024 * 1024) {
                    alert('El archivo "' + file.name + '" supera el límite de ' + MAX_MB + ' MB y no se puede adjuntar.');
                    continue;
                }
                archivosSeleccionados.push(file);
                agregados++;
            }
        }

        if (excedioLimite) {
            alert('Ya tienes ' + MAX_ARCHIVOS + ' documentos entre los existentes y los nuevos. Elimina alguno para poder adjuntar otro.');
        }

        sincronizarInputFiles();
        renderizarListaArchivos();
    }

    if (pdfInput) {
        // Evita que se abra el selector de archivos si ya no hay cupo disponible,
        // sin deshabilitar el input (así los archivos ya elegidos sí se envían).
        pdfInput.addEventListener('click', function (e) {
            if (espaciosDisponibles() <= 0) {
                e.preventDefault();
                alert('Ya tienes ' + MAX_ARCHIVOS + ' documentos entre los existentes y los nuevos. Elimina alguno para poder adjuntar otro.');
            }
        });

        pdfInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                procesarNuevosArchivos(Array.from(this.files));
            }
        });

        const formPadre = pdfInput.closest('form');
        if (formPadre) {
            formPadre.addEventListener('submit', function () {
                // Aseguramos que el input contenga exactamente los archivos nuevos
                // seleccionados justo antes de enviar el formulario.
                sincronizarInputFiles();
            });
        }
    }

    if (dropzoneContainer) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzoneContainer.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (espaciosDisponibles() > 0) {
                    dropzoneContainer.classList.add('dragover');
                }
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzoneContainer.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzoneContainer.classList.remove('dragover');
            });
        });

        dropzoneContainer.addEventListener('drop', function (e) {
            if (espaciosDisponibles() <= 0) {
                alert('Ya tienes ' + MAX_ARCHIVOS + ' documentos entre los existentes y los nuevos. Elimina alguno para poder adjuntar otro.');
                return;
            }
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                procesarNuevosArchivos(Array.from(dt.files));
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
                    documentosMarcadosParaEliminar++;
                } else {
                    item.classList.remove('bg-danger-subtle', 'text-decoration-line-through', 'border-danger');
                    documentosMarcadosParaEliminar--;
                }
            }
            renderizarListaArchivos();
        });
    });

    // Estado inicial del dropzone (por si ya hay 2 documentos existentes desde el inicio)
    renderizarListaArchivos();
});
</script>