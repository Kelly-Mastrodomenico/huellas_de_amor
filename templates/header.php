<?php
session_start();

require_once dirname(__DIR__) . '/includes/conexion.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

$paginaActual = basename($_SERVER['PHP_SELF']);

if (!isset($tituloPagina)) {
    $tituloPagina = 'Huellas de Amor';
}

$enAdmin  = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$rutaBase = $enAdmin ? '../' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
    <link rel="stylesheet" href="<?php echo $rutaBase; ?>css/style.css">
</head>
<body>

<header>
    <div class="contenedor-header">

        <!-- LOGO — sin style inline, lo controla SCSS -->
        <a href="<?php echo $rutaBase; ?>index.php" class="logo">
            <img src="<?php echo $rutaBase; ?>assets/img/Logo.png" alt="Huellas de Amor">
            <span>Huellas de Amor</span>
        </a>

        <!-- HAMBURGUESA — checkbox tecnica sin JS -->
        <input type="checkbox" id="menu-hamburguesa">
        <label for="menu-hamburguesa" id="icono-hamburguesa">
            <i class="fa-solid fa-bars"></i>
        </label>

        <!-- NAVEGACION -->
        <nav>
            <ul class="menu-principal">
                <li><a href="<?php echo $rutaBase; ?>index.php"
                    class="<?php echo ($paginaActual == 'index.php') ? 'active' : ''; ?>">Inicio</a></li>
                <li><a href="<?php echo $rutaBase; ?>adoptar.php"
                    class="<?php echo ($paginaActual == 'adoptar.php') ? 'active' : ''; ?>">Adoptar</a></li>
                <li><a href="<?php echo $rutaBase; ?>apadrinar.php"
                    class="<?php echo ($paginaActual == 'apadrinar.php') ? 'active' : ''; ?>">Apadrinar</a></li>
                <li><a href="<?php echo $rutaBase; ?>noticias.php"
                    class="<?php echo ($paginaActual == 'noticias.php') ? 'active' : ''; ?>">Noticias</a></li>
                <li><a href="<?php echo $rutaBase; ?>donaciones.php"
                    class="<?php echo ($paginaActual == 'donaciones.php') ? 'active' : ''; ?>">Donar</a></li>
                <li><a href="<?php echo $rutaBase; ?>contacto.php"
                    class="<?php echo ($paginaActual == 'contacto.php') ? 'active' : ''; ?>">Contacto</a></li>

                <?php if (estaLogueado()) { ?>
                    <?php if (esAdmin()) { ?>
                        <li><a href="<?php echo $rutaBase; ?>admin/panel.php" class="btn-admin">
                            <i class="fa-solid fa-lock"></i> Admin</a></li>
                    <?php } ?>
                    <li><a href="<?php echo $rutaBase; ?>logout.php" class="btn-cerrar-sesion">
                        Cerrar sesion</a></li>
                <?php } else { ?>
                    <li class="btn-login"><a href="<?php echo $rutaBase; ?>login.php">Login</a></li>
                    <li class="btn-registro"><a href="<?php echo $rutaBase; ?>registro.php">Registrarse</a></li>
                <?php } ?>
            </ul>
        </nav>

    </div>
</header>

<?php if (isset($_SESSION['mensaje'])) { ?>
    <div class="alerta alerta-<?php echo $_SESSION['mensaje_tipo']; ?>" id="mensajeFlash">
        <?php echo htmlspecialchars($_SESSION['mensaje']); ?>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
<?php } ?>

<!-- Nav inferior fija — solo movil -->
<nav class="nav-inferior">
    <a href="<?php echo $rutaBase; ?>index.php"
       class="<?php echo ($paginaActual == 'index.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-house"></i><span>Inicio</span>
    </a>
    <a href="<?php echo $rutaBase; ?>adoptar.php"
       class="<?php echo ($paginaActual == 'adoptar.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-paw"></i><span>Adoptar</span>
    </a>
    <a href="<?php echo $rutaBase; ?>galeria.php"
       class="<?php echo ($paginaActual == 'galeria.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-images"></i><span>Galeria</span>
    </a>
    <a href="<?php echo $rutaBase; ?>donaciones.php"
       class="<?php echo ($paginaActual == 'donaciones.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-heart"></i><span>Donar</span>
    </a>
    <?php if (estaLogueado()) { ?>
        <a href="<?php echo $rutaBase; ?>logout.php">
            <i class="fa-solid fa-right-from-bracket"></i><span>Salir</span>
        </a>
    <?php } else { ?>
        <a href="<?php echo $rutaBase; ?>login.php"
           class="<?php echo ($paginaActual == 'login.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-user"></i><span>Login</span>
        </a>
    <?php } ?>
</nav>

<main>