<?php
$u = $usuario ?? [];
$isEdit = !empty($u);
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold text-primary">
                <i class="bi bi-<?= $isEdit ? 'person-gear' : 'person-plus' ?> me-2"></i><?= $isEdit ? 'Editar usuario' : 'Nuevo usuario' ?>
            </h4>
            <small class="text-muted"><?= $isEdit ? 'Actualiza los datos de acceso' : 'Crea un nuevo acceso al sistema' ?></small>
        </div>
        <a href="<?= base_url('/usuarios') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <form method="POST" action="<?= $isEdit ? base_url('/usuarios/' . $u['id']) : base_url('/usuarios') ?>" style="max-width: 700px;">
        <?= csrf_field() ?>

        <!-- Sección: Datos de la cuenta -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold text-secondary">
                    <i class="bi bi-person-badge me-2 text-primary"></i>Datos de la cuenta
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" name="nombre" value="<?= e($u['nombre'] ?? '') ?>" class="form-control" placeholder="Ej. María Rodríguez" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" value="<?= e($u['email'] ?? '') ?>" class="form-control" placeholder="correo@empresa.com" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Contraseña <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="passwordInput" class="form-control" <?= $isEdit ? '' : 'required' ?> minlength="6" placeholder="Mínimo 6 caracteres">
                            <button type="button" class="btn btn-outline-secondary" id="btnTogglePassword" tabindex="-1" title="Mostrar contraseña">
                                <i class="bi bi-eye" id="iconTogglePassword"></i>
                            </button>
                        </div>
                        <?php if ($isEdit): ?>
                            <small class="text-muted">Deja en blanco si no quieres cambiarla.</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Permisos -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold text-secondary">
                    <i class="bi bi-shield-check me-2 text-primary"></i>Permisos y área
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-shield"></i></span>
                            <select name="rol_id" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= (int) $r['id'] ?>" <?= (string) ($u['rol_id'] ?? '') === (string) $r['id'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Área (opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-diagram-3"></i></span>
                            <select name="area" class="form-select">
                                <option value="">— Ninguna —</option>
                                <?php foreach ($areas as $a): ?>
                                    <option value="<?= e($a['nombre']) ?>" <?= ($u['area'] ?? '') === $a['nombre'] ? 'selected' : '' ?>><?= e($a['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if ($isEdit): ?>
                    <div class="col-12">
                        <div class="bg-light rounded-3 p-3 d-flex align-items-center justify-content-between mt-1">
                            <div>
                                <div class="fw-semibold"><i class="bi bi-toggle-on me-2 text-primary"></i>Usuario activo</div>
                                <small class="text-muted">Si lo desactivas, no podrá iniciar sesión.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo" role="switch" style="width: 3em; height: 1.5em;" <?= !empty($u['activo']) ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex gap-2 mb-5">
            <button class="btn btn-primary px-4 py-2 rounded-pill">
                <i class="bi bi-save me-2"></i> Guardar
            </button>
            <a href="<?= base_url('/usuarios') ?>" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                Cancelar
            </a>
        </div>
    </form>
</div>

<style>
    .card { border-radius: 12px !important; overflow: hidden; }
    .card-header { padding: 1rem 1.25rem 0.5rem 1.25rem; background-color: transparent; border-bottom: 1px solid rgba(0,0,0,0.05); }
    .form-label { font-size: 0.85rem; margin-bottom: 0.25rem; }
    .input-group-text { border: 1px solid #ced4da; border-right: none; background-color: #f8f9fa; }
    .input-group .form-control, .input-group .form-select { border-left: none; }
    .input-group .form-control:focus, .input-group .form-select:focus { border-left: none; box-shadow: none; }
    .btn.rounded-pill { border-radius: 50px !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnToggle = document.getElementById('btnTogglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const icon = document.getElementById('iconTogglePassword');

    if (btnToggle && passwordInput && icon) {
        btnToggle.addEventListener('click', function () {
            const seVaAMostrar = passwordInput.type === 'password';
            passwordInput.type = seVaAMostrar ? 'text' : 'password';
            icon.className = seVaAMostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
            btnToggle.title = seVaAMostrar ? 'Ocultar contraseña' : 'Mostrar contraseña';
        });
    }
});
</script>