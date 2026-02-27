<?php
$tituloPagina = 'Panel Admin — Huellas de Amor';
require_once '../templates/header-admin.php';

try {
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM `mascotas` WHERE `estado` = 'disponible' AND `activo` = 1");
    $stmt->execute();
    $totalDisponibles = $stmt->fetchColumn();

    $stmt = $conexion->prepare("SELECT COUNT(*) FROM `solicitudes_adopcion` WHERE `estado` = 'pendiente'");
    $stmt->execute();
    $totalPendientes = $stmt->fetchColumn();

    $stmt = $conexion->prepare("SELECT COUNT(*) FROM `usuarios` WHERE `rol` = 'registrado' AND `activo` = 1");
    $stmt->execute();
    $totalUsuarios = $stmt->fetchColumn();

    $stmt = $conexion->prepare("SELECT COALESCE(SUM(`monto`), 0) FROM `donaciones` WHERE MONTH(`fecha`) = MONTH(NOW()) AND YEAR(`fecha`) = YEAR(NOW())");
    $stmt->execute();
    $totalDonaciones = $stmt->fetchColumn();

    $stmt = $conexion->prepare(
        "SELECT s.*, u.nombre AS nombre_usuario, m.nombre AS nombre_mascota
         FROM `solicitudes_adopcion` s
         INNER JOIN `usuarios` u ON s.id_usuario = u.id
         INNER JOIN `mascotas` m ON s.id_mascota = m.id
         ORDER BY s.fecha_solicitud DESC
         LIMIT 5"
    );
    $stmt->execute();
    $ultimasSolicitudes = $stmt->fetchAll();

    $stmt = $conexion->prepare(
        "SELECT * FROM `mascotas` WHERE `activo` = 1 ORDER BY `fecha_ingreso` DESC LIMIT 5"
    );
    $stmt->execute();
    $ultimasMascotas = $stmt->fetchAll();

} catch (PDOException $e) {
    $totalDisponibles   = 0;
    $totalPendientes    = 0;
    $totalUsuarios      = 0;
    $totalDonaciones    = 0;
    $ultimasSolicitudes = [];
    $ultimasMascotas    = [];
}
?>

<div class="contenedor" style="padding-top: <?php echo $espacio_md ?? '24px'; ?>; padding-bottom: 40px;">
<div class="contenedor-admin">

    <h1><i class="fa-solid fa-chart-line"></i> Dashboard</h1>

    <!-- ESTADISTICAS -->
    <div class="grid-stats">
        <div class="stat-card stat-coral">
            <i class="fa-solid fa-paw"></i>
            <span class="stat-numero"><?php echo $totalDisponibles; ?></span>
            <span>Disponibles</span>
        </div>
        <div class="stat-card stat-turquesa">
            <i class="fa-solid fa-file-pen"></i>
            <span class="stat-numero"><?php echo $totalPendientes; ?></span>
            <span>Solicitudes</span>
        </div>
        <div class="stat-card stat-verde">
            <i class="fa-solid fa-users"></i>
            <span class="stat-numero"><?php echo $totalUsuarios; ?></span>
            <span>Usuarios</span>
        </div>
        <div class="stat-card stat-oscuro">
            <i class="fa-solid fa-hand-holding-heart"></i>
            <span class="stat-numero"><?php echo number_format($totalDonaciones, 0); ?>€</span>
            <span>Donaciones</span>
        </div>
    </div>

    <!-- BOTONES ACCESO RAPIDO -->
    <div class="botones-rapidos">
        <a href="mascotas.php" class="btn-coral">
            <i class="fa-solid fa-plus"></i> Nueva Mascota
        </a>
        <a href="solicitudes.php" class="btn-turquesa">
            <i class="fa-solid fa-list"></i> Solicitudes
        </a>
        <a href="noticias.php" class="btn-oscuro">
            <i class="fa-solid fa-newspaper"></i> Noticias
        </a>
        <a href="usuarios.php" class="btn-outline-coral">
            <i class="fa-solid fa-users"></i> Usuarios
        </a>
    </div>

    <!-- ULTIMAS SOLICITUDES -->
    <h2>Ultimas Solicitudes</h2>

    <?php if (!empty($ultimasSolicitudes)) { ?>
    <div class="tabla-wrapper">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Mascota</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ultimasSolicitudes as $solicitud) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($solicitud['nombre_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($solicitud['nombre_mascota']); ?></td>
                    <td>
                        <?php if ($solicitud['estado'] === 'pendiente') { ?>
                            <span class="badge badge-acogida">Pendiente</span>
                        <?php } elseif ($solicitud['estado'] === 'aprobada') { ?>
                            <span class="badge badge-disponible">Aprobada</span>
                        <?php } else { ?>
                            <span class="badge badge-adoptado">Rechazada</span>
                        <?php } ?>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($solicitud['fecha_solicitud'])); ?></td>
                    <td class="acciones">
                        <a href="solicitudes.php?id=<?php echo $solicitud['id']; ?>" class="btn-editar btn-sm">
                            Ver
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } else { ?>
        <p class="sin-resultados">No hay solicitudes todavia.</p>
    <?php } ?>

    <!-- ULTIMAS MASCOTAS -->
    <h2>Ultimas Mascotas Añadidas</h2>

    <?php if (!empty($ultimasMascotas)) { ?>
    <div class="tabla-wrapper">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Especie</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ultimasMascotas as $mascota) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($mascota['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($mascota['especie']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $mascota['estado']; ?>">
                            <?php echo textoEstado($mascota['estado']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($mascota['fecha_ingreso'])); ?></td>
                    <td class="acciones">
                        <a href="mascota_editar.php?id=<?php echo $mascota['id']; ?>" class="btn-editar btn-sm">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <a href="mascota_borrar.php?id=<?php echo $mascota['id']; ?>" class="btn-borrar btn-sm">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } else { ?>
        <p class="sin-resultados">No hay mascotas todavia.</p>
    <?php } ?>

</div>
</div>

<?php require_once '../templates/footer-admin.php'; ?>