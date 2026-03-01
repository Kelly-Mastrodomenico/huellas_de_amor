<?php
session_start();
require_once 'includes/conexion.php';
require_once 'includes/funciones.php';
require_once 'includes/fpdf/fpdf.php';

// Solo usuarios logueados
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
    return iconv('UTF-8', 'windows-1252//TRANSLIT', $texto ?? '');
}

// -----------------------------------------------
// CLASE PDF
// -----------------------------------------------
class CertificadoDonacion extends FPDF {

    function Header() {
        $this->SetFillColor(78, 205, 196);
        $this->Rect(0, 0, 210, 40, 'F');

        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(255, 255, 255);
        $this->SetY(8);
        $this->Cell(0, 12, utf8('Huellas de Amor'), 0, 1, 'C');

        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(220, 248, 246);
        $this->Cell(0, 8, utf8('Plataforma de Adopción y Apadrinamiento de Mascotas'), 0, 1, 'C');
        $this->Ln(8);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFillColor(44, 62, 80);
        $this->Rect(0, $this->GetY(), 210, 20, 'F');
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(180, 180, 180);
        $this->Cell(0, 10,
            utf8('Huellas de Amor — Certificado de donación — Página ') . $this->PageNo(),
            0, 0, 'C');
    }
}

$pdf = new CertificadoDonacion('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);

$numCertificado = 'DON-' . str_pad($datos['id'], 6, '0', STR_PAD_LEFT) . '-' . date('Y', strtotime($datos['fecha']));
$nombreCompleto = $datos['nombre'] . ' ' . $datos['apellidos'];
$fechaDonacion  = date('d/m/Y', strtotime($datos['fecha']));
$horaDonacion   = date('H:i', strtotime($datos['fecha']));

// -----------------------------------------------
// TÍTULO
// -----------------------------------------------
$pdf->SetY(50);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(0, 12, utf8('CERTIFICADO DE DONACIÓN'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6, utf8('Nº ' . $numCertificado), 0, 1, 'C');
$pdf->Ln(4);

$pdf->SetDrawColor(78, 205, 196);
$pdf->SetLineWidth(1);
$pdf->Line(30, $pdf->GetY(), 180, $pdf->GetY());
$pdf->Ln(8);

// -----------------------------------------------
// TEXTO INTRO
// -----------------------------------------------
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 7,
    utf8('La plataforma Huellas de Amor certifica que se ha recibido una donación con los siguientes datos:'),
    0, 'C');
$pdf->Ln(6);

// -----------------------------------------------
// CAJA DONANTE
// -----------------------------------------------
$pdf->SetFillColor(247, 249, 252);
$pdf->SetDrawColor(78, 205, 196);
$pdf->SetLineWidth(0.5);

$yActual = $pdf->GetY();
$pdf->Rect(15, $yActual, 180, 36, 'DF');

$pdf->SetY($yActual + 4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(78, 205, 196);
$pdf->Cell(0, 7, utf8('DATOS DEL DONANTE'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(95, 7, utf8('Nombre: ' . $nombreCompleto), 0, 0, 'C');
$pdf->Cell(95, 7, utf8('Email: ' . $datos['email']), 0, 1, 'C');
if (!empty($datos['dni'])) {
    $pdf->Cell(95, 7, utf8('DNI/NIE: ' . $datos['dni']), 0, 0, 'C');
} else {
    $pdf->Cell(95, 7, '', 0, 0, 'C');
}
$pdf->Cell(95, 7, utf8('Fecha: ' . $fechaDonacion . ' a las ' . $horaDonacion), 0, 1, 'C');
$pdf->Ln(6);

// -----------------------------------------------
// CAJA DONACIÓN
// -----------------------------------------------
$yActual = $pdf->GetY();
$pdf->SetFillColor(255, 248, 248);
$pdf->SetDrawColor(255, 107, 107);
$pdf->Rect(15, $yActual, 180, 45, 'DF');

$pdf->SetY($yActual + 4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 107, 107);
$pdf->Cell(0, 7, utf8('DETALLES DE LA DONACIÓN'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(95, 7, utf8('Concepto: ' . $datos['concepto']), 0, 0, 'C');
$pdf->Cell(95, 7, utf8('Método de pago: ' . ucfirst($datos['metodo_pago'])), 0, 1, 'C');
$pdf->Cell(95, 7, utf8('Referencia: ' . $numCertificado), 0, 0, 'C');
$pdf->Cell(95, 7, '', 0, 1);

// Monto destacado
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(255, 107, 107);
$pdf->Cell(0, 12, utf8(number_format($datos['monto'], 2, ',', '.') . ' EUR'), 0, 1, 'C');
$pdf->Ln(6);

// -----------------------------------------------
// VENTAJA FISCAL
// -----------------------------------------------
$pdf->SetFillColor(255, 248, 230);
$pdf->SetDrawColor(255, 152, 0);
$pdf->SetLineWidth(0.3);
$yActual = $pdf->GetY();
$pdf->Rect(15, $yActual, 180, 28, 'DF');

$pdf->SetY($yActual + 3);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(230, 100, 0);
$pdf->Cell(0, 7, utf8('INFORMACIÓN FISCAL'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(80, 80, 80);

$desgravacion = $datos['monto'] <= 150
    ? number_format($datos['monto'] * 0.80, 2, ',', '.')
    : number_format(150 * 0.80 + ($datos['monto'] - 150) * 0.35, 2, ',', '.');

$pdf->MultiCell(0, 5,
    utf8('Esta donación puede desgravarse en la declaración de la renta (IRPF). ' .
    'Las donaciones hasta 150€ desgravan un 80% y el resto un 35%. ' .
    'Desgravación estimada: ' . $desgravacion . ' EUR. Conserve este certificado como justificante.'),
    0, 'C');
$pdf->Ln(8);

// -----------------------------------------------
// AGRADECIMIENTO
// -----------------------------------------------
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(78, 205, 196);
$pdf->Cell(0, 8, utf8('¡Gracias por tu generosidad!'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 6,
    utf8('Tu donación contribuye directamente al cuidado, alimentación y atención veterinaria ' .
    'de los animales rescatados en Huellas de Amor. Cada aportación marca la diferencia.'),
    0, 'C');
$pdf->Ln(8);

// -----------------------------------------------
// SELLO OFICIAL
// -----------------------------------------------
$pdf->SetFillColor(78, 205, 196);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 10,
    utf8('✓  DOCUMENTO OFICIAL — HUELLAS DE AMOR  ✓'),
    1, 1, 'C', true);

$pdf->Output('D', 'certificado_donacion_' . $numCertificado . '.pdf');
exit();
?>