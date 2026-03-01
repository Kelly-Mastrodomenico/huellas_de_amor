<?php
// Definir ruta base
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

session_start();

// Proteger — solo admin puede ver esto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once BASE_PATH . '/includes/conexion.php';
require_once BASE_PATH . '/includes/funciones.php';

$paginaActual = basename($_SERVER['PHP_SELF']);

if (!isset($tituloPagina)) {
    $tituloPagina = 'Panel Admin — Huellas de Amor';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
    <div class="contenedor-header">

        <a href="../index.php" class="logo">
            <i class="fa-solid fa-paw"></i>
            <span>Huellas de Amor</span>
        </a>

        <input type="checkbox" id="menu-hamburguesa">
        <label for="menu-hamburguesa" id="icono-hamburguesa">
            <i class="fa-solid fa-bars"></i>
        </label>

        <nav>
            <ul class="menu-principal">
                <li>
                    <a href="panel.php"
                       class="<?php echo ($paginaActual == 'panel.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="mascotas.php"
                       class="<?php echo ($paginaActual == 'mascotas.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-paw"></i> Mascotas
                    </a>
                </li>
                <li>
                    <a href="solicitudes.php"
                       class="<?php echo ($paginaActual == 'solicitudes.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-file-pen"></i> Solicitudes
                    </a>
                </li>
                <li>
                    <a href="noticias.php"
                       class="<?php echo ($paginaActual == 'noticias.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-newspaper"></i> Noticias
                    </a>
                </li>
                <li>
                    <a href="donaciones.php"
                       class="<?php echo ($paginaActual == 'donaciones.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-hand-holding-heart"></i> Donaciones
                    </a>
                </li>
                <li>
                    <a href="usuarios.php"
                       class="<?php echo ($paginaActual == 'usuarios.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-users"></i> Usuarios
                    </a>
                </li>
                <li>
    <a href="contacto.php" class="<?php echo $paginaAdmin === 'contacto' ? 'activo' : ''; ?>">
        <i class="fa-solid fa-envelope"></i>
        <span>Mensajes</span>
        <?php
        // Contar no leídos
        try {
            $stmtBadge = $conexion->prepare("SELECT COUNT(*) FROM `contacto` WHERE `leido` = 0");
            $stmtBadge->execute();
            $noLeidos = $stmtBadge->fetchColumn();
            if ($noLeidos > 0) {
                echo '<span class="menu-badge">' . $noLeidos . '</span>';
            }
        } catch (PDOException $e) {}
        ?>
    </a>
</li>
                <li>
                    <a href="../index.php">
                        <i class="fa-solid fa-globe"></i> Ver web
                    </a>
                </li>
                <li>
                    <a href="../logout.php" class="btn-cerrar-sesion">
                        <i class="fa-solid fa-right-from-bracket"></i> Salir
                    </a>
                </li>
            </ul>
        </nav>

    </div>
</header>

<!-- Mensaje flash -->
<?php if (isset($_SESSION['mensaje'])) { ?>
    <div class="alerta alerta-<?php echo $_SESSION['mensaje_tipo']; ?>" id="mensajeFlash">
        <?php echo htmlspecialchars($_SESSION['mensaje']); ?>
    </div>
    <?php
    unset($_SESSION['mensaje']);
    unset($_SESSION['mensaje_tipo']);
    ?>
<?php } ?>

<main>
<div class="contenedor">
