document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggleBtn = document.getElementById('btnToggleSidebar');

    if (!sidebar || !toggleBtn) return;

    const isMobile = () => window.innerWidth < 768;
    const isTablet = () => window.innerWidth >= 768 && window.innerWidth < 992;

    // Función para alternar el sidebar
    function toggleSidebar(e) {
        if (e) e.stopPropagation();

        if (isMobile()) {
            // Móvil: mostrar/ocultar desde la izquierda
            sidebar.classList.toggle('sidebar-toggled');
            if (backdrop) {
                backdrop.classList.toggle('show');
            }
            document.body.style.overflow = sidebar.classList.contains('sidebar-toggled') ? 'hidden' : '';
        } else {
            // Desktop/Tablet: colapsar/expandir
            sidebar.classList.toggle('sidebar-toggled');
            // Guardar estado en localStorage
            const isCollapsed = sidebar.classList.contains('sidebar-toggled');
            try {
                localStorage.setItem('sidebarCollapsed', JSON.stringify(isCollapsed));
            } catch (e) {}
        }
    }

    // Evento del botón toggle
    toggleBtn.addEventListener('click', toggleSidebar);

    // Cerrar sidebar al hacer clic en backdrop (móvil)
    if (backdrop) {
        backdrop.addEventListener('click', function() {
            if (isMobile()) {
                sidebar.classList.remove('sidebar-toggled');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }

    // Restaurar estado del sidebar en desktop/tablet
    if (!isMobile()) {
        try {
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState !== null) {
                const isCollapsed = JSON.parse(savedState);
                if (isCollapsed) {
                    sidebar.classList.add('sidebar-toggled');
                }
            }
        } catch (e) {}
    }

    // Manejar cambio de tamaño de ventana
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (isMobile()) {
                // En móvil, resetear y asegurar que el sidebar esté oculto
                sidebar.classList.remove('sidebar-toggled');
                if (backdrop) {
                    backdrop.classList.remove('show');
                }
                document.body.style.overflow = '';
                toggleBtn.style.display = 'inline-flex';
            } else {
                // En desktop/tablet, ocultar backdrop si está visible
                if (backdrop) {
                    backdrop.classList.remove('show');
                }
                sidebar.classList.remove('sidebar-toggled-mobile');

                // Restaurar estado colapsado si existe
                if (!isMobile()) {
                    try {
                        const savedState = localStorage.getItem('sidebarCollapsed');
                        if (savedState !== null) {
                            const isCollapsed = JSON.parse(savedState);
                            if (isCollapsed) {
                                sidebar.classList.add('sidebar-toggled');
                            } else {
                                sidebar.classList.remove('sidebar-toggled');
                            }
                        }
                    } catch (e) {}
                }

                // Mostrar/ocultar botón toggle según dispositivo
                if (isTablet()) {
                    toggleBtn.style.display = 'inline-flex';
                } else {
                    toggleBtn.style.display = 'none';
                }
            }
        }, 300);
    });

    // Cerrar sidebar con tecla ESC (móvil)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isMobile()) {
            sidebar.classList.remove('sidebar-toggled');
            if (backdrop) {
                backdrop.classList.remove('show');
            }
            document.body.style.overflow = '';
        }
    });

    // Cerrar sidebar al hacer clic fuera (móvil)
    document.addEventListener('click', function(e) {
        if (isMobile() && sidebar.classList.contains('sidebar-toggled')) {
            if (!sidebar.contains(e.target) && e.target !== toggleBtn && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('sidebar-toggled');
                if (backdrop) {
                    backdrop.classList.remove('show');
                }
                document.body.style.overflow = '';
            }
        }
    });

    // Inicializar visibilidad del botón toggle
    if (isMobile() || isTablet()) {
        toggleBtn.style.display = 'inline-flex';
    } else {
        toggleBtn.style.display = 'none';
    }

    // ============================================
    // DETECTAR SCROLL EN TABLAS
    // ============================================
    
    const tableWrappers = document.querySelectorAll('.table-wrapper');
    tableWrappers.forEach(wrapper => {
        function checkScroll() {
            if (wrapper.scrollWidth > wrapper.clientWidth) {
                wrapper.classList.add('has-scroll');
            } else {
                wrapper.classList.remove('has-scroll');
            }
        }
        
        // Verificar al cargar
        checkScroll();
        
        // Verificar al hacer scroll
        wrapper.addEventListener('scroll', checkScroll);
        
        // Verificar al redimensionar
        window.addEventListener('resize', checkScroll);
    });

    console.log('✅ Sidebar inicializado correctamente');
});