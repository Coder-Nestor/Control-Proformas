document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnToggleSidebar');
    var sidebar = document.getElementById('sidebar');

    // El sidebar siempre está visible. El botón ☰ solo alterna entre
    // ancho completo (con texto) y una franja angosta de solo íconos —
    // el mismo comportamiento sin importar el tamaño de pantalla.
    if (btn && sidebar) {
        btn.addEventListener('click', function () {
            sidebar.classList.toggle('sidebar-toggled');
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