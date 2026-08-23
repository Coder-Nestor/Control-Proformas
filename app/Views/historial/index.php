<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary">
                <i class="bi bi-clock-history me-2"></i>Historial / Auditoría
            </h4>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Todo lo que se ha creado, editado y eliminado en el sistema — incluso si el registro original ya no existe
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= base_url('/historial') ?>" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Módulo</label>
                    <select name="entidad" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($entidades as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $filtros['entidad'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Usuario</label>
                    <select name="usuario_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= (int) $u['id'] ?>" <?= (string) $filtros['usuario_id'] === (string) $u['id'] ? 'selected' : '' ?>><?= e($u['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Desde</label>
                    <input type="date" name="fecha_desde" value="<?= e($filtros['fecha_desde']) ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Hasta</label>
                    <input type="date" name="fecha_hasta" value="<?= e($filtros['fecha_hasta']) ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary btn-sm rounded-pill flex-grow-1" title="Filtrar">
                        <i class="bi bi-funnel-fill me-1"></i>Filtrar
                    </button>
                    <a href="<?= base_url('/historial') ?>" class="btn btn-outline-secondary btn-sm rounded-pill" title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
                <div class="col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="buscar" value="<?= e($filtros['buscar']) ?>" class="form-control border-start-0" placeholder="Buscar en la acción (ej. 'Creó', 'Eliminó', 'Actualizó')...">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($registros)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <h6 class="fw-bold text-secondary mt-3">No hay movimientos con estos filtros</h6>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-semibold text-secondary ps-3 py-3">Fecha</th>
                            <th class="fw-semibold text-secondary py-3">Módulo</th>
                            <th class="fw-semibold text-secondary py-3">Acción</th>
                            <th class="fw-semibold text-secondary py-3">Usuario</th>
                            <th class="fw-semibold text-secondary text-end pe-3 py-3">Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registros as $rIndex => $r): ?>
                        <?php
                            $rowClass = $rIndex % 2 === 0 ? 'bg-white' : 'bg-light-subtle';
                            $esEliminacion = stripos($r['accion'], 'elimin') !== false;
                            $existe = (int) $r['existe'] > 0;
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="ps-3 py-3 text-nowrap">
                                <i class="bi bi-clock me-1 text-muted"></i><?= date('d/m/Y H:i', strtotime($r['creado_en'])) ?>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-primary-subtle text-primary fw-normal"><?= e($entidades[$r['entidad']] ?? $r['entidad']) ?></span>
                            </td>
                            <td class="py-3">
                                <?php if ($esEliminacion): ?>
                                    <i class="bi bi-trash text-danger me-1"></i>
                                <?php else: ?>
                                    <i class="bi bi-pencil text-muted me-1"></i>
                                <?php endif; ?>
                                <?= e($r['accion']) ?>
                            </td>
                            <td class="py-3">
                                <i class="bi bi-person me-1 text-muted"></i><?= e($r['usuario_nombre'] ?? 'Sistema') ?>
                            </td>
                            <td class="text-end pe-3 py-3">
                                <?php if ($existe && isset($rutas[$r['entidad']])): ?>
                                    <a href="<?= base_url($rutas[$r['entidad']] . (int) $r['entidad_id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i>Ver
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal px-3 py-2">
                                        <i class="bi bi-slash-circle me-1"></i>Ya no existe
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top-0 py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted">
                        <i class="bi bi-database me-1"></i>
                        Mostrando <?= count($registros) ?> de <?= (int) $paginacion['total_registros'] ?> movimiento(s)
                        <?php if ($paginacion['total_paginas'] > 1): ?>
                            &middot; Página <?= (int) $paginacion['pagina_actual'] ?> de <?= (int) $paginacion['total_paginas'] ?>
                        <?php endif; ?>
                    </small>

                    <?php if ($paginacion['total_paginas'] > 1): ?>
                        <?php
                            $paginaActual = (int) $paginacion['pagina_actual'];
                            $totalPaginas = (int) $paginacion['total_paginas'];
                            $buildUrl = function ($pagina) use ($filtros) {
                                $params = $filtros;
                                $params['pagina'] = $pagina;
                                $params = array_filter($params, fn($v) => $v !== null && $v !== '');
                                return base_url('/historial') . '?' . http_build_query($params);
                            };
                            $rango = 2;
                            $inicio = max(1, $paginaActual - $rango);
                            $fin = min($totalPaginas, $paginaActual + $rango);
                        ?>
                        <nav aria-label="Paginación de historial">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $paginaActual > 1 ? $buildUrl($paginaActual - 1) : '#' ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $buildUrl($i) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $paginaActual < $totalPaginas ? $buildUrl($paginaActual + 1) : '#' ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
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
    .pagination .page-link { color: #0d6efd; }
    .pagination .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; }
</style>
