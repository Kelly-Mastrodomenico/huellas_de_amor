<?php
   // Noticias relacionadas misma categoria
$tituloPagina = 'Noticia — Huellas de Amor';
require_once '../templates/header.php';

if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: ../noticias.php');
    exit();
}

$slug    = trim($_GET['slug']);
$noticia = null;
$relacionadas = [];

try {
    $stmt = $conexion->prepare(
        "SELECT n.*, u.nombre AS nombre_admin
         FROM `noticias` n
         LEFT JOIN `usuarios` u ON n.id_admin = u.id
         WHERE n.slug = :slug AND n.publicada = 1
         LIMIT 1"
    );
    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $stmt->execute();
    $noticia = $stmt->fetch();

    if (!$noticia) {
        header('Location: ../noticias.php');
        exit();
    }

    // Noticias relacionadas misma categoria
    $stmtRel = $conexion->prepare(
        "SELECT * FROM `noticias`
         WHERE `publicada` = 1 AND `categoria` = :categoria AND `id` != :id
         ORDER BY `fecha_publicacion` DESC LIMIT 3"
    );
    $stmtRel->bindParam(':categoria', $noticia['categoria'], PDO::PARAM_STR);
    $stmtRel->bindParam(':id',        $noticia['id'],        PDO::PARAM_INT);
    $stmtRel->execute();
    $relacionadas = $stmtRel->fetchAll();

} catch (PDOException $e) {
    header('Location: ../noticias.php');
    exit();
}

$tituloPagina = htmlspecialchars($noticia['titulo']) . ' — Huellas de Amor';

$imgNoticia = $noticia['imagen']
    ? (str_starts_with($noticia['imagen'], 'http') ? $noticia['imagen'] : '../' . ltrim($noticia['imagen'], '/'))
    : 'https://picsum.photos/900/400?random=' . $noticia['id'];
?>

<!-- Migas de pan -->
<div class="contenedor" style="padding-top:16px;">
    <nav class="migas-pan">
        <a href="../index.php">Inicio</a>
        <span><i class="fa-solid fa-chevron-right"></i></span>
        <a href="../noticias.php">Noticias</a>
        <span><i class="fa-solid fa-chevron-right"></i></span>
        <span><?php echo htmlspecialchars(mb_substr($noticia['titulo'], 0, 40)); ?>...</span>
    </nav>
</div>

<section class="seccion">
    <div class="contenedor">
        <div class="noticias-layout">

            <!-- ARTICULO -->
            <article class="noticia-articulo">

                <!-- Cabecera -->
                <div class="noticia-articulo-header">
                    <span class="noticia-cat noticia-cat-<?php echo strtolower($noticia['categoria'] ?? 'general'); ?>">
                        <?php echo htmlspecialchars($noticia['categoria'] ?? 'General'); ?>
                    </span>
                    <h1><?php echo htmlspecialchars($noticia['titulo']); ?></h1>
                    <div class="noticia-meta">
                        <span>
                            <i class="fa-regular fa-calendar"></i>
                            <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'])); ?>
                        </span>
                        <?php if ($noticia['nombre_admin']) { ?>
                        <span>
                            <i class="fa-solid fa-user-pen"></i>
                            <?php echo htmlspecialchars($noticia['nombre_admin']); ?>
                        </span>
                        <?php } ?>
                    </div>
                </div>

                <!-- Imagen -->
                <img src="<?php echo htmlspecialchars($imgNoticia); ?>"
                     alt="<?php echo htmlspecialchars($noticia['titulo']); ?>"
                     class="noticia-articulo-img">

                <!-- Contenido -->
                <div class="noticia-articulo-contenido">
                   <?php echo $noticia['contenido']; ?>
                </div>

                <!-- Compartir -->
                <div class="noticia-compartir">
                    <span>Compartir:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
                       target="_blank" class="compartir-btn compartir-facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($noticia['titulo']); ?>"
                       target="_blank" class="compartir-btn compartir-twitter">
                        <i class="fa-brands fa-x-twitter"></i>
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($noticia['titulo'] . ' - http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
                       target="_blank" class="compartir-btn compartir-whatsapp">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                </div>

                <!-- Noticias relacionadas -->
                <?php if (!empty($relacionadas)) { ?>
                <div class="noticia-relacionadas">
                    <h3>Noticias relacionadas</h3>
                    <div class="grid-noticias">
                        <?php foreach ($relacionadas as $rel) {
                            $imgRel = $rel['imagen']
                                ? (str_starts_with($rel['imagen'], 'http') ? $rel['imagen'] : '../' . ltrim($rel['imagen'], '/'))
                                : 'https://picsum.photos/400/250?random=' . $rel['id'];
                        ?>
                        <a href="detalle.php?slug=<?php echo urlencode($rel['slug']); ?>"
                           class="tarjeta-noticia">
                            <img src="<?php echo htmlspecialchars($imgRel); ?>"
                                 alt="<?php echo htmlspecialchars($rel['titulo']); ?>">
                            <div class="noticia-cuerpo">
                                <span class="noticia-categoria"><?php echo htmlspecialchars($rel['categoria'] ?? ''); ?></span>
                                <h3><?php echo htmlspecialchars($rel['titulo']); ?></h3>
                                <span class="noticia-fecha">
                                    <i class="fa-regular fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($rel['fecha_publicacion'])); ?>
                                </span>
                            </div>
                        </a>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

            </article>

            <!-- SIDEBAR -->
            <aside class="noticias-sidebar">
                <div class="sidebar-card">
                    <h3><i class="fa-solid fa-tags"></i> Categorías</h3>
                    <div class="sidebar-categorias">
                        <?php foreach (['Eventos', 'Consejos', 'Salud', 'Leyes', 'Adopciones'] as $cat) { ?>
                        <a href="../noticias.php?categoria=<?php echo urlencode($cat); ?>"
                           class="sidebar-cat-item">
                            <span><?php echo $cat; ?></span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                        <?php } ?>
                    </div>
                </div>

                <div class="sidebar-card sidebar-cta">
                    <i class="fa-solid fa-paw"></i>
                    <h3>¿Buscas una mascota?</h3>
                    <p>Miles de animales esperan un hogar amoroso.</p>
                    <a href="../adoptar.php" class="btn-coral" style="display:block; text-align:center; margin-top:12px;">
                        Ver mascotas
                    </a>
                </div>
            </aside>

        </div>
    </div>
</section>

<?php require_once '../templates/footer.php'; ?>
