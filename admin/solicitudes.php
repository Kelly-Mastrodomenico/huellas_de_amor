<?php
$tituloPagina = 'Gestionar Solicitudes — Admin';
require_once '../templates/header-admin.php';

// Filtros
$filtroEstado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$filtroBuscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Paginacion
$porPagina   = 10;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset       = ($paginaActual - 1) * $porPagina;

// PROCESAR ACCION (aprobar / rechazar)
if (isset($_POST['accion']) && isset($_POST['id_solicitud'])) {
    $idSolicitud  = (int) $_POST['id_solicitud'];
    $accion       = trim($_POST['accion']);
    $notasAdmin   = trim($_POST['notas_admin'] ?? '');

    if (in_array($accion, ['aprobada', 'rechazada'])) {
        try {
            $stmtUpdate = $conexion->prepare(
                "UPDATE `solicitudes_adopcion`
                 SET `estado` = :estado, `notas_admin` = :notas
                 WHERE `id` = :id"
            );
            $stmtUpdate->bindParam(':estado', $accion,      PDO::PARAM_STR);
            $stmtUpdate->bindParam(':notas',  $notasAdmin,  PDO::PARAM_STR);
            $stmtUpdate->bindParam(':id',     $idSolicitud, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // Si se aprueba, cambiar estado de la mascota a 'adoptado'
            if ($accion === 'aprobada') {
                $stmtMascota = $conexion->prepare(
                    "UPDATE `mascotas` m
                     INNER JOIN `solicitudes_adopcion` s ON m.id = s.id_mascota
                     SET m.estado = 'adoptado'
                     WHERE s.id = :id"
                );
                $stmtMascota->bindParam(':id', $idSolicitud, PDO::PARAM_INT);
                $stmtMascota->execute();
            }

            mensajeFlash('Solicitud ' . ($accion === 'aprobada' ? 'aprobada' : 'rechazada') . ' correctamente.', 'exito');

        } catch (PDOException $e) {
            mensajeFlash('Error al actualizar la solicitud.', 'error');
        }
    }

    header('Location: solicitudes.php');
    exit();
}

// OBTENER SOLICITUDES con filtros
try {
    $donde  = "WHERE 1=1";
    $params = [];

    if (!empty($filtroEstado)) {
        $donde .= " AND s.estado = :estado";
        $params[':estado'] = $filtroEstado;
    }

    if (!empty($filtroBuscar)) {
        $donde .= " AND (u.nombre LIKE :buscar OR m.nombre LIKE :buscar2)";
        $params[':buscar']  = '%' . $filtroBuscar . '%';
        $params[':buscar2'] = '%' . $filtroBuscar . '%';
    }

    // Total para paginacion
    $stmtTotal = $conexion->prepare(
        "SELECT COUNT(*) FROM `solicitudes_adopcion` s
         INNER JOIN `usuarios` u ON s.id_usuario = u.id
         INNER JOIN `mascotas` m ON s.id_mascota = m.id
         $donde"
    );
    foreach ($params as $clave => $valor) {
        $stmtTotal->bindValue($clave, $valor);
    }
    $stmtTotal->execute();
    $totalSolicitudes = $stmtTotal->fetchColumn();
    $totalPaginas     = ceil($totalSolicitudes / $porPagina);

    // Solicitudes de esta pagina
    $stmtSolicitudes = $conexion->prepare(
        "SELECT s.*, u.nombre AS nombre_usuario, u.email AS email_usuario,
                u.telefono AS telefono_usuario,
                m.nombre AS nombre_mascota, m.especie AS especie_mascota
         FROM `solicitudes_adopcion` s
         INNER JOIN `usuarios` u ON s.id_usuario = u.id
         INNER JOIN `mascotas` m ON s.id_mascota = m.id
         $donde
         ORDER BY s.fecha_solicitud DESC
         LIMIT :limite OFFSET :offset"
    );
    foreach ($params as $clave => $valor) {
        $stmtSolicitudes->bindValue($clave, $valor);
    }
    $stmtSolicitudes->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmtSolicitudes->bindValue(':offset', $offset,    PDO::PARAM_INT);
    $stmtSolicitudes->execute();
    $solicitudes = $stmtSolicitudes->fetchAll();

} catch (PDOException $e) {
    $solicitudes      = [];
    $totalSolicitudes = 0;
    $totalPaginas     = 1;
}
?>

<div class="contenedor" style="padding-top:24px; padding-bottom:40px;">
<div class="contenedor-admin">

    <h1><i class="fa-solid fa-file-pen"></i> Gestionar Solicitudes</h1>

    <!-- FILTROS -->
    <div class="barra-admin">
        <form method="get" action="solicitudes.php" class="form-filtro">
            <input type="text" name="buscar"
                   placeholder="Buscar usuario o mascota..."
                   value="<?php echo htmlspecialchars($filtroBuscar); ?>">
            <select name="estado">
                <option value="">Todos los estados</option>
                <option value="pendiente"  <?php echo $filtroEstado === 'pendiente'  ? 'selected' : ''; ?>>Pendiente</option>
                <option value="aprobada"   <?php echo $filtroEstado === 'aprobada'   ? 'selected' : ''; ?>>Aprobada</option>
                <option value="rechazada"  <?php echo $filtroEstado === 'rechazada'  ? 'selected' : ''; ?>>Rechazada</option>
            </select>
            <button type="submit" class="btn-turquesa btn-sm">
                <i class="fa-solid fa-magnifying-glass"></i> Filtrar
            </button>
            <?php if (!empty($filtroEstado) || !empty($filtroBuscar)) { ?>
            <a href="solicitudes.php" class="btn-outline-coral btn-sm">Limpiar</a>
            <?php } ?>
        </form>
    </div>

    <p style="margin-bottom:16px; color:#888;">
        Mostrando <?php echo count($solicitudes); ?> de <?php echo $totalSolicitudes; ?> solicitudes
    </p>

    <!-- TABLA -->
    <?php if (!empty($solicitudes)) { ?>
    <div class="tabla-wrapper">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Mascota</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($solicitudes as $solicitud) { ?>
                <tr>
                    <td><?php echo $solicitud['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($solicitud['nombre_usuario']); ?></strong>
                        <br>
                        <small style="color:#888;"><?php echo htmlspecialchars($solicitud['email_usuario']); ?></small>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($solicitud['nombre_mascota']); ?>
                        <br>
                        <small style="color:#888;"><?php echo htmlspecialchars($solicitud['especie_mascota']); ?></small>
                    </td>
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
                        <!-- Boton ver detalles -->
                        <button class="btn-editar btn-sm btn-ver-detalle"
                                data-id="<?php echo $solicitud['id']; ?>">
                            <i class="fa-solid fa-eye"></i>
                        </button>

                        <!-- Aprobar — solo si esta pendiente -->
                        <?php if ($solicitud['estado'] === 'pendiente') { ?>
                        <form method="post" action="solicitudes.php" style="display:inline;">
                            <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['id']; ?>">
                            <input type="hidden" name="accion" value="aprobada">
                            <input type="hidden" name="notas_admin" value="">
                            <button type="submit" class="btn-nuevo btn-sm"
                                    onclick="return confirm('¿Aprobar esta solicitud? La mascota pasara a estado Adoptado.')">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </form>

                        <!-- Rechazar -->
                        <button class="btn-borrar btn-sm btn-rechazar"
                                data-id="<?php echo $solicitud['id']; ?>"
                                data-nombre="<?php echo htmlspecialchars($solicitud['nombre_usuario']); ?>">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINACION -->
    <?php if ($totalPaginas > 1) { ?>
    <div class="paginacion">
        <?php for ($i = 1; $i <= $totalPaginas; $i++) { ?>
        <a href="solicitudes.php?pagina=<?php echo $i; ?>&estado=<?php echo urlencode($filtroEstado); ?>&buscar=<?php echo urlencode($filtroBuscar); ?>"
           class="paginacion-item <?php echo $i === $paginaActual ? 'activo' : ''; ?>">
            <?php echo $i; ?>
        </a>
        <?php } ?>
    </div>
    <?php } ?>

    <?php } else { ?>
        <p class="sin-resultados">No hay solicitudes que coincidan con los filtros.</p>
    <?php } ?>

</div>
</div>

<!-- MODAL VER DETALLE -->
<div id="modalDetalle" class="modal-fondo" style="display:none;">
    <div class="modal-caja">
        <button class="modal-cerrar" id="btnCerrarModal">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <h3 id="modalTitulo">Detalle de Solicitud</h3>
        <div id="modalContenido"></div>
    </div>
</div>

<!-- MODAL RECHAZAR CON NOTA -->
<div id="modalRechazar" class="modal-fondo" style="display:none;">
    <div class="modal-caja">
        <button class="modal-cerrar" id="btnCerrarRechazar">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <h3>Rechazar Solicitud</h3>
        <p style="color:#666; margin-bottom:16px;">
            Puedes añadir una nota explicando el motivo del rechazo (opcional).
        </p>
        <form method="post" action="solicitudes.php">
            <input type="hidden" name="id_solicitud" id="rechazarId">
            <input type="hidden" name="accion" value="rechazada">
            <div class="form-grupo" style="margin-bottom:16px;">
                <label for="notasRechazo">Nota para el usuario</label>
                <textarea id="notasRechazo" name="notas_admin" rows="3"
                          placeholder="Motivo del rechazo (opcional)..."></textarea>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn-outline-coral" id="btnCancelarRechazo">Cancelar</button>
                <button type="submit" class="btn-borrar">
                    <i class="fa-solid fa-xmark"></i> Confirmar Rechazo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Datos de solicitudes para el modal de detalle -->
<script>
const datosSolicitudes = <?php echo json_encode($solicitudes); ?>;

// Modal detalle
document.querySelectorAll('.btn-ver-detalle').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = parseInt(this.dataset.id);
        const s  = datosSolicitudes.find(function(x) { return x.id == id; });
        if (!s) { return; }

        document.getElementById('modalTitulo').textContent = 'Solicitud #' + s.id;

        const estadoTexto = s.estado === 'pendiente' ? 'Pendiente' : (s.estado === 'aprobada' ? 'Aprobada' : 'Rechazada');
        const estadoClase = s.estado === 'pendiente' ? 'badge-acogida' : (s.estado === 'aprobada' ? 'badge-disponible' : 'badge-adoptado');

        document.getElementById('modalContenido').innerHTML =
            '<div class="modal-detalle-grid">' +
                '<div><strong>Usuario:</strong><p>' + s.nombre_usuario + '</p></div>' +
                '<div><strong>Email:</strong><p>' + s.email_usuario + '</p></div>' +
                '<div><strong>Telefono:</strong><p>' + (s.telefono_usuario || '—') + '</p></div>' +
                '<div><strong>Mascota:</strong><p>' + s.nombre_mascota + '</p></div>' +
                '<div><strong>Estado:</strong><p><span class="badge ' + estadoClase + '">' + estadoTexto + '</span></p></div>' +
                '<div class="modal-detalle-full"><strong>Motivacion:</strong><p>' + (s.motivacion || '—') + '</p></div>' +
                '<div class="modal-detalle-full"><strong>Situacion del hogar:</strong><p>' + (s.situacion_hogar || '—') + '</p></div>' +
                '<div><strong>Tiene animales:</strong><p>' + (s.tiene_animales == 1 ? 'Si' : 'No') + '</p></div>' +
                '<div><strong>Fecha:</strong><p>' + s.fecha_solicitud + '</p></div>' +
                (s.notas_admin ? '<div class="modal-detalle-full"><strong>Notas admin:</strong><p>' + s.notas_admin + '</p></div>' : '') +
            '</div>';

        document.getElementById('modalDetalle').style.display = 'flex';
    });
});

document.getElementById('btnCerrarModal').addEventListener('click', function() {
    document.getElementById('modalDetalle').style.display = 'none';
});

// Modal rechazar
document.querySelectorAll('.btn-rechazar').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('rechazarId').value = this.dataset.id;
        document.getElementById('modalRechazar').style.display = 'flex';
    });
});

document.getElementById('btnCerrarRechazar').addEventListener('click', function() {
    document.getElementById('modalRechazar').style.display = 'none';
});

document.getElementById('btnCancelarRechazo').addEventListener('click', function() {
    document.getElementById('modalRechazar').style.display = 'none';
});

// Cerrar modales al hacer clic fuera
document.querySelectorAll('.modal-fondo').forEach(function(fondo) {
    fondo.addEventListener('click', function(e) {
        if (e.target === this) { this.style.display = 'none'; }
    });
});
</script>

<?php require_once '../templates/footer-admin.php'; ?>
