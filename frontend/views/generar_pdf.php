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
        @page {
            margin: 2cm;
            size: letter;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .page {
            max-width: 21cm;
            margin: 0 auto;
            padding: 2cm 1.5cm;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }
        .section {
            margin-bottom: 1.5rem;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
        }
        .data-item {
            margin-bottom: 0.5rem;
        }
        .data-label {
            font-weight: bold;
            color: #555;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 2rem;
            page-break-inside: avoid;
        }
        .signature-box {
            flex: 1;
            max-width: 300px;
            text-align: center;
            margin: 0 1rem;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            font-weight: bold;
        }
        .signature-image {
            height: 100px;
            object-fit: contain;
            margin: 0 auto;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white;
            }
            .page {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Botón de cancelar -->
    <a href="?id=<?php echo $id; ?>&cancelar=true" class="no-print fixed top-4 right-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow flex items-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
        <span>Cerrar</span>
    </a>

    <div class="page">
        <div class="header">
            <img src="/public/assets/img/logo.jpg" alt="Logo" class="h-16 mx-auto mb-4">
            <h1 class="text-2xl font-bold">Consentimiento Médico</h1>
            <p class="text-gray-600"><?php echo date('d/m/Y'); ?></p>
        </div>

        <!-- Datos Personales -->
        <div class="section">
            <h2 class="section-title">Datos Personales</h2>
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
            </div>

            <!-- Información de Contacto -->
            <div class="mt-4">
                <h3 class="font-semibold mb-2">Información de Contacto</h3>
                <div class="data-grid">
                    <div class="data-item">
                        <span class="data-label">Correo:</span>
                        <span><?php echo htmlspecialchars($formulario['correo']); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Tel. Celular:</span>
                        <span><?php echo htmlspecialchars($formulario['telefono_celular']); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Tel. Casa:</span>
                        <span><?php echo htmlspecialchars($formulario['telefono_casa']); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Tel. Trabajo:</span>
                        <span><?php echo htmlspecialchars($formulario['telefono_trabajo']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Dirección -->
            <div class="mt-4">
                <h3 class="font-semibold mb-2">Dirección</h3>
                <div class="data-grid">
                    <div class="data-item">
                        <span class="data-label">Dirección:</span>
                        <span><?php echo htmlspecialchars($formulario['direccion']); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Ciudad:</span>
                        <span><?php echo htmlspecialchars($formulario['ciudad']); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Estado:</span>
                        <span><?php echo htmlspecialchars($formulario['estado']); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Código Postal:</span>
                        <span><?php echo htmlspecialchars($formulario['zipcode']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Contacto de Emergencia -->
            <div class="mt-4">
                <h3 class="font-semibold mb-2">Contacto de Emergencia</h3>
                <div class="data-grid">
                    <div class="data-item">
                        <span class="data-label">Nombre:</span>
                        <span><?php echo htmlspecialchars($formulario['contacto_emergencia']); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Teléfono:</span>
                        <span><?php echo htmlspecialchars($formulario['telefono_emergencia']); ?></span>
                    </div>
                </div>
            </div>

            <?php if ($formulario['menor_edad'] === 'Si'): ?>
            <div class="mt-4">
                <h3 class="font-semibold mb-2">Información del Tutor</h3>
                <div class="data-grid">
                    <div class="data-item">
                        <span class="data-label">Nombre:</span>
                        <span><?php echo htmlspecialchars($formulario['nombre_tutor']); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Teléfono:</span>
                        <span><?php echo htmlspecialchars($formulario['telefono_tutor']); ?></span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Relación:</span>
                        <span><?php echo htmlspecialchars($formulario['relacion']); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quejas y Afirmaciones -->
        <div class="section">
            <h2 class="section-title">Quejas y Afirmaciones</h2>
            <div class="grid grid-cols-2 gap-6">
                <?php if (!empty($quejas)): ?>
                <div>
                    <h3 class="font-semibold mb-2">Quejas Principales:</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($quejas as $queja): ?>
                            <li><?php echo htmlspecialchars($queja); ?></li>
                        <?php endforeach; ?>
                        <?php if (!empty($formulario['otros_quejas'])): ?>
                            <li>Otras: <?php echo htmlspecialchars($formulario['otros_quejas']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($afirmaciones)): ?>
                <div>
                    <h3 class="font-semibold mb-2">Afirmaciones:</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($afirmaciones as $afirmacion): ?>
                            <li><?php echo htmlspecialchars($afirmacion); ?></li>
                        <?php endforeach; ?>
                        <?php if (!empty($formulario['otros_afirmaciones'])): ?>
                            <li>Otras: <?php echo htmlspecialchars($formulario['otros_afirmaciones']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información Médica -->
        <div class="section">
            <h2 class="section-title">Información Médica</h2>
            <div class="data-grid">
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
                <div class="data-item">
                    <span class="data-label">Alérgico:</span>
                    <span><?php echo $formulario['alergico']; ?></span>
                    <?php if ($formulario['alergico'] === 'Si'): ?>
                        <br><span class="data-label">Especificación de alergias:</span>
                        <span><?php echo htmlspecialchars($formulario['medicamento_alergico']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="data-item">
                    <span class="data-label">Condición médica:</span>
                    <span><?php echo $formulario['condicion_medica']; ?></span>
                    <?php if ($formulario['condicion_medica'] === 'Si'): ?>
                        <br><span class="data-label">Explicación:</span>
                        <span><?php echo htmlspecialchars($formulario['condicion_explicacion']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($formulario['drogas'] === 'Si'): ?>
                <div class="mt-4">
                    <span class="data-label">Frecuencia de uso de drogas:</span>
                    <span><?php echo htmlspecialchars($formulario['drogas_frecuencia']); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Medicamentos y Suplementos -->
        <div class="section">
            <h2 class="section-title">Medicamentos y Suplementos</h2>
            <div class="space-y-4">
                <?php if (!empty($formulario['medicamentos_recetados'])): ?>
                <div>
                    <h3 class="font-semibold mb-1">Medicamentos Recetados:</h3>
                    <p class="pl-4"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_recetados'])); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($formulario['medicamentos_venta_libre'])): ?>
                <div>
                    <h3 class="font-semibold mb-1">Medicamentos de Venta Libre:</h3>
                    <p class="pl-4"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_venta_libre'])); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($formulario['suplementos'])): ?>
                <div>
                    <h3 class="font-semibold mb-1">Suplementos:</h3>
                    <p class="pl-4"><?php echo nl2br(htmlspecialchars($formulario['suplementos'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($formulario['comentarios_doctor'])): ?>
        <div class="section">
            <h2 class="section-title">Observaciones del Doctor</h2>
            <p class="pl-4"><?php echo nl2br(htmlspecialchars($formulario['comentarios_doctor'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Firmas -->
        <div class="signatures">
            <div class="signature-box">
                <img src="<?php echo $formulario['firma_paciente']; ?>" alt="Firma del Paciente" class="signature-image">
                <div class="signature-line">Firma del Paciente</div>
            </div>
            <div class="signature-box">
                <img src="<?php echo $formulario['firma_doctor']; ?>" alt="Firma del Doctor" class="signature-image">
                <div class="signature-line">Firma del Profesional de la Salud</div>
            </div>
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