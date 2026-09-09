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
                                <option value="<?= e($a['nombre']) ?>" <?= ($p['solicitado_por'] ?? $prefill['solicitado_por'] ?? '') === $a['nombre'] ? 'selected' : '' ?>><?= e($a['nombre']) ?></option>
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
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" 
                                   name="n_cotizacion" 
                                   id="n_cotizacion" 
                                   class="form-control" 
                                   placeholder="Escribe o selecciona una cotización..." 
                                   value="<?= $val('n_cotizacion') ?>" 
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary dropdown-toggle" 
                                    type="button" 
                                    id="btnToggleCotizaciones" 
                                    title="Ver todas las cotizaciones"
                                    aria-expanded="false"></button>
                            <ul class="dropdown-menu dropdown-menu-end w-100 shadow p-0 mt-1" id="cotizacionesDropdownMenu" style="max-height: 280px; overflow-y: auto; z-index: 1050; width: 100%; top: 100%; left: 0;">
                                <li class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center sticky-top">
                                    <span class="small text-muted fw-bold"><i class="bi bi-list-check me-1"></i>Cotizaciones en Gestiones</span>
                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-pill text-decoration-none" id="btnSelectMensualidad">
                                        <i class="bi bi-x-circle me-1"></i>Sin cotización
                                    </button>
                                </li>
                                <div id="cotizacionesListOptions">
                                    <?php 
                                    $cotizacionesList = $cotizaciones ?? [];
                                    if (empty($cotizacionesList)):
                                    ?>
                                        <li class="p-3 text-center text-muted small">
                                            <i class="bi bi-check2-all text-success d-block mb-1 fs-5"></i>
                                            No hay cotizaciones con trabajos disponibles sin proforma
                                        </li>
                                    <?php else: ?>
                                        <?php foreach ($cotizacionesList as $c): ?>
                                            <li>
                                                <a href="#" class="dropdown-item py-2 px-3 border-bottom cotizacion-option-item text-wrap" 
                                                   data-value="<?= e($c['n_cotizacion']) ?>" 
                                                   data-proveedor-id="<?= (int)($c['proveedor_id'] ?? 0) ?>" 
                                                   data-solicitado-por="<?= e($c['solicitado_por'] ?? '') ?>">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fw-bold text-primary"><?= e($c['n_cotizacion']) ?></span>
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= e($c['proveedor_nombre'] ?? 'Sin proveedor') ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= e($c['solicitado_por'] ?? '—') ?></small>
                                                        <small class="text-success fw-semibold"><i class="bi bi-check2-circle me-1"></i><?= $c['trabajos_sin_asignar'] ?><?= $c['total_trabajos'] > 1 ? ' de ' . $c['total_trabajos'] : '' ?> disp.</small>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <li id="cotizacionesNoResults" class="p-3 text-center text-muted small d-none">
                                    <i class="bi bi-search text-muted d-block mb-1 fs-5"></i>
                                    No se encontraron cotizaciones coincidentes
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div id="cotizacionHelp" class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i>Escribe el número de cotización para filtrar o selecciona una de la lista.</div>
                </div>
                <div class="col-md-4" id="trabajo_select_container">
                    <label class="form-label fw-semibold">Trabajo de cotización</label>
                    <?php
                        // Si el trabajo viene vinculado desde una Gestión y ES una
                        // mensualidad (sin cotización), la búsqueda AJAX normal nunca
                        // corre — así que la opción se agrega de una vez aquí mismo,
                        // para que su valor SÍ se envíe al guardar el formulario.
                        $trabajoVinculadoId = $p['trabajo_id'] ?? $prefill['trabajo_id'] ?? null;
                        $esVinculoSinCotizacion = empty($p['n_cotizacion'] ?? $prefill['n_cotizacion'] ?? null);
                    ?>
                    <select name="trabajo_id" id="trabajo_id" class="form-select" data-selected="<?= e($trabajoVinculadoId ?? '') ?>">
                        <option value="">Selecciona un trabajo</option>
                        <?php if (!empty($trabajoVinculadoId) && $esVinculoSinCotizacion): ?>
                            <option value="<?= (int) $trabajoVinculadoId ?>" selected
                                    data-valor="<?= $val('valor_cotizacion') ?>"
                                    data-descripcion="<?= $val('trabajo') ?>">
                                <?= $val('trabajo') ?: ('Trabajo #' . (int) $trabajoVinculadoId) ?>
                            </option>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" name="trabajo" id="trabajo" value="<?= $val('trabajo') ?>">
                    <div id="trabajoMessage" class="form-text text-muted small"></div>
                </div>
                <div class="col-md-4" id="trabajo_manual_container" style="display: none;">
                    <label class="form-label fw-semibold">Trabajo mensualidad</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-pencil"></i></span>
                        <input type="text" name="trabajo_manual" id="trabajo_manual" value="<?= $val('trabajo') ?>" class="form-control" placeholder="Describe el trabajo manualmente">
                    </div>
                    <div class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i>Describe el trabajo que se factura como mensualidad.</div>
                </div>
                <div class="col-md-4" id="valor_cotizacion_container">
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

    <!-- Sección: Documentos -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary">
                <i class="bi bi-paperclip me-2 text-primary"></i>Documentos adjuntos
            </h6>
        </div>
        <div class="card-body">
            <?php if ($isEdit && !empty($documentos)): ?>
                <label class="form-label small fw-semibold text-secondary mb-2">Documentos actuales en esta proforma:</label>
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
                                <a href="<?= base_url('uploads/proformas/' . e($doc['nombre_archivo'])) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
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
        const btnToggleCotizaciones = document.getElementById('btnToggleCotizaciones');
        const cotizacionesDropdownMenu = document.getElementById('cotizacionesDropdownMenu');
        const cotizacionesListOptions = document.getElementById('cotizacionesListOptions');
        const cotizacionesNoResults = document.getElementById('cotizacionesNoResults');
        const btnSelectMensualidad = document.getElementById('btnSelectMensualidad');
        const trabajoSelect = document.getElementById('trabajo_id');
        const trabajoDescripcionField = document.getElementById('trabajo');
        const trabajoManualContainer = document.getElementById('trabajo_manual_container');
        const trabajoManualInput = document.getElementById('trabajo_manual');
        const cotizacionHelp = document.getElementById('cotizacionHelp');
        const valorCotizacionField = document.getElementById('valor_cotizacion');
        const valorProformaField = document.querySelector('input[name="valor_proforma"]');
        const proveedorSelect = document.querySelector('select[name="proveedor_id"]');
        const solicitadoPorSelect = document.querySelector('select[name="solicitado_por"]');
        const trabajoMessage = document.getElementById('trabajoMessage');

        let isInitialLoad = true;

        const formatCurrency = value => Number(value).toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Filtrar elementos del dropdown de cotizaciones
        const filterCotizaciones = (query) => {
            const q = (query || '').trim().toLowerCase();
            const items = cotizacionesListOptions ? cotizacionesListOptions.querySelectorAll('.cotizacion-option-item') : [];
            let visibleCount = 0;
            items.forEach(item => {
                const cotVal = (item.dataset.value || '').toLowerCase();
                const text = (item.textContent || '').toLowerCase();
                if (!q || cotVal.includes(q) || text.includes(q)) {
                    item.closest('li').style.display = '';
                    visibleCount++;
                } else {
                    item.closest('li').style.display = 'none';
                }
            });

            if (cotizacionesNoResults) {
                if (visibleCount === 0) {
                    cotizacionesNoResults.classList.remove('d-none');
                } else {
                    cotizacionesNoResults.classList.add('d-none');
                }
            }
        };

        const showDropdown = () => {
            if (cotizacionesDropdownMenu && !cotizacionesDropdownMenu.classList.contains('show')) {
                cotizacionesDropdownMenu.classList.add('show');
                if (btnToggleCotizaciones) {
                    btnToggleCotizaciones.setAttribute('aria-expanded', 'true');
                }
            }
        };

        const hideDropdown = () => {
            if (cotizacionesDropdownMenu && cotizacionesDropdownMenu.classList.contains('show')) {
                cotizacionesDropdownMenu.classList.remove('show');
                if (btnToggleCotizaciones) {
                    btnToggleCotizaciones.setAttribute('aria-expanded', 'false');
                }
            }
        };

        // Toggle manual con el botón de la flecha
        if (btnToggleCotizaciones) {
            btnToggleCotizaciones.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (cotizacionesDropdownMenu && cotizacionesDropdownMenu.classList.contains('show')) {
                    hideDropdown();
                } else {
                    filterCotizaciones(cotizacionInput ? cotizacionInput.value : '');
                    showDropdown();
                    if (cotizacionInput) cotizacionInput.focus();
                }
            });
        }

        // Eventos de entrada y búsqueda en cotizaciones (sin perder el foco)
        if (cotizacionInput) {
            cotizacionInput.addEventListener('focus', () => {
                filterCotizaciones(cotizacionInput.value);
                showDropdown();
            });

            cotizacionInput.addEventListener('input', () => {
                filterCotizaciones(cotizacionInput.value);
                showDropdown();
                updateTrabajoMode();
            });

            cotizacionInput.addEventListener('change', () => {
                handleCotizacionSelected(cotizacionInput.value.trim());
            });

            cotizacionInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    hideDropdown();
                    handleCotizacionSelected(cotizacionInput.value.trim());
                } else if (e.key === 'Escape') {
                    hideDropdown();
                }
            });
        }

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (cotizacionesDropdownMenu && 
                !cotizacionesDropdownMenu.contains(e.target) && 
                e.target !== cotizacionInput && 
                e.target !== btnToggleCotizaciones && 
                (!btnToggleCotizaciones || !btnToggleCotizaciones.contains(e.target))) {
                hideDropdown();
            }
        });

        // Selección de una cotización de la lista
        if (cotizacionesListOptions) {
            cotizacionesListOptions.addEventListener('click', (e) => {
                const option = e.target.closest('.cotizacion-option-item');
                if (!option) return;
                e.preventDefault();
                const cotNumero = option.dataset.value || '';
                const provId = option.dataset.proveedorId;
                const solicPor = option.dataset.solicitadoPor;

                cotizacionInput.value = cotNumero;
                hideDropdown();

                if (provId && provId !== '0' && proveedorSelect) {
                    proveedorSelect.value = provId;
                }
                if (solicPor && solicitadoPorSelect) {
                    solicitadoPorSelect.value = solicPor;
                }

                handleCotizacionSelected(cotNumero);
            });
        }

        // Clic en "Sin cotización (Mensualidad)"
        if (btnSelectMensualidad) {
            btnSelectMensualidad.addEventListener('click', (e) => {
                e.preventDefault();
                cotizacionInput.value = '';
                hideDropdown();
                handleCotizacionSelected('');
            });
        }

        const handleCotizacionSelected = (numero) => {
            if (!numero) {
                clearTrabajoOptions();
                updateTrabajoMode();
                return;
            }

            // Si coincide con alguna cotización existente en el listado, sincronizar proveedor/área
            if (cotizacionesListOptions) {
                const matchingItem = cotizacionesListOptions.querySelector(`.cotizacion-option-item[data-value="${CSS.escape(numero)}"]`);
                if (matchingItem) {
                    const provId = matchingItem.dataset.proveedorId;
                    const solicPor = matchingItem.dataset.solicitadoPor;
                    if (provId && provId !== '0' && proveedorSelect && (!proveedorSelect.value || proveedorSelect.value === '')) {
                        proveedorSelect.value = provId;
                    }
                    if (solicPor && solicitadoPorSelect && (!solicitadoPorSelect.value || solicitadoPorSelect.value === '')) {
                        solicitadoPorSelect.value = solicPor;
                    }
                }
            }

            fetchTrabajos(numero);
        };

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
                const asignada = trabajo.proforma_id ? ' (Ya asignado)' : '';
                option.textContent = `${trabajo.descripcion} — ${trabajo.proveedor_nombre} — L. ${formatCurrency(trabajo.valor)}${asignada}`;
                trabajoSelect.appendChild(option);
            });

            updateTrabajoMode();

            const selectedId = trabajoSelect.dataset.selected || '';
            if (selectedId) {
                trabajoSelect.value = selectedId;
                if (trabajoSelect.value === selectedId) {
                    applySelectedJob(false);
                }
            } else if (trabajos.length === 1) {
                trabajoSelect.selectedIndex = 1;
                applySelectedJob(true);
            }

            trabajoMessage.textContent = trabajos.length > 0
                ? `Se encontraron ${trabajos.length} trabajo${trabajos.length > 1 ? 's' : ''} para la cotización.`
                : 'No se encontraron trabajos para esa cotización.';

            isInitialLoad = false;
        };

        const applySelectedJob = (forceUpdateProforma = true) => {
            const selected = trabajoSelect.options[trabajoSelect.selectedIndex];
            if (!selected || !selected.value) {
                trabajoDescripcionField.value = '';
                valorCotizacionField.value = '';
                return;
            }
            const valor = selected.dataset.valor || '';
            const descripcion = selected.dataset.descripcion || selected.textContent || '';
            trabajoDescripcionField.value = descripcion;
            valorCotizacionField.value = valor;

            // Actualiza siempre el valor de proforma al seleccionar/cambiar un trabajo
            if (forceUpdateProforma || !valorProformaField.value || valorProformaField.value === '0.00' || valorProformaField.value === '') {
                if (valor && valorProformaField) {
                    valorProformaField.value = valor;
                }
            }
        };

        trabajoSelect.addEventListener('change', () => {
            applySelectedJob(true);
        });

        const valorCotizacionContainer = document.getElementById('valor_cotizacion_container');
        const trabajoSelectContainer = document.getElementById('trabajo_select_container');

        const updateTrabajoMode = () => {
            const tieneCotizacion = cotizacionInput.value.trim() !== '';
            const yaVinculadoAUnTrabajo = trabajoSelect.dataset.selected && trabajoSelect.dataset.selected !== '';

            if (!tieneCotizacion) {
                trabajoSelect.disabled = !yaVinculadoAUnTrabajo;
                if (trabajoSelectContainer) trabajoSelectContainer.style.display = 'none';
                trabajoManualContainer.style.display = 'block';
                cotizacionHelp.innerHTML = '<i class="bi bi-calendar-month me-1"></i>Modo Mensualidad: sin número de cotización.';
                trabajoMessage.textContent = 'Describe manualmente el trabajo mensualidad.';
                trabajoDescripcionField.value = trabajoManualInput.value.trim();
                if (trabajoManualInput.value.trim()) {
                    trabajoDescripcionField.value = trabajoManualInput.value.trim();
                }
                if (valorCotizacionContainer) {
                    valorCotizacionContainer.style.display = 'none';
                    valorCotizacionField.value = '';
                }
            } else {
                trabajoSelect.disabled = false;
                if (trabajoSelectContainer) trabajoSelectContainer.style.display = '';
                trabajoManualContainer.style.display = 'none';
                cotizacionHelp.innerHTML = '<i class="bi bi-info-circle me-1"></i>Escribe el número de cotización para filtrar o selecciona una de la lista.';
                if (!trabajoSelect.value) {
                    trabajoDescripcionField.value = '';
                }
                if (valorCotizacionContainer) {
                    valorCotizacionContainer.style.display = '';
                }
            }
        };

        const fetchTrabajos = numero => {
            if (!numero.trim()) {
                clearTrabajoOptions();
                updateTrabajoMode();
                return;
            }

            const searchBaseUrl = <?= json_encode(base_url('/proformas/cotizacion')) ?>;
            const url = searchBaseUrl + '?numero=' + encodeURIComponent(numero.trim());
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
    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // --- Múltiples documentos con acumulación y eliminación interactiva (Máx 2) ---
    const MAX_ARCHIVOS = 2;
    const MAX_MB = 5;
    const pdfInput = document.getElementById('pdfInput');
    const filePreviewList = document.getElementById('filePreviewList');
    const dropzoneTexto = document.getElementById('dropzoneTexto');
    const dropzoneIcon = document.getElementById('dropzoneIcon');

    const dropzoneContainer = document.getElementById('dropzoneContainer');

    // Documentos que YA están guardados en esta proforma (cuentan para el límite de 2)
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

        // Manejar los archivos que realmente se soltaron
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