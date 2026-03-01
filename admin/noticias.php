<?php
$tituloPagina = 'Gestión de Noticias — Admin';
require_once '../templates/header-admin.php';
protegerAdmin();

$categorias = ['Eventos', 'Consejos', 'Salud', 'Leyes', 'Adopciones'];
$accion     = isset($_GET['accion']) ? trim($_GET['accion']) : 'listado';
$idEditar   = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;
$errores    = [];
$exito      = '';

// ELIMINAR
if (isset($_GET['borrar']) && is_numeric($_GET['borrar'])) {
    try {
        $stmtBorrar = $conexion->prepare("DELETE FROM `noticias` WHERE `id` = :id");
        $stmtBorrar->bindValue(':id', (int) $_GET['borrar'], PDO::PARAM_INT);
        $stmtBorrar->execute();
        mensajeFlash('Noticia eliminada correctamente.', 'exito');
    } catch (PDOException $e) {
        mensajeFlash('Error al eliminar la noticia.', 'error');
    }
    header('Location: noticias.php');
    exit();
}

// PUBLICAR / DESPUBLICAR
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    try {
        $stmtToggle = $conexion->prepare(
            "UPDATE `noticias`
             SET `publicada` = IF(`publicada` = 1, 0, 1),
                 `fecha_publicacion` = IF(`publicada` = 0, NOW(), `fecha_publicacion`)
             WHERE `id` = :id"
        );
        $stmtToggle->bindValue(':id', (int) $_GET['toggle'], PDO::PARAM_INT);
        $stmtToggle->execute();
        mensajeFlash('Estado de la noticia actualizado.', 'exito');
    } catch (PDOException $e) {
        mensajeFlash('Error al actualizar el estado.', 'error');
    }
    header('Location: noticias.php');
    exit();
}

// GUARDAR (crear o editar)
if (isset($_POST['guardar'])) {
    $titulo    = trim($_POST['titulo']    ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $publicada = isset($_POST['publicada']) ? 1 : 0;
    $idAdmin   = (int) $_SESSION['usuario_id'];

    // Generar slug desde titulo
    $slug = strtolower(trim($titulo));
    $slug = preg_replace('/[áàäâ]/u', 'a', $slug);
    $slug = preg_replace('/[éèëê]/u', 'e', $slug);
    $slug = preg_replace('/[íìïî]/u', 'i', $slug);
    $slug = preg_replace('/[óòöô]/u', 'o', $slug);
    $slug = preg_replace('/[úùüû]/u', 'u', $slug);
    $slug = preg_replace('/[ñ]/u',    'n', $slug);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');

    // Validaciones
    if (empty($titulo)) { $errores[] = 'El título es obligatorio.'; }
    if (empty($contenido)) { $errores[] = 'El contenido es obligatorio.'; }
    if (empty($categoria) || !in_array($categoria, $categorias)) { $errores[] = 'Selecciona una categoría válida.'; }

    // Subir imagen si se adjuntó
    $rutaImagen = $_POST['imagen_actual'] ?? '';
    if (!empty($_FILES['imagen']['name'])) {
        $resultadoImg = subirImagen($_FILES['imagen'], 'uploads/noticias/');
        if ($resultadoImg) {
            $rutaImagen = $resultadoImg;
        } else {
            $errores[] = 'Error al subir la imagen. Formatos permitidos: JPG, PNG, WEBP.';
        }
    }

    if (empty($errores)) {
        try {
            $fechaPublicacion = $publicada ? date('Y-m-d H:i:s') : null;

            if ($idEditar > 0) {
                // EDITAR
                $stmtGuardar = $conexion->prepare(
                    "UPDATE `noticias`
                     SET `titulo` = :titulo,
                         `slug` = :slug,
                         `contenido` = :contenido,
                         `imagen` = :imagen,
                         `categoria` = :categoria,
                         `publicada` = :publicada,
                         `fecha_publicacion` = COALESCE(:fecha, `fecha_publicacion`)
                     WHERE `id` = :id"
                );
                $stmtGuardar->bindParam(':id', $idEditar, PDO::PARAM_INT);
            } else {
                // CREAR
                $stmtGuardar = $conexion->prepare(
                    "INSERT INTO `noticias`
                     (`titulo`, `slug`, `contenido`, `imagen`, `id_admin`, `categoria`, `publicada`, `fecha_publicacion`)
                     VALUES (:titulo, :slug, :contenido, :imagen, :id_admin, :categoria, :publicada, :fecha)"
                );
                $stmtGuardar->bindParam(':id_admin', $idAdmin, PDO::PARAM_INT);
            }

            $stmtGuardar->bindParam(':titulo',    $titulo,          PDO::PARAM_STR);
            $stmtGuardar->bindParam(':slug',      $slug,            PDO::PARAM_STR);
            $stmtGuardar->bindParam(':contenido', $contenido,       PDO::PARAM_STR);
            $stmtGuardar->bindParam(':imagen',    $rutaImagen,      PDO::PARAM_STR);
            $stmtGuardar->bindParam(':categoria', $categoria,       PDO::PARAM_STR);
            $stmtGuardar->bindParam(':publicada', $publicada,       PDO::PARAM_INT);
            $stmtGuardar->bindValue(':fecha',     $fechaPublicacion, $fechaPublicacion ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtGuardar->execute();

            mensajeFlash($idEditar > 0 ? 'Noticia actualizada correctamente.' : 'Noticia creada correctamente.', 'exito');
            header('Location: noticias.php');
            exit();

        } catch (PDOException $e) {
            $errores[] = 'Error al guardar: ' . $e->getMessage();
        }
    }

    $accion = $idEditar > 0 ? 'editar' : 'nueva';
}

// CARGAR NOTICIA PARA EDITAR
$noticiaEditar = null;
if (($accion === 'editar') && $idEditar > 0) {
    try {
        $stmtEditar = $conexion->prepare("SELECT * FROM `noticias` WHERE `id` = :id LIMIT 1");
        $stmtEditar->bindValue(':id', $idEditar, PDO::PARAM_INT);
        $stmtEditar->execute();
        $noticiaEditar = $stmtEditar->fetch();
        if (!$noticiaEditar) {
            header('Location: noticias.php');
            exit();
        }
    } catch (PDOException $e) {
        header('Location: noticias.php');
        exit();
    }
}

// LISTADO
$noticias      = [];
$totalNoticias = 0;
$filtroCategoria = trim($_GET['categoria'] ?? '');
$filtroBuscar    = trim($_GET['buscar']    ?? '');
$porPagina       = 10;
$paginaActual    = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset          = ($paginaActual - 1) * $porPagina;

if ($accion === 'listado') {
    try {
        $donde  = "WHERE 1=1";
        $params = [];

        if (!empty($filtroCategoria)) {
            $donde .= " AND `categoria` = :categoria";
            $params[':categoria'] = $filtroCategoria;
        }
        if (!empty($filtroBuscar)) {
            $donde .= " AND `titulo` LIKE :buscar";
            $params[':buscar'] = '%' . $filtroBuscar . '%';
        }

        $stmtTotal = $conexion->prepare("SELECT COUNT(*) FROM `noticias` $donde");
        foreach ($params as $k => $v) { $stmtTotal->bindValue($k, $v); }
        $stmtTotal->execute();
        $totalNoticias = $stmtTotal->fetchColumn();
        $totalPaginas  = ceil($totalNoticias / $porPagina);

        $stmtList = $conexion->prepare(
            "SELECT * FROM `noticias` $donde
             ORDER BY `fecha_publicacion` DESC
             LIMIT :limite OFFSET :offset"
        );
        foreach ($params as $k => $v) { $stmtList->bindValue($k, $v); }
        $stmtList->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmtList->bindValue(':offset', $offset,    PDO::PARAM_INT);
        $stmtList->execute();
        $noticias = $stmtList->fetchAll();

    } catch (PDOException $e) {
        $noticias = [];
    }
}
?>

<div class="contenedor" style="padding-top:24px; padding-bottom:40px;">
<div class="contenedor-admin">

<?php if ($accion === 'listado') { ?>
<!-- LISTADO-->
<div class="admin-cabecera">
    <h1><i class="fa-solid fa-newspaper"></i> Noticias</h1>
    <a href="noticias.php?accion=nueva" class="btn-coral btn-sm">
        <i class="fa-solid fa-plus"></i> Nueva Noticia
    </a>
</div>

<!-- Filtros -->
<div class="barra-admin">
    <form method="get" action="noticias.php" class="form-filtro">
        <input type="hidden" name="accion" value="listado">
        <input type="text" name="buscar"
               placeholder="Buscar por título..."
               value="<?php echo htmlspecialchars($filtroBuscar); ?>">
        <select name="categoria">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $cat) { ?>
            <option value="<?php echo $cat; ?>"
                <?php echo $filtroCategoria === $cat ? 'selected' : ''; ?>>
                <?php echo $cat; ?>
            </option>
            <?php } ?>
        </select>
        <button type="submit" class="btn-turquesa btn-sm">
            <i class="fa-solid fa-magnifying-glass"></i> Filtrar
        </button>
        <?php if (!empty($filtroCategoria) || !empty($filtroBuscar)) { ?>
        <a href="noticias.php" class="btn-outline-coral btn-sm">Limpiar</a>
        <?php } ?>
    </form>
</div>

<p style="color:#888; margin-bottom:16px;">
    <?php echo $totalNoticias; ?> noticia<?php echo $totalNoticias !== 1 ? 's' : ''; ?> encontrada<?php echo $totalNoticias !== 1 ? 's' : ''; ?>
</p>

<?php if (!empty($noticias)) { ?>
<div class="tabla-wrapper">
    <table class="tabla-admin">
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Título</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($noticias as $noticia) {
                $img = $noticia['imagen']
                    ? (str_starts_with($noticia['imagen'], 'http') ? $noticia['imagen'] : '../' . $noticia['imagen'])
                    : 'https://picsum.photos/80/60?random=' . $noticia['id'];
            ?>
            <tr>
                <td>
                    <img src="<?php echo htmlspecialchars($img); ?>"
                         style="width:80px; height:55px; object-fit:cover; border-radius:6px;">
                </td>
                <td>
                    <strong><?php echo htmlspecialchars($noticia['titulo']); ?></strong>
                    <br><small style="color:#aaa;"><?php echo htmlspecialchars($noticia['slug']); ?></small>
                </td>
                <td>
                    <span class="badge badge-disponible" style="font-size:0.7rem;">
                        <?php echo htmlspecialchars($noticia['categoria'] ?? '—'); ?>
                    </span>
                </td>
                <td>
                    <?php if ($noticia['publicada']) { ?>
                        <span class="badge badge-disponible">Publicada</span>
                    <?php } else { ?>
                        <span class="badge badge-adoptado">Borrador</span>
                    <?php } ?>
                </td>
                <td>
                    <?php echo $noticia['fecha_publicacion']
                        ? date('d/m/Y', strtotime($noticia['fecha_publicacion']))
                        : '—'; ?>
                </td>
                <td class="acciones">
                    <a href="../noticias/detalle.php?slug=<?php echo urlencode($noticia['slug']); ?>"
                       target="_blank" class="btn-nuevo btn-sm" title="Ver en web">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="noticias.php?accion=editar&id=<?php echo $noticia['id']; ?>"
                       class="btn-editar btn-sm" title="Editar">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <a href="noticias.php?toggle=<?php echo $noticia['id']; ?>"
                       class="btn-sm <?php echo $noticia['publicada'] ? 'btn-oscuro' : 'btn-turquesa'; ?>"
                       title="<?php echo $noticia['publicada'] ? 'Despublicar' : 'Publicar'; ?>">
                        <i class="fa-solid <?php echo $noticia['publicada'] ? 'fa-eye-slash' : 'fa-globe'; ?>"></i>
                    </a>
                    <a href="noticias.php?borrar=<?php echo $noticia['id']; ?>"
                       class="btn-borrar btn-sm" title="Eliminar"
                       onclick="return confirm('¿Eliminar esta noticia? Esta acción no se puede deshacer.')">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Paginacion -->
<?php if (!empty($totalPaginas) && $totalPaginas > 1) { ?>
<div class="paginacion" style="margin-top:24px;">
    <?php for ($i = 1; $i <= $totalPaginas; $i++) { ?>
    <a href="noticias.php?pagina=<?php echo $i; ?>&categoria=<?php echo urlencode($filtroCategoria); ?>&buscar=<?php echo urlencode($filtroBuscar); ?>"
       class="paginacion-item <?php echo $i === $paginaActual ? 'activo' : ''; ?>">
        <?php echo $i; ?>
    </a>
    <?php } ?>
</div>
<?php } ?>

<?php } else { ?>
<p class="sin-resultados">No hay noticias todavía. <a href="noticias.php?accion=nueva">Crea la primera</a>.</p>
<?php } ?>

<?php } else { ?>
<!-- FORMULARIO CREAR / EDITAR-->

<div class="admin-cabecera">
    <h1>
        <i class="fa-solid fa-<?php echo $idEditar > 0 ? 'pen' : 'plus'; ?>"></i>
        <?php echo $idEditar > 0 ? 'Editar Noticia' : 'Nueva Noticia'; ?>
    </h1>
    <a href="noticias.php" class="btn-outline-coral btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Volver al listado
    </a>
</div>

<?php if (!empty($errores)) { ?>
<div class="alerta alerta-error" style="margin-bottom:24px;">
    <?php foreach ($errores as $error) { ?>
        <p><?php echo htmlspecialchars($error); ?></p>
    <?php } ?>
</div>
<?php } ?>

<form method="post"
      action="noticias.php?accion=<?php echo $idEditar > 0 ? 'editar&id=' . $idEditar : 'nueva'; ?>"
      enctype="multipart/form-data"
      class="form-admin-noticia">

    <input type="hidden" name="imagen_actual"
           value="<?php echo htmlspecialchars($noticiaEditar['imagen'] ?? ''); ?>">

    <div class="form-admin-grid">

        <!-- Columna principal -->
        <div class="form-admin-principal">

            <!-- Titulo -->
            <div class="form-grupo">
                <label for="titulo">Título <span class="obligatorio">*</span></label>
                <input type="text" id="titulo" name="titulo"
                       value="<?php echo htmlspecialchars($_POST['titulo'] ?? $noticiaEditar['titulo'] ?? ''); ?>"
                       placeholder="Título de la noticia" required
                       oninput="generarSlug(this.value)">
                <small class="form-ayuda">
                    Slug: <span id="slugPreview" style="color:#4ECDC4; font-weight:700;">
                        <?php echo htmlspecialchars($noticiaEditar['slug'] ?? ''); ?>
                    </span>
                </small>
            </div>

            <!-- Contenido TinyMCE -->
            <div class="form-grupo">
                <label for="contenido">Contenido <span class="obligatorio">*</span></label>
                <textarea id="contenido" name="contenido" rows="15"><?php
                    echo htmlspecialchars($_POST['contenido'] ?? $noticiaEditar['contenido'] ?? '');
                ?></textarea>
            </div>

        </div>

        <!-- Columna lateral -->
        <div class="form-admin-lateral">

            <!-- Publicar -->
            <div class="admin-lateral-card">
                <h3><i class="fa-solid fa-globe"></i> Publicación</h3>
                <label class="toggle-label">
                    <input type="checkbox" name="publicada" id="publicada"
                           <?php echo (!empty($_POST) ? isset($_POST['publicada']) : ($noticiaEditar['publicada'] ?? 0)) ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                    <span>Publicar noticia</span>
                </label>
                <p style="font-size:0.8rem; color:#aaa; margin-top:8px;">
                    Si no está marcada, se guarda como borrador y no es visible al público.
                </p>
                <div style="margin-top:16px;">
                    <button type="submit" name="guardar" class="btn-coral" style="width:100%;">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <?php echo $idEditar > 0 ? 'Actualizar' : 'Guardar'; ?> Noticia
                    </button>
                </div>
            </div>

            <!-- Categoria -->
            <div class="admin-lateral-card">
                <h3><i class="fa-solid fa-tag"></i> Categoría</h3>
                <div class="form-grupo">
                    <select name="categoria" required>
                        <option value="">Selecciona categoría</option>
                        <?php foreach ($categorias as $cat) { ?>
                        <option value="<?php echo $cat; ?>"
                            <?php echo ($_POST['categoria'] ?? $noticiaEditar['categoria'] ?? '') === $cat ? 'selected' : ''; ?>>
                            <?php echo $cat; ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <!-- Imagen -->
            <div class="admin-lateral-card">
                <h3><i class="fa-solid fa-image"></i> Imagen destacada</h3>

                <?php
                $imgActual = $noticiaEditar['imagen'] ?? '';
                if ($imgActual) {
                    $srcImg = str_starts_with($imgActual, 'http') ? $imgActual : '../' . $imgActual;
                ?>
                <div style="margin-bottom:12px;">
                    <img src="<?php echo htmlspecialchars($srcImg); ?>"
                         id="previewImagen"
                         style="width:100%; border-radius:8px; object-fit:cover; max-height:160px;">
                </div>
                <?php } else { ?>
                <div id="previewContenedor" style="display:none; margin-bottom:12px;">
                    <img id="previewImagen"
                         style="width:100%; border-radius:8px; object-fit:cover; max-height:160px;">
                </div>
                <?php } ?>

                <div class="form-grupo">
                    <label for="imagen">
                        <?php echo $imgActual ? 'Cambiar imagen' : 'Subir imagen'; ?>
                    </label>
                    <input type="file" id="imagen" name="imagen"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="previsualizarImagen(this)">
                    <small class="form-ayuda">JPG, PNG o WEBP. Máx. 5MB.</small>
                </div>
            </div>

        </div>
    </div>

</form>

<!-- TinyMCE CDN gratuito -->
<script src="https://cdn.tiny.cloud/1/doztpv5qp8ep1p9byrajp1kb493nxwei6l7sue0ju7p5woqp/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
<script>
tinymce.init({
    selector: '#contenido',
    language: 'es',
    height: 450,
    menubar: false,
    plugins: 'lists link image code wordcount',
    toolbar: 'undo redo | formatselect | bold italic underline | ' +
             'alignleft aligncenter alignright | ' +
             'bullist numlist | link image | code',
    content_style: 'body { font-family: Lato, sans-serif; font-size: 16px; color: #444; line-height: 1.8; }',
    branding: false,
    promotion: false
});

// Generar slug automatico desde el titulo
function generarSlug(texto) {
    let slug = texto.toLowerCase()
        .replace(/[áàäâ]/g, 'a')
        .replace(/[éèëê]/g, 'e')
        .replace(/[íìïî]/g, 'i')
        .replace(/[óòöô]/g, 'o')
        .replace(/[úùüû]/g, 'u')
        .replace(/[ñ]/g, 'n')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('slugPreview').textContent = slug;
}

// Preview imagen antes de subir
function previsualizarImagen(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('previewImagen');
            if (img) {
                img.src = e.target.result;
                const cont = document.getElementById('previewContenedor');
                if (cont) { cont.style.display = 'block'; }
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php } ?>

</div>
</div>

<?php require_once '../templates/footer-admin.php'; ?>