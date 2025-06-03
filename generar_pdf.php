<?php
session_start();

// Verificar si el doctor está autenticado
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: revisar_formularios.php');
    exit();
}

require 'fpdf/fpdf.php';

class PDF extends FPDF {
    function Header() {
        // Logo
        $this->Image('logo.jpg', 10, 10, 50);
        
        // Título del documento
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(44, 62, 80); // Azul oscuro
        $this->Cell(0, 20, '', 0, 1, 'C'); // Espacio para el logo
        $this->Cell(0, 15, utf8_decode('Consentimiento Médico'), 0, 1, 'C');
        
        // Línea decorativa
        $this->SetDrawColor(52, 152, 219); // Azul
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function ChapterTitle($title) {
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(52, 152, 219); // Azul
        $this->SetTextColor(255);
        $this->Cell(0, 10, utf8_decode($title), 0, 1, 'L', true);
        $this->Ln(4);
    }

    function ChapterBody($field, $value) {
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(44, 62, 80);
        $this->Cell(60, 8, utf8_decode($field . ":"), 0, 0);
        
        $this->SetFont('Arial', '', 11);
        $this->MultiCell(0, 8, utf8_decode($value), 0, 'L');
    }
}

// Configuración de la base de datos
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'firma';

// Conectar a la base de datos
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener datos del formulario
$id = (int)$_GET['id'];
$sql = "SELECT * FROM formularios_consentimiento WHERE id = ? AND estado_revision = 'aprobado'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$formulario = $result->fetch_assoc();

if (!$formulario) {
    header('Location: revisar_formularios.php');
    exit();
}

// Crear PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);

// Información del Paciente
$pdf->ChapterTitle('Información del Paciente');
$pdf->ChapterBody('Nombre Completo', $formulario['nombre'] . ' ' . $formulario['apellido']);
$pdf->ChapterBody('Fecha de Nacimiento', date('d/m/Y', strtotime($formulario['fecha_nacimiento'])));
$pdf->ChapterBody('Edad', $formulario['edad'] . ' años');
$pdf->ChapterBody('Género', $formulario['genero']);
$pdf->ChapterBody('Correo', $formulario['correo']);
$pdf->ChapterBody('Teléfono', $formulario['telefono_celular']);
$pdf->ChapterBody('Dirección', $formulario['direccion'] . ', ' . $formulario['ciudad'] . ', ' . $formulario['estado']);

// Información Médica
$pdf->AddPage();
$pdf->ChapterTitle('Información Médica');

// Convertir JSON a array
$quejas = json_decode($formulario['quejas'], true);
$afirmaciones = json_decode($formulario['afirmaciones'], true);

if (!empty($quejas)) {
    $pdf->ChapterBody('Quejas Principales', implode(", ", $quejas));
}

if (!empty($afirmaciones)) {
    $pdf->ChapterBody('Motivos de Consulta', implode(", ", $afirmaciones));
}

// Condiciones Médicas
$pdf->Ln(5);
$pdf->ChapterTitle('Condiciones Médicas');
$condiciones = array(
    'Embarazada' => $formulario['embarazada'],
    'Diabético' => $formulario['diabetico'],
    'Fumador' => $formulario['fumador'],
    'Drogas' => $formulario['drogas'],
    'Paciente Renal' => $formulario['renal'],
    'Insuficiencia Cardíaca' => $formulario['insuficiencia'],
    'Anticoagulantes' => $formulario['anticoagulantes'],
    'Cáncer' => $formulario['cancer'],
    'Alergias' => $formulario['alergico']
);

foreach ($condiciones as $condicion => $valor) {
    if ($valor == 'Si') {
        $pdf->ChapterBody($condicion, 'Sí');
    }
}

// Comentarios del Doctor
if (!empty($formulario['comentarios_doctor'])) {
    $pdf->AddPage();
    $pdf->ChapterTitle('Observaciones del Doctor');
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(0, 8, utf8_decode($formulario['comentarios_doctor']), 0, 'L');
}

// Firmas
$pdf->AddPage();
$pdf->ChapterTitle('Firmas');

// Función para guardar imagen base64 como archivo temporal
function guardarImagenTemporal($base64_string) {
    $data = explode(',', $base64_string);
    $image_data = base64_decode($data[1]);
    $temp_file = tempnam(sys_get_temp_dir(), 'firma_');
    file_put_contents($temp_file, $image_data);
    return $temp_file;
}

// Calcular posiciones para las firmas
$yFirmas = $pdf->GetY() + 10;
$anchoFirma = 80;
$altoFirma = 40;

// Procesar firmas
$firma_paciente_temp = null;
$firma_doctor_temp = null;

if (!empty($formulario['firma_paciente'])) {
    $firma_paciente_temp = guardarImagenTemporal($formulario['firma_paciente']);
    $pdf->Image($firma_paciente_temp, 20, $yFirmas, $anchoFirma, $altoFirma, 'PNG');
}

if (!empty($formulario['firma_doctor'])) {
    $firma_doctor_temp = guardarImagenTemporal($formulario['firma_doctor']);
    $pdf->Image($firma_doctor_temp, 120, $yFirmas, $anchoFirma, $altoFirma, 'PNG');
}

// Textos de las firmas
$pdf->SetY($yFirmas + $altoFirma + 5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(44, 62, 80);

// Firma del paciente
$pdf->SetX(20);
$pdf->Cell($anchoFirma, 10, utf8_decode('Firma del Paciente'), 0, 0, 'C');
$pdf->SetX(20);
$pdf->SetY($pdf->GetY() + 5);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell($anchoFirma, 10, $formulario['nombre'] . ' ' . $formulario['apellido'], 0, 0, 'C');

// Firma del doctor
$pdf->SetY($yFirmas + $altoFirma + 5);
$pdf->SetX(120);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($anchoFirma, 10, utf8_decode('Firma del Doctor'), 0, 0, 'C');
$pdf->SetX(120);
$pdf->SetY($pdf->GetY() + 5);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell($anchoFirma, 10, 'Dr. ' . $_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido'], 0, 0, 'C');

// Fecha de aprobación
$pdf->SetY($yFirmas + $altoFirma + 20);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(128);
$pdf->Cell(0, 10, 'Fecha de aprobación: ' . date('d/m/Y H:i', strtotime($formulario['fecha_revision'])), 0, 1, 'R');

// Limpiar archivos temporales
if ($firma_paciente_temp) {
    unlink($firma_paciente_temp);
}
if ($firma_doctor_temp) {
    unlink($firma_doctor_temp);
}

// Generar nombre del archivo
$nombre_archivo = 'consentimiento_' . $formulario['nombre'] . '_' . $formulario['apellido'] . '_' . date('Y-m-d') . '.pdf';
$nombre_archivo = str_replace(' ', '_', $nombre_archivo);

// Descargar PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
$pdf->Output('I', $nombre_archivo);

$conn->close();
?> 