<?php
// ============================================================
// admin/mascotas.php — Listado de mascotas con filtros y paginacion
// Requisito DAWES: listado + filtrado + paginacion + exportar PDF
// ============================================================

$tituloPagina = 'Gestionar Mascotas — Admin';
require_once '../templates/header-admin.php';

// --- FILTROS ---
$filtroNombre  = isset($_GET['nombre'])  ? trim($_GET['nombre'])  : '';
$filtroEspecie = isset($_GET['especie']) ? trim($_GET['especie']) : '';
$filtroEstado  = isset($_GET['estado'])  ? trim($_GET['estado'])  : '';

// --- RESULTADOS POR PAGINA (igual que en la tienda del profesor) ---
$porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;

// --- PAGINACION ---
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($paginaActual < 1) { $paginaActual = 1; }
$inicio = ($paginaActual - 1) * $porPagina;

// --- CONSULTA CON FILTROS ---
try {
    $where  = "WHERE m.activo = 1";
    $params = [];

    if (!empty($filtroNombre)) {
        $where .= " AND m.nombre LIKE :nombre";
        $params[':nombre'] = '%' . $filtroNombre . '%';
    }

    if (!empty($filtroEspecie)) {
        $where .= " AND m.especie = :especie";
        $params[':especie'] = $filtroEspecie;
    }

    if (!empty($filtroEstado)) {
        $where .= " AND m.estado = :estado";
        $params[':estado'] = $filtroEstado;
    }

    // Contar total para la paginacion
    $sqlTotal = "SELECT COUNT(*) FROM `mascotas` m $where";
    $stmt     = $conexion->prepare($sqlTotal);
    foreach ($params as $clave => $valor) {
        $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
    }
    $stmt->execute();
    $totalMascotas = $stmt->fetchColumn();
    $totalPaginas  = ceil($totalMascotas / $porPagina);

    // Obtener mascotas — LIMIT :inicio, :por_pagina igual que el profesor
    $sql = "SELECT m.*, p.nombre AS nombre_protectora
            FROM `mascotas` m
            LEFT JOIN `protectoras` p ON m.id_protectora = p.id
            $where
            ORDER BY m.fecha_ingreso DESC
            LIMIT :inicio, :por_pagina";

    $stmt = $conexion->prepare($sql);
    foreach ($params as $clave => $valor) {
        $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
    }
    $stmt->bindValue(':inicio',     $inicio,    PDO::PARAM_INT);
    $stmt->bindValue(':por_pagina', $porPagina, PDO::PARAM_INT);
    $stmt->execute();
    $mascotas = $stmt->fetchAll();

} catch (PDOException $e) {
    $mascotas      = [];
    $totalMascotas = 0;
    $totalPaginas  = 1;
}

// URL con filtros para los enlaces de paginacion
$queryFiltros = http_build_query([
    'nombre'     => $filtroNombre,
    'especie'    => $filtroEspecie,
    'estado'     => $filtroEstado,
    'por_pagina' => $porPagina
]);
?>

<div class="contenedor-admin">
    <h1><i class="fa-solid fa-paw"></i> Gestionar Mascotas</h1>

    <!-- FILTROS -->
    <div class="barra-admin">
        <form method="get" class="form-filtro">
            <input type="text" name="nombre" placeholder="Buscar por nombre..."
                   value="<?php echo htmlspecialchars($filtroNombre); ?>">

            <select name="especie">
                <option value="">Todas las especies</option>
                <option value="perro" <?php echo ($filtroEspecie === 'perro') ? 'selected' : ''; ?>>Perro</option>
                <option value="gato"  <?php echo ($filtroEspecie === 'gato')  ? 'selected' : ''; ?>>Gato</option>
                <option value="otro"  <?php echo ($filtroEspecie === 'otro')  ? 'selected' : ''; ?>>Otro</option>
            </select>

            <select name="estado">
                <option value="">Todos los estados</option>
                <option value="disponible" <?php echo ($filtroEstado === 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                <option value="acogida"    <?php echo ($filtroEstado === 'acogida')    ? 'selected' : ''; ?>>En Acogida</option>
                <option value="adoptado"   <?php echo ($filtroEstado === 'adoptado')   ? 'selected' : ''; ?>>Adoptado</option>
            </select>

            <input type="hidden" name="por_pagina" value="<?php echo $porPagina; ?>">

            <button type="submit" class="btn-turquesa">
                <i class="fa-solid fa-magnifying-glass"></i> Filtrar
            </button>

            <?php if (!empty($filtroNombre) || !empty($filtroEspecie) || !empty($filtroEstado)) { ?>
                <a href="mascotas.php" class="btn-outline-coral">Limpiar</a>
            <?php } ?>
        </form>

        <div class="botones-admin">
            <a href="exportar_mascotas.php?<?php echo $queryFiltros; ?>" class="btn-oscuro">
                <i class="fa-solid fa-file-pdf"></i> Exportar PDF
            </a>
            <a href="mascota_nueva.php" class="btn-coral">
                <i class="fa-solid fa-plus"></i> Nueva Mascota
            </a>
        </div>
    </div>

    <!-- TOTAL Y SELECT POR PAGINA — igual que en la tienda del profesor -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:8px;">

        <p style="color:#888;">
            Mostrando <?php echo count($mascotas); ?> de <?php echo $totalMascotas; ?> mascotas
        </p>

        <form method="get" style="display:flex; align-items:center; gap:8px;">
            <input type="hidden" name="nombre"  value="<?php echo htmlspecialchars($filtroNombre); ?>">
            <input type="hidden" name="especie" value="<?php echo htmlspecialchars($filtroEspecie); ?>">
            <input type="hidden" name="estado"  value="<?php echo htmlspecialchars($filtroEstado); ?>">
            <label>Mostrar</label>
            <select name="por_pagina" onchange="this.form.submit()">
                <option value="5"  <?php echo ($porPagina == 5)  ? 'selected' : ''; ?>>5</option>
                <option value="10" <?php echo ($porPagina == 10) ? 'selected' : ''; ?>>10</option>
                <option value="20" <?php echo ($porPagina == 20) ? 'selected' : ''; ?>>20</option>
            </select>
            mascotas por pagina
        </form>

    </div>

    <!-- TABLA -->
    <?php if (!empty($mascotas)) { ?>
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Especie</th>
                    <th>Raza</th>
                    <th>Estado</th>
                    <th>Protectora</th>
                    <th>Fecha Ingreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mascotas as $mascota) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($mascota['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($mascota['especie']); ?></td>
                        <td><?php echo htmlspecialchars($mascota['raza']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $mascota['estado']; ?>">
                                <?php echo textoEstado($mascota['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $mascota['nombre_protectora']
                                ? htmlspecialchars($mascota['nombre_protectora'])
                                : '—'; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($mascota['fecha_ingreso'])); ?></td>
                        <td class="acciones">
                            <a href="mascota_editar.php?id=<?php echo $mascota['id']; ?>" class="btn-editar">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>
                            <a href="mascota_borrar.php?id=<?php echo $mascota['id']; ?>" class="btn-borrar">
                                <i class="fa-solid fa-trash"></i> Borrar
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- PAGINACION — igual que en la tienda del profesor -->
        <?php if ($totalPaginas > 1) { ?>
            <div class="paginacion">
                <?php for ($i = 1; $i <= $totalPaginas; $i++) { ?>
                    <?php if ($i === $paginaActual) { ?>
                        <span class="activa"><?php echo $i; ?></span>
                    <?php } else { ?>
                        <a href="mascotas.php?pagina=<?php echo $i; ?>&<?php echo $queryFiltros; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>

    <?php } else { ?>
        <p class="sin-resultados">
            <i class="fa-solid fa-paw"></i> No se encontraron mascotas.
        </p>
    <?php } ?>

</div>

<?php require_once '../templates/footer-admin.php'; ?>
