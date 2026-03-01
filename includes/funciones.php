<?php
// Comprueba si el usuario esta logueado
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

// Comprueba si el usuario logueado es admin
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}
// llamar al inicio de cada archivo de admin — redirige a login si no es admin
function protegerAdmin() {
    if (!estaLogueado() || !esAdmin()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

// Proteger paginas de usuario — redirige si no esta logueado para entrar solo a cosas de usuarios registrados
function protegerUsuario() {
    if (!estaLogueado()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

// Obtener la foto principal de una mascota
// Devuelve URL de la foto o una imagen por cualquiera
function obtenerFotoPrincipal($idMascota, $conexion) {
    try {
        $sql = "SELECT `ruta_foto` FROM `fotos_mascotas`
                WHERE `id_mascota` = :id AND `es_principal` = 1
                LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(':id', $idMascota, PDO::PARAM_INT);
        $stmt->execute();
        $foto = $stmt->fetch();

        if ($foto) {
            return $foto['ruta_foto'];
        }

        // imagen por defecto si no tiene foto
        return 'https://source.unsplash.com/featured/400x300?dog,cat' . $idMascota;

    } catch (PDOException $e) {
        return 'https://source.unsplash.com/featured/400x300?dog,cat' . $idMascota;
    }
}

// Formatear la edad de una mascota en texto legible
// Ejemplo: formatearEdad(2, 3) → "2 años y 3 meses"
//          formatearEdad(0, 5) → "5 meses"
//          formatearEdad(1, 0) → "1 año"
function formatearEdad($anios, $meses) {
    $anios = (int) $anios;
    $meses = (int) $meses;

    if ($anios > 0 && $meses > 0) {
        $textoAnios = $anios === 1 ? '1 año' : $anios . ' años';
        $textoMeses = $meses === 1 ? '1 mes' : $meses . ' meses';
        return $textoAnios . ' y ' . $textoMeses;
    }

    if ($anios > 0) {
        return $anios === 1 ? '1 año' : $anios . ' años';
    }

    if ($meses > 0) {
        return $meses === 1 ? '1 mes' : $meses . ' meses';
    }

    return 'Edad desconocida';
}

// Devuelve la clase CSS del badge segun el estado de la mascota
function claseBadgeEstado($estado) {
    if ($estado === 'disponible') { return 'badge-disponible'; }
    if ($estado === 'acogida')    { return 'badge-acogida'; }
    return 'badge-adoptado';
}

// Devuelve el estado
function textoEstado($estado) {
    if ($estado === 'disponible') { return 'Disponible'; }
    if ($estado === 'acogida')    { return 'En Acogida'; }
    return 'Adoptado';
}

// Contar mascotas por estado (para los contadores del index)
function contarMascotas($estado, $conexion) {
    try {
        $sql = "SELECT COUNT(*) FROM `mascotas`
                WHERE `estado` = :estado AND `activo` = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

// Subir una imagen al servidor
function subirImagen($archivo, $carpeta = 'uploads/') {
    if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

$tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/gif', 'image/webp'];
if (!in_array($archivo['type'], $tiposPermitidos)) {
    return null;
}

$extension   = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$nombreUnico = uniqid('foto_', true) . '.' . $extension;
$rutaDestino = BASE_PATH . '/' . $carpeta . $nombreUnico;

// Crear la carpeta si no existe
$directorioDestino = BASE_PATH . '/' . $carpeta;
if (!is_dir($directorioDestino)) {
    mkdir($directorioDestino, 0755, true);
}

if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    return $carpeta . $nombreUnico;
}

return null;
}

// Guardar un mensaje flash en sesion para mostrar despues
// Uso: mensajeFlash('Mascota guardada', 'exito');
//      mensajeFlash('Error al guardar', 'error');
function mensajeFlash($texto, $tipo = 'exito') {
    $_SESSION['mensaje']      = $texto;
    $_SESSION['mensaje_tipo'] = $tipo;
}?>