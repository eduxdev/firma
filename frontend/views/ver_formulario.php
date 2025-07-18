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

include '../../backend/db/conection.php';

// Obtener datos del formulario
$id = (int)$_GET['id'];
$sql = "SELECT * FROM formularios_consentimiento WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$formulario = $result->fetch_assoc();

if (!$formulario) {
    header('Location: revisar_formularios.php');
    exit();
}

// Procesar la firma y aprobación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firma_doctor = $_POST['firma_doctor'];
    $estado = $_POST['estado'];
    $comentarios = $_POST['comentarios'];
    
    $sql = "UPDATE formularios_consentimiento 
            SET firma_doctor = ?, 
                estado_revision = ?,
                comentarios_doctor = ?,
                fecha_revision = CURRENT_TIMESTAMP 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $firma_doctor, $estado, $comentarios, $id);
    
    if ($stmt->execute()) {
        if ($estado === 'aprobado') {
            // Redirigir a generar PDF
            header("Location: generar_pdf.php?id=" . $id);
        } else {
            // Redirigir a la página de formularios rechazados
            header("Location: formularios_rechazados.php");
        }
        exit();
    }
}

// Decodificar arrays JSON
$quejas = json_decode($formulario['quejas'], true) ?? [];
$afirmaciones = json_decode($formulario['afirmaciones'], true) ?? [];

// Determinar a qué página redirigir según el estado del formulario
$pagina_volver = 'formularios_pendientes.php';
if ($formulario['estado_revision'] === 'aprobado') {
    $pagina_volver = 'formularios_aprobados.php';
} elseif ($formulario['estado_revision'] === 'rechazado') {
    $pagina_volver = 'formularios_rechazados.php';
}

// Configuración para el header
$titulo = "Revisión de Formulario";
$subtitulo = "Revise y apruebe el consentimiento médico";
$url_volver = $pagina_volver;
$botones_adicionales = [
    [
        'tipo' => 'link',
        'url' => $pagina_volver,
        'icono' => 'arrow-left',
        'texto' => 'Volver',
        'clase' => 'inline-flex items-center justify-center rounded-md text-sm font-medium border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50 h-9 px-4 py-2'
    ]
];
$scripts_adicionales = '<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>';
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Formulario - Panel del Doctor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        .main-content {
            margin-left: 16rem;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="h-full bg-[#f8f9fa]">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>

        <main class="p-6">
            <div class="grid grid-cols-3 gap-6">
            <!-- Columna izquierda y central - Información del paciente -->
            <div class="col-span-2 space-y-6">
        <!-- Datos Personales -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="p-6">
                        <h3 class="font-semibold tracking-tight text-lg">Datos Personales</h3>
                        <p class="text-sm text-muted-foreground">Información básica del paciente</p>
                    </div>
                    <div class="p-6 pt-0">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <h4 class="font-medium leading-none">Información Básica</h4>
                                    <div class="text-sm">
                                        <dl class="space-y-2">
                                            <div>
                                                <dt class="text-muted-foreground inline">Nombre:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['nombre'] . ' ' . $formulario['apellido']); ?></dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Fecha de Nacimiento:</dt>
                                                <dd class="inline ml-1"><?php echo date('d/m/Y', strtotime($formulario['fecha_nacimiento'])); ?></dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Edad:</dt>
                                                <dd class="inline ml-1"><?php echo $formulario['edad']; ?> años</dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Género:</dt>
                                                <dd class="inline ml-1"><?php echo $formulario['genero']; ?></dd>
                                            </div>
                                        </dl>
                        </div>
                    </div>

                                <div class="space-y-2">
                                    <h4 class="font-medium leading-none">Contacto</h4>
                                    <div class="text-sm">
                                        <dl class="space-y-2">
                                            <div>
                                                <dt class="text-muted-foreground inline">Correo:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['correo']); ?></dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Teléfono Celular:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['telefono_celular']); ?></dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Teléfono Casa:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['telefono_casa']); ?></dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Teléfono Trabajo:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['telefono_trabajo']); ?></dd>
                                            </div>
                                        </dl>
                        </div>
                    </div>
                </div>

                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <h4 class="font-medium leading-none">Dirección</h4>
                                    <div class="text-sm">
                                        <dl class="space-y-2">
                                            <div>
                                                <dt class="text-muted-foreground inline">Dirección:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['direccion']); ?></dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Ciudad:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['ciudad']); ?></dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Estado:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['estado']); ?></dd>
                                            </div>
                <div>
                                                <dt class="text-muted-foreground inline">Código Postal:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['zipcode']); ?></dd>
                                            </div>
                                        </dl>
                        </div>
                    </div>

                                <div class="space-y-2">
                                    <h4 class="font-medium leading-none">Contacto de Emergencia</h4>
                                    <div class="text-sm">
                                        <dl class="space-y-2">
                                            <div>
                                                <dt class="text-muted-foreground inline">Nombre:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['contacto_emergencia']); ?></dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Teléfono:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['telefono_emergencia']); ?></dd>
                                            </div>
                                        </dl>
                        </div>
                    </div>

                    <?php if ($formulario['menor_edad'] === 'Si'): ?>
                                <div class="space-y-2">
                                    <h4 class="font-medium leading-none">Información del Tutor</h4>
                                    <div class="text-sm">
                                        <dl class="space-y-2">
                                            <div>
                                                <dt class="text-muted-foreground inline">Nombre:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['nombre_tutor']); ?></dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Teléfono:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['telefono_tutor']); ?></dd>
                                            </div>
                                            <div>
                                                <dt class="text-muted-foreground inline">Relación:</dt>
                                                <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['relacion']); ?></dd>
                                            </div>
                                        </dl>
                        </div>
                    </div>
                    <?php endif; ?>
                            </div>
                </div>
            </div>
        </div>

        <!-- Información Médica -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="p-6">
                        <h3 class="font-semibold tracking-tight text-lg">Información Médica</h3>
                        <p class="text-sm text-muted-foreground">Condiciones y antecedentes médicos</p>
                    </div>
                    <div class="p-6 pt-0">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <?php
                                    $condiciones = [
                                        'embarazada' => 'Embarazada',
                                        'diabetico' => 'Diabético',
                                        'fumador' => 'Fumador',
                                        'drogas' => 'Drogas',
                                        'renal' => 'Renal',
                                        'insuficiencia' => 'Insuf. Cardíaca',
                                        'anticoagulantes' => 'Anticoagulantes',
                                        'cancer' => 'Cáncer'
                                    ];
                                    foreach ($condiciones as $key => $label): ?>
                                    <div class="rounded-lg border p-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium"><?php echo $label; ?></span>
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold <?php echo $formulario[$key] === 'Si' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                                                <?php echo $formulario[$key]; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                    </div>

                    <?php if ($formulario['drogas'] === 'Si'): ?>
                                <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                                    <div class="flex items-center gap-2 text-red-700 text-sm font-medium">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <span>Frecuencia de uso de drogas</span>
                                    </div>
                                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($formulario['drogas_frecuencia']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                            <div class="space-y-4">
                                <div class="rounded-lg border p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium">Alergias</h4>
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold <?php echo $formulario['alergico'] === 'Si' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                                            <?php echo $formulario['alergico']; ?>
                                        </span>
                                    </div>
                                    <?php if ($formulario['alergico'] === 'Si'): ?>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($formulario['medicamento_alergico']); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="rounded-lg border p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium">Condición Médica</h4>
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold <?php echo $formulario['condicion_medica'] === 'Si' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                                            <?php echo $formulario['condicion_medica']; ?>
                                        </span>
                                    </div>
                                    <?php if ($formulario['condicion_medica'] === 'Si'): ?>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($formulario['condicion_explicacion']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medicamentos y Suplementos -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="p-6">
                        <h3 class="font-semibold tracking-tight text-lg">Medicamentos y Suplementos</h3>
                        <p class="text-sm text-muted-foreground">Medicamentos actuales y suplementos</p>
                    </div>
                    <div class="p-6 pt-0">
                        <div class="grid gap-4">
                            <div class="rounded-lg border p-4">
                                <h4 class="font-medium mb-2">Medicamentos Recetados</h4>
                                <p class="text-sm text-gray-600 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_recetados'])); ?></p>
                            </div>
                            <div class="rounded-lg border p-4">
                                <h4 class="font-medium mb-2">Medicamentos de Venta Libre</h4>
                                <p class="text-sm text-gray-600 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_venta_libre'])); ?></p>
                        </div>
                            <div class="rounded-lg border p-4">
                                <h4 class="font-medium mb-2">Suplementos</h4>
                                <p class="text-sm text-gray-600 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($formulario['suplementos'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quejas y Afirmaciones -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="p-6">
                        <h3 class="font-semibold tracking-tight text-lg">Quejas y Afirmaciones</h3>
                        <p class="text-sm text-muted-foreground">Motivos de consulta y expectativas</p>
                    </div>
                    <div class="p-6 pt-0">
                        <div class="grid grid-cols-2 gap-6">
                <div>
                                <div class="rounded-lg border p-4">
                                    <h4 class="font-medium mb-2">Quejas Principales</h4>
                                    <ul class="space-y-1 text-sm text-gray-600">
                    <?php foreach ($quejas as $queja): ?>
                                        <li class="flex items-center gap-2">
                                            <i class="bi bi-dot"></i>
                                            <?php echo htmlspecialchars($queja); ?>
                                        </li>
                    <?php endforeach; ?>
                    <?php if (!empty($formulario['otros_quejas'])): ?>
                                        <li class="flex items-center gap-2">
                                            <i class="bi bi-dot"></i>
                                            Otras: <?php echo htmlspecialchars($formulario['otros_quejas']); ?>
                                        </li>
                    <?php endif; ?>
                </ul>
                                </div>
                </div>

                <div>
                                <div class="rounded-lg border p-4">
                                    <h4 class="font-medium mb-2">Afirmaciones</h4>
                                    <ul class="space-y-1 text-sm text-gray-600">
                    <?php foreach ($afirmaciones as $afirmacion): ?>
                                        <li class="flex items-center gap-2">
                                            <i class="bi bi-dot"></i>
                                            <?php echo htmlspecialchars($afirmacion); ?>
                                        </li>
                    <?php endforeach; ?>
                    <?php if (!empty($formulario['otros_afirmaciones'])): ?>
                                        <li class="flex items-center gap-2">
                                            <i class="bi bi-dot"></i>
                                            Otras: <?php echo htmlspecialchars($formulario['otros_afirmaciones']); ?>
                                        </li>
                    <?php endif; ?>
                </ul>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

            <!-- Columna derecha - Panel de Aprobación -->
            <div>
                <div class="sticky top-24 rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="p-6">
                        <h3 class="font-semibold tracking-tight text-lg">Revisión y Aprobación</h3>
                        <p class="text-sm text-muted-foreground">Complete el proceso de revisión</p>
                    </div>
                    
                    <form method="POST" onsubmit="return validarFormulario()" class="p-6 pt-0 space-y-6">
                        <!-- Firma del Paciente -->
                        <div>
                            <h4 class="font-medium mb-3">Firma del Paciente</h4>
                            <div class="rounded-lg border bg-muted p-4">
                                <img src="<?php echo $formulario['firma_paciente']; ?>" alt="Firma del Paciente" class="max-h-32 mx-auto">
                            </div>
                        </div>

                        <!-- Decisión -->
                        <div>
                            <h4 class="font-medium mb-3">Decisión</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative">
                                    <input type="radio" name="estado" value="aprobado" class="peer sr-only" required>
                                    <div class="rounded-lg border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground cursor-pointer transition-all duration-200 hover:shadow-md hover:translate-y-[-1px] active:translate-y-[1px] peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-md">
                                        <div class="flex items-center justify-center gap-2">
                                            <i class="bi bi-check-circle text-xl text-green-600 peer-checked:text-green-700"></i>
                                            <span class="font-medium peer-checked:text-green-700">Aprobar</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="estado" value="rechazado" class="peer sr-only" required>
                                    <div class="rounded-lg border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground cursor-pointer transition-all duration-200 hover:shadow-md hover:translate-y-[-1px] active:translate-y-[1px] peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:shadow-md">
                                        <div class="flex items-center justify-center gap-2">
                                            <i class="bi bi-x-circle text-xl text-red-600 peer-checked:text-red-700"></i>
                                            <span class="font-medium peer-checked:text-red-700">Rechazar</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Comentarios -->
                        <div>
                            <h4 class="font-medium mb-3">Comentarios</h4>
                            <textarea 
                                name="comentarios" 
                                rows="4" 
                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-all duration-200"
                                placeholder="Agregue sus observaciones aquí..."></textarea>
                        </div>

                        <!-- Firma del Doctor -->
                        <div>
                            <h4 class="font-medium mb-3">Su Firma</h4>
                            <div class="relative">
                                <div class="rounded-lg border-2 border-dashed border-muted bg-background hover:border-muted transition-all duration-200">
                                    <canvas id="firma-doctor" class="w-full h-40"></canvas>
                                </div>
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none firma-placeholder">
                                    <div class="text-center">
                                        <i class="bi bi-pen text-3xl"></i>
                                        <p class="mt-2">Firme aquí</p>
                                    </div>
                                </div>
                                <button type="button" class="absolute top-2 right-2 text-muted-foreground hover:text-destructive transition-all duration-200" onclick="borrarFirmaDoctor()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="firma_doctor" id="firma_doctor" required>

                        <!-- Botón de Envío -->
                        <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full bg-blue-600 shadow-sm hover:shadow-md hover:translate-y-[-1px] active:translate-y-[1px] text-white">
                            <i class="bi bi-check-circle mr-2"></i>
                            Finalizar Revisión
                        </button>
                    </form>
                </div>
            </div>
        </main>

        <!-- Declaraciones Legales -->
        <div class="p-6">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="font-semibold tracking-tight text-lg">Declaraciones Legales</h3>
                    <p class="text-sm text-muted-foreground">Declaraciones aceptadas por el paciente</p>
                </div>
                <div class="p-6 pt-0">
                    <div class="rounded-lg border p-4">
                        <p class="text-sm text-gray-800 mb-4">El paciente ha aceptado y confirmado todas las siguientes declaraciones legales:</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center gap-2">
                                <i class="bi bi-check-circle text-green-600"></i>
                                He informado al profesional de cualquier alergia conocida a medicamentos u otras sustancias.
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-check-circle text-green-600"></i>
                                Entiendo que la terapia de infusión intravenosa no ha sido evaluada por la FDA.
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-check-circle text-green-600"></i>
                                He informado al profesional de todos los medicamentos y suplementos actuales.
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-check-circle text-green-600"></i>
                                Entiendo mi derecho a ser informado durante el procedimiento.
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-check-circle text-green-600"></i>
                                Comprendo los riesgos y beneficios del procedimiento.
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-check-circle text-green-600"></i>
                                Verifico que toda la información presentada es verdadera.
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-check-circle text-green-600"></i>
                                Acepto la responsabilidad del pago de servicios no cubiertos.
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-check-circle text-green-600"></i>
                                Acepto las condiciones sobre la información completa y cargos adicionales.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let signaturePad;
        let firmaPlaceholder;

        window.onload = function() {
            const canvas = document.getElementById('firma-doctor');
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });

            firmaPlaceholder = document.querySelector('.firma-placeholder');

            signaturePad.addEventListener('beginStroke', function() {
                firmaPlaceholder.style.display = 'none';
            });

            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();
        };

        function resizeCanvas() {
            const canvas = document.getElementById('firma-doctor');
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
            firmaPlaceholder.style.display = 'block';
        }

        function borrarFirmaDoctor() {
            signaturePad.clear();
            firmaPlaceholder.style.display = 'block';
        }

        function validarFormulario() {
            if (signaturePad.isEmpty()) {
                alert('Por favor, firme el documento antes de continuar.');
                return false;
            }

            document.getElementById('firma_doctor').value = signaturePad.toDataURL();
            
            const estadoSelect = document.querySelector('input[name="estado"]:checked');
            if (estadoSelect && estadoSelect.value === 'aprobado') {
                const formData = new FormData(document.querySelector('form'));
                fetch(document.querySelector('form').action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        const urlParams = new URLSearchParams(window.location.search);
                        const id = urlParams.get('id');
                        window.location.href = `generar_pdf.php?id=${id}`;
                    }
                });
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
<?php
$conn->close();
?> 