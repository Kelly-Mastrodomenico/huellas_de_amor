<?php
$tituloPagina = 'Contacto — Huellas de Amor';
require_once 'templates/header.php';

$errores = [];
$enviado = false;

// Prellenar datos si esta logueado
$nombrePre = '';
$emailPre  = '';
if (estaLogueado() && isset($_SESSION['usuario_id'])) {
    try {
        $stmtUser = $conexion->prepare("SELECT `nombre`, `apellidos`, `email` FROM `usuarios` WHERE `id` = :id LIMIT 1");
        $stmtUser->bindValue(':id', (int) $_SESSION['usuario_id'], PDO::PARAM_INT);
        $stmtUser->execute();
        $datosUser = $stmtUser->fetch();
        if ($datosUser) {
            $nombrePre = trim($datosUser['nombre'] . ' ' . $datosUser['apellidos']);
            $emailPre  = $datosUser['email'];
        }
    } catch (PDOException $e) {
        $nombrePre = '';
        $emailPre  = '';
    }
}

// PROCESAR FORMULARIO
if (isset($_POST['enviar'])) {
    $nombre  = trim($_POST['nombre']  ?? '');
    $email   = trim($_POST['email']   ?? '');
    $asunto  = trim($_POST['asunto']  ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    // Validaciones
    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Introduce un email válido.';
    }
    if (empty($asunto)) {
        $errores[] = 'El asunto es obligatorio.';
    }
    if (empty($mensaje)) {
        $errores[] = 'El mensaje es obligatorio.';
    } elseif (strlen($mensaje) < 20) {
        $errores[] = 'El mensaje debe tener al menos 20 caracteres.';
    }

    if (empty($errores)) {
        try {
            // Guardar en BD
            $stmt = $conexion->prepare(
                "INSERT INTO `contacto` (`nombre`, `email`, `asunto`, `mensaje`, `leido`, `fecha`)
                 VALUES (:nombre, :email, :asunto, :mensaje, 0, NOW())"
            );
            $stmt->bindParam(':nombre',  $nombre,  PDO::PARAM_STR);
            $stmt->bindParam(':email',   $email,   PDO::PARAM_STR);
            $stmt->bindParam(':asunto',  $asunto,  PDO::PARAM_STR);
            $stmt->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
            $stmt->execute();

            // Enviar email al admin (funciona en producción con servidor SMTP)
            // $emailAdmin = 'admin@huellasdeamor.com';
            // $asuntoEmail = '[Huellas de Amor] Nuevo mensaje: ' . $asunto;
            // $cuerpo = "Nombre: $nombre\nEmail: $email\nAsunto: $asunto\n\nMensaje:\n$mensaje";
            // mail($emailAdmin, $asuntoEmail, $cuerpo, 'From: ' . $email);

            $enviado = true;

        } catch (PDOException $e) {
            $errores[] = 'Error al enviar el mensaje. Inténtalo de nuevo.';
        }
    }
}
?>

<!-- HERO -->
<section class="contacto-hero">
    <div class="contacto-hero-overlay"></div>
    <div class="contacto-hero-contenido">
        <h1><i class="fa-solid fa-envelope"></i> Contacta con nosotros</h1>
        <p>¿Tienes alguna pregunta? Estamos aquí para ayudarte.</p>
    </div>
</section>

<section class="seccion">
    <div class="contenedor">
        <div class="contacto-grid">

            <!-- INFO LATERAL -->
            <div class="contacto-info">

                <div class="contacto-info-card">
                    <h2>¿Cómo podemos ayudarte?</h2>
                    <p>Puedes escribirnos para cualquier consulta sobre adopciones, apadrinamientos, donaciones o colaboraciones.</p>
                </div>

                <div class="contacto-datos">
                    <div class="contacto-dato">
                        <div class="contacto-dato-icono">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <h4>Dirección</h4>
                            <p>Calle del Refugio, 12<br>03500 Benidorm, Alicante</p>
                        </div>
                    </div>
                    <div class="contacto-dato">
                        <div class="contacto-dato-icono">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <h4>Teléfono</h4>
                            <p>+34 612 345 678</p>
                            <small>Lunes a Viernes, 9h - 18h</small>
                        </div>
                    </div>
                    <div class="contacto-dato">
                        <div class="contacto-dato-icono">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <h4>Email</h4>
                            <p>info@huellasdeamor.com</p>
                        </div>
                    </div>
                    <div class="contacto-dato">
                        <div class="contacto-dato-icono">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <h4>Horario de visitas</h4>
                            <p>Sábados y Domingos<br>10h - 14h y 16h - 19h</p>
                        </div>
                    </div>
                </div>

                <!-- Redes sociales -->
                <div class="contacto-redes">
                    <h4>Síguenos</h4>
                    <div class="contacto-redes-iconos">
                        <a href="#" class="red-facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="red-instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="red-twitter"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" class="red-whatsapp"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>

            </div>

            <!-- FORMULARIO -->
            <div class="contacto-formulario">

                <?php if ($enviado) { ?>
                <div class="contacto-exito">
                    <i class="fa-solid fa-circle-check"></i>
                    <h2>¡Mensaje enviado!</h2>
                    <p>Hemos recibido tu mensaje y te responderemos en un plazo de <strong>24-48 horas</strong>.</p>
                    <a href="contacto.php" class="btn-coral" style="margin-top:20px; display:inline-block;">
                        Enviar otro mensaje
                    </a>
                </div>

                <?php } else { ?>

                <h2>Envianos un mensaje</h2>

                <?php if (!empty($errores)) { ?>
                <div class="alerta alerta-error">
                    <?php foreach ($errores as $error) { ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php } ?>
                </div>
                <?php } ?>

                <form method="post" action="contacto.php" class="form-contacto">

                    <div class="form-dos-columnas">
                        <div class="form-grupo">
                            <label for="nombre">Nombre <span class="obligatorio">*</span></label>
                            <input type="text" id="nombre" name="nombre"
                                   value="<?php echo htmlspecialchars($_POST['nombre'] ?? $nombrePre); ?>"
                                   placeholder="Tu nombre completo" required>
                        </div>
                        <div class="form-grupo">
                            <label for="email">Email <span class="obligatorio">*</span></label>
                            <input type="email" id="email" name="email"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? $emailPre); ?>"
                                   placeholder="tu@email.com" required>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label for="asunto">Asunto <span class="obligatorio">*</span></label>
                        <select id="asunto" name="asunto" required>
                            <option value="">Selecciona el motivo de tu consulta</option>
                            <option value="Consulta sobre adopcion"
                                <?php echo ($_POST['asunto'] ?? '') === 'Consulta sobre adopcion' ? 'selected' : ''; ?>>
                                Consulta sobre adopción
                            </option>
                            <option value="Consulta sobre apadrinamiento"
                                <?php echo ($_POST['asunto'] ?? '') === 'Consulta sobre apadrinamiento' ? 'selected' : ''; ?>>
                                Consulta sobre apadrinamiento
                            </option>
                            <option value="Informacion sobre donaciones"
                                <?php echo ($_POST['asunto'] ?? '') === 'Informacion sobre donaciones' ? 'selected' : ''; ?>>
                                Información sobre donaciones
                            </option>
                            <option value="Quiero ser casa de acogida"
                                <?php echo ($_POST['asunto'] ?? '') === 'Quiero ser casa de acogida' ? 'selected' : ''; ?>>
                                Quiero ser casa de acogida
                            </option>
                            <option value="Colaboracion o voluntariado"
                                <?php echo ($_POST['asunto'] ?? '') === 'Colaboracion o voluntariado' ? 'selected' : ''; ?>>
                                Colaboración o voluntariado
                            </option>
                            <option value="Otro"
                                <?php echo ($_POST['asunto'] ?? '') === 'Otro' ? 'selected' : ''; ?>>
                                Otro
                            </option>
                        </select>
                    </div>

                    <div class="form-grupo">
                        <label for="mensaje">Mensaje <span class="obligatorio">*</span></label>
                        <textarea id="mensaje" name="mensaje" rows="5"
                                  placeholder="Escribe tu mensaje aquí... (mínimo 20 caracteres)"
                                  required><?php echo htmlspecialchars($_POST['mensaje'] ?? ''); ?></textarea>
                        <small class="form-ayuda">Mínimo 20 caracteres.</small>
                    </div>

                    <!-- Anti-spam honeypot -->
                    <div style="display:none;">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-botones">
                        <button type="submit" name="enviar" class="btn-coral btn-grande">
                            <i class="fa-solid fa-paper-plane"></i> Enviar Mensaje
                        </button>
                    </div>

                </form>
                <?php } ?>
            </div>

        </div>
    </div>
</section>

<?php require_once 'templates/footer.php'; ?>
