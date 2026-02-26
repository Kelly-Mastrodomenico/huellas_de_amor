<?php
// Catalogo de mascotas con filtros AJAX para cargar contenido sin recargar

$tituloPagina = 'Adoptar — Huellas de Amor';
require_once 'templates/header.php';
?>

<div class="cabecera-pagina">
    <h1><i class="fa-solid fa-paw"></i> Encuentra tu Compañero</h1>
    <p>Filtra por especie, edad o tamaño y encuentra la mascota perfecta para ti.</p>
</div>

<section class="seccion">
<div class="contenedor">

    <!-- FILTROS — al cambiar cualquier campo se lanza el AJAX -->
    <div class="form-filtros" id="contenedor-filtros">

        <div class="form-grupo">
            <label>Especie</label>
            <select id="filtroEspecie">
                <option value="">Todas</option>
                <option value="perro">Perros</option>
                <option value="gato">Gatos</option>
                <option value="otro">Otros</option>
            </select>
        </div>

        <div class="form-grupo">
            <label>Sexo</label>
            <select id="filtroSexo">
                <option value="">Todos</option>
                <option value="macho">Macho</option>
                <option value="hembra">Hembra</option>
            </select>
        </div>

        <div class="form-grupo">
            <label>Tamaño</label>
            <select id="filtroTamanio">
                <option value="">Todos</option>
                <option value="pequeño">Pequeño</option>
                <option value="mediano">Mediano</option>
                <option value="grande">Grande</option>
            </select>
        </div>

        <div class="form-grupo">
            <label>Buscar por nombre</label>
            <input type="text" id="filtroNombre" placeholder="Nombre de la mascota...">
        </div>

        <div class="form-grupo" style="display:flex; align-items:flex-end;">
            <button class="btn-outline-coral" id="btnLimpiar" style="width:100%;">
                <i class="fa-solid fa-xmark"></i> Limpiar
            </button>
        </div>

    </div>

    <!-- Si viene especie por GET desde el index (categorias) la aplicamos -->
    <div id="totalResultados" style="color:#888; margin-bottom:16px;"></div>

    <!-- contenedor donde AJAX carga las mascotas -->
    <div id="contenedor-mascotas">
        <div class="spinner">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Cargando mascotas...</p>
        </div>
    </div>

</div>
</section>


<?php require_once 'templates/footer.php'; ?>