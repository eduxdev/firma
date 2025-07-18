<?php
// Configuración de la base de datos
require_once '../../backend/db/conection.php';
require_once '../../backend/db/mail_config.php';

// Formatear la fecha de nacimiento
$dia = $_POST['fecha_nacimiento_dia'];
$mes = $_POST['fecha_nacimiento_mes'];
$anio = $_POST['fecha_nacimiento_anio'];
$fecha_nacimiento = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);

// Convertir arrays a JSON para almacenamiento
$quejas = isset($_POST['quejas']) ? json_encode($_POST['quejas']) : '[]';
$afirmaciones = isset($_POST['afirmaciones']) ? json_encode($_POST['afirmaciones']) : '[]';

// Manejar valores nulos
$nombre_tutor = !empty($_POST['nombre_tutor']) ? $_POST['nombre_tutor'] : null;
$telefono_tutor = !empty($_POST['telefono_tutor']) ? $_POST['telefono_tutor'] : null;
$relacion = !empty($_POST['relacion']) ? $_POST['relacion'] : null;
$drogas_frecuencia = !empty($_POST['drogas_frecuencia']) ? $_POST['drogas_frecuencia'] : null;
$medicamento_alergico = !empty($_POST['medicamento_alergico']) ? $_POST['medicamento_alergico'] : null;
$condicion_explicacion = !empty($_POST['condicion_explicacion']) ? $_POST['condicion_explicacion'] : null;

// Preparar la consulta SQL
$sql = "INSERT INTO formularios_consentimiento (
    nombre, apellido, menor_edad, nombre_tutor, telefono_tutor, 
    relacion, fecha_nacimiento, edad, genero, correo, direccion,
    ciudad, estado, zipcode, telefono_casa, telefono_celular,
    telefono_trabajo, contacto_emergencia, telefono_emergencia,
    quejas, otros_quejas, afirmaciones, otros_afirmaciones,
    embarazada, diabetico, fumador, drogas, drogas_frecuencia,
    renal, insuficiencia, anticoagulantes, cancer, alergico,
    medicamento_alergico, condicion_medica, condicion_explicacion,
    medicamentos_recetados, medicamentos_venta_libre, suplementos,
    firma_paciente, estado_revision
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')";

$stmt = $conn->prepare($sql);

// Verificar si la preparación fue exitosa
if ($stmt === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param("ssssssssssssssssssssssssssssssssssssssss",
    $_POST['nombre'],
    $_POST['apellido'],
    $_POST['menor_edad'],
    $nombre_tutor,
    $telefono_tutor,
    $relacion,
    $fecha_nacimiento,
    $_POST['edad'],
    $_POST['genero'],
    $_POST['correo'],
    $_POST['direccion'],
    $_POST['ciudad'],
    $_POST['estado'],
    $_POST['zipcode'],
    $_POST['telefono_casa'],
    $_POST['telefono_celular'],
    $_POST['telefono_trabajo'],
    $_POST['contacto_emergencia'],
    $_POST['telefono_emergencia'],
    $quejas,
    $_POST['otros_quejas'],
    $afirmaciones,
    $_POST['otros_afirmaciones'],
    $_POST['embarazada'],
    $_POST['diabetico'],
    $_POST['fumador'],
    $_POST['drogas'],
    $drogas_frecuencia,
    $_POST['renal'],
    $_POST['insuficiencia'],
    $_POST['anticoagulantes'],
    $_POST['cancer'],
    $_POST['alergico'],
    $medicamento_alergico,
    $_POST['condicion_medica'],
    $condicion_explicacion,
    $_POST['medicamentos_recetados'],
    $_POST['medicamentos_venta_libre'],
    $_POST['suplementos'],
    $_POST['firma_paciente']
);

if ($stmt->execute()) {
    // Enviar notificación por correo
    $datos_formulario = [
        'nombre' => $_POST['nombre'],
        'apellido' => $_POST['apellido'],
        'fecha_nacimiento' => $fecha_nacimiento,
        'correo' => $_POST['correo'],
        'telefono_celular' => $_POST['telefono_celular']
    ];
    
    enviarNotificacionNuevoFormulario($datos_formulario);

    // Éxito - redirigir a página de confirmación
    header('Location: confirmacion.php');
    exit();
} else {
    // Error
    echo "Error al guardar el formulario: " . $stmt->error;
}

$stmt->close();
$conn->close();
?> 