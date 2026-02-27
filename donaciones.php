<?php
$tituloPagina = 'Donaciones — Huellas de Amor';
require_once 'templates/header.php';

$errores  = [];
$enviado  = false;
$idUsuario = estaLogueado() ? (int) $_SESSION['usuario_id'] : null;

// Estadísticas de donaciones
$totalDonado    = 0;
$totalDonantes  = 0;
try {
    $stmt = $conexion->prepare("SELECT COALESCE(SUM(`monto`), 0) FROM `donaciones`");
    $stmt->execute();
    $totalDonado = (float) $stmt->fetchColumn();

    $stmt = $conexion->prepare("SELECT COUNT(DISTINCT `id_usuario`) FROM `donaciones` WHERE `id_usuario` IS NOT NULL");
    $stmt->execute();
    $totalDonantes = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    $totalDonado   = 0;
    $totalDonantes = 0;
}

// PROCESAR DONACIÓN
if (isset($_POST['donar'])) {
    $monto      = trim($_POST['monto'] ?? '');
    $montoCustom = trim($_POST['monto_custom'] ?? '');
    $concepto   = trim($_POST['concepto'] ?? '');
    $metodoPago = trim($_POST['metodo_pago'] ?? '');
    $nombre     = trim($_POST['nombre'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    // Si eligió monto personalizado
    if ($monto === 'otro') {
        $monto = $montoCustom;
    }

    // Validaciones
    if (empty($monto) || !is_numeric($monto) || (float)$monto < 1) {
        $errores[] = 'El monto mínimo de donación es 1€.';
    }
    if (empty($concepto)) {
        $errores[] = 'Selecciona el destino de tu donación.';
    }
    if (empty($metodoPago) || !in_array($metodoPago, ['tarjeta', 'paypal', 'bizum'])) {
        $errores[] = 'Selecciona un método de pago.';
    }
    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Introduce un email válido.';
    }

    if (empty($errores)) {
        try {
            $montoFinal = number_format((float)$monto, 2, '.', '');

            $stmtInsert = $conexion->prepare(
                "INSERT INTO `donaciones`
                 (`id_usuario`, `monto`, `concepto`, `metodo_pago`, `fecha`, `certificado_pdf`)
                 VALUES (:id_usuario, :monto, :concepto, :metodo_pago, NOW(), NULL)"
            );
            $stmtInsert->bindValue(':id_usuario', $idUsuario,   $idUsuario ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmtInsert->bindParam(':monto',      $montoFinal,  PDO::PARAM_STR);
            $stmtInsert->bindParam(':concepto',   $concepto,    PDO::PARAM_STR);
            $stmtInsert->bindParam(':metodo_pago', $metodoPago, PDO::PARAM_STR);
            $stmtInsert->execute();

            $idDonacion = $conexion->lastInsertId();
            $enviado    = true;

            // Guardar datos para pantalla de éxito
            $_SESSION['donacion_exito'] = [
                'id'      => $idDonacion,
                'monto'   => $montoFinal,
                'metodo'  => $metodoPago,
                'nombre'  => $nombre,
                'concepto'=> $concepto,
            ];

        } catch (PDOException $e) {
            $errores[] = 'Error al procesar la donación. Inténtalo de nuevo.';
        }
    }
}

// Recuperar datos de éxito
$donacionExito = null;
if ($enviado && isset($_SESSION['donacion_exito'])) {
    $donacionExito = $_SESSION['donacion_exito'];
    unset($_SESSION['donacion_exito']);
}
?>

<!-- HERO -->
<section class="donacion-hero">
<div class="donacion-hero-overlay"></div>
<div class="donacion-hero-contenido">
<span class="donacion-hero-tag">Haz la diferencia hoy</span>
<h1>Tu donación <span>salva vidas</span></h1>
<p>Cada euro se destina directamente al cuidado de los animales: alimento, veterinario, vacunas y refugio.</p>
<a href="#formulario" class="btn-coral">
    <i class="fa-solid fa-heart"></i> Donar ahora
</a>
</div>
</section>

<!-- ESTADÍSTICAS -->
<section class="seccion">
<div class="contenedor">
<div class="donacion-stats">
    <div class="donacion-stat">
        <i class="fa-solid fa-euro-sign"></i>
        <span class="stat-num"><?php echo number_format($totalDonado, 0, ',', '.'); ?>€</span>
        <span>Total recaudado</span>
    </div>
    <div class="donacion-stat">
        <i class="fa-solid fa-users"></i>
        <span class="stat-num"><?php echo $totalDonantes; ?>+</span>
        <span>Donantes</span>
    </div>
    <div class="donacion-stat">
        <i class="fa-solid fa-paw"></i>
        <span class="stat-num">100%</span>
        <span>Va a los animales</span>
    </div>
</div>
</div>
</section>

<!-- DESTINO DONACIONES -->
<section class="seccion seccion-gris">
<div class="contenedor">
<div class="seccion-titulo">
    <h2>¿A dónde va tu donación?</h2>
    <p>Cada euro se gestiona con total transparencia.</p>
</div>
<div class="donacion-destinos">
    <div class="destino-item">
        <div class="destino-icono destino-coral">
            <i class="fa-solid fa-bowl-food"></i>
        </div>
        <h3>Alimentación</h3>
        <p>Comida diaria de calidad para todos los animales del refugio.</p>
        <div class="destino-barra">
            <div class="destino-progreso" style="width: 40%;"></div>
        </div>
        <span>40%</span>
    </div>
    <div class="destino-item">
        <div class="destino-icono destino-turquesa">
            <i class="fa-solid fa-syringe"></i>
        </div>
        <h3>Veterinario y vacunas</h3>
        <p>Atención médica, vacunaciones y esterilizaciones.</p>
        <div class="destino-barra">
            <div class="destino-progreso" style="width: 35%;"></div>
        </div>
        <span>35%</span>
    </div>
    <div class="destino-item">
        <div class="destino-icono destino-verde">
            <i class="fa-solid fa-house"></i>
        </div>
        <h3>Mantenimiento</h3>
        <p>Instalaciones, mantas, juguetes y materiales del refugio.</p>
        <div class="destino-barra">
            <div class="destino-progreso destino-progreso-verde" style="width: 25%;"></div>
        </div>
        <span>25%</span>
    </div>
</div>
</div>
</section>

<!-- FORMULARIO -->
<section class="seccion" id="formulario">
<div class="contenedor">
<div class="donacion-grid">

    <!-- Info lateral -->
    <div class="donacion-info">
        <h2>Haz tu donación</h2>
        <p>Cualquier cantidad ayuda. Tu generosidad marca la diferencia en la vida de un animal.</p>

        <div class="donacion-metodos-info">
            <h4><i class="fa-solid fa-lock"></i> Pago 100% seguro</h4>
            <p>Aceptamos los métodos de pago más seguros y habituales.</p>
            <div class="metodos-logos">
                <span class="metodo-logo metodo-tarjeta">
                    <i class="fa-regular fa-credit-card"></i> Tarjeta
                </span>
                <span class="metodo-logo metodo-paypal">
                    <i class="fa-brands fa-paypal"></i> PayPal
                </span>
                <span class="metodo-logo metodo-bizum">
                    <i class="fa-solid fa-mobile-screen"></i> Bizum
                </span>
            </div>
        </div>

        <div class="donacion-fiscal-info">
            <i class="fa-solid fa-file-invoice"></i>
            <div>
                <h4>Ventaja fiscal</h4>
                <p>Las donaciones desgravan hasta un <strong>80%</strong> en los primeros 150€ del IRPF.</p>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="donacion-formulario">

        <?php if ($enviado && $donacionExito) { ?>
        <!-- PANTALLA DE ÉXITO -->
        <div class="donacion-exito">
            <div class="donacion-exito-icono">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2>¡Gracias, <?php echo htmlspecialchars($donacionExito['nombre']); ?>!</h2>
            <p>Tu donación de <strong><?php echo number_format((float)$donacionExito['monto'], 2, ',', '.'); ?>€</strong> ha sido registrada correctamente.</p>

            <div class="donacion-resumen">
                <div class="resumen-fila">
                    <span>Concepto</span>
                    <strong><?php echo htmlspecialchars($donacionExito['concepto']); ?></strong>
                </div>
                <div class="resumen-fila">
                    <span>Método de pago</span>
                    <strong><?php echo ucfirst($donacionExito['metodo']); ?></strong>
                </div>
                <div class="resumen-fila">
                    <span>Referencia</span>
                    <strong>#DON-<?php echo str_pad($donacionExito['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                </div>
            </div>

            <p style="font-size: <?php echo $fs_pequeno ?? '0.8rem'; ?>; color: #888; margin-top:16px;">
                Recibirás un email de confirmación con tu certificado de donación.
            </p>

            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:24px;">
                <a href="donaciones.php" class="btn-outline-turquesa">Hacer otra donación</a>
                <?php if (estaLogueado()) { ?>
                <a href="usuario/panel.php?tab=donaciones" class="btn-coral">Mis donaciones</a>
                <?php } else { ?>
                <a href="index.php" class="btn-coral">Volver al inicio</a>
                <?php } ?>
            </div>
        </div>

        <?php } else { ?>
        <!-- FORMULARIO DE DONACIÓN -->
        <?php if (!empty($errores)) { ?>
        <div class="alerta alerta-error">
            <?php foreach ($errores as $error) { ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php } ?>
        </div>
        <?php } ?>

        <form method="post" action="donaciones.php#formulario" class="form-donacion">

            <!-- Paso 1: Cantidad -->
            <div class="donacion-paso">
                <h3><span class="paso-num">1</span> ¿Cuánto quieres donar?</h3>
                <div class="montos-rapidos">
                    <?php foreach ([5, 10, 25, 50] as $cantidad) { ?>
                    <label class="monto-item">
                        <input type="radio" name="monto" value="<?php echo $cantidad; ?>"
                                <?php echo ($_POST['monto'] ?? '10') == $cantidad ? 'checked' : ''; ?>>
                        <span><?php echo $cantidad; ?>€</span>
                    </label>
                    <?php } ?>
                    <label class="monto-item">
                        <input type="radio" name="monto" value="otro"
                                <?php echo ($_POST['monto'] ?? '') === 'otro' ? 'checked' : ''; ?>>
                        <span>Otro</span>
                    </label>
                </div>
                <div class="monto-custom" id="montoCustom"
                        style="<?php echo ($_POST['monto'] ?? '') === 'otro' ? '' : 'display:none;'; ?>">
                    <input type="number" name="monto_custom" min="1" step="0.01"
                            placeholder="Introduce el importe en €"
                            value="<?php echo htmlspecialchars($_POST['monto_custom'] ?? ''); ?>">
                </div>
            </div>

            <!-- Paso 2: Destino -->
            <div class="donacion-paso">
                <h3><span class="paso-num">2</span> ¿Para qué es tu donación?</h3>
                <div class="form-grupo">
                    <select name="concepto" required>
                        <option value="">Selecciona el destino</option>
                        <option value="Donacion mensual general"
                            <?php echo ($_POST['concepto'] ?? '') === 'Donacion mensual general' ? 'selected' : ''; ?>>
                            Donación general al refugio
                        </option>
                        <option value="Alimentacion animales"
                            <?php echo ($_POST['concepto'] ?? '') === 'Alimentacion animales' ? 'selected' : ''; ?>>
                            Alimentación de los animales
                        </option>
                        <option value="Gastos veterinarios"
                            <?php echo ($_POST['concepto'] ?? '') === 'Gastos veterinarios' ? 'selected' : ''; ?>>
                            Gastos veterinarios y vacunas
                        </option>
                        <option value="Mantenimiento refugio"
                            <?php echo ($_POST['concepto'] ?? '') === 'Mantenimiento refugio' ? 'selected' : ''; ?>>
                            Mantenimiento del refugio
                        </option>
                    </select>
                </div>
            </div>

            <!-- Paso 3: Datos personales -->
            <div class="donacion-paso">
                <h3><span class="paso-num">3</span> Tus datos</h3>
                <div class="form-dos-columnas">
                    <div class="form-grupo">
                        <label for="nombre">Nombre <span class="obligatorio">*</span></label>
                        <input type="text" id="nombre" name="nombre"
                                value="<?php echo htmlspecialchars(
                                    !empty($_POST['nombre']) ? $_POST['nombre'] :
                                    ($usuario['nombre'] ?? '')
                                ); ?>"
                                placeholder="Tu nombre" required>
                    </div>
                    <div class="form-grupo">
                        <label for="email">Email <span class="obligatorio">*</span></label>
                        <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars(
                                    !empty($_POST['email']) ? $_POST['email'] :
                                    ($usuario['email'] ?? '')
                                ); ?>"
                                placeholder="tu@email.com" required>
                    </div>
                </div>
            </div>

            <!-- Paso 4: Método de pago -->
            <div class="donacion-paso">
                <h3><span class="paso-num">4</span> Método de pago</h3>
                <div class="metodos-pago">
                    <label class="metodo-item">
                        <input type="radio" name="metodo_pago" value="tarjeta"
                                <?php echo ($_POST['metodo_pago'] ?? 'tarjeta') === 'tarjeta' ? 'checked' : ''; ?>>
                        <div class="metodo-caja">
                            <i class="fa-regular fa-credit-card"></i>
                            <span>Tarjeta</span>
                        </div>
                    </label>
                    <label class="metodo-item">
                        <input type="radio" name="metodo_pago" value="paypal"
                                <?php echo ($_POST['metodo_pago'] ?? '') === 'paypal' ? 'checked' : ''; ?>>
                        <div class="metodo-caja">
                            <i class="fa-brands fa-paypal"></i>
                            <span>PayPal</span>
                        </div>
                    </label>
                    <label class="metodo-item">
                        <input type="radio" name="metodo_pago" value="bizum"
                                <?php echo ($_POST['metodo_pago'] ?? '') === 'bizum' ? 'checked' : ''; ?>>
                        <div class="metodo-caja">
                            <i class="fa-solid fa-mobile-screen"></i>
                            <span>Bizum</span>
                        </div>
                    </label>
                </div>

                <!-- Datos tarjeta (simulado) -->
                <div id="datosTarjeta" class="datos-pago"
                        style="<?php echo ($_POST['metodo_pago'] ?? 'tarjeta') === 'tarjeta' ? '' : 'display:none;'; ?>">
                    <div class="form-grupo">
                        <label>Número de tarjeta</label>
                        <input type="text" placeholder="1234 5678 9012 3456" maxlength="19" id="numTarjeta">
                    </div>
                    <div class="form-dos-columnas">
                        <div class="form-grupo">
                            <label>Caducidad</label>
                            <input type="text" placeholder="MM/AA" maxlength="5">
                        </div>
                        <div class="form-grupo">
                            <label>CVV</label>
                            <input type="text" placeholder="123" maxlength="3">
                        </div>
                    </div>
                    <p class="aviso-simulado">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Pasarela simulada — no introduzcas datos reales.
                    </p>
                </div>

                <!-- Datos PayPal (simulado) -->
                <div id="datosPaypal" class="datos-pago" style="display:none;">
                    <div class="form-grupo">
                        <label>Email de PayPal</label>
                        <input type="email" placeholder="tu@paypal.com">
                    </div>
                    <p class="aviso-simulado">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Pasarela simulada — no introduzcas datos reales.
                    </p>
                </div>

                <!-- Datos Bizum (simulado) -->
                <div id="datosBizum" class="datos-pago" style="display:none;">
                    <div class="form-grupo">
                        <label>Teléfono Bizum</label>
                        <input type="tel" placeholder="612 345 678">
                    </div>
                    <p class="aviso-simulado">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Pasarela simulada — no introduzcas datos reales.
                    </p>
                </div>
            </div>

            <button type="submit" name="donar" class="btn-coral btn-grande" style="width:100%;">
                <i class="fa-solid fa-heart"></i> Confirmar Donación
            </button>

            <p class="donacion-legal">
                <i class="fa-solid fa-lock"></i>
                Tu pago está protegido. Al donar aceptas nuestra
                <a href="#">política de privacidad</a>.
            </p>

        </form>
        <?php } ?>
    </div>
</div>
</div>
</section>

<script>
// Mostrar/ocultar campo monto personalizado
document.querySelectorAll('input[name="monto"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const custom = document.getElementById('montoCustom');
        custom.style.display = this.value === 'otro' ? 'block' : 'none';
        if (this.value === 'otro') {
            custom.querySelector('input').focus();
        }
    });
});

// Mostrar método de pago correspondiente
document.querySelectorAll('input[name="metodo_pago"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('datosTarjeta').style.display = 'none';
        document.getElementById('datosPaypal').style.display  = 'none';
        document.getElementById('datosBizum').style.display   = 'none';

        if (this.value === 'tarjeta') { document.getElementById('datosTarjeta').style.display = 'block'; }
        if (this.value === 'paypal')  { document.getElementById('datosPaypal').style.display  = 'block'; }
        if (this.value === 'bizum')   { document.getElementById('datosBizum').style.display   = 'block'; }
    });
});

// Formatear número de tarjeta
const numTarjeta = document.getElementById('numTarjeta');
if (numTarjeta) {
    numTarjeta.addEventListener('input', function() {
        let val = this.value.replace(/\D/g, '').substring(0, 16);
        this.value = val.replace(/(.{4})/g, '$1 ').trim();
    });
}
</script>

<?php require_once 'templates/footer.php'; ?>