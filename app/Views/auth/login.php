<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Control de Proformas y Facturas</title>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="login-wrapper">
    <div class="card login-card p-4" style="width: 100%; max-width: 400px;">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="<?= asset('img/logo.png') ?>" alt="Azucarera Choluteca" style="height: 140px; width: auto;">
                <h4 class="mt-2 mb-0">Control de Proformas</h4>
                <small class="text-muted">y Facturas</small>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= base_url('/login') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
                </button>
            </form>
        </div>
    </div>
</div>
<script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
