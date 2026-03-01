<?php
// Exportar listado de mascotas a PDF con FPDF

session_start();

// Solo admin puede exportar
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/fpdf/fpdf.php';
// Convertir UTF-8 a Latin-1 para que FPDF muestre bien los acentos
function utf8($texto) {
    return iconv('UTF-8', 'windows-1252', $texto);
}

// --- FILTROS (los mismos que en mascotas.php) ---
$filtroNombre  = isset($_GET['nombre'])  ? trim($_GET['nombre'])  : '';
$filtroEspecie = isset($_GET['especie']) ? trim($_GET['especie']) : '';
$filtroEstado  = isset($_GET['estado'])  ? trim($_GET['estado'])  : '';

// --- CONSULTA ---
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

    $sql  = "SELECT m.*, p.nombre AS nombre_protectora
             FROM `mascotas` m
             LEFT JOIN `protectoras` p ON m.id_protectora = p.id
             $where
             ORDER BY m.fecha_ingreso DESC";

    $stmt = $conexion->prepare($sql);
    foreach ($params as $clave => $valor) {
        $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
    }
    $stmt->execute();
    $mascotas = $stmt->fetchAll();

} catch (PDOException $e) {
    $mascotas = [];
}

// --- GENERAR PDF CON FPDF ---
$pdf = new FPDF('L', 'mm', 'A4'); // L = horizontal, A4
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// --- LOGO EN EL PDF ---
$rutaLogo = dirname(__DIR__) . '/assets/img/Logotipo.png';
if (file_exists($rutaLogo)) {
    $pdf->Image($rutaLogo, 10, 5, 60); // x=10 lo pone a la izquierda
$pdf->Ln(28);
} else {
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(255, 107, 107);
    $pdf->Cell(0, 12, 'Huellas de Amor', 0, 1, 'C');
    $pdf->Ln(4);
}

$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(44, 62, 80); // color oscuro
$pdf->Cell(0, 8, 'Listado de Mascotas', 0, 1, 'C');

// Fecha de exportacion
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6, 'Generado el ' . date('d/m/Y H:i'), 0, 1, 'C');

// Filtros aplicados
if (!empty($filtroNombre) || !empty($filtroEspecie) || !empty($filtroEstado)) {
    $textoFiltros = 'Filtros: ';
    if (!empty($filtroNombre))  { $textoFiltros .= 'Nombre: ' . $filtroNombre . '  '; }
    if (!empty($filtroEspecie)) { $textoFiltros .= 'Especie: ' . $filtroEspecie . '  '; }
    if (!empty($filtroEstado))  { $textoFiltros .= 'Estado: ' . $filtroEstado; }
    $pdf->Cell(0, 6, $textoFiltros, 0, 1, 'C');
}

$pdf->Ln(4);

// --- CABECERA DE LA TABLA ---
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(44, 62, 80);   // fondo oscuro
$pdf->SetTextColor(255, 255, 255); // texto blanco
$pdf->SetDrawColor(200, 200, 200);

$pdf->Cell(40, 8, 'Nombre',        1, 0, 'C', true);
$pdf->Cell(25, 8, 'Especie',       1, 0, 'C', true);
$pdf->Cell(40, 8, 'Raza',          1, 0, 'C', true);
$pdf->Cell(20, 8, 'Sexo',          1, 0, 'C', true);
$pdf->Cell(30, 8, 'Edad',          1, 0, 'C', true);
$pdf->Cell(30, 8, 'Estado',        1, 0, 'C', true);
$pdf->Cell(50, 8, 'Protectora',    1, 0, 'C', true);
$pdf->Cell(32, 8, 'Fecha Ingreso', 1, 1, 'C', true);

// --- FILAS ---
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(44, 62, 80);

$filaClara  = [247, 249, 252]; // gris claro
$filaBlanca = [255, 255, 255]; // blanco
$contador   = 0;

foreach ($mascotas as $mascota) {
    // Alternar color de fila
    if ($contador % 2 === 0) {
        $pdf->SetFillColor($filaBlanca[0], $filaBlanca[1], $filaBlanca[2]);
    } else {
        $pdf->SetFillColor($filaClara[0], $filaClara[1], $filaClara[2]);
    }

    $edad = formatearEdad($mascota['edad_anios'], $mascota['edad_meses']);
    $protectora = $mascota['nombre_protectora'] ? $mascota['nombre_protectora'] : '—';
    $fecha = date('d/m/Y', strtotime($mascota['fecha_ingreso']));

    $pdf->Cell(40, 7, utf8($mascota['nombre']),           1, 0, 'L', true);
    $pdf->Cell(25, 7, utf8(ucfirst($mascota['especie'])), 1, 0, 'C', true);
    $pdf->Cell(40, 7, utf8($mascota['raza']),              1, 0, 'L', true);
    $pdf->Cell(20, 7, utf8(ucfirst($mascota['sexo'])),    1, 0, 'C', true);
    $pdf->Cell(30, 7, utf8($edad),                         1, 0, 'C', true);
    $pdf->Cell(30, 7, utf8(textoEstado($mascota['estado'])), 1, 0, 'C', true);
    $pdf->Cell(50, 7, utf8($protectora),                   1, 0, 'L', true);
    $pdf->Cell(32, 7, utf8($fecha),                        1, 1, 'C', true);
    $contador++;
}

// --- TOTAL ---
$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(0, 7, utf8('Total: ' . count($mascotas) . ' mascotas'), 0, 1, 'R');

// --- PIE DE PAGINA ---
$pdf->Ln(4);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6, utf8('Huellas de Amor — Proyecto Intermodular 2DAW — Kelly Rodríguez Mastrodomenico'), 0, 1, 'C');

// --- ENVIAR AL NAVEGADOR ---
$pdf->Output('I', 'mascotas_' . date('Y-m-d') . '.pdf');
exit();
?>