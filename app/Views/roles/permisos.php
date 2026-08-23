<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary">
                <i class="bi bi-sliders me-2"></i>Permisos de "<?= e($rol['nombre']) ?>"
            </h4>
            <p class="text-muted small mb-0">Marca lo que este rol SÍ puede hacer en el sistema</p>
        </div>
        <a href="<?= base_url('/roles') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <form method="POST" action="<?= base_url('/roles/' . $rol['id'] . '/permisos') ?>">
        <?= csrf_field() ?>

        <?php foreach ($catalogoPorModulo as $modulo => $permisos): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-secondary mb-0"><?= e($modulo) ?></h6>
                    <div>
                        <button type="button" class="btn btn-link btn-sm p-0 me-2 btn-marcar-todos" data-modulo="<?= e($modulo) ?>">Marcar todos</button>
                        <button type="button" class="btn btn-link btn-sm p-0 btn-desmarcar-todos" data-modulo="<?= e($modulo) ?>">Ninguno</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php foreach ($permisos as $p): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-check">
                                    <input class="form-check-input permiso-checkbox mod-<?= e($modulo) ?>" type="checkbox" name="permisos[]"
                                           value="<?= (int) $p['id'] ?>" id="permiso<?= (int) $p['id'] ?>"
                                           <?= in_array((int) $p['id'], $permisosActuales, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="permiso<?= (int) $p['id'] ?>">
                                        <?= e($p['descripcion']) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-flex gap-2 mb-5">
            <button class="btn btn-primary px-4 py-2 rounded-pill"><i class="bi bi-save me-2"></i>Guardar permisos</button>
            <a href="<?= base_url('/roles') ?>" class="btn btn-outline-secondary px-4 py-2 rounded-pill">Cancelar</a>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.btn-marcar-todos').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.mod-' + CSS.escape(btn.dataset.modulo)).forEach(function (cb) { cb.checked = true; });
    });
});
document.querySelectorAll('.btn-desmarcar-todos').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.mod-' + CSS.escape(btn.dataset.modulo)).forEach(function (cb) { cb.checked = false; });
    });
});
</script>
