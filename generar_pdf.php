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
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        h2 {
            color: #34495e;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-top: 25px;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
            color: #2c3e50;
        }
        .value {
            margin-left: 10px;
        }
        ul {
            list-style-type: none;
            padding-left: 0;
        }
        li {
            margin-bottom: 5px;
        }
        .signatures {
            margin-top: 50px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            page-break-inside: avoid;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 80px;
            padding-top: 10px;
        }
        .signature-image {
            max-width: 200px;
            max-height: 100px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.jpg" alt="Logo" class="logo">
        <h1>Consentimiento Médico</h1>
    </div>

    <div class="section">
        <h2>Datos Personales</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Nombre:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['nombre'] . ' ' . $formulario['apellido']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Fecha de Nacimiento:</span>
                <span class="value"><?php echo date('d/m/Y', strtotime($formulario['fecha_nacimiento'])); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Edad:</span>
                <span class="value"><?php echo $formulario['edad']; ?> años</span>
            </div>
            <div class="info-item">
                <span class="label">Género:</span>
                <span class="value"><?php echo $formulario['genero']; ?></span>
            </div>
            <?php if ($formulario['menor_edad'] === 'Si'): ?>
            <div class="info-item">
                <span class="label">Nombre del Tutor:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['nombre_tutor']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Teléfono del Tutor:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['telefono_tutor']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Relación:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['relacion']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <h2>Información de Contacto</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Correo:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['correo']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Teléfono Celular:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['telefono_celular']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Teléfono Casa:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['telefono_casa']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Teléfono Trabajo:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['telefono_trabajo']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Dirección:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['direccion']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Ciudad:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['ciudad']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Estado:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['estado']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Código Postal:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['zipcode']); ?></span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Quejas y Afirmaciones</h2>
        <h3>Quejas Principales:</h3>
        <ul>
            <?php foreach ($quejas as $queja): ?>
                <li><?php echo htmlspecialchars($queja); ?></li>
            <?php endforeach; ?>
            <?php if (!empty($formulario['otros_quejas'])): ?>
                <li>Otras quejas: <?php echo htmlspecialchars($formulario['otros_quejas']); ?></li>
            <?php endif; ?>
        </ul>

        <h3>Afirmaciones:</h3>
        <ul>
            <?php foreach ($afirmaciones as $afirmacion): ?>
                <li><?php echo htmlspecialchars($afirmacion); ?></li>
            <?php endforeach; ?>
            <?php if (!empty($formulario['otros_afirmaciones'])): ?>
                <li>Otras afirmaciones: <?php echo htmlspecialchars($formulario['otros_afirmaciones']); ?></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="section">
        <h2>Información Médica</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Embarazada:</span>
                <span class="value"><?php echo $formulario['embarazada']; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Diabético:</span>
                <span class="value"><?php echo $formulario['diabetico']; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Fumador:</span>
                <span class="value"><?php echo $formulario['fumador']; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Drogas:</span>
                <span class="value"><?php echo $formulario['drogas']; ?></span>
            </div>
            <?php if ($formulario['drogas'] === 'Si'): ?>
            <div class="info-item">
                <span class="label">Frecuencia de uso:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['drogas_frecuencia']); ?></span>
            </div>
            <?php endif; ?>
            <div class="info-item">
                <span class="label">Paciente Renal:</span>
                <span class="value"><?php echo $formulario['renal']; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Insuficiencia Cardíaca:</span>
                <span class="value"><?php echo $formulario['insuficiencia']; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Anticoagulantes:</span>
                <span class="value"><?php echo $formulario['anticoagulantes']; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Cáncer:</span>
                <span class="value"><?php echo $formulario['cancer']; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Alergias:</span>
                <span class="value"><?php echo $formulario['alergico']; ?></span>
            </div>
            <?php if ($formulario['alergico'] === 'Si'): ?>
            <div class="info-item">
                <span class="label">Medicamentos Alérgicos:</span>
                <span class="value"><?php echo htmlspecialchars($formulario['medicamento_alergico']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <h2>Medicamentos y Suplementos</h2>
        <div class="info-item">
            <span class="label">Medicamentos Recetados:</span><br>
            <span class="value"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_recetados'])); ?></span>
        </div>
        <div class="info-item">
            <span class="label">Medicamentos de Venta Libre:</span><br>
            <span class="value"><?php echo nl2br(htmlspecialchars($formulario['medicamentos_venta_libre'])); ?></span>
        </div>
        <div class="info-item">
            <span class="label">Suplementos:</span><br>
            <span class="value"><?php echo nl2br(htmlspecialchars($formulario['suplementos'])); ?></span>
        </div>
    </div>

    <?php if (!empty($formulario['comentarios_doctor'])): ?>
    <div class="section">
        <h2>Observaciones del Doctor</h2>
        <p><?php echo nl2br(htmlspecialchars($formulario['comentarios_doctor'])); ?></p>
    </div>
    <?php endif; ?>

    <div class="section signatures">
        <div class="signature-box">
            <img src="<?php echo $formulario['firma_paciente']; ?>" alt="Firma del Paciente" class="signature-image">
            <div class="signature-line">
                Firma del Paciente<br>
                <?php echo htmlspecialchars($formulario['nombre'] . ' ' . $formulario['apellido']); ?>
            </div>
        </div>
        <div class="signature-box">
            <img src="<?php echo $formulario['firma_doctor']; ?>" alt="Firma del Doctor" class="signature-image">
            <div class="signature-line">
                Firma del Doctor<br>
                Dr. <?php echo htmlspecialchars($_SESSION['doctor_nombre'] . ' ' . $_SESSION['doctor_apellido']); ?>
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