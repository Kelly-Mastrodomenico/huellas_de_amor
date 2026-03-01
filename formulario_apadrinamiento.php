<?php
$tituloPagina = 'Apadrinar Mascota — Huellas de Amor';
require_once 'templates/header.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: apadrinar.php');
    exit();
}

$idMascota = (int) $_GET['id'];
$idUsuario = (int) $_SESSION['usuario_id'];
$mascota   = null;
$errores   = [];

$planes = [
    'basico'   => ['nombre' => 'Padrino Básico',   'precio' => 5],
    'medio'    => ['nombre' => 'Padrino Amigo',     'precio' => 15],
    'completo' => ['nombre' => 'Padrino Especial',  'precio' => 30],
];

try {
    $stmt = $conexion->prepare(
        "SELECT * FROM `mascotas` WHERE `id` = :id AND `activo` = 1 LIMIT 1"
    );
    $stmt->bindParam(':id', $idMascota, PDO::PARAM_INT);
    $stmt->execute();
    $mascota = $stmt->fetch();

    if (!$mascota) {
        header('Location: apadrinar.php');
        exit();
    }

    // Verificar si ya apadrina esta mascota
    $stmtCheck = $conexion->prepare(
        "SELECT COUNT(*) FROM `apadrinamientos`
         WHERE `id_usuario` = :id_usuario AND `id_mascota` = :id_mascota AND `activo` = 1"
    );
    $stmtCheck->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
    $stmtCheck->bindParam(':id_mascota', $idMascota, PDO::PARAM_INT);
    $stmtCheck->execute();
    $yaApadrina = $stmtCheck->fetchColumn();

} catch (PDOException $e) {
    header('Location: apadrinar.php');
    exit();
}

$fotoPrincipal = obtenerFotoPrincipal($idMascota, $conexion);
if (!$fotoPrincipal) {
    $fotoPrincipal = 'https://picsum.photos/400/300?random=' . $idMascota;
}

$enviado = false;

if (isset($_POST['apadrinar'])) {
    $planElegido = trim($_POST['plan'] ?? '');

    if (!array_key_exists($planElegido, $planes)) {
        $errores[] = 'Debes seleccionar un plan válido.';
    }
    if ($yaApadrina) {
        $errores[] = 'Ya estás apadrinando a esta mascota.';
    }

    if (empty($errores)) {
        try {
            $montoMensual = $planes[$planElegido]['precio'];
            $stmtInsert   = $conexion->prepare(
                "INSERT INTO `apadrinamientos`
                 (`id_usuario`, `id_mascota`, `plan`, `monto_mensual`, `fecha_inicio`, `activo`)
                 VALUES (:id_usuario, :id_mascota, :plan, :monto, CURDATE(), 1)"
            );
            $stmtInsert->bindParam(':id_usuario', $idUsuario,    PDO::PARAM_INT);
            $stmtInsert->bindParam(':id_mascota', $idMascota,    PDO::PARAM_INT);
            $stmtInsert->bindParam(':plan',       $planElegido,  PDO::PARAM_STR);
            $stmtInsert->bindParam(':monto',      $montoMensual, PDO::PARAM_INT);
            $stmtInsert->execute();

            $enviado = true;
            mensajeFlash('¡Gracias! Ahora eres padrino de ' . $mascota['nombre'] . '.', 'exito');

        } catch (PDOException $e) {
            $errores[] = 'Error al procesar el apadrinamiento. Intentalo de nuevo.';
        }
    }
}
?>

<div class="contenedor" style="padding-top:16px;">
    <nav class="migas-pan">
        <a href="index.php">Inicio</a>
        <span><i class="fa-solid fa-chevron-right"></i></span>
        <a href="apadrinar.php">Apadrinar</a>
        <span><i class="fa-solid fa-chevron-right"></i></span>
        <span><?php echo htmlspecialchars($mascota['nombre']); ?></span>
    </nav>
</div>

<section class="seccion">
    <div class="contenedor">
        <div class="solicitud-grid">

            <!-- Info mascota -->
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
                        </div>
                    </div>
                </div>
                <div class="solicitud-nota">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>Tu aportación mensual se destina directamente al cuidado de <?php echo htmlspecialchars($mascota['nombre']); ?>. Puedes cancelar cuando quieras.</p>
                </div>
            </div>

            <!-- Formulario -->
            <div class="solicitud-formulario">

                <?php if ($enviado) { ?>
                <div class="solicitud-exito">
                    <i class="fa-solid fa-circle-check"></i>
                    <h2>¡Eres su padrino!</h2>
                    <p>Has apadrinado a <strong><?php echo htmlspecialchars($mascota['nombre']); ?></strong> con el plan <strong><?php echo $planes[$_POST['plan']]['nombre']; ?></strong>.</p>
                    <p>Recibirás actualizaciones mensuales sobre su evolución.</p>
                    <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:24px;">
                        <a href="apadrinar.php" class="btn-outline-turquesa">Ver más mascotas</a>
                        <a href="usuario/panel.php?tab=apadrinamientos" class="btn-coral">Mis apadrinamientos</a>
                    </div>
                </div>

                <?php } elseif ($yaApadrina) { ?>
                <div class="solicitud-exito solicitud-aviso">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <h2>Ya eres su padrino</h2>
                    <p>Ya estás apadrinando a <strong><?php echo htmlspecialchars($mascota['nombre']); ?></strong>.</p>
                    <a href="usuario/panel.php?tab=apadrinamientos" class="btn-coral"
                       style="margin-top:16px; display:inline-block;">
                        Ver mis apadrinamientos
                    </a>
                </div>

                <?php } else { ?>
                <h2><i class="fa-solid fa-star"></i> Elige tu plan</h2>
                <p class="solicitud-subtitulo">Selecciona la aportación mensual con la que quieres ayudar a <strong><?php echo htmlspecialchars($mascota['nombre']); ?></strong>.</p>

                <?php if (!empty($errores)) { ?>
                <div class="alerta alerta-error">
                    <?php foreach ($errores as $error) { ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php } ?>
                </div>
                <?php } ?>

                <form method="post" action="formulario_apadrinamiento.php?id=<?php echo $idMascota; ?>">
                    <div class="planes-selector">
                        <?php foreach ($planes as $clave => $plan) { ?>
                        <label class="plan-selector-item">
                            <input type="radio" name="plan" value="<?php echo $clave; ?>"
                                   <?php echo $clave === 'medio' ? 'checked' : ''; ?>>
                            <div class="plan-selector-caja">
                                <strong><?php echo $plan['nombre']; ?></strong>
                                <span><?php echo $plan['precio']; ?>€/mes</span>
                            </div>
                        </label>
                        <?php } ?>
                    </div>

                    <div class="form-botones" style="margin-top: 24px;">
                        <button type="submit" name="apadrinar" class="btn-coral btn-grande">
                            <i class="fa-solid fa-heart"></i> Confirmar Apadrinamiento
                        </button>
                        <a href="apadrinar.php" class="btn-outline-turquesa btn-grande">Cancelar</a>
                    </div>
                </form>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'templates/footer.php'; ?>