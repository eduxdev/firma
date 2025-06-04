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

// Generar nombre del archivo
$nombre_archivo = 'consentimiento_' . $formulario['nombre'] . '_' . $formulario['apellido'] . '_' . date('Y-m-d') . '.pdf';
$nombre_archivo = str_replace(' ', '_', $nombre_archivo);

// Configurar cabeceras para PDF
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consentimiento Médico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            margin: 1.5cm;
            size: letter;
        }
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .print-columns-2 {
                column-count: 2;
                column-gap: 2rem;
            }
        }
    </style>
</head>
<body class="bg-white text-gray-800 p-6 text-sm leading-relaxed">
    <!-- Encabezado -->
    <div class="flex items-center justify-between mb-8 border-b pb-4">
        <img src="/public/assets/img/logo.jpg" alt="Logo" class="h-16 w-auto">
        <h1 class="text-2xl font-bold text-gray-700 text-center flex-grow">Consentimiento Médico</h1>
    </div>

    <!-- Datos Personales -->
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700 border-b border-gray-300 pb-2 mb-4">Datos Personales</h2>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-sm">
                <span class="font-semibold">Nombre:</span>
                <span><?php echo htmlspecialchars($formulario['nombre'] . ' ' . $formulario['apellido']); ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Fecha Nac.:</span>
                <span><?php echo date('d/m/Y', strtotime($formulario['fecha_nacimiento'])); ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Edad:</span>
                <span><?php echo $formulario['edad']; ?> años</span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Género:</span>
                <span><?php echo $formulario['genero']; ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Correo:</span>
                <span><?php echo htmlspecialchars($formulario['correo']); ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Tel. Celular:</span>
                <span><?php echo htmlspecialchars($formulario['telefono_celular']); ?></span>
            </div>
            <?php if ($formulario['menor_edad'] === 'Si'): ?>
            <div class="text-sm">
                <span class="font-semibold">Tutor:</span>
                <span><?php echo htmlspecialchars($formulario['nombre_tutor']); ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Tel. Tutor:</span>
                <span><?php echo htmlspecialchars($formulario['telefono_tutor']); ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Relación:</span>
                <span><?php echo htmlspecialchars($formulario['relacion']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quejas y Afirmaciones -->
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700 border-b border-gray-300 pb-2 mb-4">Quejas y Afirmaciones</h2>
        <div class="grid grid-cols-2 gap-6">
            <?php if (!empty($quejas)): ?>
            <div>
                <h3 class="font-semibold mb-2">Quejas Principales:</h3>
                <ul class="space-y-1">
                    <?php foreach ($quejas as $queja): ?>
                        <li class="flex items-start">
                            <span class="mr-2">•</span>
                            <span><?php echo htmlspecialchars($queja); ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (!empty($formulario['otros_quejas'])): ?>
                        <li class="flex items-start">
                            <span class="mr-2">•</span>
                            <span>Otras: <?php echo htmlspecialchars($formulario['otros_quejas']); ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($afirmaciones)): ?>
            <div>
                <h3 class="font-semibold mb-2">Afirmaciones:</h3>
                <ul class="space-y-1">
                    <?php foreach ($afirmaciones as $afirmacion): ?>
                        <li class="flex items-start">
                            <span class="mr-2">•</span>
                            <span><?php echo htmlspecialchars($afirmacion); ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (!empty($formulario['otros_afirmaciones'])): ?>
                        <li class="flex items-start">
                            <span class="mr-2">•</span>
                            <span>Otras: <?php echo htmlspecialchars($formulario['otros_afirmaciones']); ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información Médica -->
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700 border-b border-gray-300 pb-2 mb-4">Información Médica</h2>
        <div class="grid grid-cols-4 gap-4 mb-4">
            <div class="text-sm">
                <span class="font-semibold">Embarazada:</span>
                <span><?php echo $formulario['embarazada']; ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Diabético:</span>
                <span><?php echo $formulario['diabetico']; ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Fumador:</span>
                <span><?php echo $formulario['fumador']; ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Drogas:</span>
                <span><?php echo $formulario['drogas']; ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Renal:</span>
                <span><?php echo $formulario['renal']; ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Insuf. Cardíaca:</span>
                <span><?php echo $formulario['insuficiencia']; ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Anticoagulantes:</span>
                <span><?php echo $formulario['anticoagulantes']; ?></span>
            </div>
            <div class="text-sm">
                <span class="font-semibold">Cáncer:</span>
                <span><?php echo $formulario['cancer']; ?></span>
            </div>
        </div>

        <?php if ($formulario['drogas'] === 'Si'): ?>
        <div class="text-sm mb-2">
            <span class="font-semibold">Frecuencia de uso de drogas:</span>
            <span><?php echo htmlspecialchars($formulario['drogas_frecuencia']); ?></span>
        </div>
        <?php endif; ?>

        <?php if ($formulario['alergico'] === 'Si'): ?>
        <div class="text-sm mb-2">
            <span class="font-semibold">Medicamentos Alérgicos:</span>
            <span><?php echo htmlspecialchars($formulario['medicamento_alergico']); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Medicamentos y Suplementos -->
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700 border-b border-gray-300 pb-2 mb-4">Medicamentos y Suplementos</h2>
        <div class="grid grid-cols-2 gap-6">
            <?php if (!empty($formulario['medicamentos_recetados'])): ?>
            <div class="text-sm">
                <span class="font-semibold block mb-1">Medicamentos Recetados:</span>
                <span class="block pl-4"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_recetados'])); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($formulario['medicamentos_venta_libre'])): ?>
            <div class="text-sm">
                <span class="font-semibold block mb-1">Medicamentos de Venta Libre:</span>
                <span class="block pl-4"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_venta_libre'])); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($formulario['suplementos'])): ?>
        <div class="text-sm mt-4">
            <span class="font-semibold block mb-1">Suplementos:</span>
            <span class="block pl-4"><?php echo nl2br(htmlspecialchars($formulario['suplementos'])); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($formulario['comentarios_doctor'])): ?>
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700 border-b border-gray-300 pb-2 mb-4">Observaciones del Doctor</h2>
        <p class="text-sm pl-4"><?php echo nl2br(htmlspecialchars($formulario['comentarios_doctor'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- Firmas -->
    <div class="flex justify-between items-end mt-8 space-x-8">
        <div class="flex-1 text-center">
            <img src="<?php echo $formulario['firma_paciente']; ?>" alt="Firma del Paciente" class="h-16 mx-auto mb-2">
            <div class="border-t border-gray-400 pt-2 text-sm font-semibold">Firma del Paciente</div>
        </div>
        <div class="flex-1 text-center">
            <img src="<?php echo $formulario['firma_doctor']; ?>" alt="Firma del Doctor" class="h-16 mx-auto mb-2">
            <div class="border-t border-gray-400 pt-2 text-sm font-semibold">Firma del Doctor</div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
<?php
$conn->close();
?> 