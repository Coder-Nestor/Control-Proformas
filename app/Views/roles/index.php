<div class="container-fluid px-0">

    <!-- ============================================ -->
    <!-- ENCABEZADO                                   -->
    <!-- ============================================ -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary">
                <i class="bi bi-shield-lock me-2"></i>Roles y Permisos
            </h4>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Crea roles nuevos y decide qué puede hacer cada uno en el sistema
            </p>
        </div>
    </div>

<!-- ============================================ -->
<!-- FORMULARIO PARA CREAR ROL                    -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
        <h6 class="fw-bold text-secondary mb-0">
            <i class="bi bi-plus-circle me-2 text-primary"></i>Crear un rol nuevo
        </h6>
    </div>

    <div class="card-body pt-3 pb-4">
        <form method="POST" action="<?= base_url('/roles') ?>">
            <?= csrf_field() ?>

            <div class="row g-4 align-items-start">

                <!-- Nombre del rol -->
                <div class="col-lg-5">
                    <label for="nombre_rol" class="form-label fw-semibold mb-2">
                        Nombre para mostrar
                    </label>

                    <input type="text"
                           name="nombre"
                           id="nombre_rol"
                           class="form-control"
                           placeholder="Ej. Supervisor de Compras"
                           required>
                </div>

                <!-- Identificador -->
                <div class="col-lg-4">
                    <label for="slug_rol" class="form-label fw-semibold mb-2">
                        Identificador interno
                    </label>

                    <input type="text"
                           name="slug"
                           id="slug_rol"
                           class="form-control"
                           placeholder="Ej. supervisor_compras"
                           required>

                    <small class="text-muted d-block mt-1">
                        Solo letras, números y guion bajo — sin espacios ni tildes.
                    </small>
                </div>

                <!-- Botón -->
                <div class="col-lg-3 d-flex align-items-start">
                    <button class="btn btn-primary w-100 rounded-pill btn-crear-rol"
                            type="submit">
                        <i class="bi bi-plus-lg me-1"></i>
                        Crear rol
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
    <!-- ============================================ -->
    <!-- TABLA DE ROLES EXISTENTES                   -->
    <!-- ============================================ -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <!-- Cabecera -->
                <thead class="bg-light">
                    <tr>
                        <th class="fw-semibold text-secondary ps-3 py-3">Rol</th>
                        <th class="fw-semibold text-secondary py-3">Identificador</th>
                        <th class="fw-semibold text-secondary text-center py-3">Permisos asignados</th>
                        <th class="fw-semibold text-secondary text-center py-3">Usuarios con este rol</th>
                        <th class="fw-semibold text-secondary text-end pe-3 py-3">Acciones</th>
                    </tr>
                </thead>

                <!-- Cuerpo -->
                <tbody>
                    <?php foreach ($roles as $rIndex => $r): ?>
                        <?php 
                            $rowClass = $rIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle'; 
                            $isAdmin  = $r['slug'] === 'administrador';
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <!-- Nombre -->
                            <td class="ps-3 py-3">
                                <span class="fw-semibold"><?= e($r['nombre']) ?></span>
                            </td>

                            <!-- Slug -->
                            <td class="py-3">
                                <code><?= e($r['slug']) ?></code>
                            </td>

                            <!-- Total permisos -->
                            <td class="text-center py-3">
                                <span class="badge bg-primary-subtle text-primary fw-normal">
                                    <?= (int) $r['total_permisos'] ?> de 31
                                </span>
                            </td>

                            <!-- Total usuarios -->
                            <td class="text-center py-3">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal">
                                    <?= (int) $r['total_usuarios'] ?>
                                </span>
                            </td>

                            <!-- Acciones -->
                            <td class="text-end pe-3 py-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <!-- Botón permisos -->
                                    <a href="<?= base_url('/roles/' . $r['id'] . '/permisos') ?>" 
                                       class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-sliders me-1"></i>Permisos
                                    </a>

                                    <!-- Botón eliminar (solo si no es administrador) -->
                                    <?php if (!$isAdmin): ?>
                                        <form method="POST" 
                                              action="<?= base_url('/roles/' . $r['id'] . '/eliminar') ?>" 
                                              class="d-inline" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar este rol? Esta acción no se puede deshacer.');">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-outline-danger btn-sm rounded-pill" 
                                                    type="submit"
                                                    title="Eliminar rol">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Mensaje si no hay roles -->
                    <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No hay roles registrados aún.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<style>
.btn-crear-rol {
    margin-top: 31px;
    min-height: 38px;
}

</style>