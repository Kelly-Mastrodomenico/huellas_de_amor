<?php
$tituloPagina = 'Mensajes de Contacto — Admin';
require_once '../templates/header-admin.php';
protegerAdmin();

// MARCAR COMO LEIDO
if (isset($_GET['leer']) && is_numeric($_GET['leer'])) {
    try {
        $stmt = $conexion->prepare("UPDATE `contacto` SET `leido` = 1 WHERE `id` = :id");
        $stmt->bindValue(':id', (int) $_GET['leer'], PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        // silencioso
    }
    header('Location: contacto.php');
    exit();
}

// BORRAR MENSAJE
if (isset($_GET['borrar']) && is_numeric($_GET['borrar'])) {
    try {
        $stmt = $conexion->prepare("DELETE FROM `contacto` WHERE `id` = :id");
        $stmt->bindValue(':id', (int) $_GET['borrar'], PDO::PARAM_INT);
        $stmt->execute();
        mensajeFlash('Mensaje eliminado correctamente.', 'exito');
    } catch (PDOException $e) {
        mensajeFlash('Error al eliminar el mensaje.', 'error');
    }
    header('Location: contacto.php');
    exit();
}

// MARCAR TODOS COMO LEIDOS
if (isset($_POST['marcar_todos'])) {
    try {
        $conexion->prepare("UPDATE `contacto` SET `leido` = 1")->execute();
        mensajeFlash('Todos los mensajes marcados como leídos.', 'exito');
    } catch (PDOException $e) {
        mensajeFlash('Error al actualizar.', 'error');
    }
    header('Location: contacto.php');
    exit();
}

// Filtro
$filtroLeido = isset($_GET['leido']) ? trim($_GET['leido']) : '';
$filtroBuscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Paginacion
$porPagina    = 10;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset       = ($paginaActual - 1) * $porPagina;

try {
    $donde  = "WHERE 1=1";
    $params = [];

    if ($filtroLeido === '0') {
        $donde .= " AND `leido` = 0";
    } elseif ($filtroLeido === '1') {
        $donde .= " AND `leido` = 1";
    }

    if (!empty($filtroBuscar)) {
        $donde .= " AND (`nombre` LIKE :buscar OR `email` LIKE :buscar2 OR `asunto` LIKE :buscar3)";
        $params[':buscar']  = '%' . $filtroBuscar . '%';
        $params[':buscar2'] = '%' . $filtroBuscar . '%';
        $params[':buscar3'] = '%' . $filtroBuscar . '%';
    }

    // Total
    $stmtTotal = $conexion->prepare("SELECT COUNT(*) FROM `contacto` $donde");
    foreach ($params as $k => $v) { $stmtTotal->bindValue($k, $v); }
    $stmtTotal->execute();
    $totalMensajes = $stmtTotal->fetchColumn();
    $totalPaginas  = ceil($totalMensajes / $porPagina);

    // No leidos
    $stmtNoLeidos = $conexion->prepare("SELECT COUNT(*) FROM `contacto` WHERE `leido` = 0");
    $stmtNoLeidos->execute();
    $totalNoLeidos = $stmtNoLeidos->fetchColumn();

    // Mensajes
    $stmtMensajes = $conexion->prepare(
        "SELECT * FROM `contacto` $donde ORDER BY `leido` ASC, `fecha` DESC
         LIMIT :limite OFFSET :offset"
    );
    foreach ($params as $k => $v) { $stmtMensajes->bindValue($k, $v); }
    $stmtMensajes->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmtMensajes->bindValue(':offset', $offset,    PDO::PARAM_INT);
    $stmtMensajes->execute();
    $mensajes = $stmtMensajes->fetchAll();

} catch (PDOException $e) {
    $mensajes      = [];
    $totalMensajes = 0;
    $totalPaginas  = 1;
    $totalNoLeidos = 0;
}
?>

<div class="contenedor" style="padding-top:24px; padding-bottom:40px;">
<div class="contenedor-admin">

    <h1><i class="fa-solid fa-envelope"></i> Mensajes de Contacto
        <?php if ($totalNoLeidos > 0) { ?>
        <span class="badge badge-acogida" style="font-size:0.9rem; margin-left:10px;">
            <?php echo $totalNoLeidos; ?> sin leer
        </span>
        <?php } ?>
    </h1>

    <!-- BARRA -->
    <div class="barra-admin">
        <form method="get" action="contacto.php" class="form-filtro">
            <input type="text" name="buscar"
                   placeholder="Buscar nombre, email o asunto..."
                   value="<?php echo htmlspecialchars($filtroBuscar); ?>">
            <select name="leido">
                <option value="">Todos</option>
                <option value="0" <?php echo $filtroLeido === '0' ? 'selected' : ''; ?>>Sin leer</option>
                <option value="1" <?php echo $filtroLeido === '1' ? 'selected' : ''; ?>>Leídos</option>
            </select>
            <button type="submit" class="btn-turquesa btn-sm">
                <i class="fa-solid fa-magnifying-glass"></i> Filtrar
            </button>
            <?php if (!empty($filtroLeido) || !empty($filtroBuscar)) { ?>
            <a href="contacto.php" class="btn-outline-coral btn-sm">Limpiar</a>
            <?php } ?>
        </form>

        <?php if ($totalNoLeidos > 0) { ?>
        <form method="post" action="contacto.php" style="display:inline;">
            <button type="submit" name="marcar_todos" class="btn-oscuro btn-sm"
                    onclick="return confirm('¿Marcar todos como leídos?')">
                <i class="fa-solid fa-check-double"></i> Marcar todos como leídos
            </button>
        </form>
        <?php } ?>
    </div>

    <p style="color:#888; margin-bottom:16px;">
        Mostrando <?php echo count($mensajes); ?> de <?php echo $totalMensajes; ?> mensajes
    </p>

    <!-- TABLA -->
    <?php if (!empty($mensajes)) { ?>
    <div class="tabla-wrapper">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Asunto</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mensajes as $msg) { ?>
                <tr class="<?php echo $msg['leido'] ? '' : 'fila-no-leida'; ?>">
                    <td>
                        <?php if (!$msg['leido']) { ?>
                            <span class="badge badge-acogida">Nuevo</span>
                        <?php } else { ?>
                            <span class="badge badge-adoptado">Leído</span>
                        <?php } ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($msg['nombre']); ?></strong></td>
                    <td>
                        <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>"
                           style="color:<?php echo $color_turquesa ?? '#4ECDC4'; ?>;">
                            <?php echo htmlspecialchars($msg['email']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($msg['asunto']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($msg['fecha'])); ?></td>
                    <td class="acciones">
                        <button class="btn-editar btn-sm btn-ver-mensaje"
                                data-id="<?php echo $msg['id']; ?>">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <?php if (!$msg['leido']) { ?>
                        <a href="contacto.php?leer=<?php echo $msg['id']; ?>"
                           class="btn-nuevo btn-sm"
                           title="Marcar como leído">
                            <i class="fa-solid fa-check"></i>
                        </a>
                        <?php } ?>
                        <a href="contacto.php?borrar=<?php echo $msg['id']; ?>"
                           class="btn-borrar btn-sm"
                           onclick="return confirm('¿Eliminar este mensaje?')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
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
        <a href="contacto.php?pagina=<?php echo $i; ?>&leido=<?php echo urlencode($filtroLeido); ?>&buscar=<?php echo urlencode($filtroBuscar); ?>"
           class="paginacion-item <?php echo $i === $paginaActual ? 'activo' : ''; ?>">
            <?php echo $i; ?>
        </a>
        <?php } ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <p class="sin-resultados">No hay mensajes que coincidan.</p>
    <?php } ?>

</div>
</div>

<!-- MODAL VER MENSAJE -->
<div id="modalMensaje" class="modal-fondo" style="display:none;">
    <div class="modal-caja">
        <button class="modal-cerrar" id="btnCerrarMensaje">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <h3 id="modalMsgTitulo">Mensaje</h3>
        <div id="modalMsgContenido"></div>
    </div>
</div>

<script>
const datosMensajes = <?php echo json_encode($mensajes); ?>;

document.querySelectorAll('.btn-ver-mensaje').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id  = parseInt(this.dataset.id);
        const msg = datosMensajes.find(function(x) { return x.id == id; });
        if (!msg) { return; }

        document.getElementById('modalMsgTitulo').textContent = msg.asunto;
        document.getElementById('modalMsgContenido').innerHTML =
            '<div class="modal-detalle-grid">' +
                '<div><strong>De:</strong><p>' + msg.nombre + '</p></div>' +
                '<div><strong>Email:</strong><p><a href="mailto:' + msg.email + '">' + msg.email + '</a></p></div>' +
                '<div><strong>Fecha:</strong><p>' + msg.fecha + '</p></div>' +
                '<div><strong>Estado:</strong><p>' + (msg.leido == 1 ? 'Leído' : 'Sin leer') + '</p></div>' +
                '<div class="modal-detalle-full"><strong>Mensaje:</strong>' +
                '<p style="white-space:pre-wrap; background:#f7f9fc; padding:12px; border-radius:8px; margin-top:6px;">' +
                msg.mensaje + '</p></div>' +
                '<div class="modal-detalle-full" style="margin-top:8px;">' +
                '<a href="mailto:' + msg.email + '?subject=Re: ' + encodeURIComponent(msg.asunto) + '" ' +
                'class="btn-turquesa btn-sm"><i class="fa-solid fa-reply"></i> Responder por email</a>' +
                '</div>' +
            '</div>';

        document.getElementById('modalMensaje').style.display = 'flex';

        // Marcar como leido automaticamente al abrir
        if (msg.leido == 0) {
            fetch('contacto.php?leer=' + msg.id);
            msg.leido = 1;
        }
    });
});

document.getElementById('btnCerrarMensaje').addEventListener('click', function() {
    document.getElementById('modalMensaje').style.display = 'none';
});

document.getElementById('modalMensaje').addEventListener('click', function(e) {
    if (e.target === this) { this.style.display = 'none'; }
});
</script>

<?php require_once '../templates/footer-admin.php'; ?>