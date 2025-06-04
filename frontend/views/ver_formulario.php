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
            header("Location: revisar_formularios.php");
        }
        exit();
    }
}

// Decodificar arrays JSON
$quejas = json_decode($formulario['quejas'], true) ?? [];
$afirmaciones = json_decode($formulario['afirmaciones'], true) ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Formulario - Panel del Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
</head>
<body>
    <nav class="custom-navbar">
        <img src="/public/assets/img/logo.jpg" alt="Logo">
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Revisar Formulario</h2>
        
        <!-- Datos del Paciente -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Datos del Paciente</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($formulario['nombre'] . ' ' . $formulario['apellido']); ?></p>
                        <p><strong>Fecha de Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($formulario['fecha_nacimiento'])); ?></p>
                        <p><strong>Edad:</strong> <?php echo $formulario['edad']; ?></p>
                        <p><strong>Género:</strong> <?php echo $formulario['genero']; ?></p>
                        <?php if ($formulario['menor_edad'] === 'Si'): ?>
                            <p><strong>Menor de edad:</strong> Sí</p>
                            <p><strong>Nombre del tutor:</strong> <?php echo htmlspecialchars($formulario['nombre_tutor']); ?></p>
                            <p><strong>Teléfono del tutor:</strong> <?php echo htmlspecialchars($formulario['telefono_tutor']); ?></p>
                            <p><strong>Relación:</strong> <?php echo htmlspecialchars($formulario['relacion']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Correo:</strong> <?php echo htmlspecialchars($formulario['correo']); ?></p>
                        <p><strong>Teléfono Celular:</strong> <?php echo htmlspecialchars($formulario['telefono_celular']); ?></p>
                        <p><strong>Teléfono Casa:</strong> <?php echo htmlspecialchars($formulario['telefono_casa']); ?></p>
                        <p><strong>Teléfono Trabajo:</strong> <?php echo htmlspecialchars($formulario['telefono_trabajo']); ?></p>
                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($formulario['direccion']); ?></p>
                        <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($formulario['ciudad']); ?></p>
                        <p><strong>Estado:</strong> <?php echo htmlspecialchars($formulario['estado']); ?></p>
                        <p><strong>Código Postal:</strong> <?php echo htmlspecialchars($formulario['zipcode']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quejas y Afirmaciones -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Quejas y Afirmaciones</h4>
            </div>
            <div class="card-body">
                <h5>Quejas Principales:</h5>
                <ul>
                    <?php foreach ($quejas as $queja): ?>
                        <li><?php echo htmlspecialchars($queja); ?></li>
                    <?php endforeach; ?>
                    <?php if (!empty($formulario['otros_quejas'])): ?>
                        <li>Otras quejas: <?php echo htmlspecialchars($formulario['otros_quejas']); ?></li>
                    <?php endif; ?>
                </ul>

                <h5>Afirmaciones:</h5>
                <ul>
                    <?php foreach ($afirmaciones as $afirmacion): ?>
                        <li><?php echo htmlspecialchars($afirmacion); ?></li>
                    <?php endforeach; ?>
                    <?php if (!empty($formulario['otros_afirmaciones'])): ?>
                        <li>Otras afirmaciones: <?php echo htmlspecialchars($formulario['otros_afirmaciones']); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Información Médica -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Información Médica</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Embarazada:</strong> <?php echo $formulario['embarazada']; ?></p>
                        <p><strong>Diabético:</strong> <?php echo $formulario['diabetico']; ?></p>
                        <p><strong>Fumador:</strong> <?php echo $formulario['fumador']; ?></p>
                        <p><strong>Drogas:</strong> <?php echo $formulario['drogas']; ?></p>
                        <?php if ($formulario['drogas'] === 'Si'): ?>
                            <p><strong>Frecuencia de uso:</strong> <?php echo htmlspecialchars($formulario['drogas_frecuencia']); ?></p>
                        <?php endif; ?>
                        <p><strong>Paciente Renal:</strong> <?php echo $formulario['renal']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Insuficiencia Cardíaca:</strong> <?php echo $formulario['insuficiencia']; ?></p>
                        <p><strong>Anticoagulantes:</strong> <?php echo $formulario['anticoagulantes']; ?></p>
                        <p><strong>Cáncer:</strong> <?php echo $formulario['cancer']; ?></p>
                        <p><strong>Alergias:</strong> <?php echo $formulario['alergico']; ?></p>
                        <?php if ($formulario['alergico'] === 'Si'): ?>
                            <p><strong>Medicamentos Alérgicos:</strong> <?php echo htmlspecialchars($formulario['medicamento_alergico']); ?></p>
                        <?php endif; ?>
                        <p><strong>Condición Médica:</strong> <?php echo $formulario['condicion_medica']; ?></p>
                        <?php if ($formulario['condicion_medica'] === 'Si'): ?>
                            <p><strong>Explicación:</strong> <?php echo htmlspecialchars($formulario['condicion_explicacion']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Medicamentos y Suplementos -->
                <div class="mt-4">
                    <h5>Medicamentos y Suplementos</h5>
                    <p><strong>Medicamentos Recetados:</strong><br><?php echo nl2br(htmlspecialchars($formulario['medicamentos_recetados'])); ?></p>
                    <p><strong>Medicamentos de Venta Libre:</strong><br><?php echo nl2br(htmlspecialchars($formulario['medicamentos_venta_libre'])); ?></p>
                    <p><strong>Suplementos:</strong><br><?php echo nl2br(htmlspecialchars($formulario['suplementos'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Firma del Paciente -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Firma del Paciente</h4>
            </div>
            <div class="card-body">
                <img src="<?php echo $formulario['firma_paciente']; ?>" alt="Firma del Paciente" class="img-fluid" style="max-height: 200px;">
            </div>
        </div>

        <!-- Formulario de Aprobación -->
        <form method="POST" onsubmit="return validarFormulario()">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Revisión del Doctor</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Comentarios:</label>
                        <textarea name="comentarios" class="form-control" rows="3"></textarea>
                    </div>

                    <!-- Área de firma del doctor -->
                    <div class="mb-3">
                        <label class="form-label">Firma del Doctor:</label>
                        <div class="signature-box border rounded p-2" style="background-color: #f8f9fa;">
                            <canvas id="firma-doctor" width="600" height="200" style="border: 1px solid #ccc; width: 100%; height: 200px;"></canvas>
                        </div>
                        <button type="button" class="btn btn-link text-danger" onclick="borrarFirmaDoctor()">
                            <i class="bi bi-trash"></i> Borrar Firma
                        </button>
                        <input type="hidden" name="firma_doctor" id="firma_doctor" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Decisión:</label>
                        <select name="estado" class="form-select" required>
                            <option value="">Seleccione una opción</option>
                            <option value="aprobado">Aprobar y Generar PDF</option>
                            <option value="rechazado">Rechazar</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Enviar Revisión
                        </button>
                        <a href="revisar_formularios.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let signaturePad;

        window.onload = function() {
            const canvas = document.getElementById('firma-doctor');
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });

            // Ajustar el tamaño del canvas al cargar y al redimensionar
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
        }

        function borrarFirmaDoctor() {
            signaturePad.clear();
        }

        function validarFormulario() {
            if (signaturePad.isEmpty()) {
                alert('Por favor, firme el documento antes de continuar.');
                return false;
            }

            document.getElementById('firma_doctor').value = signaturePad.toDataURL();
            return true;
        }
    </script>
</body>
</html>
<?php
$conn->close();
?> 