<?php
$tituloPagina = 'Huellas de Amor — Adopta, no compres';
require_once 'templates/header.php';

// Obtener mascotas destacadas (las 8 mas recientes disponibles)
$mascotasDestacadas = [];
try {
$sql = "SELECT m.*, 
    (SELECT f.ruta_foto FROM fotos_mascotas f 
    WHERE f.id_mascota = m.id AND f.es_principal = 1 
    LIMIT 1) AS foto_principal
        FROM mascotas m
        WHERE m.activo = 1 AND m.estado = 'disponible'
        ORDER BY m.fecha_ingreso DESC
        LIMIT 8";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $mascotasDestacadas = $stmt->fetchAll();
        } catch (PDOException $e) {
        $mascotasDestacadas = [];
        }

// Obtener testimonios aprobados
$testimonios = [];
try {
        $sql = "SELECT t.*, u.nombre AS nombre_usuario
        FROM testimonios t
        INNER JOIN usuarios u ON t.id_usuario = u.id
        WHERE t.aprobado = 1
        ORDER BY t.fecha DESC
        LIMIT 3";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $testimonios = $stmt->fetchAll();
        } catch (PDOException $e) {
        $testimonios = [];
        }

// Obtener ultimas noticias publicadas
$noticias = [];
try {
        $sql = "SELECT * FROM noticias
        WHERE publicada = 1
        ORDER BY fecha_publicacion DESC
        LIMIT 3";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $noticias = $stmt->fetchAll();
        } catch (PDOException $e) {
        $noticias = [];
        }

// Contar mascotas para los contadores animados
$totalAdoptadas  = contarMascotas('adoptado',    $conexion);
$totalDisponibles = contarMascotas('disponible', $conexion);
$totalAcogida    = contarMascotas('acogida',     $conexion);
?>

<!-- SECCION 1: HERO CON CARRUSEL
jQuery se encarga de animar las slides en main.js -->
<section class="seccion-hero">

<!-- Slide 1 -->
<div class="hero-slide activo">
<img src="https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=1200&h=600&fit=crop">
</div>

<!-- Slide 2 -->
<div class="hero-slide">
<img src="https://images.unsplash.com/photo-1533738363-b7f9aef128ce?w=1200&h=600&fit=crop">
</div>

<!-- Slide 3 -->
<div class="hero-slide">
<img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=1200&h=600&fit=crop">
</div>

<!-- Contenido sobre las slides -->
<div class="hero-contenido">
<h1>Encuentra tu <span>compañero</span> perfecto</h1>
<p>Miles de mascotas esperan un hogar lleno de amor. Tu nueva historia comienza aqui.</p>
<div class="hero-botones">
<a href="adoptar.php" class="btn-coral">
<i class="fa-solid fa-paw"></i> Explorar Mascotas
</a>
<a href="apadrinar.php" class="btn-outline-turquesa" style="border-color:#fff; color:#fff;">
Apadrinar
</a>
</div>
</div>

<!-- Flechas -->
<button class="hero-flecha flecha-izq" id="flechaIzq">
<i class="fa-solid fa-chevron-left"></i>
</button>
<button class="hero-flecha flecha-der" id="flechaDer">
<i class="fa-solid fa-chevron-right"></i>
</button>

<!-- Puntos indicadores -->
<div class="hero-dots">
<span class="activo" data-slide="0"></span>
<span data-slide="1"></span>
<span data-slide="2"></span>
</div>

</section>


<!-- SECCION 2: CONTADORES ANIMADOS
jQuery anima los numeros desde 0 hasta el valor real -->
<section class="seccion" >
<div class="contenedor">
<div class="seccion-contadores">
<div class="grid-contadores">

<div class="contador-item">
    <i class="fa-solid fa-heart"></i>
    <span class="contador-numero" data-objetivo="<?php echo $totalAdoptadas; ?>">0</span>
    <span>Mascotas Adoptadas</span>
</div>

<div class="contador-item">
    <i class="fa-solid fa-house"></i>
    <span class="contador-numero" data-objetivo="<?php echo $totalDisponibles; ?>">0</span>
    <span>En Adopcion Ahora</span>
</div>

<div class="contador-item">
    <i class="fa-solid fa-star"></i>
    <span class="contador-numero" data-objetivo="<?php echo $totalAcogida; ?>">0</span>
    <span>En Acogida</span>
</div>

</div>
</div>
</div>
</section>


<!-- SECCION 3: CATEGORIAS -->
<section class="seccion seccion-gris">
<div class="contenedor">

<div class="seccion-titulo">
<h2>Categorias para Explorar</h2>
<p>Cada tipo de mascota tiene su propia personalidad. Encuentra la que mejor encaje contigo.</p>
</div>

<div class="grid-categorias">

<a href="adoptar.php?especie=perro" class="tarjeta-categoria cat-perros">
<div class="icono-categoria">
    <i class="fa-solid fa-dog"></i>
</div>
<h3>Perros</h3>
<p>Fieles y llenos de energia</p>
</a>

<a href="adoptar.php?especie=gato" class="tarjeta-categoria cat-gatos">
<div class="icono-categoria">
    <i class="fa-solid fa-cat"></i>
</div>
<h3>Gatos</h3>
<p>Independientes y cariñosos</p>
</a>

<a href="adoptar.php?especie=otro" class="tarjeta-categoria cat-otros">
<div class="icono-categoria">
    <i class="fa-solid fa-dove"></i>
</div>
<h3>Otros</h3>
<p>Conejos, pajaros y mas</p>
</a>

</div>
</div>
</section>


<!--SECCION 4: MASCOTAS DESTACADAS -->
<section class="seccion">
<div class="contenedor">

<div class="seccion-titulo" style="text-align:left;">
<h2>Mascotas Destacadas</h2>
<p>Estas pequeñas almas estan buscando un lugar al cual llamar hogar.</p>
</div>

<?php if (!empty($mascotasDestacadas)) { ?>
<div class="grid-mascotas">
<?php foreach ($mascotasDestacadas as $mascota) { ?>
    <div class="tarjeta-mascota">

        <div class="tarjeta-img">
            <img src="<?php echo $mascota['foto_principal']
                ? htmlspecialchars($mascota['foto_principal'])
                : 'https://picsum.photos/400/300?random=' . $mascota['id']; ?>"
                    alt="<?php echo htmlspecialchars($mascota['nombre']); ?>">

            <span class="badge-estado badge badge-<?php echo $mascota['estado']; ?>">
                <?php echo textoEstado($mascota['estado']); ?>
            </span>

            <button class="btn-favorito" data-id="<?php echo $mascota['id']; ?>">
                <i class="fa-regular fa-heart"></i>
            </button>
        </div>

        <div class="tarjeta-cuerpo">
            <h3><?php echo htmlspecialchars($mascota['nombre']); ?></h3>
            <span class="tarjeta-edad">
                <?php echo formatearEdad($mascota['edad_anios'], $mascota['edad_meses']); ?>
            </span>

            <div class="tarjeta-datos">
                <span>
                    <i class="fa-solid fa-ruler"></i>
                    <?php echo htmlspecialchars($mascota['tamanio']); ?>
                </span>
                <span>
                    <i class="fa-solid fa-venus-mars"></i>
                    <?php echo htmlspecialchars($mascota['sexo']); ?>
                </span>
                <span>
                    <i class="fa-solid fa-circle-check"></i>
                    Activo
                </span>
            </div>
        </div>

        <div class="tarjeta-footer">
            <a href="mascotas/detalle.php?id=<?php echo $mascota['id']; ?>" class="btn-coral">
                Ver Detalles
            </a>
        </div>

    </div>
<?php } ?>
</div>

<div style="text-align:center; margin-top:32px;">
<a href="adoptar.php" class="btn-outline-coral">
    Ver todos los amigos <i class="fa-solid fa-arrow-right"></i>
</a>
</div>

<?php } else { ?>
<p style="text-align:center; color:#888;">No hay mascotas disponibles en este momento.</p>
<?php } ?>

</div>
</section>


<!-- SECCION 5: TESTIMONIOS -->
<section class="seccion seccion-gris">
<div class="contenedor">
<div class="seccion-titulo">
    <h2>Historias que Inspiran</h2>
    <p>Testimonios reales de familias que encontraron a su mejor amigo a traves de nuestra plataforma.</p>
</div>

<?php if (!empty($testimonios)) { ?>
<div class="grid-testimonios">
<?php foreach ($testimonios as $testimonio) { ?>
<div class="tarjeta-testimonio">
    <div class="testimonio-img">
        <img src="<?php echo $testimonio['foto_despues']
            ? htmlspecialchars($testimonio['foto_despues'])
            : 'https://placedog.net/400/300?id=' . $testimonio['id']; ?>"
                alt="Historia de adopcion">
        <span class="badge-final-feliz">Final Feliz</span>
    </div>
    <div class="testimonio-cuerpo">
        <p>"<?php echo htmlspecialchars($testimonio['texto']); ?>"</p>
        <div class="testimonio-autor">
            <img src="https://placedog.net/40/40?id=<?php echo $testimonio['id_usuario']; ?>"
                    alt="<?php echo htmlspecialchars($testimonio['nombre_usuario']); ?>">
            <div>
                <span><?php echo htmlspecialchars($testimonio['nombre_usuario']); ?></span>
                <small>Adoptante Feliz</small>
            </div>
        </div>
    </div>
</div>
<?php } ?>
</div>

<?php } else { ?>
<!-- Testimonios de ejemplo si no hay en la BD -->
<div class="grid-testimonios">

<div class="tarjeta-testimonio">
    <div class="testimonio-img">
        <img src="https://placedog.net/400/300?id=1" alt="Testimonio Bruno">
        <span class="badge-final-feliz">Final Feliz</span>
    </div>
    <div class="testimonio-cuerpo">
        <p>"Adoptar a Bruno cambio mi vida por completo. Ahora tengo un compañero fiel para mis caminatas."</p>
        <div class="testimonio-autor">
            <img src="https://placedog.net/40/40?id=11" alt="Carlos">
            <div>
                <span>Carlos Mendoza</span>
                <small>Adoptante Feliz</small>
            </div>
        </div>
    </div>
</div>

    <div class="tarjeta-testimonio">
        <div class="testimonio-img">
            <img src="https://placekitten.com/400/300" alt="Testimonio Mimi">
            <span class="badge-final-feliz">Final Feliz</span>
        </div>
        <div class="testimonio-cuerpo">
            <p>"Mimi llego asustada pero con amor y paciencia hoy es la reina de la casa."</p>
            <div class="testimonio-autor">
                <img src="https://placekitten.com/40/40" alt="Elena">
                <div>
                    <span>Elena Rodriguez</span>
                    <small>Adoptante Feliz</small>
                </div>
            </div>
        </div>
    </div>

    <div class="tarjeta-testimonio">
        <div class="testimonio-img">
            <img src="https://placedog.net/400/300?id=5" alt="Testimonio Toby">
            <span class="badge-final-feliz">Final Feliz</span>
        </div>
        <div class="testimonio-cuerpo">
            <p>"Toby es el mejor regalo que nuestros hijos pudieron recibir. Nos enseño el valor de la lealtad."</p>
            <div class="testimonio-autor">
                <img src="https://placedog.net/40/40?id=15" alt="Familia Ortega">
                <div>
                    <span>Familia Ortega</span>
                    <small>Adoptante Feliz</small>
                </div>
            </div>
        </div>
    </div>

</div>
<?php } ?>

</div>
</section>


<!-- SECCION 6: ULTIMAS NOTICIAS -->
<?php if (!empty($noticias)) { ?>
<section class="seccion">
<div class="contenedor">

<div class="seccion-titulo">
<h2>Ultimas Noticias</h2>
<p>Mantente informado sobre eventos, consejos y novedades del mundo animal.</p>
</div>

<div class="grid-noticias">
<?php foreach ($noticias as $noticia) { ?>
<div class="tarjeta-noticia">
    <img src="<?php echo $noticia['imagen']
        ? htmlspecialchars($noticia['imagen'])
        : 'https://picsum.photos/400/200?random=' . $noticia['id']; ?>"
            alt="<?php echo htmlspecialchars($noticia['titulo']); ?>">
    <div class="noticia-cuerpo">
        <p class="noticia-categoria">
            <?php echo htmlspecialchars($noticia['categoria']); ?>
        </p>
        <h3><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
        <p><?php echo htmlspecialchars(substr($noticia['contenido'], 0, 100)) . '...'; ?></p>
        <span class="noticia-fecha">
            <i class="fa-regular fa-calendar"></i>
            <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'])); ?>
        </span>
    </div>
</div>
<?php } ?>
</div>

</div>
</section>
<?php } ?>


<!--SECCION 7: CTA — APADRINAR O DONAR-->
<section class="seccion-cta">
<h2>¿Quieres marcar la diferencia?</h2>
<p>No todos pueden adoptar, pero todos pueden ayudar. Tu apoyo financiero o tiempo de voluntariado salva vidas.</p>
<div class="cta-botones">
<a href="apadrinar.php" class="btn-coral">Apadrinar Ahora</a>
<a href="donaciones.php" class="btn-blanco">Hacer Donacion</a>
</div>
</section>

<?php require_once 'templates/footer.php'; ?>