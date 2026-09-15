<?php use Core\Auth; ?>
<div class="container-fluid px-0">
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>
                      <h4 class="mb-1 fw-bold text-primary">
                <i class="bi bi-truck me-2"></i>Proveedores
            </h4>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Administra los proveedores registrados en el sistema
            </p>
            
        </div>
    </div>

    <?php if (Auth::can('proveedores.crear')): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary mb-0">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Agregar proveedor
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= base_url('/proveedores') ?>" class="row g-3 align-items-start" id="formCrearProveedor">
                <?= csrf_field() ?>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                        <input type="text" name="nombre" id="crearProveedorNombre" class="form-control" placeholder="Ej. Mario Pérez" autocomplete="off" required>
                    </div>
                    <!-- Contenedor de Similitudes en Tiempo Real -->
                    <div id="similitudesCrearContainer" class="mt-2 d-none"></div>
                </div>
                <div class="col-md-4 pt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="habilitado_proforma" value="1" id="habilitadoProformaNuevo">
                        <label class="form-check-label small" for="habilitadoProformaNuevo">
                            ¿Puede pasar a Proforma?
                        </label>
                    </div>
                    <small class="text-muted d-block">Si no se marca, sus cotizaciones se quedan solo en Gestiones.</small>
                </div>
                <div class="col-md-2 pt-1">
                    <button class="btn btn-primary w-100 rounded-pill">
                        <i class="bi bi-plus-lg me-1"></i> Agregar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($proveedores)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <h5 class="fw-bold text-secondary mt-3">No hay proveedores registrados</h5>
                <p class="text-muted">Agrega el primero usando el formulario de arriba.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-semibold text-secondary ps-3 py-3">Nombre</th>
                            <th class="fw-semibold text-secondary py-3">Estado</th>
                            <th class="fw-semibold text-secondary py-3">¿Pasa a Proforma?</th>
                            <?php if (Auth::can('proveedores.editar') || Auth::can('proveedores.activar') || Auth::can('proveedores.eliminar')): ?>
                                <th class="fw-semibold text-secondary text-end pe-3 py-3">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($proveedores as $pIndex => $p): ?>
                        <?php $rowClass = $pIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle'; ?>
                        <tr class="<?= $rowClass ?>" id="fila-proveedor-<?= (int) $p['id'] ?>" style="transition: background-color 0.5s ease;">
                            <td class="ps-3 py-3">
                                <span class="fw-semibold"><i class="bi bi-building me-2 text-muted"></i><?= e($p['nombre']) ?></span>
                            </td>
                            <td class="py-3">
                                <span class="badge <?= $p['activo'] ? 'bg-success-subtle text-success' : 'bg-secondary bg-opacity-10 text-secondary' ?> fw-normal px-3 py-2">
                                    <?= $p['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="py-3">
                                <?php if (Auth::can('proveedores.activar')): ?>
                                    <form method="POST" action="<?= base_url('/proveedores/' . $p['id'] . '/toggle-proforma') ?>" class="d-inline m-0">
                                        <?= csrf_field() ?>
                                        <div class="form-check form-switch d-inline-flex align-items-center m-0">
                                            <input class="form-check-input my-0 me-2" type="checkbox" role="switch"
                                                   id="switch-proforma-<?= (int) $p['id'] ?>"
                                                   <?= !empty($p['habilitado_proforma']) ? 'checked' : '' ?>
                                                   onchange="this.form.submit()"
                                                   title="Clic para cambiar si puede pasar a Proforma"
                                                   style="cursor: pointer; width: 2.3em; height: 1.2em;">
                                            <label class="form-check-label small <?= !empty($p['habilitado_proforma']) ? 'text-primary fw-semibold' : 'text-muted' ?>"
                                                   for="switch-proforma-<?= (int) $p['id'] ?>" style="cursor: pointer;">
                                                <?= !empty($p['habilitado_proforma']) ? 'Sí' : 'No' ?>
                                            </label>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <?php if (!empty($p['habilitado_proforma'])): ?>
                                        <span class="badge bg-primary-subtle text-primary fw-normal px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i>Sí
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal px-3 py-2">
                                            <i class="bi bi-dash-circle me-1"></i>No
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <?php if (Auth::can('proveedores.editar') || Auth::can('proveedores.activar') || Auth::can('proveedores.eliminar')): ?>
                            <td class="text-end pe-3 py-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if (Auth::can('proveedores.editar')): ?>
                                    <button type="button"
                                             class="btn btn-outline-primary btn-sm rounded-pill"
                                             data-bs-toggle="modal"
                                             data-bs-target="#modalEditarProveedor"
                                             data-id="<?= (int) $p['id'] ?>"
                                             data-nombre="<?= e($p['nombre']) ?>"
                                             data-activo="<?= (int) $p['activo'] ?>"
                                             data-habilitado-proforma="<?= (int) ($p['habilitado_proforma'] ?? 0) ?>"
                                             title="Editar proveedor">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if (Auth::can('proveedores.activar')): ?>
                                    <form method="POST" action="<?= base_url('/proveedores/' . $p['id'] . '/toggle-estado') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn <?= $p['activo'] ? 'btn-outline-secondary' : 'btn-outline-success' ?> btn-sm rounded-pill px-3"
                                                title="<?= $p['activo'] ? 'Desactivar proveedor' : 'Activar proveedor' ?>">
                                            <?= $p['activo'] ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if (Auth::can('proveedores.eliminar')): ?>
                                        <?php 
                                            $totalGestiones = (int) ($p['total_gestiones'] ?? 0);
                                            $totalProformas = (int) ($p['total_proformas'] ?? 0);
                                            $totalUso = $totalGestiones + $totalProformas;
                                        ?>
                                        <?php if ($totalUso > 0): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-sm rounded-pill opacity-50" 
                                                    title="No se puede eliminar: tiene <?= $totalGestiones ?> gestión(es) y <?= $totalProformas ?> proforma(s) asociadas"
                                                    onclick="alert('No se puede eliminar al proveedor \'<?= e(addslashes($p['nombre'])) ?>\' porque tiene <?= $totalGestiones ?> gestión(es) y <?= $totalProformas ?> proforma(s) asociadas.\n\nPuedes desactivarlo en su lugar.');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" action="<?= base_url('/proveedores/' . $p['id'] . '/eliminar') ?>" class="d-inline" onsubmit="return confirm('¿Eliminar definitivamente el proveedor \'<?= e(addslashes($p['nombre'])) ?>\'?');">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-outline-danger btn-sm rounded-pill" title="Eliminar"><i class="bi bi-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top-0 py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-database me-1"></i>
                        Mostrando <?= count($proveedores) ?> proveedor(es)
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Última actualización: <?= date('d/m/Y H:i') ?>
                    </small>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (Auth::can('proveedores.crear')): ?>
<!-- Modal Simple de Confirmación para Agregar Proveedor -->
<div class="modal fade" id="modalConfirmarAgregarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="confirmarModalTitulo">
                    <i class="bi bi-question-circle text-primary me-2" id="confirmarModalIcono"></i>
                    <span id="confirmarModalTituloTexto">Agregar proveedor</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3" id="confirmarModalCuerpo">
                <p class="mb-0 text-secondary">¿Estás seguro que deseas agregar al siguiente proveedor?</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnConfirmarAgregarSubmit">
                    Sí, agregar
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (Auth::can('proveedores.editar')): ?>
<!-- Modal compartido para editar el nombre de un Proveedor -->
<div class="modal fade" id="modalEditarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEditarProveedor" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="activo" id="editarProveedorActivo" value="1">
                <input type="hidden" id="editarProveedorId" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2 text-primary"></i>Editar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Nombre</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-diagram-3"></i></span>
                        <input type="text" name="nombre" id="editarProveedorNombre" class="form-control" autocomplete="off" required>
                    </div>
                    <!-- Contenedor de Similitudes en Edición -->
                    <div id="similitudesEditarContainer" class="mt-2 d-none"></div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="habilitado_proforma" value="1" id="editarProveedorHabilitadoProforma">
                        <label class="form-check-label small" for="editarProveedorHabilitadoProforma">
                            ¿Puede pasar a Proforma?
                        </label>
                    </div>
                    <small class="text-muted d-block">Si no se marca, sus cotizaciones se quedan solo en Gestiones.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill"><i class="bi bi-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const urlSimilares = <?= json_encode(base_url('/proveedores/similares')) ?>;
    let ultimosSimilares = [];

    // Función para resaltar la fila en la tabla
    window.resaltarProveedor = function(id) {
        const fila = document.getElementById('fila-proveedor-' + id);
        if (fila) {
            fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
            fila.classList.add('table-warning');
            setTimeout(() => {
                fila.classList.remove('table-warning');
            }, 2500);
        }
    };

    function renderizarSimilitudes(container, data, nombreOriginal = '') {
        if (!container) return;

        if (!data || !data.similares || data.similares.length === 0) {
            if (nombreOriginal.trim().length >= 3) {
                container.className = 'mt-2 p-2 rounded-3 bg-success-subtle text-success small border border-success-subtle';
                container.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Nombre disponible. No se detectaron proveedores similares.';
                container.classList.remove('d-none');
            } else {
                container.className = 'mt-2 d-none';
                container.innerHTML = '';
            }
            return;
        }

        const exacto = data.similares.find(s => s.score >= 100);
        const tieneMuySimilar = data.similares.some(s => s.score >= 80);

        let bgClass = exacto ? 'bg-danger-subtle border-danger-subtle' : (tieneMuySimilar ? 'bg-warning-subtle border-warning-subtle' : 'bg-light border');
        container.className = `mt-2 p-2 rounded-3 border ${bgClass}`;

        let headerHtml = '';
        if (exacto) {
            headerHtml = `
                <div class="d-flex align-items-center text-danger fw-bold small mb-2">
                    <i class="bi bi-exclamation-octagon-fill me-2 fs-6"></i>
                    <span>¡Atención! Ya existe un proveedor exactamente con este nombre:</span>
                </div>`;
        } else {
            headerHtml = `
                <div class="d-flex align-items-center text-dark fw-semibold small mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-warning fs-6"></i>
                    <span>Proveedores similares encontrados (${data.similares.length}):</span>
                </div>`;
        }

        let itemsHtml = '<div class="d-flex flex-column gap-1">';
        data.similares.forEach(p => {
            let scoreBadgeClass = 'bg-info-subtle text-info-emphasis';
            if (p.score >= 100) {
                scoreBadgeClass = 'bg-danger text-white';
            } else if (p.score >= 80) {
                scoreBadgeClass = 'bg-warning text-dark';
            }

            const activoBadge = p.activo == 1 
                ? '<span class="badge bg-success-subtle text-success fw-normal">Activo</span>' 
                : '<span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal">Inactivo</span>';

            const proformaBadge = p.habilitado_proforma == 1
                ? '<span class="badge bg-primary-subtle text-primary fw-normal">Pasa a Proforma</span>'
                : '<span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal">Solo Gestiones</span>';

            itemsHtml += `
                <div class="p-2 bg-white rounded-2 border d-flex justify-content-between align-items-center shadow-xs">
                    <div class="me-2">
                        <div class="fw-bold text-dark small">
                            <i class="bi bi-building me-1 text-secondary"></i>${escapeHtml(p.nombre)}
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <span class="badge ${scoreBadgeClass} fw-normal">${escapeHtml(p.motivo)} (${p.score}%)</span>
                            ${activoBadge}
                            ${proformaBadge}
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0" 
                            onclick="resaltarProveedor(${p.id})" title="Ubicar en la tabla">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>`;
        });
        itemsHtml += '</div>';

        container.innerHTML = headerHtml + itemsHtml;
        container.classList.remove('d-none');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Modal de Confirmación al Agregar Proveedor
    const formCrear = document.getElementById('formCrearProveedor');
    const inputCrear = document.getElementById('crearProveedorNombre');
    const checkHabilitado = document.getElementById('habilitadoProformaNuevo');
    const modalConfirmarElement = document.getElementById('modalConfirmarAgregarProveedor');
    let modalConfirmarInstance = null;
    let confirmadoParaEnviar = false;

    if (formCrear && inputCrear && modalConfirmarElement) {
        modalConfirmarInstance = new bootstrap.Modal(modalConfirmarElement);

        formCrear.addEventListener('submit', async function (e) {
            if (confirmadoParaEnviar) {
                return;
            }

            e.preventDefault();

            const nombre = inputCrear.value.trim();
            if (!nombre) {
                inputCrear.focus();
                return;
            }

            // Consultar similitudes por si no se disparó el debounce
            let similares = ultimosSimilares;
            try {
                const res = await fetch(urlSimilares + '?nombre=' + encodeURIComponent(nombre));
                const data = await res.json();
                similares = data.similares || [];
                ultimosSimilares = similares;
            } catch (err) {
                // usar ultimosSimilares
            }

            const exacto = similares.find(s => s.score >= 100);

            const modalTitulo = document.getElementById('confirmarModalTitulo');
            const modalIcono = document.getElementById('confirmarModalIcono');
            const modalTituloTexto = document.getElementById('confirmarModalTituloTexto');
            const modalCuerpo = document.getElementById('confirmarModalCuerpo');
            const btnSubmit = document.getElementById('btnConfirmarAgregarSubmit');

            if (exacto) {
                // Caso 1: El proveedor ya existe en el sistema
                modalTitulo.className = 'modal-title fw-bold text-danger';
                modalIcono.className = 'bi bi-exclamation-triangle-fill text-danger me-2';
                modalTituloTexto.textContent = 'Proveedor ya existe';

                modalCuerpo.innerHTML = `
                    <div class="alert alert-danger py-2 px-3 mb-3 rounded-3">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i> Este proveedor ya existe en el sistema como <strong>${escapeHtml(exacto.nombre)}</strong>.
                    </div>
                    <p class="mb-0 text-secondary">¿Estás seguro que deseas agregarlo?</p>`;

                btnSubmit.className = 'btn btn-danger rounded-pill px-4';
                btnSubmit.textContent = 'Sí, agregar';
            } else {
                // Caso 2: Es un proveedor diferente / nuevo
                modalTitulo.className = 'modal-title fw-bold text-primary';
                modalIcono.className = 'bi bi-question-circle text-primary me-2';
                modalTituloTexto.textContent = 'Agregar proveedor';

                modalCuerpo.innerHTML = `
                    <p class="mb-0 text-secondary">¿Estás seguro que deseas agregar al proveedor <strong>${escapeHtml(nombre)}</strong>?</p>`;

                btnSubmit.className = 'btn btn-primary rounded-pill px-4';
                btnSubmit.textContent = 'Sí, agregar';
            }

            modalConfirmarInstance.show();
        });

        const btnConfirmar = document.getElementById('btnConfirmarAgregarSubmit');
        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', function () {
                confirmadoParaEnviar = true;
                modalConfirmarInstance.hide();
                formCrear.submit();
            });
        }
    }

    // Live search en Agregar Proveedor
    const containerCrear = document.getElementById('similitudesCrearContainer');

    if (inputCrear && containerCrear) {
        const consultarCrear = debounce(function() {
            const query = inputCrear.value.trim();
            if (query.length < 2) {
                ultimosSimilares = [];
                containerCrear.className = 'mt-2 d-none';
                containerCrear.innerHTML = '';
                return;
            }

            fetch(urlSimilares + '?nombre=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    ultimosSimilares = data.similares || [];
                    renderizarSimilitudes(containerCrear, data, query);
                })
                .catch(() => {
                    ultimosSimilares = [];
                    containerCrear.className = 'mt-2 d-none';
                });
        }, 220);

        inputCrear.addEventListener('input', consultarCrear);
    }

    // Modal Editar Proveedor
    const modal = document.getElementById('modalEditarProveedor');
    if (modal) {
        const inputEditar = document.getElementById('editarProveedorNombre');
        const inputEditarId = document.getElementById('editarProveedorId');
        const containerEditar = document.getElementById('similitudesEditarContainer');

        modal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            const activo = btn.getAttribute('data-activo');
            const habilitadoProforma = btn.getAttribute('data-habilitado-proforma');

            document.getElementById('formEditarProveedor').action = '<?= base_url('/proveedores') ?>/' + id;
            inputEditar.value = nombre;
            if (inputEditarId) inputEditarId.value = id;
            document.getElementById('editarProveedorActivo').value = activo;
            document.getElementById('editarProveedorHabilitadoProforma').checked = (habilitadoProforma === '1');
            
            if (containerEditar) {
                containerEditar.className = 'mt-2 d-none';
                containerEditar.innerHTML = '';
            }
        });

        if (inputEditar && containerEditar) {
            const consultarEditar = debounce(function() {
                const query = inputEditar.value.trim();
                const idExcluir = inputEditarId ? inputEditarId.value : '';

                if (query.length < 2) {
                    containerEditar.className = 'mt-2 d-none';
                    containerEditar.innerHTML = '';
                    return;
                }

                fetch(urlSimilares + '?nombre=' + encodeURIComponent(query) + '&excluir_id=' + encodeURIComponent(idExcluir))
                    .then(res => res.json())
                    .then(data => {
                        renderizarSimilitudes(containerEditar, data, query);
                    })
                    .catch(() => {
                        containerEditar.className = 'mt-2 d-none';
                    });
            }, 220);

            inputEditar.addEventListener('input', consultarEditar);
        }
    }
});
</script>

<style>
    .card { border-radius: 12px !important; overflow: hidden; }
    .card-header { padding: 1rem 1.25rem 0.5rem 1.25rem; background-color: transparent; border-bottom: 1px solid rgba(0,0,0,0.05); }
    .badge { font-weight: 500; }
    .btn.rounded-pill { border-radius: 50px !important; }
    .table > thead { border-bottom: 2px solid #e9ecef; }
    .table-hover > tbody > tr:hover { background-color: rgba(13, 110, 253, 0.04) !important; }
    .input-group-text { background-color: #f8f9fa; border-right: none; }
    .input-group .form-control { border-left: none; }
    .input-group .form-control:focus { border-left: none; box-shadow: none; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
</style>