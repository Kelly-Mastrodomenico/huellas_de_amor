<?php
$tituloPagina = 'Mi Panel — Huellas de Amor';
require_once '../templates/header.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit();
}

$idUsuario = (int) $_SESSION['usuario_id'];
$usuario   = null;
$solicitudes = [];

try {
    $stmt = $conexion->prepare("SELECT * FROM `usuarios` WHERE `id` = :id LIMIT 1");
    $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch();

    $stmtSolicitudes = $conexion->prepare(
        "SELECT s.*, m.nombre AS nombre_mascota, m.especie,
                (SELECT f.ruta_foto FROM `fotos_mascotas` f
                 WHERE f.id_mascota = m.id AND f.es_principal = 1
                 LIMIT 1) AS foto_mascota
         FROM `solicitudes_adopcion` s
         INNER JOIN `mascotas` m ON s.id_mascota = m.id
         WHERE s.id_usuario = :id
         ORDER BY s.fecha_solicitud DESC"
    );
    $stmtSolicitudes->bindParam(':id', $idUsuario, PDO::PARAM_INT);
    $stmtSolicitudes->execute();
    $solicitudes = $stmtSolicitudes->fetchAll();

} catch (PDOException $e) {
    $solicitudes = [];
}

$pestanaActiva = isset($_GET['tab']) ? trim($_GET['tab']) : 'solicitudes';
?>

<div class="contenedor" style="padding-top:16px;">
    <nav class="migas-pan">
        <a href="../index.php">Inicio</a>
        <span><i class="fa-solid fa-chevron-right"></i></span>
        <span>Mi Panel</span>
    </nav>
</div>

<section class="seccion">
<div class="contenedor">
<div class="panel-grid">

<aside class="panel-sidebar">
    <div class="panel-perfil-card">
        <div class="panel-avatar">
            <?php if ($usuario['foto_perfil']) { ?>
                <img src="../<?php echo htmlspecialchars($usuario['foto_perfil']); ?>"
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
        <a href="panel.php?tab=solicitudes"
           class="panel-menu-item <?php echo $pestanaActiva === 'solicitudes' ? 'activo' : ''; ?>">
            <i class="fa-solid fa-file-pen"></i>
            <span>Mis Solicitudes</span>
            <?php if (!empty($solicitudes)) { ?>
            <span class="panel-badge"><?php echo count($solicitudes); ?></span>
            <?php } ?>
        </a>
        <a href="panel.php?tab=favoritos"
           class="panel-menu-item <?php echo $pestanaActiva === 'favoritos' ? 'activo' : ''; ?>">
            <i class="fa-solid fa-heart"></i>
            <span>Mis Favoritos</span>
        </a>
        <a href="panel.php?tab=apadrinamientos"
           class="panel-menu-item <?php echo $pestanaActiva === 'apadrinamientos' ? 'activo' : ''; ?>">
            <i class="fa-solid fa-star"></i>
            <span>Mis Apadrinamientos</span>
        </a>
        <a href="panel.php?tab=donaciones"
           class="panel-menu-item <?php echo $pestanaActiva === 'donaciones' ? 'activo' : ''; ?>">
            <i class="fa-solid fa-hand-holding-heart"></i>
            <span>Mis Donaciones</span>
        </a>
        <a href="panel.php?tab=perfil"
           class="panel-menu-item <?php echo $pestanaActiva === 'perfil' ? 'activo' : ''; ?>">
            <i class="fa-solid fa-user-pen"></i>
            <span>Mi Perfil</span>
        </a>
        <a href="../logout.php" class="panel-menu-item panel-menu-salir">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Cerrar Sesion</span>
        </a>
    </nav>
</aside>

<div class="panel-contenido">

    <!-- SOLICITUDES -->
    <?php if ($pestanaActiva === 'solicitudes') { ?>
    <div class="panel-seccion">
        <h2><i class="fa-solid fa-file-pen"></i> Mis Solicitudes de Adopcion</h2>
        <?php if (!empty($solicitudes)) { ?>
        <div class="panel-solicitudes">
            <?php foreach ($solicitudes as $solicitud) {
                $fotoMascota = $solicitud['foto_mascota'];
                if ($fotoMascota && !str_starts_with($fotoMascota, 'http')) {
                    $fotoMascota = '../' . ltrim($fotoMascota, '/');
                }
                if (!$fotoMascota) {
                    $fotoMascota = 'https://picsum.photos/80/80?random=' . $solicitud['id_mascota'];
                }
            ?>
            <div class="solicitud-item">
                <img src="<?php echo htmlspecialchars($fotoMascota); ?>"
                     alt="<?php echo htmlspecialchars($solicitud['nombre_mascota']); ?>">
                <div class="solicitud-item-info">
                    <h4><?php echo htmlspecialchars($solicitud['nombre_mascota']); ?></h4>
                    <p><?php echo ucfirst(htmlspecialchars($solicitud['especie'])); ?></p>
                    <small>Enviada el <?php echo date('d/m/Y', strtotime($solicitud['fecha_solicitud'])); ?></small>
                </div>
                <div class="solicitud-item-estado">
                    <?php if ($solicitud['estado'] === 'pendiente') { ?>
                        <span class="badge badge-acogida">Pendiente</span>
                        <p>En revision</p>
                    <?php } elseif ($solicitud['estado'] === 'aprobada') { ?>
                        <span class="badge badge-disponible">Aprobada</span>
                        <p>¡Enhorabuena!</p>
                        <a href="../certificado_adopcion.php?id=<?php echo $solicitud['id']; ?>"
                           target="_blank" class="btn-outline-coral btn-sm" style="margin-top:6px;">
                            <i class="fa-solid fa-file-pdf"></i> Certificado
                        </a>
                    <?php } else { ?>
                        <span class="badge badge-adoptado">Rechazada</span>
                        <?php if ($solicitud['notas_admin']) { ?>
                        <p><?php echo htmlspecialchars($solicitud['notas_admin']); ?></p>
                        <?php } ?>
                    <?php } ?>
                </div>
                <a href="../mascotas/detalle.php?id=<?php echo $solicitud['id_mascota']; ?>"
                   class="btn-outline-turquesa btn-sm">
                    Ver mascota
                </a>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="panel-vacio">
            <i class="fa-solid fa-file-circle-xmark"></i>
            <h3>No tienes solicitudes</h3>
            <p>Aun no has solicitado adoptar ninguna mascota.</p>
            <a href="../adoptar.php" class="btn-coral">Ver mascotas disponibles</a>
        </div>
        <?php } ?>
    </div>

    <!-- FAVORITOS -->
    <?php } elseif ($pestanaActiva === 'favoritos') { ?>
    <div class="panel-seccion">
        <h2><i class="fa-solid fa-heart"></i> Mis Favoritos</h2>
        <div class="panel-vacio">
            <i class="fa-regular fa-heart"></i>
            <h3>No tienes favoritos guardados</h3>
            <p>Guarda mascotas como favoritas para encontrarlas facilmente.</p>
            <a href="../adoptar.php" class="btn-coral">Explorar mascotas</a>
        </div>
    </div>

    <!-- APADRINAMIENTOS -->
    <?php } elseif ($pestanaActiva === 'apadrinamientos') { ?>
    <div class="panel-seccion">
        <h2><i class="fa-solid fa-star"></i> Mis Apadrinamientos</h2>
        <div class="panel-vacio">
            <i class="fa-regular fa-star"></i>
            <h3>No tienes apadrinamientos activos</h3>
            <p>Apadrina una mascota y ayuda a cubrir sus gastos mensuales.</p>
            <a href="../apadrinar.php" class="btn-coral">Ver mascotas para apadrinar</a>
        </div>
    </div>

    <!-- DONACIONES -->
    <?php } elseif ($pestanaActiva === 'donaciones') { ?>
    <div class="panel-seccion">
        <h2><i class="fa-solid fa-hand-holding-heart"></i> Mis Donaciones</h2>
        <?php
        try {
            $stmtDon = $conexion->prepare(
                "SELECT * FROM `donaciones`
                 WHERE `id_usuario` = :id
                 ORDER BY `fecha` DESC"
            );
            $stmtDon->bindParam(':id', $idUsuario, PDO::PARAM_INT);
            $stmtDon->execute();
            $misDonaciones = $stmtDon->fetchAll();
        } catch (PDOException $e) {
            $misDonaciones = [];
        }
        ?>
        <?php if (!empty($misDonaciones)) { ?>
        <div class="tabla-wrapper">
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Fecha</th>
                        <th>Certificado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($misDonaciones as $don) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($don['concepto']); ?></td>
                        <td><strong><?php echo number_format($don['monto'], 2, ',', '.'); ?>€</strong></td>
                        <td><?php echo ucfirst(htmlspecialchars($don['metodo_pago'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($don['fecha'])); ?></td>
                        <td>
                            <a href="../certificado_donacion.php?id=<?php echo $don['id']; ?>"
                               target="_blank" class="btn-outline-coral btn-sm">
                                <i class="fa-solid fa-file-pdf"></i> Descargar
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } else { ?>
        <div class="panel-vacio">
            <i class="fa-solid fa-hand-holding-heart"></i>
            <h3>No tienes donaciones registradas</h3>
            <p>Cada donación ayuda a mantener el refugio y cuidar a los animales.</p>
            <a href="../donaciones.php" class="btn-coral">Hacer una donación</a>
        </div>
        <?php } ?>
    </div>

    <!-- MI PERFIL -->
    <?php } elseif ($pestanaActiva === 'perfil') { ?>
    <div class="panel-seccion">
        <h2><i class="fa-solid fa-user-pen"></i> Mi Perfil</h2>
        <div class="panel-perfil-datos">
            <div class="perfil-fila">
                <span>Nombre</span>
                <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>
            </div>
            <div class="perfil-fila">
                <span>Apellidos</span>
                <strong><?php echo htmlspecialchars($usuario['apellidos']); ?></strong>
            </div>
            <div class="perfil-fila">
                <span>Email</span>
                <strong><?php echo htmlspecialchars($usuario['email']); ?></strong>
            </div>
            <div class="perfil-fila">
                <span>Telefono</span>
                <strong><?php echo htmlspecialchars($usuario['telefono'] ?? '—'); ?></strong>
            </div>
            <div class="perfil-fila">
                <span>Ciudad</span>
                <strong><?php echo htmlspecialchars($usuario['ciudad'] ?? '—'); ?></strong>
            </div>
            <div class="perfil-fila">
                <span>Miembro desde</span>
                <strong><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></strong>
            </div>
        </div>
        <div style="margin-top:24px;">
            <a href="editar_perfil.php" class="btn-coral">
                <i class="fa-solid fa-pen"></i> Editar Perfil
            </a>
        </div>
    </div>
    <?php } ?>

</div>
</div>
</div>
</section>

<?php require_once '../templates/footer.php'; ?>