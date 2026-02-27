<?php
$tituloPagina = 'Editar Perfil — Huellas de Amor';
require_once '../templates/header.php';

// Proteger
if (!estaLogueado()) {
    header('Location: ../login.php');
    exit();
}

$idUsuario = (int) $_SESSION['usuario_id'];
$usuario   = null;
$errores   = [];
$exito     = false;

try {
    $stmt = $conexion->prepare("SELECT * FROM `usuarios` WHERE `id` = :id LIMIT 1");
    $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch();
} catch (PDOException $e) {
    header('Location: panel.php');
    exit();
}

// PROCESAR FORMULARIO DATOS PERSONALES
if (isset($_POST['guardar_datos'])) {
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $ciudad    = trim($_POST['ciudad'] ?? '');
    $cp        = trim($_POST['cp'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($nombre))    { $errores[] = 'El nombre es obligatorio.'; }
    if (empty($apellidos)) { $errores[] = 'Los apellidos son obligatorios.'; }

    if (empty($errores)) {
try {
    // Subir foto de perfil si se envió
    $rutaFoto = $usuario['foto_perfil'];
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $nuevaFoto = subirImagen($_FILES['foto_perfil'], 'uploads/perfiles/');
        if ($nuevaFoto) {
            $rutaFoto = $nuevaFoto;
        } else {
            $errores[] = 'Formato de imagen no permitido. Usa JPG, PNG o WEBP.';
        }
    }

    if (empty($errores)) {
        $stmtUpdate = $conexion->prepare(
            "UPDATE `usuarios` SET
                `nombre`      = :nombre,
                `apellidos`   = :apellidos,
                `telefono`    = :telefono,
                `ciudad`      = :ciudad,
                `cp`          = :cp,
                `direccion`   = :direccion,
                `foto_perfil` = :foto_perfil
                WHERE `id` = :id"
        );
        $stmtUpdate->bindParam(':nombre',      $nombre,    PDO::PARAM_STR);
        $stmtUpdate->bindParam(':apellidos',   $apellidos, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':telefono',    $telefono,  PDO::PARAM_STR);
        $stmtUpdate->bindParam(':ciudad',      $ciudad,    PDO::PARAM_STR);
        $stmtUpdate->bindParam(':cp',          $cp,        PDO::PARAM_STR);
        $stmtUpdate->bindParam(':direccion',   $direccion, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':foto_perfil', $rutaFoto,  PDO::PARAM_STR);
        $stmtUpdate->bindParam(':id',          $idUsuario, PDO::PARAM_INT);
        $stmtUpdate->execute();

        // Recargar datos del usuario
        $stmt = $conexion->prepare("SELECT * FROM `usuarios` WHERE `id` = :id LIMIT 1");
        $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch();

        mensajeFlash('Perfil actualizado correctamente.', 'exito');
        header('Location: editar_perfil.php');
        exit();
    }

} catch (PDOException $e) {
    $errores[] = 'Error al guardar los cambios. Intentalo de nuevo.';
}
}
}

// PROCESAR CAMBIO DE CONTRASEÑA
if (isset($_POST['cambiar_password'])) {
$passwordActual  = $_POST['password_actual'] ?? '';
$passwordNueva   = $_POST['password_nueva'] ?? '';
$passwordRepetir = $_POST['password_repetir'] ?? '';

if (empty($passwordActual))  { $errores[] = 'Introduce tu contraseña actual.'; }
if (strlen($passwordNueva) < 6) { $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres.'; }
if ($passwordNueva !== $passwordRepetir) { $errores[] = 'Las contraseñas nuevas no coinciden.'; }

if (empty($errores)) {
if (!password_verify($passwordActual, $usuario['contrasena'])) {
    $errores[] = 'La contraseña actual no es correcta.';
} else {
    try {
        $hashNuevo = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $stmtPass  = $conexion->prepare(
            "UPDATE `usuarios` SET `contrasena` = :pass WHERE `id` = :id"
        );
        $stmtPass->bindParam(':pass', $hashNuevo,  PDO::PARAM_STR);
        $stmtPass->bindParam(':id',   $idUsuario, PDO::PARAM_INT);
        $stmtPass->execute();

        mensajeFlash('Contraseña cambiada correctamente.', 'exito');
        header('Location: editar_perfil.php');
        exit();

    } catch (PDOException $e) {
        $errores[] = 'Error al cambiar la contraseña.';
    }
}
}
}

// Foto de perfil con ruta corregida
$fotoPerfilUrl = '';
if ($usuario['foto_perfil']) {
    $fotoPerfilUrl = str_starts_with($usuario['foto_perfil'], 'http')
        ? $usuario['foto_perfil']
        : '../' . ltrim($usuario['foto_perfil'], '/');
}
?>

<!-- Migas de pan -->
<div class="contenedor" style="padding-top:16px;">
    <nav class="migas-pan">
        <a href="../index.php">Inicio</a>
        <span><i class="fa-solid fa-chevron-right"></i></span>
        <a href="panel.php">Mi Panel</a>
        <span><i class="fa-solid fa-chevron-right"></i></span>
        <span>Editar Perfil</span>
    </nav>
</div>

<section class="seccion">
    <div class="contenedor">
        <div class="panel-grid">

            <!-- SIDEBAR -->
            <aside class="panel-sidebar">
                <div class="panel-perfil-card">
                    <div class="panel-avatar">
                        <?php if ($fotoPerfilUrl) { ?>
                            <img src="<?php echo htmlspecialchars($fotoPerfilUrl); ?>"
                                 alt="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                        <?php } else { ?>
                            <i class="fa-solid fa-user"></i>
                        <?php } ?>
                    </div>
                    <h3><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h3>
                    <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                    <span class="badge badge-disponible">Adoptante</span>
                </div>

                <nav class="panel-menu">
                    <a href="panel.php?tab=solicitudes" class="panel-menu-item">
                        <i class="fa-solid fa-file-pen"></i><span>Mis Solicitudes</span>
                    </a>
                    <a href="panel.php?tab=favoritos" class="panel-menu-item">
                        <i class="fa-solid fa-heart"></i><span>Mis Favoritos</span>
                    </a>
                    <a href="panel.php?tab=apadrinamientos" class="panel-menu-item">
                        <i class="fa-solid fa-star"></i><span>Mis Apadrinamientos</span>
                    </a>
                    <a href="panel.php?tab=donaciones" class="panel-menu-item">
                        <i class="fa-solid fa-hand-holding-heart"></i><span>Mis Donaciones</span>
                    </a>
                    <a href="panel.php?tab=perfil" class="panel-menu-item activo">
                        <i class="fa-solid fa-user-pen"></i><span>Mi Perfil</span>
                    </a>
                    <a href="../logout.php" class="panel-menu-item panel-menu-salir">
                        <i class="fa-solid fa-right-from-bracket"></i><span>Cerrar Sesion</span>
                    </a>
                </nav>
            </aside>

            <!-- CONTENIDO -->
            <div class="panel-contenido">
                <div class="panel-seccion">

                    <?php if (!empty($errores)) { ?>
                    <div class="alerta alerta-error">
                        <?php foreach ($errores as $error) { ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <!-- FORMULARIO DATOS PERSONALES -->
                    <h2><i class="fa-solid fa-user-pen"></i> Editar Perfil</h2>

                    <form method="post" action="editar_perfil.php"
                          enctype="multipart/form-data" class="form-editar-perfil">

                        <!-- Foto de perfil -->
                        <div class="editar-foto-grupo">
                            <div class="editar-foto-preview" id="fotoPreview">
                                <?php if ($fotoPerfilUrl) { ?>
                                    <img src="<?php echo htmlspecialchars($fotoPerfilUrl); ?>"
                                         alt="Foto de perfil" id="imgPreview">
                                <?php } else { ?>
                                    <i class="fa-solid fa-user" id="iconoDefault"></i>
                                    <img src="" alt="" id="imgPreview" style="display:none;">
                                <?php } ?>
                            </div>
                            <div class="editar-foto-info">
                                <label for="foto_perfil" class="btn-outline-turquesa btn-sm" style="cursor:pointer;">
                                    <i class="fa-solid fa-camera"></i> Cambiar foto
                                </label>
                                <input type="file" id="foto_perfil" name="foto_perfil"
                                       accept="image/jpeg,image/png,image/webp" style="display:none;">
                                <small>JPG, PNG o WEBP. Max 2MB.</small>
                            </div>
                        </div>

                        <div class="form-dos-columnas">
                            <div class="form-grupo">
                                <label for="nombre">Nombre <span class="obligatorio">*</span></label>
                                <input type="text" id="nombre" name="nombre"
                                       value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                                       required>
                            </div>
                            <div class="form-grupo">
                                <label for="apellidos">Apellidos <span class="obligatorio">*</span></label>
                                <input type="text" id="apellidos" name="apellidos"
                                       value="<?php echo htmlspecialchars($usuario['apellidos']); ?>"
                                       required>
                            </div>
                        </div>

                        <div class="form-grupo">
                            <label for="email">Correo electronico</label>
                            <input type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>"
                                   disabled style="background:#f7f9fc; cursor:not-allowed;">
                            <small class="form-ayuda">El email no se puede cambiar.</small>
                        </div>

                        <div class="form-dos-columnas">
                            <div class="form-grupo">
                                <label for="telefono">Telefono</label>
                                <input type="tel" id="telefono" name="telefono"
                                       value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                                       placeholder="612 345 678">
                            </div>
                            <div class="form-grupo">
                                <label for="ciudad">Ciudad</label>
                                <input type="text" id="ciudad" name="ciudad"
                                       value="<?php echo htmlspecialchars($usuario['ciudad'] ?? ''); ?>"
                                       placeholder="Tu ciudad">
                            </div>
                        </div>

                        <div class="form-dos-columnas">
                            <div class="form-grupo">
                                <label for="direccion">Direccion</label>
                                <input type="text" id="direccion" name="direccion"
                                       value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>"
                                       placeholder="Calle, numero...">
                            </div>
                            <div class="form-grupo">
                                <label for="cp">Codigo Postal</label>
                                <input type="text" id="cp" name="cp"
                                       value="<?php echo htmlspecialchars($usuario['cp'] ?? ''); ?>"
                                       placeholder="03000">
                            </div>
                        </div>

                        <div class="form-botones">
                            <button type="submit" name="guardar_datos" class="btn-coral btn-grande">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                            </button>
                            <a href="panel.php" class="btn-outline-turquesa btn-grande">Cancelar</a>
                        </div>

                    </form>

                    <!-- SEPARADOR -->
                    <hr style="margin: 32px 0; border-color: #f0f0f0;">

                    <!-- CAMBIAR CONTRASEÑA -->
                    <h2><i class="fa-solid fa-lock"></i> Cambiar Contraseña</h2>

                    <form method="post" action="editar_perfil.php" class="form-editar-perfil">

                        <div class="form-grupo">
                            <label for="password_actual">Contraseña actual <span class="obligatorio">*</span></label>
                            <input type="password" id="password_actual" name="password_actual"
                                   placeholder="Tu contraseña actual" required>
                        </div>

                        <div class="form-dos-columnas">
                            <div class="form-grupo">
                                <label for="password_nueva">Nueva contraseña <span class="obligatorio">*</span></label>
                                <input type="password" id="password_nueva" name="password_nueva"
                                       placeholder="Minimo 6 caracteres" required>
                            </div>
                            <div class="form-grupo">
                                <label for="password_repetir">Repetir contraseña <span class="obligatorio">*</span></label>
                                <input type="password" id="password_repetir" name="password_repetir"
                                       placeholder="Repite la nueva contraseña" required>
                            </div>
                        </div>

                        <div class="form-botones">
                            <button type="submit" name="cambiar_password" class="btn-oscuro btn-grande">
                                <i class="fa-solid fa-key"></i> Cambiar Contraseña
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- Preview foto antes de subir -->
<script>
document.getElementById('foto_perfil').addEventListener('change', function() {
    const archivo = this.files[0];
    if (!archivo) { return; }

    const reader  = new FileReader();
    const preview = document.getElementById('imgPreview');
    const icono   = document.getElementById('iconoDefault');

    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        if (icono) { icono.style.display = 'none'; }
    };

    reader.readAsDataURL(archivo);
});
</script>

<?php require_once '../templates/footer.php'; ?>