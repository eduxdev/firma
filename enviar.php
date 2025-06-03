<?php
require 'fpdf/fpdf.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';
require 'PHPMailer/PHPMailer/src/Exception.php';

// Configuración Gmail
$smtp_user = 'dropsinfusionvital@gmail.com';
$smtp_pass = 'jjdjcgyrjkpkcbll';
$destinatario = $smtp_user;

// 1. Generar PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20); // Margen inferior = 20mm


// Estilo del PDF
// Después del encabezado principal
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(0, 10, utf8_decode('Consentimiento Médico'), 0, 1, 'C', true);

// Agregar logo a la derecha
$pdf->Image('logo.jpg', 160, 12, 30, 0, 'JPG'); // 30mm de ancho
$pdf->Ln(15); // Ajustar espacio después del logo

// Resto del código...

// Función para agregar datos al PDF (VERSIÓN MEJORADA)
function agregarDato($pdf, $titulo, $dato) {
    if (!empty($dato)) {
        // Convertir caracteres
        $titulo = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $titulo);
        $dato = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $dato);
        
        // Configurar estilos
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(75, 10, $titulo, 0, 0); // Aumenté el ancho de 60 a 70
        
        // Posicionar datos 15mm más a la derecha
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetX(100); // Posición horizontal fija para los datos
        $pdf->MultiCell(0, 10, $dato); // Usar MultiCell para texto largo
        
        $pdf->Ln(2); // Pequeño espacio entre elementos
    }
}

// Función para agregar secciones
function agregarSeccion($pdf, $titulo) {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(200, 220, 255); // Color de fondo para el título de la sección
    $pdf->Cell(0, 10, utf8_decode($titulo), 0, 1, 'L', true); // Usar utf8_decode
    $pdf->Ln(5);
}

// Recibir los valores de fecha de nacimiento y edad
$dia = $_POST['fecha_nacimiento_dia'];
$mes = $_POST['fecha_nacimiento_mes'];
$anio = $_POST['fecha_nacimiento_anio'];
$edad = $_POST['edad'];

// Validar que los campos de fecha de nacimiento no estén vacíos
if (empty($dia) || empty($mes) || empty($anio)) {
    die("Error: La fecha de nacimiento es obligatoria.");
}

// Validar que la edad no esté vacía
if (empty($edad)) {
    die("Error: La edad es obligatoria.");
}

// Formatear la fecha de nacimiento
$fecha_nacimiento = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);

// Validar que la fecha de nacimiento sea válida
if (!checkdate($mes, $dia, $anio)) {
    die("Error: La fecha de nacimiento no es válida.");
}

// Sección 1: Datos Personales
agregarSeccion($pdf, 'Datos Personales');
agregarDato($pdf, 'Nombre del Paciente:', $_POST['nombre']. ' ' . $_POST['apellido']);
agregarDato($pdf, 'Menor de edad:', $_POST['menor_edad']);
agregarDato($pdf, 'Nombre del tutor:', $_POST['nombre_tutor']);
agregarDato($pdf, 'Telefono del tutor:', $_POST['telefono_tutor']);
agregarDato($pdf, 'Relación:', $_POST['relacion']);
agregarDato($pdf, 'Fecha de Nacimiento:', $fecha_nacimiento);
agregarDato($pdf, 'Edad:', $edad);
agregarDato($pdf, 'Género:', $_POST['genero']);
agregarDato($pdf, 'Correo:', $_POST['correo']);
agregarDato($pdf, 'Dirección:', $_POST['direccion']);
agregarDato($pdf, 'Ciudad:', $_POST['ciudad']);
agregarDato($pdf, 'Estado:', $_POST['estado']);
agregarDato($pdf, 'Zip Code:', $_POST['zipcode']);
agregarDato($pdf, 'Teléfono Casa:', $_POST['telefono_casa']);
agregarDato($pdf, 'Teléfono Celular:', $_POST['telefono_celular']);
agregarDato($pdf, 'Teléfono Trabajo:', $_POST['telefono_trabajo']);
agregarDato($pdf, 'Contacto de Emergencia:', $_POST['contacto_emergencia']);
agregarDato($pdf, 'Teléfono de Emergencia:', $_POST['telefono_emergencia']);


// Sección 2: Quejas y Afirmaciones
agregarSeccion($pdf, 'Quejas y Afirmaciones');

// Array de quejas con sus descripciones
$quejas = [
    'Fatiga' => 'Fatiga o falta de energía',
    'Estrés' => 'Estrés',
    'Mala alimentación' => 'Mala alimentación',
    'Concentración' => 'Problemas de concentración',
    'Depresión' => 'Bajo estado de ánimo o depresión',
    'Resfriado' => 'Síntomas de resfriado o gripe',
    'Arrugas' => 'Arrugas faciales o líneas de expresión',
    'Piel opaca' => 'Piel opaca o seca',
    'Mala absorción' => 'Problemas de mala absorción',
];

// Agrega solo las quejas seleccionadas
if (!empty($_POST['quejas'])) {
    foreach ($_POST['quejas'] as $queja) {
        if (array_key_exists($queja, $quejas)) {
            agregarDato($pdf, $quejas[$queja] . ':', 'Si');
        }
    }
} else {
    agregarDato($pdf, 'Principales Quejas:', 'Ninguna');
}

// Agrega la queja adicional si existe
agregarDato($pdf, 'Otras Quejas:', !empty($_POST['otros_quejas']) ? utf8_decode($_POST['otros_quejas']) : 'Ninguna');

// Sección 3: Afirmaciones
agregarSeccion($pdf, 'Afirmaciones');

// Array de afirmaciones con sus descripciones
$afirmaciones = [
    'Energía' => 'Más energía y bienestar',
    'Cuidar cuerpo' => 'Cuidar mi cuerpo',
    'Pérdida de peso' => 'Apoyar mi pérdida de peso',
    'Evitar enfermedad' => 'Evitar enfermedades',
    'Recuperación' => 'Recuperarme rápido',
    'Envejecimiento' => 'Retrasar el envejecimiento',
    'Parecer joven' => 'Sentirme y verme joven',
    'Piel suave' => 'Piel más suave y brillante',
    'Toxinas' => 'Eliminar toxinas',
    'Resaca' => 'Recuperarme de resaca',
];

// Agrega solo las afirmaciones seleccionadas
if (!empty($_POST['afirmaciones'])) {
    foreach ($_POST['afirmaciones'] as $afirmacion) {
        if (array_key_exists($afirmacion, $afirmaciones)) {
            agregarDato($pdf, $afirmaciones[$afirmacion] . ':', 'Si');
        }
    }
} else {
    agregarDato($pdf, 'Afirmaciones:', 'Ninguna');
}  
agregarDato($pdf, 'Otras Afirmaciones:', !empty($_POST['otros_afirmaciones']) ? utf8_decode($_POST['otros_afirmaciones']) : 'Ninguna');

// Sección 4: Preguntas de Salud
agregarSeccion($pdf, 'Preguntas de Salud');
agregarDato($pdf, 'Embarazada:', $_POST['embarazada']);
agregarDato($pdf, 'Diabético:', $_POST['diabetico']);
agregarDato($pdf, 'Fumador:', $_POST['fumador']);
agregarDato($pdf, 'Drogas:', $_POST['drogas']);
agregarDato($pdf, 'Drogas con Frecuencia:', $_POST['drogas_frecuencia']);
agregarDato($pdf, 'Paciente Renal:', $_POST['renal']);
agregarDato($pdf, 'Insuficiencia Cardiaca:', $_POST['insuficiencia']);
agregarDato($pdf, 'Consume Anticoagulantes:', $_POST['anticoagulantes']);
agregarDato($pdf, 'Paciente con Cáncer:', $_POST['cancer']);
agregarDato($pdf, 'Paciente Alérgico:', $_POST['alergico']);
agregarDato($pdf, 'Alergico a:', $_POST['medicamento_alergico']);
agregarDato($pdf, 'Condición Médica:', $_POST['condicion_medica']);
agregarDato($pdf, 'Explicación:', $_POST['condicion_explicacion']);
agregarDato($pdf, 'Medicamentos Recetados:', $_POST['medicamentos_recetados']);
agregarDato($pdf, 'Medicamentos de Venta Libre:', $_POST['medicamentos_venta_libre']);
agregarDato($pdf, 'Suplementos:', $_POST['suplementos']);

// Sección 5: Declaraciones
if (!empty($_POST['declaraciones'])) {
    agregarSeccion($pdf, 'Aceptacion de Productos y Servicios');
    $pdf->SetFont('Arial', '', 12);
    
    // Texto de aceptación destacado
    $pdf->SetFont('','B');
    $pdf->Cell(0, 10, utf8_decode('DECLARACIÓN FINAL DE ACEPTACIÓN'), 0, 1);
    $pdf->SetFont('','');
    $pdf->MultiCell(0, 8, utf8_decode('Reconozco haber leído y comprendido toda la información anterior. Acepto voluntariamente someterme al procedimiento y autorizo al personal médico a realizar las acciones necesarias.'), 0, 1);
    
    // Espacio adicional antes de firmas
    $pdf->Ln(10);
}

/// SECCIÓN DE FIRMAS MEJORADA ///
/// SECCIÓN DE FIRMAS CORREGIDA ///
$alturaNecesaria = 50; // 40mm (altura recuadro) + 10mm (texto)
$alturaDisponible = $pdf->GetPageHeight() - $pdf->GetY() - 20;

// Agregar nueva página si es necesario
if ($alturaDisponible < $alturaNecesaria) {
    $pdf->AddPage();
    $yFirmas = $pdf->GetY();
} else {
    $yFirmas = $pdf->GetY();
}

// Posiciones y dimensiones
$anchoFirma = 80;
$altoFirma = 40;
$margen = 15;
$espacioEntreFirmas = 20; // Espacio entre los dos recuadros

// Calcular posiciones X
$xPaciente = $margen;
$xMedico = $pdf->GetPageWidth() - $anchoFirma - $margen;

// Firma Paciente (Izquierda)
$pdf->Rect($xPaciente, $yFirmas, $anchoFirma, $altoFirma);
if (!empty($_POST['firma_paciente'])) {
    $pdf->Image($_POST['firma_paciente'], $xPaciente, $yFirmas, $anchoFirma, $altoFirma, 'PNG');
} else {
    // Texto guía si no hay firma
    $pdf->SetFont('Arial','I',10);
    $pdf->Text($xPaciente + 5, $yFirmas + 20, utf8_decode('Firma del paciente aquí'));
    $pdf->SetFont('Arial','',12);
}

$pdf->SetXY($xPaciente, $yFirmas + $altoFirma + 2);
$pdf->Cell($anchoFirma, 10, utf8_decode('Firma del Paciente'), 0, 0, 'C');

// Firma Médico (Derecha - Siempre visible)
$pdf->Rect($xMedico, $yFirmas, $anchoFirma, $altoFirma);
$pdf->Image('firma_doctor.png', $xMedico, $yFirmas, $anchoFirma, $altoFirma, 'PNG');
$pdf->SetFont('Arial','',12);
$pdf->SetXY($xMedico, $yFirmas + $altoFirma + 2);
$pdf->Cell($anchoFirma, 10, utf8_decode('Firma del Profesional de la Salud'), 0, 0, 'C');

// Generar contenido PDF
$pdfContent = $pdf->Output('', 'S');
// 2. Enviar por correo
$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = $smtp_user;
$mail->Password = $smtp_pass;
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom($smtp_user, 'Formulario Web');
$mail->addAddress($destinatario);
$mail->Subject = 'Nuevo Consentimiento - ' . date('d/m/Y');
$mail->Body = 'Adjunto encontrarás el documento firmado.';
$mail->addStringAttachment($pdfContent, 'consentimiento.pdf');

if ($mail->send()) {
    header("Location: index.php");
} else {
    die("Error al enviar: " . $mail->ErrorInfo);
}
?>