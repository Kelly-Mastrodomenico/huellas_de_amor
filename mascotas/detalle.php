<?php
$tituloPagina = 'Detalle Mascota — Huellas de Amor';
require_once '../templates/header.php';

// Validar que viene un ID por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../adoptar.php');
    exit();
}

$idMascota = (int) $_GET['id'];
$mascota   = null;
$fotos     = [];

try {
    $stmt = $conexion->prepare(
        "SELECT m.*, p.nombre AS nombre_protectora, p.ciudad AS ciudad_protectora,
                p.telefono AS telefono_protectora, p.email AS email_protectora
         FROM `mascotas` m
         LEFT JOIN `protectoras` p ON m.id_protectora = p.id
         WHERE m.id = :id AND m.activo = 1
         LIMIT 1"
    );
    $stmt->bindParam(':id', $idMascota, PDO::PARAM_INT);
    $stmt->execute();
    $mascota = $stmt->fetch();

    if (!$mascota) {
        header('Location: ../adoptar.php');
        exit();
    }

    $stmtFotos = $conexion->prepare(
        "SELECT * FROM `fotos_mascotas`
         WHERE `id_mascota` = :id
         ORDER BY `es_principal` DESC"
    );
    $stmtFotos->bindParam(':id', $idMascota, PDO::PARAM_INT);
    $stmtFotos->execute();
    $fotos = $stmtFotos->fetchAll();

    $stmtSimilares = $conexion->prepare(
        "SELECT m.*,
            (SELECT f.ruta_foto FROM `fotos_mascotas` f
             WHERE f.id_mascota = m.id AND f.es_principal = 1
             LIMIT 1) AS foto_principal
         FROM `mascotas` m
         WHERE m.especie = :especie
           AND m.id != :id
           AND m.activo = 1
           AND m.estado = 'disponible'
         ORDER BY RAND()
         LIMIT 4"
    );
    $stmtSimilares->bindParam(':especie', $mascota['especie']);
    $stmtSimilares->bindParam(':id', $idMascota, PDO::PARAM_INT);
    $stmtSimilares->execute();
    $mascotasSimilares = $stmtSimilares->fetchAll();

} catch (PDOException $e) {
    header('Location: ../adoptar.php');
    exit();
}

// Foto principal
$fotoPrincipal = '';
foreach ($fotos as $foto) {
    if ($foto['es_principal']) {
        $fotoPrincipal = $foto['ruta_foto'];
        break;
    }
}
if (!$fotoPrincipal && !empty($fotos)) {
    $fotoPrincipal = $fotos[0]['ruta_foto'];
}
if (!$fotoPrincipal) {
    $fotoPrincipal = 'https://picsum.photos/600/450?random=' . $idMascota;
}

// Corregir ruta si es archivo local — añadir ../ porque estamos en mascotas/
if ($fotoPrincipal && !str_starts_with($fotoPrincipal, 'http')) {
    $fotoPrincipal = '../' . ltrim($fotoPrincipal, '/');
}
?>

<!-- Migas de pan -->
<div class="contenedor" style="padding-top:16px;">
    <nav class="migas-pan">
        <a href="../index.php">Inicio</a>
        <span><i class="fa-solid fa-chevron-right"></i></span>
        <a href="../adoptar.php">Adoptar</a>
        <span><i class="fa-solid fa-chevron-right"></i></span>
        <span><?php echo htmlspecialchars($mascota['nombre']); ?></span>
    </nav>
</div>

<section class="seccion">
<div class="contenedor">
<div class="detalle-grid">

<!-- COLUMNA IZQUIERDA: Galeria -->
<div class="detalle-galeria">

    <div class="galeria-principal">
        <a href="<?php echo htmlspecialchars($fotoPrincipal); ?>"
            data-fancybox="galeria-mascota"
            data-caption="<?php echo htmlspecialchars($mascota['nombre']); ?>">
            <img src="<?php echo htmlspecialchars($fotoPrincipal); ?>"
                    alt="<?php echo htmlspecialchars($mascota['nombre']); ?>">
        </a>
        <span class="badge badge-<?php echo $mascota['estado']; ?> badge-detalle">
            <?php echo textoEstado($mascota['estado']); ?>
        </span>
    </div>

    <?php if (count($fotos) > 1) { ?>
    <div class="galeria-miniaturas">
        <?php foreach ($fotos as $foto) {
            // Corregir ruta de cada miniatura
            $rutaFoto = str_starts_with($foto['ruta_foto'], 'http')
                ? $foto['ruta_foto']
                : '../' . ltrim($foto['ruta_foto'], '/');
        ?>
        <a href="<?php echo htmlspecialchars($rutaFoto); ?>"
            data-fancybox="galeria-mascota"
            data-caption="<?php echo htmlspecialchars($mascota['nombre']); ?>"
            class="miniatura<?php echo $foto['es_principal'] ? ' activa' : ''; ?>">
            <img src="<?php echo htmlspecialchars($rutaFoto); ?>"
                    alt="Foto de <?php echo htmlspecialchars($mascota['nombre']); ?>">
        </a>
        <?php } ?>
    </div>
    <?php } ?>

</div>

<!-- COLUMNA DERECHA: Info -->
<div class="detalle-info">

    <div class="detalle-encabezado">
        <h1><?php echo htmlspecialchars($mascota['nombre']); ?></h1>
        <span class="detalle-edad">
            <?php echo formatearEdad($mascota['edad_anios'], $mascota['edad_meses']); ?>
        </span>
    </div>

    <div class="detalle-chips">
        <span class="chip"><i class="fa-solid fa-paw"></i> <?php echo ucfirst(htmlspecialchars($mascota['especie'])); ?></span>
        <span class="chip"><i class="fa-solid fa-venus-mars"></i> <?php echo ucfirst(htmlspecialchars($mascota['sexo'])); ?></span>
        <span class="chip"><i class="fa-solid fa-ruler"></i> <?php echo ucfirst(htmlspecialchars($mascota['tamanio'])); ?></span>
        <?php if ($mascota['raza']) { ?>
        <span class="chip"><i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($mascota['raza']); ?></span>
        <?php } ?>
    </div>

    <?php if ($mascota['descripcion']) { ?>
    <div class="detalle-seccion">
        <h3>Sobre <?php echo htmlspecialchars($mascota['nombre']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($mascota['descripcion'])); ?></p>
    </div>
    <?php } ?>

    <?php if ($mascota['caracter']) { ?>
    <div class="detalle-seccion">
        <h3>Caracter</h3>
        <p><?php echo htmlspecialchars($mascota['caracter']); ?></p>
    </div>
    <?php } ?>

    <?php if ($mascota['nombre_protectora']) { ?>
    <div class="detalle-protectora">
        <i class="fa-solid fa-house-heart"></i>
        <div>
            <span>Protectora</span>
            <strong><?php echo htmlspecialchars($mascota['nombre_protectora']); ?></strong>
            <small><?php echo htmlspecialchars($mascota['ciudad_protectora']); ?></small>
        </div>
    </div>
    <?php } ?>

    <div class="detalle-botones">
        <?php if ($mascota['estado'] === 'disponible') { ?>
            <?php if (estaLogueado()) { ?>
                <a href="../solicitud_adopcion.php?id=<?php echo $idMascota; ?>"
                    class="btn-coral btn-grande">
                    <i class="fa-solid fa-heart"></i> Solicitar Adopcion
                </a>
            <?php } else { ?>
                <a href="../login.php" class="btn-coral btn-grande">
                    <i class="fa-solid fa-heart"></i> Solicitar Adopcion
                </a>
            <?php } ?>
            <a href="../apadrinar.php?id=<?php echo $idMascota; ?>"
                class="btn-outline-turquesa btn-grande">
                <i class="fa-solid fa-star"></i> Apadrinar
            </a>
        <?php } else { ?>
            <div class="alerta alerta-aviso">
                Esta mascota ya ha sido <?php echo $mascota['estado'] === 'adoptado' ? 'adoptada' : 'acogida'; ?>.
            </div>
        <?php } ?>

        <button class="btn-favorito-detalle" data-id="<?php echo $idMascota; ?>">
            <i class="fa-regular fa-heart"></i>
            <span>Guardar</span>
        </button>
    </div>

    <div class="detalle-compartir">
        <span>Compartir:</span>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
            target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('Mira a ' . $mascota['nombre'] . ' en Huellas de Amor'); ?>&url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
            target="_blank" rel="noopener"><i class="fa-brands fa-twitter"></i></a>
        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode('Mira a ' . $mascota['nombre'] . ' buscando hogar: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
            target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i></a>
    </div>

</div>
</div>
</div>
</section>

<!-- MASCOTAS SIMILARES -->
<?php if (!empty($mascotasSimilares)) { ?>
<section class="seccion seccion-gris">
    <div class="contenedor">
        <div class="seccion-titulo">
            <h2>Mascotas Similares</h2>
            <p>Otros <?php echo htmlspecialchars($mascota['especie']); ?>s que tambien buscan hogar.</p>
        </div>
<div class="grid-mascotas">
    <?php foreach ($mascotasSimilares as $similar) {
        // Corregir ruta fotos similares
        $fotoSimilar = $similar['foto_principal'];
        if ($fotoSimilar && !str_starts_with($fotoSimilar, 'http')) {
            $fotoSimilar = '../' . ltrim($fotoSimilar, '/');
        }
        if (!$fotoSimilar) {
            $fotoSimilar = 'https://picsum.photos/400/300?random=' . $similar['id'];
        }
    ?>
    <div class="tarjeta-mascota">
        <div class="tarjeta-img">
            <img src="<?php echo htmlspecialchars($fotoSimilar); ?>"
                    alt="<?php echo htmlspecialchars($similar['nombre']); ?>">
            <span class="badge-estado badge badge-<?php echo $similar['estado']; ?>">
                <?php echo textoEstado($similar['estado']); ?>
            </span>
        </div>
        <div class="tarjeta-cuerpo">
            <h3><?php echo htmlspecialchars($similar['nombre']); ?></h3>
            <span class="tarjeta-edad">
                <?php echo formatearEdad($similar['edad_anios'], $similar['edad_meses']); ?>
            </span>
            <div class="tarjeta-datos">
                <span><i class="fa-solid fa-ruler"></i> <?php echo htmlspecialchars($similar['tamanio']); ?></span>
                <span><i class="fa-solid fa-venus-mars"></i> <?php echo htmlspecialchars($similar['sexo']); ?></span>
            </div>
        </div>
        <div class="tarjeta-footer">
            <a href="detalle.php?id=<?php echo $similar['id']; ?>" class="btn-coral">Ver Detalles</a>
        </div>
    </div>
    <?php } ?>
</div>
</div>
</section>
<?php } ?>

<?php require_once '../templates/footer.php'; ?>