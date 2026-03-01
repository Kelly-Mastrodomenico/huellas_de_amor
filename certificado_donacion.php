<?php
session_start();
require_once 'includes/conexion.php';
require_once 'includes/funciones.php';
require_once 'includes/fpdf/fpdf.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: usuario/panel.php');
    exit();
}

$idDonacion = (int) $_GET['id'];
$idUsuario  = (int) $_SESSION['usuario_id'];

try {
    $stmt = $conexion->prepare(
        "SELECT d.*, u.nombre, u.apellidos, u.email, u.dni
         FROM `donaciones` d
         JOIN `usuarios` u ON d.id_usuario = u.id
         WHERE d.id = :id AND d.id_usuario = :id_usuario
         LIMIT 1"
    );
    $stmt->bindParam(':id',         $idDonacion, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $idUsuario,  PDO::PARAM_INT);
    $stmt->execute();
    $datos = $stmt->fetch();

    if (!$datos) {
        header('Location: usuario/panel.php');
        exit();
    }

} catch (PDOException $e) {
    header('Location: usuario/panel.php');
    exit();
}

function utf8($texto) {
    return iconv('UTF-8', 'windows-1252', $texto ?? '');
}

$numCertificado = 'DON-' . str_pad($datos['id'], 6, '0', STR_PAD_LEFT) . '-' . date('Y', strtotime($datos['fecha']));
$nombreCompleto = $datos['nombre'] . ' ' . $datos['apellidos'];
$fechaDonacion  = date('d/m/Y', strtotime($datos['fecha']));
$horaDonacion   = date('H:i',   strtotime($datos['fecha']));

$desgravacion = $datos['monto'] <= 150
    ? number_format($datos['monto'] * 0.80, 2, ',', '.')
    : number_format(150 * 0.80 + ($datos['monto'] - 150) * 0.35, 2, ',', '.');

// --- GENERAR PDF ---
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 20);

// --- LOGO ---
$rutaLogo = dirname(__FILE__) . '/assets/img/Logotipo.png';
if (file_exists($rutaLogo)) {
    $pdf->Image($rutaLogo, 75, 10, 60);
    $pdf->Ln(38);
} else {
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(78, 205, 196);
    $pdf->Cell(0, 12, 'Huellas de Amor', 0, 1, 'C');
    $pdf->Ln(4);
}

// --- TITULO ---
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(0, 10, utf8('CERTIFICADO DE DONACIÓN'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6, utf8('Nº ' . $numCertificado), 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetDrawColor(78, 205, 196);
$pdf->SetLineWidth(0.8);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

// --- INTRO ---
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 7,
    utf8('La plataforma Huellas de Amor certifica que se ha recibido la siguiente donacion:'),
    0, 'C');
$pdf->Ln(6);

// --- CAJA DONANTE ---
$pdf->SetFillColor(247, 249, 252);
$pdf->SetDrawColor(78, 205, 196);
$pdf->SetLineWidth(0.4);
$pdf->Rect(15, $pdf->GetY(), 180, 36, 'DF');

$pdf->SetY($pdf->GetY() + 3);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(78, 205, 196);
$pdf->Cell(0, 7, utf8('DATOS DEL DONANTE'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(90, 7, utf8('Nombre: ' . $nombreCompleto), 0, 0, 'C');
$pdf->Cell(90, 7, utf8('Email: ' . $datos['email']), 0, 1, 'C');
if (!empty($datos['dni'])) {
    $pdf->Cell(90, 7, utf8('DNI/NIE: ' . $datos['dni']), 0, 0, 'C');
} else {
    $pdf->Cell(90, 7, '', 0, 0);
}
$pdf->Cell(90, 7, utf8('Fecha: ' . $fechaDonacion . ' a las ' . $horaDonacion), 0, 1, 'C');
$pdf->Ln(6);

// --- CAJA DONACION ---
$pdf->SetFillColor(255, 248, 248);
$pdf->SetDrawColor(255, 107, 107);
$pdf->Rect(15, $pdf->GetY(), 180, 36, 'DF');

$pdf->SetY($pdf->GetY() + 3);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 107, 107);
$pdf->Cell(0, 7, utf8('DETALLES DE LA DONACION'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(90, 7, utf8('Concepto: ' . $datos['concepto']), 0, 0, 'C');
$pdf->Cell(90, 7, utf8('Metodo de pago: ' . ucfirst($datos['metodo_pago'])), 0, 1, 'C');
$pdf->Cell(90, 7, utf8('Referencia: ' . $numCertificado), 0, 0, 'C');

// Monto destacado
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(255, 107, 107);
$pdf->Cell(90, 7, utf8(number_format($datos['monto'], 2, ',', '.') . ' EUR'), 0, 1, 'C');
$pdf->Ln(6);

// --- INFORMACION FISCAL ---
$pdf->SetFillColor(255, 248, 230);
$pdf->SetDrawColor(255, 152, 0);
$pdf->Rect(15, $pdf->GetY(), 180, 28, 'DF');

$pdf->SetY($pdf->GetY() + 3);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(230, 100, 0);
$pdf->Cell(0, 7, utf8('INFORMACION FISCAL'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 5,
    utf8('Esta donacion puede desgravarse en la declaracion de la renta (IRPF). ' .
    'Las donaciones hasta 150 EUR desgravan un 80% y el resto un 35%. ' .
    'Desgravacion estimada: ' . $desgravacion . ' EUR. Conserve este certificado como justificante.'),
    0, 'C');
$pdf->Ln(8);

// --- AGRADECIMIENTO ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(78, 205, 196);
$pdf->Cell(0, 8, utf8('Gracias por tu generosidad!'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 6,
    utf8('Tu donacion contribuye directamente al cuidado y atencion veterinaria ' .
    'de los animales rescatados en Huellas de Amor. Cada aportacion marca la diferencia.'),
    0, 'C');
$pdf->Ln(8);

// --- SELLO ---
$pdf->SetFillColor(78, 205, 196);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 10, utf8('DOCUMENTO OFICIAL — HUELLAS DE AMOR'), 1, 1, 'C', true);

// --- PIE ---
$pdf->Ln(4);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6,
    utf8('Huellas de Amor — Proyecto Intermodular 2DAW — Kelly Rodriguez Mastrodomenico'),
    0, 1, 'C');

$pdf->Output('D', 'certificado_donacion_' . $numCertificado . '.pdf');
exit();
?>