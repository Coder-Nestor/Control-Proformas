<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>403 - Acceso denegado</title>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="text-center">
        <h1 class="display-4 fw-bold text-danger">403</h1>
        <p class="lead">No tienes permisos para acceder a esta sección.</p>
        <a href="<?= base_url('/') ?>" class="btn btn-primary">Ir al inicio</a>
    </div>
</body>
</html>
