<?php
// ============================================================
// admin/mascota_editar.php — Editar mascota existente
// Requisito DAWES: UPDATE con PDO
// ============================================================

$tituloPagina = 'Editar Mascota — Admin';
require_once '../templates/header-admin.php';

$error = '';

// Comprobar que viene un id por GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mascotas.php');
    exit();
}

$idMascota = (int)$_GET['id'];

// Obtener protectoras para el select
$protectoras = [];
try {
    $stmt = $conexion->prepare("SELECT `id`, `nombre` FROM `protectoras` WHERE `activa` = 1 ORDER BY `nombre`");
    $stmt->execute();
    $protectoras = $stmt->fetchAll();
} catch (PDOException $e) {
    $protectoras = [];
}

// Obtener los datos actuales de la mascota
try {
    $sql  = "SELECT * FROM `mascotas` WHERE `id` = :id AND `activo` = 1 LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bindValue(':id', $idMascota, PDO::PARAM_INT);
    $stmt->execute();
    $mascota = $stmt->fetch();

    if (!$mascota) {
        header('Location: mascotas.php');
        exit();
    }

    // Obtener foto principal actual
    $stmtFoto = $conexion->prepare("SELECT * FROM `fotos_mascotas` WHERE `id_mascota` = :id AND `es_principal` = 1 LIMIT 1");
    $stmtFoto->bindValue(':id', $idMascota, PDO::PARAM_INT);
    $stmtFoto->execute();
    $fotoPrincipal = $stmtFoto->fetch();

} catch (PDOException $e) {
    header('Location: mascotas.php');
    exit();
}

// Procesar el formulario cuando se envia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre       = isset($_POST['nombre'])       ? trim($_POST['nombre'])       : '';
    $especie      = isset($_POST['especie'])       ? trim($_POST['especie'])      : '';
    $raza         = isset($_POST['raza'])          ? trim($_POST['raza'])         : '';
    $edadAnios    = isset($_POST['edad_anios'])    ? (int)$_POST['edad_anios']    : 0;
    $edadMeses    = isset($_POST['edad_meses'])    ? (int)$_POST['edad_meses']    : 0;
    $sexo         = isset($_POST['sexo'])          ? trim($_POST['sexo'])         : '';
    $tamanio      = isset($_POST['tamanio'])       ? trim($_POST['tamanio'])      : '';
    $descripcion  = isset($_POST['descripcion'])   ? trim($_POST['descripcion'])  : '';
    $caracter     = isset($_POST['caracter'])      ? trim($_POST['caracter'])     : '';
    $estado       = isset($_POST['estado'])        ? trim($_POST['estado'])       : 'disponible';
    $idProtectora = isset($_POST['id_protectora']) && !empty($_POST['id_protectora']) ? (int)$_POST['id_protectora'] : null;
    $fechaIngreso = isset($_POST['fecha_ingreso']) ? trim($_POST['fecha_ingreso']) : '';

    if (empty($nombre) || empty($especie) || empty($sexo)) {
        $error = 'El nombre, la especie y el sexo son obligatorios.';
    } else {

        try {
            $sql = "UPDATE `mascotas` SET
                        `nombre`        = :nombre,
                        `especie`       = :especie,
                        `raza`          = :raza,
                        `edad_anios`    = :edad_anios,
                        `edad_meses`    = :edad_meses,
                        `sexo`          = :sexo,
                        `tamanio`       = :tamanio,
                        `descripcion`   = :descripcion,
                        `caracter`      = :caracter,
                        `estado`        = :estado,
                        `id_protectora` = :id_protectora,
                        `fecha_ingreso` = :fecha_ingreso
                    WHERE `id` = :id";

            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':nombre',        $nombre,       PDO::PARAM_STR);
            $stmt->bindValue(':especie',       $especie,      PDO::PARAM_STR);
            $stmt->bindValue(':raza',          $raza,         PDO::PARAM_STR);
            $stmt->bindValue(':edad_anios',    $edadAnios,    PDO::PARAM_INT);
            $stmt->bindValue(':edad_meses',    $edadMeses,    PDO::PARAM_INT);
            $stmt->bindValue(':sexo',          $sexo,         PDO::PARAM_STR);
            $stmt->bindValue(':tamanio',       $tamanio,      PDO::PARAM_STR);
            $stmt->bindValue(':descripcion',   $descripcion,  PDO::PARAM_STR);
            $stmt->bindValue(':caracter',      $caracter,     PDO::PARAM_STR);
            $stmt->bindValue(':estado',        $estado,       PDO::PARAM_STR);
            $stmt->bindValue(':id_protectora', $idProtectora, PDO::PARAM_INT);
            $stmt->bindValue(':fecha_ingreso', $fechaIngreso, PDO::PARAM_STR);
            $stmt->bindValue(':id',            $idMascota,    PDO::PARAM_INT);
            $stmt->execute();

            // Si se sube una foto nueva, reemplazar la anterior
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

                $rutaFoto = subirImagen($_FILES['foto'], 'uploads/mascotas/');

                if ($rutaFoto) {
                    if ($fotoPrincipal) {
                        // Actualizar la foto existente
                        $sqlFoto = "UPDATE `fotos_mascotas` SET `ruta_foto` = :ruta_foto
                                    WHERE `id_mascota` = :id_mascota AND `es_principal` = 1";
                        $stmtFoto = $conexion->prepare($sqlFoto);
                        $stmtFoto->bindValue(':ruta_foto',  $rutaFoto,  PDO::PARAM_STR);
                        $stmtFoto->bindValue(':id_mascota', $idMascota, PDO::PARAM_INT);
                        $stmtFoto->execute();
                    } else {
                        // Insertar nueva foto principal
                        $sqlFoto = "INSERT INTO `fotos_mascotas` (`id_mascota`, `ruta_foto`, `es_principal`)
                                    VALUES (:id_mascota, :ruta_foto, 1)";
                        $stmtFoto = $conexion->prepare($sqlFoto);
                        $stmtFoto->bindValue(':id_mascota', $idMascota, PDO::PARAM_INT);
                        $stmtFoto->bindValue(':ruta_foto',  $rutaFoto,  PDO::PARAM_STR);
                        $stmtFoto->execute();
                    }
                }
            }

            $_SESSION['mensaje']      = 'Mascota actualizada correctamente.';
            $_SESSION['mensaje_tipo'] = 'exito';
            header('Location: mascotas.php');
            exit();

        } catch (PDOException $e) {
            $error = 'Error al actualizar la mascota. Intentalo de nuevo.';
        }
    }

    // Si hay error rellenar con los datos del POST
    $mascota['nombre']        = $nombre;
    $mascota['especie']       = $especie;
    $mascota['raza']          = $raza;
    $mascota['edad_anios']    = $edadAnios;
    $mascota['edad_meses']    = $edadMeses;
    $mascota['sexo']          = $sexo;
    $mascota['tamanio']       = $tamanio;
    $mascota['descripcion']   = $descripcion;
    $mascota['caracter']      = $caracter;
    $mascota['estado']        = $estado;
    $mascota['id_protectora'] = $idProtectora;
    $mascota['fecha_ingreso'] = $fechaIngreso;
}
?>

<div class="contenedor-admin">
    <h1><i class="fa-solid fa-pen"></i> Editar Mascota</h1>

    <?php if (!empty($error)) { ?>
        <div class="alerta alerta-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <form method="post" enctype="multipart/form-data">

        <div class="form-fila">
            <div class="form-grupo">
                <label>Nombre <span style="color:#e74a3b">*</span></label>
                <input type="text" name="nombre"
                       value="<?php echo htmlspecialchars($mascota['nombre']); ?>">
            </div>

            <div class="form-grupo">
                <label>Especie <span style="color:#e74a3b">*</span></label>
                <select name="especie">
                    <option value="perro" <?php echo ($mascota['especie'] === 'perro') ? 'selected' : ''; ?>>Perro</option>
                    <option value="gato"  <?php echo ($mascota['especie'] === 'gato')  ? 'selected' : ''; ?>>Gato</option>
                    <option value="otro"  <?php echo ($mascota['especie'] === 'otro')  ? 'selected' : ''; ?>>Otro</option>
                </select>
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label>Raza</label>
                <input type="text" name="raza"
                       value="<?php echo htmlspecialchars($mascota['raza']); ?>">
            </div>

            <div class="form-grupo">
                <label>Sexo <span style="color:#e74a3b">*</span></label>
                <select name="sexo">
                    <option value="macho"  <?php echo ($mascota['sexo'] === 'macho')  ? 'selected' : ''; ?>>Macho</option>
                    <option value="hembra" <?php echo ($mascota['sexo'] === 'hembra') ? 'selected' : ''; ?>>Hembra</option>
                </select>
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label>Edad (años)</label>
                <input type="number" name="edad_anios" min="0" max="30"
                       value="<?php echo (int)$mascota['edad_anios']; ?>">
            </div>

            <div class="form-grupo">
                <label>Edad (meses)</label>
                <input type="number" name="edad_meses" min="0" max="11"
                       value="<?php echo (int)$mascota['edad_meses']; ?>">
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label>Tamaño</label>
                <select name="tamanio">
                    <option value="pequeño"  <?php echo ($mascota['tamanio'] === 'pequeño')  ? 'selected' : ''; ?>>Pequeño</option>
                    <option value="mediano"  <?php echo ($mascota['tamanio'] === 'mediano')  ? 'selected' : ''; ?>>Mediano</option>
                    <option value="grande"   <?php echo ($mascota['tamanio'] === 'grande')   ? 'selected' : ''; ?>>Grande</option>
                </select>
            </div>

            <div class="form-grupo">
                <label>Estado</label>
                <select name="estado">
                    <option value="disponible" <?php echo ($mascota['estado'] === 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                    <option value="acogida"    <?php echo ($mascota['estado'] === 'acogida')    ? 'selected' : ''; ?>>En Acogida</option>
                    <option value="adoptado"   <?php echo ($mascota['estado'] === 'adoptado')   ? 'selected' : ''; ?>>Adoptado</option>
                </select>
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label>Protectora</label>
                <select name="id_protectora">
                    <option value="">Sin protectora</option>
                    <?php foreach ($protectoras as $protectora) { ?>
                        <option value="<?php echo $protectora['id']; ?>"
                            <?php echo ($mascota['id_protectora'] == $protectora['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($protectora['nombre']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-grupo">
                <label>Fecha de Ingreso</label>
                <input type="date" name="fecha_ingreso"
                       value="<?php echo htmlspecialchars($mascota['fecha_ingreso']); ?>">
            </div>
        </div>

        <div class="form-grupo">
            <label>Descripcion</label>
            <textarea name="descripcion" rows="4"><?php echo htmlspecialchars($mascota['descripcion']); ?></textarea>
        </div>

        <div class="form-grupo">
            <label>Caracter</label>
            <textarea name="caracter" rows="3"><?php echo htmlspecialchars($mascota['caracter']); ?></textarea>
        </div>

        <!-- Foto actual -->
        <?php if ($fotoPrincipal) { ?>
            <div class="form-grupo">
                <label>Foto Actual</label>
                <img src="<?php echo htmlspecialchars('../' . $fotoPrincipal['ruta_foto']); ?>"
                     alt="Foto actual"
                     style="width:150px; height:150px; object-fit:cover; border-radius:8px; display:block; margin-bottom:8px;">
            </div>
        <?php } ?>

        <div class="form-grupo">
            <label><?php echo $fotoPrincipal ? 'Cambiar Foto' : 'Subir Foto'; ?></label>
            <input type="file" name="foto" accept="image/*">
            <small style="color:#888;">Deja en blanco para mantener la foto actual.</small>
        </div>

        <div class="form-botones">
            <a href="mascotas.php" class="btn-outline-coral">Cancelar</a>
            <button type="submit" class="btn-coral">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
            </button>
        </div>

    </form>
</div>

<?php require_once '../templates/footer-admin.php'; ?>
