document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnToggleSidebar');
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebarBackdrop');

    function esCelular() {
        // Coincide con el breakpoint "md" de Bootstrap (767.98px) usado en app.css
        return window.innerWidth < 768;
    }

    // Un solo botón, dos comportamientos según el ancho real de pantalla:
    // - En celular: abre/cierra el sidebar como cajón, con fondo oscuro detrás.
    // - En tablet/escritorio: solo colapsa el sidebar a una franja de íconos
    //   (el sidebar sigue siempre visible, nunca se oculta).
    function toggleSidebar() {
        var abriendo = !sidebar.classList.contains('sidebar-toggled');
        sidebar.classList.toggle('sidebar-toggled');

        if (esCelular()) {
            if (backdrop) backdrop.classList.toggle('show', abriendo);
            document.body.style.overflow = abriendo ? 'hidden' : '';
        }
    }

    // Solo cierra el cajón en celular (en tablet/escritorio el sidebar
    // nunca se oculta, así que no hay nada que "cerrar" al navegar).
    function cerrarSiEsCelular() {
        if (esCelular()) {
            sidebar.classList.remove('sidebar-toggled');
            if (backdrop) backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    if (btn && sidebar) {
        btn.addEventListener('click', toggleSidebar);
    }

    if (backdrop) {
        backdrop.addEventListener('click', cerrarSiEsCelular);
    }

    // Al elegir una opción del menú en celular, cierra el cajón automáticamente.
    if (sidebar) {
        sidebar.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', cerrarSiEsCelular);
        });
    }
});

/**
 * Inicializa los 3 gráficos del dashboard usando Chart.js (servido localmente).
 * Recibe los datos ya pintados por PHP en window.DASHBOARD_DATA (ver dashboard/index.php)
 * y opcionalmente los refresca vía fetch a /dashboard/data.
 */
function initDashboardCharts(data) {
    function mostrarError(canvasId, mensaje) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error('Dashboard: no existe el elemento #' + canvasId + ' en la página.');
            return;
        }
        var contenedor = canvas.parentElement;
        contenedor.innerHTML =
            '<div class="d-flex align-items-center justify-content-center h-100 text-danger small text-center p-2">' +
            '<div><i class="bi bi-exclamation-triangle d-block mb-1" style="font-size:1.3rem;"></i>' + mensaje + '</div></div>';
    }

    function dibujarDoughnut(canvasId, bloque) {
        var el = document.getElementById(canvasId);
        if (!el) {
            return; // este gráfico no está en esta página, no es un error
        }
        if (typeof Chart === 'undefined') {
            mostrarError(canvasId, 'No se cargó la librería de gráficos (Chart.js). Revisa que el archivo exista en public/assets/vendor/chartjs/chart.js');
            return;
        }
        if (!bloque || !bloque.labels || !bloque.values) {
            mostrarError(canvasId, 'No llegaron datos desde el servidor para este gráfico.');
            return;
        }
        if (bloque.values.every(function (v) { return !v; })) {
            el.parentElement.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted small">Todavía no hay datos suficientes.</div>';
            return;
        }
        try {
            new Chart(el, {
                type: 'doughnut',
                data: {
                    labels: bloque.labels,
                    datasets: [{ data: bloque.values, backgroundColor: ['#198754', '#ffc107', '#dc3545'] }]
                },
                options: { plugins: { legend: { position: 'bottom' } }, maintainAspectRatio: false }
            });
        } catch (err) {
            console.error('Error al dibujar ' + canvasId + ':', err);
            mostrarError(canvasId, 'Error al dibujar el gráfico: ' + err.message);
        }
    }

    dibujarDoughnut('chartFacturaEstado', data.facturaEstado);
    dibujarDoughnut('chartEstados', data.ocEstado);

    var elProveedores = document.getElementById('chartProveedores');
    if (elProveedores && data.porProveedor) {
        try {
            new Chart(elProveedores, {
                type: 'bar',
                data: {
                    labels: data.porProveedor.labels,
                    datasets: [{ label: 'Monto (L.)', data: data.porProveedor.values, backgroundColor: '#0d6efd' }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        } catch (err) {
            console.error('Error al dibujar chartProveedores:', err);
            mostrarError('chartProveedores', 'Error al dibujar el gráfico: ' + err.message);
        }
    }

    var elTendencia = document.getElementById('chartTendencia');
    if (elTendencia) {
        if (typeof Chart === 'undefined') {
            mostrarError('chartTendencia', 'No se cargó la librería de gráficos (Chart.js).');
        } else if (!data.tendencia || !data.tendencia.labels || !data.tendencia.labels.length) {
            elTendencia.parentElement.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted small">Todavía no hay suficientes gestiones registradas para mostrar una tendencia.</div>';
        } else {
            try {
                new Chart(elTendencia, {
                    type: 'line',
                    data: {
                        labels: data.tendencia.labels,
                        datasets: [{
                            label: 'Gestiones por mes',
                            data: data.tendencia.values,
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25,135,84,.15)',
                            tension: .3,
                            fill: true
                        }]
                    },
                    options: { plugins: { legend: { display: false } }, maintainAspectRatio: false }
                });
            } catch (err) {
                console.error('Error al dibujar chartTendencia:', err);
                mostrarError('chartTendencia', 'Error al dibujar el gráfico: ' + err.message);
            }
        }
    }
}