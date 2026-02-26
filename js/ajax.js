// ajax.js — Buscar mascotas en adoptar.php

$(document).ready(function() {

    // Solo ejecutar si estamos en adoptar.php
    if ($('#contenedor-mascotas').length === 0) { return; }

    function cargarMascotas() {
        var especie = $('#filtroEspecie').val();
        var sexo    = $('#filtroSexo').val();
        var tamanio = $('#filtroTamanio').val();
        var nombre  = $('#filtroNombre').val().trim();

        $('#contenedor-mascotas').html(
            '<div class="spinner"><i class="fa-solid fa-spinner fa-spin"></i><p>Cargando...</p></div>'
        );

        $.ajax({
            type: 'GET',
            url: 'ajax/buscar_mascotas.php',
            data: { especie: especie, sexo: sexo, tamanio: tamanio, nombre: nombre },
            dataType: 'json'
        })
        .done(function(data) {
            $('#totalResultados').text(data.total + ' mascotas encontradas');

            if (data.mascotas.length === 0) {
                $('#contenedor-mascotas').html('<div class="sin-mascotas"><i class="fa-solid fa-paw"></i><p>No se encontraron mascotas.</p></div>');
                return;
            }

            var html = '<div class="grid-mascotas">';
            $.each(data.mascotas, function(i, m) {
                var foto = m.foto_principal ? m.foto_principal : 'https://placedog.net/400/300?id=' + m.id;
                html += '<div class="tarjeta-mascota">';
                html += '<div class="tarjeta-img"><img src="' + foto + '" alt="' + m.nombre + '">';
                html += '<span class="badge badge-' + m.estado + '">' + m.estado_texto + '</span></div>';
                html += '<div class="tarjeta-cuerpo"><h3>' + m.nombre + '</h3>';
                html += '<span class="tarjeta-edad">' + m.edad + '</span></div>';
                html += '<div class="tarjeta-footer"><a href="mascotas/detalle.php?id=' + m.id + '" class="btn-coral">Ver Detalles</a></div>';
                html += '</div>';
            });
            html += '</div>';
            $('#contenedor-mascotas').html(html);
        })
        .fail(function() {
            $('#contenedor-mascotas').html('<p style="color:red;text-align:center;">Error al cargar.</p>');
        });
    }

    // Leer especie de la URL si viene del index
    var urlParams = new URLSearchParams(window.location.search);
    var especieUrl = urlParams.get('especie');
    if (especieUrl) { $('#filtroEspecie').val(especieUrl); }

    // Eventos
    $('#filtroEspecie, #filtroSexo, #filtroTamanio').on('change', function() { cargarMascotas(); });

    var temporizador;
    $('#filtroNombre').on('keyup', function() {
        clearTimeout(temporizador);
        temporizador = setTimeout(cargarMascotas, 400);
    });

    $('#btnLimpiar').on('click', function() {
        $('#filtroEspecie, #filtroSexo, #filtroTamanio').val('');
        $('#filtroNombre').val('');
        cargarMascotas();
    });

    // Cargar al entrar
    cargarMascotas();

});