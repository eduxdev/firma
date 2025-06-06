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

// Configuración de la base de datos
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Formulario - Panel del Doctor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Barra de navegación -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <img src="/public/assets/img/logo.jpg" alt="Logo" class="h-12">
                <a href="<?php echo $pagina_volver; ?>" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-6 rounded-lg flex items-center transition duration-150">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Revisar Formulario</h2>

        <!-- Datos Personales -->
        <div class="bg-white rounded-xl shadow-md p-8 mb-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">Datos Personales</h3>
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-700 mb-4">Información Básica</h4>
                        <div class="space-y-3">
                            <p><span class="font-semibold">Nombre:</span> <?php echo htmlspecialchars($formulario['nombre'] . ' ' . $formulario['apellido']); ?></p>
                            <p><span class="font-semibold">Fecha de Nacimiento:</span> <?php echo date('d/m/Y', strtotime($formulario['fecha_nacimiento'])); ?></p>
                            <p><span class="font-semibold">Edad:</span> <?php echo $formulario['edad']; ?> años</p>
                            <p><span class="font-semibold">Género:</span> <?php echo $formulario['genero']; ?></p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-700 mb-4">Contacto</h4>
                        <div class="space-y-3">
                            <p><span class="font-semibold">Correo:</span> <?php echo htmlspecialchars($formulario['correo']); ?></p>
                            <p><span class="font-semibold">Teléfono Celular:</span> <?php echo htmlspecialchars($formulario['telefono_celular']); ?></p>
                            <p><span class="font-semibold">Teléfono Casa:</span> <?php echo htmlspecialchars($formulario['telefono_casa']); ?></p>
                            <p><span class="font-semibold">Teléfono Trabajo:</span> <?php echo htmlspecialchars($formulario['telefono_trabajo']); ?></p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-700 mb-4">Dirección</h4>
                        <div class="space-y-3">
                            <p><span class="font-semibold">Dirección:</span> <?php echo htmlspecialchars($formulario['direccion']); ?></p>
                            <p><span class="font-semibold">Ciudad:</span> <?php echo htmlspecialchars($formulario['ciudad']); ?></p>
                            <p><span class="font-semibold">Estado:</span> <?php echo htmlspecialchars($formulario['estado']); ?></p>
                            <p><span class="font-semibold">Código Postal:</span> <?php echo htmlspecialchars($formulario['zipcode']); ?></p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-700 mb-4">Contacto de Emergencia</h4>
                        <div class="space-y-3">
                            <p><span class="font-semibold">Nombre:</span> <?php echo htmlspecialchars($formulario['contacto_emergencia']); ?></p>
                            <p><span class="font-semibold">Teléfono:</span> <?php echo htmlspecialchars($formulario['telefono_emergencia']); ?></p>
                        </div>
                    </div>

                    <?php if ($formulario['menor_edad'] === 'Si'): ?>
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-700 mb-4">Información del Tutor</h4>
                        <div class="space-y-3">
                            <p><span class="font-semibold">Nombre:</span> <?php echo htmlspecialchars($formulario['nombre_tutor']); ?></p>
                            <p><span class="font-semibold">Teléfono:</span> <?php echo htmlspecialchars($formulario['telefono_tutor']); ?></p>
                            <p><span class="font-semibold">Relación:</span> <?php echo htmlspecialchars($formulario['relacion']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Información Médica -->
        <div class="bg-white rounded-xl shadow-md p-8 mb-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">Información Médica</h3>
            
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h4 class="text-lg font-medium text-gray-700 mb-4">Condiciones Médicas</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <p><span class="font-semibold">Embarazada:</span> <?php echo $formulario['embarazada']; ?></p>
                        <p><span class="font-semibold">Diabético:</span> <?php echo $formulario['diabetico']; ?></p>
                        <p><span class="font-semibold">Fumador:</span> <?php echo $formulario['fumador']; ?></p>
                        <p><span class="font-semibold">Drogas:</span> <?php echo $formulario['drogas']; ?></p>
                        <p><span class="font-semibold">Renal:</span> <?php echo $formulario['renal']; ?></p>
                        <p><span class="font-semibold">Insuf. Cardíaca:</span> <?php echo $formulario['insuficiencia']; ?></p>
                        <p><span class="font-semibold">Anticoagulantes:</span> <?php echo $formulario['anticoagulantes']; ?></p>
                        <p><span class="font-semibold">Cáncer:</span> <?php echo $formulario['cancer']; ?></p>
                    </div>

                    <?php if ($formulario['drogas'] === 'Si'): ?>
                        <div class="mt-4">
                            <p><span class="font-semibold">Frecuencia de uso de drogas:</span> <?php echo htmlspecialchars($formulario['drogas_frecuencia']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($formulario['alergico'] === 'Si'): ?>
                        <div class="mt-4">
                            <p><span class="font-semibold">Medicamentos Alérgicos:</span> <?php echo htmlspecialchars($formulario['medicamento_alergico']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <h4 class="text-lg font-medium text-gray-700 mb-4">Medicamentos y Suplementos</h4>
                    <div class="space-y-6">
                        <div>
                            <p class="font-semibold mb-2">Medicamentos Recetados:</p>
                            <p class="bg-gray-50 p-3 rounded"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_recetados'])); ?></p>
                        </div>
                        <div>
                            <p class="font-semibold mb-2">Medicamentos de Venta Libre:</p>
                            <p class="bg-gray-50 p-3 rounded"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_venta_libre'])); ?></p>
                        </div>
                        <div>
                            <p class="font-semibold mb-2">Suplementos:</p>
                            <p class="bg-gray-50 p-3 rounded"><?php echo nl2br(htmlspecialchars($formulario['suplementos'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quejas y Afirmaciones -->
        <div class="bg-white rounded-xl shadow-md p-8 mb-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">Quejas y Afirmaciones</h3>
            
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h4 class="text-lg font-medium text-gray-700 mb-4">Quejas Principales</h4>
                    <ul class="list-disc list-inside space-y-2">
                    <?php foreach ($quejas as $queja): ?>
                            <li class="text-gray-700"><?php echo htmlspecialchars($queja); ?></li>
                    <?php endforeach; ?>
                    <?php if (!empty($formulario['otros_quejas'])): ?>
                            <li class="text-gray-700">Otras: <?php echo htmlspecialchars($formulario['otros_quejas']); ?></li>
                    <?php endif; ?>
                </ul>
                </div>

                <div>
                    <h4 class="text-lg font-medium text-gray-700 mb-4">Afirmaciones</h4>
                    <ul class="list-disc list-inside space-y-2">
                    <?php foreach ($afirmaciones as $afirmacion): ?>
                            <li class="text-gray-700"><?php echo htmlspecialchars($afirmacion); ?></li>
                    <?php endforeach; ?>
                    <?php if (!empty($formulario['otros_afirmaciones'])): ?>
                            <li class="text-gray-700">Otras: <?php echo htmlspecialchars($formulario['otros_afirmaciones']); ?></li>
                    <?php endif; ?>
                </ul>
                </div>
            </div>
        </div>

        <!-- Firmas y Aprobación -->
        <div class="bg-white rounded-xl shadow-md p-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">Firmas y Aprobación</h3>
            
            <div class="grid grid-cols-2 gap-8">
                <!-- Firma del Paciente -->
                <div>
                    <h4 class="text-lg font-medium text-gray-700 mb-4">Firma del Paciente</h4>
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <img src="<?php echo $formulario['firma_paciente']; ?>" alt="Firma del Paciente" class="max-h-32 mx-auto">
                    </div>
                </div>

                <!-- Firma del Doctor y Decisión -->
                <div>
                    <form method="POST" onsubmit="return validarFormulario()">
                        <!-- Área de Decisión -->
                        <div class="mb-6">
                            <h4 class="text-lg font-medium text-gray-700 mb-4">Decisión</h4>
                            <div class="flex gap-4">
                                <label class="flex-1 relative">
                                    <input type="radio" name="estado" value="aprobado" class="peer sr-only" required>
                                    <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer transition-all hover:bg-gray-50 peer-checked:border-green-500 peer-checked:bg-green-50">
                                        <div class="flex items-center justify-center gap-2">
                                            <i class="bi bi-check-circle text-xl text-green-600"></i>
                                            <span class="font-medium">Aprobar</span>
                </div>
            </div>
                                </label>
                                <label class="flex-1 relative">
                                    <input type="radio" name="estado" value="rechazado" class="peer sr-only" required>
                                    <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer transition-all hover:bg-gray-50 peer-checked:border-red-500 peer-checked:bg-red-50">
                                        <div class="flex items-center justify-center gap-2">
                                            <i class="bi bi-x-circle text-xl text-red-600"></i>
                                            <span class="font-medium">Rechazar</span>
        </div>
            </div>
                                </label>
            </div>
        </div>

                        <!-- Área de Firma -->
                        <div class="mb-6">
                            <h4 class="text-lg font-medium text-gray-700 mb-4">Firma del Profesional de la Salud</h4>
                            <div class="relative">
                                <div class="border-2 border-dashed border-gray-300 rounded-lg bg-white hover:border-gray-400 transition-colors">
                                    <canvas id="firma-doctor" class="w-full h-40"></canvas>
                                </div>
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-gray-400 pointer-events-none firma-placeholder">
                                    <div class="text-center">
                                        <i class="bi bi-pen text-3xl"></i>
                                        <p class="mt-2">Firme aquí</p>
                                    </div>
                </div>
                                <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 transition-colors" onclick="borrarFirmaDoctor()">
                                    <i class="bi bi-trash"></i>
                                </button>
                    </div>
                        </div>
                        <input type="hidden" name="firma_doctor" id="firma_doctor" required>

                        <!-- Botón de Envío -->
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 px-6 rounded-lg flex items-center justify-center text-lg font-medium transition duration-150 shadow-md hover:shadow-lg">
                            <i class="bi bi-check-circle mr-2"></i> Finalizar Revisión
                        </button>
                    </form>
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

            // Ocultar placeholder cuando se empiece a firmar
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
            
            // Si el estado es "aprobado", abrir el PDF en una nueva ventana
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
                        window.open(`generar_pdf.php?id=${id}`, '_blank');
                        window.location.href = 'formularios_aprobados.php';
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