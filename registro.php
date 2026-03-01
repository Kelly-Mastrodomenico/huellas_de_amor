<?php
$tituloPagina = 'Registrarse — Huellas de Amor';
require_once 'templates/header.php';

$error = '';
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre    = isset($_POST['nombre'])    ? trim($_POST['nombre'])    : '';
    $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
    $email     = isset($_POST['email'])     ? trim($_POST['email'])     : '';
    $telefono  = isset($_POST['telefono'])  ? trim($_POST['telefono'])  : '';
    $dni       = isset($_POST['dni'])       ? strtoupper(trim($_POST['dni'])) : '';
    $password  = isset($_POST['password'])  ? trim($_POST['password'])  : '';
    $password2 = isset($_POST['password2']) ? trim($_POST['password2']) : '';

    // Validaciones servidor
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($password) || empty($dni)) {
        $error = 'Por favor rellena todos los campos obligatorios.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es valido.';

    } elseif (!preg_match('/^[0-9]{8}[A-Z]$/', $dni)) {
        $error = 'El DNI no es valido. Formato: 8 numeros y 1 letra (ej: 12345678A).';

    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';

    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';

    } else {
        try {
            // Comprobar email duplicado
            $stmt = $conexion->prepare("SELECT `id` FROM `usuarios` WHERE `email` = :email LIMIT 1");
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->fetch()) {
                $error = 'Ese email ya esta registrado. Prueba a iniciar sesion.';
            } else {
                // Comprobar DNI duplicado
                $stmtDni = $conexion->prepare("SELECT `id` FROM `usuarios` WHERE `dni` = :dni LIMIT 1");
                $stmtDni->bindValue(':dni', $dni, PDO::PARAM_STR);
                $stmtDni->execute();

                if ($stmtDni->fetch()) {
                    $error = 'Ese DNI ya esta registrado.';
                } else {
                    $passwordEncriptada = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conexion->prepare(
                        "INSERT INTO `usuarios`
                         (`nombre`, `apellidos`, `email`, `telefono`, `dni`, `contrasena`, `rol`, `activo`, `fecha_registro`)
                         VALUES (:nombre, :apellidos, :email, :telefono, :dni, :contrasena, 'registrado', 1, NOW())"
                    );
                    $stmt->bindValue(':nombre',     $nombre,             PDO::PARAM_STR);
                    $stmt->bindValue(':apellidos',  $apellidos,          PDO::PARAM_STR);
                    $stmt->bindValue(':email',      $email,              PDO::PARAM_STR);
                    $stmt->bindValue(':telefono',   $telefono,           PDO::PARAM_STR);
                    $stmt->bindValue(':dni',        $dni,                PDO::PARAM_STR);
                    $stmt->bindValue(':contrasena', $passwordEncriptada, PDO::PARAM_STR);
                    $stmt->execute();

                    header('Location: login.php?registro=ok');
                    exit();
                }
            }

        } catch (PDOException $e) {
            $error = 'Error al guardar el usuario. Intentalo de nuevo.';
        }
    }
}
?>

<div class="contenedor-formulario" style="max-width:600px;">
    <h2><i class="fa-solid fa-user-plus"></i> Crear Cuenta</h2>

    <?php if (!empty($error)) { ?>
    <div class="alerta alerta-error">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php } ?>

    <form action="registro.php" method="post" id="formRegistro">

        <div class="form-fila">
            <div class="form-grupo">
                <label for="nombre">Nombre <span style="color:#e74a3b">*</span></label>
                <input type="text" id="nombre" name="nombre" placeholder="Tu nombre"
                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                <span class="error" id="errorNombre"></span>
            </div>
            <div class="form-grupo">
                <label for="apellidos">Apellidos <span style="color:#e74a3b">*</span></label>
                <input type="text" id="apellidos" name="apellidos" placeholder="Tus apellidos"
                       value="<?php echo isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : ''; ?>">
                <span class="error" id="errorApellidos"></span>
            </div>
        </div>

        <div class="form-grupo">
            <label for="email">Correo electronico <span style="color:#e74a3b">*</span></label>
            <input type="text" id="email" name="email" placeholder="ejemplo@correo.com"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            <span class="error" id="errorEmail"></span>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="dni">DNI <span style="color:#e74a3b">*</span></label>
                <input type="text" id="dni" name="dni" placeholder="12345678A" maxlength="9"
                       value="<?php echo isset($_POST['dni']) ? htmlspecialchars(strtoupper($_POST['dni'])) : ''; ?>">
                <span class="error" id="errorDni"></span>
            </div>
            <div class="form-grupo">
                <label for="telefono">Telefono</label>
                <input type="text" id="telefono" name="telefono" placeholder="612 345 678"
                       value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                <span class="error" id="errorTelefono"></span>
            </div>
        </div>

        <div class="form-fila">
            <div class="form-grupo">
                <label for="password">Contraseña <span style="color:#e74a3b">*</span></label>
                <input type="password" id="password" name="password" placeholder="Minimo 6 caracteres">
                <span class="error" id="errorPassword"></span>
            </div>
            <div class="form-grupo">
                <label for="password2">Repetir Contraseña <span style="color:#e74a3b">*</span></label>
                <input type="password" id="password2" name="password2" placeholder="Repite la contraseña">
                <span class="error" id="errorPassword2"></span>
            </div>
        </div>

        <div class="form-botones">
            <button type="submit" name="enviar" class="btn-coral">
                Crear Cuenta
            </button>
            <a href="login.php" class="btn-outline-coral">
                Ya tengo cuenta
            </a>
        </div>

    </form>
</div>

<script>
$(document).ready(function() {

    // Pasar DNI a mayúsculas mientras escribe
    $('#dni').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    $('#formRegistro').on('submit', function(e) {
        var hayErrores = false;
        $('.error').text('');

        // Nombre
        var nombre = $('#nombre').val().trim();
        if (nombre === '') {
            $('#errorNombre').text('El nombre es obligatorio.');
            hayErrores = true;
        } else if (nombre.length < 2) {
            $('#errorNombre').text('El nombre debe tener al menos 2 caracteres.');
            hayErrores = true;
        }

        // Apellidos
        var apellidos = $('#apellidos').val().trim();
        if (apellidos === '') {
            $('#errorApellidos').text('Los apellidos son obligatorios.');
            hayErrores = true;
        }

        // Email
        var email = $('#email').val().trim();
        var regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email === '') {
            $('#errorEmail').text('El email es obligatorio.');
            hayErrores = true;
        } else if (!regexEmail.test(email)) {
            $('#errorEmail').text('Introduce un email valido.');
            hayErrores = true;
        }

        // DNI — formato español: 8 dígitos + 1 letra
        var dni = $('#dni').val().trim().toUpperCase();
        var regexDni = /^[0-9]{8}[A-Z]$/;
        if (dni === '') {
            $('#errorDni').text('El DNI es obligatorio.');
            hayErrores = true;
        } else if (!regexDni.test(dni)) {
            $('#errorDni').text('Formato incorrecto. Ej: 12345678A');
            hayErrores = true;
        }

        // Teléfono (opcional)
        var telefono = $('#telefono').val().trim();
        var regexTelefono = /^[0-9\s\+\-]{9,15}$/;
        if (telefono !== '' && !regexTelefono.test(telefono)) {
            $('#errorTelefono').text('El telefono no es valido.');
            hayErrores = true;
        }

        // Contraseña
        var password = $('#password').val().trim();
        if (password === '') {
            $('#errorPassword').text('La contraseña es obligatoria.');
            hayErrores = true;
        } else if (password.length < 6) {
            $('#errorPassword').text('La contraseña debe tener al menos 6 caracteres.');
            hayErrores = true;
        }

        // Repetir contraseña
        var password2 = $('#password2').val().trim();
        if (password2 === '') {
            $('#errorPassword2').text('Repite la contraseña.');
            hayErrores = true;
        } else if (password !== password2) {
            $('#errorPassword2').text('Las contraseñas no coinciden.');
            hayErrores = true;
        }

        if (hayErrores) {
            e.preventDefault();
        }
    });

});
</script>

<?php require_once 'templates/footer.php'; ?>