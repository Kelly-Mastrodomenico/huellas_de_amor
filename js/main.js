/**
 * Funcionalidades principales con jQuery
 * 
 * Contenido:
 * 1. Carrusel hero (fadeIn/fadeOut)
 * 2. Contadores animados
 * 3. Mensaje flash (slideUp)
 * 4. Boton favorito (AJAX)
 * 5. Mostrar/ocultar secciones con jQuery
 * 6. Uso del objeto Date en el footer
 */

$(document).ready(function () {

    // 1. CARRUSEL HERO
    // Cambia las slides con efecto fadeIn/fadeOut cada 4 segundos

    var slideActual = 0;
    var totalSlides = $('.hero-slide').length;
    var intervaloCarrusel;

    // Funcion que muestra una slide concreta
    function mostrarSlide(indice) {
        // Ocultar todas las slides
        $('.hero-slide').fadeOut(500).removeClass('activo');
        $('.hero-dots span').removeClass('activo');

        // Si el indice se pasa del final, volver al principio
        if (indice >= totalSlides) { indice = 0; }
        if (indice < 0) { indice = totalSlides - 1; }

        slideActual = indice;

        // Mostrar la slide correcta con fadeIn
        $('.hero-slide').eq(slideActual).fadeIn(500).addClass('activo');
        $('.hero-dots span').eq(slideActual).addClass('activo');
    }

    // Funcion que avanza automaticamente
    function iniciarCarrusel() {
        intervaloCarrusel = setInterval(function () {
            mostrarSlide(slideActual + 1);
        }, 4000);
    }

    // Arrancar el carrusel si hay slides en la pagina
    if (totalSlides > 0) {
        iniciarCarrusel();

        // Flecha derecha
        $('#flechaDer').on('click', function () {
            clearInterval(intervaloCarrusel);
            mostrarSlide(slideActual + 1);
            iniciarCarrusel();
        });

        // Flecha izquierda
        $('#flechaIzq').on('click', function () {
            clearInterval(intervaloCarrusel);
            mostrarSlide(slideActual - 1);
            iniciarCarrusel();
        });

        // Clic en los puntos indicadores
        $('.hero-dots span').on('click', function () {
            clearInterval(intervaloCarrusel);
            mostrarSlide($(this).data('slide'));
            iniciarCarrusel();
        });
    }


    // 2. CONTADORES ANIMADOS
    // Anima los numeros desde 0 hasta el valor de data-objetivo

    function animarContadores() {
        $('.contador-numero').each(function () {
            var $contador = $(this);
            var objetivo  = parseInt($contador.data('objetivo')) || 0;

            // Animar desde 0 hasta el objetivo en 2 segundos
            $({ valor: 0 }).animate({ valor: objetivo }, {
                duration: 2000,
                easing: 'swing',
                step: function () {
                    $contador.text(Math.floor(this.valor));
                },
                complete: function () {
                    $contador.text(objetivo);
                }
            });
        });
    }

    // Solo animar cuando los contadores sean visibles en pantalla
    // (cuando el usuario hace scroll hasta ellos)
    var contadoresAnimados = false;

    $(window).on('scroll', function () {
        if (contadoresAnimados) { return; }

        var $contadores = $('.seccion-contadores');
        if ($contadores.length === 0) { return; }

        // Comprobar si el elemento esta en el viewport
        var posicionElemento = $contadores.offset().top;
        var posicionScroll   = $(window).scrollTop() + $(window).height();

        if (posicionScroll > posicionElemento) {
            contadoresAnimados = true;
            animarContadores();
        }
    });

    // Si los contadores ya estan visibles al cargar la pagina
    if ($('.seccion-contadores').length > 0) {
        $(window).trigger('scroll');
    }

    // 3. MENSAJE FLASH
    // Se oculta automaticamente despues de 4 segundos con slideUp

    if ($('#mensajeFlash').length > 0) {
        setTimeout(function () {
            $('#mensajeFlash').slideUp(400);
        }, 4000);
    }

    // 4. BOTON FAVORITO
    // Marca/desmarca favoritos con AJAX sin recargar la pagina

    $(document).on('click', '.btn-favorito', function () {
        var $btn       = $(this);
        var idMascota  = $btn.data('id');

        // Si no esta logueado, redirigir al login
        if (typeof usuarioLogueado === 'undefined' || !usuarioLogueado) {
            window.location.href = 'login.php';
            return;
        }

        // Llamada AJAX para añadir o quitar favorito
        $.ajax({
            url: 'ajax/favorito.php',
            type: 'POST',
            data: { id_mascota: idMascota },
            dataType: 'json',
            success: function (respuesta) {
                if (respuesta.accion === 'añadido') {
                    $btn.addClass('activo');
                    $btn.find('i').removeClass('fa-regular').addClass('fa-solid');
                } else {
                    $btn.removeClass('activo');
                    $btn.find('i').removeClass('fa-solid').addClass('fa-regular');
                }
            },
            error: function () {
                alert('Error al actualizar favorito. Intentalo de nuevo.');
            }
        });
    });

    // 5. MOSTRAR / OCULTAR con jQuery
    // Si hay un formulario con clase .form-toggle, lo gestionamos aqui
    $(document).on('click', '.btn-toggle-form', function () {
        var objetivo = $(this).data('objetivo');
        $('#' + objetivo).slideToggle(300);
    });

    // 6. OBJETO Date — mostrar fecha actual en el footer

var hoy = new Date();
var dia = hoy.getDate();
var mes = hoy.getMonth() + 1;
var anio = hoy.getFullYear();

$('#fechaHoy').text(dia + '/' + mes + '/' + anio);

    // 7. INICIALIZAR jQuery UI Tabs
    // Se activa en cualquier elemento con id="tabs"

    if ($('#tabs').length > 0) {
        $('#tabs').tabs();
    }

    // 8. INICIALIZAR FancyBox
    // Se activa en cualquier enlace con data-fancybox

    if (typeof $.fancybox !== 'undefined') {
        $('[data-fancybox]').fancybox({
            buttons: ['zoom', 'slideShow', 'close'],
            loop: true,
            transitionEffect: 'fade'
        });
    }

});