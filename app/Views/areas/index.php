<?php use Core\Auth; ?>
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary">
                <i class="bi bi-diagram-3 me-2"></i>Áreas
            </h4>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Catálogo de áreas solicitantes (Finca, Logística, Operaciones...)
            </p>
        </div>
    </div>

    <?php if (Auth::hasRole(['administrador'])): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <h6 class="fw-bold text-secondary mb-0">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Agregar área
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= base_url('/areas') ?>" class="row g-3 align-items-end">
                <?= csrf_field() ?>
                <div class="col-md-9">
                    <label class="form-label fw-semibold">Nombre</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-diagram-3"></i></span>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Mantenimiento" required>
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

    <?php if (empty($areas)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <h5 class="fw-bold text-secondary mt-3">No hay áreas registradas</h5>
                <p class="text-muted">Agrega la primera usando el formulario de arriba.</p>
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
                            <?php if (Auth::hasRole(['administrador'])): ?>
                                <th class="fw-semibold text-secondary text-end pe-3 py-3">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($areas as $aIndex => $a): ?>
                        <?php $rowClass = $aIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle'; ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="ps-3 py-3">
                                <span class="fw-semibold"><i class="bi bi-diagram-3 me-2 text-muted"></i><?= e($a['nombre']) ?></span>
                            </td>
                            <td class="py-3">
                                <span class="badge <?= $a['activo'] ? 'bg-success-subtle text-success' : 'bg-secondary bg-opacity-10 text-secondary' ?> fw-normal px-3 py-2">
                                    <?= $a['activo'] ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                            <?php if (Auth::hasRole(['administrador'])): ?>
                            <td class="text-end pe-3 py-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm rounded-pill"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarArea"
                                            data-id="<?= (int) $a['id'] ?>"
                                            data-nombre="<?= e($a['nombre']) ?>"
                                            data-activo="<?= (int) $a['activo'] ?>"
                                            title="Editar nombre">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="<?= base_url('/areas/' . $a['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="nombre" value="<?= e($a['nombre']) ?>">
                                        <input type="hidden" name="activo" value="<?= $a['activo'] ? '0' : '1' ?>">
                                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                            <?= $a['activo'] ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= base_url('/areas/' . $a['id'] . '/eliminar') ?>" class="d-inline" onsubmit="return confirm('¿Eliminar esta área? Las proformas o usuarios que ya la usaban conservan el nombre, solo se quita del catálogo para futuras selecciones.');">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-outline-danger btn-sm rounded-pill" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top-0 py-2">
                <small class="text-muted">
                    <i class="bi bi-database me-1"></i>
                    Mostrando <?= count($areas) ?> área(s)
                </small>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (Auth::hasRole(['administrador'])): ?>
<!-- Modal compartido para editar el nombre de un área -->
<div class="modal fade" id="modalEditarArea" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEditarArea" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="activo" id="editarAreaActivo" value="1">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2 text-primary"></i>Editar área</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Nombre</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-diagram-3"></i></span>
                        <input type="text" name="nombre" id="editarAreaNombre" class="form-control" required>
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
    var modal = document.getElementById('modalEditarArea');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        var id = btn.getAttribute('data-id');
        var nombre = btn.getAttribute('data-nombre');
        var activo = btn.getAttribute('data-activo');

        document.getElementById('formEditarArea').action = '<?= base_url('/areas') ?>/' + id;
        document.getElementById('editarAreaNombre').value = nombre;
        document.getElementById('editarAreaActivo').value = activo;
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