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

    <?php if (Auth::can('proveedores.gestionar')): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary mb-0">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Agregar proveedor
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= base_url('/proveedores') ?>" class="row g-3 align-items-end">
                <?= csrf_field() ?>
                <div class="col-md-9">
                    <label class="form-label fw-semibold">Nombre</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                        <input type="text" name="nombre" class="form-control"  required>
                    </div>
                </div>
                <div class="col-md-3">
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
                            <?php if (Auth::can('proveedores.gestionar') || Auth::can('proveedores.eliminar')): ?>
                                <th class="fw-semibold text-secondary text-end pe-3 py-3">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($proveedores as $pIndex => $p): ?>
                        <?php $rowClass = $pIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle'; ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="ps-3 py-3">
                                <span class="fw-semibold"><i class="bi bi-building me-2 text-muted"></i><?= e($p['nombre']) ?></span>
                            </td>
                            <td class="py-3">
                                <span class="badge <?= $p['activo'] ? 'bg-success-subtle text-success' : 'bg-secondary bg-opacity-10 text-secondary' ?> fw-normal px-3 py-2">
                                    <?= $p['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <?php if (Auth::can('proveedores.gestionar') || Auth::can('proveedores.eliminar')): ?>
                            <td class="text-end pe-3 py-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if (Auth::can('proveedores.gestionar')): ?>
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm rounded-pill"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarProveedor"
                                            data-id="<?= (int) $p['id'] ?>"
                                            data-nombre="<?= e($p['nombre']) ?>"
                                            data-activo="<?= (int) $p['activo'] ?>"
                                            title="Editar nombre">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="<?= base_url('/proveedores/' . $p['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="nombre" value="<?= e($p['nombre']) ?>">
                                        <input type="hidden" name="activo" value="<?= $p['activo'] ? '0' : '1' ?>">
                                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                            <?= $p['activo'] ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if (Auth::can('proveedores.eliminar')): ?>
                                    <form method="POST" action="<?= base_url('/proveedores/' . $p['id'] . '/eliminar') ?>" class="d-inline" onsubmit="return confirm('¿Eliminar este proveedor?');">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-outline-danger btn-sm rounded-pill" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
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

<?php if (Auth::can('proveedores.gestionar')): ?>
<!-- Modal compartido para editar el nombre de un Proveedor -->
<div class="modal fade" id="modalEditarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEditarProveedor" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="activo" id="editarProveedorActivo" value="1">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2 text-primary"></i>Editar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Nombre</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-diagram-3"></i></span>
                        <input type="text" name="nombre" id="editarProveedorNombre" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill"><i class="bi bi-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalEditarProveedor');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        var id = btn.getAttribute('data-id');
        var nombre = btn.getAttribute('data-nombre');
        var activo = btn.getAttribute('data-activo');

        document.getElementById('formEditarProveedor').action = '<?= base_url('/proveedores') ?>/' + id;
        document.getElementById('editarProveedorNombre').value = nombre;
        document.getElementById('editarProveedorActivo').value = activo;
    });
});
</script>
<?php endif; ?>


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
</style>