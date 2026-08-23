<?php
$p = $proforma ?? [];
$isEdit = !empty($p);
$prefill = $prefill ?? [];
$val = fn($campo) => e($p[$campo] ?? ($prefill[$campo] ?? ''));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-primary">
            <i class="bi bi-file-earmark-text me-2"></i><?= $isEdit ? 'Editar proforma #' . (int) $p['id'] : 'Nueva proforma' ?>
        </h4>
        <small class="text-muted"><?= $isEdit ? 'Actualiza la información de la proforma' : 'Ingresa los datos de la nueva proforma' ?></small>
    </div>
    <a href="<?= base_url('/proformas') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<form method="POST" action="<?= $isEdit ? base_url('/proformas/' . $p['id']) : base_url('/proformas') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Sección: Datos generales -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary">
                <i class="bi bi-info-circle me-2 text-primary"></i>Datos generales de la proforma
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Proveedor <span class="text-danger">*</span></label>
                    <select name="proveedor_id" class="form-select" required>
                        <option value="">Selecciona un proveedor...</option>
                        <?php foreach ($proveedores as $pr): ?>
                            <option value="<?= (int) $pr['id'] ?>" <?= (string) ($p['proveedor_id'] ?? $prefill['proveedor_id'] ?? '') === (string) $pr['id'] ? 'selected' : '' ?>><?= e($pr['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Solicitado por (área)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                        <select name="solicitado_por" class="form-select">
                            <option value="">Selecciona...</option>
                            <?php foreach ($areas as $a): ?>
                                <option value="<?= e($a['nombre']) ?>" <?= ($p['solicitado_por'] ?? '') === $a['nombre'] ? 'selected' : '' ?>><?= e($a['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Fecha de solicitud</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="fecha_solicitud" value="<?= $val('fecha_solicitud') ?>" class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">N° de cotización</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-hash"></i></span>
                        <input type="text" name="n_cotizacion" id="n_cotizacion" value="<?= $val('n_cotizacion') ?>" class="form-control" placeholder="Ej. S06603">
                    </div>
                    <div id="cotizacionHelp" class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i>Si no hay número de cotización, esta proforma se guardará como mensualidad.</div>
                </div>
                <div class="col-md-4" id="trabajo_select_container">
                    <label class="form-label fw-semibold">Trabajo de cotización</label>
                    <select name="trabajo_id" id="trabajo_id" class="form-select" data-selected="<?= e($p['trabajo_id'] ?? $prefill['trabajo_id'] ?? '') ?>">
                        <option value="">Selecciona un trabajo</option>
                    </select>
                    <input type="hidden" name="trabajo" id="trabajo" value="<?= $val('trabajo') ?>">
                    <div id="trabajoMessage" class="form-text text-muted small"></div>
                </div>
                <div class="col-md-4" id="trabajo_manual_container" style="display: none;">
                    <label class="form-label fw-semibold">Trabajo mensualidad</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-pencil"></i></span>
                        <input type="text" name="trabajo_manual" id="trabajo_manual" value="<?= e($p['trabajo'] ?? '') ?>" class="form-control" placeholder="Describe el trabajo manualmente">
                    </div>
                    <div class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i>Describe el trabajo que se factura como mensualidad.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Valor de cotización (L.)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-cash"></i></span>
                        <input type="number" step="0.01" name="valor_cotizacion" id="valor_cotizacion" value="<?= $val('valor_cotizacion') ?>" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">N° Proforma</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-tag"></i></span>
                        <input type="text" name="n_proforma" value="<?= $val('n_proforma') ?>" class="form-control" placeholder="Ej. 512">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Valor de proforma (L.)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-cash"></i></span>
                        <input type="number" step="0.01" name="valor_proforma" value="<?= $val('valor_proforma') ?>" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Fecha de revisión</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-check2-circle"></i></span>
                        <input type="date" name="fecha_revision_proforma" value="<?= $val('fecha_revision_proforma') ?>" class="form-control">
                    </div>
                    <small class="text-muted">(Aud. Prod. Agrícola)</small>
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
            <?php if ($isEdit && !empty($p['documento_pdf'])): ?>
                <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 mb-3" id="documentoActualBox">
                    <span><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Documento actual: <strong><?= e($p['documento_pdf']) ?></strong></span>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('uploads/proformas/' . e($p['documento_pdf'])) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> Ver 
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="btnEliminarPdfActual">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
                <div class="form-check mb-3 d-none" id="avisoEliminarPdf">
                    <input class="form-check-input" type="checkbox" name="eliminar_pdf" value="1" id="eliminarPdfCheckbox" checked>
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

    <!-- Mensaje informativo -->
    <?php if (!$isEdit): ?>
        <div class="card border-0 bg-light mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-info-circle text-primary" style="font-size: 1.5rem;"></i>
                    <div>
                        <p class="mb-1 small">
                            <strong>Después de crear la proforma</strong>, ve a cada <strong>Gestión</strong> (Editar) y, en el trabajo correspondiente,
                            selecciona esta proforma en su campo "Proforma asignada".
                        </p>
                        <p class="mb-0 small text-muted">Un mismo trabajo puede reasignarse en cualquier momento.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Botones de acción -->
    <div class="d-flex gap-2 mb-5">
        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill">
            <i class="bi bi-save me-2"></i> <?= $isEdit ? 'Guardar cambios' : 'Registrar proforma' ?>
        </button>
        <a href="<?= base_url('/proformas') ?>" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
            Cancelar
        </a>
    </div>
</form>

<style>
    /* Estilos idénticos al form de gestiones */
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
        position: relative;
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

<script>
    (() => {
        const cotizacionInput = document.getElementById('n_cotizacion');
        const trabajoSelect = document.getElementById('trabajo_id');
        const trabajoDescripcionField = document.getElementById('trabajo');
        const trabajoManualContainer = document.getElementById('trabajo_manual_container');
        const trabajoManualInput = document.getElementById('trabajo_manual');
        const cotizacionHelp = document.getElementById('cotizacionHelp');
        const valorCotizacionField = document.getElementById('valor_cotizacion');
        const valorProformaField = document.querySelector('input[name="valor_proforma"]');

        const trabajoMessage = document.getElementById('trabajoMessage');

        let fetchTimeout = null;

        const formatCurrency = value => Number(value).toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const clearTrabajoOptions = () => {
            trabajoSelect.innerHTML = '<option value="">Selecciona un trabajo</option>';
            trabajoDescripcionField.value = '';
            valorCotizacionField.value = '';
            trabajoMessage.textContent = '';
        };

        const fillTrabajoOptions = trabajos => {
            clearTrabajoOptions();
            trabajos.forEach(trabajo => {
                const option = document.createElement('option');
                option.value = trabajo.id;
                option.dataset.valor = trabajo.valor;
                option.dataset.descripcion = trabajo.descripcion;
                option.textContent = `${trabajo.descripcion} — ${trabajo.proveedor_nombre} — L. ${formatCurrency(trabajo.valor)}`;
                trabajoSelect.appendChild(option);
            });

            updateTrabajoMode();

            const selectedId = trabajoSelect.dataset.selected || '';
            if (selectedId) {
                trabajoSelect.value = selectedId;
                if (trabajoSelect.value === selectedId) {
                    trabajoSelect.dispatchEvent(new Event('change'));
                }
            } else if (trabajos.length === 1) {
                trabajoSelect.selectedIndex = 1;
                trabajoSelect.dispatchEvent(new Event('change'));
            }

            trabajoMessage.textContent = trabajos.length > 0
                ? `Se encontraron ${trabajos.length} trabajo${trabajos.length > 1 ? 's' : ''} para la cotización.`
                : 'No se encontraron trabajos para esa cotización.';
        };

        const buildSearchUrl = () => {
            const pathSegments = window.location.pathname.split('/').filter(Boolean);
            const proformasIndex = pathSegments.indexOf('proformas');
            if (proformasIndex >= 0) {
                return '/' + pathSegments.slice(0, proformasIndex + 1).join('/') + '/cotizacion';
            }
            return '/proformas/cotizacion';
        };

        const updateTrabajoMode = () => {
            const tieneCotizacion = cotizacionInput.value.trim() !== '';

            if (!tieneCotizacion) {
                trabajoSelect.disabled = true;
                trabajoManualContainer.style.display = 'block';
                cotizacionHelp.textContent = 'Sin número de cotización: esta proforma se guardará como mensualidad.';
                trabajoMessage.textContent = 'Describe manualmente el trabajo mensualidad.';
                trabajoDescripcionField.value = trabajoManualInput.value.trim();
                if (trabajoManualInput.value.trim()) {
                    trabajoDescripcionField.value = trabajoManualInput.value.trim();
                }
            } else {
                trabajoSelect.disabled = false;
                trabajoManualContainer.style.display = 'none';
                cotizacionHelp.textContent = 'Selecciona un trabajo desde la cotización.';
                if (!trabajoSelect.value) {
                    trabajoDescripcionField.value = '';
                }
            }
        };

        const fetchTrabajos = numero => {
            if (!numero.trim()) {
                clearTrabajoOptions();
                updateTrabajoMode();
                return;
            }

            const url = buildSearchUrl() + '?numero=' + encodeURIComponent(numero.trim());
            fetch(url, { credentials: 'same-origin' })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Respuesta inválida del servidor.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error('Error al buscar trabajos.');
                    }
                    fillTrabajoOptions(data.trabajos || []);
                    updateTrabajoMode();
                })
                .catch(() => {
                    clearTrabajoOptions();
                    trabajoMessage.textContent = 'No se pudo cargar la información de trabajos. Intenta nuevamente.';
                });
        };

        cotizacionInput.addEventListener('input', () => {
            if (fetchTimeout) {
                clearTimeout(fetchTimeout);
            }
            fetchTimeout = setTimeout(() => fetchTrabajos(cotizacionInput.value), 500);
        });

        trabajoSelect.addEventListener('change', () => {
            const selected = trabajoSelect.options[trabajoSelect.selectedIndex];
            const valor = selected?.dataset?.valor || '';
            const descripcion = selected?.dataset?.descripcion || selected?.textContent || '';
            trabajoDescripcionField.value = descripcion;
            valorCotizacionField.value = valor;
            if (valor && valorProformaField) {
                valorProformaField.value = valor;
            }
        });

        trabajoManualInput.addEventListener('input', () => {
            trabajoDescripcionField.value = trabajoManualInput.value.trim();
        });

        if (cotizacionInput.value.trim()) {
            fetchTrabajos(cotizacionInput.value);
        } else {
            updateTrabajoMode();
        }
    })();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
                if (dropzoneIcon) dropzoneIcon.className = 'bi bi-file-earmark-check text-success';

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
                if (dropzoneIcon) dropzoneIcon.className = 'bi bi-cloud-upload text-primary';
            }
        });
    }

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