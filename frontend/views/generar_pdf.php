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

// Si se solicita cancelar, redirigir a la página de formularios aprobados
if (isset($_GET['cancelar']) && $_GET['cancelar'] == 'true') {
    header('Location: formularios_aprobados.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consentimiento Médico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .page {
            background-color: white;
            max-width: 21cm;
            margin: 0 auto;
            padding: 2cm 1.5cm;
        }
        @page {
            margin: 1.5cm;
            size: letter;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
        .btn-cancel {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #f44336, #c62828);
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            display: flex;
            align-items: center;
            z-index: 1000;
        }
        .btn-cancel:hover {
            background: linear-gradient(135deg, #e53935, #b71c1c);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }
        .btn-cancel svg {
            margin-right: 8px;
        }
        .section {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .section:last-child {
            border-bottom: none;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .data-item {
            margin-bottom: 5px;
        }
        .data-label {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
    <!-- Botón de cancelar en la esquina superior derecha -->
    <a href="?id=<?php echo $id; ?>&cancelar=true" class="btn-cancel no-print">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
        </svg>
        Cancelar
    </a>

    <!-- Contenido del PDF -->
    <div class="page">
        <!-- Encabezado -->
        <div class="flex items-center justify-between mb-6 border-b pb-4">
            <img src="/public/assets/img/logo.jpg" alt="Logo" class="h-14 w-auto">
            <h1 class="text-xl font-bold text-gray-800 text-center">Consentimiento Médico</h1>
            <div class="text-sm text-gray-500"><?php echo date('d/m/Y'); ?></div>
        </div>

        <!-- Datos Personales -->
        <div class="section">
            <h2 class="section-title text-lg text-gray-700">Datos Personales</h2>
            <div class="data-grid">
                <div class="data-item">
                    <span class="data-label">Nombre:</span>
                    <span><?php echo htmlspecialchars($formulario['nombre'] . ' ' . $formulario['apellido']); ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Fecha Nac.:</span>
                    <span><?php echo date('d/m/Y', strtotime($formulario['fecha_nacimiento'])); ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Edad:</span>
                    <span><?php echo $formulario['edad']; ?> años</span>
                </div>
                <div class="data-item">
                    <span class="data-label">Género:</span>
                    <span><?php echo $formulario['genero']; ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Correo:</span>
                    <span><?php echo htmlspecialchars($formulario['correo']); ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Tel. Celular:</span>
                    <span><?php echo htmlspecialchars($formulario['telefono_celular']); ?></span>
                </div>
                <?php if ($formulario['menor_edad'] === 'Si'): ?>
                <div class="data-item">
                    <span class="data-label">Tutor:</span>
                    <span><?php echo htmlspecialchars($formulario['nombre_tutor']); ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Tel. Tutor:</span>
                    <span><?php echo htmlspecialchars($formulario['telefono_tutor']); ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Relación:</span>
                    <span><?php echo htmlspecialchars($formulario['relacion']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quejas y Afirmaciones -->
        <div class="section">
            <h2 class="section-title text-lg text-gray-700">Quejas y Afirmaciones</h2>
            <div class="grid grid-cols-2 gap-4">
                <?php if (!empty($quejas)): ?>
                <div>
                    <h3 class="font-semibold text-sm mb-2">Quejas Principales:</h3>
                    <ul class="text-sm space-y-1">
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
                    <h3 class="font-semibold text-sm mb-2">Afirmaciones:</h3>
                    <ul class="text-sm space-y-1">
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
        <div class="section">
            <h2 class="section-title text-lg text-gray-700">Información Médica</h2>
            <div class="data-grid mb-2">
                <div class="data-item">
                    <span class="data-label">Embarazada:</span>
                    <span><?php echo $formulario['embarazada']; ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Diabético:</span>
                    <span><?php echo $formulario['diabetico']; ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Fumador:</span>
                    <span><?php echo $formulario['fumador']; ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Drogas:</span>
                    <span><?php echo $formulario['drogas']; ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Renal:</span>
                    <span><?php echo $formulario['renal']; ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Insuf. Cardíaca:</span>
                    <span><?php echo $formulario['insuficiencia']; ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Anticoagulantes:</span>
                    <span><?php echo $formulario['anticoagulantes']; ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">Cáncer:</span>
                    <span><?php echo $formulario['cancer']; ?></span>
                </div>
            </div>

            <?php if ($formulario['drogas'] === 'Si'): ?>
            <div class="data-item mb-1">
                <span class="data-label">Frecuencia de uso de drogas:</span>
                <span><?php echo htmlspecialchars($formulario['drogas_frecuencia']); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($formulario['alergico'] === 'Si'): ?>
            <div class="data-item mb-1">
                <span class="data-label">Medicamentos Alérgicos:</span>
                <span><?php echo htmlspecialchars($formulario['medicamento_alergico']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Medicamentos y Suplementos -->
        <div class="section">
            <h2 class="section-title text-lg text-gray-700">Medicamentos y Suplementos</h2>
            <div class="grid grid-cols-2 gap-4">
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
            <div class="text-sm mt-3">
                <span class="font-semibold block mb-1">Suplementos:</span>
                <span class="block pl-4"><?php echo nl2br(htmlspecialchars($formulario['suplementos'])); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($formulario['comentarios_doctor'])): ?>
        <div class="section">
            <h2 class="section-title text-lg text-gray-700">Observaciones del Doctor</h2>
            <p class="text-sm pl-4"><?php echo nl2br(htmlspecialchars($formulario['comentarios_doctor'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Firmas -->
        <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="flex justify-between items-end space-x-8">
                <div class="flex-1 text-center">
                    <img src="<?php echo $formulario['firma_paciente']; ?>" alt="Firma del Paciente" class="h-16 mx-auto mb-2">
                    <div class="border-t border-gray-400 pt-2 text-sm font-semibold">Firma del Paciente</div>
                </div>
                <div class="flex-1 text-center">
                    <img src="<?php echo $formulario['firma_doctor']; ?>" alt="Firma del Doctor" class="h-16 mx-auto mb-2">
                    <div class="border-t border-gray-400 pt-2 text-sm font-semibold">Firma del Doctor</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Al cargar la página, activar la impresión automáticamente
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
<?php
$conn->close();
?> 