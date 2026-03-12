new DataTable('#example');


function eliminar() {
   let rta = confirm("¿Estás seguro de eliminar este registro?");
   return rta;
}


$(document).ready(function() {
    // Garantiza scroll horizontal en tablas que no tengan contenedor responsive.
    $('table').each(function () {
        var $table = $(this);

        if ($table.closest('.table-responsive, .table-scroll-x-auto, .dataTables_scrollBody').length) {
            return;
        }

        $table.wrap('<div class="table-scroll-x-auto"></div>');
    });
    
    // Abre/Cierra el menú lateral al hacer clic en el botón de toggle
    $('#menu-toggle').click(function() {
        $('#side-menu').toggleClass('open');
    });

    // Cierra el menú al hacer clic en el ícono 'X' dentro del menú
    $('#menu-close').click(function() {
        $('#side-menu').removeClass('open');
    });

    // Opcional: Cierra el menú si se hace clic fuera de él (útil para la experiencia de usuario)
    $(document).mouseup(function(e) {
        var menu = $('#side-menu');
        var toggle = $('#menu-toggle');
        
        // Si el clic no está en el menú ni en el botón de toggle
        if (!menu.is(e.target) && menu.has(e.target).length === 0 && !toggle.is(e.target) && toggle.has(e.target).length === 0) {
            if (menu.hasClass('open')) {
                menu.removeClass('open');
            }
        }
    });

    // En movil, el sidebar inicia replegado y se expande al tocar el logo.
    function isMobileViewport() {
        return window.matchMedia('(max-width: 756px)').matches;
    }

    $('#navbar .navbar-logo').on('click', function (e) {
        if (!isMobileViewport()) {
            return;
        }

        e.preventDefault();
        $('#navbar').toggleClass('mobile-open');
    });

    $('#navbar .navbar-item-inner').on('click', function () {
        if (isMobileViewport()) {
            $('#navbar').removeClass('mobile-open');
        }
    });

    $(document).on('click touchstart', function (e) {
        if (!isMobileViewport()) {
            return;
        }

        if ($(e.target).closest('#navbar').length === 0) {
            $('#navbar').removeClass('mobile-open');
        }
    });

    $(window).on('resize', function () {
        if (!isMobileViewport()) {
            $('#navbar').removeClass('mobile-open');
        }
    });
});