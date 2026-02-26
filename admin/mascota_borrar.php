<?php
//DELETE/UPDATE con PDO

$tituloPagina = 'Borrar Mascota — Admin';
require_once '../templates/header-admin.php';

// Comprobar que viene un id por GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
header('Location: mascotas.php');
exit();
}

$idMascota = (int)$_GET['id'];

// Obtener los datos de la mascota para mostrar en la confirmacion
try {
$sql  = "SELECT * FROM `mascotas` WHERE `id` = :id AND `activo` = 1 LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bindValue(':id', $idMascota, PDO::PARAM_INT);
$stmt->execute();
$mascota = $stmt->fetch();

if (!$mascota) {
    header('Location: mascotas.php');
    exit();
}

} catch (PDOException $e) {
header('Location: mascotas.php');
exit();
}

// Confirmar el borrado cuando se pulsa el boton
if (isset($_POST['confirmar'])) {

try {
    // ponemos activo = 0 en lugar de borrar el registro
    // Asi no perdemos el historial de la mascota
    $sql  = "UPDATE `mascotas` SET `activo` = 0 WHERE `id` = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindValue(':id', $idMascota, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['mensaje']      = 'Mascota eliminada correctamente.';
    $_SESSION['mensaje_tipo'] = 'exito';
    header('Location: mascotas.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['mensaje']      = 'Error al eliminar la mascota.';
    $_SESSION['mensaje_tipo'] = 'error';
    header('Location: mascotas.php');
    exit();
}
}
?>

<div class="contenedor-admin" style="max-width:500px;">
<h1><i class="fa-solid fa-trash"></i> Borrar Mascota</h1>

<div class="alerta alerta-error">
    ¿Seguro que quieres eliminar a <strong><?php echo htmlspecialchars($mascota['nombre']); ?></strong>?
    Esta accion no se puede deshacer.
</div>

<table class="tabla-admin" style="margin-bottom:24px;">
    <tr>
        <th>Nombre</th>
        <td><?php echo htmlspecialchars($mascota['nombre']); ?></td>
    </tr>
    <tr>
        <th>Especie</th>
        <td><?php echo htmlspecialchars($mascota['especie']); ?></td>
    </tr>
    <tr>
        <th>Raza</th>
        <td><?php echo htmlspecialchars($mascota['raza']); ?></td>
    </tr>
    <tr>
        <th>Estado</th>
        <td>
            <span class="badge badge-<?php echo $mascota['estado']; ?>">
                <?php echo textoEstado($mascota['estado']); ?>
            </span>
        </td>
    </tr>
    <tr>
        <th>Fecha Ingreso</th>
        <td><?php echo date('d/m/Y', strtotime($mascota['fecha_ingreso'])); ?></td>
    </tr>
</table>

<form method="post">
    <div class="form-botones">
        <a href="mascotas.php" class="btn-outline-coral">Cancelar</a>
        <button type="submit" name="confirmar" class="btn-borrar">
            <i class="fa-solid fa-trash"></i> Si, eliminar
        </button>
    </div>
</form>

</div>

<?php require_once '../templates/footer-admin.php'; ?>