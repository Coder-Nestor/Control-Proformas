<?php use Core\Auth; ?>
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary">
                <i class="bi bi-people me-2"></i>Usuarios
            </h4>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Administra los accesos al sistema
            </p>
        </div>
        <?php if (Auth::can('usuarios.crear')): ?>
        <div class="mt-2 mt-sm-0">
            <a href="<?= base_url('/usuarios/crear') ?>" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-person-plus me-2"></i>Nuevo usuario
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (empty($usuarios)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <h5 class="fw-bold text-secondary mt-3">No hay usuarios registrados</h5>
                <p class="text-muted">Crea el primero con el botón de arriba.</p>
                <?php if (Auth::can('usuarios.crear')): ?>
                <a href="<?= base_url('/usuarios/crear') ?>" class="btn btn-primary rounded-pill px-4 mt-2">
                    <i class="bi bi-person-plus me-2"></i>Crear primer usuario
                </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-semibold text-secondary ps-3 py-3">Nombre</th>
                            <th class="fw-semibold text-secondary py-3">Correo</th>
                            <th class="fw-semibold text-secondary py-3">Rol</th>
                            <th class="fw-semibold text-secondary py-3">Área</th>
                            <th class="fw-semibold text-secondary py-3">Estado</th>
                            <th class="fw-semibold text-secondary text-end pe-3 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($usuarios as $uIndex => $u): ?>
                        <?php $rowClass = $uIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle'; ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="ps-3 py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.85rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span class="fw-semibold"><?= e($u['nombre']) ?></span>
                                </div>
                            </td>
                            <td class="py-3 text-muted"><?= e($u['email']) ?></td>
                            <td class="py-3">
                                <span class="badge bg-primary-subtle text-primary fw-normal px-3 py-2"><?= e($u['rol_nombre']) ?></span>
                            </td>
                            <td class="py-3"><?= e($u['area'] ?? '—') ?></td>
                            <td class="py-3">
                                <span class="badge <?= $u['activo'] ? 'bg-success-subtle text-success' : 'bg-secondary bg-opacity-10 text-secondary' ?> fw-normal px-3 py-2">
                                    <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="text-end pe-3 py-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if (Auth::can('usuarios.editar')): ?>
                                        <a href="<?= base_url('/usuarios/' . $u['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm rounded-pill" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <?php endif; ?>

                                    <?php if (Auth::can('usuarios.activar')): ?>
                                        <?php if ((int)$u['id'] === (int)Auth::id()): ?>
                                            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled title="No puedes desactivar tu propia cuenta">
                                                <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        <?php elseif ($u['activo']): ?>
                                            <form method="POST" action="<?= base_url('/usuarios/' . $u['id'] . '/toggle-estado') ?>" class="d-inline" onsubmit="return confirm('¿Desactivar a <?= e(addslashes($u['nombre'])) ?>? No podrá iniciar sesión.');">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Desactivar usuario">
                                                    Desactivar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?= base_url('/usuarios/' . $u['id'] . '/toggle-estado') ?>" class="d-inline" onsubmit="return confirm('¿Activar a <?= e(addslashes($u['nombre'])) ?>? Podrá iniciar sesión nuevamente.');">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-outline-success btn-sm rounded-pill px-3" title="Activar usuario">
                                                    Activar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (!Auth::can('usuarios.editar') && !Auth::can('usuarios.activar')): ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top-0 py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-database me-1"></i>
                        Mostrando <?= count($usuarios) ?> usuario(s)
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

<style>
    .card { border-radius: 12px !important; overflow: hidden; }
    .badge { font-weight: 500; }
    .btn.rounded-pill { border-radius: 50px !important; }
    .table > thead { border-bottom: 2px solid #e9ecef; }
    .table-hover > tbody > tr:hover { background-color: rgba(13, 110, 253, 0.04) !important; }
</style>