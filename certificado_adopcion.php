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

$idSolicitud = (int) $_GET['id'];
$idUsuario   = (int) $_SESSION['usuario_id'];

// Obtener datos de la solicitud
try {
    $stmt = $conexion->prepare(
        "SELECT s.*,
                m.nombre AS nombre_mascota, m.especie, m.raza, m.sexo,
                m.edad_anios, m.edad_meses,
                u.nombre AS nombre_usuario, u.apellidos, u.email, u.dni,
                p.nombre AS nombre_protectora, p.ciudad AS ciudad_protectora
         FROM `solicitudes_adopcion` s
         JOIN `mascotas` m ON s.id_mascota = m.id
         JOIN `usuarios` u ON s.id_usuario = u.id
         LEFT JOIN `protectoras` p ON m.id_protectora = p.id
         WHERE s.id = :id AND s.id_usuario = :id_usuario AND s.estado = 'aprobada'
         LIMIT 1"
    );
    $stmt->bindParam(':id',         $idSolicitud, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $idUsuario,   PDO::PARAM_INT);
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

// Función para convertir UTF-8 a Latin-1
function utf8($texto) {
    return iconv('UTF-8', 'windows-1252//TRANSLIT', $texto ?? '');
}

// -----------------------------------------------
// GENERAR PDF
// -----------------------------------------------
class CertificadoAdopcion extends FPDF {

    function Header() {
        // Fondo cabecera coral
        $this->SetFillColor(255, 107, 107);
        $this->Rect(0, 0, 210, 40, 'F');

        // Logo texto
        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(255, 255, 255);
        $this->SetY(8);
        $this->Cell(0, 12, utf8('Huellas de Amor'), 0, 1, 'C');

        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(255, 240, 240);
        $this->Cell(0, 8, utf8('Plataforma de Adopción y Apadrinamiento de Mascotas'), 0, 1, 'C');
        $this->Ln(8);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFillColor(44, 62, 80);
        $this->Rect(0, $this->GetY(), 210, 20, 'F');
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(180, 180, 180);
        $this->Cell(0, 10, utf8('Huellas de Amor — Documento oficial de adopción — Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new CertificadoAdopcion('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);

// Número de certificado
$numCertificado = 'HA-' . str_pad($datos['id'], 6, '0', STR_PAD_LEFT) . '-' . date('Y');

// -----------------------------------------------
// TÍTULO CERTIFICADO
// -----------------------------------------------
$pdf->SetY(50);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(0, 12, utf8('CERTIFICADO DE ADOPCIÓN'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6, utf8('Nº ' . $numCertificado), 0, 1, 'C');
$pdf->Ln(4);

// Línea decorativa
$pdf->SetDrawColor(255, 107, 107);
$pdf->SetLineWidth(1);
$pdf->Line(30, $pdf->GetY(), 180, $pdf->GetY());
$pdf->Ln(8);

// -----------------------------------------------
// TEXTO INTRO
// -----------------------------------------------
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(80, 80, 80);
$nombreCompleto = $datos['nombre_usuario'] . ' ' . $datos['apellidos'];
$fechaAdopcion  = date('d/m/Y', strtotime($datos['fecha_solicitud']));
$edadMascota    = formatearEdad($datos['edad_anios'], $datos['edad_meses']);

$pdf->MultiCell(0, 7,
    utf8('Por medio del presente documento, la plataforma Huellas de Amor certifica que:'),
    0, 'C');
$pdf->Ln(4);

// -----------------------------------------------
// CAJA ADOPTANTE
// -----------------------------------------------
$pdf->SetFillColor(247, 249, 252);
$pdf->SetDrawColor(78, 205, 196);
$pdf->SetLineWidth(0.5);
$pdf->RoundedRect(15, $pdf->GetY(), 180, 38, 3, 'DF');

$pdf->SetY($pdf->GetY() + 4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(78, 205, 196);
$pdf->Cell(0, 7, utf8('DATOS DEL ADOPTANTE'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(95, 7, utf8('Nombre: ' . $nombreCompleto), 0, 0, 'C');
$pdf->Cell(95, 7, utf8('Email: ' . $datos['email']), 0, 1, 'C');
$pdf->Cell(95, 7, utf8('Fecha de adopción: ' . $fechaAdopcion), 0, 0, 'C');
if (!empty($datos['dni'])) {
    $pdf->Cell(95, 7, utf8('DNI/NIE: ' . $datos['dni']), 0, 1, 'C');
} else {
    $pdf->Cell(95, 7, '', 0, 1, 'C');
}
$pdf->Ln(6);

// -----------------------------------------------
// CAJA MASCOTA
// -----------------------------------------------
$pdf->SetFillColor(255, 248, 248);
$pdf->SetDrawColor(255, 107, 107);
$pdf->RoundedRect(15, $pdf->GetY(), 180, 45, 3, 'DF');

$pdf->SetY($pdf->GetY() + 4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 107, 107);
$pdf->Cell(0, 7, utf8('DATOS DE LA MASCOTA ADOPTADA'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(95, 7, utf8('Nombre: ' . $datos['nombre_mascota']), 0, 0, 'C');
$pdf->Cell(95, 7, utf8('Especie: ' . ucfirst($datos['especie'])), 0, 1, 'C');
$pdf->Cell(95, 7, utf8('Raza: ' . ($datos['raza'] ?? 'Mestizo')), 0, 0, 'C');
$pdf->Cell(95, 7, utf8('Sexo: ' . ucfirst($datos['sexo'])), 0, 1, 'C');
$pdf->Cell(95, 7, utf8('Edad: ' . $edadMascota), 0, 0, 'C');
if ($datos['nombre_protectora']) {
    $pdf->Cell(95, 7, utf8('Protectora: ' . $datos['nombre_protectora']), 0, 1, 'C');
} else {
    $pdf->Ln(7);
}
$pdf->Ln(8);

// -----------------------------------------------
// TEXTO COMPROMISO
// -----------------------------------------------
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(0, 8, utf8('Compromiso de adopción responsable'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 6,
    utf8('El adoptante se compromete a proporcionar a ' . $datos['nombre_mascota'] .
    ' un hogar seguro, alimentación adecuada, atención veterinaria regular y todo el amor y cuidado que merece. ' .
    'La adopción es un acto de responsabilidad y compromiso de por vida con el animal.'),
    0, 'J');
$pdf->Ln(8);

// -----------------------------------------------
// FIRMA Y SELLO
// -----------------------------------------------
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.3);

// Línea firma adoptante
$pdf->Cell(85, 6, '', 0, 0);
$pdf->Cell(40, 6, '', 'B', 0, 'C');
$pdf->Ln(2);
$pdf->Cell(85, 6, '', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(40, 6, utf8('Firma del adoptante'), 0, 1, 'C');
$pdf->Ln(6);

// Sello oficial
$pdf->SetFillColor(255, 107, 107);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 10, utf8('✓  DOCUMENTO OFICIAL — HUELLAS DE AMOR  ✓'), 1, 1, 'C', true);

$pdf->Output('D', 'certificado_adopcion_' . $datos['nombre_mascota'] . '_' . date('Y') . '.pdf');
exit();

// Función para rectángulos redondeados
function RoundedRect($pdf, $x, $y, $w, $h, $r, $style = '') {
    $k  = $pdf->k;
    $hp = $pdf->h;
    if ($style === 'F') { $op = 'f'; }
    elseif ($style === 'FD' || $style === 'DF') { $op = 'B'; }
    else { $op = 'S'; }
    $MyArc = 4/3 * (sqrt(2) - 1);
    $pdf->_out(sprintf('%.2F %.2F m', ($x+$r)*$k, ($hp-$y)*$k));
    $xc = $x+$w-$r; $yc = $y+$r;
    $pdf->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-$y)*$k));
    $pdf->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
    $xc = $x+$w-$r; $yc = $y+$h-$r;
    $pdf->_out(sprintf('%.2F %.2F l', ($x+$w)*$k, ($hp-$yc)*$k));
    $pdf->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
    $xc = $x+$r; $yc = $y+$h-$r;
    $pdf->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-($y+$h))*$k));
    $pdf->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
    $xc = $x+$r; $yc = $y+$r;
    $pdf->_out(sprintf('%.2F %.2F l', ($x)*$k, ($hp-$yc)*$k));
    $pdf->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
    $pdf->_out($op);
}
?>