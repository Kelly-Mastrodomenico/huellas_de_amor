<?php
$tituloPagina = 'Noticias y Eventos — Huellas de Amor';
require_once 'templates/header.php';

// Categorías disponibles
$categorias = ['Eventos', 'Consejos', 'Salud', 'Leyes', 'Adopciones'];

// Filtro de categoría
$categoriaActiva = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';

// Paginación
$porPagina    = 9;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset       = ($paginaActual - 1) * $porPagina;

try {
    $donde  = "WHERE `publicada` = 1";
    $params = [];

    if (!empty($categoriaActiva)) {
        $donde .= " AND `categoria` = :categoria";
        $params[':categoria'] = $categoriaActiva;
    }

    // Total noticias
    $stmtTotal = $conexion->prepare("SELECT COUNT(*) FROM `noticias` $donde");
    foreach ($params as $k => $v) { $stmtTotal->bindValue($k, $v); }
    $stmtTotal->execute();
    $totalNoticias = $stmtTotal->fetchColumn();
    $totalPaginas  = ceil($totalNoticias / $porPagina);

    // Noticias de esta página
    $stmtNoticias = $conexion->prepare(
        "SELECT * FROM `noticias` $donde
         ORDER BY `fecha_publicacion` DESC
         LIMIT :limite OFFSET :offset"
    );
    foreach ($params as $k => $v) { $stmtNoticias->bindValue($k, $v); }
    $stmtNoticias->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmtNoticias->bindValue(':offset', $offset,    PDO::PARAM_INT);
    $stmtNoticias->execute();
    $noticias = $stmtNoticias->fetchAll();

    // Próximos eventos
    $stmtEventos = $conexion->prepare(
        "SELECT * FROM `eventos`
         WHERE `publicado` = 1 AND `fecha_evento` >= NOW()
         ORDER BY `fecha_evento` ASC
         LIMIT 3"
    );
    $stmtEventos->execute();
    $proximosEventos = $stmtEventos->fetchAll();

    // Noticia destacada (la más reciente)
    $stmtDestacada = $conexion->prepare(
        "SELECT * FROM `noticias` WHERE `publicada` = 1
         ORDER BY `fecha_publicacion` DESC LIMIT 1"
    );
    $stmtDestacada->execute();
    $noticiaDestacada = $stmtDestacada->fetch();

} catch (PDOException $e) {
    $noticias         = [];
    $totalNoticias    = 0;
    $totalPaginas     = 1;
    $proximosEventos  = [];
    $noticiaDestacada = null;
}
?>

<!-- HERO -->
<section class="noticias-hero">
    <div class="noticias-hero-overlay"></div>
    <div class="noticias-hero-contenido">
        <h1><i class="fa-solid fa-newspaper"></i> Noticias y Eventos</h1>
        <p>Mantente al día con las últimas novedades sobre adopciones, eventos y bienestar animal.</p>
    </div>
</section>

<section class="seccion">
<div class="contenedor">
<div class="noticias-layout">

    <!-- COLUMNA PRINCIPAL -->
    <div class="noticias-principal">

        <!-- Noticia destacada -->
        <?php if ($noticiaDestacada && empty($categoriaActiva) && $paginaActual === 1) {
            $imgDestacada = $noticiaDestacada['imagen']
                ? (str_starts_with($noticiaDestacada['imagen'], 'http') ? $noticiaDestacada['imagen'] : $noticiaDestacada['imagen'])
                : 'https://picsum.photos/900/400?random=' . $noticiaDestacada['id'];
        ?>
        <a href="noticias/detalle.php?slug=<?php echo urlencode($noticiaDestacada['slug']); ?>"
            class="noticia-destacada">
            <div class="noticia-destacada-img">
                <img src="<?php echo htmlspecialchars($imgDestacada); ?>"
                        alt="<?php echo htmlspecialchars($noticiaDestacada['titulo']); ?>">
                <span class="noticia-destacada-badge">Destacado</span>
            </div>
            <div class="noticia-destacada-info">
                <span class="noticia-cat noticia-cat-<?php echo strtolower($noticiaDestacada['categoria'] ?? 'general'); ?>">
                    <?php echo htmlspecialchars($noticiaDestacada['categoria'] ?? 'General'); ?>
                </span>
                <h2><?php echo htmlspecialchars($noticiaDestacada['titulo']); ?></h2>
                <p><?php echo htmlspecialchars(mb_substr(strip_tags($noticiaDestacada['contenido']), 0, 150)) . '...'; ?></p>
                <span class="noticia-fecha">
                    <i class="fa-regular fa-calendar"></i>
                    <?php echo date('d/m/Y', strtotime($noticiaDestacada['fecha_publicacion'])); ?>
                </span>
            </div>
        </a>
        <?php } ?>

        <!-- Filtros de categoría -->
        <div class="noticias-filtros">
            <a href="noticias.php"
                class="filtro-btn <?php echo empty($categoriaActiva) ? 'activo' : ''; ?>">
                Todas
            </a>
            <?php foreach ($categorias as $cat) { ?>
            <a href="noticias.php?categoria=<?php echo urlencode($cat); ?>"
                class="filtro-btn filtro-<?php echo strtolower($cat); ?> <?php echo $categoriaActiva === $cat ? 'activo' : ''; ?>">
                <?php echo $cat; ?>
            </a>
            <?php } ?>
        </div>

        <!-- Grid de noticias -->
        <?php if (!empty($noticias)) { ?>
        <div class="grid-noticias">
            <?php foreach ($noticias as $noticia) {
                $img = $noticia['imagen']
                    ? (str_starts_with($noticia['imagen'], 'http') ? $noticia['imagen'] : $noticia['imagen'])
                    : 'https://picsum.photos/400/250?random=' . $noticia['id'];
            ?>
            <a href="noticias/detalle.php?slug=<?php echo urlencode($noticia['slug']); ?>"
                class="tarjeta-noticia">
                <img src="<?php echo htmlspecialchars($img); ?>"
                        alt="<?php echo htmlspecialchars($noticia['titulo']); ?>">
                <div class="noticia-cuerpo">
                    <span class="noticia-categoria noticia-cat-<?php echo strtolower($noticia['categoria'] ?? 'general'); ?>">
                        <?php echo htmlspecialchars($noticia['categoria'] ?? 'General'); ?>
                    </span>
                    <h3><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                    <p><?php echo htmlspecialchars(mb_substr(strip_tags($noticia['contenido']), 0, 100)) . '...'; ?></p>
                    <span class="noticia-fecha">
                        <i class="fa-regular fa-calendar"></i>
                        <?php echo date('d/m/Y', strtotime($noticia['fecha_publicacion'])); ?>
                    </span>
                </div>
            </a>
            <?php } ?>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1) { ?>
        <div class="paginacion" style="margin-top:32px;">
            <?php for ($i = 1; $i <= $totalPaginas; $i++) { ?>
            <a href="noticias.php?pagina=<?php echo $i; ?>&categoria=<?php echo urlencode($categoriaActiva); ?>"
                class="paginacion-item <?php echo $i === $paginaActual ? 'activo' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php } ?>
        </div>
        <?php } ?>

        <?php } else { ?>
        <p class="sin-resultados">No hay noticias en esta categoría todavía.</p>
        <?php } ?>

    </div>

    <!-- SIDEBAR -->
    <aside class="noticias-sidebar">

        <!-- Próximos eventos -->
        <div class="sidebar-card">
            <h3><i class="fa-solid fa-calendar-days"></i> Próximos Eventos</h3>
            <?php if (!empty($proximosEventos)) { ?>
            <div class="eventos-lista">
                <?php foreach ($proximosEventos as $evento) { ?>
                <div class="evento-item">
                    <div class="evento-fecha">
                        <span class="evento-dia">
                            <?php echo date('d', strtotime($evento['fecha_evento'])); ?>
                        </span>
                        <span class="evento-mes">
                            <?php echo strtoupper(date('M', strtotime($evento['fecha_evento']))); ?>
                        </span>
                    </div>
                    <div class="evento-info">
                        <h4><?php echo htmlspecialchars($evento['titulo']); ?></h4>
                        <?php if ($evento['lugar']) { ?>
                        <p><i class="fa-solid fa-location-dot"></i>
                            <?php echo htmlspecialchars($evento['lugar']); ?>
                        </p>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } else { ?>
            <p style="color:#888; font-size:0.85rem;">No hay eventos próximos.</p>
            <?php } ?>
        </div>

        <!-- Categorías -->
        <div class="sidebar-card">
            <h3><i class="fa-solid fa-tags"></i> Categorías</h3>
            <div class="sidebar-categorias">
                <?php foreach ($categorias as $cat) { ?>
                <a href="noticias.php?categoria=<?php echo urlencode($cat); ?>"
                    class="sidebar-cat-item <?php echo $categoriaActiva === $cat ? 'activo' : ''; ?>">
                    <span><?php echo $cat; ?></span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                <?php } ?>
            </div>
        </div>

        <!-- CTA adoptar -->
        <div class="sidebar-card sidebar-cta">
            <i class="fa-solid fa-paw"></i>
            <h3>¿Buscas una mascota?</h3>
            <p>Miles de animales esperan un hogar amoroso.</p>
            <a href="adoptar.php" class="btn-coral" style="display:block; text-align:center; margin-top:12px;">
                Ver mascotas
            </a>
        </div>

    </aside>

</div>
</div>
</section>

<?php require_once 'templates/footer.php'; ?>