<?php
$tituloPagina = 'Gestión de Usuarios — Admin';
require_once '../templates/header-admin.php';
protegerAdmin();

$errores = [];

// CAMBIAR ROL
if (isset($_POST['cambiar_rol'])) {
    $idUsuario = isset($_POST['id_usuario']) && is_numeric($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : 0;
    $nuevoRol  = trim($_POST['nuevo_rol'] ?? '');

    if ($idUsuario > 0 && in_array($nuevoRol, ['visitante', 'registrado', 'admin'])) {
        // No puede cambiar su propio rol
        if ($idUsuario === (int) $_SESSION['usuario_id']) {
            mensajeFlash('No puedes cambiar tu propio rol.', 'error');
        } else {
            try {
                $stmt = $conexion->prepare("UPDATE `usuarios` SET `rol` = :rol WHERE `id` = :id");
                $stmt->bindParam(':rol', $nuevoRol,  PDO::PARAM_STR);
                $stmt->bindParam(':id',  $idUsuario, PDO::PARAM_INT);
                $stmt->execute();
                mensajeFlash('Rol actualizado correctamente.', 'exito');
            } catch (PDOException $e) {
                mensajeFlash('Error al cambiar el rol.', 'error');
            }
        }
    }
    header('Location: usuarios.php');
    exit();
}

// ACTIVAR / DESACTIVAR
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $idToggle = (int) $_GET['toggle'];
    if ($idToggle === (int) $_SESSION['usuario_id']) {
        mensajeFlash('No puedes desactivar tu propia cuenta.', 'error');
    } else {
        try {
            $stmt = $conexion->prepare(
                "UPDATE `usuarios` SET `activo` = IF(`activo` = 1, 0, 1) WHERE `id` = :id"
            );
            $stmt->bindValue(':id', $idToggle, PDO::PARAM_INT);
            $stmt->execute();
            mensajeFlash('Estado del usuario actualizado.', 'exito');
        } catch (PDOException $e) {
            mensajeFlash('Error al actualizar el estado.', 'error');
        }
    }
    header('Location: usuarios.php');
    exit();
}


// ELIMINAR
if (isset($_GET['borrar']) && is_numeric($_GET['borrar'])) {
    $idBorrar = (int) $_GET['borrar'];
    if ($idBorrar === (int) $_SESSION['usuario_id']) {
        mensajeFlash('No puedes eliminar tu propia cuenta.', 'error');
    } else {
        try {
            $stmt = $conexion->prepare("DELETE FROM `usuarios` WHERE `id` = :id");
            $stmt->bindValue(':id', $idBorrar, PDO::PARAM_INT);
            $stmt->execute();
            mensajeFlash('Usuario eliminado correctamente.', 'exito');
        } catch (PDOException $e) {
            mensajeFlash('Error al eliminar el usuario.', 'error');
        }
    }
    header('Location: usuarios.php');
    exit();
}


// FILTROS Y PAGINACION
$filtroRol    = trim($_GET['rol']    ?? '');
$filtroBuscar = trim($_GET['buscar'] ?? '');
$filtroActivo = trim($_GET['activo'] ?? '');

$porPagina    = 12;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset       = ($paginaActual - 1) * $porPagina;

try {
    $donde  = "WHERE 1=1";
    $params = [];

    if (!empty($filtroRol)) {
        $donde .= " AND `rol` = :rol";
        $params[':rol'] = $filtroRol;
    }
    if ($filtroActivo !== '') {
        $donde .= " AND `activo` = :activo";
        $params[':activo'] = (int) $filtroActivo;
    }
    if (!empty($filtroBuscar)) {
        $donde .= " AND (`nombre` LIKE :buscar OR `apellidos` LIKE :buscar2 OR `email` LIKE :buscar3)";
        $params[':buscar']  = '%' . $filtroBuscar . '%';
        $params[':buscar2'] = '%' . $filtroBuscar . '%';
        $params[':buscar3'] = '%' . $filtroBuscar . '%';
    }

    // Estadisticas
    $stmtStats = $conexion->prepare(
        "SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN `rol` = 'admin'      THEN 1 ELSE 0 END) AS admins,
            SUM(CASE WHEN `rol` = 'registrado' THEN 1 ELSE 0 END) AS registrados,
            SUM(CASE WHEN `activo` = 0         THEN 1 ELSE 0 END) AS inactivos
         FROM `usuarios`"
    );
    $stmtStats->execute();
    $stats = $stmtStats->fetch();

    // Total filtrado
    $stmtTotal = $conexion->prepare("SELECT COUNT(*) FROM `usuarios` $donde");
    foreach ($params as $k => $v) { $stmtTotal->bindValue($k, $v); }
    $stmtTotal->execute();
    $totalUsuarios = $stmtTotal->fetchColumn();
    $totalPaginas  = ceil($totalUsuarios / $porPagina);

    // Usuarios
    $stmtUsers = $conexion->prepare(
        "SELECT * FROM `usuarios` $donde
         ORDER BY `fecha_registro` DESC
         LIMIT :limite OFFSET :offset"
    );
    foreach ($params as $k => $v) { $stmtUsers->bindValue($k, $v); }
    $stmtUsers->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmtUsers->bindValue(':offset', $offset,    PDO::PARAM_INT);
    $stmtUsers->execute();
    $usuarios = $stmtUsers->fetchAll();

} catch (PDOException $e) {
    $usuarios      = [];
    $totalUsuarios = 0;
    $totalPaginas  = 1;
    $stats         = ['total' => 0, 'admins' => 0, 'registrados' => 0, 'inactivos' => 0];
}
?>

<div class="contenedor" style="padding-top:24px; padding-bottom:40px;">
<div class="contenedor-admin">

    <div class="admin-cabecera">
        <h1><i class="fa-solid fa-users"></i> Usuarios</h1>
    </div>

    <!-- ESTADISTICAS -->
    <div class="dashboard-stats">
        <div class="stat-card stat-coral">
            <i class="fa-solid fa-users"></i>
            <span class="stat-num"><?php echo $stats['total']; ?></span>
            <span class="stat-label">Total usuarios</span>
        </div>
        <div class="stat-card stat-turquesa">
            <i class="fa-solid fa-user-check"></i>
            <span class="stat-num"><?php echo $stats['registrados']; ?></span>
            <span class="stat-label">Registrados</span>
        </div>
        <div class="stat-card stat-verde">
            <i class="fa-solid fa-user-shield"></i>
            <span class="stat-num"><?php echo $stats['admins']; ?></span>
            <span class="stat-label">Administradores</span>
        </div>
        <div class="stat-card stat-oscuro">
            <i class="fa-solid fa-user-slash"></i>
            <span class="stat-num"><?php echo $stats['inactivos']; ?></span>
            <span class="stat-label">Inactivos</span>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="barra-admin">
        <form method="get" action="usuarios.php" class="form-filtro">
            <input type="text" name="buscar"
                   placeholder="Buscar nombre o email..."
                   value="<?php echo htmlspecialchars($filtroBuscar); ?>">
            <select name="rol">
                <option value="">Todos los roles</option>
                <option value="visitante"  <?php echo $filtroRol === 'visitante'  ? 'selected' : ''; ?>>Visitante</option>
                <option value="registrado" <?php echo $filtroRol === 'registrado' ? 'selected' : ''; ?>>Registrado</option>
                <option value="admin"      <?php echo $filtroRol === 'admin'      ? 'selected' : ''; ?>>Admin</option>
            </select>
            <select name="activo">
                <option value="">Todos los estados</option>
                <option value="1" <?php echo $filtroActivo === '1' ? 'selected' : ''; ?>>Activos</option>
                <option value="0" <?php echo $filtroActivo === '0' ? 'selected' : ''; ?>>Inactivos</option>
            </select>
            <button type="submit" class="btn-turquesa btn-sm">
                <i class="fa-solid fa-magnifying-glass"></i> Filtrar
            </button>
            <?php if (!empty($filtroRol) || !empty($filtroBuscar) || $filtroActivo !== '') { ?>
            <a href="usuarios.php" class="btn-outline-coral btn-sm">Limpiar</a>
            <?php } ?>
        </form>
    </div>

    <p style="color:#888; margin-bottom:16px;">
        <?php echo $totalUsuarios; ?> usuario<?php echo $totalUsuarios !== 1 ? 's' : ''; ?> encontrado<?php echo $totalUsuarios !== 1 ? 's' : ''; ?>
    </p>

    <!-- TABLA -->
    <?php if (!empty($usuarios)) { ?>
    <div class="tabla-wrapper">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usr) {
                    $esMiCuenta = ($usr['id'] == $_SESSION['usuario_id']);
                    $foto = $usr['foto_perfil']
                        ? (str_starts_with($usr['foto_perfil'], 'http') ? $usr['foto_perfil'] : '../' . $usr['foto_perfil'])
                        : null;
                ?>
                <tr class="<?php echo !$usr['activo'] ? 'fila-inactiva' : ''; ?>">
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <?php if ($foto) { ?>
                            <img src="<?php echo htmlspecialchars($foto); ?>"
                                 style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                            <?php } else { ?>
                            <div style="width:36px; height:36px; border-radius:50%; background:#e0e0e0;
                                        display:flex; align-items:center; justify-content:center;">
                                <i class="fa-solid fa-user" style="color:#aaa; font-size:0.9rem;"></i>
                            </div>
                            <?php } ?>
                            <div>
                                <strong><?php echo htmlspecialchars($usr['nombre'] . ' ' . $usr['apellidos']); ?></strong>
                                <?php if ($esMiCuenta) { ?>
                                <span style="font-size:0.7rem; color:#4ECDC4; font-weight:700;"> (tú)</span>
                                <?php } ?>
                                <?php if ($usr['ciudad']) { ?>
                                <br><small style="color:#aaa;">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?php echo htmlspecialchars($usr['ciudad']); ?>
                                </small>
                                <?php } ?>
                            </div>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($usr['email']); ?></td>
                    <td>
                        <?php if (!$esMiCuenta) { ?>
                        <form method="post" action="usuarios.php" style="display:inline;">
                            <input type="hidden" name="id_usuario" value="<?php echo $usr['id']; ?>">
                            <select name="nuevo_rol" onchange="this.form.submit()"
                                    style="padding:4px 8px; border-radius:6px; border:1px solid #ddd;
                                           font-size:0.8rem; cursor:pointer;">
                                <option value="visitante"  <?php echo $usr['rol'] === 'visitante'  ? 'selected' : ''; ?>>Visitante</option>
                                <option value="registrado" <?php echo $usr['rol'] === 'registrado' ? 'selected' : ''; ?>>Registrado</option>
                                <option value="admin"      <?php echo $usr['rol'] === 'admin'      ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <input type="hidden" name="cambiar_rol" value="1">
                        </form>
                        <?php } else { ?>
                        <span class="badge badge-disponible">Admin</span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($usr['activo']) { ?>
                            <span class="badge badge-disponible">Activo</span>
                        <?php } else { ?>
                            <span class="badge badge-adoptado">Inactivo</span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php echo $usr['fecha_registro']
                            ? date('d/m/Y', strtotime($usr['fecha_registro']))
                            : '—'; ?>
                    </td>
                    <td class="acciones">
                        <button class="btn-editar btn-sm btn-ver-usuario"
                                data-usuario='<?php echo htmlspecialchars(json_encode([
                                    'nombre'    => $usr['nombre'] . ' ' . $usr['apellidos'],
                                    'email'     => $usr['email'],
                                    'telefono'  => $usr['telefono'] ?? '—',
                                    'ciudad'    => $usr['ciudad'] ?? '—',
                                    'rol'       => $usr['rol'],
                                    'activo'    => $usr['activo'],
                                    'registro'  => $usr['fecha_registro'] ?? '—',
                                ]), ENT_QUOTES); ?>'
                                title="Ver detalles">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <?php if (!$esMiCuenta) { ?>
                        <a href="usuarios.php?toggle=<?php echo $usr['id']; ?>"
                           class="btn-sm <?php echo $usr['activo'] ? 'btn-oscuro' : 'btn-nuevo'; ?>"
                           title="<?php echo $usr['activo'] ? 'Desactivar' : 'Activar'; ?>"
                           onclick="return confirm('<?php echo $usr['activo'] ? '¿Desactivar este usuario?' : '¿Activar este usuario?'; ?>')">
                            <i class="fa-solid <?php echo $usr['activo'] ? 'fa-user-slash' : 'fa-user-check'; ?>"></i>
                        </a>
                        <a href="usuarios.php?borrar=<?php echo $usr['id']; ?>"
                           class="btn-borrar btn-sm"
                           title="Eliminar"
                           onclick="return confirm('¿Eliminar este usuario? Se eliminarán todos sus datos.')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINACION -->
    <?php if ($totalPaginas > 1) { ?>
    <div class="paginacion" style="margin-top:24px;">
        <?php for ($i = 1; $i <= $totalPaginas; $i++) { ?>
        <a href="usuarios.php?pagina=<?php echo $i; ?>&rol=<?php echo urlencode($filtroRol); ?>&buscar=<?php echo urlencode($filtroBuscar); ?>&activo=<?php echo urlencode($filtroActivo); ?>"
           class="paginacion-item <?php echo $i === $paginaActual ? 'activo' : ''; ?>">
            <?php echo $i; ?>
        </a>
        <?php } ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <p class="sin-resultados">No hay usuarios que coincidan con los filtros.</p>
    <?php } ?>

</div>
</div>

<!-- MODAL VER USUARIO -->
<div id="modalUsuario" class="modal-fondo" style="display:none;">
    <div class="modal-caja">
        <button class="modal-cerrar" id="btnCerrarUsuario">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <h3>Detalle del Usuario</h3>
        <div id="modalUsuarioContenido"></div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-ver-usuario').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const usr = JSON.parse(this.dataset.usuario);

        const roles = { visitante: 'Visitante', registrado: 'Registrado', admin: 'Administrador' };
        const estado = usr.activo == 1 ? 'Activo' : 'Inactivo';
        const fecha  = usr.registro !== '—'
            ? new Date(usr.registro).toLocaleDateString('es-ES')
            : '—';

        document.getElementById('modalUsuarioContenido').innerHTML =
            '<div class="modal-detalle-grid">' +
                '<div><strong>Nombre</strong><p>' + usr.nombre + '</p></div>' +
                '<div><strong>Email</strong><p>' + usr.email + '</p></div>' +
                '<div><strong>Teléfono</strong><p>' + (usr.telefono || '—') + '</p></div>' +
                '<div><strong>Ciudad</strong><p>' + (usr.ciudad || '—') + '</p></div>' +
                '<div><strong>Rol</strong><p>' + (roles[usr.rol] || usr.rol) + '</p></div>' +
                '<div><strong>Estado</strong><p>' + estado + '</p></div>' +
                '<div class="modal-detalle-full"><strong>Fecha de registro</strong><p>' + fecha + '</p></div>' +
            '</div>';

        document.getElementById('modalUsuario').style.display = 'flex';
    });
});

document.getElementById('btnCerrarUsuario').addEventListener('click', function() {
    document.getElementById('modalUsuario').style.display = 'none';
});

document.getElementById('modalUsuario').addEventListener('click', function(e) {
    if (e.target === this) { this.style.display = 'none'; }
});
</script>

<?php require_once '../templates/footer-admin.php'; ?>