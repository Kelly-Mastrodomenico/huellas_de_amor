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

try {
    $stmt = $conexion->prepare(
        "SELECT s.*,
                m.nombre AS nombre_mascota, m.especie, m.raza, m.sexo,
                m.edad_anios, m.edad_meses,
                u.nombre AS nombre_usuario, u.apellidos, u.email, u.dni,
                p.nombre AS nombre_protectora
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

// Igual que en exportar_mascotas.php
function utf8($texto) {
    return iconv('UTF-8', 'windows-1252', $texto ?? '');
}

// Datos del certificado
$numCertificado = 'HA-' . str_pad($datos['id'], 6, '0', STR_PAD_LEFT) . '-' . date('Y');
$nombreCompleto = $datos['nombre_usuario'] . ' ' . $datos['apellidos'];
$fechaAdopcion  = date('d/m/Y', strtotime($datos['fecha_solicitud']));
$edadMascota    = formatearEdad($datos['edad_anios'], $datos['edad_meses']);
$protectora     = $datos['nombre_protectora'] ?? 'Huellas de Amor';

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
    $pdf->SetTextColor(255, 107, 107);
    $pdf->Cell(0, 12, 'Huellas de Amor', 0, 1, 'C');
    $pdf->Ln(4);
}

// --- TITULO ---
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(0, 10, utf8('CERTIFICADO DE ADOPCIÓN'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6, utf8('Nº ' . $numCertificado), 0, 1, 'C');
$pdf->Ln(2);

// Linea decorativa
$pdf->SetDrawColor(255, 107, 107);
$pdf->SetLineWidth(0.8);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

// --- TEXTO INTRO ---
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 7,
    utf8('La plataforma Huellas de Amor certifica que la siguiente adopción ha sido aprobada oficialmente:'),
    0, 'C');
$pdf->Ln(6);

// --- CAJA ADOPTANTE ---
$pdf->SetFillColor(247, 249, 252);
$pdf->SetDrawColor(78, 205, 196);
$pdf->SetLineWidth(0.4);
$pdf->Rect(15, $pdf->GetY(), 180, 36, 'DF');

$pdf->SetY($pdf->GetY() + 3);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(78, 205, 196);
$pdf->Cell(0, 7, utf8('DATOS DEL ADOPTANTE'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(90, 7, utf8('Nombre: ' . $nombreCompleto), 0, 0, 'C');
$pdf->Cell(90, 7, utf8('Email: ' . $datos['email']), 0, 1, 'C');
$pdf->Cell(90, 7, utf8('Fecha de adopcion: ' . $fechaAdopcion), 0, 0, 'C');
if (!empty($datos['dni'])) {
    $pdf->Cell(90, 7, utf8('DNI/NIE: ' . $datos['dni']), 0, 1, 'C');
} else {
    $pdf->Ln(7);
}
$pdf->Ln(6);

// --- CAJA MASCOTA ---
$pdf->SetFillColor(255, 248, 248);
$pdf->SetDrawColor(255, 107, 107);
$pdf->Rect(15, $pdf->GetY(), 180, 44, 'DF');

$pdf->SetY($pdf->GetY() + 3);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 107, 107);
$pdf->Cell(0, 7, utf8('DATOS DE LA MASCOTA ADOPTADA'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(90, 7, utf8('Nombre: ' . $datos['nombre_mascota']), 0, 0, 'C');
$pdf->Cell(90, 7, utf8('Especie: ' . ucfirst($datos['especie'])), 0, 1, 'C');
$pdf->Cell(90, 7, utf8('Raza: ' . ($datos['raza'] ?? 'Mestizo')), 0, 0, 'C');
$pdf->Cell(90, 7, utf8('Sexo: ' . ucfirst($datos['sexo'])), 0, 1, 'C');
$pdf->Cell(90, 7, utf8('Edad: ' . $edadMascota), 0, 0, 'C');
$pdf->Cell(90, 7, utf8('Protectora: ' . $protectora), 0, 1, 'C');
$pdf->Ln(8);

// --- COMPROMISO ---
$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(44, 62, 80);
$pdf->Cell(0, 8, utf8('Compromiso de adopcion responsable'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 6,
    utf8('El adoptante se compromete a proporcionar a ' . $datos['nombre_mascota'] .
    ' un hogar seguro, alimentacion adecuada, atencion veterinaria regular y todo el amor' .
    ' y cuidado que merece. La adopcion es un acto de responsabilidad y compromiso de por vida.'),
    0, 'J');
$pdf->Ln(10);

// --- FIRMA ---
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.3);
$pdf->Line(50, $pdf->GetY(), 160, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6, utf8('Firma del adoptante'), 0, 1, 'C');
$pdf->Ln(6);

// --- SELLO ---
$pdf->SetFillColor(255, 107, 107);
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

$pdf->Output('D', 'certificado_adopcion_' . $datos['nombre_mascota'] . '_' . date('Y') . '.pdf');
exit();
?>