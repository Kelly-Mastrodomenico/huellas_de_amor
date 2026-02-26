<?php
$tituloPagina = 'Nueva Mascota — Admin';
require_once '../templates/header-admin.php';

$error  = '';

// Obtener protectoras para el select
$protectoras = [];
try {
    $stmt = $conexion->prepare("SELECT `id`, `nombre` FROM `protectoras` WHERE `activa` = 1 ORDER BY `nombre`");
    $stmt->execute();
    $protectoras = $stmt->fetchAll();
} catch (PDOException $e) {
    $protectoras = [];
}

// Procesar el formulario cuando se envia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre        = isset($_POST['nombre'])        ? trim($_POST['nombre'])        : '';
    $especie       = isset($_POST['especie'])        ? trim($_POST['especie'])       : '';
    $raza          = isset($_POST['raza'])           ? trim($_POST['raza'])          : '';
    $edadAnios     = isset($_POST['edad_anios'])     ? (int)$_POST['edad_anios']     : 0;
    $edadMeses     = isset($_POST['edad_meses'])     ? (int)$_POST['edad_meses']     : 0;
    $sexo          = isset($_POST['sexo'])           ? trim($_POST['sexo'])          : '';
    $tamanio       = isset($_POST['tamanio'])        ? trim($_POST['tamanio'])       : '';
    $descripcion   = isset($_POST['descripcion'])    ? trim($_POST['descripcion'])   : '';
    $caracter      = isset($_POST['caracter'])       ? trim($_POST['caracter'])      : '';
    $estado        = isset($_POST['estado'])         ? trim($_POST['estado'])        : 'disponible';
    $idProtectora  = isset($_POST['id_protectora'])  && !empty($_POST['id_protectora']) ? (int)$_POST['id_protectora'] : null;
    $fechaIngreso  = isset($_POST['fecha_ingreso'])  ? trim($_POST['fecha_ingreso']) : date('Y-m-d');

    // Validar campos obligatorios
    if (empty($nombre) || empty($especie) || empty($sexo)) {
        $error = 'El nombre, la especie y el sexo son obligatorios.';
    } else {

        try {
            // Insertar la mascota
            $sql = "INSERT INTO `mascotas`
                    (`nombre`, `especie`, `raza`, `edad_anios`, `edad_meses`, `sexo`, `tamanio`,
                     `descripcion`, `caracter`, `estado`, `id_protectora`, `fecha_ingreso`, `activo`)
                    VALUES
                    (:nombre, :especie, :raza, :edad_anios, :edad_meses, :sexo, :tamanio,
                     :descripcion, :caracter, :estado, :id_protectora, :fecha_ingreso, 1)";

            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':nombre',       $nombre,       PDO::PARAM_STR);
            $stmt->bindValue(':especie',      $especie,      PDO::PARAM_STR);
            $stmt->bindValue(':raza',         $raza,         PDO::PARAM_STR);
            $stmt->bindValue(':edad_anios',   $edadAnios,    PDO::PARAM_INT);
            $stmt->bindValue(':edad_meses',   $edadMeses,    PDO::PARAM_INT);
            $stmt->bindValue(':sexo',         $sexo,         PDO::PARAM_STR);
            $stmt->bindValue(':tamanio',      $tamanio,      PDO::PARAM_STR);
            $stmt->bindValue(':descripcion',  $descripcion,  PDO::PARAM_STR);
            $stmt->bindValue(':caracter',     $caracter,     PDO::PARAM_STR);
            $stmt->bindValue(':estado',       $estado,       PDO::PARAM_STR);
            $stmt->bindValue(':id_protectora', $idProtectora, PDO::PARAM_INT);
            $stmt->bindValue(':fecha_ingreso', $fechaIngreso, PDO::PARAM_STR);
            $stmt->execute();

            // Obtener el id de la mascota recien insertada
            $idMascota = $conexion->lastInsertId();

            // SUBIDA DE FOTO —  subir ficheros al servidor
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

                $rutaFoto = subirImagen($_FILES['foto'], 'uploads/mascotas/');

                if ($rutaFoto) {
                    // Guardar la ruta en fotos_mascotas como foto principal
                    $sqlFoto = "INSERT INTO `fotos_mascotas` (`id_mascota`, `ruta_foto`, `es_principal`)
                                VALUES (:id_mascota, :ruta_foto, 1)";
                    $stmtFoto = $conexion->prepare($sqlFoto);
                    $stmtFoto->bindValue(':id_mascota', $idMascota,  PDO::PARAM_INT);
                    $stmtFoto->bindValue(':ruta_foto',  $rutaFoto,   PDO::PARAM_STR);
                    $stmtFoto->execute();
                }
            }

            // Redirigir al listado con mensaje de exito
            $_SESSION['mensaje']      = 'Mascota añadida correctamente.';
            $_SESSION['mensaje_tipo'] = 'exito';
            header('Location: mascotas.php');
            exit();

        } catch (PDOException $e) {
            $error = 'Error al guardar la mascota. Intentalo de nuevo.';
        }
    }
}
?>

<div class="contenedor-admin">
    <h1><i class="fa-solid fa-plus"></i> Nueva Mascota</h1>

    <?php if (!empty($error)) { ?>
        <div class="alerta alerta-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <form method="post" enctype="multipart/form-data">

        <div class="form-fila">
            <div class="form-grupo">
                <label>Nombre <span style="color:#e74a3b">*</span></label>
                <input type="text" name="nombre" placeholder="Nombre de la mascota"
                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
            </div>

            <div class="form-grupo">
                <label>Especie <span style="color:#e74a3b">*</span></label>
                <select name="especie">
                    <option value="">Seleccionar...</option>
                    <option value="perro" <?php echo (isset($_POST['especie']) && $_POST['especie'] === 'perro') ? 'selected' : ''; ?>>Perro</option>
                    <option value="gato"  <?php echo (isset($_POST['especie']) && $_POST['especie'] === 'gato')  ? 'selected' : ''; ?>>Gato</option>
                    <option value="otro"  <?php echo (isset($_POST['especie']) && $_POST['especie'] === 'otro')  ? 'selected' : ''; ?>>Otro</option>
                </select>
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label>Raza</label>
                <input type="text" name="raza" placeholder="Raza o mestizo"
                       value="<?php echo isset($_POST['raza']) ? htmlspecialchars($_POST['raza']) : ''; ?>">
            </div>

            <div class="form-grupo">
                <label>Sexo <span style="color:#e74a3b">*</span></label>
                <select name="sexo">
                    <option value="">Seleccionar...</option>
                    <option value="macho"  <?php echo (isset($_POST['sexo']) && $_POST['sexo'] === 'macho')  ? 'selected' : ''; ?>>Macho</option>
                    <option value="hembra" <?php echo (isset($_POST['sexo']) && $_POST['sexo'] === 'hembra') ? 'selected' : ''; ?>>Hembra</option>
                </select>
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label>Edad (años)</label>
                <input type="number" name="edad_anios" min="0" max="30"
                       value="<?php echo isset($_POST['edad_anios']) ? (int)$_POST['edad_anios'] : 0; ?>">
            </div>

            <div class="form-grupo">
                <label>Edad (meses)</label>
                <input type="number" name="edad_meses" min="0" max="11"
                       value="<?php echo isset($_POST['edad_meses']) ? (int)$_POST['edad_meses'] : 0; ?>">
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label>Tamaño</label>
                <select name="tamanio">
                    <option value="">Seleccionar...</option>
                    <option value="pequeño"  <?php echo (isset($_POST['tamanio']) && $_POST['tamanio'] === 'pequeño')  ? 'selected' : ''; ?>>Pequeño</option>
                    <option value="mediano"  <?php echo (isset($_POST['tamanio']) && $_POST['tamanio'] === 'mediano')  ? 'selected' : ''; ?>>Mediano</option>
                    <option value="grande"   <?php echo (isset($_POST['tamanio']) && $_POST['tamanio'] === 'grande')   ? 'selected' : ''; ?>>Grande</option>
                </select>
            </div>

            <div class="form-grupo">
                <label>Estado</label>
                <select name="estado">
                    <option value="disponible" <?php echo (isset($_POST['estado']) && $_POST['estado'] === 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                    <option value="acogida"    <?php echo (isset($_POST['estado']) && $_POST['estado'] === 'acogida')    ? 'selected' : ''; ?>>En Acogida</option>
                    <option value="adoptado"   <?php echo (isset($_POST['estado']) && $_POST['estado'] === 'adoptado')   ? 'selected' : ''; ?>>Adoptado</option>
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
                            <?php echo (isset($_POST['id_protectora']) && $_POST['id_protectora'] == $protectora['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($protectora['nombre']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-grupo">
                <label>Fecha de Ingreso</label>
                <input type="date" name="fecha_ingreso"
                       value="<?php echo isset($_POST['fecha_ingreso']) ? htmlspecialchars($_POST['fecha_ingreso']) : date('Y-m-d'); ?>">
            </div>
        </div>

        <div class="form-grupo">
            <label>Descripcion</label>
            <textarea name="descripcion" rows="4" placeholder="Descripcion de la mascota..."><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
        </div>

        <div class="form-grupo">
            <label>Caracter</label>
            <textarea name="caracter" rows="3" placeholder="Como es su caracter, con niños, con otros animales..."><?php echo isset($_POST['caracter']) ? htmlspecialchars($_POST['caracter']) : ''; ?></textarea>
        </div>

        <!-- SUBIDA DE FOTO -->
        <div class="form-grupo">
            <label>Foto Principal</label>
            <input type="file" name="foto" accept="image/*">
            <small style="color:#888;">Formatos: JPG, PNG, GIF. Maximo 2MB.</small>
        </div>

        <div class="form-botones">
            <a href="mascotas.php" class="btn-outline-coral">Cancelar</a>
            <button type="submit" class="btn-coral">
                <i class="fa-solid fa-plus"></i> Guardar Mascota
            </button>
        </div>

    </form>
</div>

<?php require_once '../templates/footer-admin.php'; ?>