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

// Decodificar arrays JSON
$quejas = json_decode($formulario['quejas'], true) ?? [];
$afirmaciones = json_decode($formulario['afirmaciones'], true) ?? [];

// Si se solicita cancelar, redirigir a la página de formularios aprobados
if (isset($_GET['cancelar']) && $_GET['cancelar'] == 'true') {
    header('Location: formularios_aprobados.php');
    exit();
}

// Configuración para el header
$titulo = "Vista Previa del Consentimiento";
$subtitulo = "Revise el documento antes de imprimir";
// Determinar a qué página redirigir según el estado del formulario
$pagina_volver = 'formularios_pendientes.php';
if ($formulario['estado_revision'] === 'aprobado') {
    $pagina_volver = 'formularios_aprobados.php';
} elseif ($formulario['estado_revision'] === 'rechazado') {
    $pagina_volver = 'formularios_rechazados.php';
}
$botones_adicionales = [
    [
        'tipo' => 'link',
        'url' => $pagina_volver,
        'icono' => 'arrow-left',
        'texto' => 'Volver',
        'clase' => 'inline-flex items-center justify-center rounded-md text-sm font-medium border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50 h-9 px-4 py-2'
    ],
    [
        'tipo' => 'button',
        'onclick' => 'window.print()',
        'icono' => 'printer',
        'texto' => 'Imprimir',
        'clase' => 'inline-flex items-center justify-center rounded-md text-sm font-medium bg-blue-600 text-white px-4 py-2 shadow-sm transition-all duration-200 hover:shadow-md hover:translate-y-[-1px] hover:bg-blue-700 active:translate-y-[1px]'
    ]
];

// Estilos adicionales para impresión
$scripts_adicionales = '
<style>
    @page {
        margin: 1.5cm;
        size: letter;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .page {
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
            width: 100% !important;
            max-width: none !important;
        }
        /* ... resto de los estilos de impresión ... */
    }
</style>';
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa del Consentimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .main-content {
            margin-left: 16rem;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        @page {
            margin: 1.5cm;
            size: letter;
        }
        @media print {
            /* Ocultar elementos de navegación */
            aside, 
            header {
                display: none !important;
            }
            
            /* Restablecer el contenedor principal */
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                background-color: white !important;
            }

            /* Ajustar el contenedor principal para impresión */
            main {
                padding: 0 !important;
            }
            
            /* Mostrar solo el contenido del consentimiento */
            .page {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                max-width: none !important;
                width: 100% !important;
            }

            /* Restablecer márgenes y fondos */
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Resto de los estilos de impresión existentes */
            .container {
                width: 100% !important;
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .grid {
                break-inside: avoid;
                display: grid !important;
            }
            .rounded-lg {
                border-radius: 0.5rem !important;
            }
            .border {
                border-color: #ddd !important;
            }
            .shadow-sm {
                box-shadow: none !important;
            }
            .mb-8 {
                margin-bottom: 0.75rem !important;
            }
            .p-8 {
                padding: 0.75rem !important;
            }
            .p-4 {
                padding: 0.5rem !important;
            }
            .gap-8 {
                gap: 0.75rem !important;
            }
            .gap-6 {
                gap: 0.5rem !important;
            }
            .gap-4 {
                gap: 0.375rem !important;
            }
            .gap-3 {
                gap: 0.25rem !important;
            }
            .text-sm {
                font-size: 9pt !important;
                line-height: 1.2 !important;
            }
            .text-xs {
                font-size: 8pt !important;
                line-height: 1.1 !important;
            }
            h1 {
                font-size: 14pt !important;
                margin-bottom: 0.5rem !important;
            }
            h2 {
                font-size: 12pt !important;
                margin-bottom: 0.5rem !important;
            }
            h3 {
                font-size: 10pt !important;
                margin-bottom: 0.25rem !important;
            }
            .grid-cols-2 {
                grid-template-columns: 1fr 1fr !important;
            }
            img {
                max-width: 100% !important;
                height: auto !important;
            }
            .space-y-2 > * + * {
                margin-top: 0.2rem !important;
            }
            .space-y-4 > * + * {
                margin-top: 0.4rem !important;
            }
            dl.space-y-2 {
                margin-bottom: 0.4rem !important;
            }
            .pb-2 {
                padding-bottom: 0.25rem !important;
            }
            .mb-4 {
                margin-bottom: 0.5rem !important;
            }
            .mb-2 {
                margin-bottom: 0.25rem !important;
            }
            /* Ajustes para las firmas */
            .grid.grid-cols-2.gap-8.mt-8 {
                margin-top: 0.75rem !important;
                page-break-inside: avoid;
            }
            .h-24 {
                height: 4rem !important;
            }
            /* Ajustes para los badges de estado */
            .rounded-full {
                border-radius: 9999px !important;
                padding: 0.1rem 0.3rem !important;
            }
            /* Optimización de espacios en listas */
            ul.space-y-1 {
                margin: 0 !important;
                padding-left: 0.5rem !important;
            }
            .bi {
                font-size: 0.8em !important;
            }
            /* Ajuste para condiciones médicas */
            .grid.grid-cols-2.gap-3 {
                gap: 0.25rem !important;
            }
            /* Reducir espacio entre secciones pero mantener legibilidad */
            .section + .section {
                margin-top: 0.5rem !important;
            }
        }
    </style>
</head>
<body class="h-full bg-[#f8f9fa]">
    <?php include 'menu_lateral.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>

        <main class="p-6">
            <div class="page bg-white rounded-lg border shadow-sm p-8 max-w-4xl mx-auto">
                <div class="text-center mb-8">
                        <h1 class="text-2xl font-bold text-gray-900">Consentimiento Médico</h1>
                </div>

                <!-- Datos Personales -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Datos Personales</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="font-medium mb-2">Información Básica</h3>
                                    <dl class="space-y-2 text-sm">
                                        <div>
                                            <dt class="text-gray-600 inline">Nombre:</dt>
                                            <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['nombre'] . ' ' . $formulario['apellido']); ?></dd>
                                </div>
                                        <div>
                                            <dt class="text-gray-600 inline">Fecha Nac.:</dt>
                                            <dd class="inline ml-1"><?php echo date('d/m/Y', strtotime($formulario['fecha_nacimiento'])); ?></dd>
                                </div>
                                        <div>
                                            <dt class="text-gray-600 inline">Edad:</dt>
                                            <dd class="inline ml-1"><?php echo $formulario['edad']; ?> años</dd>
                                </div>
                                        <div>
                                            <dt class="text-gray-600 inline">Género:</dt>
                                            <dd class="inline ml-1"><?php echo $formulario['genero']; ?></dd>
                                </div>
                                    </dl>
                            </div>

                                <div>
                                    <h3 class="font-medium mb-2">Información de Contacto</h3>
                                    <dl class="space-y-2 text-sm">
                                        <div>
                                            <dt class="text-gray-600 inline">Correo:</dt>
                                            <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['correo']); ?></dd>
                                </div>
                                        <div>
                                            <dt class="text-gray-600 inline">Tel. Celular:</dt>
                                            <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['telefono_celular']); ?></dd>
                                </div>
                                        <div>
                                            <dt class="text-gray-600 inline">Tel. Casa:</dt>
                                            <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['telefono_casa']); ?></dd>
                                </div>
                                        <div>
                                            <dt class="text-gray-600 inline">Tel. Trabajo:</dt>
                                            <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['telefono_trabajo']); ?></dd>
                                </div>
                                    </dl>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h3 class="font-medium mb-2">Dirección</h3>
                                <dl class="space-y-2 text-sm">
                                    <div>
                                        <dt class="text-gray-600 inline">Dirección:</dt>
                                        <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['direccion']); ?></dd>
                                </div>
                                    <div>
                                        <dt class="text-gray-600 inline">Ciudad:</dt>
                                        <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['ciudad']); ?></dd>
                                </div>
                                    <div>
                                        <dt class="text-gray-600 inline">Estado:</dt>
                                        <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['estado']); ?></dd>
                                </div>
                                    <div>
                                        <dt class="text-gray-600 inline">Código Postal:</dt>
                                        <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['zipcode']); ?></dd>
                        </div>
                            </dl>
                        </div>

                            <div>
                                <h3 class="font-medium mb-2">Contacto de Emergencia</h3>
                                <dl class="space-y-2 text-sm">
                                    <div>
                                        <dt class="text-gray-600 inline">Nombre:</dt>
                                        <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['contacto_emergencia']); ?></dd>
                                </div>
                                    <div>
                                        <dt class="text-gray-600 inline">Teléfono:</dt>
                                        <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['telefono_emergencia']); ?></dd>
                                </div>
                            </dl>
                        </div>

                        <?php if ($formulario['menor_edad'] === 'Si'): ?>
                            <div>
                                <h3 class="font-medium mb-2">Información del Tutor</h3>
                                <dl class="space-y-2 text-sm">
                                    <div>
                                        <dt class="text-gray-600 inline">Nombre:</dt>
                                        <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['nombre_tutor']); ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-600 inline">Teléfono:</dt>
                                        <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['telefono_tutor']); ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-600 inline">Relación:</dt>
                                        <dd class="inline ml-1"><?php echo htmlspecialchars($formulario['relacion']); ?></dd>
                                </div>
                            </dl>
                        </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quejas y Afirmaciones -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Quejas y Afirmaciones</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <?php if (!empty($quejas)): ?>
                        <div>
                                <h3 class="font-medium mb-2">Quejas Principales:</h3>
                                <ul class="space-y-1 text-sm">
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
                        <?php endif; ?>

                        <?php if (!empty($afirmaciones)): ?>
                        <div>
                                <h3 class="font-medium mb-2">Afirmaciones:</h3>
                                <ul class="space-y-1 text-sm">
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
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Información Médica -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Información Médica</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <div class="grid grid-cols-2 gap-3">
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
                                    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 p-4">
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
                                            <h3 class="font-medium">Alergias</h3>
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
                                            <h3 class="font-medium">Condición Médica</h3>
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

                <!-- Medicamentos y Suplementos -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Medicamentos y Suplementos</h2>
                        <div class="grid gap-4">
                        <?php if (!empty($formulario['medicamentos_recetados'])): ?>
                            <div class="rounded-lg border p-4">
                                <h3 class="font-medium mb-2">Medicamentos Recetados</h3>
                                <p class="text-sm text-gray-600 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_recetados'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($formulario['medicamentos_venta_libre'])): ?>
                            <div class="rounded-lg border p-4">
                                <h3 class="font-medium mb-2">Medicamentos de Venta Libre</h3>
                                <p class="text-sm text-gray-600 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_venta_libre'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($formulario['suplementos'])): ?>
                            <div class="rounded-lg border p-4">
                                <h3 class="font-medium mb-2">Suplementos</h3>
                                <p class="text-sm text-gray-600 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($formulario['suplementos'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                    <!-- Declaraciones Legales -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Declaraciones Legales</h2>
                        <div class="rounded-lg border p-4">
                            <p class="text-sm text-gray-800 mb-4">El paciente ha aceptado y confirmado todas las siguientes declaraciones legales:</p>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check text-green-600"></i>
                                    He informado al profesional de cualquier alergia conocida a medicamentos u otras sustancias.
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check text-green-600"></i>
                                    Entiendo que la terapia de infusión intravenosa no ha sido evaluada por la FDA.
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check text-green-600"></i>
                                    He informado al profesional de todos los medicamentos y suplementos actuales.
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check text-green-600"></i>
                                    Entiendo mi derecho a ser informado durante el procedimiento.
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check text-green-600"></i>
                                    Comprendo los riesgos y beneficios del procedimiento.
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check text-green-600"></i>
                                    Verifico que toda la información presentada es verdadera.
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check text-green-600"></i>
                                    Acepto la responsabilidad del pago de servicios no cubiertos.
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="bi bi-check text-green-600"></i>
                                    Acepto las condiciones sobre la información completa y cargos adicionales.
                                </li>
                            </ul>
                        </div>
                    </div>

                <?php if (!empty($formulario['comentarios_doctor'])): ?>
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Observaciones del Doctor</h2>
                        <div class="rounded-lg border p-4">
                            <p class="text-sm text-gray-600 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($formulario['comentarios_doctor'])); ?></p>
                        </div>
                </div>
                <?php endif; ?>

                <!-- Firmas -->
                    <div class="grid grid-cols-2 gap-8 mt-8">
                        <div class="text-center">
                            <div class="border rounded-lg p-4 bg-gray-50 mb-2">
                                <img src="<?php echo $formulario['firma_paciente']; ?>" alt="Firma del Paciente" class="h-24 mx-auto object-contain">
                            </div>
                            <p class="text-sm font-medium text-gray-900">Firma del Paciente</p>
                        </div>
                        <div class="text-center">
                            <div class="border rounded-lg p-4 bg-gray-50 mb-2">
                                <img src="<?php echo $formulario['firma_doctor']; ?>" alt="Firma del Doctor" class="h-24 mx-auto object-contain">
                            </div>
                            <p class="text-sm font-medium text-gray-900">Firma del Profesional de la Salud</p>
                    </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php
$conn->close();
?> 