<?php
$g = $gestion ?? [];
$isEdit = !empty($g['id']);
$trabajos = $trabajos ?? [];
$areas = $areas ?? [];
$val = fn($campo) => e($g[$campo] ?? '');
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
                <i class="bi bi-building me-2 text-primary"></i>Datos generales de la cotización
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 mb-1">
                    <label class="form-label fw-semibold d-block">Tipo de gestión</label>
                    <div class="btn-group" role="group" aria-label="Tipo de gestión">
                        <input type="radio" class="btn-check" name="tipo_gestion" id="tipoCotizacion" value="cotizacion" autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm" for="tipoCotizacion">
                            <i class="bi bi-receipt me-1"></i>Cotización
                        </label>

                        <input type="radio" class="btn-check" name="tipo_gestion" id="tipoMensualidad" value="mensualidad" autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm" for="tipoMensualidad">
                            <i class="bi bi-calendar3 me-1"></i>Mensualidad (sin cotización)
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1" id="tipoGestionAyuda">
                       Registra una gestión con número de cotización.
                    </small>
                </div>
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Solicitado por</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                        <select name="solicitado_por" class="form-select">
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
                    <i class="bi bi-list-task me-2 text-primary"></i>Trabajos de esta cotización
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
                    <i class="bi bi-info-circle me-1"></i>Cada trabajo puede asignarse a una proforma distinta
                </small>
                <div class="bg-primary bg-opacity-10 px-4 py-2 rounded">
                    <strong>Total: <span class="text-primary" id="totalTrabajos">L. 0.00</span></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Fechas -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary">
                <i class="bi bi-calendar-event me-2 text-primary"></i>Fechas clave
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Aprobación por ACHSA</label>
                    <input type="date" name="fecha_aprobacion_trabajo" value="<?= $val('fecha_aprobacion_trabajo') ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Finalización por HELIOS</label>
                    <input type="date" name="fecha_finalizacion_trabajo" value="<?= $val('fecha_finalizacion_trabajo') ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Revisión para facturar</label>
                    <input type="date" name="fecha_revision_cotizacion" value="<?= $val('fecha_revision_cotizacion') ?>" class="form-control">
                    <small class="text-muted">Deja en blanco si aún no se revisa</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Documento -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary">
                <i class="bi bi-file-pdf me-2 text-danger"></i>Documento
            </h6>
        </div>
        <div class="card-body">
            <?php if ($isEdit && !empty($g['documento_pdf'])): ?>
                <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 mb-3" id="documentoActualBox">
                    <span><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Documento actual: <strong><?= e($g['documento_pdf']) ?></strong></span>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('uploads/gestiones/' . e($g['documento_pdf'])) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="btnEliminarPdfActual">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
                <div class="form-check mb-3 d-none" id="avisoEliminarPdf">
                    <input class="form-check-input" type="checkbox" name="eliminar_pdf" value="1" id="eliminarPdfCheckbox">
                    <label class="form-check-label text-danger small" for="eliminarPdfCheckbox">
                        Se eliminará el documento actual al guardar. Si subes un archivo nuevo abajo, se usará ese en su lugar.
                    </label>
                    <button type="button" class="btn btn-link btn-sm p-0 ms-2" id="btnCancelarEliminar">Cancelar</button>
                </div>
            <?php endif; ?>
            <div class="dropzone-wrapper border rounded p-4 text-center bg-light">
                <div class="mb-2" id="dropzoneIconWrap">
                    <i class="bi bi-cloud-upload text-primary" id="dropzoneIcon" style="font-size: 2rem;"></i>
                </div>
                <p class="mb-1" id="dropzoneTexto">Arrastra tu archivo aquí o haz clic para seleccionar</p>
                <input type="file" name="documento_pdf" accept="application/pdf,image/jpeg,image/png,image/gif,image/webp" class="form-control" id="pdfInput">
                <small class="text-muted">PDF o imagen (JPG, PNG, GIF, WEBP), máx. 10 MB</small>
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
            <i class="bi bi-save me-2"></i> <?= $isEdit ? 'Guardar cambios' : 'Registrar gestión' ?>
        </button>
        <a href="<?= base_url('/gestiones') ?>" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
            Cancelar
        </a>
    </div>
</form>

<!-- Plantilla de opciones de proforma -->
<template id="proformaOptionsTemplate">
    <option value="">— Sin asignar —</option>
    <?php foreach ($proformas as $p): ?>
        <option value="<?= (int) $p['id'] ?>">
            <?= e($p['n_proforma'] ?: ('Proforma #' . $p['id'])) ?><?= $p['fecha_solicitud'] ? ' (' . fmt_date($p['fecha_solicitud']) . ')' : '' ?>
        </option>
    <?php endforeach; ?>
</template>

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
    // --- Tipo de gestión: Cotización real vs Mensualidad (sin cotización) ---
    const radioCotizacion = document.getElementById('tipoCotizacion');
    const radioMensualidad = document.getElementById('tipoMensualidad');
    const campoNCotizacion = document.getElementById('campoNCotizacion');
    const inputNCotizacion = document.getElementById('inputNCotizacion');
    const campoAprobadoPor = document.getElementById('campoAprobadoPor');
    const inputAprobadoPor = document.getElementById('inputAprobadoPor');
    const tipoGestionAyuda = document.getElementById('tipoGestionAyuda');

    // Se guarda el valor cada vez que se oculta (no solo una vez al cargar la
    // página), para que si el usuario escribe algo y luego cambia de modo,
    // sí se le devuelva lo que había escrito al volver a Cotización real.
    let valorNCotizacionGuardado = inputNCotizacion.value;
    let valorAprobadoPorGuardado = inputAprobadoPor.value;

    function aplicarTipoGestion(esMensualidad) {
        if (esMensualidad) {
            campoNCotizacion.classList.add('d-none');
            if (inputNCotizacion.value) valorNCotizacionGuardado = inputNCotizacion.value;
            inputNCotizacion.value = '';
            campoAprobadoPor.classList.add('d-none');
            if (inputAprobadoPor.value) valorAprobadoPorGuardado = inputAprobadoPor.value;
            inputAprobadoPor.value = '';
            tipoGestionAyuda.innerHTML = '<i class="bi bi-info-circle me-1"></i>Esta gestión quedará registrada como <strong>Mensualidad</strong>, sin número de cotización. Podrás asociarla a una proforma más adelante, igual que cualquier otro trabajo.';
        } else {
            campoNCotizacion.classList.remove('d-none');
            campoAprobadoPor.classList.remove('d-none');
            // Si el usuario había escrito una cotización o un aprobador y solo
            // estaba "probando" el modo mensualidad, se los devolvemos al volver
            // a Cotización real.
            if (!inputNCotizacion.value) {
                inputNCotizacion.value = valorNCotizacionGuardado;
            }
            if (!inputAprobadoPor.value) {
                inputAprobadoPor.value = valorAprobadoPorGuardado;
            }
            tipoGestionAyuda.innerHTML = 'Registra una gestión con número de cotización.';
        }
    }

    radioCotizacion.addEventListener('change', function () { if (this.checked) aplicarTipoGestion(false); });
    radioMensualidad.addEventListener('change', function () { if (this.checked) aplicarTipoGestion(true); });

    // Estado inicial: si es una gestión existente SIN cotización, arranca en modo
    // Mensualidad. En cualquier otro caso (nueva, o ya tiene cotización), Cotización real.
    const esGestionExistenteSinCotizacion = <?= ($isEdit && empty($g['n_cotizacion'])) ? 'true' : 'false' ?>;
    if (esGestionExistenteSinCotizacion) {
        radioMensualidad.checked = true;
        aplicarTipoGestion(true);
    } else {
        radioCotizacion.checked = true;
        aplicarTipoGestion(false);
    }

    const container = document.getElementById('trabajosContainer');
    const btnAgregar = document.getElementById('btnAgregarTrabajo');
    const totalSpan = document.getElementById('totalTrabajos');
    const optionsHtml = document.getElementById('proformaOptionsTemplate').innerHTML;

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
            '<div class="col-md-5">' +
            '   <label class="form-label small fw-semibold">Descripción del trabajo</label>' +
            '   <input type="text" name="trabajo_descripcion[]" class="form-control form-control-sm" placeholder="Ej. Instalación de GPS y FLS">' +
            '</div>' +
            '<div class="col-md-2">' +
            '   <label class="form-label small fw-semibold">Valor (L.)</label>' +
            '   <input type="number" step="0.01" name="trabajo_valor[]" class="form-control form-control-sm valor-input" placeholder="0.00">' +
            '</div>' +
            '<div class="col-md-4">' +
            '   <label class="form-label small fw-semibold">Proforma asignada</label>' +
            '   <select name="trabajo_proforma_id[]" class="form-select form-select-sm">' + optionsHtml + '</select>' +
            '   <div class="mt-1 crear-proforma-slot"></div>' +
            '</div>' +
            '<div class="col-md-1 d-flex justify-content-end">' +
            '   <button type="button" class="btn btn-outline-danger btn-sm btn-quitar-trabajo" title="Quitar este trabajo">' +
            '       <i class="bi bi-trash"></i>' +
            '   </button>' +
            '</div>';

        row.querySelector('input[name="trabajo_descripcion[]"]').value = data.descripcion || '';
        row.querySelector('input[name="trabajo_valor[]"]').value = data.valor ?? '';
        if (data.proforma_id) {
            row.querySelector('select[name="trabajo_proforma_id[]"]').value = data.proforma_id;
        }

        // "Crear proforma": solo disponible para trabajos que ya están guardados
        // en la base de datos (tienen un id real). Las filas nuevas que aún no
        // se han guardado muestran un aviso en su lugar.
        const slot = row.querySelector('.crear-proforma-slot');
        if (data.id) {
            row.dataset.trabajoId = data.id;
            const link = document.createElement('a');
            link.href = '<?= base_url('/proformas/crear') ?>?trabajo_id=' + data.id;
            link.className = 'small text-primary text-decoration-none';
            link.innerHTML = '<i class="bi bi-plus-circle me-1"></i>Crear proforma';
            slot.appendChild(link);
        } else {
            const aviso = document.createElement('small');
            aviso.className = 'text-muted';
            aviso.textContent = 'Guarda la gestión para poder crear la proforma desde aquí';
            slot.appendChild(aviso);
        }

        row.querySelector('.btn-quitar-trabajo').addEventListener('click', function () {
            row.remove();
            actualizarTotal();
        });

        row.querySelector('.valor-input').addEventListener('input', actualizarTotal);

        return row;
    }

    btnAgregar.addEventListener('click', function () {
        container.appendChild(crearFila());
    });

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

    // --- Documento PDF: mostrar el nombre del archivo apenas se selecciona ---
    const pdfInput = document.getElementById('pdfInput');
    const dropzoneTexto = document.getElementById('dropzoneTexto');
    const dropzoneIcon = document.getElementById('dropzoneIcon');
    const textoOriginal = dropzoneTexto ? dropzoneTexto.textContent : '';

    if (pdfInput) {
        pdfInput.addEventListener('change', function () {
            if (pdfInput.files && pdfInput.files.length > 0) {
                const archivo = pdfInput.files[0];
                const kb = (archivo.size / 1024).toFixed(0);
                dropzoneTexto.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i>' +
                    'Archivo seleccionado: <strong>' + archivo.name + '</strong> (' + kb + ' KB)';
                if (dropzoneIcon) {
                    dropzoneIcon.className = 'bi bi-file-earmark-check text-success';
                }
                // Si había pedido eliminar el documento actual, al elegir uno nuevo
                // ya no hace falta esa opción (el nuevo archivo lo reemplaza igual).
                const avisoEliminar = document.getElementById('avisoEliminarPdf');
                const checkEliminar = document.getElementById('eliminarPdfCheckbox');
                if (avisoEliminar && !avisoEliminar.classList.contains('d-none')) {
                    avisoEliminar.classList.add('d-none');
                    if (checkEliminar) checkEliminar.checked = false;
                    const box = document.getElementById('documentoActualBox');
                    if (box) box.classList.remove('d-none');
                }
            } else {
                dropzoneTexto.textContent = textoOriginal;
                if (dropzoneIcon) {
                    dropzoneIcon.className = 'bi bi-cloud-upload text-primary';
                }
            }
        });
    }

    // --- Botón "Eliminar" el documento ya guardado (sin subir uno nuevo) ---
    const btnEliminarPdfActual = document.getElementById('btnEliminarPdfActual');
    const btnCancelarEliminar = document.getElementById('btnCancelarEliminar');
    const documentoActualBox = document.getElementById('documentoActualBox');
    const avisoEliminarPdf = document.getElementById('avisoEliminarPdf');
    const eliminarPdfCheckbox = document.getElementById('eliminarPdfCheckbox');

    if (btnEliminarPdfActual) {
        btnEliminarPdfActual.addEventListener('click', function () {
            documentoActualBox.classList.add('d-none');
            avisoEliminarPdf.classList.remove('d-none');
            if (eliminarPdfCheckbox) eliminarPdfCheckbox.checked = true;
        });
    }
    if (btnCancelarEliminar) {
        btnCancelarEliminar.addEventListener('click', function () {
            avisoEliminarPdf.classList.add('d-none');
            documentoActualBox.classList.remove('d-none');
            if (eliminarPdfCheckbox) eliminarPdfCheckbox.checked = false;
        });
    }
});
</script>