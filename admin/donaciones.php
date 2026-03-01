<?php
$tituloPagina = 'Gestión de Donaciones — Admin';
require_once '../templates/header-admin.php';
protegerAdmin();

// Filtros
$filtroMetodo  = trim($_GET['metodo']  ?? '');
$filtroDesde   = trim($_GET['desde']   ?? '');
$filtroHasta   = trim($_GET['hasta']   ?? '');
$filtroBuscar  = trim($_GET['buscar']  ?? '');
$exportarPDF   = isset($_GET['exportar_pdf']);

// Paginacion (solo para listado, no para PDF)
$porPagina    = 15;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset       = ($paginaActual - 1) * $porPagina;

// Construir WHERE
$donde  = "WHERE 1=1";
$params = [];

if (!empty($filtroMetodo)) {
    $donde .= " AND d.`metodo_pago` = :metodo";
    $params[':metodo'] = $filtroMetodo;
}
if (!empty($filtroDesde)) {
    $donde .= " AND DATE(d.`fecha`) >= :desde";
    $params[':desde'] = $filtroDesde;
}
if (!empty($filtroHasta)) {
    $donde .= " AND DATE(d.`fecha`) <= :hasta";
    $params[':hasta'] = $filtroHasta;
}
if (!empty($filtroBuscar)) {
    $donde .= " AND (u.`nombre` LIKE :buscar OR u.`email` LIKE :buscar2 OR d.`concepto` LIKE :buscar3)";
    $params[':buscar']  = '%' . $filtroBuscar . '%';
    $params[':buscar2'] = '%' . $filtroBuscar . '%';
    $params[':buscar3'] = '%' . $filtroBuscar . '%';
}

try {
    // Estadisticas globales
    $stmtStats = $conexion->prepare(
        "SELECT
            COUNT(*) AS total_donaciones,
            COALESCE(SUM(d.`monto`), 0) AS total_monto,
            COALESCE(AVG(d.`monto`), 0) AS media_monto,
            COUNT(DISTINCT d.`id_usuario`) AS total_donantes
         FROM `donaciones` d
         LEFT JOIN `usuarios` u ON d.id_usuario = u.id
         $donde"
    );
    foreach ($params as $k => $v) { $stmtStats->bindValue($k, $v); }
    $stmtStats->execute();
    $stats = $stmtStats->fetch();

    // Total para paginacion
    $stmtTotal = $conexion->prepare(
        "SELECT COUNT(*) FROM `donaciones` d
         LEFT JOIN `usuarios` u ON d.id_usuario = u.id
         $donde"
    );
    foreach ($params as $k => $v) { $stmtTotal->bindValue($k, $v); }
    $stmtTotal->execute();
    $totalDonaciones = $stmtTotal->fetchColumn();
    $totalPaginas    = ceil($totalDonaciones / $porPagina);

    // Query principal
    $limitOffset = $exportarPDF ? '' : "LIMIT :limite OFFSET :offset";
    $stmtDon = $conexion->prepare(
        "SELECT d.*, u.nombre AS nombre_usuario, u.apellidos, u.email
         FROM `donaciones` d
         LEFT JOIN `usuarios` u ON d.id_usuario = u.id
         $donde
         ORDER BY d.`fecha` DESC
         $limitOffset"
    );
    foreach ($params as $k => $v) { $stmtDon->bindValue($k, $v); }
    if (!$exportarPDF) {
        $stmtDon->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmtDon->bindValue(':offset', $offset,    PDO::PARAM_INT);
    }
    $stmtDon->execute();
    $donaciones = $stmtDon->fetchAll();

} catch (PDOException $e) {
    $donaciones = [];
    $stats      = ['total_donaciones' => 0, 'total_monto' => 0, 'media_monto' => 0, 'total_donantes' => 0];
    $totalDonaciones = 0;
    $totalPaginas    = 1;
}


// EXPORTAR PDF con FPDF
if ($exportarPDF) {
    require_once '../includes/fpdf/fpdf.php';

    class PDFDonaciones extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 16);
            $this->SetTextColor(255, 107, 107);
            $this->Cell(0, 10, 'Huellas de Amor', 0, 1, 'C');
            $this->SetFont('Arial', 'B', 12);
            $this->SetTextColor(44, 62, 80);
            $this->Cell(0, 8, 'Reporte de Donaciones', 0, 1, 'C');
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(150, 150, 150);
            $this->Cell(0, 6, 'Generado el ' . date('d/m/Y H:i'), 0, 1, 'C');
            $this->Ln(4);
            $this->SetDrawColor(255, 107, 107);
            $this->SetLineWidth(0.5);
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            $this->Ln(4);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(150, 150, 150);
            $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' de {nb}', 0, 0, 'C');
        }
    }

    $pdf = new PDFDonaciones('L', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 20);

    // Resumen estadisticas
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(247, 249, 252);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->Cell(65, 10, 'Total donaciones: ' . $stats['total_donaciones'], 1, 0, 'C', true);
    $pdf->Cell(65, 10, 'Total recaudado: ' . number_format($stats['total_monto'], 2, ',', '.') . ' EUR', 1, 0, 'C', true);
    $pdf->Cell(65, 10, 'Donacion media: ' . number_format($stats['media_monto'], 2, ',', '.') . ' EUR', 1, 0, 'C', true);
    $pdf->Cell(72, 10, 'Donantes unicos: ' . $stats['total_donantes'], 1, 1, 'C', true);
    $pdf->Ln(4);

    // Filtros aplicados
    if (!empty($filtroMetodo) || !empty($filtroDesde) || !empty($filtroHasta)) {
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(100, 100, 100);
        $filtroTexto = 'Filtros aplicados: ';
        if (!empty($filtroMetodo))  { $filtroTexto .= 'Metodo: ' . $filtroMetodo . ' | '; }
        if (!empty($filtroDesde))   { $filtroTexto .= 'Desde: ' . $filtroDesde . ' | '; }
        if (!empty($filtroHasta))   { $filtroTexto .= 'Hasta: ' . $filtroHasta; }
        $pdf->Cell(0, 6, rtrim($filtroTexto, ' | '), 0, 1);
        $pdf->Ln(2);
    }

    // Cabecera tabla
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(255, 107, 107);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(255, 107, 107);
    $pdf->Cell(12,  8, '#',         1, 0, 'C', true);
    $pdf->Cell(55,  8, 'Donante',   1, 0, 'C', true);
    $pdf->Cell(75,  8, 'Concepto',  1, 0, 'C', true);
    $pdf->Cell(25,  8, 'Monto',     1, 0, 'C', true);
    $pdf->Cell(35,  8, 'Metodo',    1, 0, 'C', true);
    $pdf->Cell(65,  8, 'Fecha',     1, 1, 'C', true);

    // Filas
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetDrawColor(220, 220, 220);
    $fila = 0;
    foreach ($donaciones as $don) {
        $fila++;
        $relleno = ($fila % 2 === 0);
        $pdf->SetFillColor(247, 249, 252);
        $pdf->SetTextColor(44, 62, 80);

        $nombreDon = $don['nombre_usuario']
            ? $don['nombre_usuario'] . ' ' . ($don['apellidos'] ?? '')
            : 'Anonimo';

        $pdf->Cell(12,  7, $fila,                                    1, 0, 'C', $relleno);
        $pdf->Cell(55,  7, iconv('UTF-8', 'CP1252//TRANSLIT', mb_substr($nombreDon, 0, 28)), 1, 0, 'L', $relleno);
        $pdf->Cell(75,  7, iconv('UTF-8', 'CP1252//TRANSLIT', mb_substr($don['concepto'], 0, 40)), 1, 0, 'L', $relleno);
        $pdf->Cell(25,  7, number_format($don['monto'], 2, ',', '.') . ' EUR', 1, 0, 'R', $relleno);
        $pdf->Cell(35,  7, iconv('UTF-8', 'CP1252//TRANSLIT', ucfirst($don['metodo_pago'])), 1, 0, 'C', $relleno);
        $pdf->Cell(65,  7, date('d/m/Y H:i', strtotime($don['fecha'])), 1, 1, 'C', $relleno);
    }

    // Total final
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(255, 107, 107);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(167, 8, 'TOTAL RECAUDADO', 1, 0, 'R', true);
    $pdf->Cell(100, 8, number_format($stats['total_monto'], 2, ',', '.') . ' EUR', 1, 1, 'C', true);

    $pdf->Output('D', 'donaciones_' . date('Y-m-d') . '.pdf');
    exit();
}
?>

<div class="contenedor" style="padding-top:24px; padding-bottom:40px;">
<div class="contenedor-admin">

    <div class="admin-cabecera">
        <h1><i class="fa-solid fa-hand-holding-heart"></i> Donaciones</h1>
        <a href="donaciones.php?<?php echo http_build_query(array_filter([
            'exportar_pdf' => '1',
            'metodo'  => $filtroMetodo,
            'desde'   => $filtroDesde,
            'hasta'   => $filtroHasta,
            'buscar'  => $filtroBuscar,
        ])); ?>" class="btn-oscuro btn-sm">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
    </div>

    <!-- ESTADISTICAS -->
<div class="dashboard-stats">
    <div class="stat-card stat-coral">
        <i class="fa-solid fa-hand-holding-heart"></i>
        <span class="stat-num"><?php echo $stats['total_donaciones']; ?></span>
        <span class="stat-label">Total donaciones</span>
    </div>
    <div class="stat-card stat-turquesa">
        <i class="fa-solid fa-euro-sign"></i>
        <span class="stat-num"><?php echo number_format($stats['total_monto'], 2, ',', '.'); ?>€</span>
        <span class="stat-label">Total recaudado</span>
    </div>
    <div class="stat-card stat-verde">
        <i class="fa-solid fa-chart-line"></i>
        <span class="stat-num"><?php echo number_format($stats['media_monto'], 2, ',', '.'); ?>€</span>
        <span class="stat-label">Donación media</span>
    </div>
    <div class="stat-card stat-oscuro">
        <i class="fa-solid fa-users"></i>
        <span class="stat-num"><?php echo $stats['total_donantes']; ?></span>
        <span class="stat-label">Donantes únicos</span>
    </div>
</div>

    <!-- FILTROS -->
    <div class="barra-admin" style="flex-wrap:wrap; gap:12px;">
        <form method="get" action="donaciones.php" class="form-filtro" style="flex-wrap:wrap; gap:8px;">
            <input type="text" name="buscar"
                   placeholder="Buscar donante o concepto..."
                   value="<?php echo htmlspecialchars($filtroBuscar); ?>">
            <select name="metodo">
                <option value="">Todos los métodos</option>
                <option value="tarjeta" <?php echo $filtroMetodo === 'tarjeta' ? 'selected' : ''; ?>>Tarjeta</option>
                <option value="paypal"  <?php echo $filtroMetodo === 'paypal'  ? 'selected' : ''; ?>>PayPal</option>
                <option value="bizum"   <?php echo $filtroMetodo === 'bizum'   ? 'selected' : ''; ?>>Bizum</option>
            </select>
            <input type="date" name="desde" value="<?php echo htmlspecialchars($filtroDesde); ?>"
                   title="Desde">
            <input type="date" name="hasta" value="<?php echo htmlspecialchars($filtroHasta); ?>"
                   title="Hasta">
            <button type="submit" class="btn-turquesa btn-sm">
                <i class="fa-solid fa-magnifying-glass"></i> Filtrar
            </button>
            <?php if (!empty($filtroMetodo) || !empty($filtroDesde) || !empty($filtroHasta) || !empty($filtroBuscar)) { ?>
            <a href="donaciones.php" class="btn-outline-coral btn-sm">Limpiar</a>
            <?php } ?>
        </form>
    </div>

    <p style="color:#888; margin-bottom:16px;">
        <?php echo $totalDonaciones; ?> donación<?php echo $totalDonaciones !== 1 ? 'es' : ''; ?> encontrada<?php echo $totalDonaciones !== 1 ? 's' : ''; ?>
    </p>

<!-- TABLA -->
<?php if (!empty($donaciones)) { ?>
<div class="tabla-wrapper">
<table class="tabla-admin">
    <thead>
        <tr>
            <th>#</th>
            <th>Donante</th>
            <th>Concepto</th>
            <th>Monto</th>
            <th>Método</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($donaciones as $i => $don) { ?>
        <tr>
            <td><?php echo $offset + $i + 1; ?></td>
            <td>
                <?php if ($don['nombre_usuario']) { ?>
                    <strong><?php echo htmlspecialchars($don['nombre_usuario'] . ' ' . $don['apellidos']); ?></strong>
                    <br><small style="color:#aaa;"><?php echo htmlspecialchars($don['email']); ?></small>
                <?php } else { ?>
                    <span style="color:#aaa;"><i class="fa-solid fa-user-secret"></i> Anónimo</span>
                <?php } ?>
            </td>
            <td><?php echo htmlspecialchars($don['concepto']); ?></td>
            <td>
                <strong style="color:#2C3E50; font-size:1rem;">
                    <?php echo number_format($don['monto'], 2, ',', '.'); ?>€
                </strong>
            </td>
            <td>
                <?php
                $iconoMetodo = [
                    'tarjeta' => 'fa-credit-card',
                    'paypal'  => 'fa-paypal',
                    'bizum'   => 'fa-mobile-screen',
                ];
                $icono = $iconoMetodo[$don['metodo_pago']] ?? 'fa-money-bill';
                ?>
                <span class="badge badge-disponible" style="font-size:0.7rem;">
                    <i class="fa-<?php echo $don['metodo_pago'] === 'paypal' ? 'brands' : 'solid'; ?> <?php echo $icono; ?>"></i>
                    <?php echo ucfirst($don['metodo_pago']); ?>
                </span>
            </td>
            <td><?php echo date('d/m/Y H:i', strtotime($don['fecha'])); ?></td>
        </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right; font-weight:700; padding:12px;">
                Total filtrado:
            </td>
            <td colspan="3" style="font-weight:800; font-size:1.1rem; color:#FF6B6B; padding:12px;">
                <?php echo number_format($stats['total_monto'], 2, ',', '.'); ?>€
            </td>
        </tr>
    </tfoot>
</table>
</div>

    <!-- Paginacion -->
    <?php if ($totalPaginas > 1) { ?>
    <div class="paginacion" style="margin-top:24px;">
        <?php for ($i = 1; $i <= $totalPaginas; $i++) { ?>
        <a href="donaciones.php?pagina=<?php echo $i; ?>&metodo=<?php echo urlencode($filtroMetodo); ?>&desde=<?php echo urlencode($filtroDesde); ?>&hasta=<?php echo urlencode($filtroHasta); ?>&buscar=<?php echo urlencode($filtroBuscar); ?>"
           class="paginacion-item <?php echo $i === $paginaActual ? 'activo' : ''; ?>">
            <?php echo $i; ?>
        </a>
        <?php } ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <p class="sin-resultados">No hay donaciones con los filtros seleccionados.</p>
    <?php } ?>

</div>
</div>

<?php require_once '../templates/footer-admin.php'; ?>