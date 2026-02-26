<?php
//  Responde al AJAX de adoptar.php
// Recibe los filtros y devuelve las mascotas en formato JSON

require_once '../includes/conexion.php';
require_once '../includes/funciones.php';

// Recoger filtros del GET
$especie = isset($_GET['especie']) ? trim($_GET['especie']) : '';
$sexo    = isset($_GET['sexo'])    ? trim($_GET['sexo'])    : '';
$tamanio = isset($_GET['tamanio']) ? trim($_GET['tamanio']) : '';
$nombre  = isset($_GET['nombre'])  ? trim($_GET['nombre'])  : '';

// Construir consulta con filtros
$where  = "WHERE m.activo = 1 AND m.estado = 'disponible'";
$params = [];

if (!empty($especie)) {
    $where .= " AND m.especie = :especie";
    $params[':especie'] = $especie;
}

if (!empty($sexo)) {
    $where .= " AND m.sexo = :sexo";
    $params[':sexo'] = $sexo;
}

if (!empty($tamanio)) {
    $where .= " AND m.tamanio = :tamanio";
    $params[':tamanio'] = $tamanio;
}

if (!empty($nombre)) {
    $where .= " AND m.nombre LIKE :nombre";
    $params[':nombre'] = '%' . $nombre . '%';
}

try {
    $sql = "SELECT m.*,
                   (SELECT f.ruta_foto FROM fotos_mascotas f
                    WHERE f.id_mascota = m.id AND f.es_principal = 1
                    LIMIT 1) AS foto_principal
            FROM mascotas m
            $where
            ORDER BY m.fecha_ingreso DESC
            LIMIT 20";

    $stmt = $conexion->prepare($sql);
    foreach ($params as $clave => $valor) {
        $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
    }
    $stmt->execute();
    $mascotas = $stmt->fetchAll();

    // Preparar array para devolver como JSON
    $resultado = [];
    foreach ($mascotas as $mascota) {
        $resultado[] = [
            'id'            => $mascota['id'],
            'nombre'        => $mascota['nombre'],
            'especie'       => $mascota['especie'],
            'raza'          => $mascota['raza'],
            'sexo'          => ucfirst($mascota['sexo']),
            'tamanio'       => ucfirst($mascota['tamanio']),
            'edad'          => formatearEdad($mascota['edad_anios'], $mascota['edad_meses']),
            'estado'        => $mascota['estado'],
            'estado_texto'  => textoEstado($mascota['estado']),
            'foto_principal' => $mascota['foto_principal']
        ];
    }

    // Devolver JSON 
    echo json_encode([
        'total'    => count($resultado),
        'mascotas' => $resultado
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'total'    => 0,
        'mascotas' => []
    ]);
}
?>