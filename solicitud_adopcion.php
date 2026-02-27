<?php
// DESPUÉS — session_start() la hace el header
$tituloPagina = 'Solicitar Adopcion — Huellas de Amor';
require_once 'templates/header.php';

// Proteger — solo usuarios registrados
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'templates/header.php';

// Validar que viene un ID de mascota
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
header('Location: adoptar.php');
exit();
}

$idMascota    = (int) $_GET['id'];
$idUsuario    = (int) $_SESSION['usuario_id'];
$mascota      = null;
$errores      = [];
$enviado      = false;

try {
// Obtener datos de la mascota
$stmt = $conexion->prepare(
"SELECT m.*, p.nombre AS nombre_protectora
    FROM `mascotas` m
    LEFT JOIN `protectoras` p ON m.id_protectora = p.id
    WHERE m.id = :id AND m.activo = 1 AND m.estado = 'disponible'
    LIMIT 1"
);
$stmt->bindParam(':id', $idMascota, PDO::PARAM_INT);
$stmt->execute();
$mascota = $stmt->fetch();

if (!$mascota) {
header('Location: adoptar.php');
exit();
}

// Verificar si el usuario ya tiene una solicitud pendiente para esta mascota
$stmtCheck = $conexion->prepare(
"SELECT COUNT(*) FROM `solicitudes_adopcion`
    WHERE `id_usuario` = :id_usuario AND `id_mascota` = :id_mascota
    AND `estado` IN ('pendiente', 'aprobada')"
);
$stmtCheck->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
$stmtCheck->bindParam(':id_mascota', $idMascota, PDO::PARAM_INT);
$stmtCheck->execute();
$yaExiste = $stmtCheck->fetchColumn();

} catch (PDOException $e) {
header('Location: adoptar.php');
exit();
}

// Obtener foto principal de la mascota

$fotoPrincipal = obtenerFotoPrincipal($idMascota, $conexion);
if (!$fotoPrincipal) {
    $fotoPrincipal = 'https://picsum.photos/400/300?random=' . $idMascota;
}

// PROCESAR FORMULARIO
if (isset($_POST['enviar'])) {

$motivacion      = trim($_POST['motivacion'] ?? '');
$situacionHogar  = trim($_POST['situacion_hogar'] ?? '');
$tieneAnimales   = isset($_POST['tiene_animales']) ? 1 : 0;
$descripcionHogar = trim($_POST['descripcion_hogar'] ?? '');
$experiencia     = trim($_POST['experiencia'] ?? '');

// Validaciones
if (empty($motivacion)) {
$errores[] = 'La motivacion es obligatoria.';
} elseif (strlen($motivacion) < 50) {
$errores[] = 'La motivacion debe tener al menos 50 caracteres.';
}

if (empty($situacionHogar)) {
$errores[] = 'Debes describir tu situacion de hogar.';
}

if ($yaExiste) {
$errores[] = 'Ya tienes una solicitud activa para esta mascota.';
}

if (empty($errores)) {
try {
    $stmtInsert = $conexion->prepare(
        "INSERT INTO `solicitudes_adopcion`
            (`id_usuario`, `id_mascota`, `estado`, `motivacion`, `situacion_hogar`, `tiene_animales`, `notas_admin`, `fecha_solicitud`)
            VALUES (:id_usuario, :id_mascota, 'pendiente', :motivacion, :situacion_hogar, :tiene_animales, '', NOW())"
    );
    $stmtInsert->bindParam(':id_usuario',     $idUsuario,       PDO::PARAM_INT);
    $stmtInsert->bindParam(':id_mascota',     $idMascota,       PDO::PARAM_INT);
    $stmtInsert->bindParam(':motivacion',     $motivacion,      PDO::PARAM_STR);
    $stmtInsert->bindParam(':situacion_hogar', $situacionHogar, PDO::PARAM_STR);
    $stmtInsert->bindParam(':tiene_animales', $tieneAnimales,   PDO::PARAM_INT);
    $stmtInsert->execute();

    $enviado = true;
    mensajeFlash('Tu solicitud ha sido enviada. Te contactaremos pronto.', 'exito');

} catch (PDOException $e) {
    $errores[] = 'Error al enviar la solicitud. Intentalo de nuevo.';
}
}
}
?>

<!-- Migas de pan -->
<div class="contenedor" style="padding-top:16px;">
<nav class="migas-pan">
<a href="index.php">Inicio</a>
<span><i class="fa-solid fa-chevron-right"></i></span>
<a href="adoptar.php">Adoptar</a>
<span><i class="fa-solid fa-chevron-right"></i></span>
<a href="mascotas/detalle.php?id=<?php echo $idMascota; ?>">
    <?php echo htmlspecialchars($mascota['nombre']); ?>
</a>
<span><i class="fa-solid fa-chevron-right"></i></span>
<span>Solicitar Adopcion</span>
</nav>
</div>

<section class="seccion">
<div class="contenedor">
<div class="solicitud-grid">

    <!-- COLUMNA IZQUIERDA: Info mascota -->
    <div class="solicitud-mascota">
        <div class="solicitud-mascota-card">
            <img src="<?php echo htmlspecialchars($fotoPrincipal); ?>"
                    alt="<?php echo htmlspecialchars($mascota['nombre']); ?>">
            <div class="solicitud-mascota-info">
                <h3><?php echo htmlspecialchars($mascota['nombre']); ?></h3>
                <p><?php echo formatearEdad($mascota['edad_anios'], $mascota['edad_meses']); ?></p>
                <div class="solicitud-chips">
                    <span><i class="fa-solid fa-paw"></i> <?php echo ucfirst($mascota['especie']); ?></span>
                    <span><i class="fa-solid fa-venus-mars"></i> <?php echo ucfirst($mascota['sexo']); ?></span>
                    <span><i class="fa-solid fa-ruler"></i> <?php echo ucfirst($mascota['tamanio']); ?></span>
                </div>
                <?php if ($mascota['nombre_protectora']) { ?>
                <p class="solicitud-protectora">
                    <i class="fa-solid fa-house-heart"></i>
                    <?php echo htmlspecialchars($mascota['nombre_protectora']); ?>
                </p>
                <?php } ?>
            </div>
        </div>

        <!-- Nota informativa -->
        <div class="solicitud-nota">
            <i class="fa-solid fa-circle-info"></i>
            <p>Tu solicitud sera revisada por nuestro equipo. Te contactaremos en un plazo de 3-5 dias habiles.</p>
        </div>
    </div>

    <!-- COLUMNA DERECHA: Formulario -->
    <div class="solicitud-formulario">

        <?php if ($enviado) { ?>
        <!-- EXITO -->
        <div class="solicitud-exito">
            <i class="fa-solid fa-circle-check"></i>
            <h2>¡Solicitud Enviada!</h2>
            <p>Tu solicitud de adopcion para <strong><?php echo htmlspecialchars($mascota['nombre']); ?></strong> ha sido enviada correctamente.</p>
            <p>Nuestro equipo la revisara y se pondra en contacto contigo pronto.</p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:24px;">
                <a href="adoptar.php" class="btn-outline-turquesa">Ver mas mascotas</a>
                <a href="usuario/panel.php" class="btn-coral">Mis solicitudes</a>
            </div>
        </div>

        <?php } elseif ($yaExiste) { ?>
        <!-- YA EXISTE SOLICITUD -->
        <div class="solicitud-exito solicitud-aviso">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <h2>Ya tienes una solicitud</h2>
            <p>Ya enviaste una solicitud de adopcion para <strong><?php echo htmlspecialchars($mascota['nombre']); ?></strong>.</p>
            <a href="usuario/panel.php" class="btn-coral" style="margin-top:16px; display:inline-block;">
                Ver mis solicitudes
            </a>
        </div>

        <?php } else { ?>
        <!-- FORMULARIO -->
        <h2><i class="fa-solid fa-heart"></i> Solicitar Adopcion</h2>
        <p class="solicitud-subtitulo">
            Cuéntanos sobre ti para que podamos encontrar el mejor hogar para
            <strong><?php echo htmlspecialchars($mascota['nombre']); ?></strong>.
        </p>

        <?php if (!empty($errores)) { ?>
        <div class="alerta alerta-error">
            <?php foreach ($errores as $error) { ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php } ?>
        </div>
        <?php } ?>

        <form method="post" action="solicitud_adopcion.php?id=<?php echo $idMascota; ?>" class="form-solicitud">

            <!-- Motivacion -->
            <div class="form-grupo">
                <label for="motivacion">
                    ¿Por que quieres adoptar a <?php echo htmlspecialchars($mascota['nombre']); ?>?
                    <span class="obligatorio">*</span>
                </label>
                <textarea id="motivacion" name="motivacion" rows="4"
                            placeholder="Cuéntanos tu motivacion, tu estilo de vida, cuanto tiempo pasas en casa... (minimo 50 caracteres)"
                            required><?php echo htmlspecialchars($_POST['motivacion'] ?? ''); ?></textarea>
                <small class="form-ayuda">Minimo 50 caracteres. Cuanto mas detallado, mejor.</small>
            </div>

            <!-- Situacion del hogar -->
            <div class="form-grupo">
                <label for="situacion_hogar">
                    Describe tu hogar <span class="obligatorio">*</span>
                </label>
                <select id="situacion_hogar" name="situacion_hogar" required>
                    <option value="">Selecciona una opcion</option>
                    <option value="piso_sin_terraza"
                        <?php echo ($_POST['situacion_hogar'] ?? '') === 'piso_sin_terraza' ? 'selected' : ''; ?>>
                        Piso sin terraza
                    </option>
                    <option value="piso_con_terraza"
                        <?php echo ($_POST['situacion_hogar'] ?? '') === 'piso_con_terraza' ? 'selected' : ''; ?>>
                        Piso con terraza o balcon
                    </option>
                    <option value="casa_sin_jardin"
                        <?php echo ($_POST['situacion_hogar'] ?? '') === 'casa_sin_jardin' ? 'selected' : ''; ?>>
                        Casa sin jardin
                    </option>
                    <option value="casa_con_jardin"
                        <?php echo ($_POST['situacion_hogar'] ?? '') === 'casa_con_jardin' ? 'selected' : ''; ?>>
                        Casa con jardin
                    </option>
                </select>
            </div>

            <!-- Descripcion del hogar -->
            <div class="form-grupo">
                <label for="descripcion_hogar">Informacion adicional sobre tu hogar</label>
                <textarea id="descripcion_hogar" name="descripcion_hogar" rows="3"
                            placeholder="¿Hay niños? ¿Cuanto espacio tienes? ¿Vives solo o en familia?"
                            ><?php echo htmlspecialchars($_POST['descripcion_hogar'] ?? ''); ?></textarea>
            </div>

            <!-- Experiencia con animales -->
            <div class="form-grupo">
                <label for="experiencia">¿Tienes experiencia con animales?</label>
                <textarea id="experiencia" name="experiencia" rows="2"
                            placeholder="¿Has tenido mascotas antes? ¿De que tipo?"
                            ><?php echo htmlspecialchars($_POST['experiencia'] ?? ''); ?></textarea>
            </div>

            <!-- Tiene animales -->
            <div class="form-grupo form-grupo-check">
                <label class="label-check">
                    <input type="checkbox" name="tiene_animales"
                            <?php echo isset($_POST['tiene_animales']) ? 'checked' : ''; ?>>
                    <span>Actualmente tengo otros animales en casa</span>
                </label>
            </div>

            <!-- Aceptar condiciones -->
            <div class="form-grupo form-grupo-check">
                <label class="label-check">
                    <input type="checkbox" name="acepta_condiciones" required>
                    <span>Acepto que la protectora puede hacer una visita de seguimiento tras la adopcion</span>
                </label>
            </div>

            <div class="form-botones">
                <button type="submit" name="enviar" class="btn-coral btn-grande">
                    <i class="fa-solid fa-paper-plane"></i> Enviar Solicitud
                </button>
                <a href="mascotas/detalle.php?id=<?php echo $idMascota; ?>"
                    class="btn-outline-turquesa btn-grande">
                    Cancelar
                </a>
            </div>

        </form>
        <?php } ?>

    </div>
</div>
</div>
</section>

<?php require_once 'templates/footer.php'; ?>