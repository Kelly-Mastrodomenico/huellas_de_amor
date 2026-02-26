<?php
$tituloPagina = 'Iniciar Sesion — Huellas de Amor';
require_once 'templates/header.php';

$error = '';

// Procesar el formulario cuando se envia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email     = isset($_POST['email'])    ? trim($_POST['email'])    : '';
    $password  = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validar que no esten vacios
    if (empty($email) || empty($password)) {
        $error = 'Por favor rellena todos los campos.';

    } else {

        try {
            // Buscar el usuario por email
            $sql  = "SELECT * FROM `usuarios` WHERE `email` = :email AND `activo` = 1 LIMIT 1";
            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $usuario = $stmt->fetch();

            // Comprobar si existe y la contraseña es correcta
            if ($usuario && password_verify($password, $usuario['contrasena'])) {

                // Guardar datos en sesion
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['rol']            = $usuario['rol'];

                // Redirigir segun el rol
                if ($usuario['rol'] === 'admin') {
                    header('Location: admin/panel.php');
                } else {
                    header('Location: index.php');
                }
                exit();

            } else {
                $error = 'Email o contraseña incorrectos.';
            }

        } catch (PDOException $e) {
            $error = 'Error al conectar con la base de datos.';
        }
    }
}
?>

<div class="contenedor-formulario">
    <h2><i class="fa-solid fa-lock"></i> Iniciar Sesion</h2>

    <?php if (!empty($error)) { ?>
        <div class="alerta alerta-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok') { ?>
        <div class="alerta alerta-exito">
            Registro completado. Ya puedes iniciar sesion.
        </div>
    <?php } ?>

    <form action="login.php" method="post" id="formLogin">

        <div class="form-grupo">
            <label for="email">Correo electronico</label>
            <input type="text" id="email" name="email"
                   placeholder="ejemplo@correo.com"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            <span class="error" id="errorEmail"></span>
        </div>

        <div class="form-grupo">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password"
                   placeholder="Tu contraseña">
            <span class="error" id="errorPassword"></span>
        </div>

        <div class="form-botones">
            <button type="submit" name="enviar" class="btn-coral">
                Entrar
            </button>
            <a href="registro.php" class="btn-outline-coral">
                Registrarse
            </a>
        </div>

    </form>
</div>

<!-- Validacion con JavaScript — Requisito DWEC -->
<script>
$(document).ready(function() {

    $('#formLogin').on('submit', function(e) {
        var hayErrores = false;

        // Limpiar errores anteriores
        $('.error').text('');

        // Validar email
        var email = $('#email').val().trim();
        var regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email === '') {
            $('#errorEmail').text('El email es obligatorio.');
            hayErrores = true;
        } else if (!regexEmail.test(email)) {
            $('#errorEmail').text('Introduce un email valido.');
            hayErrores = true;
        }

        // Validar contraseña
        var password = $('#password').val().trim();

        if (password === '') {
            $('#errorPassword').text('La contraseña es obligatoria.');
            hayErrores = true;
        }

        // Si hay errores no enviar el formulario
        if (hayErrores) {
            e.preventDefault();
        }
    });

});
</script>

<?php require_once 'templates/footer.php'; ?>